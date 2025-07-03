<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
  show: Boolean,
  mode: String, // 'create' | 'edit'
  customer: Object, // untuk edit
});
const emit = defineEmits(['close', 'success']);

const form = useForm({
  nik: '',
  name: '',
  type: 'branch',
  region: '',
  status: 'active',

  gender: '',
  place_birth: '',
  date_birth: '',
  domicile: '',
  domicile_ktp: '',
  religion: '',
  blood_type: '',

  wa_number: '',
  email: '',
  name_emergency_contact: '',
  emergency_contact: '',
  emergency_contact_relationship: '',

  marital_status: '',
  number_of_children: '',
  spouse: '',
  wa_spouse: '',

  id_card: '',
  family_card_number: '',
  upload_id_card: null,
  upload_family_card: null,

  bca_account_number: '',
  bca_account_name: '',
  npwp_number: '',
  bpjs_health_number: '',
  bpjs_employment_number: '',

  last_education: '',
  name_school_college: '',
  school_college_major: '',

  work_start_date: '',
  position: '',
  upload_latest_color_photo: null,
});

watch(() => props.show, (val) => {
  if (val && props.mode === 'edit' && props.customer) {
    form.nik = props.customer.nik || '';
    form.name = props.customer.nama_lengkap || '';
    form.type = props.customer.type || 'branch';
    form.region = props.customer.region || '';
    form.status = props.customer.status || 'active';

    form.gender = props.customer.jenis_kelamin || '';
    form.place_birth = props.customer.tempat_lahir || '';
    form.date_birth = props.customer.tanggal_lahir || ''; // Perbaikan nama field
    form.domicile = props.customer.alamat || '';
    form.domicile_ktp = props.customer.alamat_ktp || '';
    form.religion = props.customer.agama || '';
    form.blood_type = props.customer.golongan_darah || ''; // Nama field diubah ke format Inggris
    form.wa_number = props.customer.no_hp || ''; // Nama field diubah
    form.email = props.customer.email || '';

    // Kontak Darurat
    form.name_emergency_contact = props.customer.nama_kontak_darurat || '';
    form.emergency_contact = props.customer.no_hp_kontak_darurat || ''; // Field baru
    form.emergency_contact_relationship = props.customer.hubungan_kontak_darurat || '';

    // Keluarga
    form.marital_status = props.customer.status_pernikahan || '';
    form.number_of_children = props.customer.jumlah_anak || '';
    form.spouse = props.customer.nama_pasangan || '';
    form.wa_spouse = props.customer.wa_pasangan || '';

    // Identitas
    form.id_card = props.customer.no_ktp || '';
    form.upload_id_card = props.customer.foto_ktp || null;
    form.family_card_number = props.customer.nomor_kk || '';
    form.upload_family_card = props.customer.foto_kk || null;

    // Keuangan
    form.bca_account_number = props.customer.no_rekening || '';
    form.bca_account_name = props.customer.nama_rekening || '';
    form.npwp_number = props.customer.npwp_number || '';
    form.bpjs_health_number = props.customer.bpjs_health_number || '';
    form.bpjs_employment_number = props.customer.bpjs_employment_number || '';

    // Pendidikan
    form.last_education = props.customer.last_education || '';
    form.name_school_college = props.customer.name_school_college || '';
    form.school_college_major = props.customer.school_college_major || '';

    // Pekerjaan
    form.work_start_date = props.customer.work_start_date || '';
    form.position = props.customer.position || '';
    form.upload_latest_color_photo = props.customer.upload_latest_color_photo || null;
  } else if (val && props.mode === 'create') {
    form.nik  = '';
    form.name  = '';
    form.type  = 'branch';
    form.region  = '';
    form.status  = 'active';

    form.gender  = '';
    form.place_birth  = '';
    form.date_birth  = '';
    form.domicile  = '';
    form.domicile_ktp  = '';
    form.religion  = '';
    form.blood_type  = '';

    form.wa_number  = '';
    form.email  = '';
    form.name_emergency_contact  = '';
    form.emergency_contact  = '';
    form.emergency_contact_relationship  = '';

    form.marital_status  = '';
    form.number_of_children  = '';
    form.spouse  = '';
    form.wa_spouse  = '';

    form.id_card  = '';
    form.family_card_number  = '';
    form.upload_id_card  = null;
    form.upload_family_card  = null;

    form.bca_account_number  = '';
    form.bca_account_name  = '';
    form.npwp_number  = '';
    form.bpjs_health_number  = '';
    form.bpjs_employment_number  = '';

    form.last_education  = '';
    form.name_school_college  = '';
    form.school_college_major  = '';

    form.work_start_date  = '';
    form.position  = '';
    form.upload_latest_color_photo  = null;
  }
});

function handleFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    // Menentukan field mana yang akan diisi sesuai dengan input
    if (event.target.name === 'upload_id_card') {
      form.upload_id_card = file;
    } else if (event.target.name === 'upload_family_card') {
      form.upload_family_card = file;
    } else if (event.target.name === 'upload_latest_color_photo') {
      form.upload_latest_color_photo = file;
    }
  }
}

const isSubmitting = ref(false);

