<?php

namespace App\Services\Evaluation;

use Carbon\CarbonInterface;

class SalesEvaluator
{
    /**
     * Mengevaluasi tujuan SALES sebagai fungsi murni.
     *
     * @param CarbonInterface $eventDate Tanggal pelaksanaan event
     * @param float|null $proposalCost Biaya proposal
     * @param float|null $revenue Realisasi omzet (NULL jika belum dilaporkan)
     * @param bool $isVerified Apakah event/omzet telah diverifikasi oleh Marketing
     * @param CarbonInterface $asOfDate Tanggal titik evaluasi (default now())
     * @param float|null $audienceRelevancePct Persentase relevansi audiens dari survei
     * @return array
     */
    public static function evaluate(
        CarbonInterface $eventDate,
        ?float $proposalCost,
        ?float $revenue,
        bool $isVerified,
        CarbonInterface $asOfDate,
        ?float $audienceRelevancePct = null
    ): array {
        $h90Date = $eventDate->copy()->addDays(90)->startOfDay();
        $isH90Reached = $asOfDate->startOfDay()->gte($h90Date);
        $daysRemaining = max(0, (int) $asOfDate->startOfDay()->diffInDays($h90Date, false));

        // 1. Cek apakah sudah jatuh tempo H+90
        if (!$isH90Reached) {
            return [
                'goal' => 'sales',
                'is_due' => false,
                'days_remaining' => $daysRemaining,
                'due_date' => $h90Date->format('Y-m-d'),
                'revenue' => $revenue,
                'verified_revenue' => null,
                'proposal_cost' => $proposalCost,
                'ratio' => null,
                'passed' => false,
                'status' => 'belum_jatuh_tempo',
                'message' => "Penilaian Sales belum jatuh tempo H+90 (tersisa {$daysRemaining} hari hingga {$h90Date->format('d/m/Y')}).",
            ];
        }

        // 2. Cek apakah omzet sudah dilaporkan
        if ($revenue === null) {
            return [
                'goal' => 'sales',
                'is_due' => true,
                'days_remaining' => 0,
                'due_date' => $h90Date->format('Y-m-d'),
                'revenue' => null,
                'verified_revenue' => null,
                'proposal_cost' => $proposalCost,
                'ratio' => null,
                'passed' => false,
                'status' => 'menunggu_laporan',
                'message' => 'Jatuh tempo H+90 telah terlewati, namun omzet aktual belum dilaporkan oleh PIC cabang.',
            ];
        }

        // 3. Cek apakah omzet terverifikasi oleh Marketing
        if (!$isVerified) {
            return [
                'goal' => 'sales',
                'is_due' => true,
                'days_remaining' => 0,
                'due_date' => $h90Date->format('Y-m-d'),
                'revenue' => $revenue,
                'verified_revenue' => null,
                'proposal_cost' => $proposalCost,
                'ratio' => null,
                'passed' => false,
                'status' => 'menunggu_verifikasi',
                'message' => 'Omzet telah dilaporkan namun belum diverifikasi oleh Tim Marketing (omzet belum terverifikasi belum sah).',
            ];
        }

        // 4. Hitung Rasio Omzet / Proposal Cost
        // Pembagian dengan nol tidak boleh menghasilkan rasio; kembalikan null
        $ratio = null;
        if ($proposalCost !== null && $proposalCost > 0) {
            $ratio = round($revenue / $proposalCost, 2);
        }

        // Syarat lolos Sales: Rasio >= 10x dan proposal_cost > 0
        $passed = ($ratio !== null && $ratio >= 10.0);

        return [
            'goal' => 'sales',
            'is_due' => true,
            'days_remaining' => 0,
            'due_date' => $h90Date->format('Y-m-d'),
            'revenue' => $revenue,
            'verified_revenue' => $revenue,
            'proposal_cost' => $proposalCost,
            'ratio' => $ratio,
            'threshold_ratio' => 10.0,
            'passed' => $passed,
            'status' => $passed ? 'lolos' : 'tidak_lolos',
            'audience_relevance_pct' => $audienceRelevancePct,
            'message' => $passed
                ? "Lolos: Rasio omzet tercatat {$ratio}x dari biaya proposal (ambang batas: >= 10.0x)."
                : "Tidak memenuhi syarat: Rasio omzet tercatat {$ratio}x dari biaya proposal (di bawah ambang batas 10.0x).",
        ];
    }
}
