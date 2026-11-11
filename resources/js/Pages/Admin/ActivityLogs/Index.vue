<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    activities: Object,
    filters: Object,
    logNames: Array,
});

const search = ref(props.filters?.search || '');
const selectedLogName = ref(props.filters?.log_name || '');

const handleFilter = () => {
    router.get(route('admin.activity-logs.index'), {
        search: search.value,
        log_name: selectedLogName.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    selectedLogName.value = '';
    handleFilter();
};

// Properties Preview Modal
const isDetailsOpen = ref(false);
const selectedActivity = ref(null);

const openDetailsModal = (activity) => {
    selectedActivity.value = activity;
    isDetailsOpen.value = true;
};

const closeDetailsModal = () => {
    isDetailsOpen.value = false;
    selectedActivity.value = null;
};
</script>

<template>
    <Head title="Log Aktivitas Sistem - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Audit Trail & Akuntabilitas
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Log Aktivitas Sistem
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Rekam jejak seluruh aksi penting pengguna (verifikasi marketing, keputusan direksi, input laporan, dan administrasi)
                    </p>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                <form @submit.prevent="handleFilter" class="flex flex-col md:flex-row gap-3">
                    <div class="relative flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari deskripsi aksi, subjek, atau nama pengguna..."
                            class="w-full text-xs rounded-md border-gray-300 shadow-2xs focus:border-red-500 focus:ring-red-500 pl-9"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div v-if="logNames?.length" class="w-full md:w-48">
                        <select
                            v-model="selectedLogName"
                            @change="handleFilter"
                            class="w-full text-xs rounded-md border-gray-300 shadow-2xs focus:border-red-500 focus:ring-red-500"
                        >
                            <option value="">Semua Kategori Log</option>
                            <option v-for="name in logNames" :key="name" :value="name">
                                {{ name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-md shadow-2xs transition"
                        >
                            Filter
                        </button>
                        <button
                            v-if="search || selectedLogName"
                            type="button"
                            @click="resetFilter"
                            class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-md transition"
                        >
                            Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#F4F6F9] border-b border-gray-200 text-gray-600 font-semibold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Waktu</th>
                                <th class="p-3">Aktor / Pengguna</th>
                                <th class="p-3">Aksi & Deskripsi</th>
                                <th class="p-3">Subjek Terkait</th>
                                <th class="p-3 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="act in activities.data" :key="act.id" class="hover:bg-gray-50/80 transition">
                                <td class="p-3 whitespace-nowrap">
                                    <div class="font-semibold text-gray-900">{{ act.created_at }}</div>
                                    <div class="text-[10px] text-gray-400">{{ act.created_at_human }}</div>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <div v-if="act.causer">
                                        <div class="font-bold text-gray-900">{{ act.causer.name }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono">{{ act.causer.email }}</div>
                                    </div>
                                    <div v-else class="text-gray-400 italic text-[11px]">
                                        Sistem Otomatis
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-gray-900 leading-snug">
                                        {{ act.description }}
                                    </div>
                                    <div v-if="act.log_name && act.log_name !== 'default'" class="mt-0.5">
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-mono uppercase bg-gray-100 text-gray-600">
                                            {{ act.log_name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span v-if="act.subject_type" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ act.subject_type }} #{{ act.subject_id }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap">
                                    <button
                                        v-if="act.properties && Object.keys(act.properties).length"
                                        type="button"
                                        @click="openDetailsModal(act)"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#172B4D] hover:underline"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Lihat Payload
                                    </button>
                                    <span v-else class="text-gray-300 text-[10px]">-</span>
                                </td>
                            </tr>
                            <tr v-if="!activities.data?.length">
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    Belum ada catatan aktivitas yang sesuai dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="activities.links?.length > 3" class="p-3 border-t border-gray-200 flex items-center justify-between text-xs">
                    <div class="text-gray-500">
                        Menampilkan {{ activities.from || 0 }} - {{ activities.to || 0 }} dari {{ activities.total }} log
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in activities.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="px-2.5 py-1 rounded text-xs transition"
                            :class="{
                                'bg-[#172B4D] text-white font-bold': link.active,
                                'text-gray-600 hover:bg-gray-100': !link.active && link.url,
                                'text-gray-300 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAILS MODAL -->
        <Modal :show="isDetailsOpen" @close="closeDetailsModal" max-width="lg">
            <div class="p-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">
                            Detail Payload Aktivitas #{{ selectedActivity?.id }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ selectedActivity?.description }}</p>
                    </div>
                    <button @click="closeDetailsModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-[11px] overflow-x-auto max-h-80 leading-relaxed">
                        <pre>{{ JSON.stringify(selectedActivity?.properties, null, 2) }}</pre>
                    </div>

                    <div class="text-[11px] text-gray-500 flex justify-between pt-2">
                        <span>Aktor: {{ selectedActivity?.causer?.name || 'Sistem' }}</span>
                        <span>Waktu: {{ selectedActivity?.created_at }}</span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end pt-3 border-t border-gray-100">
                    <SecondaryButton @click="closeDetailsModal">
                        Tutup
                    </SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
