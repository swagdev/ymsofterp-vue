<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import Swal from 'sweetalert2';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Switch } from '@headlessui/vue';
import EmployeeFormModal from './EmployeeFormModal.vue';
import { Chart } from 'chart.js/auto';

const props = defineProps({
  d: Object, // { data, links, meta }
  filters: Object,
});

const today = new Date();
const currentMonthYear = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;

const form = useForm({
  // Your form fields here
  id_outlet: props.d?.id_outlet || '',
  bulan_tahun: props.d?.bulan_tahun || currentMonthYear,
});

function onSubmit() {
  // Tampilkan loading
  // Swal.fire({
  //   title: 'Load Data...',
  //   text: 'Mohon tunggu sebentar',
  //   allowOutsideClick: false,
  //   allowEscapeKey: false,
  //   showConfirmButton: false,
  //   didOpen: () => {
  //     Swal.showLoading();
  //   }
  // });

  form.post(route('listrevenue.index'), {
    onSuccess: () => {
      Swal.close();
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


onMounted(() => {
  
});

function getChartOptions(dataType) {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'bottom',
        labels: {
          boxWidth: 12,
          padding: 20
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            let label = context.dataset.label || '';
            if (label) {
              label += ': ';
            }
            if (dataType === 'Currency') {
              label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
            } else {
              label += context.parsed.y.toLocaleString('id-ID');
            }
            return label;
          }
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            if (dataType === 'Currency') {
              return 'Rp ' + value.toLocaleString('id-ID');
            }
            return value.toLocaleString('id-ID');
          }
        }
      }
    }
  };
}

const columns = [
  { key: 'nama_outlet', label: 'OUTLET' },
  { key: 'Total', label: 'TOTAL' },
  { key: 'Disc', label: 'DISCOUNT' },
  { key: 'Redeem', label: 'REDEEM' },
  { key: 'DPP', label: 'DPP' },
  { key: 'PPN', label: 'PB1' },
  { key: 'Service', label: 'SERVICE' },
  { key: 'Commfee', label: 'COMMFEE' },
  { key: 'GrandTotal', label: 'GRAND TOTAL' },
  { key: 'Cover', label: 'COVER' },
  { key: 'AC', label: 'AVERAGE CHECK' }
]

const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10
const sortField = ref('nama_outlet')
const sortDirection = ref('desc')

const filteredData = computed(() => {
  return props.d.getListRevenue.filter(outlet =>
    outlet.nama_outlet.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const sortedData = computed(() => {
  return [...filteredData.value].sort((a, b) => {
    const valA = a[sortField.value] || ''
    const valB = b[sortField.value] || ''
    return sortDirection.value === 'asc'
      ? String(valA).localeCompare(String(valB))
      : String(valB).localeCompare(String(valA))
  })
})

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage
  return sortedData.value.slice(start, start + perPage)
})

const total = computed(() => sortedData.value.length)
const totalPages = computed(() => Math.ceil(total.value / perPage))
const from = computed(() => (currentPage.value - 1) * perPage + 1)
const to = computed(() => Math.min(currentPage.value * perPage, total.value))

function sort(field) {
  if (sortField.value === field) {
    // toggle antara desc <-> asc
    sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc'
  } else {
    sortField.value = field
    sortDirection.value = 'desc' // default pertama kali klik = DESC
  }
}

function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}
function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}
function goToPage(page) {
  currentPage.value = page
}

function formatCurrency(value) {
  return Number(value || 0).toLocaleString('id-ID')
}

function numberFormat(value) {
  return new Intl.NumberFormat('id-ID').format(value)
}

//table 2

// const columns = [
//   { key: 'name', label: 'Name Outlet' },
//   { key: 'transaksi', label: 'Transaksi' },
//   { key: 'topup', label: 'Topup' },
//   { key: 'redeem', label: 'Redeem' },
//   { key: 'saldo', label: 'Saldo' },
// ]

// const searchQuery = ref('')
// const currentPage = ref(1)
// const perPage = 10
// const sortField = ref('transaksi')
// const sortDirection = ref('desc')

// const filteredData = computed(() => {
//   return props.d.getTabelPointInMonth.filter(outlet =>
//     outlet.name.toLowerCase().includes(searchQuery.value.toLowerCase())
//   )
// })

// const sortedData = computed(() => {
//   return [...filteredData.value].sort((a, b) => {
//     const valA = a[sortField.value] || ''
//     const valB = b[sortField.value] || ''
//     return sortDirection.value === 'asc'
//       ? String(valA).localeCompare(String(valB))
//       : String(valB).localeCompare(String(valA))
//   })
// })

