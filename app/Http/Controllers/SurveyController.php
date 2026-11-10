<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitSurveyRequest;
use App\Models\Event;
use App\Models\Respondent;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class SurveyController extends Controller
{
    /**
     * Show public survey form.
     */
    public function show(Request $request, string $token): Response
    {
        $event = Event::where('survey_token', $token)->first();

        if (!$event) {
            abort(404, 'Tautan survei tidak valid atau telah kedaluwarsa.');
        }

        $googleUser = session('google_survey_user');

        return Inertia::render('Survey/Form', [
            'token' => $token,
            'event' => [
                'name' => $event->name,
                'type_label' => $event->type->label(),
                'event_date_formatted' => $event->event_date->format('d/m/Y'),
                'location' => $event->location,
            ],
            'googleUser' => $googleUser,
        ]);
    }

    /**
     * Redirect to Google OAuth for survey.
     */
    public function redirectToGoogle(Request $request, string $token): RedirectResponse
    {
        $event = Event::where('survey_token', $token)->firstOrFail();

        session(['target_survey_token' => $token]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth Callback.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            session([
                'google_survey_user' => [
                    'email' => strtolower(trim($googleUser->getEmail())),
                    'name' => $googleUser->getName(),
                ],
            ]);

            $token = session('target_survey_token');

            if ($token) {
                return redirect()->route('survey.show', $token);
            }

            return redirect()->route('dashboard');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Gagal melakukan autentikasi Google: ' . $e->getMessage());
        }
    }

    /**
     * Store survey response.
     */
    public function store(SubmitSurveyRequest $request, string $token): Response|RedirectResponse
    {
        $event = Event::where('survey_token', $token)->first();

        if (!$event) {
            abort(404, 'Tautan survei tidak valid atau event tidak ditemukan.');
        }

        // Get email from google session or request
        $email = session('google_survey_user.email') ?? $request->input('email');

        if (!$email) {
            return redirect()->route('survey.show', $token)
                ->withErrors(['email' => 'Silakan lakukan login akun Google terlebih dahulu sebelum mengisi survei.']);
        }

        $email = strtolower(trim($email));
        $validated = $request->validated();

        // 1. Save or update respondent identity in isolated table
        Respondent::updateOrCreate(
            ['email' => $email],
            [
                'name' => $validated['name'] ?? session('google_survey_user.name'),
                'organization' => $validated['organization'],
                'phone' => $validated['phone'] ?? null,
            ]
        );

        // 2. Hash email for survey deduplication
        $emailHash = SurveyResponse::hashEmail($email);

        // Check if an existing response for this event from this email was already counted
        $alreadyCounted = SurveyResponse::where('event_id', $event->id)
            ->where('email_hash', $emailHash)
            ->where('counted', true)
            ->exists();

        $counted = !$alreadyCounted;

        // 3. Anomaly detection: check rapid submission (e.g., submissions within 15 seconds from same hash)
        $anomalyFlags = null;
        $recentSubmission = SurveyResponse::where('event_id', $event->id)
            ->where('email_hash', $emailHash)
            ->where('submitted_at', '>=', now()->subSeconds(15))
            ->exists();

        if ($recentSubmission) {
            $anomalyFlags = ['rapid_submission' => true, 'note' => 'Pengisian berulang dalam jeda waktu sangat singkat'];
        }

        // Check brand recall text (INSTRUMEN RECALL_V2)
        $recallText = $validated['unaided_brand_recall'] ?? '';
        $isKencana = (bool) preg_match('/\b(kencana|kencana\s*truss|pt\s*kencana)\b/i', $recallText);

        // 4. Save response (without personal identity)
        SurveyResponse::create([
            'event_id' => $event->id,
            'email_hash' => $emailHash,
            'counted' => $counted,
            'answers' => [
                'unaided_brand_recall' => $recallText,
                'brand_recalled_kencana' => $isKencana,
                'audience_relevance' => (bool) $validated['audience_relevance'],
                'is_customer_or_partner' => (bool) $validated['is_customer_or_partner'],
                'relationship_score' => (int) $validated['relationship_score'],
                'awareness_before' => (int) $validated['awareness_before'],
                'awareness_after' => (int) $validated['awareness_after'],
                'awareness_uplift' => (int) $validated['awareness_after'] - (int) $validated['awareness_before'],
            ],
            'anomaly_flags' => $anomalyFlags,
            'submitted_at' => now(),
        ]);

        return Inertia::render('Survey/Thanks', [
            'eventName' => $event->name,
        ]);
    }
}
