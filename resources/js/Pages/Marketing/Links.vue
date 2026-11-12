<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    events: Object,
    branches: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const branch_id = ref(props.filters.branch_id || '');
const copiedKey = ref(null);

const applyFilters = () => {
    router.get(route('marketing.links.index'), {
        search: search.value || undefined,
        branch_id: branch_id.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    search.value = '';
    branch_id.value = '';
    router.get(route('marketing.links.index'));
};

const copyToClipboard = (text, key) => {
    navigator.clipboard.writeText(text);
    copiedKey.value = key;
    setTimeout(() => {
        if (copiedKey.value === key) {
            copiedKey.value = null;
        }
    }, 2000);
};
</script>

<template>
    <Head title="Tautan & QR Code Survei - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                        Sheet 08 Distribusi Survei
                    </span>
                </div>
                <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                    Tautan & QR Code Survei Publik
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Kelola tautan formulir survei peserta, unduhan QR Code PNG, dan tautan formulir pelaporan internal
                </p>
            </div>

            <!-- Peringatan Keamanan Distribusi -->
            <div class="rounded-lg bg-amber-50 p-4 border border-amber-200 text-xs text-amber-900 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <strong class="font-bold">PERINGATAN PRIVASI & DISTRIBUSI TAUTAN:</strong>
                    Hanya bagikan <strong>Tautan Survei Publik</strong> atau <strong>QR Code</strong> kepada peserta/customer.
                    <em>Jangan pernah membagikan tautan formulir internal (Laporan Pasca-Event / Omzet H+90 / Dashboard) kepada peserta!</em>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-card flex flex-col sm:flex-row gap-3 items-center justify-between">
                <div class="flex flex-wrap gap-3 w-full sm:w-auto">
                    <div class="w-full sm:w-72">
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="Cari kode atau nama event..."
                            class="w-full text-xs"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div class="w-full sm:w-48">
                        <select
                            v-model="branch_id"
                            class="w-full rounded border-gray-300 text-xs py-1.5 focus:border-[#172B4D] focus:ring-[#172B4D]"
                            @change="applyFilters"
                        >
                            <option value="">Semua Cabang</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">
                                {{ b.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        @click="resetFilters"
                        class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition"
                    >
                        Reset
                    </button>
                    <button
                        type="button"
                        @click="applyFilters"
                        class="px-3 py-1.5 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-semibold rounded shadow-xs transition"
                    >
                        Cari
                    </button>
                </div>
            </div>

            <!-- Daftar Event & Tautan Cards -->
            <div class="space-y-4">
                <div
                    v-for="item in events.data"
                    :key="item.id"
                    class="bg-white rounded-lg border border-gray-200 shadow-card p-5 hover:border-gray-300 transition"
                >
                    <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center justify-between">
                        <!-- QR Code Preview & Download -->
                        <div class="flex items-center gap-4 shrink-0">
                            <div class="p-2 border border-gray-200 rounded-lg bg-white shadow-xs flex items-center justify-center w-32 h-32">
                                <div v-html="item.qr_svg" class="w-full h-full flex items-center justify-center"></div>
                            </div>
                            <div class="space-y-1.5">
                                <a
                                    :href="item.qr_download_url"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-[#F4F6F9] hover:bg-[#172B4D] text-[#172B4D] hover:text-white text-xs font-bold border border-gray-200 transition"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Unduh QR (PNG)
                                </a>

                                <a
                                    :href="item.export_responses_url"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 transition"
                                    title="Ekspor seluruh data jawaban survei responden ke format CSV"
                                >
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Ekspor Survei ({{ item.responses_count }})
                                </a>
                                <div class="text-[10px] text-gray-400 font-mono">
                                    QR_SURVEI_{{ item.code }}.png
                                </div>
                            </div>
                        </div>

                        <!-- Info Event -->
                        <div class="flex-1 space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-gray-900 text-xs bg-gray-100 px-2 py-0.5 rounded">
                                    {{ item.code }}
                                </span>
                                <span class="text-xs text-gray-600 font-semibold">{{ item.branch_name }}</span>
                                <span class="text-xs text-gray-400">• {{ item.event_date_formatted }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">
                                {{ item.name }}
                            </h3>
                            <div class="text-xs text-gray-500">
                                PIC: <strong>{{ item.pic_name }}</strong> • Tujuan: <strong class="uppercase text-[#172B4D]">{{ item.goal_label }}</strong>
                            </div>
                        </div>

                        <!-- Kolom Tautan: Publik vs Internal -->
                        <div class="w-full lg:w-96 space-y-2.5 bg-[#F4F6F9] p-3 rounded-lg border border-gray-200 text-xs">
                            <!-- Link 1: Survei Publik -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase font-bold text-emerald-800 tracking-wider">
                                        1. Tautan Survei Peserta (Publik)
                                    </span>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(item.survey_url, `survey-${item.id}`)"
                                        class="text-[11px] font-bold transition"
                                        :class="copiedKey === `survey-${item.id}` ? 'text-emerald-700' : 'text-[#172B4D] hover:underline'"
                                    >
                                        {{ copiedKey === `survey-${item.id}` ? '✓ Tersalin!' : 'Salin Tautan' }}
                                    </button>
                                </div>
                                <div class="font-mono text-[11px] text-gray-600 truncate bg-white p-1.5 rounded border border-gray-200 select-all">
                                    {{ item.survey_url }}
                                </div>
                            </div>

                            <!-- Link 2: Laporan Pasca-Event (Internal) -->
                            <div class="border-t border-gray-200 pt-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase font-bold text-gray-600 tracking-wider">
                                        2. Form Laporan Pasca-Event (PIC)
                                    </span>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(item.report_url, `report-${item.id}`)"
                                        class="text-[11px] font-bold transition"
                                        :class="copiedKey === `report-${item.id}` ? 'text-emerald-700' : 'text-gray-700 hover:underline'"
                                    >
                                        {{ copiedKey === `report-${item.id}` ? '✓ Tersalin!' : 'Salin' }}
                                    </button>
                                </div>
                                <div class="font-mono text-[11px] text-gray-500 truncate bg-white p-1 rounded border border-gray-100 select-all">
                                    {{ item.report_url }}
                                </div>
                            </div>

                            <!-- Link 3: H+90 (Khusus Sales) -->
                            <div v-if="item.sales_url" class="border-t border-gray-200 pt-2">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase font-bold text-indigo-700 tracking-wider">
                                        3. Form Pembaruan Omzet H+90 (Sales)
                                    </span>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(item.sales_url, `sales-${item.id}`)"
                                        class="text-[11px] font-bold transition"
                                        :class="copiedKey === `sales-${item.id}` ? 'text-emerald-700' : 'text-indigo-700 hover:underline'"
                                    >
                                        {{ copiedKey === `sales-${item.id}` ? '✓ Tersalin!' : 'Salin' }}
                                    </button>
                                </div>
                                <div class="font-mono text-[11px] text-gray-500 truncate bg-white p-1 rounded border border-gray-100 select-all">
                                    {{ item.sales_url }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="events.data.length === 0" class="p-8 text-center text-gray-400 bg-white rounded-lg border border-gray-200">
                    Tidak ada event yang ditemukan.
                </div>

                <!-- Pagination Links -->
                <div v-if="events.links && events.links.length > 3" class="p-4 bg-white rounded-lg border border-gray-200 shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="text-gray-500">
                        Menampilkan <strong class="text-gray-800">{{ events.from }}</strong> s/d <strong class="text-gray-800">{{ events.to }}</strong> dari total <strong class="text-gray-800">{{ events.total }}</strong> event
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        <template v-for="(link, index) in events.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-2.5 py-1 text-xs rounded border transition"
                                :class="{
                                    'bg-[#172B4D] text-white border-[#172B4D] font-bold': link.active,
                                    'bg-white text-gray-700 border-gray-300 hover:bg-gray-100': !link.active
                                }"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-2.5 py-1 text-xs rounded border text-gray-300 border-gray-200 cursor-not-allowed"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
