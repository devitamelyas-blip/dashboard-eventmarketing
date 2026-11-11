<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-[#F1F5F9] via-[#F8FAFC] to-[#FFFFFF] p-4 sm:p-6"
    >
        <div class="w-full max-w-md">
            <!-- Corporate Brand Header with Official PNG Logo -->
            <div class="flex flex-col items-center mb-6 text-center">
                <Link href="/" class="flex flex-col items-center group focus:outline-none">
                    <!-- Crisp Logo Container -->
                    <div class="p-3 mb-3 bg-white rounded-xl shadow-xs border border-gray-200/80 flex items-center justify-center transition group-hover:shadow-md">
                        <img
                            src="/images/kencana-logo.png"
                            alt="PT Kencana Maju Bersama"
                            style="height: 42px; width: auto; max-width: 220px; object-fit: contain;"
                            class="block select-none"
                        />
                    </div>

                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-red-50 border border-red-200/80 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626] animate-pulse"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#991B1B]">
                            Portal Internal Perusahaan
                        </span>
                    </div>

                    <h1 class="text-xl font-black tracking-tight text-[#0F172A]">
                        Event Marketing Platform
                    </h1>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mt-0.5">
                        PT Kencana Maju Bersama
                    </p>
                </Link>
            </div>

            <!-- Login / Auth Card -->
            <div
                class="w-full overflow-hidden bg-white px-7 py-8 shadow-sm hover:shadow-md transition-shadow border border-gray-200 rounded-xl"
            >
                <slot />
            </div>

            <!-- Corporate Footer Note -->
            <div class="mt-6 text-center text-xs text-gray-500 space-y-1">
                <div>
                    Sistem Enterprise Internal &bull; Hak Akses Terproteksi Multi-Cabang
                </div>
                <div class="text-[11px] text-gray-400">
                    &copy; 2026 PT Kencana Maju Bersama. Seluruh hak cipta dilindungi.
                </div>
            </div>
        </div>
    </div>
</template>
