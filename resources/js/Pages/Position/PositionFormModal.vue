<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { sub } from 'date-fns';
import id from '@/lang/id';

const props = defineProps({
  show: Boolean,
  mode: String, // 'create' | 'edit'
  ds: Object, // untuk edit
  jabatans: Object, // daftar jabatan untuk form
  divisis: Object,
  subDivisis: Object, // daftar sub divisi untuk form
  levels: Object, // daftar level untuk form
});
const emit = defineEmits(['close', 'success']);

const form = useForm({
  nama_jabatan: '',
  id_atasan: '',
  id_divisi: '',
  id_sub_divisi: '',
  id_level: '',
  standard_qa: '',
});

watch(() => props.show, (val) => {
  if (val && props.mode === 'edit' && props.ds) {
    form.nama_jabatan = props.ds.nama_jabatan || '';
    form.id_atasan = props.ds.id_atasan || '';
    form.id_divisi = props.ds.id_divisi || '';
    form.id_sub_divisi = props.ds.id_sub_divisi || '';
    form.id_level = props.ds.id_level || '';
    form.standard_qa = props.ds.standard_qa || '';
  } else if (val && props.mode === 'create') {
    form.nama_jabatan = '';
    form.id_atasan = '';
    form.id_divisi  = '';
    form.id_sub_divisi = '';
    form.id_level = '';
    form.standard_qa = '';
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
    form.post(route('position.store'), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Jabatan berhasil ditambahkan!', 'success');
        emit('success');
        emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  } else if (props.mode === 'edit' && props.ds) {
    form._method = 'POST';
    form.post(route('position.update', props.ds.id_jabatan), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Jabatan berhasil diupdate!', 'success');
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
          <h3 class="text-2xl font-bold text-gray-900">{{ mode === 'edit' ? 'Edit' : 'Tambah' }} Jabatan</h3>
        </div>
        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
          <!-- Basic Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Jabatan</label>
            <input v-model="form.nama_jabatan" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="50" />
            <div v-if="form.errors.nama_jabatan" class="text-xs text-red-500 mt-1">{{ form.errors.nama_jabatan }}</div>
          </div>

          <!-- Atasan -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700">Atasan*</label>
            <select
              v-model="form.id_atasan"
              @change="emitAtasan"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option disabled value="">-- Pilih Atasan --</option>
              <option
                v-for="jabatan in jabatans"
                :key="jabatan.id_jabatan"
                :value="jabatan.id_jabatan"
              >
                {{ jabatan.nama_jabatan }}
              </option>
            </select>
            <div v-if="form.errors.id_atasan" class="text-xs text-red-500 mt-1">
              {{ form.errors.id_atasan }}
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

          <!-- Sub Divisi -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700">Sub Divisi*</label>
            <select
              v-model="form.id_sub_divisi"
              @change="emitSubDivisi"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option disabled value="">-- Pilih Sub Divisi --</option>
              <option
                v-for="subDivisi in subDivisis"
                :key="subDivisi.id"
                :value="subDivisi.id"
              >
                {{ subDivisi.nama_sub_divisi }}
              </option>
            </select>
            <div v-if="form.errors.id_sub_divisi" class="text-xs text-red-500 mt-1">
              {{ form.errors.id_sub_divisi }}
            </div>
          </div>

          <!-- Level -->
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700">Level*</label>
            <select
              v-model="form.id_level"
              @change="emitLevel"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option disabled value="">-- Pilih Level --</option>
              <option
                v-for="level in levels"
                :key="level.id"
                :value="level.id"
              >
                {{ level.nama_level }}
              </option>
            </select>
            <div v-if="form.errors.id_level" class="text-xs text-red-500 mt-1">
              {{ form.errors.id_level }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Standard QA</label>
            <input v-model="form.standard_qa" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="50" />
            <div v-if="form.errors.standard_qa" class="text-xs text-red-500 mt-1">{{ form.errors.standard_qa }}</div>
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