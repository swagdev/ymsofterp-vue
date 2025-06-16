<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Swal from 'sweetalert2';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Switch } from '@headlessui/vue';
import EmployeeFormModal from './MemberFormModal.vue';

const props = defineProps({
  customers: Object, // { data, links, meta }
  outlets: Object, // List of outlets
  filters: Object,
});

const search = ref(props.filters?.search || '');
const showInactive = ref(false);
const showModal = ref(false);
const modalMode = ref('create'); // 'create' | 'edit'
const selectedCustomer = ref(null);
const today = new Date().toISOString().slice(0, 10);

const debouncedSearch = debounce(() => {
  router.post('/member-data', { search: search.value, status: showInactive.value ? 'inactive' : 'active' }, { preserveState: true, replace: true });
}, 100);

watch(showInactive, (val) => {
  router.post('/member-data', { search: search.value, status: val ? 'inactive' : 'active' }, { preserveState: true, replace: true });
});

const form = useForm({
  id_outlet: props.outlets?.id || '',
  tanggal_awal: today,
  tanggal_akhir: today,
});

function onSearchInput() {
  debouncedSearch();
}

function goToPage(url) {
  if (url) router.visit(url, { preserveState: true, replace: true });
}

function openCreate() {
  modalMode.value = 'create';
  selectedCustomer.value = null;
  showModal.value = true;
}

function openEdit(customer) {
  Swal.fire({
    title: 'Load data...',
    text: 'Mohon tunggu sebentar',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  axios.post(route('member.view_detail'), {
    data: customer,
    tgl_awal: form.tanggal_awal,
    tgl_akhir: form.tanggal_akhir,
  }).then(response => {
    modalMode.value = 'edit';
    selectedCustomer.value = response.data;
    showModal.value = true;
    Swal.close();
  });
}

function onFilter() {
  // Tampilkan loading
  // Swal.fire({
  //   title: 'Menyimpan Data...',
  //   text: 'Mohon tunggu sebentar',
  //   allowOutsideClick: false,
  //   allowEscapeKey: false,
  //   showConfirmButton: false,
  //   didOpen: () => {
  //     Swal.showLoading();
  //   }
  // });

  form.post(route('member.index'), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data berhasil disimpan',
        timer: 1500,
        showConfirmButton: false
      }).then(() => {
        // router.visit(route('employee.edit', props.customer.id));
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat menyimpan data'
      });
    }
  });
}

