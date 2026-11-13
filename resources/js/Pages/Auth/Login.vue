<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const activeDemo = ref(null);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const demoAccounts = [
    {
        label: 'Administrator',
        email: 'admin@kencana.id',
        role: 'Admin Sistem',
        desc: 'Akses penuh kelola pengguna, cabang, audit log',
        iconBg: 'bg-purple-100 text-purple-700',
        activeBorder: 'border-purple-500 bg-purple-50/50 ring-1 ring-purple-500',
    },
    {
        label: 'Direksi',
        email: 'direksi@kencana.id',
        role: 'Direksi Eksekutif',
        desc: 'Review hasil evaluasi, cetak A4, keputusan resmi',
        iconBg: 'bg-amber-100 text-amber-800',
        activeBorder: 'border-amber-500 bg-amber-50/50 ring-1 ring-amber-500',
    },
    {
        label: 'Tim Marketing',
        email: 'marketing@kencana.id',
        role: 'Marketing Pusat',
        desc: 'Verifikasi lapangan, survei QR, ekspor respon',
        iconBg: 'bg-emerald-100 text-emerald-800',
        activeBorder: 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500',
    },
    {
        label: 'PIC Surabaya',
        email: 'pic.surabaya@kencana.id',
        role: 'PIC Cabang Surabaya',
        desc: 'Pengajuan proposal event, upload bukti presensi & foto',
        iconBg: 'bg-blue-100 text-blue-700',
        activeBorder: 'border-blue-500 bg-blue-50/50 ring-1 ring-blue-500',
    },
];

const fillDemo = (acc) => {
    activeDemo.value = acc.email;
    form.email = acc.email;
    form.password = 'password';
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk Sistem - Event Marketing Platform" />

        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xl shadow-slate-100 p-7 sm:p-9 space-y-6">
            <!-- Form Title & Header -->
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                    Masuk ke Sistem
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Silakan gunakan akun email perusahaan yang telah terdaftar.
                </p>
            </div>

            <!-- Status Alert -->
            <div
                v-if="status"
                class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-semibold flex items-center gap-2.5 shadow-2xs"
            >
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ status }}</span>
            </div>

            <!-- Main Login Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Email Perusahaan
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="nama@kencana.id"
                            class="w-full pl-10 pr-4 py-2.5 text-xs text-slate-900 bg-slate-50/60 border border-slate-300 rounded-xl placeholder:text-slate-400 focus:bg-white focus:border-red-600 focus:ring-2 focus:ring-red-600/20 transition-all outline-hidden font-medium"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700">
                            Kata Sandi (Password)
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-[11px] text-slate-500 hover:text-red-600 font-medium transition"
                        >
                            Lupa kata sandi?
                        </Link>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-11 py-2.5 text-xs text-slate-900 bg-slate-50/60 border border-slate-300 rounded-xl placeholder:text-slate-400 focus:bg-white focus:border-red-600 focus:ring-2 focus:ring-red-600/20 transition-all outline-hidden font-medium"
                        />
                        <!-- Show/Hide Password Button -->
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 transition focus:outline-none"
                            tabindex="-1"
                        >
                            <!-- Eye icon -->
                            <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <!-- Eye Slash icon -->
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer select-none">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded text-red-600 focus:ring-red-500" />
                        <span class="ms-2 text-xs text-slate-600 font-medium">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full py-3 px-4 bg-[#0A1120] hover:bg-slate-800 active:scale-[0.99] text-white text-xs font-black uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                        :disabled="form.processing"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>{{ form.processing ? 'Memverifikasi...' : 'Masuk ke Dashboard' }}</span>
                        <svg v-if="!form.processing" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>

            <!-- ════════════════════════════════════════════════════════════════
                 ELEGANT QUICK DEMO ACCOUNT SELECTOR (1-CLICK FILL)
                 ════════════════════════════════════════════════════════════════ -->
            <div class="pt-5 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-500">
                            Akses Cepat Demo (1-Click)
                        </span>
                    </div>
                    <span class="text-[10px] text-slate-400">Pilih role pengujian</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <button
                        v-for="acc in demoAccounts"
                        :key="acc.email"
                        type="button"
                        @click="fillDemo(acc)"
                        class="p-2.5 rounded-xl border border-slate-200 text-left transition-all flex items-start gap-2.5 group cursor-pointer hover:border-slate-400 hover:bg-slate-50/80"
                        :class="activeDemo === acc.email ? acc.activeBorder : 'bg-white'"
                    >
                        <div
                            class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0 mt-0.5"
                            :class="acc.iconBg"
                        >
                            {{ acc.label.charAt(0) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-bold text-slate-800 group-hover:text-slate-900 truncate">
                                    {{ acc.label }}
                                </span>
                                <span class="text-[9px] font-mono text-slate-400 group-hover:text-red-600 transition">
                                    Pilih &rarr;
                                </span>
                            </div>
                            <div class="text-[10px] text-slate-400 truncate mt-0.5">
                                {{ acc.email }}
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
