<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kpis: { type: Object, default: () => ({}) },
    analytics: { type: Object, default: () => ({}) },
    tableEvents: { type: Array, default: () => [] },
    selectedPortfolio: String,
    eventOptions: Array,
    selectedEventId: [Number, String],
    selectedEvent: Object,
    evaluation: Object,
});

const searchEvent = ref('');
const isEventDropdownOpen = ref(false);

const decisionForm = useForm({
    director_decision: props.selectedEvent?.director_decision || '',
    director_note: props.selectedEvent?.director_note || '',
});

const verificationForm = useForm({
    verification_status: props.selectedEvent?.verification_status || 'belum_diperiksa',
    verification_note: props.selectedEvent?.verification_note || '',
});

watch(() => props.selectedEvent, (newEvt) => {
    if (newEvt) {
        decisionForm.director_decision = newEvt.director_decision || '';
        decisionForm.director_note = newEvt.director_note || '';
        verificationForm.verification_status = newEvt.verification_status || 'belum_diperiksa';
        verificationForm.verification_note = newEvt.verification_note || '';
    }
}, { immediate: true });

const submitDecision = () => {
    if (!props.selectedEvent) return;
    decisionForm.post(route('events.decision', props.selectedEvent.id), { preserveScroll: true });
};

const submitVerification = () => {
    if (!props.selectedEvent) return;
    verificationForm.post(route('events.verify', props.selectedEvent.id), { preserveScroll: true });
};

const filteredEventOptions = computed(() => {
    if (!searchEvent.value.trim()) return props.eventOptions || [];
    const q = searchEvent.value.toLowerCase();
    return (props.eventOptions || []).filter(e =>
        e.code.toLowerCase().includes(q) || e.name.toLowerCase().includes(q)
    );
});

const selectEvent = (eventId) => {
    isEventDropdownOpen.value = false;
    router.get(route('dashboard'), { portfolio: props.selectedPortfolio, event_id: eventId }, {
        preserveState: true, preserveScroll: true,
    });
};

const changePortfolio = (portfolio) => {
    router.get(route('dashboard'), { portfolio }, { preserveState: true, preserveScroll: false });
};

const formatRupiah = (val) => {
    if (val === null || val === undefined) return 'Belum dilaporkan';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val);
};

const formatRupiahShort = (val) => {
    if (val === null || val === undefined) return '-';
    const num = Number(val);
    if (num >= 1_000_000_000) return 'Rp ' + (num / 1_000_000_000).toFixed(1) + 'M';
    if (num >= 1_000_000) return 'Rp ' + (num / 1_000_000).toFixed(0) + 'jt';
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
};

// Analytics Computations
const totalEvents = computed(() => props.kpis?.total_events || (props.eventOptions?.length ?? 0));
const totalSales = computed(() => props.analytics?.goal_distribution?.sales ?? 0);
const totalAwareness = computed(() => props.analytics?.goal_distribution?.awareness ?? 0);
const totalRelationship = computed(() => props.analytics?.goal_distribution?.relationship ?? 0);
const salesPct = computed(() => totalEvents.value > 0 ? Math.round((totalSales.value / totalEvents.value) * 100) : 0);
const awarenessPct = computed(() => totalEvents.value > 0 ? Math.round((totalAwareness.value / totalEvents.value) * 100) : 0);
const relationshipPct = computed(() => totalEvents.value > 0 ? Math.round((totalRelationship.value / totalEvents.value) * 100) : 0);

const totalBudgetVal = computed(() => props.kpis?.total_budget ?? 0);
const verifiedCostVal = computed(() => props.kpis?.verified_actual_cost ?? 0);
const budgetRealizationPct = computed(() => {
    if (totalBudgetVal.value <= 0) return 0;
    return Math.min(Math.round((verifiedCostVal.value / totalBudgetVal.value) * 100), 100);
});

// Branch Performance Analytics
const branchPerformance = computed(() => props.analytics?.branch_performance || []);
const maxBranchBudget = computed(() => {
    if (!branchPerformance.value.length) return 1;
    return Math.max(
        ...branchPerformance.value.map(b => Math.max(Number(b.plan_budget) || 0, Number(b.actual_cost) || 0)),
        1
    );
});

