<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#F5F6F8] p-4 sm:p-6"
    >
        <div class="w-full max-w-md">
            <!-- Corporate Brand Header (Tanpa Logo) -->
            <div class="flex flex-col items-center mb-6 text-center">
                <Link href="/" class="flex flex-col items-center group">
                    <div class="inline-flex items-center gap-2 mb-2 px-3 py-1 rounded-full bg-[#FEF2F2] border border-[#FECACA]">
                        <span class="w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-black uppercase tracking-wider text-[#991B1B]">
                            Portal Internal
                        </span>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-[#172B4D]">
                        Dashboard Event Marketing
                    </h1>
                    <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider mt-1">
                        PT Kencana Maju Bersama
                    </p>
                </Link>
            </div>

            <!-- Login Card -->
            <div
                class="w-full overflow-hidden bg-white px-8 py-8 shadow-card border border-[#E5E7EB] rounded-lg"
            >
                <slot />
            </div>

            <!-- Corporate Footer Note -->
            <div class="mt-6 text-center text-xs text-[#6B7280]">
                Sistem Enterprise Internal &bull; Akses Terbatas Khusus Karyawan Terdaftar
            </div>
        </div>
    </div>
</template>
