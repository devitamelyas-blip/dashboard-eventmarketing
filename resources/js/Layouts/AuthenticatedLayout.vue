<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => user.value?.roles || []);
const hasRole = (role) => userRoles.value.includes(role);

const primaryRole = computed(() => {
    if (userRoles.value.includes('admin')) return 'Admin';
    if (userRoles.value.includes('direksi')) return 'Direksi';
    if (userRoles.value.includes('marketing')) return 'Marketing';
    if (userRoles.value.includes('pic')) return 'PIC Cabang';
    return 'User';
});

const branchName = computed(() => user.value?.branch?.name || 'Pusat');
const isMobileSidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-[#F5F6F8] text-[#1F2937] font-sans flex flex-col md:flex-row antialiased">
        <!-- Backdrop for mobile sidebar -->
        <div
            v-if="isMobileSidebarOpen"
            @click="isMobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/50 md:hidden backdrop-blur-sm transition-opacity"
        ></div>

        <!-- SIDEBAR -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-[#172B4D] text-white flex flex-col transition-transform duration-200 ease-in-out md:static md:translate-x-0 shrink-0 shadow-lg md:shadow-none"
            :class="isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand Header -->
            <div class="h-20 flex flex-col justify-center px-4 border-b border-[#243B5A] bg-[#0E1D35]">
                <div class="flex items-center gap-3">
                    <div class="bg-white px-2 py-1.5 rounded-md shadow-xs shrink-0 flex items-center justify-center border border-white/20">
                        <img
                            src="/images/kencana-logo.png"
                            alt="PT Kencana Maju Bersama"
                            class="h-6 w-auto object-contain"
                        />
                    </div>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs font-black tracking-wider text-white uppercase truncate">
                            Event Marketing
                        </span>
                        <span class="text-[10px] text-gray-300 font-medium truncate leading-tight">
                            Management Dashboard
                        </span>
                        <span class="text-[9px] text-[#DC2626] font-semibold truncate tracking-wider">
                            PT Kencana Maju Bersama
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                <!-- Group 1: Inti Sistem & Monitoring -->
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 mb-2">
                        Menu Utama
                    </div>
                    <div class="space-y-1">
                        <!-- 1. Dashboard -->
                        <Link
                            :href="route('dashboard')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold transition group"
                            :class="route().current('dashboard') && !page.url.includes('#')
                                ? 'bg-[#243B5A] text-white border-l-4 border-[#DC2626]'
                                : 'text-gray-300 hover:text-white hover:bg-[#243B5A]/60'"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span>1. Dashboard</span>
                        </Link>

                        <!-- 2. Data Event -->
                        <Link
                            :href="route('events.index')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold transition group"
                            :class="route().current('events.index')
                                ? 'bg-[#243B5A] text-white border-l-4 border-[#DC2626]'
                                : 'text-gray-300 hover:text-white hover:bg-[#243B5A]/60'"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>2. Data Event</span>
                        </Link>

                        <!-- 3. Evaluasi Event -->
                        <a
                            href="/dashboard#evaluation-summary"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold text-gray-300 hover:text-white hover:bg-[#243B5A]/60 transition group"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>3. Evaluasi Event</span>
                        </a>

                        <!-- 4. Monitoring Anggaran -->
                        <a
                            href="/dashboard#budget-monitoring"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold text-gray-300 hover:text-white hover:bg-[#243B5A]/60 transition group"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>4. Monitoring Anggaran</span>
                        </a>

                        <!-- 5. Survei Peserta -->
                        <Link
                            v-if="hasRole('marketing') || hasRole('admin')"
                            :href="route('marketing.links.index')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold transition group"
                            :class="route().current('marketing.links.*')
                                ? 'bg-[#243B5A] text-white border-l-4 border-[#DC2626]'
                                : 'text-gray-300 hover:text-white hover:bg-[#243B5A]/60'"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <span>5. Survei Peserta</span>
                        </Link>

                        <!-- 6. Laporan & Analitik -->
                        <a
                            href="/dashboard#analytics-section"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold text-gray-300 hover:text-white hover:bg-[#243B5A]/60 transition group"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>6. Laporan & Analitik</span>
                        </a>
                    </div>
                </div>

                <!-- Group 2: Operasional PIC -->
                <div v-if="hasRole('pic') || hasRole('admin')">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 mb-2">
                        Operasional Lapangan
                    </div>
                    <div class="space-y-1">
                        <Link
                            :href="route('events.create')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold transition group"
                            :class="route().current('events.create')
                                ? 'bg-[#243B5A] text-white border-l-4 border-[#DC2626]'
                                : 'text-gray-300 hover:text-white hover:bg-[#243B5A]/60'"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Pengajuan Event Baru</span>
                        </Link>
                    </div>
                </div>

                <!-- Group 3: Administrasi -->
                <div v-if="hasRole('admin')">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 px-3 mb-2">
                        7. Pengaturan Sistem
                    </div>
                    <div class="space-y-1">
                        <Link
                            :href="route('admin.users.index')"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-semibold transition group"
                            :class="route().current('admin.users.*')
                                ? 'bg-[#243B5A] text-white border-l-4 border-[#DC2626]'
                                : 'text-gray-300 hover:text-white hover:bg-[#243B5A]/60'"
                        >
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Kelola Pengguna & Hak Akses</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Sidebar Footer Profile -->
            <div class="p-3 border-t border-[#243B5A] bg-[#0E1D35]/50">
                <div class="flex items-center justify-between p-2 rounded-md bg-[#243B5A]/40 text-xs">
                    <div class="min-w-0 pr-2">
                        <div class="font-bold text-white truncate">{{ user.name }}</div>
                        <div class="text-[10px] text-gray-300 truncate">{{ branchName }} • {{ primaryRole }}</div>
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="p-1.5 text-gray-400 hover:text-red-400 rounded transition"
                        title="Keluar"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">
            <!-- TOP NAVIGATION BAR -->
            <header class="h-16 bg-white border-b border-[#E5E7EB] sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 shadow-sm">
                <!-- Left: Hamburger (mobile) + Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <button
                        @click="isMobileSidebarOpen = !isMobileSidebarOpen"
                        class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 md:hidden"
                        aria-label="Toggle Navigation Menu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Mobile Brand Badge -->
                    <div class="flex items-center gap-2 md:hidden">
                        <div class="bg-white px-1.5 py-0.5 rounded border border-gray-200">
                            <img
                                src="/images/kencana-logo.png"
                                alt="Kencana"
                                class="h-4 w-auto object-contain"
                            />
                        </div>
                        <span class="text-xs font-black text-[#172B4D]">Event Marketing</span>
                    </div>

                    <!-- Breadcrumbs -->
                    <div class="hidden md:flex items-center gap-2 text-xs font-medium text-gray-500">
                        <Link :href="route('dashboard')" class="hover:text-[#172B4D] font-bold text-[#172B4D] transition">
                            Dashboard
                        </Link>
                        <span>/</span>
                        <span class="text-gray-500 font-medium">PT Kencana Maju Bersama</span>
                        <span>/</span>
                        <span class="text-gray-900 font-bold truncate">
                            {{ primaryRole }} Workspace
                        </span>
                    </div>
                </div>

                <!-- Right: Branch Pill, Role Badge & User Profile Dropdown -->
                <div class="flex items-center gap-3">
                    <!-- Branch Tag -->
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold bg-[#F4F6F9] text-[#172B4D] border border-gray-200">
                        <svg class="w-3 h-3 mr-1 text-[#DC2626]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ branchName }}
                    </span>

                    <!-- Role Badge Pill -->
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider"
                        :class="{
                            'bg-purple-50 text-purple-700 border border-purple-200': primaryRole === 'Admin',
                            'bg-amber-50 text-amber-800 border border-amber-200': primaryRole === 'Direksi',
                            'bg-emerald-50 text-emerald-800 border border-emerald-200': primaryRole === 'Marketing',
                            'bg-blue-50 text-blue-700 border border-blue-200': primaryRole === 'PIC Cabang'
                        }"
                    >
                        {{ primaryRole }}
                    </span>

                    <!-- Profile Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 p-1 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition text-xs font-semibold"
                                >
                                    <div class="w-7 h-7 rounded-full bg-[#172B4D] text-white flex items-center justify-center font-bold text-xs uppercase">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span class="hidden lg:inline-block max-w-[120px] truncate">{{ user.name }}</span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500">
                                    <div>Masuk sebagai:</div>
                                    <div class="font-bold text-gray-900 truncate">{{ user.email }}</div>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Pengaturan Profil
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Keluar dari Sistem
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Header Slot (Optional) -->
            <div v-if="$slots.header" class="bg-white border-b border-[#E5E7EB] px-4 py-4 sm:px-6">
                <slot name="header" />
            </div>

            <!-- Flash Notifications -->
            <div v-if="$page.props.flash?.success" class="px-4 sm:px-6 pt-4">
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-md flex items-center gap-2 text-xs text-emerald-800 font-semibold shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $page.props.flash.success }}</span>
                </div>
            </div>

            <div v-if="$page.props.flash?.error" class="px-4 sm:px-6 pt-4">
                <div class="p-3 bg-red-50 border border-red-200 rounded-md flex items-center gap-2 text-xs text-red-800 font-semibold shadow-sm">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $page.props.flash.error }}</span>
                </div>
            </div>

            <!-- Main Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
