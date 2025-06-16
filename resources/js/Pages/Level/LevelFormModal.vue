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
});
const emit = defineEmits(['close', 'success']);

const form = useForm({
  nama_level: '',
  nilai_level: '',
  nilai_public_holiday: '',
  nilai_dasar_potongan_bpjs: '',
  nilai_point: '',
  qa_reward: '',
  qa_penalty: '',
});

watch(() => props.show, (val) => {
console.log(props.ds);
  if (val && props.mode === 'edit' && props.ds) {
    form.nama_level = props.ds.nama_level || '';
    form.nilai_level = props.ds.nilai_level || '';
    form.nilai_public_holiday = props.ds.nilai_public_holiday || '';
    form.nilai_dasar_potongan_bpjs = props.ds.nilai_dasar_potongan_bpjs || '';
    form.nilai_point = props.ds.nilai_point || '';
    form.qa_reward = props.ds.qa_reward || '';
    form.qa_penalty = props.ds.qa_penalty || '';
  } else if (val && props.mode === 'create') {
    form.nama_level = '';
    form.nilai_level = '';
    form.nilai_public_holiday = '';
    form.nilai_dasar_potongan_bpjs = '';
    form.nilai_point = '';
    form.qa_reward = '';
    form.qa_penalty = '';
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
    form.post(route('level.store'), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Level berhasil ditambahkan!', 'success');
        emit('success');
        emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  } else if (props.mode === 'edit' && props.ds) {
    form._method = 'POST';
    form.post(route('level.update', props.ds.id), {
      onSuccess: () => {
        Swal.fire('Berhasil', 'Level berhasil diupdate!', 'success');
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
          <h3 class="text-2xl font-bold text-gray-900">{{ mode === 'edit' ? 'Edit' : 'Tambah' }} Level</h3>
        </div>
        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
          <!-- Basic Info -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Nama Level</label>
            <input v-model="form.nama_level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500"  maxlength="50" />
            <div v-if="form.errors.nama_level" class="text-xs text-red-500 mt-1">{{ form.errors.nama_level }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nilai Level</label>
            <input v-model="form.nilai_level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.nilai_level" class="text-xs text-red-500 mt-1">{{ form.errors.nilai_level }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nilai Public Holiday</label>
            <input v-model="form.nilai_public_holiday" type="number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.nilai_public_holiday" class="text-xs text-red-500 mt-1">{{ form.errors.nilai_public_holiday }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nilai Dasar Potongan BPJS</label>
            <input v-model="form.nilai_dasar_potongan_bpjs" type="number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.nilai_dasar_potongan_bpjs" class="text-xs text-red-500 mt-1">{{ form.errors.nilai_dasar_potongan_bpjs }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Nilai Point</label>
            <input v-model="form.nilai_point" type="number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.nilai_point" class="text-xs text-red-500 mt-1">{{ form.errors.nilai_point }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">QA Reward</label>
            <input v-model="form.qa_reward" type="number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.qa_reward" class="text-xs text-red-500 mt-1">{{ form.errors.qa_reward }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">QA Penalty</label>
            <input v-model="form.qa_penalty" type="number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            <div v-if="form.errors.qa_penalty" class="text-xs text-red-500 mt-1">{{ form.errors.qa_penalty }}</div>
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