<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    branches: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get(route('admin.branches.index'), {
        search: search.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetSearch = () => {
    search.value = '';
    handleSearch();
};

// Modal State
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedBranch = ref(null);

// Create Form
const createForm = useForm({
    name: '',
    code: '',
    is_headquarters: false,
});

// Edit Form
const editForm = useForm({
    name: '',
    code: '',
    is_headquarters: false,
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    isCreateOpen.value = true;
};

const closeCreateModal = () => {
    isCreateOpen.value = false;
};

const submitCreate = () => {
    createForm.post(route('admin.branches.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            createForm.reset();
        },
    });
};

const openEditModal = (branch) => {
    selectedBranch.value = branch;
    editForm.clearErrors();
    editForm.name = branch.name;
    editForm.code = branch.code;
    editForm.is_headquarters = Boolean(branch.is_headquarters);
    isEditOpen.value = true;
};

const closeEditModal = () => {
    isEditOpen.value = false;
    selectedBranch.value = null;
};

const submitEdit = () => {
    if (!selectedBranch.value) return;
    editForm.put(route('admin.branches.update', selectedBranch.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const openDeleteModal = (branch) => {
    selectedBranch.value = branch;
    isDeleteOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteOpen.value = false;
    selectedBranch.value = null;
};

const confirmDelete = () => {
    if (!selectedBranch.value) return;
    router.delete(route('admin.branches.destroy', selectedBranch.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
    });
};
</script>

<template>
    <Head title="Kelola Kantor Cabang - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Master Data Wilayah & Cabang
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Kelola Kantor Cabang
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Daftar kantor cabang operasional PT Kencana Maju Bersama untuk isolasi data event
                    </p>
                </div>
                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-bold rounded-md shadow-xs transition"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Cabang Baru
                </button>
            </div>

            <!-- Search Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-card">
                <form @submit.prevent="handleSearch" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama atau kode cabang..."
                            class="w-full text-xs rounded-md border-gray-300 shadow-2xs focus:border-red-500 focus:ring-red-500 pl-9"
                        />
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-gray-800 hover:bg-black text-white text-xs font-semibold rounded-md shadow-2xs transition"
                        >
                            Cari
                        </button>
                        <button
                            v-if="search"
                            type="button"
                            @click="resetSearch"
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
                                <th class="p-3">Kode Cabang</th>
                                <th class="p-3">Nama Kantor Cabang</th>
                                <th class="p-3">Status Kantor</th>
                                <th class="p-3 text-center">Pengguna Terkait</th>
                                <th class="p-3 text-center">Total Event</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="b in branches.data" :key="b.id" class="hover:bg-gray-50/80 transition">
                                <td class="p-3 font-mono font-bold text-gray-900 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                        {{ b.code }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ b.name }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        v-if="b.is_headquarters"
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300"
                                    >
                                        <svg class="w-3 h-3 mr-1 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Kantor Pusat
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200"
                                    >
                                        Kantor Cabang
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ b.users_count || 0 }} User
                                    </span>
                                </td>
                                <td class="p-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ b.events_count || 0 }} Event
                                    </span>
                                </td>
                                <td class="p-3 text-right whitespace-nowrap space-x-2">
                                    <button
                                        type="button"
                                        @click="openEditModal(b)"
                                        class="text-blue-600 hover:text-blue-800 font-semibold"
                                    >
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        @click="openDeleteModal(b)"
                                        class="text-red-600 hover:text-red-800 font-semibold ml-2"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!branches.data?.length">
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    Belum ada kantor cabang yang sesuai dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="branches.links?.length > 3" class="p-3 border-t border-gray-200 flex items-center justify-between text-xs">
                    <div class="text-gray-500">
                        Menampilkan {{ branches.from || 0 }} - {{ branches.to || 0 }} dari {{ branches.total }} cabang
                    </div>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in branches.links"
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

        <!-- CREATE MODAL -->
        <Modal :show="isCreateOpen" @close="closeCreateModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-base font-bold text-gray-900">
                        Tambah Kantor Cabang Baru
                    </h3>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="create-name" value="Nama Kantor Cabang" />
                        <TextInput
                            id="create-name"
                            v-model="createForm.name"
                            type="text"
                            class="mt-1 block w-full text-xs"
                            placeholder="Contoh: Surabaya (Kantor Pusat) / Jakarta"
                            required
                        />
                        <InputError :message="createForm.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="create-code" value="Kode Cabang (Unik)" />
                        <TextInput
                            id="create-code"
                            v-model="createForm.code"
                            type="text"
                            class="mt-1 block w-full text-xs font-mono uppercase"
                            placeholder="Contoh: SBY-HQ / JKT / BDG / SMG"
                            required
                        />
                        <InputError :message="createForm.errors.code" class="mt-1" />
                        <p class="text-[10px] text-gray-500 mt-1">Kode akan otomatis dinormalisasi menjadi huruf kapital.</p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="create-hq"
                            v-model="createForm.is_headquarters"
                            type="checkbox"
                            class="rounded border-gray-300 text-red-600 shadow-2xs focus:ring-red-500 h-4 w-4"
                        />
                        <label for="create-hq" class="text-xs font-semibold text-gray-700 cursor-pointer">
                            Tandai sebagai Kantor Pusat (Headquarters)
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeCreateModal" type="button">
                            Batal
                        </SecondaryButton>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="px-4 py-2 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-bold rounded-md shadow-xs transition disabled:opacity-50"
                        >
                            Simpan Cabang
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- EDIT MODAL -->
        <Modal :show="isEditOpen" @close="closeEditModal" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-base font-bold text-gray-900">
                        Ubah Kantor Cabang: [{{ selectedBranch?.code }}]
                    </h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel for="edit-name" value="Nama Kantor Cabang" />
                        <TextInput
                            id="edit-name"
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 block w-full text-xs"
                            required
                        />
                        <InputError :message="editForm.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit-code" value="Kode Cabang (Unik)" />
                        <TextInput
                            id="edit-code"
                            v-model="editForm.code"
                            type="text"
                            class="mt-1 block w-full text-xs font-mono uppercase"
                            required
                        />
                        <InputError :message="editForm.errors.code" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input
                            id="edit-hq"
                            v-model="editForm.is_headquarters"
                            type="checkbox"
                            class="rounded border-gray-300 text-red-600 shadow-2xs focus:ring-red-500 h-4 w-4"
                        />
                        <label for="edit-hq" class="text-xs font-semibold text-gray-700 cursor-pointer">
                            Tandai sebagai Kantor Pusat (Headquarters)
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-100">
                        <SecondaryButton @click="closeEditModal" type="button">
                            Batal
                        </SecondaryButton>
                        <button
                            type="submit"
                            :disabled="editForm.processing"
                            class="px-4 py-2 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-bold rounded-md shadow-xs transition disabled:opacity-50"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- DELETE CONFIRMATION MODAL -->
        <Modal :show="isDeleteOpen" @close="closeDeleteModal" max-width="sm">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 text-red-600 mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-bold text-center text-gray-900 mb-2">
                    Hapus Kantor Cabang?
                </h3>
                <p class="text-xs text-center text-gray-600 leading-relaxed mb-6">
                    Apakah Anda yakin ingin menghapus cabang <strong>[{{ selectedBranch?.code }}] {{ selectedBranch?.name }}</strong>? Tindakan ini hanya dapat dilakukan bila cabang tidak memiliki pengguna atau riwayat event.
                </p>
                <div class="flex justify-center gap-2">
                    <SecondaryButton @click="closeDeleteModal">
                        Batal
                    </SecondaryButton>
                    <DangerButton @click="confirmDelete">
                        Ya, Hapus Cabang
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
