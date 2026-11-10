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
