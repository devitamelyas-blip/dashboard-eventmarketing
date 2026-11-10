<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  event: {
    type: Object,
    required: true,
  },
  evaluation: {
    type: Object,
    required: true,
  },
});

function formatRupiah(val) {
  if (val === null || val === undefined) return 'Belum dilaporkan';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val);
}

function printPage() {
  window.print();
}
</script>

<template>
  <Head :title="`Cetak Evaluasi - ${event.code}`" />

  <div class="min-h-screen bg-slate-100 p-4 md:p-6 print:bg-white print:p-0 text-slate-800 font-sans">
    <!-- Non-print action bar -->
    <div class="no-print max-w-5xl mx-auto mb-4 flex items-center justify-between bg-white p-4 rounded-lg shadow-sm border border-slate-200">
      <div class="flex items-center space-x-3">
        <a
          href="/dashboard"
          class="text-sm font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded transition"
        >
          &larr; Kembali ke Dashboard
        </a>
        <span class="text-xs text-slate-400">Layout disesuaikan untuk A4 Landscape (1 Halaman)</span>
      </div>
      <button
        @click="printPage"
        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded shadow transition"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Cetak / Simpan PDF
      </button>
    </div>

    <!-- Print Canvas -->
    <div class="max-w-5xl mx-auto bg-white p-6 print:p-2 border border-slate-200 print:border-0 rounded-lg shadow print:shadow-none print:max-w-none print:w-full">
      <!-- Header -->
      <div class="border-b-2 border-slate-900 pb-3 mb-4 flex justify-between items-start">
        <div class="flex items-center gap-3">
          <img src="/images/kencana-logo.png" alt="Kencana" class="h-10 w-auto object-contain" />
          <div>
            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">PT Kencana Maju Bersama</div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Dashboard Event Marketing — Laporan Evaluasi Event</h1>
            <div class="text-xs text-slate-600 mt-0.5">
              Dicetak: {{ new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
            </div>
          </div>
        </div>
        <div class="text-right">
          <div class="inline-block px-2.5 py-1 rounded text-xs font-bold border"
               :class="event.verification_status === 'terverifikasi' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-amber-50 text-amber-800 border-amber-300'">
            {{ event.verification_status_label }}
          </div>
          <div class="text-sm font-mono font-bold text-slate-900 mt-1">{{ event.code }}</div>
        </div>
      </div>

      <!-- Event Identity Grid -->
      <div class="grid grid-cols-4 gap-2 bg-slate-50 p-3 rounded text-xs border border-slate-200 mb-4">
        <div>
          <span class="text-slate-500 block">Nama Event</span>
          <span class="font-bold text-slate-900">{{ event.name }}</span>
        </div>
        <div>
          <span class="text-slate-500 block">Tanggal / Cabang</span>
          <span class="font-semibold text-slate-900">{{ event.event_date_formatted }} • {{ event.branch_name }}</span>
        </div>
        <div>
          <span class="text-slate-500 block">Tujuan Utama</span>
          <span class="font-bold text-indigo-700 uppercase">{{ event.goal_label }}</span>
        </div>
        <div>
          <span class="text-slate-500 block">PIC / Lokasi</span>
          <span class="font-semibold text-slate-900">{{ event.pic_name }} ({{ event.location }})</span>
        </div>
      </div>

      <!-- Warning if data changed after decision -->
      <div v-if="event.decision_stale_flag" class="mb-3 p-2 bg-amber-50 border border-amber-300 rounded text-xs text-amber-900 font-semibold flex items-center">
        ⚠️ PERINGATAN: Data laporan pasca-event diperbarui PIC setelah keputusan Direksi tercatat.
      </div>

      <!-- Main Body: 2 Columns Layout -->
      <div class="grid grid-cols-2 gap-4">
        <!-- Col Left: Biaya & Realisasi -->
        <div class="space-y-4">
          <!-- Biaya -->
          <div class="border border-slate-200 rounded p-3">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-2 border-b border-slate-100 pb-1">
              1. Evaluasi Biaya (Rupiah)
            </h3>
            <table class="w-full text-xs">
              <tbody class="divide-y divide-slate-100">
                <tr>
                  <td class="py-1 text-slate-600">Biaya Proposal (DP/Sewa):</td>
                  <td class="py-1 text-right font-mono">{{ formatRupiah(evaluation.cost.proposal_cost) }}</td>
                </tr>
                <tr>
                  <td class="py-1 text-slate-600">Budget Tim Cabang:</td>
                  <td class="py-1 text-right font-mono">{{ formatRupiah(evaluation.cost.team_budget) }}</td>
                </tr>
                <tr class="font-semibold bg-slate-50">
                  <td class="py-1 text-slate-800">Total Rencana Budget:</td>
                  <td class="py-1 text-right font-mono text-slate-900">{{ formatRupiah(evaluation.cost.total_budget) }}</td>
                </tr>
                <tr>
                  <td class="py-1 text-slate-600">Realisasi Biaya Tim:</td>
                  <td class="py-1 text-right font-mono">
                    {{ evaluation.cost.actual_team_cost !== null ? formatRupiah(evaluation.cost.actual_team_cost) : 'Belum dilaporkan' }}
                  </td>
                </tr>
                <tr class="font-semibold bg-slate-50">
                  <td class="py-1 text-slate-800">Total Biaya Aktual:</td>
                  <td class="py-1 text-right font-mono">
                    {{ evaluation.cost.total_actual !== null ? formatRupiah(evaluation.cost.total_actual) : 'Belum final' }}
                  </td>
                </tr>
                <tr class="border-t-2 border-slate-300 font-bold">
                  <td class="py-1 text-slate-900">Selisih Budget (Gap):</td>
                  <td class="py-1 text-right font-mono"
                      :class="evaluation.cost.budget_gap !== null && evaluation.cost.budget_gap >= 0 ? 'text-emerald-700' : 'text-red-700'">
                    {{ evaluation.cost.budget_gap !== null ? formatRupiah(evaluation.cost.budget_gap) : '-' }}
                  </td>
                </tr>
                <tr>
                  <td class="py-1 text-slate-600">Varians Pengeluaran:</td>
                  <td class="py-1 text-right font-mono font-bold"
                      :class="evaluation.cost.budget_variance_pct !== null && evaluation.cost.budget_variance_pct > 0 ? 'text-red-700' : 'text-emerald-700'">
                    {{ evaluation.cost.budget_variance_pct !== null ? (evaluation.cost.budget_variance_pct > 0 ? '+' : '') + evaluation.cost.budget_variance_pct + '%' : '-' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Goal Evaluation -->
          <div class="border border-slate-200 rounded p-3">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-2 border-b border-slate-100 pb-1">
              2. Pencapaian Target & KPI ({{ event.goal_label }})
            </h3>

            <!-- Sales -->
            <div v-if="evaluation.goal_result.goal === 'sales'" class="space-y-1.5 text-xs">
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Status Tahap H+90:</span>
                <span class="font-bold" :class="evaluation.goal_result.h90_eligible ? 'text-emerald-700' : 'text-amber-700'">
                  {{ evaluation.goal_result.h90_eligible ? 'Jatuh tempo terlewati' : 'Belum H+90 (' + evaluation.goal_result.days_since_event + '/90 hari)' }}
                </span>
              </div>
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Omzet Aktual H+90:</span>
                <span class="font-mono font-bold text-slate-900">{{ formatRupiah(evaluation.goal_result.revenue) }}</span>
              </div>
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Target Rasio Sales / Proposal:</span>
                <span class="font-mono">Min. 10.0x</span>
              </div>
              <div class="flex justify-between py-0.5 font-bold">
                <span class="text-slate-800">Rasio Tercapai:</span>
                <span class="font-mono" :class="evaluation.goal_result.ratio >= 10 ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.ratio !== null ? evaluation.goal_result.ratio + 'x' : '-' }}
                </span>
              </div>
            </div>

            <!-- Awareness -->
            <div v-else-if="evaluation.goal_result.goal === 'awareness'" class="space-y-1.5 text-xs">
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Kecukupan Survei Peserta:</span>
                <span class="font-bold" :class="evaluation.goal_result.survey_sufficient ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.counted_surveys }} / min. {{ evaluation.goal_result.survey_threshold }}
                </span>
              </div>
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Awareness Uplift (Skala 1-5):</span>
                <span class="font-mono font-bold" :class="evaluation.goal_result.uplift >= 0.5 ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.uplift !== null ? '+' + evaluation.goal_result.uplift : '-' }} (min. +0.50)
                </span>
              </div>
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Brand Recall Tanpa Bantuan:</span>
                <span class="font-mono font-bold" :class="evaluation.goal_result.brand_recall_pct >= 50 ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.brand_recall_pct !== null ? evaluation.goal_result.brand_recall_pct + '%' : '-' }} (min. 50%)
                </span>
              </div>
              <div class="flex justify-between py-0.5">
                <span class="text-slate-600">Relevansi Audiens:</span>
                <span class="font-mono font-bold text-slate-900">
                  {{ evaluation.goal_result.relevance_pct !== null ? evaluation.goal_result.relevance_pct + '%' : '-' }} (min. 50%)
                </span>
              </div>
            </div>

            <!-- Relationship -->
            <div v-else-if="evaluation.goal_result.goal === 'relationship'" class="space-y-1.5 text-xs">
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Responden Partner/Customer:</span>
                <span class="font-mono font-bold" :class="evaluation.goal_result.partner_count >= 5 ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.partner_count }} responden (min. 5)
                </span>
              </div>
              <div class="flex justify-between py-0.5 border-b border-slate-100">
                <span class="text-slate-600">Rata-rata Skor Hubungan:</span>
                <span class="font-mono font-bold" :class="evaluation.goal_result.avg_relationship_score >= 4 ? 'text-emerald-700' : 'text-red-700'">
                  {{ evaluation.goal_result.avg_relationship_score !== null ? evaluation.goal_result.avg_relationship_score + ' / 5.0' : '-' }} (min. 4.0)
                </span>
              </div>
              <div class="flex justify-between py-0.5">
                <span class="text-slate-600">Relevansi Audiens:</span>
                <span class="font-mono font-bold text-slate-900">
                  {{ evaluation.goal_result.relevance_pct !== null ? evaluation.goal_result.relevance_pct + '%' : '-' }} (min. 50%)
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Col Right: Rekomendasi & Keputusan Direksi -->
        <div class="space-y-4">
          <!-- Rekomendasi Otomatis -->
          <div class="border border-slate-200 rounded p-3">
            <h3 class="text-xs font-black uppercase text-slate-700 tracking-wider mb-2 border-b border-slate-100 pb-1">
              3. Rekomendasi Sistem Otomatis
            </h3>
            <div class="mb-2">
              <span class="inline-block px-3 py-1 text-xs font-black rounded uppercase tracking-wider"
                    :class="{
                      'bg-emerald-100 text-emerald-800 border border-emerald-300': evaluation.recommendation.status === 'layak',
                      'bg-amber-100 text-amber-800 border border-amber-300': evaluation.recommendation.status === 'perlu_evaluasi',
                      'bg-red-100 text-red-800 border border-red-300': evaluation.recommendation.status === 'tidak_layak',
                      'bg-slate-100 text-slate-800 border border-slate-300': evaluation.recommendation.status === 'belum_final'
                    }">
                {{ evaluation.recommendation.label }}
              </span>
            </div>
            <p class="text-xs text-slate-700 leading-relaxed mb-2 font-medium">
              {{ evaluation.recommendation.summary }}
            </p>
            <div v-if="evaluation.recommendation.action_checklist?.length" class="bg-slate-50 p-2 rounded text-xs border border-slate-200">
              <span class="font-bold text-slate-700 block mb-1">Checklist Tindak Lanjut:</span>
              <ul class="list-disc list-inside space-y-0.5 text-slate-600">
                <li v-for="(item, idx) in evaluation.recommendation.action_checklist" :key="idx">
                  {{ item }}
                </li>
              </ul>
            </div>
          </div>

          <!-- Keputusan Akhir Direksi -->
          <div class="border-2 border-slate-800 rounded p-3 bg-slate-50">
            <h3 class="text-xs font-black uppercase text-slate-900 tracking-wider mb-2 border-b border-slate-200 pb-1">
              4. Keputusan Akhir Direksi (Resmi)
            </h3>
            <div class="mb-2">
              <span class="text-xs text-slate-500 block">Status Keputusan:</span>
              <span class="text-sm font-black"
                    :class="{
                      'text-emerald-700': event.director_decision === 'layak',
                      'text-amber-700': event.director_decision === 'perlu_evaluasi',
                      'text-red-700': event.director_decision === 'tidak_layak',
                      'text-slate-500 italic': !event.director_decision
                    }">
                {{ event.director_decision ? event.director_decision.toUpperCase() : 'Belum Ditentukan oleh Direksi' }}
              </span>
            </div>
            <div>
              <span class="text-xs text-slate-500 block">Catatan / Arahan Direksi:</span>
              <p class="text-xs text-slate-800 whitespace-pre-wrap font-sans mt-0.5 min-h-[40px] bg-white p-2 rounded border border-slate-200">
                {{ event.director_note || 'Tidak ada catatan tambahan.' }}
              </p>
            </div>

            <!-- Signature block -->
            <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-300 text-center text-xs">
              <div>
                <div class="text-slate-500 mb-8">Diajukan & Dilaporkan Oleh,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-0.5 inline-block min-w-[120px]">
                  {{ event.pic_name }}
                </div>
                <div class="text-slate-500 text-[10px] mt-0.5">PIC / Kepala Cabang</div>
              </div>
              <div>
                <div class="text-slate-500 mb-8">Disetujui & Ditetapkan Oleh,</div>
                <div class="font-bold text-slate-900 border-b border-slate-400 pb-0.5 inline-block min-w-[120px]">
                  Direksi KENCANA
                </div>
                <div class="text-slate-500 text-[10px] mt-0.5">PT Kencana Maju Bersama</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  @page {
    size: A4 landscape;
    margin: 8mm;
  }
  body {
    background: white !important;
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }
  .no-print {
    display: none !important;
  }
}
</style>