async function hapus(customer) {
  const result = await Swal.fire({
    title: 'Hapus Customer?',
    text: `Yakin ingin menghapus customer "${customer.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });
  if (!result.isConfirmed) return;
  router.delete(route('customers.destroy', customer.id), {
    onSuccess: () => Swal.fire('Berhasil', 'Customer berhasil dihapus!', 'success'),
  });
}

function reload() {
  router.reload({ preserveState: true, replace: true });
}

function closeModal() {
  showModal.value = false;
}

function toggleStatus(customer) {
  const newStatus = customer.status === 'active' ? 'inactive' : 'active';
  router.patch(route('customers.toggle-status', customer.id), { status: newStatus }, {
    preserveState: true,
    onSuccess: reload,
  });
}

function formatRupiah(value) {
  if (!value) return 'Rp 0';
  return value.toLocaleString('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  });
}
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl w-full mx-auto py-8 px-2">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-500"></i> Data Member
        </h1>
      </div>
      <form @submit.prevent="onFilter" class="space-y-6" enctype="multipart/form-data">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-[250px]">
          <select v-model="form.id_outlet" @change="emitOutlet" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option disabled value="">-- All Outlet--</option>
            <option v-for="outlet in outlets" :key="outlet.id" :value="outlet.id">
              {{ outlet.nama_singkat }}
            </option>
          </select>
          <div v-if="form.errors.id_outlet" class="text-xs text-red-500 mt-1">{{ form.errors.id_outlet }}</div>
        </div>
        <div class="w-[200px]">
          <input type="date" v-model="form.tanggal_awal" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
          <div v-if="form.errors.tanggal_awal" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal_awal }}</div>
        </div>
        <div class="w-[200px]">
          <input type="date" v-model="form.tanggal_akhir" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
          <div v-if="form.errors.tanggal_akhir" class="text-xs text-red-500 mt-1">{{ form.errors.tanggal_akhir }}</div>
        </div>
        <div class="w-[250px]">
          <label class="block text-sm font-medium text-gray-700" for="category"></label>
          <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50">FILTER</button>
        </div>
      </div>
      </form>
      <div class="mb-4">
        <input
          v-model="search"
          @input="onSearchInput"
          type="text"
          placeholder="Cari nama/kode/region..."
          class="w-full px-4 py-2 rounded-xl border border-blue-200 shadow focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
        />
      </div>
      <div class="bg-white rounded-2xl shadow-2xl overflow-x-auto transition-all">
        <table class="w-full min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tl-2xl">MEMBER NAME</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">DATE OF BIRTH</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">CONTACT</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">EMAIL</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">ADDRESS</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">REGISTER DATE</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">YEARLY TOTAL COUNT TRANSACTION</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">TOTAL COUNT TRANSACTION</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">YEARLY TOTAL TRANSACTION</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">TOTAL TRANSACTION</th>
              <!-- <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">YEARLY LAST TRANSACTION</th> -->
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">LAST TRANSACTION DATE</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">REDEM</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">POINT BALANCE</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tr-2xl">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="customers.data.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Tidak ada data member.</td>
            </tr>
            <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-blue-50 transition shadow-sm">
              <td class="px-6 py-3 font-semibold">{{ customer.name }}</td>
              <td class="px-6 py-3">{{ customer.tanggal_lahir }}</td>
              <td class="px-6 py-3">{{ customer.telepon }}</td>
              <td class="px-6 py-3">{{ customer.email }}</td>
              <td class="px-6 py-3">{{ customer.alamat }}</td>
              <td class="px-6 py-3">{{ customer.tanggal_register }}</td>
              <td class="px-6 py-3">{{ customer.total_transaksi_tahunan }}</td>
              <td class="px-6 py-3">{{ customer.total_transaksi_semua }}</td>
              <td class="px-6 py-3">{{ formatRupiah(customer.total_jml_trans) }}</td>
              <td class="px-6 py-3">{{ customer.total_year_jum_trans }}</td>
              <!-- <td class="px-6 py-3">aaaa</td> -->
              <td class="px-6 py-3">{{ new Date(customer.last_transaksi_date).toLocaleDateString('id-ID') }}</td>
              <td class="px-6 py-3">{{ customer.total_redeem_point_tahun_ini }}</td>
              <td class="px-6 py-3">{{ customer.total_point_tahun_ini }}</td>
              <td class="px-6 py-3">
                <div class="flex gap-2">
                  <button @click="openEdit(customer)" class="inline-flex items-center btn btn-xs bg-yellow-100 text-yellow-800 hover:bg-yellow-200 rounded px-2 py-1 font-semibold transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    View Detail
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="flex justify-end mt-4 gap-2">
        <button
          v-for="link in customers.links"
          :key="link.label"
          :disabled="!link.url"
          @click="goToPage(link.url)"
          v-html="link.label"
          class="px-3 py-1 rounded-lg border text-sm font-semibold"
          :class="[
            link.active ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-blue-700 hover:bg-blue-50',
            !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
          ]"
        />
      </div>
      <EmployeeFormModal
        :show="showModal"
        :mode="modalMode"
        :customer="selectedCustomer"
        @close="closeModal"
        @success="reload"
      />
    </div>
  </AppLayout>
</template>

<style scoped>
.bg-3d {
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 1.5px 4px 0 rgba(31, 38, 135, 0.08);
}
</style> 