<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col lg:flex-row antialiased">
        <!-- ════════════════════════════════════════════════════════════════
             LEFT PANEL: BRAND & ENTERPRISE SHOWCASE (Visible on Desktop lg+)
             ════════════════════════════════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[48%] bg-[#0A1120] text-white flex-col justify-between p-12 xl:p-16 relative overflow-hidden shrink-0 border-r border-slate-800/80">
            <!-- Ambient Background Glow Accents -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-red-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Subtle Grid Pattern Overlay -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 24px 24px;"></div>

            <!-- Top: Brand Header -->
            <div class="relative z-10">
                <Link href="/" class="inline-flex items-center gap-3 group focus:outline-none">
                    <img
                        src="/images/kencana-logo-white.png"
                        alt="PT Kencana Maju Bersama"
                        style="height: 36px; width: auto; max-width: 210px; object-fit: contain;"
                        class="select-none transition-transform group-hover:scale-[1.02]"
                    />
                </Link>
                <div class="flex items-center gap-2 mt-3 text-xs text-slate-400 font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="tracking-wide uppercase text-[10px] font-bold text-slate-300">
                        Enterprise Event Marketing System
                    </span>
                </div>
            </div>

            <!-- Middle: Value Proposition & Features -->
            <div class="relative z-10 my-auto py-10 space-y-8 max-w-lg">
                <div class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-950/60 border border-red-500/30 text-red-300 text-[11px] font-bold uppercase tracking-wider shadow-sm">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Platform Evaluasi Terpadu
                    </div>
                    <h2 class="text-3xl xl:text-4xl font-black text-white tracking-tight leading-tight">
                        Transformasi Tata Kelola & Evaluasi Event Pemasaran
                    </h2>
                    <p class="text-sm text-slate-400 leading-relaxed font-normal">
                        Sistem terintegrasi PT Kencana Maju Bersama untuk mengelola siklus event, survei peserta berbasis QR code, verifikasi data marketing, dan kalkulasi KPI otomatis multi-cabang.
                    </p>
                </div>

                <!-- Feature Highlights -->
                <div class="space-y-3.5">
                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-xs">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white tracking-wide">Kalkulasi KPI Otomatis & Objektif</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                Evaluasi deterministik untuk Sales ROI, Brand Awareness, Kepuasan Mitra, dan Varians Biaya tanpa risiko rumus spreadsheet rusak.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-xs">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white tracking-wide">Isolasi Data Cabang Terproteksi</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                Hak akses berbasis peran (Admin, Direksi, Marketing, PIC Cabang) dengan penyimpanan dokumen privat dan audit trail Spatie.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-xl bg-white/[0.03] border border-white/[0.06] backdrop-blur-xs">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-white tracking-wide">Rekonsiliasi Omzet H+90 Valid</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                Pengingat otomatis, verifikasi unggahan faktur invoice, dan penetapan keputusan resmi direksi dalam satu alur terpusat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom: Branch Network Footer -->
            <div class="relative z-10 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-[11px] text-slate-400">
                <span class="font-semibold text-slate-300">PT Kencana Maju Bersama</span>
                <span class="text-slate-400 text-[10px]">Pusat Surabaya & 9 Cabang Nasional</span>
            </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════════
             RIGHT PANEL: LOGIN / AUTH FORM CONTAINER
             ════════════════════════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col justify-center px-4 py-8 sm:px-8 sm:py-12 lg:px-12 xl:px-16 min-h-screen">
            <div class="w-full max-w-md mx-auto">
                <!-- Mobile Brand Header (Visible only on < lg) -->
                <div class="lg:hidden flex flex-col items-center text-center mb-8">
                    <img
                        src="/images/kencana-logo.png"
                        alt="PT Kencana Maju Bersama"
                        style="height: 40px; width: auto; max-width: 200px; object-fit: contain;"
                        class="block mb-2"
                    />
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-red-50 text-red-700 text-[10px] font-bold uppercase tracking-wider border border-red-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                        Event Marketing Platform
                    </div>
                </div>

                <!-- Form Card / Slot -->
                <slot />

                <!-- Bottom Security Note -->
                <div class="mt-8 text-center text-xs text-slate-400 space-y-1">
                    <div class="flex items-center justify-center gap-1.5 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Sistem Keamanan Terproteksi &bull; Enkripsi Sesi Aktif</span>
                    </div>
                    <div class="text-[10px] text-slate-400">
                        &copy; 2026 PT Kencana Maju Bersama. Hak Cipta Dilindungi.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
