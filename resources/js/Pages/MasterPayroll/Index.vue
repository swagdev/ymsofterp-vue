<script setup>
import { ref, watch, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Swal from 'sweetalert2';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Switch } from '@headlessui/vue';
import EmployeeFormModal from './EmployeeFormModal.vue';

const props = defineProps({
  d: Object, // { data, links, meta }
  filters: Object,
  jabatans: Object, // daftar jabatan untuk form
  divisis: Object, // daftar divisi untuk form
  outlets: Object, // daftar outlet untuk form
  levels: Object, // daftar level untuk form
});

const search = ref(props.filters?.search || '');
const showInactive = ref(false);
const showModal = ref(false);
const modalMode = ref('create'); // 'create' | 'edit'
const selectedCustomer = ref(null);
const loadingField = ref({});

const debouncedSearch = debounce(() => {
  router.get('/employee-data', { search: search.value, status: showInactive.value ? 'inactive' : 'active' }, { preserveState: true, replace: true });
}, 100);

watch(showInactive, (val) => {
  router.get('/employee-data', { search: search.value, status: val ? 'inactive' : 'active' }, { preserveState: true, replace: true });
});

const ds = ref({
  gaji: props.d.gaji,  // Initialize with passed prop
  tunjangan_jabatan: props.d.tunjangan_jabatan,
});

const form = useForm({
  // Your form fields here
  id_divisi: props.d?.division_id || '',
  id_outlet: props.d?.id_outlet || '',
});

function onSearchInput() {
  debouncedSearch();
}

function goToPage(url) {
  if (url) router.visit(url, { preserveState: true, replace: true });
}

function openCreate() {
  // modalMode.value = 'create';
  // selectedCustomer.value = null;
  // showModal.value = true;
  router.visit(route('employee.create'));
}

function openEdit(customer) {
  router.visit(route('employee.edit', customer.id));
}