// const paginatedData = computed(() => {
//   const start = (currentPage.value - 1) * perPage
//   return sortedData.value.slice(start, start + perPage)
// })

// const total = computed(() => sortedData.value.length)
// const totalPages = computed(() => Math.ceil(total.value / perPage))
// const from = computed(() => (currentPage.value - 1) * perPage + 1)
// const to = computed(() => Math.min(currentPage.value * perPage, total.value))

// function sort(field) {
//   if (sortField.value === field) {
//     // toggle antara desc <-> asc
//     sortDirection.value = sortDirection.value === 'desc' ? 'asc' : 'desc'
//   } else {
//     sortField.value = field
//     sortDirection.value = 'desc' // default pertama kali klik = DESC
//   }
// }

// function prevPage() {
//   if (currentPage.value > 1) currentPage.value--
// }
// function nextPage() {
//   if (currentPage.value < totalPages.value) currentPage.value++
// }
// function goToPage(page) {
//   currentPage.value = page
// }

// function formatCurrency(value) {
//   return Number(value || 0).toLocaleString('id-ID')
// }

</script>

<template>
  <AppLayout>
    <div class="max-w-7xl w-full mx-auto py-8 px-2">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-2xl font-bold text-gray-800">List Revenue</h1>
          <div class="flex space-x-3">
            <!-- <button class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center">
              <i class="fas fa-download mr-2"></i> Export
            </button> -->
          </div>
        </div>

        <form @submit.prevent="onSubmit" class="space-y-6" enctype="multipart/form-data">
        <div class="flex items-center gap-3 mb-4">
          <!-- <div class="w-[250px]">
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
          </div> -->
          <div class="w-[200px]">
            <input type="month" v-model="form.bulan_tahun" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.bulan_tahun" class="text-xs text-red-500 mt-1">{{ form.errors.bulan_tahun }}</div>
          </div>
          <div class="w-[250px]">
            <label class="block text-sm font-medium text-gray-700" for="category"></label>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50">SUBMIT</button>
          </div>
        </div>
        </form>

        <!-- Outlet Transactions Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="flex justify-between items-center p-4 border-b">
            <div class="flex items-center">
              <span class="text-sm text-gray-600">Show</span>
              <select 
                v-model="perPage"
                class="mx-2 border border-gray-300 rounded px-2 py-1 text-sm"
                @change="updatePagination"
              >
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
              <span class="text-sm text-gray-600">entries</span>
            </div>
            <div class="mb-4">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search outlet..."
                class="border px-4 py-2 rounded w-full sm:w-64"
              />
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="col in columns" :key="col.key" @click="sort(col.key)" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                    {{ col.label }}
                    <i v-if="sortField === col.key" :class="['fas ml-1', sortDirection === 'desc' ? 'fa-sort-up' : 'fa-sort-down']"></i>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="outlet in paginatedData" :key="outlet.name" class="hover:bg-gray-50">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ outlet.nama_outlet }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Total) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Disc) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Redeem) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.DPP) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.PPN) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Service) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Commfee) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.GrandTotal) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.Cover) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.AC) }}</td>
                </tr>
                <tr class="bg-gray-100 font-semibold text-gray-700">
                  <td class="px-4 py-3 text-sm">Total</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Total) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Disc) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Redeem) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.DPP) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.PPN) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Service) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Commfee) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.GrandTotal) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.Cover) }}</td>
                  <td class="px-4 py-3 text-sm text-right">Rp {{ formatCurrency(d.getListRevenueTotal.GrandTotal/d.getListRevenueTotal.Cover) }}</td>
                </tr>
                <tr v-if="paginatedData.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No outlets found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-between items-center px-4 py-3 border-t border-gray-200">
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of <span class="font-medium">{{ total }}</span> entries
            </p>
            <div class="flex gap-1">
              <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 text-sm text-gray-600 bg-white border rounded hover:bg-gray-100 disabled:opacity-50">
                <i class="fas fa-chevron-left"></i>
              </button>
              <button v-for="page in totalPages" :key="page" @click="goToPage(page)" :class="[currentPage === page ? 'bg-indigo-500 text-white' : 'bg-white text-gray-700', 'px-3 py-1 text-sm border rounded']">
                {{ page }}
              </button>
              <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 text-sm text-gray-600 bg-white border rounded hover:bg-gray-100 disabled:opacity-50">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
        <br>

        
    </div>
  </AppLayout>
</template>

<style scoped>
.bg-3d {
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 1.5px 4px 0 rgba(31, 38, 135, 0.08);
}
.fas.fa-sort-up::before { content: "\f0de"; }
.fas.fa-sort-down::before { content: "\f0dd"; }
</style> 