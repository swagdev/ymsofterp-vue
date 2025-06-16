<script setup>
import { ref, reactive, watch, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

const props = defineProps({
  customer: Object, // untuk edit
  jabatans: Object,
  divisis: Object,
  outlets: Object,
  roles: Object,
});

const isEdit = computed(() => {
  return props.customer && props.customer.id;
});

const form = useForm({
    nik : props.customer?.nik || '',
    name : props.customer?.nama_lengkap || '',
    type : props.customer?.type || 'branch',
    region : props.customer?.region || '',
    status : props.customer?.status || 'active',

    gender : props.customer?.jenis_kelamin || '',
    place_birth : props.customer?.tempat_lahir || '',
    date_birth : props.customer?.tanggal_lahir || '', // Perbaikan nama field
    domicile : props.customer?.alamat || '',
    domicile_ktp : props.customer?.alamat_ktp || '',
    religion : props.customer?.agama || '',
    blood_type : props.customer?.golongan_darah || '', // Nama field diubah ke format Inggris
    wa_number : props.customer?.no_hp || '', // Nama field diubah
    email : props.customer?.email || '',
    password : '',
    imei : props.customer?.imei || '',
    id_jabatan: props.customer?.id_jabatan || '',
    id_divisi: props.customer?.division_id || '',
    id_outlet: props.customer?.id_outlet || '',
    id_role: props.customer?.id_role || '',

    // Kontak Darurat
    name_emergency_contact : props.customer?.nama_kontak_darurat || '',
    emergency_contact : props.customer?.no_hp_kontak_darurat || '', // Field baru
    emergency_contact_relationship : props.customer?.hubungan_kontak_darurat || '',

    // Keluarga
    marital_status : props.customer?.status_pernikahan || '',
    number_of_children : props.customer?.jumlah_anak || '',
    spouse : props.customer?.nama_pasangan || '',
    wa_spouse : props.customer?.wa_pasangan || '',

    // Identitas
    id_card : props.customer?.no_ktp || '',
    upload_id_card : props.customer?.foto_ktp || null,
    family_card_number : props.customer?.nomor_kk || '',
    upload_family_card : props.customer?.foto_kk || null,

    // Keuangan
    bca_account_number : props.customer?.no_rekening || '',
    bca_account_name : props.customer?.nama_rekening || '',
    npwp_number : props.customer?.npwp_number || '',
    bpjs_health_number : props.customer?.bpjs_health_number || '',
    bpjs_employment_number : props.customer?.bpjs_employment_number || '',

    // Pendidikan
    last_education : props.customer?.last_education || '',
    name_school_college : props.customer?.name_school_college || '',
    school_college_major : props.customer?.school_college_major || '',

    // Pekerjaan
    work_start_date : props.customer?.work_start_date || '',
    position : props.customer?.position || '',
    upload_latest_color_photo : props.customer?.upload_latest_color_photo || null,
});

const itemInputRefs = ref([]);

function onSubmit() {
  // Tampilkan loading
  Swal.fire({
    title: 'Menyimpan Data...',
    text: 'Mohon tunggu sebentar',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  if (isEdit.value) {
    form.post(route('employee.update', props.customer.id), {
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
  } else {
    form.post(route('employee.store'), {
      onSuccess: () => {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Data berhasil disimpan',
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          // router.visit(route('employee.index'));
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
}

function goBack() {
  router.visit(route('employee.index'));
}

onMounted(() => {
  // window.addEventListener('keydown', handleF1Focus);
  
  // Set tanggal otomatis ke hari ini jika bukan mode edit dan tanggal belum diset
  if (!isEdit.value && !form.tanggal) {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    form.tanggal = `${year}-${month}-${day}`;
  }
});

function handleF1Focus(e) {
  if (e.key === 'F1') {
    e.preventDefault();
    console.log('F1 pressed!');
    const lastIdx = form.items.length - 1;
    const inputId = `item-input-${lastIdx}`;
    const input = document.getElementById(inputId);
    if (input) {
      input.focus();
      input.select();
      // Tambahkan highlight visual
      input.style.outline = '2px solid #2563eb';
      input.style.boxShadow = '0 0 0 2px rgba(37, 99, 235, 0.2)';
      setTimeout(() => {
        input.style.outline = '';
        input.style.boxShadow = '';
      }, 1000);
    }
  }
}

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    // Preview gambar di frontend
    const previewUrl = URL.createObjectURL(file);
    form.upload_id_card = previewUrl;

    // Simpan file asli ke form (untuk dikirim ke server)
    form.id_card_file = file;
  }
}

</script>
<template>
  <AppLayout>
    <div class="max-w-5xl w-full mx-auto py-8 px-2">
      <div class="flex items-center gap-2 mb-6">
        <button @click="goBack" class="text-blue-500 hover:underline"><i class="fa fa-arrow-left"></i> Kembali</button>
        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2 ml-4">
          <i class="fa-solid fa-file-invoice text-blue-500"></i> {{ isEdit ? 'Edit' : 'Tambah' }} Karyawan
        </h1>
      </div>
      <form @submit.prevent="onSubmit" class="space-y-6" enctype="multipart/form-data">
          <!-- Basic Info -->
          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Nik</label>
              <input v-model="form.nik" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" maxlength="50" />
              <div v-if="form.errors.nik" class="text-xs text-red-500 mt-1">{{ form.errors.nik }}</div>
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Nama Lengkap Sesuai KTP</label>
              <input v-model="form.name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" maxlength="100" />
              <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
            </div>
          </div>
          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
              <select v-model="form.gender" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" >
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
              <div v-if="form.errors.gender" class="text-xs text-red-500 mt-1">{{ form.errors.gender }}</div>
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
              <input v-model="form.place_birth" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="255" />
              <div v-if="form.errors.place_birth" class="text-xs text-red-500 mt-1">{{ form.errors.place_birth }}</div>
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
              <input 
                type="date" 
                v-model="form.date_birth" 
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                
              />
              <div v-if="form.errors.date_birth" class="text-xs text-red-500 mt-1">{{ form.errors.date_birth }}</div>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Alamat Lengkap Domisili</label>
            <textarea v-model="form.domicile"  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            <div v-if="form.errors.domicile" class="text-xs text-red-500 mt-1">{{ form.errors.domicile }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Alamat Lengkap Sesuai KTP</label>
            <textarea v-model="form.domicile_ktp"  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
            <div v-if="form.errors.domicile_ktp" class="text-xs text-red-500 mt-1">{{ form.errors.domicile_ktp }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Agama</label>
            <div class="flex flex-wrap gap-4">
              <!-- Islam -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Islam" v-model="form.religion" />
                <span>Islam</span>
              </label>

              <!-- Kristen -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Kristen" v-model="form.religion" />
                <span>Kristen</span>
              </label>

              <!-- Katolik -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Katolik" v-model="form.religion" />
                <span>Katolik</span>
              </label>

              <!-- Hindu -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Hindu" v-model="form.religion" />
                <span>Hindu</span>
              </label>

              <!-- Budha -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Budha" v-model="form.religion" />
                <span>Budha</span>
              </label>

              <!-- Lainnya -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="Lainnya" v-model="form.religion" />
                <span>Lainnya</span>
                <input
                  v-if="form.religion === 'Lainnya'"
                  v-model="form.religion_lainnya"
                  type="text"
                  class="border rounded px-2 py-1 w-40"
                  placeholder="Contoh: Konghucu"
                />
              </label>
            </div>

            <!-- Error -->
            <div v-if="form.errors.religion" class="text-xs text-red-500 mt-1">
              {{ form.errors.religion }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Golongan Darah</label>
            <div class="flex flex-wrap gap-6">
              <!-- A -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="A" v-model="form.blood_type" />
                <span>A</span>
              </label>

              <!-- B -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="B" v-model="form.blood_type" />
                <span>B</span>
              </label>

              <!-- O -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="O" v-model="form.blood_type" />
                <span>O</span>
              </label>

              <!-- AB -->
              <label class="flex items-center space-x-2">
                <input type="radio" value="AB" v-model="form.blood_type" />
                <span>AB</span>
              </label>
            </div>

            <!-- Error message -->
            <div v-if="form.errors.blood_type" class="text-xs text-red-500 mt-1">
              {{ form.errors.blood_type }}
            </div>
          </div>
          
          <!-- Contact Info -->
          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
              <input 
                type="text"
                v-model="form.wa_number"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="08xxxxxxxxxx"
                
              />
              <div v-if="form.errors.wa_number" class="text-xs text-red-500 mt-1">{{ form.errors.wa_number }}</div>
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Alamat Email</label>
              <input 
                type="email"
                v-model="form.email"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="contoh@email.com"
                
              />
              <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Password</label>
              <input 
                type="password"
                v-model="form.password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan password"
                
              />
              <div v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</div>
            </div>
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">IMEI*</label>
              <input 
                type="text"
                v-model="form.imei"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan IMEI perangkat"
                
              />
              <div v-if="form.errors.imei" class="text-xs text-red-500 mt-1">{{ form.errors.imei }}</div>
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <!-- Jabatan -->
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Jabatan</label>
              <select
                v-model="form.id_jabatan"
                @change="emitJabatan"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option disabled value="">-- Pilih Jabatan --</option>
                <option
                  v-for="jabatan in jabatans"
                  :key="jabatan.id_jabatan"
                  :value="jabatan.id_jabatan"
                >
                  {{ jabatan.nama_jabatan }}
                </option>
              </select>
              <div v-if="form.errors.id_jabatan" class="text-xs text-red-500 mt-1">
                {{ form.errors.id_jabatan }}
              </div>
            </div>

            <!-- Divisi -->
            <div class="flex-1 min-w-[200px]">
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
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700" for="category">Outlet*</label>
              <select v-model="form.id_outlet" @change="emitOutlet" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option disabled value="">-- Pilih Outlet--</option>
                <option v-for="outlet in outlets" :key="outlet.id_outlet" :value="outlet.id_outlet">
                  {{ outlet.nama_outlet }}
                </option>
              </select>
              <div v-if="form.errors.id_outlet" class="text-xs text-red-500 mt-1">{{ form.errors.id_outlet }}</div>
            </div>

            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700" for="category">Role*</label>
              <select v-model="form.id_role" @change="emitRole" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option disabled value="">-- Pilih Role --</option>
                <option v-for="role in roles" :key="role.role_id" :value="role.role_id">
                  {{ role.nama_role }}
                </option>
              </select>
              <div v-if="form.errors.id_role" class="text-xs text-red-500 mt-1">{{ form.errors.id_role }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Hubungan dengan kontak darurat
            </label>
            
            <div class="flex flex-wrap items-center gap-4">
              <label class="flex items-center">
                <input
                  type="radio"
                  value="Suami/Istri"
                  v-model="form.emergency_contact_relationship"
                />
                <span class="ml-2">Suami/Istri</span>
              </label>

              <label class="flex items-center">
                <input
                  type="radio"
                  value="Orang Tua"
                  v-model="form.emergency_contact_relationship"
                />
                <span class="ml-2">Orang Tua</span>
              </label>

              <label class="flex items-center">
                <input
                  type="radio"
                  value="Kakak/Adik"
                  v-model="form.emergency_contact_relationship"
                />
                <span class="ml-2">Kakak/Adik</span>
              </label>

              <label class="flex items-center">
                <input
                  type="radio"
                  value="Lainnya"
                  v-model="form.emergency_contact_relationship"
                />
                <span class="ml-2">Keluarga Lainnya:</span>
                <input
                  v-if="form.emergency_contact_relationship === 'Lainnya'"
                  v-model="form.keterangan_lainnya"
                  type="text"
                  class="ml-2 border rounded px-2 py-1 w-48"
                  placeholder="Contoh: Sepupu"
                />
              </label>
            </div>

            <div
              v-if="form.errors.emergency_contact_relationship"
              class="text-xs text-red-500 mt-1"
            >
              {{ form.errors.emergency_contact_relationship }}
            </div>
          </div>

          <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Nama Kontak Darurat</label>
              <input 
                type="text"
                v-model="form.name_emergency_contact"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan nama kontak darurat"
                
              />
              <div v-if="form.errors.name_emergency_contact" class="text-xs text-red-500 mt-1">{{ form.errors.name_emergency_contact }}</div>
            </div>

            <div class="flex-1 min-w-[200px]">
              <label class="block text-sm font-medium text-gray-700">Nomor telepon kontak darurat *</label>
              <input 
                type="text"
                v-model="form.emergency_contact"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan nama kontak darurat"
                
              />
              <div v-if="form.errors.emergency_contact" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_contact }}</div>
            </div>
          </div>

          <!-- Family Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Status Pernikahan
            </label>
            
            <div class="flex flex-wrap items-center gap-6">
              <label class="flex items-center">
                <input type="radio" value="Menikah" v-model="form.marital_status" />
                <span class="ml-2">Menikah</span>
              </label>

              <label class="flex items-center">
                <input type="radio" value="Belum menikah" v-model="form.marital_status" />
                <span class="ml-2">Belum menikah</span>
              </label>

              <label class="flex items-center">
                <input type="radio" value="Janda/Duda" v-model="form.marital_status" />
                <span class="ml-2">Janda/Duda</span>
              </label>
            </div>

            <div
              v-if="form.errors.marital_status"
              class="text-xs text-red-500 mt-1"
            >
              {{ form.errors.marital_status }}
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Jumlah Anak</label>
            <input 
              type="text"
              v-model="form.number_of_children"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Jumlah Anak"
              
            />
            <div v-if="form.errors.number_of_children" class="text-xs text-red-500 mt-1">{{ form.errors.number_of_children }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Suami/Istri</label>
            <input 
              type="text"
              v-model="form.spouse"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nama Suami/Istri"
              
            />
            <div v-if="form.errors.spouse" class="text-xs text-red-500 mt-1">{{ form.errors.spouse }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp Suami/Istri</label>
            <input 
              type="text"
              v-model="form.wa_spouse"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor WhatsApp Suami/Istri"
              
            />
            <div v-if="form.errors.wa_spouse" class="text-xs text-red-500 mt-1">{{ form.errors.wa_spouse }}</div>
          </div>

          <!-- Identification -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor KTP Karyawan</label>
            <input 
              type="text"
              v-model="form.id_card"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor KTP Karyawan"
              
            />
            <div v-if="form.errors.id_card" class="text-xs text-red-500 mt-1">{{ form.errors.id_card }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Upload Foto KTP Karyawan</label>
            <div v-if="upload_id_card" class="mt-2 relative">
              <img :src="upload_id_card" alt="Preview KTP" class="h-32 rounded-md border border-gray-200">
              <button 
                @click="removeImage"
                type="button"
                class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div v-else class="mt-1">
              <input
                ref="fileInput"
                type="file"
                @change="handleFileChange"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              >
              <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG (Maks. 1MB)</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor Kartu Keluarga</label>
            <input 
              type="text"
              v-model="form.family_card_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor Kartu Keluarga"
              
            />
            <div v-if="form.errors.family_card_number" class="text-xs text-red-500 mt-1">{{ form.errors.family_card_number }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Upload foto kartu keluarga</label>
            <div v-if="upload_family_card" class="mt-2 relative">
              <img :src="upload_family_card" alt="Preview KTP" class="h-32 rounded-md border border-gray-200">
              <button 
                @click="removeImage"
                type="button"
                class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div v-else class="mt-1">
              <input
                ref="fileInput"
                type="file"
                name="upload_id_card"
                @change="handleFileChange2"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              >
              <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG (Maks. 1MB)</p>
            </div>
          </div>

          <!-- Financial Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor Rekening BCA (bila tidak ada = 0)</label>
            <input 
              type="text"
              v-model="form.bca_account_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor Rekening BCA"
              
            />
            <div v-if="form.errors.bca_account_number" class="text-xs text-red-500 mt-1">{{ form.errors.bca_account_number }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Sesuai Rekening</label>
            <input 
              type="text"
              v-model="form.bca_account_name"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nama Sesuai Rekening"
              
            />
            <div v-if="form.errors.bca_account_name" class="text-xs text-red-500 mt-1">{{ form.errors.bca_account_name }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor NPWP (bila tidak ada = 0)</label>
            <input 
              type="text"
              v-model="form.npwp_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor NPWP"
              
            />
            <div v-if="form.errors.npwp_number" class="text-xs text-red-500 mt-1">{{ form.errors.npwp_number }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor BPJS Kesehatan (bila tidak ada = 0)</label>
            <input 
              type="text"
              v-model="form.bpjs_health_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor BPJS Kesehatan"
              
            />
            <div v-if="form.errors.bpjs_health_number" class="text-xs text-red-500 mt-1">{{ form.errors.bpjs_health_number }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor BPJS Tenaga Kerja (bila tidak ada = 0)</label>
            <input 
              type="text"
              v-model="form.bpjs_employment_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nomor BPJS Tenaga Kerja"
              
            />
            <div v-if="form.errors.bpjs_employment_number" class="text-xs text-red-500 mt-1">{{ form.errors.bpjs_employment_number }}</div>
          </div>

          <!-- Education Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Pendidikan Terakhir <span class="text-red-500">*</span></label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" value="SMA/SMK/sederajat" v-model="form.last_education" />
                <span class="ml-2">SMA/SMK/sederajat</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Diploma 1" v-model="form.last_education" />
                <span class="ml-2">Diploma 1</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Diploma 2" v-model="form.last_education" />
                <span class="ml-2">Diploma 2</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Diploma 3" v-model="form.last_education" />
                <span class="ml-2">Diploma 3</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="S1" v-model="form.last_education" />
                <span class="ml-2">S1</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="S2/S3" v-model="form.last_education" />
                <span class="ml-2">S2/S3</span>
              </label>
            </div>
            <div v-if="form.errors.last_education" class="text-xs text-red-500 mt-1">{{ form.errors.last_education }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Sekolah/Kampus</label>
            <input 
              type="text"
              v-model="form.name_school_college"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Nama Sekolah/Kampus"
              
            />
            <div v-if="form.errors.name_school_college" class="text-xs text-red-500 mt-1">{{ form.errors.name_school_college }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Jurusan Sekolah/Kampus (Terakhir)</label>
            <input 
              type="text"
              v-model="form.school_college_major"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Jurusan Sekolah/Kampus"
              
            />
            <div v-if="form.errors.school_college_major" class="text-xs text-red-500 mt-1">{{ form.errors.school_college_major }}</div>
          </div>

          <!-- Employment Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai Bekerja</label>
            <input 
              type="date"
              v-model="form.work_start_date" 
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
              
            />
            <div v-if="form.errors.work_start_date" class="text-xs text-red-500 mt-1">{{ form.errors.work_start_date }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Jabatan</label>
            <input 
              type="text"
              v-model="form.position"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan Jabatan"
              
            />
            <div v-if="form.errors.position" class="text-xs text-red-500 mt-1">{{ form.errors.position }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Upload Photo Berwarna Terbaru*</label>
            <div v-if="upload_latest_color_photo" class="mt-2 relative">
              <img :src="upload_latest_color_photo" alt="Preview KTP" class="h-32 rounded-md border border-gray-200">
              <button 
                @click="removeImage"
                type="button"
                class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
            <div v-else class="mt-1">
              <input
                ref="fileInput"
                type="file"
                @change="handleFileChange3"
                accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              >
              <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG (Maks. 1MB)</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Tipe</label>
            <select v-model="form.type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" >
              <option value="branch">Branch</option>
              <option value="customer">Customer</option>
            </select>
            <div v-if="form.errors.type" class="text-xs text-red-500 mt-1">{{ form.errors.type }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Region</label>
            <input v-model="form.region" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="255" />
            <div v-if="form.errors.region" class="text-xs text-red-500 mt-1">{{ form.errors.region }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select v-model="form.status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
              <option value="A">Active</option>
              <option value="B">Inactive</option>
            </select>
            <div v-if="form.errors.status" class="text-xs text-red-500 mt-1">{{ form.errors.status }}</div>
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-2">
            <button type="button" @click="goBack" class="px-4 py-2 rounded bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300">Batal</button>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-50">{{ isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
          </div>
        </form>
    </div>
  </AppLayout>
</template> 