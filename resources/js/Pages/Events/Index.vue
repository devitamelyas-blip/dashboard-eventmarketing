<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';

const props = defineProps({
    events: Object,
    branches: Array,
    filters: Object,
    goals: Array,
    verificationStatuses: Array,
    portfolios: Array,
});

const user = usePage().props.auth.user;
const canCreate = user.roles.includes('pic') || user.roles.includes('admin');

// Verification Modal State
const isVerifyModalOpen = ref(false);
const verifyTargetEvent = ref(null);

const verifyForm = useForm({
    verification_status: '',
    verification_note: '',
});

const openVerifyModal = (item) => {
    verifyTargetEvent.value = item;
    verifyForm.verification_status = item.verification_status;
    verifyForm.verification_note = item.verification_note || '';
    verifyForm.clearErrors();
    isVerifyModalOpen.value = true;
};

const submitVerification = () => {
    if (!verifyTargetEvent.value) return;
    verifyForm.post(route('events.verify', verifyTargetEvent.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isVerifyModalOpen.value = false;
        },
    });
};

// Filters state
const search = ref(props.filters.search || '');
const branch_id = ref(props.filters.branch_id || '');
const goal = ref(props.filters.goal || '');
const verification_status = ref(props.filters.verification_status || '');
const portfolio = ref(props.filters.portfolio || '');

