<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, router, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    kpis: {
        type: Object,
        default: () => ({}),
    },
    analytics: {
        type: Object,
        default: () => ({}),
    },
    tableEvents: {
        type: Array,
        default: () => [],
    },
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
    decisionForm.post(route('events.decision', props.selectedEvent.id), {
        preserveScroll: true,
    });
};

const submitVerification = () => {
    if (!props.selectedEvent) return;
    verificationForm.post(route('events.verify', props.selectedEvent.id), {
        preserveScroll: true,
    });
};

const filteredEventOptions = computed(() => {
    if (!searchEvent.value.trim()) {
        return props.eventOptions || [];
    }
    const q = searchEvent.value.toLowerCase();
    return (props.eventOptions || []).filter(e =>
        e.code.toLowerCase().includes(q) || e.name.toLowerCase().includes(q)
    );
});

const selectEvent = (eventId) => {
    isEventDropdownOpen.value = false;
    router.get(route('dashboard'), {
        portfolio: props.selectedPortfolio,
        event_id: eventId,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const changePortfolio = (portfolio) => {
    router.get(route('dashboard'), {
        portfolio: portfolio,
    }, {
        preserveState: true,
        preserveScroll: false,
    });
};

const formatRupiah = (val) => {
    if (val === null || val === undefined) return 'Belum dilaporkan';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

// Analytics Computations
const totalEvents = computed(() => props.kpis?.total_events || (props.eventOptions?.length ?? 0));
const totalSales = computed(() => props.analytics?.goal_distribution?.sales ?? 0);
const totalAwareness = computed(() => props.analytics?.goal_distribution?.awareness ?? 0);
const totalRelationship = computed(() => props.analytics?.goal_distribution?.relationship ?? 0);

const salesPct = computed(() => totalEvents.value > 0 ? Math.round((totalSales.value / totalEvents.value) * 100) : 0);
const awarenessPct = computed(() => totalEvents.value > 0 ? Math.round((totalAwareness.value / totalEvents.value) * 100) : 0);
const relationshipPct = computed(() => totalEvents.value > 0 ? Math.round((totalRelationship.value / totalEvents.value) * 100) : 0);

// Budget Realization Ratio
const totalBudgetVal = computed(() => props.kpis?.total_budget ?? 0);
const verifiedCostVal = computed(() => props.kpis?.verified_actual_cost ?? 0);
const budgetRealizationPct = computed(() => {
    if (totalBudgetVal.value <= 0) return 0;
    return Math.min(Math.round((verifiedCostVal.value / totalBudgetVal.value) * 100), 100);
});
</script>

<template>
    <Head title="Event Performance Overview - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- A. HEADER & PORTFOLIO FILTER -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Enterprise Business Intelligence
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Event Performance Overview
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Monitoring dan evaluasi kinerja event PT Kencana Maju Bersama
                    </p>
                </div>

                <!-- Filter Controls & Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Portfolio Tabs -->
                    <div class="inline-flex rounded-md border border-gray-200 bg-[#F4F6F9] p-1 text-xs">
                        <button
                            type="button"
                            @click="changePortfolio('operational')"
                            class="px-3 py-1.5 rounded font-semibold transition"
                            :class="selectedPortfolio === 'operational'
                                ? 'bg-[#172B4D] text-white shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            Operasional
                        </button>
                        <button
                            type="button"
                            @click="changePortfolio('demo')"
                            class="px-3 py-1.5 rounded font-semibold transition"
                            :class="selectedPortfolio === 'demo'
                                ? 'bg-[#172B4D] text-white shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            Demo
                        </button>
                        <button
                            type="button"
                            @click="changePortfolio('uat')"
                            class="px-3 py-1.5 rounded font-semibold transition"
                            :class="selectedPortfolio === 'uat'
                                ? 'bg-[#172B4D] text-white shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            UAT
                        </button>
                        <button
                            type="button"
                            @click="changePortfolio('all')"
                            class="px-3 py-1.5 rounded font-semibold transition"
                            :class="selectedPortfolio === 'all'
                                ? 'bg-[#172B4D] text-white shadow-xs'
                                : 'text-gray-600 hover:text-gray-900'"
                        >
                            Semua
                        </button>
                    </div>

                    <!-- Print Button -->
                    <a
                        v-if="selectedEvent"
                        :href="route('dashboard.print', selectedEvent.id)"
                        target="_blank"
                        class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-md shadow-xs transition"
                        title="Buka format cetak 1 halaman A4 Landscape"
                    >
                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Cetak Laporan (A4)
                    </a>
                </div>
            </div>

            <!-- B. KPI OVERVIEW CARDS (6 COMPACT CARDS) -->
            <div id="budget-monitoring" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 scroll-mt-24">
                <!-- KPI 1: Total Event -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Total Event
                    </span>
                    <div class="text-2xl font-black text-[#172B4D] mt-1">
                        {{ totalEvents }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Portofolio {{ selectedPortfolio }}
                    </div>
                </div>

                <!-- KPI 2: Terverifikasi -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Terverifikasi
                    </span>
                    <div class="text-2xl font-black text-emerald-700 mt-1">
                        {{ kpis.verified_events_count ?? 0 }}
                    </div>
                    <div class="text-[11px] text-emerald-800 font-semibold mt-1">
                        Siap dievaluasi resmi
                    </div>
                </div>

                <!-- KPI 3: Menunggu Verifikasi -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Menunggu Verifikasi
                    </span>
                    <div class="text-2xl font-black text-amber-700 mt-1">
                        {{ kpis.pending_verification_count ?? 0 }}
                    </div>
                    <div class="text-[11px] text-amber-800 font-semibold mt-1">
                        Perlu ditinjau Marketing
                    </div>
                </div>

                <!-- KPI 4: Total Rencana Budget -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Total Rencana Budget
                    </span>
                    <div class="text-base font-bold text-gray-900 font-mono mt-1 truncate" :title="formatRupiah(kpis.total_budget ?? 0)">
                        {{ formatRupiah(kpis.total_budget ?? 0) }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Proposal + Tim Cabang
                    </div>
                </div>

                <!-- KPI 5: Realisasi Biaya Aktual -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Realisasi Biaya Aktual
                    </span>
                    <div class="text-base font-bold text-slate-800 font-mono mt-1 truncate" :title="formatRupiah(kpis.verified_actual_cost ?? 0)">
                        {{ formatRupiah(kpis.verified_actual_cost ?? 0) }}
                    </div>
                    <div class="text-[11px] text-gray-500 mt-1">
                        Khusus event terverifikasi
                    </div>
                </div>

                <!-- KPI 6: Total Omzet Terverifikasi -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                        Omzet Terverifikasi (Sales)
                    </span>
                    <div class="text-base font-black text-indigo-700 font-mono mt-1 truncate" :title="formatRupiah(kpis.verified_revenue ?? 0)">
                        {{ formatRupiah(kpis.verified_revenue ?? 0) }}
                    </div>
                    <div class="text-[11px] text-indigo-900 font-semibold mt-1">
                        Bukti invoice teruji H+90
                    </div>
                </div>
            </div>

            <!-- C. EVENT PERFORMANCE ANALYTICS SECTION -->
            <div id="analytics-section" class="grid grid-cols-1 md:grid-cols-3 gap-4 scroll-mt-24">
                <!-- Goal Distribution -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700">
                            Distribusi Tujuan Event
                        </span>
                        <span class="text-[11px] text-gray-400">Target</span>
                    </div>

                    <!-- Progress Multi-bar -->
                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden flex mb-3">
                        <div :style="`width: ${salesPct}%`" class="bg-emerald-600" title="Sales"></div>
                        <div :style="`width: ${awarenessPct}%`" class="bg-sky-500" title="Awareness"></div>
                        <div :style="`width: ${relationshipPct}%`" class="bg-indigo-600" title="Relationship"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-emerald-50/50 p-2 rounded border border-emerald-100">
                            <span class="text-gray-500 block text-[10px]">Sales</span>
                            <span class="font-bold text-emerald-800 text-sm">{{ totalSales }}</span>
                            <span class="text-[10px] text-gray-400 block">{{ salesPct }}%</span>
                        </div>
                        <div class="bg-sky-50/50 p-2 rounded border border-sky-100">
                            <span class="text-gray-500 block text-[10px]">Awareness</span>
                            <span class="font-bold text-sky-800 text-sm">{{ totalAwareness }}</span>
                            <span class="text-[10px] text-gray-400 block">{{ awarenessPct }}%</span>
                        </div>
                        <div class="bg-indigo-50/50 p-2 rounded border border-indigo-100">
                            <span class="text-gray-500 block text-[10px]">Relationship</span>
                            <span class="font-bold text-indigo-800 text-sm">{{ totalRelationship }}</span>
                            <span class="text-[10px] text-gray-400 block">{{ relationshipPct }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Budget vs Actual Realization -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700">
                            Realisasi Terhadap Anggaran
                        </span>
                        <span class="text-[11px] font-bold" :class="budgetRealizationPct > 100 ? 'text-red-600' : 'text-emerald-600'">
                            {{ budgetRealizationPct }}%
                        </span>
                    </div>

                    <!-- Progress bar -->
                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden mb-3">
                        <div
                            :style="`width: ${budgetRealizationPct}%`"
                            class="h-full rounded-full transition-all duration-300"
                            :class="budgetRealizationPct > 100 ? 'bg-red-500' : 'bg-emerald-500'"
                        ></div>
                    </div>

                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-100">
                            <span class="text-gray-500">Anggaran Rencana:</span>
                            <span class="font-mono font-semibold">{{ formatRupiah(kpis.total_budget ?? 0) }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-gray-500">Realisasi Terverifikasi:</span>
                            <span class="font-mono font-bold text-slate-900">{{ formatRupiah(kpis.verified_actual_cost ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution -->
                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700">
                            Status Verifikasi Lapangan
                        </span>
                        <span class="text-[11px] text-gray-400">Marketing</span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between p-2 rounded bg-emerald-50/60 border border-emerald-100">
                            <span class="font-medium text-emerald-900">Terverifikasi:</span>
                            <span class="font-bold text-emerald-800">{{ kpis.verified_events_count ?? 0 }} event</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-amber-50/60 border border-amber-100">
                            <span class="font-medium text-amber-900">Belum Diperiksa:</span>
                            <span class="font-bold text-amber-800">{{ kpis.pending_verification_count ?? 0 }} event</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded bg-red-50/60 border border-red-100">
                            <span class="font-medium text-red-900">Perlu Koreksi:</span>
                            <span class="font-bold text-red-800">{{ kpis.correction_needed_count ?? 0 }} event</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- D. EVENT MONITORING TABLE (10 RECENT EVENTS) -->
            <div id="table-monitoring" class="bg-white rounded-lg border border-gray-200 shadow-card overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#F4F6F9]">
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-wider text-[#172B4D]">
                            Tabel Monitoring Event Terkini
                        </h2>
                        <p class="text-[11px] text-gray-500">
                            Ringkasan status data, verifikasi, dan rekomendasi sistem per event
                        </p>
                    </div>

                    <Link
                        :href="route('events.index')"
                        class="inline-flex items-center text-xs font-semibold text-[#172B4D] hover:text-[#DC2626] transition"
                    >
                        Lihat Seluruh Event (Sheet 01) &rarr;
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Kode Event</th>
                                <th class="p-3">Nama Event</th>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Cabang</th>
                                <th class="p-3">Tujuan</th>
                                <th class="p-3">Status Data</th>
                                <th class="p-3">Verifikasi</th>
                                <th class="p-3">Rekomendasi</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="item in tableEvents"
                                :key="item.id"
                                class="hover:bg-gray-50/80 transition"
                                :class="item.id === selectedEventId ? 'bg-indigo-50/40 font-medium' : ''"
                            >
                                <td class="p-3 font-mono font-bold text-gray-900 whitespace-nowrap">
                                    {{ item.code }}
                                </td>
                                <td class="p-3 text-gray-900 max-w-xs truncate" :title="item.name">
                                    {{ item.name }}
                                </td>
                                <td class="p-3 text-gray-600 whitespace-nowrap">
                                    {{ item.event_date_formatted }}
                                </td>
                                <td class="p-3 text-gray-700 whitespace-nowrap">
                                    {{ item.branch_name }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-50 text-emerald-800 border border-emerald-200': item.goal === 'sales',
                                            'bg-sky-50 text-sky-800 border border-sky-200': item.goal === 'awareness',
                                            'bg-indigo-50 text-indigo-800 border border-indigo-200': item.goal === 'relationship'
                                        }"
                                    >
                                        {{ item.goal_label }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium"
                                        :class="{
                                            'bg-green-50 text-green-700 border border-green-200': item.data_status === 'lengkap',
                                            'bg-amber-50 text-amber-700 border border-amber-200': item.data_status === 'parsial',
                                            'bg-red-50 text-red-700 border border-red-200': item.data_status === 'kurang_lengkap',
                                            'bg-gray-50 text-gray-700 border border-gray-200': !item.data_status
                                        }"
                                    >
                                        {{ item.data_status_label }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
                                        :class="{
                                            'bg-emerald-50 text-emerald-800 border border-emerald-200': item.verification_status === 'terverifikasi',
                                            'bg-amber-50 text-amber-800 border border-amber-200': item.verification_status === 'belum_diperiksa',
                                            'bg-red-50 text-red-800 border border-red-200': item.verification_status === 'perlu_koreksi'
                                        }"
                                    >
                                        {{ item.verification_status_label }}
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-100 text-emerald-900 border border-emerald-300': item.recommendation === 'layak',
                                            'bg-amber-100 text-amber-900 border border-amber-300': item.recommendation === 'perlu_evaluasi',
                                            'bg-red-100 text-red-900 border border-red-300': item.recommendation === 'tidak_layak',
                                            'bg-gray-100 text-gray-700 border border-gray-200': item.recommendation === 'belum_final'
                                        }"
                                    >
                                        {{ item.recommendation_label }}
                                    </span>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="selectEvent(item.id)"
                                        class="px-2.5 py-1 text-xs font-semibold rounded bg-[#F4F6F9] hover:bg-[#172B4D] hover:text-white transition"
                                    >
                                        Tinjau
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!tableEvents || tableEvents.length === 0">
                                <td colspan="9" class="p-6 text-center text-xs text-gray-400">
                                    Belum ada data event pada portofolio ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- E. INTERACTIVE EVENT REVIEWER & EVALUATION SUMMARY -->
            <div id="evaluation-summary" class="scroll-mt-24 space-y-6">
                <div v-if="selectedEvent" class="bg-white rounded-lg border border-gray-200 shadow-card p-5 space-y-6">
                <!-- Event Selector Toolbar -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-gray-200 pb-4">
                    <div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                            Lembar Tinjauan & Rekomendasi
                        </div>
                        <h2 class="text-base font-black text-[#172B4D] mt-0.5">
                            {{ selectedEvent.name }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Print A4 Button -->
                        <a
                            :href="route('dashboard.print', selectedEvent.id)"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-50 text-gray-700 rounded-md text-xs font-semibold transition border border-gray-300 shadow-2xs hover:shadow-xs"
                            title="Buka Lembar Cetak Eksekutif A4 Landscape"
                        >
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span class="hidden sm:inline">Cetak A4</span>
                        </a>

                        <!-- Dropdown Event Switcher -->
                        <div class="relative min-w-[260px] sm:min-w-[300px]">
                            <button
                                type="button"
                                @click="isEventDropdownOpen = !isEventDropdownOpen"
                                class="w-full bg-[#F4F6F9] border border-gray-300 rounded-md px-3 py-1.5 text-xs text-left font-semibold flex items-center justify-between text-gray-800 hover:bg-gray-100 transition shadow-2xs"
                            >
                                <span class="truncate font-mono">{{ selectedEvent.code }} — {{ selectedEvent.name }}</span>
                                <svg class="w-4 h-4 ml-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                        <div
                            v-if="isEventDropdownOpen"
                            class="absolute right-0 z-20 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 py-1 max-h-72 overflow-y-auto"
                        >
                            <div class="p-2 border-b">
                                <TextInput
                                    v-model="searchEvent"
                                    type="text"
                                    placeholder="Cari kode/nama event..."
                                    class="w-full text-xs"
                                    @click.stop
                                />
                            </div>
                            <button
                                v-for="opt in filteredEventOptions"
                                :key="opt.id"
                                type="button"
                                @click="selectEvent(opt.id)"
                                class="w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center justify-between text-xs transition border-b border-gray-50"
                                :class="opt.id === selectedEventId ? 'bg-indigo-50 font-bold text-indigo-900' : 'text-gray-700'"
                            >
                                <span class="font-mono font-bold">{{ opt.code }}</span>
                                <span class="truncate ml-2 text-[11px] text-gray-500">{{ opt.name }}</span>
                            </button>
                        </div>
                    </div>
                </div>
                </div>

                <!-- 1. Identitas Event (Grid 4 Kolom) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 bg-[#F4F6F9] p-4 rounded-md border border-gray-200 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[10px]">Tanggal & Lokasi</span>
                        <span class="font-semibold text-gray-900">{{ selectedEvent.event_date_formatted }} • {{ selectedEvent.location }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Cabang & PIC</span>
                        <span class="font-semibold text-gray-900">{{ selectedEvent.branch_name }} ({{ selectedEvent.pic_name }})</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Tujuan Utama</span>
                        <span class="font-bold uppercase text-[#172B4D]">{{ selectedEvent.goal_label }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Status Verifikasi</span>
                        <span class="font-semibold" :class="selectedEvent.verification_status === 'terverifikasi' ? 'text-emerald-700' : 'text-amber-700'">
                            {{ selectedEvent.verification_status_label }}
                        </span>
                    </div>
                </div>

                <!-- 2. Evaluasi Biaya & Finansial -->
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 border-b border-gray-100 pb-1.5 flex items-center justify-between">
                        <span>1. Evaluasi Biaya & Anggaran (Rupiah)</span>
                        <span class="font-mono text-[11px] text-gray-500 font-normal">Preservasi Nilai Eksplisit Rp0 vs NULL</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Total Rencana Budget:</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">
                                {{ formatRupiah(evaluation?.cost?.total_budget) }}
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Total Biaya Aktual:</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">
                                {{ formatRupiah(evaluation?.cost?.total_actual) }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded border" :class="(evaluation?.cost?.budget_gap ?? 0) >= 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200'">
                            <span class="text-gray-500 block text-[10px]">Selisih Budget (Gap):</span>
                            <span class="font-mono font-bold text-sm" :class="(evaluation?.cost?.budget_gap ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700'">
                                {{ formatRupiah(evaluation?.cost?.budget_gap) }}
                            </span>
                        </div>
                        <div class="p-2.5 rounded border" :class="(evaluation?.cost?.budget_variance_pct ?? 0) > 0 ? 'bg-red-50/50 border-red-200' : 'bg-emerald-50/50 border-emerald-200'">
                            <span class="text-gray-500 block text-[10px]">Varians Pengeluaran:</span>
                            <span class="font-mono font-bold text-sm" :class="(evaluation?.cost?.budget_variance_pct ?? 0) > 0 ? 'text-red-700' : 'text-emerald-700'">
                                {{ evaluation?.cost?.budget_variance_pct !== null ? (evaluation?.cost?.budget_variance_pct > 0 ? '+' : '') + evaluation?.cost?.budget_variance_pct + '%' : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. Pencapaian Target & KPI Sesuai Goal -->
                <div class="border border-gray-200 rounded-md p-4 bg-white">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 mb-3 border-b border-gray-100 pb-1.5">
                        2. Pencapaian Target Berdasarkan Tujuan: {{ selectedEvent.goal_label }}
                    </h3>

                    <!-- A. Sales Goal -->
                    <div v-if="evaluation?.goal_result?.goal === 'sales'" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Status Jatuh Tempo H+90:</span>
                            <span class="font-bold text-xs" :class="evaluation?.goal_result?.h90_eligible ? 'text-emerald-700' : 'text-amber-700'">
                                {{ evaluation?.goal_result?.h90_eligible ? 'Sudah Jatuh Tempo' : 'Belum H+90 (' + evaluation?.goal_result?.days_since_event + '/90 hari)' }}
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Omzet Aktual H+90:</span>
                            <span class="font-mono font-bold text-gray-900 text-sm">
                                {{ formatRupiah(evaluation?.goal_result?.revenue) }}
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Ambang Batas Rasio Omzet / Biaya:</span>
                            <span class="font-mono font-semibold">Min. 10.0x</span>
                        </div>
                        <div class="p-2.5 rounded border" :class="(evaluation?.goal_result?.ratio ?? 0) >= 10 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-red-50/50 border-red-200'">
                            <span class="text-gray-500 block text-[10px]">Rasio Tercapai:</span>
                            <span class="font-mono font-bold text-sm" :class="(evaluation?.goal_result?.ratio ?? 0) >= 10 ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.ratio !== null ? evaluation?.goal_result?.ratio + 'x' : 'Belum final' }}
                            </span>
                        </div>
                    </div>

                    <!-- B. Awareness Goal -->
                    <div v-else-if="evaluation?.goal_result?.goal === 'awareness'" class="grid grid-cols-1 md:grid-cols-4 gap-3 text-xs">
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Kecukupan Survei:</span>
                            <span class="font-bold" :class="evaluation?.goal_result?.survey_sufficient ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.counted_surveys }} / min. {{ evaluation?.goal_result?.survey_threshold }} responden
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Awareness Uplift (Skala 1-5):</span>
                            <span class="font-mono font-bold" :class="(evaluation?.goal_result?.uplift ?? 0) >= 0.5 ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.uplift !== null ? '+' + evaluation?.goal_result?.uplift : '-' }} (min. +0.50)
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Unassisted Brand Recall:</span>
                            <span class="font-mono font-bold" :class="(evaluation?.goal_result?.brand_recall_pct ?? 0) >= 50 ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.brand_recall_pct !== null ? evaluation?.goal_result?.brand_recall_pct + '%' : '-' }} (min. 50%)
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Relevansi Audiens:</span>
                            <span class="font-mono font-bold text-gray-900">
                                {{ evaluation?.goal_result?.relevance_pct !== null ? evaluation?.goal_result?.relevance_pct + '%' : '-' }} (min. 50%)
                            </span>
                        </div>
                    </div>

                    <!-- C. Relationship Goal -->
                    <div v-else-if="evaluation?.goal_result?.goal === 'relationship'" class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Responden Mitra / Customer:</span>
                            <span class="font-mono font-bold" :class="(evaluation?.goal_result?.partner_count ?? 0) >= 5 ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.partner_count }} mitra (min. 5)
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Rata-rata Skor Hubungan:</span>
                            <span class="font-mono font-bold" :class="(evaluation?.goal_result?.avg_relationship_score ?? 0) >= 4 ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.avg_relationship_score !== null ? evaluation?.goal_result?.avg_relationship_score + ' / 5.0' : '-' }} (min. 4.0)
                            </span>
                        </div>
                        <div class="p-2.5 bg-gray-50 rounded border border-gray-100">
                            <span class="text-gray-500 block text-[10px]">Kecukupan Responden:</span>
                            <span class="font-bold" :class="evaluation?.goal_result?.survey_sufficient ? 'text-emerald-700' : 'text-red-700'">
                                {{ evaluation?.goal_result?.survey_sufficient ? 'Memadai' : 'Belum Memadai' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 4. Rekomendasi Sistem & Keputusan Direksi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Rekomendasi Otomatis Mesin Evaluasi -->
                    <div class="border border-gray-200 rounded-md p-4 bg-gray-50/50 space-y-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block">
                            3. Rekomendasi Otomatis Sistem
                        </span>
                        <div>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded text-xs font-black uppercase tracking-wider"
                                :class="{
                                    'bg-emerald-100 text-emerald-900 border border-emerald-300': evaluation?.recommendation?.status === 'layak',
                                    'bg-amber-100 text-amber-900 border border-amber-300': evaluation?.recommendation?.status === 'perlu_evaluasi',
                                    'bg-red-100 text-red-900 border border-red-300': evaluation?.recommendation?.status === 'tidak_layak',
                                    'bg-gray-100 text-gray-800 border border-gray-300': evaluation?.recommendation?.status === 'belum_final'
                                }"
                            >
                                {{ evaluation?.recommendation?.label }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-700 leading-relaxed font-medium">
                            {{ evaluation?.recommendation?.summary }}
                        </p>

                        <!-- Checklist Tindak Lanjut -->
                        <div v-if="evaluation?.recommendation?.action_checklist?.length" class="pt-2 border-t border-gray-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-600 block mb-1">
                                Checklist Tindak Lanjut:
                            </span>
                            <ul class="text-xs text-gray-600 space-y-1">
                                <li v-for="(act, idx) in evaluation.recommendation.action_checklist" :key="idx" class="flex items-start gap-1.5">
                                    <span class="text-amber-600 font-bold">•</span>
                                    <span>{{ act }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Keputusan Akhir Direksi -->
                    <div class="border border-gray-300 rounded-md p-4 bg-white space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-700">
                                4. Keputusan Akhir Direksi (Resmi)
                            </span>
                            <span
                                v-if="selectedEvent.director_decision"
                                class="px-2 py-0.5 rounded text-xs font-black uppercase tracking-wider"
                                :class="{
                                    'bg-emerald-100 text-emerald-800 border border-emerald-200': selectedEvent.director_decision === 'layak',
                                    'bg-amber-100 text-amber-800 border border-amber-200': selectedEvent.director_decision === 'perlu_evaluasi',
                                    'bg-red-100 text-red-800 border border-red-200': selectedEvent.director_decision === 'tidak_layak',
                                }"
                            >
                                {{ selectedEvent.director_decision }}
                            </span>
                        </div>

                        <!-- Stale decision warning -->
                        <div v-if="selectedEvent.decision_stale_flag" class="p-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-800 font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Data laporan diperbarui PIC setelah keputusan dicatat. Mohon tinjau ulang keputusan Direksi.</span>
                        </div>

                        <!-- View Only Decision (if user has no permission) -->
                        <div v-if="!selectedEvent.can?.recordDecision">
                            <div v-if="selectedEvent.director_decision">
                                <div class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded border border-gray-200">
                                    <span class="font-bold text-gray-700 block text-[10px] uppercase">Catatan / Arahan Direksi:</span>
                                    <p class="italic mt-0.5">
                                        "{{ selectedEvent.director_note || 'Tidak ada catatan tambahan.' }}"
                                    </p>
                                </div>
                            </div>
                            <div v-else class="p-4 text-center text-xs text-gray-400 bg-gray-50 rounded border border-dashed border-gray-200">
                                Direksi belum mencatat keputusan akhir untuk event ini.
                            </div>
                        </div>

                        <!-- Interactive Form for Direksi / Admin -->
                        <form v-else @submit.prevent="submitDecision" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilihan Keputusan Resmi:</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <label
                                        class="flex items-center gap-1.5 p-2 rounded border text-xs cursor-pointer transition"
                                        :class="decisionForm.director_decision === 'layak' ? 'border-emerald-500 bg-emerald-50 font-bold text-emerald-900' : 'border-gray-200 hover:bg-gray-50'"
                                    >
                                        <input type="radio" value="layak" v-model="decisionForm.director_decision" class="text-emerald-600 focus:ring-emerald-500" />
                                        <span>Layak</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-1.5 p-2 rounded border text-xs cursor-pointer transition"
                                        :class="decisionForm.director_decision === 'perlu_evaluasi' ? 'border-amber-500 bg-amber-50 font-bold text-amber-900' : 'border-gray-200 hover:bg-gray-50'"
                                    >
                                        <input type="radio" value="perlu_evaluasi" v-model="decisionForm.director_decision" class="text-amber-600 focus:ring-amber-500" />
                                        <span>Perlu Evaluasi</span>
                                    </label>
                                    <label
                                        class="flex items-center gap-1.5 p-2 rounded border text-xs cursor-pointer transition"
                                        :class="decisionForm.director_decision === 'tidak_layak' ? 'border-red-500 bg-red-50 font-bold text-red-900' : 'border-gray-200 hover:bg-gray-50'"
                                    >
                                        <input type="radio" value="tidak_layak" v-model="decisionForm.director_decision" class="text-red-600 focus:ring-red-500" />
                                        <span>Tidak Layak</span>
                                    </label>
                                </div>
                                <InputError :message="decisionForm.errors.director_decision" class="mt-1" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan & Arahan Direksi:</label>
                                <textarea
                                    v-model="decisionForm.director_note"
                                    rows="2"
                                    placeholder="Tuliskan arahan resmi untuk tindak lanjut cabang..."
                                    class="w-full text-xs rounded border-gray-300 focus:border-[#172B4D] focus:ring-[#172B4D]"
                                ></textarea>
                                <InputError :message="decisionForm.errors.director_note" class="mt-1" />
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="decisionForm.processing"
                                    class="px-3 py-1.5 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-semibold rounded shadow-xs transition disabled:opacity-50"
                                >
                                    Simpan Keputusan Direksi
                                </button>
                            </div>
                        </form>

                        <!-- Quick Verification for Marketing -->
                        <div v-if="selectedEvent.can?.verify" class="mt-4 pt-3 border-t border-dashed border-gray-200">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-2">
                                Panel Verifikasi Marketing
                            </span>
                            <form @submit.prevent="submitVerification" class="space-y-2 bg-[#F4F6F9] p-3 rounded border border-gray-200">
                                <div>
                                    <select
                                        v-model="verificationForm.verification_status"
                                        class="w-full text-xs rounded border-gray-300 focus:border-[#172B4D] focus:ring-[#172B4D] py-1"
                                    >
                                        <option value="belum_diperiksa">Belum Diperiksa</option>
                                        <option value="terverifikasi">Terverifikasi</option>
                                        <option value="perlu_koreksi">Perlu Koreksi</option>
                                    </select>
                                    <InputError :message="verificationForm.errors.verification_status" class="mt-1" />
                                </div>
                                <div>
                                    <textarea
                                        v-model="verificationForm.verification_note"
                                        rows="2"
                                        placeholder="Catatan verifikasi (wajib jika perlu koreksi)..."
                                        class="w-full text-xs rounded border-gray-300 focus:border-[#172B4D] focus:ring-[#172B4D] py-1"
                                    ></textarea>
                                    <InputError :message="verificationForm.errors.verification_note" class="mt-1" />
                                </div>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="verificationForm.processing"
                                        class="px-2.5 py-1 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded shadow-xs transition disabled:opacity-50"
                                    >
                                        Perbarui Verifikasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="bg-white rounded-lg border border-gray-200 shadow-card p-8 text-center space-y-3">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">Lembar Evaluasi Event & Rekomendasi</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto">
                    Belum ada event yang dipilih. Silakan pilih salah satu event pada tabel di atas untuk memuat lembar evaluasi lengkap.
                </p>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
</template>
