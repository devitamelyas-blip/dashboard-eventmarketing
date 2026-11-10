<?php

namespace App\Services\Evaluation;

use App\Enums\DataStatus;
use App\Enums\EventPortfolio;
use App\Enums\Goal;
use App\Enums\Recommendation;
use App\Enums\VerificationStatus;
use App\Models\Event;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class EventEvaluator
{
    /**
     * Mesin evaluasi event utama sebagai fungsi murni.
     * Menerima model/DTO tanpa query database di dalam service ini.
     *
     * @param Event $event Model event beserta relasi yang sudah dimuat (report, salesUpdate, surveyResponses)
     * @param iterable|null $surveyResponses Koleksi/array survey response (opsional jika relasi event sudah ada)
     * @param CarbonInterface|null $asOfDate Tanggal titik evaluasi (default: now())
     * @return array
     */
    public static function evaluate(
        Event $event,
        ?iterable $surveyResponses = null,
        ?CarbonInterface $asOfDate = null
    ): array {
        $asOf = $asOfDate ?? Carbon::now();
        $isVerified = ($event->verification_status === VerificationStatus::TERVERIFIKASI);

        // 1. Ekstraksi Data Relasi
        $report = $event->report;
        $salesUpdate = $event->salesUpdate;

        $actualParticipants = $report?->actual_participants;
        $validLeads = $report?->valid_leads;
        $actualTeamCost = $report?->actual_team_cost !== null ? (float) $report->actual_team_cost : null;

        $revenue = $salesUpdate?->revenue !== null ? (float) $salesUpdate->revenue : null;

        // Kumpulkan respons survei counted=true
        $responses = $surveyResponses ?? $event->surveyResponses ?? [];
        $countedResponses = [];
        foreach ($responses as $resp) {
            $isCounted = is_array($resp) ? ($resp['counted'] ?? true) : ($resp->counted ?? true);
            if ($isCounted) {
                $countedResponses[] = $resp;
            }
        }
        $surveyCount = count($countedResponses);

        // 2. Ambang Batas Survei
        $minSurveyRequired = SurveyThreshold::calculate($actualParticipants);
        $isSurveySufficient = ($minSurveyRequired !== null && $minSurveyRequired > 0 && $surveyCount >= $minSurveyRequired);

        // Relevansi audiens umum dari survei
        $relevantCount = 0;
        foreach ($countedResponses as $resp) {
            $ans = is_array($resp) ? ($resp['answers'] ?? []) : ($resp->answers ?? []);
            if (!empty($ans['audience_relevance'])) {
                $relevantCount++;
            }
        }
        $audienceRelevancePct = $surveyCount > 0 ? round(($relevantCount / $surveyCount) * 100, 2) : null;

        // 3. Evaluasi Biaya (CostEvaluator)
        $cost = CostEvaluator::evaluate(
            $event->proposal_cost !== null ? (float) $event->proposal_cost : null,
            $event->team_budget !== null ? (float) $event->team_budget : null,
            $actualTeamCost
        );

        // 4. Evaluasi Tujuan Utama (Goal)
        $goalResult = match ($event->goal) {
            Goal::SALES => SalesEvaluator::evaluate(
                $event->event_date,
                $event->proposal_cost !== null ? (float) $event->proposal_cost : null,
                $revenue,
                $isVerified,
                $asOf,
                $audienceRelevancePct
            ),
            Goal::AWARENESS => AwarenessEvaluator::evaluate(
                $surveyCount,
                $minSurveyRequired,
                $countedResponses
            ),
            Goal::RELATIONSHIP => RelationshipEvaluator::evaluate(
                $surveyCount,
                $minSurveyRequired,
                $countedResponses
            ),
        };

        // 5. Penentuan Status Data (DataStatus)
        $followUps = [];
        $dataStatus = DataStatus::TERVERIFIKASI;

        if ($event->goal === Goal::SALES) {
            $h90Date = $event->event_date->copy()->addDays(90)->startOfDay();
            if ($asOf->startOfDay()->lt($h90Date)) {
                $dataStatus = DataStatus::BELUM_JATUH_TEMPO;
                $days = (int) $asOf->startOfDay()->diffInDays($h90Date, false);
                $followUps[] = "Tunggu jatuh tempo H+90 pada tanggal {$h90Date->format('d/m/Y')} (tersisa {$days} hari).";
            } elseif ($revenue === null) {
                $dataStatus = DataStatus::MENUNGGU_LAPORAN;
                $followUps[] = "Laporkan realisasi omzet penjualan H+90 oleh PIC cabang.";
            } elseif (!$isVerified) {
                $dataStatus = DataStatus::MENUNGGU_VERIFIKASI;
                $followUps[] = "Lakukan verifikasi bukti invoice omzet oleh Tim Marketing.";
            } elseif ($actualParticipants === null || !$isSurveySufficient || $actualTeamCost === null) {
                $dataStatus = DataStatus::DATA_BELUM_CUKUP;
                if ($actualTeamCost === null) {
                    $followUps[] = "Laporkan biaya operasional tim aktual pada laporan pasca-event.";
                }
                if ($actualParticipants === null) {
                    $followUps[] = "Laporkan jumlah peserta aktual yang hadir.";
                } elseif (!$isSurveySufficient) {
                    $followUps[] = "Kumpulkan survei peserta valid: {$surveyCount} dari minimal {$minSurveyRequired} respons.";
                }
            }
        } else {
            // Awareness / Relationship
            if ($actualParticipants === null || !$isSurveySufficient || $actualTeamCost === null) {
                $dataStatus = DataStatus::DATA_BELUM_CUKUP;
                if ($actualParticipants === null) {
                    $followUps[] = "Laporkan jumlah peserta aktual yang hadir.";
                } elseif (!$isSurveySufficient) {
                    $followUps[] = "Kumpulkan survei peserta valid: {$surveyCount} dari minimal {$minSurveyRequired} respons.";
                }
                if ($actualTeamCost === null) {
                    $followUps[] = "Laporkan biaya operasional tim aktual pada laporan pasca-event.";
                }
            } elseif (!$isVerified) {
                $dataStatus = DataStatus::MENUNGGU_VERIFIKASI;
                $followUps[] = "Lakukan verifikasi kelengkapan event oleh Tim Marketing.";
            }
        }

        // 6. Rekomendasi Otomatis
        $recommendation = Recommendation::BELUM_FINAL;
        $recommendationReason = '';

        if ($dataStatus !== DataStatus::TERVERIFIKASI) {
            $recommendation = Recommendation::BELUM_FINAL;
            $recommendationReason = "Evaluasi belum final karena kelengkapan data atau status verifikasi marketing belum terpenuhi.";
        } else {
            // Data sudah final & terverifikasi
            $goalPassed = !empty($goalResult['passed']);
            $costStatus = $cost['status']; // 'hemat_atau_sesuai' | 'over_budget'

            if ($goalPassed && $costStatus !== 'over_budget') {
                $recommendation = Recommendation::LAYAK;
                $recommendationReason = "Target tujuan utama berhasil tercapai dan realisasi biaya tetap berada dalam batas anggaran rencana.";
            } elseif ($goalPassed && $costStatus === 'over_budget') {
                $recommendation = Recommendation::PERLU_EVALUASI;
                $recommendationReason = "Target tujuan utama tercapai, namun realisasi total biaya melebihi anggaran rencana ({$cost['budget_variance']}x over budget).";
            } else {
                // Goal tidak lolos
                if ($event->goal === Goal::SALES && isset($goalResult['ratio']) && $goalResult['ratio'] >= 7.0) {
                    $recommendation = Recommendation::PERLU_EVALUASI;
                    $recommendationReason = "Rasio omzet mencapai {$goalResult['ratio']}x (mendekati ambang 10.0x). Perlu evaluasi konversi tindak lanjut.";
                } elseif ($event->goal === Goal::AWARENESS && isset($goalResult['avg_uplift']) && $goalResult['avg_uplift'] >= 0.3) {
                    $recommendation = Recommendation::PERLU_EVALUASI;
                    $recommendationReason = "Uplift pemahaman mencapai {$goalResult['avg_uplift']} poin (mendekati ambang 0.5 poin).";
                } elseif ($event->goal === Goal::RELATIONSHIP && isset($goalResult['avg_relationship_score']) && $goalResult['avg_relationship_score'] >= 3.5) {
                    $recommendation = Recommendation::PERLU_EVALUASI;
                    $recommendationReason = "Skor hubungan kemitraan mencapai {$goalResult['avg_relationship_score']}/5.0 (mendekati ambang 4.0).";
                } else {
                    $recommendation = Recommendation::TIDAK_LAYAK;
                    $recommendationReason = "Capaian hasil tidak memenuhi standar kelayakan tujuan utama PT Kencana Maju Bersama.";
                }
            }
        }

        // 7. Flags & Metadata
        $portfolio = $event->portfolio;
        $flags = [
            'is_demo' => ($portfolio === EventPortfolio::DEMO),
            'is_uat' => ($portfolio === EventPortfolio::UAT),
            'is_operational' => ($portfolio === EventPortfolio::OPERATIONAL),
            'decision_stale' => (bool) $event->decision_stale_flag,
            'needs_verification' => ($event->verification_status !== VerificationStatus::TERVERIFIKASI),
            'survey_insufficient' => !$isSurveySufficient,
            'audience_relevance_pct' => $audienceRelevancePct,
            'follow_ups' => $followUps,
        ];

        return [
            'event_id' => $event->id,
            'code' => $event->code,
            'name' => $event->name,
            'portfolio' => $portfolio->value,
            'portfolio_label' => $portfolio->label(),
            'goal' => $event->goal->value,
            'goal_label' => $event->goal->label(),
            'verification_status' => $event->verification_status->value,
            'verification_status_label' => $event->verification_status->label(),
            'director_decision' => $event->director_decision,
            'director_note' => $event->director_note,
            'data_status' => $dataStatus->value,
            'data_status_label' => $dataStatus->label(),
            'recommendation' => $recommendation->value,
            'recommendation_label' => $recommendation->label(),
            'recommendation_reason' => $recommendationReason,
            'survey' => [
                'actual_participants' => $actualParticipants,
                'min_required' => $minSurveyRequired,
                'collected' => $surveyCount,
                'is_sufficient' => $isSurveySufficient,
                'relevance_pct' => $audienceRelevancePct,
            ],
            'cost' => $cost,
            'goal_result' => $goalResult,
            'flags' => $flags,
        ];
    }
}
