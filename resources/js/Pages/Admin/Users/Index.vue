<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    branches: Array,
    roles: Array,
});

// Modal state
const isCreateOpen = ref(false);
const isEditOpen = ref(false);
const isDeleteOpen = ref(false);
const selectedUser = ref(null);

// Forms
const createForm = useForm({
    name: '',
    email: '',
    branch_id: '',
    role: 'pic',
    password: '',
});

const editForm = useForm({
    name: '',
    email: '',
    branch_id: '',
    role: 'pic',
    password: '',
    reason: '',
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
    createForm.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
            createForm.reset();
        },
    });
};

const openEditModal = (user) => {
    selectedUser.value = user;
    editForm.clearErrors();
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.branch_id = user.branch_id || '';
    editForm.role = user.roles[0] || 'pic';
    editForm.password = '';
    editForm.reason = '';
    isEditOpen.value = true;
};

const closeEditModal = () => {
    isEditOpen.value = false;
    selectedUser.value = null;
};

const submitEdit = () => {
    if (!selectedUser.value) return;
    editForm.put(route('admin.users.update', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    isDeleteOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteOpen.value = false;
    selectedUser.value = null;
};

const confirmDelete = () => {
    if (!selectedUser.value) return;
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
        },
    });
};
</script>

<template>
    <Head title="Kelola Pengguna - PT Kencana Maju Bersama" />

    <AuthenticatedLayout>
        <div class="space-y-5">
            <!-- Header Card -->
            <div class="bg-white rounded-lg border border-gray-200 p-5 shadow-card flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#DC2626]"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">
                            Pengaturan Sistem & Keamanan
                        </span>
                    </div>
                    <h1 class="text-xl font-black text-[#172B4D] tracking-tight">
                        Kelola Pengguna & Hak Akses
                    </h1>
                    <p class="text-xs text-gray-600 mt-0.5">
                        Daftar pengguna internal PT Kencana Maju Bersama beserta role dan penugasan cabang
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
                    Tambah Pengguna
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#F4F6F9] border-b border-gray-200 text-gray-600 font-semibold uppercase text-[10px] tracking-wider">
                                <th class="p-3">Nama</th>
                                <th class="p-3">Email Akun</th>
                                <th class="p-3">Cabang Penugasan</th>
                                <th class="p-3">Role Akses</th>
                                <th class="p-3">Terdaftar</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-gray-50/80 transition">
                                <td class="p-3 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ u.name }}
                                </td>
                                <td class="p-3 text-gray-600 whitespace-nowrap font-mono text-[11px]">
                                    {{ u.email }}
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span v-if="u.branch" class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ u.branch.name }}
                                    </span>
                                    <span v-else class="text-[11px] text-gray-400 italic">
                                        Pusat / Seluruh Cabang
                                    </span>
                                </td>
                                <td class="p-3 whitespace-nowrap">
                                    <span
                                        v-for="r in u.roles"
                                        :key="r"
                                        class="inline-flex items-center rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider mr-1"
                                        :class="{
                                            'bg-purple-50 text-purple-700 border border-purple-200': r === 'admin',
                                            'bg-amber-50 text-amber-800 border border-amber-200': r === 'direksi',
                                            'bg-emerald-50 text-emerald-800 border border-emerald-200': r === 'marketing',
                                            'bg-blue-50 text-blue-700 border border-blue-200': r === 'pic'
                                        }"
                                    >
                                        {{ r }}
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500 whitespace-nowrap text-[11px]">
                                    {{ u.created_at }}
                                </td>
                                <td class="p-3 text-right whitespace-nowrap space-x-2">
                                    <button
                                        @click="openEditModal(u)"
                                        class="text-[#172B4D] hover:underline font-bold text-xs uppercase"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="$page.props.auth.user.id !== u.id"
                                        @click="openDeleteModal(u)"
                                        class="text-red-600 hover:underline font-bold text-xs uppercase ml-3"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-gray-400">
                                    Belum ada data pengguna terdaftar.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="users.links && users.links.length > 3" class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    <div class="text-gray-500">
                        Menampilkan <strong class="text-gray-800">{{ users.from }}</strong> s/d <strong class="text-gray-800">{{ users.to }}</strong> dari total <strong class="text-gray-800">{{ users.total }}</strong> pengguna
                    </div>
                    <div class="flex gap-1 flex-wrap">
                        <component
                            :is="link.url ? 'Link' : 'span'"
                            v-for="(link, index) in users.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-2.5 py-1 text-xs rounded border transition"
                            :class="{
                                'bg-[#172B4D] text-white border-[#172B4D] font-bold': link.active,
                                'bg-white text-gray-700 border-gray-300 hover:bg-gray-100': !link.active && link.url,
                                'text-gray-300 border-gray-200 cursor-not-allowed': !link.url
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <Modal :show="isCreateOpen" @close="closeCreateModal" max-width="md">
            <div class="p-6 space-y-4">
                <div class="border-b pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#172B4D] uppercase tracking-wider">
                        Tambah Pengguna Baru
                    </h3>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="create_name" value="Nama Lengkap" class="text-xs font-semibold" />
                        <TextInput id="create_name" v-model="createForm.name" type="text" class="mt-1 block w-full text-xs" required />
                        <InputError :message="createForm.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="create_email" value="Alamat Email" class="text-xs font-semibold" />
                        <TextInput id="create_email" v-model="createForm.email" type="email" class="mt-1 block w-full text-xs" required />
                        <InputError :message="createForm.errors.email" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="create_role" value="Role Pengguna" class="text-xs font-semibold" />
                        <select id="create_role" v-model="createForm.role" class="mt-1 block w-full rounded border-gray-300 text-xs py-1.5 focus:border-[#172B4D]" required>
                            <option v-for="r in roles" :key="r" :value="r">{{ r.toUpperCase() }}</option>
                        </select>
                        <InputError :message="createForm.errors.role" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="create_branch" value="Cabang Penugasan (Wajib untuk PIC)" class="text-xs font-semibold" />
                        <select id="create_branch" v-model="createForm.branch_id" class="mt-1 block w-full rounded border-gray-300 text-xs py-1.5 focus:border-[#172B4D]">
                            <option value="">Pusat / Semua Cabang</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} ({{ b.code }})</option>
                        </select>
                        <InputError :message="createForm.errors.branch_id" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="create_password" value="Password Awal" class="text-xs font-semibold" />
                        <TextInput id="create_password" v-model="createForm.password" type="password" class="mt-1 block w-full text-xs" required />
                        <InputError :message="createForm.errors.password" class="mt-1" />
                    </div>
                    <div class="pt-3 border-t flex justify-end gap-2">
                        <SecondaryButton @click="closeCreateModal" type="button" class="text-xs">Batal</SecondaryButton>
                        <button type="submit" :disabled="createForm.processing" class="px-3 py-1.5 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-semibold rounded shadow-xs transition">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit User Modal -->
        <Modal :show="isEditOpen" @close="closeEditModal" max-width="md">
            <div class="p-6 space-y-4">
                <div class="border-b pb-3 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-[#172B4D] uppercase tracking-wider">
                        Ubah Data Pengguna
                    </h3>
                    <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Nama Lengkap" class="text-xs font-semibold" />
                        <TextInput id="edit_name" v-model="editForm.name" type="text" class="mt-1 block w-full text-xs" required />
                        <InputError :message="editForm.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_email" value="Alamat Email" class="text-xs font-semibold" />
                        <TextInput id="edit_email" v-model="editForm.email" type="email" class="mt-1 block w-full text-xs" required />
                        <InputError :message="editForm.errors.email" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_role" value="Role Pengguna" class="text-xs font-semibold" />
                        <select id="edit_role" v-model="editForm.role" class="mt-1 block w-full rounded border-gray-300 text-xs py-1.5 focus:border-[#172B4D]" required>
                            <option v-for="r in roles" :key="r" :value="r">{{ r.toUpperCase() }}</option>
                        </select>
                        <InputError :message="editForm.errors.role" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_branch" value="Cabang Penugasan" class="text-xs font-semibold" />
                        <select id="edit_branch" v-model="editForm.branch_id" class="mt-1 block w-full rounded border-gray-300 text-xs py-1.5 focus:border-[#172B4D]">
                            <option value="">Pusat / Semua Cabang</option>
                            <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }} ({{ b.code }})</option>
                        </select>
                        <InputError :message="editForm.errors.branch_id" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_password" value="Password Baru (Kosongkan bila tidak diubah)" class="text-xs font-semibold" />
                        <TextInput id="edit_password" v-model="editForm.password" type="password" class="mt-1 block w-full text-xs" placeholder="••••••••" />
                        <InputError :message="editForm.errors.password" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel for="edit_reason" value="Alasan Koreksi Akun (Wajib Dicatat di Audit Log)" class="text-xs font-semibold" />
                        <TextInput id="edit_reason" v-model="editForm.reason" type="text" class="mt-1 block w-full text-xs" placeholder="Contoh: Mutasi penugasan ke cabang Surabaya" required />
                        <InputError :message="editForm.errors.reason" class="mt-1" />
                    </div>
                    <div class="pt-3 border-t flex justify-end gap-2">
                        <SecondaryButton @click="closeEditModal" type="button" class="text-xs">Batal</SecondaryButton>
                        <button type="submit" :disabled="editForm.processing" class="px-3 py-1.5 bg-[#172B4D] hover:bg-[#243B5A] text-white text-xs font-semibold rounded shadow-xs transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete User Modal -->
        <Modal :show="isDeleteOpen" @close="closeDeleteModal" max-width="md">
            <div class="p-6 space-y-4">
                <h3 class="text-sm font-bold text-red-700 uppercase tracking-wider">
                    Konfirmasi Hapus Pengguna
                </h3>
                <p class="text-xs text-gray-600">
                    Apakah Anda yakin ingin menghapus akun pengguna <strong>{{ selectedUser?.name }}</strong> ({{ selectedUser?.email }})? Tindakan ini akan dicatat ke dalam audit log dan tidak dapat dibatalkan.
                </p>
                <div class="flex justify-end gap-2 pt-2 border-t">
                    <SecondaryButton @click="closeDeleteModal" type="button" class="text-xs">Batal</SecondaryButton>
                    <DangerButton @click="confirmDelete" class="text-xs">Hapus Pengguna</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
