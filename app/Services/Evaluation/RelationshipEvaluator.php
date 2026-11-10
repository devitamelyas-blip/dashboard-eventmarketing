<?php

namespace App\Services\Evaluation;

class RelationshipEvaluator
{
    /**
     * Mengevaluasi tujuan RELATIONSHIP sebagai fungsi murni.
     *
     * @param int $surveyCount Jumlah survei valid (counted=true)
     * @param int|null $minSurveyRequired Ambang minimum survei
     * @param iterable $countedResponses Kumpulan respons survei counted=true
     * @return array
     */
    public static function evaluate(int $surveyCount, ?int $minSurveyRequired, iterable $countedResponses): array
    {
        $isSurveySufficient = ($minSurveyRequired !== null && $minSurveyRequired > 0 && $surveyCount >= $minSurveyRequired);

        $partnerCount = 0;
        $relevantAudienceCount = 0;
        $totalScore = 0;
        $validScoreResponses = 0;

        foreach ($countedResponses as $resp) {
            $answers = is_array($resp) ? ($resp['answers'] ?? []) : ($resp->answers ?? []);

            // 1. Kategori partner/customer saat ini
            if (!empty($answers['is_customer_or_partner'])) {
                $partnerCount++;
            }

            // 2. Relevansi audiens
            if (!empty($answers['audience_relevance'])) {
                $relevantAudienceCount++;
            }

            // 3. Skor hubungan kemitraan (1-5)
            if (isset($answers['relationship_score'])) {
                $totalScore += (int) $answers['relationship_score'];
                $validScoreResponses++;
            }
        }

        $audienceRelevancePct = $surveyCount > 0 ? round(($relevantAudienceCount / $surveyCount) * 100, 2) : 0.0;
        $avgScore = $validScoreResponses > 0 ? round($totalScore / $validScoreResponses, 2) : 0.0;

        // Syarat lolos Relationship:
        // 1. Jumlah survei memadai
        // 2. Partner/customer saat ini >= 5 orang
        // 3. Skor hubungan rata-rata >= 4.0 (skala 5)
        // 4. Relevansi audiens >= 50%
        $passed = ($isSurveySufficient && $partnerCount >= 5 && $avgScore >= 4.0 && $audienceRelevancePct >= 50.0);

        return [
            'goal' => 'relationship',
            'survey_count' => $surveyCount,
            'min_survey_required' => $minSurveyRequired,
            'is_survey_sufficient' => $isSurveySufficient,
            'partner_count' => $partnerCount,
            'threshold_partner_count' => 5,
            'avg_relationship_score' => $avgScore,
            'threshold_relationship_score' => 4.0,
            'audience_relevance_pct' => $audienceRelevancePct,
            'threshold_relevance_pct' => 50.0,
            'passed' => $passed,
            'status' => $passed ? 'lolos' : (!$isSurveySufficient ? 'data_belum_cukup' : 'tidak_lolos'),
            'message' => $passed
                ? "Lolos: Skor hubungan kemitraan rata-rata {$avgScore}/5.0 (>= 4.0), mitra/customer aktif {$partnerCount} orang (>= 5), relevansi audiens {$audienceRelevancePct}% (>= 50%)."
                : "Tidak memenuhi syarat: Skor hubungan {$avgScore}/5.0, mitra aktif {$partnerCount}/5, relevansi {$audienceRelevancePct}%, survei: {$surveyCount}/{$minSurveyRequired}.",
        ];
    }
}