const portfolioOptions = [
    { value: 'operational', label: 'Operasional' },
    { value: 'demo', label: 'Demo' },
    { value: 'uat', label: 'UAT' },
    { value: 'all', label: 'Semua' },
];
</script>

<template>
    <Head title="Event Performance Overview" />

    <AuthenticatedLayout>
        <div class="space-y-5">

            <!-- ═══════════════════════════════════════════════
                 A. PAGE HEADER DENGAN FILTER PORTOFOLIO
            ═══════════════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Left: Title -->
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="w-1 h-5 rounded-full bg-red-600 inline-block"></span>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Executive Dashboard</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Event Performance Overview</h1>
                    <p class="text-sm text-slate-500 mt-0.5">PT Kencana Maju Bersama · Monitoring & Evaluasi Event Multi-Cabang</p>
                </div>

                <!-- Right: Controls -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Portfolio Tabs -->
                    <div class="flex items-center bg-white border border-slate-200 rounded-lg p-1 shadow-sm text-xs font-semibold gap-0.5">
                        <button
                            v-for="opt in portfolioOptions"
                            :key="opt.value"
                            type="button"
                            @click="changePortfolio(opt.value)"
                            class="px-3 py-1.5 rounded-md transition-all"
                            :class="selectedPortfolio === opt.value
                                ? 'bg-slate-900 text-white shadow-sm'
                                : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
                        >
                            {{ opt.label }}
                        </button>
                    </div>

                    <!-- Print Button -->
                    <a
                        v-if="selectedEvent"
                        :href="route('dashboard.print', selectedEvent.id)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak A4
                    </a>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════
                 B. KPI METRIC CARDS (6 CARDS)
            ═══════════════════════════════════════════════ -->
            <div id="budget-monitoring" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 scroll-mt-20">
                <!-- Card 1: Total Event -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Event</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 leading-none">{{ totalEvents }}</div>
                    <div class="text-[11px] text-slate-400 mt-1.5 capitalize">Portofolio {{ selectedPortfolio }}</div>
                </div>

                <!-- Card 2: Terverifikasi -->
                <div class="bg-white rounded-xl border border-emerald-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Terverifikasi</span>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-3xl font-black text-emerald-700 leading-none">{{ kpis.verified_events_count ?? 0 }}</div>
                    <div class="text-[11px] text-emerald-600 mt-1.5 font-medium">Siap dievaluasi</div>
                </div>

                <!-- Card 3: Menunggu Verifikasi -->
                <div class="bg-white rounded-xl border border-amber-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu</span>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-3xl font-black text-amber-700 leading-none">{{ kpis.pending_verification_count ?? 0 }}</div>
                    <div class="text-[11px] text-amber-600 mt-1.5 font-medium">Perlu ditinjau</div>
                </div>

                <!-- Card 4: Perlu Koreksi -->
                <div class="bg-white rounded-xl border border-red-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Perlu Koreksi</span>
                        <span class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-3xl font-black text-red-700 leading-none">{{ kpis.correction_needed_count ?? 0 }}</div>
                    <div class="text-[11px] text-red-500 mt-1.5 font-medium">Butuh perbaikan</div>
                </div>

                <!-- Card 5: Total Budget -->
                <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rencana Budget</span>
                        <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-lg font-black text-slate-900 leading-tight font-mono" :title="formatRupiah(kpis.total_budget ?? 0)">
                        {{ formatRupiahShort(kpis.total_budget ?? 0) }}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1.5">Proposal + Tim Cabang</div>
                </div>

                <!-- Card 6: Omzet Terverifikasi -->
                <div class="bg-white rounded-xl border border-indigo-100 p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Omzet H+90</span>
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </span>
                    </div>
                    <div class="text-lg font-black text-indigo-700 leading-tight font-mono" :title="formatRupiah(kpis.verified_revenue ?? 0)">
                        {{ formatRupiahShort(kpis.verified_revenue ?? 0) }}
                    </div>
                    <div class="text-[11px] text-indigo-500 mt-1.5 font-medium">Invoice terverifikasi</div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════
                 C. ANALYTICS SECTION (3 KOLOM)
            ═══════════════════════════════════════════════ -->
            <div id="analytics-section" class="grid grid-cols-1 md:grid-cols-3 gap-4 scroll-mt-20">

                <!-- C1: Distribusi Tujuan Event -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Distribusi Tujuan</h3>
                        <span class="text-[11px] text-slate-400 font-mono">{{ totalEvents }} total</span>
                    </div>

                    <!-- Stacked bar -->
                    <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden flex mb-4">
                        <div :style="`width: ${salesPct}%`" class="bg-emerald-500 transition-all duration-500"></div>
                        <div :style="`width: ${awarenessPct}%`" class="bg-sky-400 transition-all duration-500"></div>
                        <div :style="`width: ${relationshipPct}%`" class="bg-indigo-500 transition-all duration-500"></div>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span>
                                <span class="text-slate-600 font-medium">Sales</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-900 font-bold">{{ totalSales }}</span>
                                <span class="text-slate-400 text-[10px]">{{ salesPct }}%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-sm bg-sky-400"></span>
                                <span class="text-slate-600 font-medium">Awareness</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-900 font-bold">{{ totalAwareness }}</span>
                                <span class="text-slate-400 text-[10px]">{{ awarenessPct }}%</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-sm bg-indigo-500"></span>
                                <span class="text-slate-600 font-medium">Relationship</span>
                            </div>
                            <div class="flex items-center gap-2 font-mono">
                                <span class="text-slate-900 font-bold">{{ totalRelationship }}</span>
                                <span class="text-slate-400 text-[10px]">{{ relationshipPct }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- C2: Realisasi Anggaran -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Realisasi Anggaran</h3>
                        <span
                            class="text-xs font-black px-2 py-0.5 rounded-full"
                            :class="budgetRealizationPct > 100 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700'"
                        >
                            {{ budgetRealizationPct }}%
                        </span>
                    </div>

                    <!-- Gauge-style bar -->
                    <div class="relative h-3 w-full bg-slate-100 rounded-full overflow-hidden mb-4">
                        <div
                            :style="`width: ${budgetRealizationPct}%`"
                            class="h-full rounded-full transition-all duration-700"
                            :class="budgetRealizationPct > 100 ? 'bg-red-500' : budgetRealizationPct > 80 ? 'bg-amber-500' : 'bg-emerald-500'"
                        ></div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2.5 bg-slate-50 rounded-lg">
                            <span class="text-slate-500 font-medium">Rencana Total</span>
                            <span class="font-mono font-bold text-slate-900">{{ formatRupiahShort(kpis.total_budget ?? 0) }}</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 rounded-lg" :class="budgetRealizationPct > 100 ? 'bg-red-50' : 'bg-emerald-50'">
                            <span class="text-slate-500 font-medium">Realisasi Aktual</span>
                            <span class="font-mono font-bold" :class="budgetRealizationPct > 100 ? 'text-red-700' : 'text-emerald-700'">
                                {{ formatRupiahShort(kpis.verified_actual_cost ?? 0) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- C3: Status Verifikasi -->
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Status Verifikasi</h3>
                        <span class="text-[11px] text-slate-400">Marketing</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Terverifikasi -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-600 font-medium">Terverifikasi</span>
                                <span class="font-bold text-emerald-700">{{ kpis.verified_events_count ?? 0 }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-emerald-500 rounded-full transition-all duration-500"
                                    :style="`width: ${totalEvents > 0 ? Math.round(((kpis.verified_events_count ?? 0) / totalEvents) * 100) : 0}%`"
                                ></div>
                            </div>
                        </div>
                        <!-- Belum Diperiksa -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-600 font-medium">Belum Diperiksa</span>
                                <span class="font-bold text-amber-700">{{ kpis.pending_verification_count ?? 0 }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-amber-400 rounded-full transition-all duration-500"
                                    :style="`width: ${totalEvents > 0 ? Math.round(((kpis.pending_verification_count ?? 0) / totalEvents) * 100) : 0}%`"
                                ></div>
                            </div>
                        </div>
                        <!-- Perlu Koreksi -->
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-slate-600 font-medium">Perlu Koreksi</span>
                                <span class="font-bold text-red-700">{{ kpis.correction_needed_count ?? 0 }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-red-500 rounded-full transition-all duration-500"
                                    :style="`width: ${totalEvents > 0 ? Math.round(((kpis.correction_needed_count ?? 0) / totalEvents) * 100) : 0}%`"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════
                 C.2 BRANCH PERFORMANCE CHART
            ═══════════════════════════════════════════════ -->
            <div v-if="branchPerformance.length > 0" class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Kinerja Anggaran Per Cabang
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Perbandingan alokasi rencana vs realisasi biaya aktual</p>
                    </div>
                    <div class="flex items-center gap-4 text-[11px] text-slate-500">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-slate-300 inline-block"></span> Rencana</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Normal</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-red-500 inline-block"></span> Over Budget</span>
                    </div>
                </div>

                <div class="space-y-5">
                    <div v-for="branch in branchPerformance" :key="branch.branch_name">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-800">{{ branch.branch_name }}</span>
                                <span class="text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded font-mono">{{ branch.total_events }} event</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] font-mono text-slate-500">
                                <span>Rencana: <strong class="text-slate-700">{{ formatRupiahShort(branch.plan_budget) }}</strong></span>
                                <span class="text-slate-200">|</span>
                                <span :class="branch.actual_cost > branch.plan_budget ? 'text-red-600 font-bold' : 'text-emerald-700 font-bold'">
                                    Aktual: {{ formatRupiahShort(branch.actual_cost) }}
                                    <span v-if="branch.plan_budget > 0" class="text-slate-400 font-normal">
                                        ({{ Math.round((branch.actual_cost / branch.plan_budget) * 100) }}%)
                                    </span>
                                </span>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="bg-slate-300 h-2 rounded-full transition-all duration-700"
                                    :style="{ width: `${Math.min(Math.round((branch.plan_budget / maxBranchBudget) * 100), 100)}%` }"
                                ></div>
                            </div>
                            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div
                                    class="h-2 rounded-full transition-all duration-700"
                                    :class="branch.actual_cost > branch.plan_budget ? 'bg-red-500' : 'bg-emerald-500'"
                                    :style="{ width: `${Math.min(Math.round((branch.actual_cost / maxBranchBudget) * 100), 100)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════
                 D. MONITORING TABLE
            ═══════════════════════════════════════════════ -->
            <div id="table-monitoring" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700">Tabel Monitoring Event Terkini</h2>
                        <p class="text-[11px] text-slate-400 mt-0.5">Ringkasan status data, verifikasi, dan rekomendasi sistem per event</p>
                    </div>
                    <Link
                        :href="route('events.index')"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 hover:text-red-600 transition shrink-0"
                    >
                        Lihat Semua Event
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="px-4 py-3 whitespace-nowrap">Kode</th>
                                <th class="px-4 py-3">Nama Event</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tanggal</th>
                                <th class="px-4 py-3 whitespace-nowrap">Cabang</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tujuan</th>
                                <th class="px-4 py-3 whitespace-nowrap">Status Data</th>
                                <th class="px-4 py-3 whitespace-nowrap">Verifikasi</th>
                                <th class="px-4 py-3 whitespace-nowrap">Rekomendasi</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="item in tableEvents"
                                :key="item.id"
                                class="hover:bg-slate-50/70 transition-colors"
                                :class="item.id === selectedEventId ? 'bg-blue-50/50' : ''"
                            >
                                <td class="px-4 py-3 font-mono font-bold text-slate-800 whitespace-nowrap text-[11px]">
                                    {{ item.code }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 max-w-[180px] truncate" :title="item.name">
                                    {{ item.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ item.event_date_formatted }}</td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap font-medium">{{ item.branch_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': item.goal === 'sales',
                                            'bg-sky-100 text-sky-800': item.goal === 'awareness',
                                            'bg-indigo-100 text-indigo-800': item.goal === 'relationship'
                                        }"
                                    >
                                        {{ item.goal_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold"
                                        :class="{
                                            'bg-green-100 text-green-700': item.data_status === 'lengkap',
                                            'bg-amber-100 text-amber-700': item.data_status === 'parsial',
                                            'bg-red-100 text-red-700': item.data_status === 'kurang_lengkap',
                                            'bg-slate-100 text-slate-600': !item.data_status
                                        }"
                                    >
                                        {{ item.data_status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': item.verification_status === 'terverifikasi',
                                            'bg-amber-100 text-amber-800': item.verification_status === 'belum_diperiksa',
                                            'bg-red-100 text-red-800': item.verification_status === 'perlu_koreksi'
                                        }"
                                    >
                                        {{ item.verification_status_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide"
                                        :class="{
                                            'bg-emerald-100 text-emerald-900': item.recommendation === 'layak',
                                            'bg-amber-100 text-amber-900': item.recommendation === 'perlu_evaluasi',
                                            'bg-red-100 text-red-900': item.recommendation === 'tidak_layak',
                                            'bg-slate-100 text-slate-600': item.recommendation === 'belum_final'
                                        }"
                                    >
                                        {{ item.recommendation_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        @click="selectEvent(item.id)"
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-lg border transition"
                                        :class="item.id === selectedEventId
                                            ? 'bg-slate-900 text-white border-slate-900'
                                            : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-900 hover:text-white hover:border-slate-900'"
                                    >
                                        Tinjau
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!tableEvents || tableEvents.length === 0">
                                <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-400">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <span>Belum ada data event pada portofolio ini.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════
                 E. EVENT REVIEW PANEL
            ═══════════════════════════════════════════════ -->
            <div id="evaluation-summary" class="scroll-mt-20">
                <!-- Panel with event selected -->
                <div v-if="selectedEvent" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">

                    <!-- Panel Header -->
                    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Lembar Tinjauan & Rekomendasi</div>
                            <h2 class="text-sm font-black text-slate-900">{{ selectedEvent.name }}</h2>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Print Button -->
                            <a
                                :href="route('dashboard.print', selectedEvent.id)"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold transition border border-slate-200 shadow-sm"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Cetak A4
                            </a>

                            <!-- Event Switcher Dropdown -->
                            <div class="relative min-w-[260px]">
                                <button
                                    type="button"
                                    @click="isEventDropdownOpen = !isEventDropdownOpen"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-xs text-left font-semibold flex items-center justify-between text-slate-700 hover:bg-slate-50 transition shadow-sm"
                                >
                                    <span class="truncate font-mono text-[11px]">{{ selectedEvent.code }} — {{ selectedEvent.name }}</span>
                                    <svg class="w-3.5 h-3.5 ml-2 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    v-if="isEventDropdownOpen"
                                    class="absolute right-0 z-20 mt-1 w-full bg-white rounded-xl shadow-xl border border-slate-200 py-1 max-h-72 overflow-y-auto"
                                >
                                    <div class="p-2 border-b border-slate-100">
                                        <TextInput v-model="searchEvent" type="text" placeholder="Cari kode / nama event..." class="w-full text-xs" @click.stop />
                                    </div>
                                    <button
                                        v-for="opt in filteredEventOptions"
                                        :key="opt.id"
                                        type="button"
                                        @click="selectEvent(opt.id)"
                                        class="w-full px-3 py-2 text-left hover:bg-slate-50 flex items-center justify-between text-xs transition"
                                        :class="opt.id === selectedEventId ? 'bg-indigo-50 text-indigo-900 font-bold' : 'text-slate-700'"
                                    >
                                        <span class="font-mono font-bold text-[11px]">{{ opt.code }}</span>
                                        <span class="truncate ml-2 text-[11px] text-slate-500">{{ opt.name }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">
                        <!-- 1. Event Identity -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide block mb-1">Tanggal & Lokasi</span>
                                <span class="text-xs font-semibold text-slate-800">{{ selectedEvent.event_date_formatted }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ selectedEvent.location }}</span>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide block mb-1">Cabang & PIC</span>
                                <span class="text-xs font-semibold text-slate-800">{{ selectedEvent.branch_name }}</span>
                                <span class="text-[11px] text-slate-500 block">{{ selectedEvent.pic_name }}</span>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide block mb-1">Tujuan Utama</span>
                                <span class="text-xs font-bold uppercase text-slate-900">{{ selectedEvent.goal_label }}</span>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wide block mb-1">Status Verifikasi</span>
                                <span
                                    class="text-xs font-bold"
                                    :class="selectedEvent.verification_status === 'terverifikasi' ? 'text-emerald-700' : selectedEvent.verification_status === 'perlu_koreksi' ? 'text-red-700' : 'text-amber-700'"
                                >
                                    {{ selectedEvent.verification_status_label }}
                                </span>
                            </div>
                        </div>

                        <!-- 2. Financial Evaluation -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">1. Evaluasi Biaya & Anggaran</h3>
                            </div>
                            <div class="p-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-center">
                                    <span class="text-[10px] text-slate-500 block mb-1">Total Rencana Budget</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm">{{ formatRupiahShort(evaluation?.cost?.total_budget) }}</span>
                                </div>
                                <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-center">
                                    <span class="text-[10px] text-slate-500 block mb-1">Total Biaya Aktual</span>
                                    <span class="font-mono font-bold text-slate-900 text-sm">{{ formatRupiahShort(evaluation?.cost?.total_actual) }}</span>
                                </div>
                                <div class="p-3 rounded-lg border text-center" :class="(evaluation?.cost?.budget_gap ?? 0) >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'">
                                    <span class="text-[10px] text-slate-500 block mb-1">Selisih Budget</span>
                                    <span class="font-mono font-bold text-sm" :class="(evaluation?.cost?.budget_gap ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700'">
                                        {{ formatRupiahShort(evaluation?.cost?.budget_gap) }}
                                    </span>
                                </div>
                                <div class="p-3 rounded-lg border text-center" :class="(evaluation?.cost?.budget_variance_pct ?? 0) > 0 ? 'bg-red-50 border-red-200' : 'bg-emerald-50 border-emerald-200'">
                                    <span class="text-[10px] text-slate-500 block mb-1">Varians Pengeluaran</span>
                                    <span class="font-mono font-bold text-sm" :class="(evaluation?.cost?.budget_variance_pct ?? 0) > 0 ? 'text-red-700' : 'text-emerald-700'">
                                        {{ evaluation?.cost?.budget_variance_pct !== null ? (evaluation?.cost?.budget_variance_pct > 0 ? '+' : '') + evaluation?.cost?.budget_variance_pct + '%' : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. KPI by Goal -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">2. Pencapaian Target: {{ selectedEvent.goal_label }}</h3>
                            </div>
                            <div class="p-4">
                                <!-- Sales Goal -->
                                <div v-if="evaluation?.goal_result?.goal === 'sales'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Status H+90</span>
                                        <span class="font-bold" :class="evaluation?.goal_result?.h90_eligible ? 'text-emerald-700' : 'text-amber-700'">
                                            {{ evaluation?.goal_result?.h90_eligible ? 'Sudah Jatuh Tempo' : 'Belum H+90 (' + evaluation?.goal_result?.days_since_event + '/90 hr)' }}
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Omzet Aktual H+90</span>
                                        <span class="font-mono font-bold text-slate-900">{{ formatRupiahShort(evaluation?.goal_result?.revenue) }}</span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Ambang Rasio</span>
                                        <span class="font-mono font-semibold">Min. 10.0×</span>
                                    </div>
                                    <div class="p-3 rounded-lg border" :class="(evaluation?.goal_result?.ratio ?? 0) >= 10 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200'">
                                        <span class="text-[10px] text-slate-500 block mb-1">Rasio Tercapai</span>
                                        <span class="font-mono font-bold" :class="(evaluation?.goal_result?.ratio ?? 0) >= 10 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.ratio !== null ? evaluation?.goal_result?.ratio + '×' : 'Belum final' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Awareness Goal -->
                                <div v-else-if="evaluation?.goal_result?.goal === 'awareness'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Kecukupan Survei</span>
                                        <span class="font-bold" :class="evaluation?.goal_result?.survey_sufficient ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.counted_surveys }} / min. {{ evaluation?.goal_result?.survey_threshold }} responden
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Awareness Uplift</span>
                                        <span class="font-mono font-bold" :class="(evaluation?.goal_result?.uplift ?? 0) >= 0.5 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.uplift !== null ? '+' + evaluation?.goal_result?.uplift : '-' }} (min. +0.50)
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Unassisted Brand Recall</span>
                                        <span class="font-mono font-bold" :class="(evaluation?.goal_result?.brand_recall_pct ?? 0) >= 50 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.brand_recall_pct !== null ? evaluation?.goal_result?.brand_recall_pct + '%' : '-' }} (min. 50%)
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Relevansi Audiens</span>
                                        <span class="font-mono font-bold text-slate-900">
                                            {{ evaluation?.goal_result?.relevance_pct !== null ? evaluation?.goal_result?.relevance_pct + '%' : '-' }} (min. 50%)
                                        </span>
                                    </div>
                                </div>

                                <!-- Relationship Goal -->
                                <div v-else-if="evaluation?.goal_result?.goal === 'relationship'" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Responden Mitra / Customer</span>
                                        <span class="font-mono font-bold" :class="(evaluation?.goal_result?.partner_count ?? 0) >= 5 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.partner_count }} mitra (min. 5)
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Rata-rata Skor Hubungan</span>
                                        <span class="font-mono font-bold" :class="(evaluation?.goal_result?.avg_relationship_score ?? 0) >= 4 ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.avg_relationship_score !== null ? evaluation?.goal_result?.avg_relationship_score + ' / 5.0' : '-' }} (min. 4.0)
                                        </span>
                                    </div>
                                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
                                        <span class="text-[10px] text-slate-500 block mb-1">Kecukupan Responden</span>
                                        <span class="font-bold" :class="evaluation?.goal_result?.survey_sufficient ? 'text-emerald-700' : 'text-red-700'">
                                            {{ evaluation?.goal_result?.survey_sufficient ? 'Memadai' : 'Belum Memadai' }}
                                        </span>
                                    </div>
                                </div>

                                <div v-else class="text-xs text-slate-400 text-center py-4">Data evaluasi KPI belum tersedia untuk event ini.</div>
                            </div>
                        </div>

                        <!-- 4. Recommendation + Decision (2 column grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Auto Recommendation -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">3. Rekomendasi Otomatis Sistem</h3>
                                </div>
                                <div class="p-4 space-y-3">
                                    <div>
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider"
                                            :class="{
                                                'bg-emerald-100 text-emerald-900': evaluation?.recommendation?.status === 'layak',
                                                'bg-amber-100 text-amber-900': evaluation?.recommendation?.status === 'perlu_evaluasi',
                                                'bg-red-100 text-red-900': evaluation?.recommendation?.status === 'tidak_layak',
                                                'bg-slate-100 text-slate-700': evaluation?.recommendation?.status === 'belum_final'
                                            }"
                                        >
                                            {{ evaluation?.recommendation?.label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-700 leading-relaxed">{{ evaluation?.recommendation?.summary }}</p>

                                    <div v-if="evaluation?.recommendation?.action_checklist?.length" class="pt-2 border-t border-slate-100">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1.5">Checklist Tindak Lanjut:</span>
                                        <ul class="text-xs text-slate-600 space-y-1.5">
                                            <li v-for="(act, idx) in evaluation.recommendation.action_checklist" :key="idx" class="flex items-start gap-1.5">
                                                <span class="text-amber-500 font-bold mt-0.5 shrink-0">•</span>
                                                <span>{{ act }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Director Decision -->
                            <div class="border border-slate-200 rounded-xl overflow-hidden">
                                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">4. Keputusan Akhir Direksi</h3>
                                    <span
                                        v-if="selectedEvent.director_decision"
                                        class="px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wide"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800': selectedEvent.director_decision === 'layak',
                                            'bg-amber-100 text-amber-800': selectedEvent.director_decision === 'perlu_evaluasi',
                                            'bg-red-100 text-red-800': selectedEvent.director_decision === 'tidak_layak',
                                        }"
                                    >
                                        {{ selectedEvent.director_decision }}
                                    </span>
                                </div>
                                <div class="p-4 space-y-3">
                                    <!-- Stale Warning -->
                                    <div v-if="selectedEvent.decision_stale_flag" class="p-3 bg-red-50 border border-red-200 rounded-lg text-xs text-red-800 font-semibold flex items-start gap-2">
                                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>Data laporan diperbarui PIC setelah keputusan dicatat. Mohon tinjau ulang keputusan Direksi.</span>
                                    </div>

                                    <!-- View Only -->
                                    <div v-if="!selectedEvent.can?.recordDecision">
                                        <div v-if="selectedEvent.director_decision" class="text-xs text-slate-700 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                            <span class="font-bold text-slate-500 block text-[10px] uppercase mb-1">Catatan Direksi:</span>
                                            <p class="italic">"{{ selectedEvent.director_note || 'Tidak ada catatan tambahan.' }}"</p>
                                        </div>
                                        <div v-else class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                                            Direksi belum mencatat keputusan untuk event ini.
                                        </div>
                                    </div>

                                    <!-- Decision Form -->
                                    <form v-else @submit.prevent="submitDecision" class="space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-2">Pilihan Keputusan:</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <label
                                                    class="flex items-center gap-1.5 p-2 rounded-lg border text-xs cursor-pointer transition text-center justify-center"
                                                    :class="decisionForm.director_decision === 'layak' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900' : 'border-slate-200 hover:bg-slate-50'"
                                                >
                                                    <input type="radio" value="layak" v-model="decisionForm.director_decision" class="sr-only" />
                                                    Layak
                                                </label>
                                                <label
                                                    class="flex items-center gap-1.5 p-2 rounded-lg border text-xs cursor-pointer transition text-center justify-center"
                                                    :class="decisionForm.director_decision === 'perlu_evaluasi' ? 'border-amber-500 bg-amber-50 font-bold text-amber-900' : 'border-slate-200 hover:bg-slate-50'"
                                                >
                                                    <input type="radio" value="perlu_evaluasi" v-model="decisionForm.director_decision" class="sr-only" />
                                                    Perlu Eval.
                                                </label>
                                                <label
                                                    class="flex items-center gap-1.5 p-2 rounded-lg border text-xs cursor-pointer transition text-center justify-center"
                                                    :class="decisionForm.director_decision === 'tidak_layak' ? 'border-red-500 bg-red-50 font-bold text-red-900' : 'border-slate-200 hover:bg-slate-50'"
                                                >
                                                    <input type="radio" value="tidak_layak" v-model="decisionForm.director_decision" class="sr-only" />
                                                    Tidak Layak
                                                </label>
                                            </div>
                                            <InputError :message="decisionForm.errors.director_decision" class="mt-1" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan & Arahan Direksi:</label>
                                            <textarea
                                                v-model="decisionForm.director_note"
                                                rows="2"
                                                placeholder="Tuliskan arahan resmi untuk tindak lanjut cabang..."
                                                class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-700 focus:ring-slate-700"
                                            ></textarea>
                                            <InputError :message="decisionForm.errors.director_note" class="mt-1" />
                                        </div>
                                        <div class="flex justify-end">
                                            <button
                                                type="submit"
                                                :disabled="decisionForm.processing"
                                                class="px-4 py-1.5 bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg shadow-sm transition disabled:opacity-50"
                                            >
                                                Simpan Keputusan
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Marketing Verification Panel -->
                                    <div v-if="selectedEvent.can?.verify" class="pt-3 border-t border-dashed border-slate-200">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2">Panel Verifikasi Marketing</span>
                                        <form @submit.prevent="submitVerification" class="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                            <select
                                                v-model="verificationForm.verification_status"
                                                class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-700 focus:ring-slate-700 py-1.5"
                                            >
                                                <option value="belum_diperiksa">Belum Diperiksa</option>
                                                <option value="terverifikasi">Terverifikasi</option>
                                                <option value="perlu_koreksi">Perlu Koreksi</option>
                                            </select>
                                            <InputError :message="verificationForm.errors.verification_status" class="mt-1" />
                                            <textarea
                                                v-model="verificationForm.verification_note"
                                                rows="2"
                                                placeholder="Catatan verifikasi (wajib jika perlu koreksi)..."
                                                class="w-full text-xs rounded-lg border-slate-300 focus:border-slate-700 focus:ring-slate-700"
                                            ></textarea>
                                            <InputError :message="verificationForm.errors.verification_note" class="mt-1" />
                                            <div class="flex justify-end">
                                                <button
                                                    type="submit"
                                                    :disabled="verificationForm.processing"
                                                    class="px-3 py-1.5 bg-slate-800 hover:bg-black text-white text-xs font-semibold rounded-lg transition disabled:opacity-50"
                                                >
                                                    Perbarui Verifikasi
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state: no event selected -->
                <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-100 mb-4">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 mb-1">Lembar Evaluasi Event & Rekomendasi</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">
                        Pilih salah satu event dari tabel di atas dengan menekan tombol <strong>Tinjau</strong> untuk memuat lembar evaluasi lengkap.
                    </p>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