async function hapus(customer) {
  const result = await Swal.fire({
    title: 'Hapus Karyawan?',
    text: `Yakin ingin menghapus karyawan "${customer.nama_lengkap}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal'
  });
  if (!result.isConfirmed) return;
  router.delete(route('employee.destroy', customer.id), {
    onSuccess: () => Swal.fire('Berhasil', 'Karyawan berhasil dihapus!', 'success'),
  });
}

function reload() {
  router.reload({ preserveState: true, replace: true });
}

function closeModal() {
  showModal.value = false;
}

function onSubmit() {
  // Tampilkan loading
  Swal.fire({
    title: 'Load Data...',
    text: 'Mohon tunggu sebentar',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  form.post(route('master_payroll.index'), {
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

function updateField(userId, field, value) {
  const key = `${userId}_${field}`
  loadingField.value[key] = true

  axios.patch(route('master_payroll.update', userId), {
    [field]: value
  }).then(() => {
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: 'Tersimpan!',
      showConfirmButton: false,
      timer: 1500
    })
  }).finally(() => {
    loadingField.value[key] = false
  })
}

function toggleStatus(customer) {
  const newStatus = customer.status === 'inactive' ? 'inactive' : 'active';
  router.patch(route('employee.toggle-status', customer.id), { status: newStatus }, {
    preserveState: true,
    onSuccess: reload,
  });
}
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl w-full mx-auto py-8 px-2">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-500"></i> MASTER GAJI KARYAWAN
        </h1>
        <button @click="openCreate" class="bg-gradient-to-r from-blue-500 to-blue-700 text-white px-4 py-2 rounded-xl shadow-lg hover:shadow-2xl transition-all font-semibold">
          + Buat Karyawan Baru
        </button>
      </div>
      <form @submit.prevent="onSubmit" class="space-y-6" enctype="multipart/form-data">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-[250px]">
          <label class="block text-sm font-medium text-gray-700">Divisi*</label>
          <select
            v-model="form.id_divisi"
            @change="emitDivisi"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
            <option disabled value="">-- Pilih Divisi --</option>
            <option
              v-for="divisi in divisis"
              :key="divisi.id"
              :value="divisi.id"
            >
              {{ divisi.nama_divisi }}
            </option>
          </select>
          <div v-if="form.errors.id_divisi" class="text-xs text-red-500 mt-1">
            {{ form.errors.id_divisi }}
          </div>
        </div>
        <div class="w-[250px]">
          <label class="block text-sm font-medium text-gray-700" for="category">Outlet*</label>
          <select v-model="form.id_outlet" @change="emitOutlet" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option disabled value="">-- Pilih Outlet--</option>
            <option v-for="outlet in outlets" :key="outlet.id_outlet" :value="outlet.id_outlet">
              {{ outlet.nama_outlet }}
            </option>
          </select>
          <div v-if="form.errors.id_outlet" class="text-xs text-red-500 mt-1">{{ form.errors.id_outlet }}</div>
        </div>
        <div class="w-[250px]">
          <label class="block text-sm font-medium text-gray-700" for="category"></label>
          <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50">FILTER</button>
        </div>
      </div>
      </form>
      <div class="flex items-center gap-3 mb-4">
        <Switch
          v-model="showInactive"
          :class="showInactive ? 'bg-blue-600' : 'bg-gray-200'"
          class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
        >
          <span
            :class="showInactive ? 'translate-x-6' : 'translate-x-1'"
            class="inline-block h-4 w-4 transform rounded-full bg-white transition"
          />
        </Switch>
        <span class="ml-2 text-sm text-gray-700">Tampilkan Inactive</span>
      </div>
      <div class="mb-4">
        <input
          v-model="search"
          @input="onSearchInput"
          type="text"
          placeholder="Cari nama/jabatan/outlet..."
          class="w-full px-4 py-2 rounded-xl border border-blue-200 shadow focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"
        />
      </div>
      <div class="bg-white rounded-2xl shadow-2xl overflow-x-auto transition-all">
        <table class="w-full min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tl-2xl">
                <span
                  class="text-sm text-gray-700 font-medium cursor-pointer select-none"
                  @click="allSelected = !allSelected; toggleAll()"
                >
                  Check All
                </span>
              </th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">NAMA</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">JABATAN</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">OUTLET</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">GAJI(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">TUNJANGAN(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">OT(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">UM(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">PH(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">SC(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">QA REWARD(EARN)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS JKN(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS TK(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS JHT(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS JP(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS JKK(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">BPJS JKM(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">L & B(DEDUCTION)</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tr-2xl">QA PUNISHMENT (DEDUCTION)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="d.data.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Tidak ada data customer.</td>
            </tr>
            <tr v-for="ds in d.data" :key="ds.id" class="hover:bg-blue-50 transition shadow-sm">
              <td class="px-6 py-3 font-semibold">
                <input
                  type="checkbox"
                  :value="ds.id"
                  v-model="selected"
                />
              </td>
              <td class="px-6 py-3">{{ ds.nama_lengkap }}</td>
              <td class="px-6 py-3">{{ ds.nama_jabatan }}</td>
              <td class="px-6 py-3">{{ ds.nama_outlet }}</td>
              <td class="px-6 py-3">
                <input
                  type="text"
                  class="w-[150px] p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Gaji"
                  v-model="ds.gaji"
                  @change="updateField(ds.id, 'gaji', $event.target.value)"
                  :disabled="loadingField[`${ds.id}_gaji`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="text"
                  class="w-[150px] p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Tunjangan Jabatan"
                  v-model="ds.tunjangan_jabatan"
                  @change="updateField(ds.id, 'tunjangan_jabatan', $event.target.value)"
                  :disabled="loadingField[`${ds.id}_tunjangan_jabatan`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.over_time == 1"
                  @change="updateField(ds.id, 'over_time', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_over_time`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.uang_makan == 1"
                  @change="updateField(ds.id, 'uang_makan', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_uang_makan`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.public_holiday == 1"
                  @change="updateField(ds.id, 'public_holiday', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_public_holiday`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.service_charge == 1"
                  @change="updateField(ds.id, 'service_charge', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_service_charge`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.qa_reward == 1"
                  @change="updateField(ds.id, 'qa_reward', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_qa_reward`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_jkn == 1"
                  @change="updateField(ds.id, 'bpjs_jkn', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_jkn`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_tk == 1"
                  @change="updateField(ds.id, 'bpjs_tk', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_tk`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_jht == 1"
                  @change="updateField(ds.id, 'bpjs_jht', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_jht`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_jp == 1"
                  @change="updateField(ds.id, 'bpjs_jp', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_jp`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_jkk == 1"
                  @change="updateField(ds.id, 'bpjs_jkk', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_jkk`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.bpjs_jkm == 1"
                  @change="updateField(ds.id, 'bpjs_jkm', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_bpjs_jkm`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.l_n_b == 1"
                  @change="updateField(ds.id, 'l_n_b', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_l_n_b`] === true"
                />
              </td>
              <td class="px-6 py-3">
                <input
                  type="checkbox"
                  :checked="ds.qa_penalty == 1"
                  @change="updateField(ds.id, 'qa_penalty', $event.target.value)"
                  class="h-5 w-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 transition update-column-value"
                  :disabled="loadingField[`${ds.id}_qa_penalty`] === true"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="flex justify-end mt-4 gap-2">
        <button
          v-for="link in d.links"
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