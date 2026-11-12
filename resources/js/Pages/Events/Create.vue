<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    branches: Array,
    userBranchId: [Number, String],
    isPic: Boolean,
    userName: String,
    eventTypes: Array,
    goals: Array,
});

const form = useForm({
    code: '',
    name: '',
    type: 'gathering_internal',
    event_date: '',
    branch_id: props.userBranchId || (props.branches[0]?.id ?? ''),
    location: '',
    pic_name: props.userName || '',
    goal: 'sales',
    theme_product: '',
    target_participants: 50,
    target_leads: 10,
    estimated_relevance_pct: 75,
    estimated_relevance_basis: '',
    proposal_cost: '',
    team_budget: '',
    proposal_doc: null,
});

const formatRupiah = (val) => {
    if (!val || isNaN(val)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const proposalCostPreview = computed(() => formatRupiah(form.proposal_cost));
const teamBudgetPreview = computed(() => formatRupiah(form.team_budget));

const totalBudgetPreview = computed(() => {
    const p = parseFloat(form.proposal_cost) || 0;
    const t = parseFloat(form.team_budget) || 0;
    return formatRupiah(p + t);
});

const submit = () => {
    form.post(route('events.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengajuan Event Baru - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Tahap 1 Operasional
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Pengajuan Event Baru
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Formulir rencana kegiatan dan alokasi anggaran pra-event
                    </p>
                </div>
                <Link
                    :href="route('events.index')"
                    class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded transition"
                >
                    &larr; Kembali ke Daftar
                </Link>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-lg border border-gray-200 p-6 shadow-card space-y-6">
                <!-- Bagian 1: Identitas & Jadwal Event -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200 pb-2 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-[#172B4D] text-white flex items-center justify-center text-[10px] font-bold">1</span>
                        Identitas & Jadwal Event
                    </h3>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="code" value="Kode Event (Unik, Awalan DEMO- untuk simulasi)" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="code"
                                v-model="form.code"
                                type="text"
                                class="mt-1 block w-full uppercase font-mono font-bold text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Contoh: SBY-GATH-2026-01"
                                required
                                @input="form.code = form.code.toUpperCase()"
                            />
                            <p class="text-[11px] text-gray-400 mt-1">Otomatis dinormalisasi uppercase dan trim.</p>
                            <InputError :message="form.errors.code" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Nama Event" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Contoh: Gathering Aplikator & Toko Bangunan Kencana"
                                required
                            />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="type" value="Jenis Event" class="text-xs font-semibold text-gray-700" />
                            <select
                                id="type"
                                v-model="form.type"
                                class="mt-1 block w-full rounded border-gray-300 text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                required
                            >
                                <option v-for="t in eventTypes" :key="t.value" :value="t.value">
                                    {{ t.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="event_date" value="Tanggal Pelaksanaan" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="event_date"
                                v-model="form.event_date"
                                type="date"
                                class="mt-1 block w-full text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                required
                            />
                            <InputError :message="form.errors.event_date" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="branch_id" value="Cabang Penyelenggara" class="text-xs font-semibold text-gray-700" />
                            <select
                                id="branch_id"
                                v-model="form.branch_id"
                                class="mt-1 block w-full rounded border-gray-300 text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                :disabled="isPic"
                                required
                            >
                                <option v-for="b in branches" :key="b.id" :value="b.id">
                                    {{ b.name }} ({{ b.code }})
                                </option>
                            </select>
                            <InputError :message="form.errors.branch_id" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="location" value="Lokasi / Venue Event" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="location"
                                v-model="form.location"
                                type="text"
                                class="mt-1 block w-full text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Contoh: Hotel Mercure Grand Mirama, Surabaya"
                                required
                            />
                            <InputError :message="form.errors.location" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="pic_name" value="Nama PIC / Kepala Cabang" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="pic_name"
                                v-model="form.pic_name"
                                type="text"
                                class="mt-1 block w-full text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                required
                            />
                            <InputError :message="form.errors.pic_name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="theme_product" value="Tema Acara / Produk Unggulan" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="theme_product"
                                v-model="form.theme_product"
                                type="text"
                                class="mt-1 block w-full text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                placeholder="Contoh: Genteng Metal Kencana & Baja Ringan Sni"
                                required
                            />
                            <InputError :message="form.errors.theme_product" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Tujuan Utama & Target Kinerja -->
                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200 pb-2 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-[#172B4D] text-white flex items-center justify-center text-[10px] font-bold">2</span>
                        Tujuan Utama & Target Kinerja
                    </h3>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-3">
                            <InputLabel value="Tujuan Utama Event (Pilih Tepat Satu)" class="text-xs font-semibold text-gray-700 mb-1" />
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label
                                    v-for="g in goals"
                                    :key="g.value"
                                    class="border rounded-lg p-3 cursor-pointer transition flex items-start gap-2.5"
                                    :class="form.goal === g.value ? 'border-[#172B4D] bg-[#F4F6F9] text-[#172B4D] font-bold ring-1 ring-[#172B4D]' : 'border-gray-200 hover:bg-gray-50 text-gray-600'"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.goal"
                                        :value="g.value"
                                        class="mt-0.5 text-[#172B4D] focus:ring-[#172B4D]"
                                    />
                                    <div>
                                        <span class="block text-xs uppercase tracking-wider font-extrabold">{{ g.label }}</span>
                                        <span class="text-[11px] font-normal text-gray-500 mt-0.5 block leading-tight">
                                            {{ g.value === 'sales' ? 'Evaluasi omzet H+90 (rasio min. 10x proposal)' : (g.value === 'awareness' ? 'Recall merek tanpa bantuan & uplift' : 'Hubungan mitra & loyalitas') }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <InputError :message="form.errors.goal" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="target_participants" value="Target Peserta (Orang)" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="target_participants"
                                v-model="form.target_participants"
                                type="number"
                                min="1"
                                class="mt-1 block w-full font-mono text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                required
                            />
                            <InputError :message="form.errors.target_participants" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="target_leads" value="Target Lead Valid (Orang/Toko)" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="target_leads"
                                v-model="form.target_leads"
                                type="number"
                                min="0"
                                class="mt-1 block w-full font-mono text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                                required
                            />
                            <InputError :message="form.errors.target_leads" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="estimated_relevance_pct" value="Estimasi Relevansi Audiens (%)" class="text-xs font-semibold text-gray-700" />
                            <TextInput
                                id="estimated_relevance_pct"
                                v-model="form.estimated_relevance_pct"
                                type="number"
                                min="0"
                                max="100"
                                class="mt-1 block w-full font-mono text-xs focus:border-[#172B4D] focus:ring-[#172B4D]"
                            />
                            <InputError :message="form.errors.estimated_relevance_pct" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Alokasi Anggaran -->
                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-700 border-b border-gray-200 pb-2 flex items-center gap-2">
                        <span class="w-5 h-5 rounded bg-[#172B4D] text-white flex items-center justify-center text-[10px] font-bold">3</span>
                        Rencana Anggaran & Biaya (Rupiah Murni)
                    </h3>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="proposal_cost" value="Biaya Proposal (DP, Tempat, EO, Sewa)" class="text-xs font-semibold text-gray-700" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                                <TextInput
                                    id="proposal_cost"
                                    v-model="form.proposal_cost"
                                    type="number"
                                    min="0"
                                    class="block w-full pl-9 font-mono text-xs font-bold focus:border-[#172B4D] focus:ring-[#172B4D]"
                                    placeholder="10000000"
                                    required
                                />
                            </div>
                            <span class="text-[11px] text-gray-500 font-medium block mt-1">
                                Pratinjau: <strong class="text-gray-900 font-mono">{{ proposalCostPreview }}</strong>
                            </span>
                            <InputError :message="form.errors.proposal_cost" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="team_budget" value="Budget Operasional Tim Cabang" class="text-xs font-semibold text-gray-700" />
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs text-gray-500 font-bold">Rp</span>
                                <TextInput
                                    id="team_budget"
                                    v-model="form.team_budget"
                                    type="number"
                                    min="0"
                                    class="block w-full pl-9 font-mono text-xs font-bold focus:border-[#172B4D] focus:ring-[#172B4D]"
                                    placeholder="2000000"
                                    required
                                />
                            </div>
                            <span class="text-[11px] text-gray-500 font-medium block mt-1">
                                Pratinjau: <strong class="text-gray-900 font-mono">{{ teamBudgetPreview }}</strong>
                            </span>
                            <InputError :message="form.errors.team_budget" class="mt-1" />
                        </div>
                    </div>

                    <!-- Upload Dokumen Proposal -->
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <InputLabel for="proposal_doc" value="Lampiran Dokumen Proposal / Rundown Acara (Opsional, PDF maks. 10MB)" class="text-xs font-semibold text-gray-700" />
                        <input
                            id="proposal_doc"
                            type="file"
                            accept=".pdf,application/pdf"
                            @change="form.proposal_doc = $event.target.files[0]"
                            class="mt-1 block w-full text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer border border-gray-300 rounded"
                        />
                        <InputError :message="form.errors.proposal_doc" class="mt-1" />
                        <p class="text-[10px] text-gray-500 mt-1">Unggah dokumen rancangan proposal lengkap untuk ditinjau oleh Tim Marketing.</p>
                    </div>

                    <!-- Total Preview Banner -->
                    <div class="mt-4 p-3 rounded bg-[#F4F6F9] border border-gray-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Total Rencana Anggaran (Budget)</span>
                            <div class="text-[11px] text-gray-500">Biaya Proposal + Budget Tim Cabang</div>
                        </div>
                        <div class="text-lg font-black text-[#172B4D] font-mono">
                            {{ totalBudgetPreview }}
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
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
                        class="px-4 py-2 bg-[#DC2626] hover:bg-[#B91C1C] text-white text-xs font-bold rounded shadow-xs transition flex items-center gap-2 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Menyimpan...' : 'Ajukan Event Sekarang' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
