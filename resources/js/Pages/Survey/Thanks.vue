<script setup>
import { Head } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    eventName: String,
});
</script>

<template>
    <Head title="Terima Kasih - Survei PT Kencana Maju Bersama" />

    <div class="min-h-screen bg-gradient-to-b from-[#F1F5F9] via-[#F8FAFC] to-[#FFFFFF] flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-md border border-gray-200/90 p-8 text-center space-y-6">
            <!-- Brand Logo -->
            <div class="flex justify-center">
                <div class="p-2.5 bg-white rounded-xl shadow-2xs border border-gray-100 flex items-center justify-center">
                    <ApplicationLogo class="h-10 w-auto" alt="PT Kencana Maju Bersama" />
                </div>
            </div>

            <!-- Success Icon Badge -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-2xs">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">
                    Terima Kasih!
                </h1>
                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">
                    Respon Survei Berhasil Disimpan
                </p>
            </div>

            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">
                Tanggapan Anda untuk kegiatan <strong class="text-gray-900">{{ eventName }}</strong> telah tercatat secara aman dalam sistem evaluasi kami.
            </p>

            <p class="text-xs text-gray-400 leading-normal">
                Masukan Anda sangat berharga bagi peningkatan mutu layanan, kemitraan, dan inovasi baja ringan PT Kencana Maju Bersama.
            </p>

            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href="https://kencana.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-bold rounded-lg shadow-2xs transition"
                >
                    <span>Kunjungi Website Resmi Kencana</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>
</template>
