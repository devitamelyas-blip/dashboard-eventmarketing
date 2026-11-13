<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';

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

const roleColor = computed(() => {
    const r = primaryRole.value;
    if (r === 'Admin') return 'bg-purple-100 text-purple-700 ring-purple-200';
    if (r === 'Direksi') return 'bg-amber-100 text-amber-800 ring-amber-200';
    if (r === 'Marketing') return 'bg-emerald-100 text-emerald-800 ring-emerald-200';
    if (r === 'PIC Cabang') return 'bg-blue-100 text-blue-700 ring-blue-200';
    return 'bg-slate-100 text-slate-700 ring-slate-200';
});

const branchName = computed(() => user.value?.branch?.name || 'Kantor Pusat');
const isMobileSidebarOpen = ref(false);
const activeSection = ref('');

// Notifications
const notifications = computed(() => page.props.notifications);
const unreadCount = computed(() => notifications.value?.unread_count || 0);
const latestNotifications = computed(() => notifications.value?.latest || []);
const isNotificationOpen = ref(false);

const markAsRead = (n) => {
    if (!n.read_at) {
        router.post(route('notifications.read', n.id), {}, { preserveScroll: true, preserveState: true });
    }
    if (n.url) {
        isNotificationOpen.value = false;
        router.visit(n.url);
    }
};

const markAllAsRead = () => {
    router.post(route('notifications.readAll'), {}, { preserveScroll: true, preserveState: true });
};

const closeSidebar = () => { isMobileSidebarOpen.value = false; };

