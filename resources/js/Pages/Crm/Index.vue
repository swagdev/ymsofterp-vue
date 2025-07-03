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

onMounted(() => {
  // Current Year Chart
  const currentNewMonthMemberCtx = document.getElementById('memberNewMonthMemberChart').getContext('2d');
  const labelsNewMember = [];
  const dataNewMember = [];

  props.d.getNewMemberGrafik.forEach(item => {
    labelsNewMember.push(item.month)
    dataNewMember.push(item.total)
  });

  new Chart(currentNewMonthMemberCtx, {
    type: 'line',
    data: {
      labels: labelsNewMember,
      datasets: [{
        label: 'New Members',
        data: dataNewMember,
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        borderColor: 'rgba(79, 70, 229, 1)',
        borderWidth: 2,
        tension: 0.3,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (context) => context.parsed.y.toLocaleString()
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => value.toLocaleString()
          }
        }
      }
    }
  });


  // Member Transactions Charts
  const currentlastYearMemberCtx = document.getElementById('lastYearMemberChart').getContext('2d');
  const labelsLastYearMember = [];
  const dataLastYearMember = [];

  props.d.getLastYearMemberGrafik.forEach(item => {
    labelsLastYearMember.push(item.month)
    dataLastYearMember.push(item.total)
  });

  new Chart(currentlastYearMemberCtx, {
    type: 'line',
    data: {
      labels: labelsLastYearMember,
      datasets: [{
        label: 'New Members',
        data: dataLastYearMember,
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        borderColor: 'rgba(79, 70, 229, 1)',
        borderWidth: 2,
        tension: 0.3,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (context) => context.parsed.y.toLocaleString()
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: (value) => value.toLocaleString()
          }
        }
      }
    }
  });

  const monthMemberTransactionCtx = document.getElementById('monthMemberTransactionChart').getContext('2d');
  const labelsMonthTransaction = [];
  const dataMonthTransaction = [];
  const dataMonthTopUp = [];
  const dataMonthRedeem = [];

  props.d.monthTransaksiGrafik.forEach(item => {
    labelsMonthTransaction.push(item.month)
    dataMonthTransaction.push(item.transaksi)
    dataMonthTopUp.push(item.topup)
    dataMonthRedeem.push(item.redeem)
  });

  new Chart(monthMemberTransactionCtx, {
    type: 'bar',
    data: {
      labels: labelsMonthTransaction,
      datasets: [
        {
          label: 'Transaksi',
          data: dataMonthTransaction,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Point',
          data: dataMonthTopUp,
          backgroundColor: 'rgba(139, 92, 246, 0.7)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Redeem',
          data: dataMonthRedeem,
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: getChartOptions('Currency')
  });

  // Last Year Chart
  const lastYearTransaksiCtx = document.getElementById('lastYearTransactionChart').getContext('2d');
  const labelsLastYearTransaksi = [];
  const dataLastYearTransaksi = [];
  const dataLastYearTopUp = [];
  const dataLastYearRedeem = [];

  props.d.lastYearTransaksiGrafik.forEach(item => {
    labelsLastYearTransaksi.push(item.month)
    dataLastYearTransaksi.push(item.transaksi)
    dataLastYearTopUp.push(item.topup)
    dataLastYearRedeem.push(item.redeem)
  });

  new Chart(lastYearTransaksiCtx, {
    type: 'bar',
    data: {
      labels: labelsLastYearTransaksi,
      datasets: [
        {
          label: 'Transaksi',
          data: dataLastYearTransaksi,
          backgroundColor: 'rgba(59, 130, 246, 0.7)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Point',
          data: dataLastYearTopUp,
          backgroundColor: 'rgba(139, 92, 246, 0.7)',
          borderColor: 'rgba(139, 92, 246, 1)',
          borderWidth: 1
        },
        {
          label: 'Redeem',
          data: dataLastYearRedeem,
          backgroundColor: 'rgba(239, 68, 68, 0.7)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1
        }
      ]
    },
    options: getChartOptions('Currency')
  });
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
  { key: 'name', label: 'Name Outlet' },
  { key: 'transaksi', label: 'Transaksi' },
  { key: 'topup', label: 'Topup' },
  { key: 'redeem', label: 'Redeem' },
  { key: 'saldo', label: 'Saldo' },
]

const searchQuery = ref('')
const currentPage = ref(1)
const perPage = 10
const sortField = ref('transaksi')
const sortDirection = ref('desc')

const filteredData = computed(() => {
  return props.d.getTabelPointInMonth.filter(outlet =>
    outlet.name.toLowerCase().includes(searchQuery.value.toLowerCase())
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
          <h1 class="text-2xl font-bold text-gray-800">YMSOFT CRM DASHBOARD June 2025</h1>
          <div class="flex space-x-3">
            <button class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 flex items-center">
              <i class="fas fa-download mr-2"></i> Export
            </button>
          </div>
        </div>

        <!-- Month To Date Statistics -->
        <div class="mb-10">
            <h2 class="text-xl font-semibold text-gray-700 mb-6 pb-2 border-b border-gray-200">Month To Date Statistics</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Sales Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Sales</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ numberFormat(d.getTotalTransaksiInMounth) }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-wallet text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- New Members Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">New Members</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalNewMemberMounth) }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-user-plus text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Top Up Points Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Top Up Points</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ numberFormat(d.getTotalPointInMounth) }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-arrow-up text-lg"></i>
                        </div>
                    </div>
                </div>

                <!-- Redeem Points Card -->
                <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-red-500 hover:shadow-lg transition-all duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">Redeem Points</p>
                            <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ numberFormat(d.getTotalRedeemInMounth) }}</p>
                        </div>
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-arrow-down text-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Member Statistics -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold text-gray-700 mb-6 pb-2 border-b border-gray-200">Member Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
          <!-- Total Members Card -->
          <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-indigo-500 hover:shadow-lg transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-500 text-sm font-medium">Total Members</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalMember) }}</p>
              </div>
              <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <i class="fas fa-users text-lg"></i>
              </div>
            </div>
          </div>

          <!-- Bandung Members Card -->
          <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-amber-500 hover:shadow-lg transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-500 text-sm font-medium">Bandung Members</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalMemberBandung) }}</p>
              </div>
              <div class="p-3 rounded-full bg-amber-100 text-amber-600">
                <i class="fas fa-map-marker-alt text-lg"></i>
              </div>
            </div>
          </div>

          <!-- Jakarta Members Card -->
          <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-cyan-500 hover:shadow-lg transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-500 text-sm font-medium">Jakarta Members</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalMemberJakarta) }}</p>
              </div>
              <div class="p-3 rounded-full bg-cyan-100 text-cyan-600">
                <i class="fas fa-city text-lg"></i>
              </div>
            </div>
          </div>

          <!-- Valid Members Card -->
          <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-emerald-500 hover:shadow-lg transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-500 text-sm font-medium">Valid Members</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalActiveMember) }}</p>
              </div>
              <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                <i class="fas fa-check-circle text-lg"></i>
              </div>
            </div>
          </div>

          <!-- Aktif Members Card -->
          <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-pink-500 hover:shadow-lg transition-all duration-300">
            <div class="flex justify-between items-start">
              <div>
                <p class="text-gray-500 text-sm font-medium">Aktif Members</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ numberFormat(d.getTotalActiveMemberTransactions) }}</p>
              </div>
              <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                <i class="fas fa-star text-lg"></i>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Members Growth Section -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold text-gray-700 mb-6 pb-2 border-b border-gray-200">Members Growth</h2>
        
        <!-- Current Year Growth -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
          <h3 class="text-lg font-medium text-gray-700 mb-4">New Members Growth by Month</h3>
          <div class="h-80">
            <canvas id="memberNewMonthMemberChart"></canvas>
          </div>
        </div>

        <!-- Last Year Growth --> 
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
          <h3 class="text-lg font-medium text-gray-700 mb-4">Last Year Members Growth</h3>
          <div class="h-80">
            <canvas id="lastYearMemberChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Member Transactions Section -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold text-gray-700 mb-6 pb-2 border-b border-gray-200">Member Transactions</h2>
        
        <!-- Current Year Transactions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
          <h3 class="text-lg font-medium text-gray-700 mb-4">Monthly Member Transaction Chart</h3>
          <div class="h-80">
            <canvas id="monthMemberTransactionChart"></canvas>
          </div>
        </div>

        <!-- Last Year Transactions -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-medium text-gray-700 mb-4">Monthly Member Transaction Last Year Chart</h3>
          <div class="h-80">
            <canvas id="lastYearTransactionChart"></canvas>
          </div>
        </div>
      </div>


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
                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ outlet.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.transaksi) }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.topup) }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.redeem) }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">Rp {{ formatCurrency(outlet.saldo) }}</td>
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