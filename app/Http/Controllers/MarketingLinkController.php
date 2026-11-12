<?php

namespace App\Http\Controllers;

use App\Enums\Goal;
use App\Models\Branch;
use App\Models\Event;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarketingLinkController extends Controller
{
    /**
     * Show marketing links and QR codes page (Sheet 08 equivalent).
     */
    public function index(Request $request, QrCodeService $qrCodeService): Response
    {
        if (!$request->user()->hasAnyRole(['marketing', 'admin'])) {
            abort(403, 'Akses terbatas untuk Tim Marketing dan Administrator.');
        }

        $query = Event::with('branch');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        $events = $query->latest('event_date')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Event $event) use ($qrCodeService) {
                $surveyUrl = url("/survei/{$event->survey_token}");

                return [
                    'id' => $event->id,
                    'code' => $event->code,
                    'name' => $event->name,
                    'branch_name' => $event->branch->name,
                    'event_date_formatted' => $event->event_date->format('d/m/Y'),
                    'goal' => $event->goal->value,
                    'goal_label' => $event->goal->label(),
                    'survey_token' => $event->survey_token,
                    'survey_url' => $surveyUrl,
                    'report_url' => route('events.report.edit', $event->id),
                    'sales_url' => $event->goal === Goal::SALES ? route('events.sales.edit', $event->id) : null,
                    'qr_svg' => $qrCodeService->generateSvg($surveyUrl, 150),
                    'qr_download_url' => route('marketing.links.downloadQr', $event->id),
                    'responses_count' => $event->surveyResponses()->count(),
                    'export_responses_url' => route('marketing.links.surveyResponses', $event->id),
                ];
            });

        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Marketing/Links', [
            'events' => $events,
            'branches' => $branches,
            'filters' => $request->only(['search', 'branch_id']),
        ]);
    }

    /**
     * Export raw survey responses for market research to CSV with UTF-8 BOM.
     */
    public function exportSurveyResponses(Request $request, Event $event): StreamedResponse
    {
        if (!$request->user()->hasAnyRole(['marketing', 'admin'])) {
            abort(403, 'Akses terbatas untuk Tim Marketing dan Administrator.');
        }

        $responses = $event->surveyResponses()->latest()->get();
        $filename = "respon_survei_{$event->code}_" . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($responses, $event) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Respon',
                'Kode Event',
                'Nama Event',
                'Waktu Submit',
                'Status Validasi (Counted)',
                'Spontaneous Brand Recall (Unaided)',
                'Relevansi Audiens (Targeted)',
                'Status Kemitraan (Customer/Partner)',
                'Skor Kemitraan (1-5)',
                'Pemahaman Sebelum Acara (1-5)',
                'Pemahaman Sesudah Acara (1-5)',
                'Uplift Pemahaman',
            ]);

            foreach ($responses as $resp) {
                $ans = $resp->answers ?? [];
                $before = isset($ans['awareness_before']) ? (int) $ans['awareness_before'] : null;
                $after = isset($ans['awareness_after']) ? (int) $ans['awareness_after'] : null;
                $uplift = ($before !== null && $after !== null) ? ($after - $before) : null;

                fputcsv($handle, [
                    $resp->id,
                    $event->code,
                    $event->name,
                    $resp->created_at->format('Y-m-d H:i:s'),
                    $resp->counted ? 'Valid (Dihitung)' : 'Duplikat / Tidak Dihitung',
                    $ans['unaided_brand_recall'] ?? $ans['unassisted_brand'] ?? '-',
                    isset($ans['audience_relevance']) ? ($ans['audience_relevance'] ? 'Sesuai' : 'Tidak') : (isset($ans['relevance_score']) ? $ans['relevance_score'] : '-'),
                    isset($ans['is_customer_or_partner']) ? ($ans['is_customer_or_partner'] ? 'Ya' : 'Bukan') : (isset($ans['is_current_client']) ? ($ans['is_current_client'] ? 'Ya' : 'Bukan') : '-'),
                    $ans['relationship_score'] ?? '-',
                    $before ?? '-',
                    $after ?? '-',
                    $uplift !== null ? ($uplift >= 0 ? "+{$uplift}" : "{$uplift}") : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Download high-res QR code PNG with naming: QR_SURVEI_[KODE EVENT].png
     */
    public function downloadQr(Request $request, Event $event, QrCodeService $qrCodeService): HttpResponse
    {
        if (!$request->user()->hasAnyRole(['marketing', 'admin'])) {
            abort(403, 'Akses terbatas untuk Tim Marketing dan Administrator.');
        }

        $surveyUrl = url("/survei/{$event->survey_token}");
        $pngContent = $qrCodeService->generatePng($surveyUrl, 600);
        $filename = "QR_SURVEI_{$event->code}.png";

        return response($pngContent)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
