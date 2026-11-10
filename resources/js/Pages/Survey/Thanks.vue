<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    eventName: String,
});
</script>

<template>
    <Head title="Terima Kasih - Survei Kencana" />

    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-gray-200 p-8 text-center space-y-5">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900">
                Terima Kasih!
            </h1>

            <p class="text-sm text-gray-600 leading-relaxed">
                Respon survei Anda untuk kegiatan <strong class="text-gray-800">{{ eventName }}</strong> telah berhasil kami terima.
            </p>

            <p class="text-xs text-gray-400">
                Masukan Anda sangat berharga bagi peningkatan mutu kemitraan dan produk PT Kencana Maju Bersama ke depan.
            </p>

            <div class="pt-4 border-t border-gray-100">
                <a
                    href="https://kencana.id"
                    target="_blank"
                    class="text-xs text-red-600 font-bold hover:underline"
                >
                    Kunjungi Website KENCANA &rarr;
                </a>
            </div>
        </div>
    </div>
</template>