// Close notification dropdown on outside click
const notifRef = ref(null);
const handleClickOutside = (e) => {
    if (notifRef.value && !notifRef.value.contains(e.target)) {
        isNotificationOpen.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

const highlightAndScroll = (sectionId) => {
    const el = document.getElementById(sectionId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        el.classList.add('ring-4', 'ring-red-500/30', 'ring-offset-2', 'rounded-xl');
        setTimeout(() => el.classList.remove('ring-4', 'ring-red-500/30', 'ring-offset-2', 'rounded-xl'), 2000);
        return true;
    }
    return false;
};

const navigateToSection = (sectionId) => {
    closeSidebar();
    activeSection.value = sectionId;
    if (route().current('dashboard')) {
        window.history.replaceState(null, '', `#${sectionId}`);
        if (!highlightAndScroll(sectionId)) setTimeout(() => highlightAndScroll(sectionId), 150);
        return;
    }
    router.visit(route('dashboard') + `#${sectionId}`, {
        preserveState: false,
        onFinish: () => setTimeout(() => highlightAndScroll(sectionId), 300),
    });
};

const goToDashboardHome = () => {
    closeSidebar();
    activeSection.value = '';
    if (route().current('dashboard')) {
        window.history.replaceState(null, '', '/dashboard');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        router.visit(route('dashboard'));
    }
};

onMounted(() => {
    if (window.location.hash) {
        const hashId = window.location.hash.replace('#', '');
        activeSection.value = hashId;
        setTimeout(() => highlightAndScroll(hashId), 250);
    }
});

const navItems = computed(() => [
    {
        group: 'Menu Navigasi',
        items: [
            {
                type: 'button',
                label: 'Dashboard Eksekutif',
                icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                active: route().current('dashboard') && !activeSection.value,
                onClick: goToDashboardHome,
            },
            {
                type: 'link',
                label: 'Data Event',
                icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                active: route().current('events.index'),
                href: route('events.index'),
            },
            {
                type: 'button',
                label: 'Evaluasi Event & KPI',
                icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                active: route().current('dashboard') && activeSection.value === 'evaluation-summary',
                onClick: () => navigateToSection('evaluation-summary'),
            },
            {
                type: 'button',
                label: 'Monitoring Anggaran',
                icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                active: route().current('dashboard') && activeSection.value === 'budget-monitoring',
                onClick: () => navigateToSection('budget-monitoring'),
            },
            ...(hasRole('marketing') || hasRole('admin') ? [{
                type: 'link',
                label: 'Survei & QR Code',
                icon: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
                active: route().current('marketing.links.*'),
                href: route('marketing.links.index'),
            }] : []),
        ]
    },
    ...(hasRole('pic') || hasRole('admin') ? [{
        group: 'Operasional Cabang',
        items: [
            {
                type: 'link',
                label: 'Pengajuan Event Baru',
                icon: 'M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z',
                active: route().current('events.create'),
                href: route('events.create'),
            },
        ]
    }] : []),
    ...(hasRole('admin') ? [{
        group: 'Administrasi Sistem',
        items: [
            {
                type: 'link',
                label: 'Kelola Pengguna & Akses',
                icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                active: route().current('admin.users.*'),
                href: route('admin.users.index'),
            },
            {
                type: 'link',
                label: 'Kelola Kantor Cabang',
                icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                active: route().current('admin.branches.*'),
                href: route('admin.branches.index'),
            },
            {
                type: 'link',
                label: 'Log Aktivitas Sistem',
                icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                active: route().current('admin.activity-logs.*'),
                href: route('admin.activity-logs.index'),
            },
        ]
    }] : []),
]);
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 font-sans flex antialiased">

        <!-- Mobile backdrop -->
        <div
            v-if="isMobileSidebarOpen"
            @click="closeSidebar"
            class="fixed inset-0 z-40 bg-black/50 md:hidden backdrop-blur-sm"
        ></div>

        <!-- ═══════════════════════════════════════════════
             SIDEBAR
        ═══════════════════════════════════════════════ -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-60 bg-[#0B1526] text-white flex flex-col transition-transform duration-200 ease-out md:static md:translate-x-0 shrink-0"
            :class="isMobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand -->
            <div class="h-16 flex items-center px-4 border-b border-white/5">
                <button type="button" @click="goToDashboardHome" class="flex items-center gap-3 group focus:outline-none w-full">
                    <div class="w-7 h-7 rounded-lg bg-red-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-black text-white leading-tight truncate">Event Marketing</div>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[9px] text-slate-400 font-semibold uppercase tracking-widest">PT Kencana Maju Bersama</span>
                        </div>
                    </div>
                </button>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-4 px-3">
                <div v-for="section in navItems" :key="section.group" class="mb-5">
                    <div class="text-[9px] font-extrabold uppercase tracking-widest text-slate-500 px-2 mb-1.5">
                        {{ section.group }}
                    </div>
                    <div class="space-y-0.5">
                        <template v-for="item in section.items" :key="item.label">
                            <!-- Button nav item -->
                            <button
                                v-if="item.type === 'button'"
                                type="button"
                                @click="item.onClick"
                                class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all group text-left"
                                :class="item.active
                                    ? 'bg-white/10 text-white'
                                    : 'text-slate-400 hover:text-white hover:bg-white/5'"
                            >
                                <span
                                    class="w-1 h-4 rounded-full shrink-0 transition-all"
                                    :class="item.active ? 'bg-red-500' : 'bg-transparent group-hover:bg-white/20'"
                                ></span>
                                <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="item.active ? 'text-white' : 'text-slate-500 group-hover:text-slate-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span>{{ item.label }}</span>
                            </button>

                            <!-- Link nav item -->
                            <Link
                                v-else
                                :href="item.href"
                                @click="closeSidebar(); activeSection = '';"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[11px] font-semibold transition-all group"
                                :class="item.active
                                    ? 'bg-white/10 text-white'
                                    : 'text-slate-400 hover:text-white hover:bg-white/5'"
                            >
                                <span
                                    class="w-1 h-4 rounded-full shrink-0 transition-all"
                                    :class="item.active ? 'bg-red-500' : 'bg-transparent group-hover:bg-white/20'"
                                ></span>
                                <svg class="w-3.5 h-3.5 shrink-0 transition-colors" :class="item.active ? 'text-white' : 'text-slate-500 group-hover:text-slate-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                                </svg>
                                <span>{{ item.label }}</span>
                            </Link>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Footer Profile -->
            <div class="p-3 border-t border-white/5">
                <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg bg-white/5">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center font-black text-xs text-white shrink-0 uppercase">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-white truncate leading-tight">{{ user.name }}</div>
                        <div class="text-[10px] text-slate-400 truncate">{{ branchName }}</div>
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="p-1 text-slate-500 hover:text-red-400 rounded-md hover:bg-white/10 transition shrink-0"
                        title="Keluar dari Sistem"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════════
             MAIN CONTENT
        ═══════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

            <!-- TOP BAR -->
            <header class="h-14 bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-5 shadow-sm">
                <!-- Left -->
                <div class="flex items-center gap-3">
                    <!-- Hamburger -->
                    <button
                        @click="isMobileSidebarOpen = !isMobileSidebarOpen"
                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 md:hidden transition"
                        aria-label="Toggle Navigation"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Mobile brand -->
                    <div class="flex items-center gap-2 md:hidden">
                        <div class="w-6 h-6 rounded-md bg-red-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-sm font-black text-slate-900">Event Marketing</span>
                    </div>

                    <!-- Breadcrumb (desktop) -->
                    <nav class="hidden md:flex items-center gap-1.5 text-xs text-slate-400 font-medium">
                        <Link :href="route('dashboard')" class="text-slate-900 font-bold hover:text-red-600 transition">Dashboard</Link>
                        <span class="text-slate-200">/</span>
                        <span>PT Kencana Maju Bersama</span>
                        <span class="text-slate-200">/</span>
                        <span class="text-slate-700 font-semibold">{{ primaryRole }} Workspace</span>
                    </nav>
                </div>

                <!-- Right -->
                <div class="flex items-center gap-2">
                    <!-- Role Badge (desktop) -->
                    <span
                        class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider ring-1"
                        :class="roleColor"
                    >
                        {{ primaryRole }}
                    </span>

                    <!-- Branch (desktop) -->
                    <span class="hidden lg:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-50 text-slate-700 ring-1 ring-slate-200">
                        <svg class="w-3 h-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ branchName }}
                    </span>

                    <!-- Notification Bell -->
                    <div class="relative" ref="notifRef">
                        <button
                            type="button"
                            @click.stop="isNotificationOpen = !isNotificationOpen"
                            class="relative p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                            title="Notifikasi"
                        >
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span
                                v-if="unreadCount > 0"
                                class="absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-black text-white ring-2 ring-white"
                            >
                                {{ unreadCount > 9 ? '9+' : unreadCount }}
                            </span>
                        </button>

                        <!-- Notification Dropdown -->
                        <Transition
                            enter-active-class="transition ease-out duration-150"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-100"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div
                                v-if="isNotificationOpen"
                                class="absolute right-0 mt-2 w-80 sm:w-96 rounded-xl bg-white shadow-xl border border-slate-200 z-50 overflow-hidden"
                                @click.stop
                            >
                                <!-- Notif Header -->
                                <div class="px-4 py-3 flex items-center justify-between border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900">Notifikasi</span>
                                        <span v-if="unreadCount > 0" class="px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-red-100 text-red-700">
                                            {{ unreadCount }} baru
                                        </span>
                                    </div>
                                    <button
                                        v-if="unreadCount > 0"
                                        type="button"
                                        @click="markAllAsRead"
                                        class="text-[11px] text-slate-400 hover:text-slate-700 font-semibold transition"
                                    >
                                        Tandai semua dibaca
                                    </button>
                                </div>

                                <!-- Notif List -->
                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                                    <div
                                        v-for="n in latestNotifications"
                                        :key="n.id"
                                        @click="markAsRead(n)"
                                        class="p-3.5 text-xs transition cursor-pointer hover:bg-slate-50 flex items-start gap-3"
                                        :class="!n.read_at ? 'bg-blue-50/40' : ''"
                                    >
                                        <span
                                            class="mt-1 w-2 h-2 rounded-full shrink-0"
                                            :class="{
                                                'bg-emerald-500': n.type === 'success',
                                                'bg-amber-500': n.type === 'warning',
                                                'bg-red-500': n.type === 'danger',
                                                'bg-blue-500': n.type === 'info' || !n.type,
                                            }"
                                        ></span>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="font-bold text-slate-900 truncate">{{ n.title }}</span>
                                                <span class="text-[10px] text-slate-400 shrink-0">{{ n.created_at_human }}</span>
                                            </div>
                                            <p class="text-slate-500 mt-0.5 line-clamp-2 leading-relaxed">{{ n.message }}</p>
                                        </div>
                                    </div>
                                    <div v-if="!latestNotifications?.length" class="p-8 text-center text-slate-400 text-xs">
                                        <svg class="w-8 h-8 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        Tidak ada notifikasi
                                    </div>
                                </div>

                                <div class="p-2 bg-slate-50 border-t border-slate-100 text-center">
                                    <button
                                        type="button"
                                        @click="isNotificationOpen = false"
                                        class="text-[11px] text-slate-400 hover:text-slate-700 font-semibold transition"
                                    >
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- Profile Dropdown -->
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center gap-2 px-2 py-1.5 rounded-lg text-slate-700 hover:bg-slate-100 transition"
                            >
                                <div class="w-6 h-6 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-[10px] uppercase">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <span class="hidden lg:block text-xs font-semibold max-w-[100px] truncate">{{ user.name }}</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <div class="px-4 py-2.5 border-b border-slate-100">
                                <div class="text-[10px] text-slate-400">Masuk sebagai</div>
                                <div class="text-xs font-bold text-slate-900 truncate mt-0.5">{{ user.email }}</div>
                            </div>
                            <DropdownLink :href="route('profile.edit')">Pengaturan Profil</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Keluar dari Sistem</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Page Header Slot -->
            <div v-if="$slots.header" class="bg-white border-b border-slate-200 px-5 py-4">
                <slot name="header" />
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="px-4 sm:px-5 pt-4">
                <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center gap-3 text-xs text-emerald-800 font-semibold shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $page.props.flash.success }}
                </div>
            </div>
            <div v-if="$page.props.flash?.error" class="px-4 sm:px-5 pt-4">
                <div class="p-3.5 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 text-xs text-red-800 font-semibold shadow-sm">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $page.props.flash.error }}
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 p-4 sm:p-5 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
