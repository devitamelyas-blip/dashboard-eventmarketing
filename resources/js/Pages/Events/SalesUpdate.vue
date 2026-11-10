<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    event: Object,
    salesUpdate: Object,
});

const form = useForm({
    revenue: props.salesUpdate?.revenue ?? '',
    invoice: null,
    notes: props.salesUpdate?.notes ?? '',
});

const formatRupiah = (val) => {
    if (val === null || val === undefined || val === '') return 'Belum dilaporkan (NULL)';
    if (isNaN(val)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const revenuePreview = computed(() => formatRupiah(form.revenue));

const submit = () => {
    form.post(route('events.sales.update', props.event.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Pembaruan Omzet H+90 - ${event.code}`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Tahap 3 Khusus Goal Sales
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Pembaruan Omzet H+90
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Pencatatan realisasi omzet penjualan transaksi nyata beserta bukti invoice terverifikasi
                    </p>
                </div>
                <Link
                    :href="route('events.index')"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition"
                >
                    &larr; Kembali ke Daftar
                </Link>
            </div>

            <!-- Ringkasan Info Event & Jadwal H+90 -->
            <div class="bg-[#F4F6F9] border border-gray-200 rounded-lg p-5">
                <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider block mb-3">
                    Informasi Acara & Batas Waktu H+90
                </span>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-gray-500 block text-[10px]">Kode Event:</span>
                        <span class="font-mono font-bold text-[#172B4D] text-sm">{{ event.code }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Nama Event:</span>
                        <span class="font-semibold text-gray-900">{{ event.name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Tanggal Pelaksanaan:</span>
                        <span class="font-semibold text-gray-900">{{ event.event_date_formatted }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Jatuh Tempo H+90:</span>
                        <span class="font-bold text-emerald-800">{{ event.h90_date_formatted }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Biaya Proposal:</span>
                        <span class="font-mono font-bold text-gray-900">{{ formatRupiah(event.proposal_cost) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Target Rasio Omzet:</span>
                        <span class="font-semibold text-gray-800">Min. 10.0x Proposal</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="text-gray-500 block text-[10px]">Ambang Batas Omzet Layak:</span>
                        <span class="font-mono font-black text-indigo-700 text-xs">
                            {{ formatRupiah(event.proposal_cost * 10) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Form Omzet H+90 -->
            <form @submit.prevent="submit" class="bg-white rounded-lg border border-gray-200 p-6 shadow-card space-y-6">
                <div class="border-b border-gray-200 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#172B4D]">
                        Input Omzet & Unggah Berkas Bukti
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Berkas invoice disimpan pada storage privat dan hanya dapat diakses oleh user berwenang.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="revenue" value="Omzet Penjualan Aktual Terealisasi (Rupiah)" class="text-xs font-semibold text-gray-700" />
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                            <TextInput
                                id="revenue"
                                v-model="form.revenue"
                                type="number"
                                min="0"
                                step="1"
                                class="block w-full pl-9 font-mono text-xs font-bold focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Contoh: 150000000"
                            />
                        </div>
                        <div class="mt-1 flex justify-between items-center text-xs">
                            <span class="text-gray-500">Pratinjau Nilai:</span>
                            <span class="font-bold text-gray-900 font-mono">{{ revenuePreview }}</span>
                        </div>
                        <InputError :message="form.errors.revenue" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="invoice" value="Unggah Berkas Bukti Invoice (PDF / JPG / PNG, Maks 5 MB)" class="text-xs font-semibold text-gray-700" />
                        <input
                            id="invoice"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png"
                            @input="form.invoice = $event.target.files[0]"
                            class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                        />
                        <div v-if="salesUpdate?.invoice_path" class="mt-2 text-xs text-emerald-700 flex items-center gap-1 font-semibold">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Berkas bukti invoice telah terunggah sebelumnya.</span>
                        </div>
                        <InputError :message="form.errors.invoice" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="notes" value="Catatan Penjelasan Transaksi Omzet" class="text-xs font-semibold text-gray-700" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded border-gray-300 text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                            placeholder="Tuliskan nomor faktur/invoice atau penjelasan tindak lanjut pelanggan..."
                        ></textarea>
                        <InputError :message="form.errors.notes" class="mt-1" />
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 flex items-center justify-end gap-3">
                    <Link :href="route('events.index')">
                        <button
                            type="button"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition"
                        >
                            Batal
                        </button>
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-bold rounded shadow-xs transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Pembaruan Omzet H+90' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
