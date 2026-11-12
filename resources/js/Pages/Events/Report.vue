<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    event: Object,
    report: Object,
});

const form = useForm({
    actual_participants: props.report?.actual_participants ?? '',
    valid_leads: props.report?.valid_leads ?? '',
    actual_team_cost: props.report?.actual_team_cost ?? '',
    attendance_doc: null,
    documentation_doc: null,
    notes: props.report?.notes ?? '',
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

const actualCostPreview = computed(() => formatRupiah(form.actual_team_cost));

const submit = () => {
    form.post(route('events.report.update', props.event.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Laporan Pasca-Event - ${event.code}`" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Tahap 2 Operasional Pasca-Event
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Laporan Realisasi Pasca-Event
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Pencatatan realisasi jumlah peserta, lead valid, dan biaya aktual tim cabang
                    </p>
                </div>
                <Link
                    :href="route('events.index')"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition"
                >
                    &larr; Kembali ke Daftar
                </Link>
            </div>

            <!-- Ringkasan Info Event (Readonly) -->
            <div class="bg-[#F4F6F9] border border-gray-200 rounded-lg p-5">
                <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider block mb-3">
                    Informasi Data Acara Terdaftar
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
                        <span class="text-gray-500 block text-[10px]">Cabang & PIC:</span>
                        <span class="font-semibold text-gray-900">{{ event.branch.name }} ({{ event.pic_name }})</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Tanggal Acara:</span>
                        <span class="font-semibold text-gray-900">{{ event.event_date_formatted }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Tujuan Utama:</span>
                        <span class="font-bold uppercase text-[#172B4D]">{{ event.goal_label }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Target Peserta / Lead:</span>
                        <span class="font-semibold text-gray-900">{{ event.target_participants }} orang / {{ event.target_leads }} lead</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Biaya Proposal:</span>
                        <span class="font-mono font-semibold text-gray-900">{{ formatRupiah(event.proposal_cost) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Budget Tim Rencana:</span>
                        <span class="font-mono font-semibold text-gray-900">{{ formatRupiah(event.team_budget) }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Realisasi -->
            <form @submit.prevent="submit" class="bg-white rounded-lg border border-gray-200 p-6 shadow-card space-y-6">
                <div class="border-b border-gray-200 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#172B4D]">
                        Input Realisasi Lapangan
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">
                        Kosongkan nilai biaya jika belum dilaporkan (NULL). Angka Rp 0 hanya sah bila benar-benar diinput 0.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="actual_participants" value="Peserta Aktual yang Hadir (Orang)" class="text-xs font-semibold text-gray-700" />
                        <TextInput
                            id="actual_participants"
                            v-model="form.actual_participants"
                            type="number"
                            min="0"
                            class="mt-1 block w-full text-xs font-mono focus:border-[#172B4D] focus:ring-[#172B4D]"
                            placeholder="Contoh: 85"
                        />
                        <p class="text-[11px] text-gray-500 mt-1">
                            Target rencana awal: <strong>{{ event.target_participants }}</strong> orang
                        </p>
                        <InputError :message="form.errors.actual_participants" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="valid_leads" value="Lead / Calon Mitra Valid (Orang/Toko)" class="text-xs font-semibold text-gray-700" />
                        <TextInput
                            id="valid_leads"
                            v-model="form.valid_leads"
                            type="number"
                            min="0"
                            class="mt-1 block w-full text-xs font-mono focus:border-[#172B4D] focus:ring-[#172B4D]"
                            placeholder="Contoh: 18"
                        />
                        <p class="text-[11px] text-gray-500 mt-1">
                            Target rencana awal: <strong>{{ event.target_leads }}</strong> lead
                        </p>
                        <InputError :message="form.errors.valid_leads" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="actual_team_cost" value="Realisasi Biaya Aktual Tim Cabang (Rupiah)" class="text-xs font-semibold text-gray-700" />
                        <div class="relative mt-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                            <TextInput
                                id="actual_team_cost"
                                v-model="form.actual_team_cost"
                                type="number"
                                min="0"
                                step="1"
                                class="block w-full pl-9 font-mono text-xs font-bold focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Kosongkan jika belum ada data nota tim"
                            />
                        </div>
                        <div class="mt-1 flex justify-between items-center text-xs">
                            <span class="text-gray-500">Status Pembacaan:</span>
                            <span class="font-bold text-gray-900 font-mono">{{ actualCostPreview }}</span>
                        </div>
                        <InputError :message="form.errors.actual_team_cost" class="mt-1" />
                    </div>

                    <!-- Upload Daftar Hadir / Presensi -->
                    <div>
                        <InputLabel for="attendance_doc" value="Daftar Hadir / Presensi Peserta (PDF / Gambar maks. 10MB)" class="text-xs font-semibold text-gray-700" />
                        <input
                            id="attendance_doc"
                            type="file"
                            accept=".pdf,image/*"
                            @change="form.attendance_doc = $event.target.files[0]"
                            class="mt-1 block w-full text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer border border-gray-300 rounded"
                        />
                        <div v-if="report?.attendance_doc_path" class="mt-1.5 flex items-center gap-1.5 text-xs text-emerald-700 font-medium">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Dokumen presensi tersimpan:</span>
                            <a :href="route('events.attendance.download', event.id)" target="_blank" class="text-blue-600 underline font-semibold hover:text-blue-800">
                                Unduh / Lihat Berkas
                            </a>
                        </div>
                        <InputError :message="form.errors.attendance_doc" class="mt-1" />
                    </div>

                    <!-- Upload Foto Dokumentasi Acara -->
                    <div>
                        <InputLabel for="documentation_doc" value="Foto / Berkas Dokumentasi Acara (PDF / Gambar maks. 10MB)" class="text-xs font-semibold text-gray-700" />
                        <input
                            id="documentation_doc"
                            type="file"
                            accept=".pdf,image/*"
                            @change="form.documentation_doc = $event.target.files[0]"
                            class="mt-1 block w-full text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer border border-gray-300 rounded"
                        />
                        <div v-if="report?.documentation_doc_path" class="mt-1.5 flex items-center gap-1.5 text-xs text-emerald-700 font-medium">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>Dokumentasi tersimpan:</span>
                            <a :href="route('events.documentation.download', event.id)" target="_blank" class="text-blue-600 underline font-semibold hover:text-blue-800">
                                Unduh / Lihat Berkas
                            </a>
                        </div>
                        <InputError :message="form.errors.documentation_doc" class="mt-1" />
                    </div>

                    <div class="md:col-span-2">
                        <InputLabel for="notes" value="Catatan Penjelasan Pelaksanaan Event" class="text-xs font-semibold text-gray-700" />
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded border-gray-300 text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                            placeholder="Catatan kendala di lapangan, antusiasme peserta, perbandingan dengan target, dll."
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
                        <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Laporan Pasca-Event' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