const applyFilters = () => {
    router.get(route('events.index'), {
        search: search.value || undefined,
        branch_id: branch_id.value || undefined,
        goal: goal.value || undefined,
        verification_status: verification_status.value || undefined,
        portfolio: portfolio.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetFilters = () => {
    search.value = '';
    branch_id.value = '';
    goal.value = '';
    verification_status.value = '';
    portfolio.value = '';
    router.get(route('events.index'));
};

const formatRupiah = (val) => {
    if (val === null || val === undefined) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

<template>
    <Head title="Data Event - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Header Bar -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#172B4D]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Sheet 01 Database Event
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Daftar Seluruh Event
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Monitoring data pengajuan, status verifikasi marketing, dan keputusan direksi
                    </p>
                </div>

                <div v-if="canCreate">
                    <Link :href="route('events.create')">
                        <button
                            type="button"
                            class="inline-flex items-center px-4 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold rounded-md shadow-xs transition"
                        >
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Ajukan Event Baru
                        </button>
                    </Link>
                </div>
            </div>

            <!-- Filter Controls -->
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-card space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                    <!-- Search -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Pencarian</label>
                        <TextInput
                            v-model="search"
                            type="text"
                            placeholder="Kode, Nama, atau PIC..."
                            class="w-full text-xs"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Filter Cabang -->
                    <div v-if="!user.roles.includes('pic')">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Cabang</label>
                        <select
                            v-model="branch_id"
                            class="w-full rounded-md border-gray-300 text-xs py-1.5 focus:border-[#172B4D] focus:ring-[#172B4D]"
                            @change="applyFilters"
                        >
                            <option value="">Semua Cabang</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">
                                {{ b.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Portofolio -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Portofolio</label>
                        <select
                            v-model="portfolio"
                            class="w-full rounded-md border-gray-300 text-xs py-1.5 focus:border-[#172B4D] focus:ring-[#172B4D]"
                            @change="applyFilters"
                        >
                            <option value="">Semua Portofolio</option>
                            <option v-for="p in portfolios" :key="p.value" :value="p.value">
                                {{ p.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Tujuan -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Tujuan Utama</label>
                        <select
                            v-model="goal"
                            class="w-full rounded-md border-gray-300 text-xs py-1.5 focus:border-[#172B4D] focus:ring-[#172B4D]"
                            @change="applyFilters"
                        >
                            <option value="">Semua Tujuan</option>
                            <option v-for="g in goals" :key="g.value" :value="g.value">
                                {{ g.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Verifikasi -->
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Status Verifikasi</label>
                        <select
                            v-model="verification_status"
                            class="w-full rounded-md border-gray-300 text-xs py-1.5 focus:border-[#172B4D] focus:ring-[#172B4D]"
                            @change="applyFilters"
                        >
                            <option value="">Semua Status</option>
                            <option v-for="v in verificationStatuses" :key="v.value" :value="v.value">
                                {{ v.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                    <span class="text-xs text-gray-500">
                        Total ditemukan: <strong class="text-gray-900">{{ events.total }}</strong> event
                    </span>
                    <div class="flex gap-2">
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
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#F4F6F9] border-b border-gray-200 text-gray-600 font-semibold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Kode & Portofolio</th>
                                <th class="p-3">Nama Event</th>
                                <th class="p-3">Tanggal</th>
                                <th class="p-3">Cabang & PIC</th>
                                <th class="p-3">Tujuan</th>
                                <th class="p-3 text-right">Biaya Proposal</th>
                                <th class="p-3">Verifikasi</th>
                                <th class="p-3">Keputusan Direksi</th>
                                <th class="p-3 text-right">Tahap & Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in events.data" :key="item.id" class="hover:bg-gray-50/80 transition">
                                <!-- Kode & Portofolio -->
                                <td class="p-3 whitespace-nowrap">
                                    <div class="font-mono font-bold text-gray-900">
                                        {{ item.code }}
                                    </div>
                                    <div class="mt-0.5">
                                        <span
                                            class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider"
                                            :class="{
                                                'bg-blue-50 text-blue-700 border border-blue-200': item.portfolio === 'operational',
                                                'bg-amber-50 text-amber-800 border border-amber-200': item.portfolio === 'demo',
                                                'bg-purple-50 text-purple-700 border border-purple-200': item.portfolio === 'uat'
                                            }"
                                        >
                                            {{ item.portfolio_label }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Nama Event -->
                                <td class="p-3 max-w-xs">
                                    <div class="font-semibold text-gray-900 truncate" :title="item.name">
                                        {{ item.name }}
                                    </div>
                                    <div class="text-gray-500 text-[11px] truncate mt-0.5">
                                        {{ item.type_label }} • {{ item.location }}
                                    </div>
                                </td>

                                <!-- Tanggal -->
                                <td class="p-3 whitespace-nowrap text-gray-600">
                                    {{ item.event_date_formatted }}
                                </td>

                                <!-- Cabang & PIC -->
                                <td class="p-3 whitespace-nowrap">
                                    <div class="font-semibold text-gray-800">{{ item.branch.name }}</div>
                                    <div class="text-gray-500 text-[11px]">{{ item.pic_name }}</div>
                                </td>

                                <!-- Tujuan -->
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

                                <!-- Biaya Proposal -->
                                <td class="p-3 whitespace-nowrap text-right font-mono font-bold text-gray-900">
                                    {{ formatRupiah(item.proposal_cost) }}
                                </td>

                                <!-- Verifikasi -->
                                <td class="p-3 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
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
                                        <button
                                            v-if="item.can.verify"
                                            type="button"
                                            @click="openVerifyModal(item)"
                                            class="p-1 text-gray-400 hover:text-[#172B4D] rounded transition"
                                            title="Ubah Status Verifikasi"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div v-if="item.verification_note" class="text-[10px] text-gray-500 max-w-[140px] truncate mt-0.5" :title="item.verification_note">
                                        {{ item.verification_note }}
                                    </div>
                                </td>

                                <!-- Keputusan Direksi -->
                                <td class="p-3 whitespace-nowrap">
                                    <div v-if="item.director_decision" class="font-bold text-xs uppercase"
                                         :class="{
                                             'text-emerald-700': item.director_decision === 'layak',
                                             'text-amber-700': item.director_decision === 'perlu_evaluasi',
                                             'text-red-700': item.director_decision === 'tidak_layak',
                                         }">
                                        {{ item.director_decision }}
                                    </div>
                                    <div v-else class="text-gray-400 italic text-[11px]">
                                        Belum Ditentukan
                                    </div>
                                    <span v-if="item.decision_stale_flag" class="text-[9px] text-red-600 font-bold block mt-0.5">
                                        ⚠️ Data berubah
                                    </span>
                                </td>

                                <!-- Tahap & Aksi -->
                                <td class="p-3 whitespace-nowrap text-right space-x-1">
                                    <!-- Tahap 2: Laporan Pasca Event -->
                                    <template v-if="item.can.updateReport">
                                        <Link
                                            :href="route('events.report.edit', item.id)"
                                            class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold transition"
                                            :class="item.has_report
                                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                                : 'bg-[#172B4D] text-white hover:bg-[#243B5A]'"
                                        >
                                            {{ item.has_report ? 'Edit Laporan' : 'Isi Laporan' }}
                                        </Link>
                                    </template>
                                    <template v-else-if="!item.is_eligible_for_report">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] text-gray-400 bg-gray-50 border border-gray-200" title="Laporan hanya bisa diisi pada atau setelah tanggal event">
                                            Pra-Event
                                        </span>
                                    </template>

                                    <!-- Tahap 3: H+90 Sales -->
                                    <template v-if="item.goal === 'sales'">
                                        <template v-if="item.can.updateSales">
                                            <Link
                                                :href="route('events.sales.edit', item.id)"
                                                class="inline-flex items-center px-2 py-1 rounded text-[11px] font-semibold transition ml-1"
                                                :class="item.has_sales
                                                    ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100'
                                                    : 'bg-emerald-600 text-white hover:bg-emerald-700'"
                                            >
                                                {{ item.has_sales ? 'Edit H+90' : 'Isi H+90' }}
                                            </Link>
                                        </template>
                                        <template v-else-if="!item.is_eligible_for_sales">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] text-amber-700 bg-amber-50 ml-1 border border-amber-200" :title="`Omzet H+90 dapat diisi setelah 90 hari`">
                                                H+90: {{ item.days_until_sales }} hr
                                            </span>
                                        </template>
                                    </template>

                                    <!-- Bukti Invoice -->
                                    <template v-if="item.has_invoice && item.can.viewInvoice">
                                        <a
                                            :href="route('events.invoice.download', item.id)"
                                            target="_blank"
                                            class="inline-flex items-center p-1 text-gray-500 hover:text-[#172B4D] ml-1"
                                            title="Unduh Berkas Invoice"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    </template>
                                </td>
                            </tr>

                            <tr v-if="events.data.length === 0">
                                <td colspan="9" class="p-8 text-center text-gray-400">
                                    Tidak ada event yang sesuai dengan filter pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="events.links && events.links.length > 3" class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="text-gray-500">
                        Menampilkan <strong class="text-gray-800">{{ events.from }}</strong> s/d <strong class="text-gray-800">{{ events.to }}</strong> dari total <strong class="text-gray-800">{{ events.total }}</strong> event
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, index) in events.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-2.5 py-1 text-xs rounded border transition"
                            :class="{
                                'bg-[#172B4D] text-white border-[#172B4D] font-bold': link.active,
                                'bg-white text-gray-700 border-gray-300 hover:bg-gray-100': !link.active && link.url,
                                'text-gray-300 border-gray-200 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Modal -->
        <div v-if="isVerifyModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-5 space-y-4 border border-gray-200">
                <div class="flex items-center justify-between border-b pb-2">
                    <h3 class="text-sm font-bold text-[#172B4D] uppercase tracking-wider">
                        Verifikasi Event Marketing
                    </h3>
                    <button @click="isVerifyModalOpen = false" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>

                <div v-if="verifyTargetEvent" class="bg-[#F4F6F9] p-3 rounded text-xs space-y-1 border border-gray-200">
                    <div><span class="text-gray-500">Kode:</span> <span class="font-mono font-bold">{{ verifyTargetEvent.code }}</span></div>
                    <div><span class="text-gray-500">Nama:</span> <span class="font-semibold">{{ verifyTargetEvent.name }}</span></div>
                    <div><span class="text-gray-500">Portofolio:</span> <span class="font-bold uppercase">{{ verifyTargetEvent.portfolio_label }}</span></div>
                </div>

                <!-- Warning if DEMO or UAT -->
                <div v-if="verifyTargetEvent && verifyTargetEvent.portfolio !== 'operational'" class="p-2.5 bg-amber-50 border border-amber-200 rounded text-xs text-amber-800">
                    ⚠️ <strong>Perhatian:</strong> Event dengan portofolio Demo atau UAT tidak dapat diverifikasi sebagai event operasional nyata.
                </div>

                <form @submit.prevent="submitVerification" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status Verifikasi:</label>
                        <select
                            v-model="verifyForm.verification_status"
                            class="w-full text-xs rounded border-gray-300 focus:border-[#172B4D] focus:ring-[#172B4D]"
                        >
                            <option value="belum_diperiksa">Belum Diperiksa</option>
                            <option value="terverifikasi" :disabled="verifyTargetEvent && verifyTargetEvent.portfolio !== 'operational'">
                                Terverifikasi {{ verifyTargetEvent && verifyTargetEvent.portfolio !== 'operational' ? '(Blokir untuk Demo/UAT)' : '' }}
                            </option>
                            <option value="perlu_koreksi">Perlu Koreksi</option>
                        </select>
                        <InputError :message="verifyForm.errors.verification_status" class="mt-1" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            Catatan Verifikasi / Alasan Koreksi:
                            <span v-if="verifyForm.verification_status === 'perlu_koreksi'" class="text-red-600">* (Wajib diisi)</span>
                        </label>
                        <textarea
                            v-model="verifyForm.verification_note"
                            rows="3"
                            placeholder="Tuliskan catatan verifikasi atau alasan perbaikan..."
                            class="w-full text-xs rounded border-gray-300 focus:border-[#172B4D] focus:ring-[#172B4D]"
                        ></textarea>
                        <InputError :message="verifyForm.errors.verification_note" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button
                            type="button"
                            @click="isVerifyModalOpen = false"
                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="verifyForm.processing"
                            class="px-3 py-1.5 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-semibold rounded shadow-xs transition"
                        >
                            Simpan Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