async function submit() {
  isSubmitting.value = true;
  if (props.mode === 'create') {
    form.post(route('employee.store'), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Customer berhasil ditambahkan!', 'success');
        emit('success');
        emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  } else if (props.mode === 'edit' && props.customer) {
    form._method = 'PUT';
    form.post(route('employee.update', props.customer.id), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Customer berhasil diupdate!', 'success');
        emit('success');
        emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  }
}

function closeModal() {
  emit('close');
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto max-h-[90vh] overflow-y-auto p-0 animate-fade-in">
      <div class="px-8 pt-8 pb-2">
        <div class="flex items-center gap-2 mb-6">
          <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h3 class="text-2xl font-bold text-gray-900">{{ mode === 'edit' ? 'Edit' : 'Tambah' }} Karyawan</h3>
        </div>
        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
          <!-- Basic Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nik</label>
            <input v-model="form.nik" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="50" />
            <div v-if="form.errors.nik" class="text-xs text-red-500 mt-1">{{ form.errors.nik }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Lengkap Sesuai KTP</label>
            <input v-model="form.name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="100" />
            <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
            <select v-model="form.gender" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" >
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
            <div v-if="form.errors.gender" class="text-xs text-red-500 mt-1">{{ form.errors.gender }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
            <input v-model="form.place_birth" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="255" />
            <div v-if="form.errors.place_birth" class="text-xs text-red-500 mt-1">{{ form.errors.place_birth }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
            <input 
              type="date" 
              v-model="form.date_birth" 
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" 
              
            />
            <div v-if="form.errors.date_birth" class="text-xs text-red-500 mt-1">{{ form.errors.date_birth }}</div>
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
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" value="Islam" v-model="form.religion" />
                <span class="ml-2">Islam</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Kristen" v-model="form.religion" />
                <span class="ml-2">Kristen</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Katolik" v-model="form.religion" />
                <span class="ml-2">Katolik</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Hindu" v-model="form.religion" />
                <span class="ml-2">Hindu</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Budha" v-model="form.religion" />
                <span class="ml-2">Budha</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Lainnya" v-model="form.religion" />
                <span class="ml-2">Agama Lainnya:</span>
                <input
                  v-if="form.religion === 'Lainnya'"
                  v-model="form.religion"
                  type="text"
                  class="ml-2 border rounded px-2 py-1 w-1/2"
                  placeholder="Contoh: Islam"
                />
              </label>
            </div>
            <div v-if="form.errors.religion" class="text-xs text-red-500 mt-1">{{ form.errors.religion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Golongan Darah</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" value="A" v-model="form.blood_type" />
                <span class="ml-2">A</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="B" v-model="form.blood_type" />
                <span class="ml-2">B</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="O" v-model="form.blood_type" />
                <span class="ml-2">O</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="AB" v-model="form.blood_type" />
                <span class="ml-2">AB</span>
              </label>
            </div>
            <div v-if="form.errors.blood_type" class="text-xs text-red-500 mt-1">{{ form.errors.blood_type }}</div>
          </div>
          
          <!-- Contact Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
            <input 
              type="text"
              v-model="form.wa_number"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="08xxxxxxxxxx"
              
            />
            <div v-if="form.errors.wa_number" class="text-xs text-red-500 mt-1">{{ form.errors.wa_number }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Alamat Email</label>
            <input 
              type="email"
              v-model="form.email"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="contoh@email.com"
              
            />
            <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Kontak Darurat</label>
            <input 
              type="text"
              v-model="form.name_emergency_contact"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan nama kontak darurat"
              
            />
            <div v-if="form.errors.name_emergency_contact" class="text-xs text-red-500 mt-1">{{ form.errors.name_emergency_contact }}</div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Hubungan dengan kontak darurat</label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input type="radio" value="Suami/Istri" v-model="form.emergency_contact_relationship" />
                <span class="ml-2">Suami/Istri</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Orang Tua" v-model="form.emergency_contact_relationship" />
                <span class="ml-2">Orang Tua</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Kakak/Adik" v-model="form.emergency_contact_relationship" />
                <span class="ml-2">Kakak/Adik</span>
              </label>
              <label class="flex items-center">
                <input type="radio" value="Lainnya" v-model="form.emergency_contact_relationship" />
                <span class="ml-2">Keluarga Lainnya:</span>
                <input
                  v-if="form.emergency_contact_relationship === 'Lainnya'"
                  v-model="form.keterangan_lainnya"
                  type="text"
                  class="ml-2 border rounded px-2 py-1 w-1/2"
                  placeholder="Contoh: Sepupu"
                />
              </label>
            </div>
            <div v-if="form.errors.emergency_contact_relationship" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_contact_relationship }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor telepon kontak darurat *</label>
            <input 
              type="text"
              v-model="form.emergency_contact"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Masukkan nama kontak darurat"
              
            />
            <div v-if="form.errors.emergency_contact" class="text-xs text-red-500 mt-1">{{ form.errors.emergency_contact }}</div>
          </div>

          <!-- Family Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Status Pernikahan</label>
            <div class="space-y-2">
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
            <div v-if="form.errors.marital_status" class="text-xs text-red-500 mt-1">{{ form.errors.marital_status }}</div>
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
                @change="handleFileChange"
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
                @change="handleFileChange"
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
          <div class="flex justify-end gap-2 pt-4">
            <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">Batal</button>
            <button type="submit" :disabled="isSubmitting" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-60">
              {{ mode === 'edit' ? 'Update' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(20px);}
  to { opacity: 1; transform: translateY(0);}
}
.animate-fade-in {
  animation: fade-in 0.3s cubic-bezier(.4,0,.2,1);
}

.upload-container {
  max-width: 400px;
  margin: 20px auto;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 8px;
  text-align: center;
}

.info-text {
  font-size: 14px;
  color: #666;
  margin: 10px 0;
}

.file-input {
  margin: 15px 0;
  display: block;
  width: 100%;
}

.upload-button {
  background: #4CAF50;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
}

.upload-button:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

.error-message {
  color: red;
  font-size: 14px;
  margin-top: 10px;
}
</style> 