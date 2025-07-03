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
  jabatans: Object, // daftar jabatan untuk form
  divisis: Object, // daftar divisi untuk form
  outlets: Object, // daftar outlet untuk form
  levels: Object, // daftar level untuk form
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

  form.post(route('dailyrevenuereport.index'), {
    onSuccess: () => {
      Swal.close();
      getDailyRevenueChart();
      getDailyCoverChart();
      getDailyAllChart();
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

function getDailyRevenueChart() {
  const dailyRevenueCtx = document.getElementById('dailyRevenueChart').getContext('2d');
  const labelsDailyRevenueCtx = [];
  const dataLunchRevenue = [];
  const dataDinnerRevenue = [];
  const dataTotalRevenue = [];

  props.d.getDataDailyRevenue.forEach(item => {
    const lunch = Number(item.LunchRevenue) || 0
    const dinner = Number(item.DinnerRevenue) || 0
    const total = lunch + dinner

    labelsDailyRevenueCtx.push(item.Tanggal)
    dataLunchRevenue.push(item.LunchRevenue)
    dataDinnerRevenue.push(item.DinnerRevenue)
    dataTotalRevenue.push(total)
  });

  new Chart(dailyRevenueCtx, {
    type: 'bar',
    data: {
      labels: labelsDailyRevenueCtx,
      datasets: [
        {
          label: 'Lunch Revenue',
          data: dataLunchRevenue,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Dinner Revenue',
          data: dataDinnerRevenue,
          backgroundColor: 'rgba(139, 92, 246, 0.7)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Total Revenue',
          data: dataTotalRevenue,
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: getChartOptions('Currency')
  });
}

function getDailyCoverChart() {
  const dailyCoverCtx = document.getElementById('dailyCoverChart').getContext('2d');
  const labelsDailyCoverCtx = [];
  const dataLunchCover = [];
  const dataDinnerCover = [];
  const dataTotalCover = [];

  props.d.getDataDailyRevenue.forEach(item => {
    const lunch = Number(item.LunchCoverage) || 0
    const dinner = Number(item.DinnerCoverage) || 0
    const total = lunch + dinner

    labelsDailyCoverCtx.push(item.Tanggal)
    dataLunchCover.push(item.LunchCoverage)
    dataDinnerCover.push(item.DinnerCoverage)
    dataTotalCover.push(total)
  });

  new Chart(dailyCoverCtx, {
    type: 'bar',
    data: {
      labels: labelsDailyCoverCtx,
      datasets: [
        {
          label: 'Lunch Coverage',
          data: dataLunchCover,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Dinner Coverage',
          data: dataDinnerCover,
          backgroundColor: 'rgba(139, 92, 246, 0.7)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Total Coverage',
          data: dataTotalCover,
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: getChartOptions('Currency')
  });
}

function getDailyAllChart() {
  const dailyAllCtx = document.getElementById('dailyAllChart').getContext('2d');
  const labelsDailyAllCtx = [];
  const dataLunchAll = [];
  const dataDinnerAll = [];
  const dataTotalAll = [];

  props.d.getDataDailyRevenue.forEach(item => {
    const lunch = Number(item.AllRevenue) || 0
    const dinner = Number(item.AllCoverage) || 0
    const total = lunch + dinner

    labelsDailyAllCtx.push(item.Tanggal)
    dataLunchAll.push(item.AllRevenue)
    dataDinnerAll.push(item.AllCoverage)
    dataTotalAll.push(total)
  });

  new Chart(dailyAllCtx, {
    type: 'bar',
    data: {
      labels: labelsDailyAllCtx,
      datasets: [
        {
          label: 'Lunch A/C',
          data: dataLunchAll,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Dinner A/C',
          data: dataDinnerAll,
          backgroundColor: 'rgba(139, 92, 246, 0.7)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Total A/C',
          data: dataTotalAll,
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: getChartOptions('Currency')
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
  { key: 'Tanggal', label: 'DATE' },
  { key: 'Hari', label: 'DAY' },
  { key: 'LunchPax', label: 'L.COVER' },
  { key: 'LunchRevenue', label: 'L.REVENUE' },
  { key: 'LunchCoverage', label: 'L.A/C' },
  { key: 'LunchDiscount', label: 'L.DISC' },
  { key: 'DinnerPax', label: 'D.COVER' },
  { key: 'DinnerRevenue', label: 'D.REVENUE' },
  { key: 'DinnerCoverage', label: 'D.A/C' },
  { key: 'DinnerDiscount', label: 'D.DISC' },
  { key: 'AllPax', label: 'T.COVER' },
  { key: 'AllRevenue', label: 'T.REVENUE' },
  { key: 'AllCoverage', label: 'T.A/C' },
  { key: 'AllDiscount', label: 'T.DISC' },
]

const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10
const sortField = ref('nama_outlet')
const sortDirection = ref('desc')

const filteredData = computed(() => {
  return props.d.getDataDailyRevenue.filter(outlet =>
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
          <h1 class="text-2xl font-bold text-gray-800">Daily Report Revenue</h1>
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
          <div class="w-[250px]">
            <select v-model="form.id_outlet" @change="emitOutlet" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option disabled value="">-- Pilih Outlet--</option>
              <option v-for="outlet in outlets" :key="outlet.id_outlet" :value="outlet.id_outlet">
                {{ outlet.nama_outlet }}
              </option>
            </select>
            <div v-if="form.errors.id_outlet" class="text-xs text-red-500 mt-1">{{ form.errors.id_outlet }}</div>
          </div>
          <div class="w-[200px]">
            <input type="month" v-model="form.bulan_tahun" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.bulan_tahun" class="text-xs text-red-500 mt-1">{{ form.errors.bulan_tahun }}</div>
          </div>
          <div class="w-[250px]">
            <label class="block text-sm font-medium text-gray-700" for="category"></label>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50">CREATE OR VIEW</button>
          </div>
        </div>
        </form>

        <div class="bg-white rounded-lg shadow p-6 space-y-3 text-sm text-gray-700">
          <div class="font-semibold">Monthly Budget: <span class="font-normal">Data tidak tersedia</span></div>
          <div class="font-semibold">MTD Performance: <span class="font-normal">Data tidak tersedia</span></div>
          <div class="font-semibold">No. of Days: <span class="font-normal"></span></div>
          <div class="font-semibold">No. of Weekdays: <span class="font-normal"></span></div>
          <div class="font-semibold">No. of Weekends: <span class="font-normal"></span></div>
          <div class="font-semibold">Day to Date: <span class="font-normal"></span></div>
          <div class="font-semibold">Weekdays to Date: <span class="font-normal"></span></div>
          <div class="font-semibold">Weekends to Date: <span class="font-normal"></span></div>
        </div>
        <br>
        <!-- Outlet Transactions Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div class="p-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Month To Date Outlet Member Transaction</h2>
          </div>

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
                  <td class="px-6 py-4 text-sm text-gray-500">{{ outlet.Tanggal }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">{{ outlet.Hari }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.LunchPax) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.LunchRevenue) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.LunchCoverage) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.LunchDiscount) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.DinnerPax) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.DinnerRevenue) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.DinnerCoverage) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.DinnerDiscount) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.AllPax) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.AllRevenue) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.AllCoverage) }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.AllDiscount) }}</td>
                </tr>
                <tr class="bg-gray-100 font-semibold text-gray-700">
                  <td class="px-4 py-3 text-sm">Total</td>
                  <td class="px-4 py-3 text-sm"></td> <!-- Tanggal -->
                  <td class="px-4 py-3 text-sm"></td> <!-- Hari -->
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.LunchPax) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.LunchRevenue) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.LunchCoverage) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.LunchDiscount) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.DinnerPax) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.DinnerRevenue) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.DinnerCoverage) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.DinnerDiscount) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.AllPax) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.AllRevenue) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.AllCoverage) }}</td>
                  <td class="px-4 py-3 text-sm text-right">{{ formatCurrency(d.getDataDailyRevenueTotal.AllDiscount) }}</td>
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

        <div class="mb-10">
          <!-- Current Year Transactions -->
          <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-700 mb-4">DAILY REVENUE CHART</h3>
            <div class="h-80">
              <canvas id="dailyRevenueChart"></canvas>
            </div>
          </div>
        </div>

        <div class="mb-10">
          <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-700 mb-4">DAILY COVER CHART</h3>
            <div class="h-80">
              <canvas id="dailyCoverChart"></canvas>
            </div>
          </div>
        </div>

        <div class="mb-10">
          <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h3 class="text-lg font-medium text-gray-700 mb-4">DAILY A/C CHART</h3>
            <div class="h-80">
              <canvas id="dailyAllChart"></canvas>
            </div>
          </div>
        </div>

        <div class="p-4">
          <h2 class="text-md font-semibold text-gray-700 mb-3">WALK IN REVENUE</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ORDER TYPE
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    REVENUE
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, index) in props.d.getDataWalkInRevenue" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ row.ModifiedOrderType }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                    {{ formatCurrency(row.Total) }}
                  </td>
                </tr>
                <tr v-if="!props.d.getDataWalkInRevenue || props.d.getDataWalkInRevenue.length === 0">
                  <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data transaksi.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="p-4">
          <h2 class="text-md font-semibold text-gray-700 mb-3">MARKETPLACE REVENUE</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ORDER TYPE
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    REVENUE
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, index) in props.d.getDataMarketplaceRevenue" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ row.ModifiedOrderType }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                    {{ formatCurrency(row.Total) }}
                  </td>
                </tr>
                <tr v-if="!props.d.getDataMarketplaceRevenue || props.d.getDataMarketplaceRevenue.length === 0">
                  <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data transaksi.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="p-4">
          <h2 class="text-md font-semibold text-gray-700 mb-3">JENIS PEMBAYARAN</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ORDER TYPE
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    REVENUE
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, index) in props.d.getDataJenisPembayaran" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ row.ModifiedOrderType }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                    {{ formatCurrency(row.Total) }}
                  </td>
                </tr>
                <tr v-if="!props.d.getDataJenisPembayaran || props.d.getDataJenisPembayaran.length === 0">
                  <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data transaksi.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="p-4">
          <h2 class="text-md font-semibold text-gray-700 mb-3">WAITER/SS PRODUCTIVITY</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    ORDER TYPE
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    REVENUE
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, index) in props.d.getDataWaiterProduc" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ row.ModifiedOrderType }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                    {{ formatCurrency(row.Total) }}
                  </td>
                </tr>
                <tr v-if="!props.d.getDataWaiterProduc || props.d.getDataWaiterProduc.length === 0">
                  <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                    Tidak ada data transaksi.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
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