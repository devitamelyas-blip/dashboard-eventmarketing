<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const demoAccounts = [
    { label: 'Admin', email: 'admin@kencana.id', role: 'admin', color: 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100' },
    { label: 'Direksi', email: 'direksi@kencana.id', role: 'direksi', color: 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' },
    { label: 'Marketing', email: 'marketing@kencana.id', role: 'marketing', color: 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' },
    { label: 'PIC Surabaya', email: 'pic.surabaya@kencana.id', role: 'pic', color: 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' },
];

const fillDemo = (acc) => {
    form.email = acc.email;
    form.password = 'password';
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk Sistem - Event Marketing PT Kencana Maju Bersama" />

        <div v-if="status" class="mb-4 text-xs font-semibold p-3 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ status }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Alamat Email Karyawan" class="text-xs font-semibold text-gray-700" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-xs"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="nama@kencana.id"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Kata Sandi (Password)" class="text-xs font-semibold text-gray-700" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[11px] text-gray-500 hover:text-[#DC2626] font-medium transition"
                    >
                        Lupa password?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-xs"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-xs text-gray-600 font-medium">Ingat saya di perangkat ini</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full justify-center py-2.5 bg-[#172B4D] hover:bg-[#243B5A] text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Masuk ke Dashboard</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Quick 1-Click Demo Login Account Helper -->
        <div class="mt-6 pt-5 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                    Akses Cepat Akun Demo (1-Click)
                </span>
                <span class="text-[10px] text-gray-400">Klik untuk isi otomatis</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button
                    v-for="acc in demoAccounts"
                    :key="acc.email"
                    type="button"
                    @click="fillDemo(acc)"
                    class="flex items-center justify-between px-2.5 py-1.5 rounded-lg border text-left transition"
                    :class="acc.color"
                    :title="`Isi email ${acc.email}`"
                >
                    <span class="text-[11px] font-bold">{{ acc.label }}</span>
                    <span class="text-[10px] opacity-75 font-mono">Fill &rarr;</span>
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
