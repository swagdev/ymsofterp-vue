<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Swal from 'sweetalert2';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Switch } from '@headlessui/vue';
import WebProfileBrandsFormModal from './WebProfileBrandsFormModal.vue';
import { se } from 'date-fns/locale';

const props = defineProps({
  d: Object, // { data, links, meta }
  filters: Object,
  jabatans: Object, // daftar jabatan untuk form
  divisis: Object, // daftar divisi untuk form
  subDivisis: Object, // daftar sub divisi untuk form
  levels: Object, // daftar level untuk form
});

const search = ref(props.filters?.search || '');
const showInactive = ref(false);
const showModal = ref(false);
const modalMode = ref('create'); // 'create' | 'edit'
const selectedDs = ref(null);
const selectedJabatans = ref(null); // untuk form modal
const selectdDivisis = ref(null);
const selectedSubDivisis = ref(null); // untuk form modal
const selectedLevels = ref(null); // untuk form modal
const openDropdown = ref(null)

const debouncedSearch = debounce(() => {
  router.get('/position-data', { search: search.value, status: showInactive.value ? 'inactive' : 'active' }, { preserveState: true, replace: true });
}, 100);

watch(showInactive, (val) => {
  router.get('/position-data', { search: search.value, status: val ? 'inactive' : 'active' }, { preserveState: true, replace: true });
});

function onSearchInput() {
  debouncedSearch();
}

function goToPage(url) {
  if (url) router.visit(url, { preserveState: true, replace: true });
}

function toggle(index) {
  openDropdown.value = openDropdown.value === index ? null : index
}

function openCreate() {
  modalMode.value = 'create';
  selectedDs.value = null;
  selectedJabatans.value = props.jabatans; // reset jabatan
  selectdDivisis.value = props.divisis;
  selectedSubDivisis.value = props.subDivisis; // reset sub divisi
  selectedLevels.value = props.levels; // reset level
  showModal.value = true;
  // router.visit(route('position.create'));
}

function open_edit(ds) {
  // router.visit(route('position.edit', d.id));
  modalMode.value = 'edit';
  selectedDs.value = ds;
  selectedJabatans.value = props.jabatans; // set jabatan yang dipilih
  selectdDivisis.value = props.divisis;
  selectedSubDivisis.value = props.subDivisis; // set sub divisi yang dipilih
  selectedLevels.value = props.levels; // set level yang dipilih
  showModal.value = true;
}

function open_list_menu(ds) {
  router.visit(route('webprofilebrandslistmenu.index', ds.id));
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
          <i class="fa-solid fa-users text-blue-500"></i> Data Brands
        </h1>
        <button @click="openCreate" class="bg-gradient-to-r from-blue-500 to-blue-700 text-white px-4 py-2 rounded-xl shadow-lg hover:shadow-2xl transition-all font-semibold">
          + Buat Data Brands Baru
        </button>
      </div>
      <!-- <div class="flex items-center gap-3 mb-4">
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
      </div> -->
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
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tl-2xl">NO</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Nama Brands</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Link Sosmed</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Image</th>
              <th class="px-6 py-3 min-w-[120px] text-left text-xs font-bold text-blue-700 uppercase tracking-wider rounded-tr-2xl">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="d.data.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Tidak ada data customer.</td>
            </tr>
            <tr v-for="(ds, key) in d.data" :key="ds.id" class="hover:bg-blue-50 transition shadow-sm">
              <td class="px-6 py-3 font-semibold">{{ (d.current_page - 1) * d.per_page + key + 1 }}</td>
              <td class="px-6 py-3">{{ ds.title }}</td>
              <td class="px-6 py-3">
                <a 
                  :href="ds.link_menu" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="text-blue-600 hover:underline hover:text-blue-800 transition"
                >
                  {{ ds.link_menu }}
                </a>
              </td>
              <td class="px-6 py-3">
                <img 
                  :src="`https://justusmember.co.id/assets/justusku_web_profile/images/${ds.image}`" 
                  alt="Brand Image" 
                  class="w-32 h-32 object-cover rounded-xl shadow border hover:scale-105 transition duration-200 ease-in-out"
                />
              </td>
              <!-- <td class="px-6 py-3">
                <button
                  :class="ds.status === 'N' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                  class="px-2 py-1 rounded-full text-xs font-semibold shadow hover:opacity-80 transition"
                  @click="toggleStatus(ds)"
                >
                  {{ ds.status === 'N' ? 'inactive' : 'active' }}
                </button>
              </td> -->
              <td class="px-6 py-3">
                <div class="relative inline-block min-w-[120px] text-left">
                  <button @click="toggle((d.current_page - 1) * d.per_page + key + 1)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2">
                    Pilih Aksi
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </button>

                  <!-- Dropdown menu -->
                  <div v-if="openDropdown === (d.current_page - 1) * d.per_page + key + 1"
                    class="absolute right-0 mt-2 w-40 bg-white border rounded shadow z-50">
                    <ul class="text-sm text-gray-700">
                      <li>
                        <button @click="open_edit(ds)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Edit</button>
                      </li>
                      <li>
                        <button @click="open_list_menu(ds)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">List Menu</button>
                      </li>
                      <!-- <li>
                        <button @click="open_delete(ds)" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Delete</button>
                      </li> -->
                    </ul>
                  </div>
                </div>
                <!-- <div class="flex gap-2">
                  <button @click="openEdit(ds)" class="inline-flex items-center btn btn-xs bg-yellow-100 text-yellow-800 hover:bg-yellow-200 rounded px-2 py-1 font-semibold transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2h6a2 2 0 002-2v-6a2 2 0 00-2-2H7a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    Edit
                  </button>
                  <button @click="hapus(ds)" class="inline-flex items-center btn btn-xs bg-red-100 text-red-700 hover:bg-red-200 rounded px-2 py-1 font-semibold transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Hapus
                  </button>
                </div> -->
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
      <WebProfileBrandsFormModal
        :show="showModal"
        :mode="modalMode"
        :ds="selectedDs"
        :jabatans="selectedJabatans"
        :divisis="selectdDivisis"
        :subDivisis="selectedSubDivisis"
        :levels="selectedLevels"
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