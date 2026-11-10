<script setup>
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String,
    event: Object,
    googleUser: Object,
});

const form = useForm({
    email: props.googleUser?.email || '',
    name: props.googleUser?.name || '',
    organization: '',
    phone: '',
    unaided_brand_recall: '',
    audience_relevance: true,
    is_customer_or_partner: false,
    relationship_score: 4,
    awareness_before: 3,
    awareness_after: 5,
});

const submit = () => {
    form.post(route('survey.store', props.token), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Survei Kepuasan Peserta - ${event.name}`" />

    <div class="min-h-screen bg-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header Kartu Acara -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-red-600 px-6 py-4 text-white">
                    <div class="text-xs uppercase tracking-wider font-semibold opacity-90">PT Kencana Maju Bersama</div>
                    <h1 class="text-xl font-extrabold mt-1">Survei Umpan Balik Peserta</h1>
                </div>
                <div class="p-6 bg-white border-b border-gray-100 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ event.name }}</h2>
                        <div class="mt-2 flex flex-wrap gap-4 text-xs text-gray-600">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ event.event_date_formatted }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ event.location }}
                            </span>
                        </div>
                    </div>
                    <img src="/images/kencana-logo.png" alt="Kencana" class="h-8 w-auto object-contain shrink-0 hidden sm:block" />
                </div>

                <!-- Pemberitahuan Privasi -->
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 text-xs text-amber-900 leading-relaxed">
                    <span class="font-bold block mb-1">Pemberitahuan Privasi Peserta:</span>
                    Data identitas (nama, instansi, dan kontak) dipakai oleh Tim Marketing PT Kencana Maju Bersama semata untuk identifikasi peserta dan tindak lanjut kemitraan, dan <strong>tidak pernah ditampilkan</strong> pada Dashboard Evaluasi Direksi.
                </div>
            </div>

            <!-- Google Authentication Status -->
            <div v-if="!googleUser && !form.email" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 text-center space-y-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-50 text-red-600 mb-2">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Masuk dengan Akun Google</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto">
                    Untuk menjamin keaslian respon dan validitas survei, mohon verifikasi dengan akun Google Anda terlebih dahulu.
                </p>
                <div class="pt-2">
                    <a
                        :href="route('survey.google', token)"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition"
                    >
                        <svg class="w-4 h-4 text-red-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.24 10.285V13.4h6.887C18.2 16.1 15.645 18 12.24 18c-3.315 0-6-2.685-6-6s2.685-6 6-6c1.665 0 3.18.675 4.29 1.77l2.25-2.25C17.37 4.095 14.97 3 12.24 3 7.275 3 3.24 7.035 3.24 12s4.035 9 9 9c5.205 0 8.655-3.66 8.655-8.805 0-.6-.06-1.185-.165-1.74H12.24z"/>
                        </svg>
                        Lanjutkan dengan Google
                    </a>
                </div>

                <!-- Fallback Email Input for local development or manual test -->
                <div class="mt-4 pt-4 border-t border-gray-100 text-left">
                    <span class="text-[11px] text-gray-400 block mb-1">Mode Pengujian / Alamat Email:</span>
                    <TextInput
                        v-model="form.email"
                        type="email"
                        placeholder="nama@email.com"
                        class="w-full text-xs"
                    />
                </div>
            </div>

            <!-- Form Survei -->
            <form v-else @submit.prevent="submit" class="bg-white p-6 sm:p-8 rounded-xl shadow-sm border border-gray-200 space-y-8">
                <!-- Status User Terverifikasi -->
                <div class="p-3 bg-emerald-50 rounded-lg flex items-center justify-between text-xs text-emerald-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Terverifikasi sebagai: <strong>{{ form.email }}</strong></span>
                    </div>
                </div>

                <!-- Bagian Identitas -->
                <div class="space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 border-b pb-2">
                        Data Identitas Peserta
                    </h3>

                    <div>
                        <InputLabel for="name" value="Nama Lengkap (Opsional)" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            placeholder="Nama Anda"
                        />
                    </div>

                    <div>
                        <InputLabel for="organization" value="Nama Instansi / Usaha / Toko (Wajib)" />
                        <TextInput
                            id="organization"
                            v-model="form.organization"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            placeholder="Contoh: CV Maju Berkah / Toko Besi Jaya / Pribadi"
                            required
                        />
                        <p class="text-[11px] text-gray-500 mt-1">
                            Jika tidak mewakili instansi, isikan "Pribadi/Umum".
                        </p>
                        <InputError :message="form.errors.organization" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="phone" value="Nomor WhatsApp / Telepon (Opsional)" />
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            placeholder="0812xxxxxxxx"
                        />
                    </div>
                </div>

                <!-- Pertanyaan 1: RECALL MEREK TANPA BANTUAN (RECALL_V2) HARUS DI ATAS -->
                <div class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-bold text-gray-900">
                        1. Sebutkan merek rangka atap baja ringan atau genteng metal yang pertama kali Anda ingat ketika mendengar konstruksi atap:
                    </label>
                    <p class="text-xs text-gray-500 italic">
                        * Tuliskan merek pertama yang muncul di ingatan Anda secara spontan tanpa petunjuk.
                    </p>
                    <TextInput
                        v-model="form.unaided_brand_recall"
                        type="text"
                        class="block w-full text-sm font-medium"
                        placeholder="Tuliskan nama merek di sini..."
                    />
                    <InputError :message="form.errors.unaided_brand_recall" class="mt-1" />
                </div>

                <!-- Pertanyaan 2: Relevansi Audiens -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-900">
                        2. Apakah profil pekerjaan, usaha, atau aktivitas Anda relevan dengan industri konstruksi / bahan bangunan / renovasi rumah?
                    </label>
                    <div class="flex gap-6 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.audience_relevance" :value="true" class="text-red-600 focus:ring-red-500" />
                            <span class="text-sm font-medium text-gray-800">Ya, Relevan (Kontraktor, Aplikator, Toko Bangunan, Developer, dll.)</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.audience_relevance" :value="false" class="text-red-600 focus:ring-red-500" />
                            <span class="text-sm font-medium text-gray-800">Tidak Relevan</span>
                        </label>
                    </div>
                </div>

                <!-- Pertanyaan 3: Kategori Customer / Partner -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-900">
                        3. Apakah saat ini Anda atau tempat usaha Anda merupakan pelanggan aktif, mitra aplikator, atau toko rekanan PT Kencana Maju Bersama?
                    </label>
                    <div class="flex gap-6 mt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.is_customer_or_partner" :value="true" class="text-red-600 focus:ring-red-500" />
                            <span class="text-sm font-medium text-gray-800">Ya, Pelanggan / Mitra / Toko Rekanan Saat Ini</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" v-model="form.is_customer_or_partner" :value="false" class="text-red-600 focus:ring-red-500" />
                            <span class="text-sm font-medium text-gray-800">Bukan / Calon Mitra Baru</span>
                        </label>
                    </div>
                </div>

                <!-- Pertanyaan 4: Skor Hubungan (1-5) -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-900">
                        4. Seberapa puas Anda dengan kualitas layanan, komunikasi, dan hubungan kerja sama dengan Kencana?
                    </label>
                    <p class="text-xs text-gray-500">Skala 1 (Sangat Tidak Puas) sampai 5 (Sangat Puas)</p>
                    <div class="flex items-center gap-3 mt-2">
                        <label v-for="score in [1, 2, 3, 4, 5]" :key="score" class="flex flex-col items-center gap-1 cursor-pointer">
                            <input type="radio" v-model="form.relationship_score" :value="score" class="text-red-600 focus:ring-red-500" />
                            <span class="text-xs font-bold text-gray-700">{{ score }}</span>
                        </label>
                    </div>
                </div>

                <!-- Pertanyaan 5: Retrospective Awareness (Sebelum & Sesudah) -->
                <div class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-900">
                        5. Pengetahuan Produk Kencana (Retrospective Awareness)
                    </h4>
                    <p class="text-xs text-gray-500">
                        Beri nilai pemahaman Anda mengenai varian produk, sertifikasi SNI, dan keunggulan Kencana:
                    </p>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            A. Pemahaman Anda SEBELUM menghadiri acara:
                        </label>
                        <div class="flex items-center gap-4">
                            <label v-for="score in [1, 2, 3, 4, 5]" :key="score" class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" v-model="form.awareness_before" :value="score" class="text-red-600 focus:ring-red-500" />
                                <span class="text-xs font-semibold text-gray-700">{{ score }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            B. Pemahaman Anda SESUDAH menghadiri acara:
                        </label>
                        <div class="flex items-center gap-4">
                            <label v-for="score in [1, 2, 3, 4, 5]" :key="score" class="flex items-center gap-1 cursor-pointer">
                                <input type="radio" v-model="form.awareness_after" :value="score" class="text-red-600 focus:ring-red-500" />
                                <span class="text-xs font-semibold text-gray-700">{{ score }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t flex justify-end">
                    <PrimaryButton
                        :disabled="form.processing"
                        class="bg-red-600 hover:bg-red-700 w-full sm:w-auto py-3 px-8 text-center justify-center font-bold"
                    >
                        {{ form.processing ? 'Mengirimkan Respon...' : 'Kirim Respon Survei' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
