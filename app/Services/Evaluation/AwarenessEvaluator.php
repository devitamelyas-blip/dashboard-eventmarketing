<?php

namespace App\Services\Evaluation;

class AwarenessEvaluator
{
    /**
     * Mengevaluasi tujuan AWARENESS sebagai fungsi murni.
     *
     * @param int $surveyCount Jumlah survei valid (counted=true)
     * @param int|null $minSurveyRequired Ambang minimum survei
     * @param iterable $countedResponses Kumpulan respons survei counted=true
     * @return array
     */
    public static function evaluate(int $surveyCount, ?int $minSurveyRequired, iterable $countedResponses): array
    {
        $isSurveySufficient = ($minSurveyRequired !== null && $minSurveyRequired > 0 && $surveyCount >= $minSurveyRequired);

        $kencanaRecallCount = 0;
        $relevantAudienceCount = 0;
        $totalUplift = 0;
        $validUpliftResponses = 0;

        foreach ($countedResponses as $resp) {
            $answers = is_array($resp) ? ($resp['answers'] ?? []) : ($resp->answers ?? []);

            // 1. Recall Kencana (Instrumen RECALL_V2)
            if (!empty($answers['brand_recalled_kencana'])) {
                $kencanaRecallCount++;
            }

            // 2. Relevansi audiens
            if (!empty($answers['audience_relevance'])) {
                $relevantAudienceCount++;
            }

            // 3. Uplift retrospektif (sesudah - sebelum)
            if (isset($answers['awareness_after']) && isset($answers['awareness_before'])) {
                $uplift = (int) $answers['awareness_after'] - (int) $answers['awareness_before'];
                $totalUplift += $uplift;
                $validUpliftResponses++;
            }
        }

        $recallPct = $surveyCount > 0 ? round(($kencanaRecallCount / $surveyCount) * 100, 2) : 0.0;
        $audienceRelevancePct = $surveyCount > 0 ? round(($relevantAudienceCount / $surveyCount) * 100, 2) : 0.0;
        $avgUplift = $validUpliftResponses > 0 ? round($totalUplift / $validUpliftResponses, 2) : 0.0;

        // Syarat lolos Awareness:
        // 1. Jumlah survei memadai
        // 2. Uplift >= 0.5 poin
        // 3. Recall merek Kencana >= 50%
        // 4. Relevansi audiens >= 50%
        $passed = ($isSurveySufficient && $avgUplift >= 0.5 && $recallPct >= 50.0 && $audienceRelevancePct >= 50.0);

        return [
            'goal' => 'awareness',
            'survey_count' => $surveyCount,
            'min_survey_required' => $minSurveyRequired,
            'is_survey_sufficient' => $isSurveySufficient,
            'recall_count' => $kencanaRecallCount,
            'recall_pct' => $recallPct,
            'threshold_recall_pct' => 50.0,
            'avg_uplift' => $avgUplift,
            'threshold_uplift' => 0.5,
            'audience_relevance_pct' => $audienceRelevancePct,
            'threshold_relevance_pct' => 50.0,
            'passed' => $passed,
            'status' => $passed ? 'lolos' : (!$isSurveySufficient ? 'data_belum_cukup' : 'tidak_lolos'),
            'message' => $passed
                ? "Lolos: Uplift awareness {$avgUplift} poin (>= 0.5), brand recall {$recallPct}% (>= 50%), dan relevansi audiens {$audienceRelevancePct}% (>= 50%)."
                : "Tidak memenuhi syarat: Uplift {$avgUplift} poin, recall {$recallPct}%, relevansi {$audienceRelevancePct}%, survei: {$surveyCount}/{$minSurveyRequired}.",
        ];
    }
}
