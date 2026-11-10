<?php

namespace App\Services\Evaluation;

class CostEvaluator
{
    /**
     * Mengevaluasi aspek anggaran dan biaya event sebagai fungsi murni.
     *
     * @param float|null $proposalCost Biaya proposal pengajuan (harus tetap)
     * @param float|null $teamBudget Anggaran operasional tim cabang yang direncanakan
     * @param float|null $actualTeamCost Realisasi biaya operasional tim cabang (NULL jika belum dilaporkan)
     * @return array
     */
    public static function evaluate(?float $proposalCost, ?float $teamBudget, ?float $actualTeamCost): array
    {
        // 1. Total Budget Rencana
        $totalBudget = null;
        if ($proposalCost !== null && $teamBudget !== null) {
            $totalBudget = round($proposalCost + $teamBudget, 2);
        }

        // 2. Total Biaya Aktual
        $totalActual = null;
        if ($proposalCost !== null && $actualTeamCost !== null) {
            $totalActual = round($proposalCost + $actualTeamCost, 2);
        }

        // 3. Selisih Anggaran (Budget Gap = Budget - Aktual, positif = sisa/hemat)
        $budgetGap = null;
        if ($totalBudget !== null && $totalActual !== null) {
            $budgetGap = round($totalBudget - $totalActual, 2);
        }

        // 4. Budget Variance = (total_actual - total_budget) / total_budget
        // Pembagian dengan nol tidak boleh menghasilkan rasio; kembalikan null
        $budgetVariance = null;
        if ($totalBudget !== null && $totalActual !== null && $totalBudget > 0) {
            $budgetVariance = round(($totalActual - $totalBudget) / $totalBudget, 4);
        }

        // 5. Status Anggaran
        $status = 'belum_dapat_dinilai';
        $statusLabel = 'Belum Dapat Dinilai';

        if ($totalActual !== null && $totalBudget !== null) {
            if ($totalActual <= $totalBudget) {
                $status = 'hemat_atau_sesuai';
                $statusLabel = 'Sesuai / Hemat Anggaran';
            } else {
                $status = 'over_budget';
                $statusLabel = 'Melebihi Anggaran';
            }
        }

        return [
            'proposal_cost' => $proposalCost,
            'team_budget' => $teamBudget,
            'actual_team_cost' => $actualTeamCost,
            'total_budget' => $totalBudget,
            'total_actual' => $totalActual,
            'budget_gap' => $budgetGap,
            'budget_variance' => $budgetVariance,
            'status' => $status,
            'status_label' => $statusLabel,
            'is_complete' => ($totalActual !== null && $totalBudget !== null),
        ];
    }
}
