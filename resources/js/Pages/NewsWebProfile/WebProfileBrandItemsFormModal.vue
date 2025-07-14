<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { sub } from 'date-fns';
import id from '@/lang/id';
import FancyUploader from '@/Components/FancyUploader.vue';

const props = defineProps({
  show: Boolean,
  mode: String, // 'create' | 'edit'
  id: Object,
  ds: Object, // untuk edit
  errors: Object,
  jabatans: Object, // daftar jabatan untuk form
  divisis: Object,
  subDivisis: Object, // daftar sub divisi untuk form
  levels: Object, // daftar level untuk form
});
const emit = defineEmits(['close', 'success']);

const formDeskripsi = ref('')

const form = useForm({
  name: '',
  menu_pdf: null,  // <-- File
});

watch(() => props.show, (val) => {
  if (val && props.mode === 'edit' && props.ds) {
    form.name = props.ds.name || '';
    form.menu_pdf = props.ds.menu_pdf || null;
  } else if (val && props.mode === 'create') {
    form.name = '';
    form.menu_pdf = null;
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
const isSubClose = ref(false);

async function submit() {
  isSubmitting.value = true;

  const fd = new FormData()
  fd.append('name', form.name)
  fd.append('menu_pdf', form.menu_pdf)   // File

  if (props.mode === 'create') {
    form.post(route('webprofilebrandslistmenu.store', props.id), {
      onSuccess: () => {
        // Swal.fire('Berhasil', 'Data Brands berhasil ditambahkan!', 'success');
        // emit('success');
        // emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  } else if (props.mode === 'edit' && props.ds) {
    form._method = 'POST';
    form.post(route('webprofilebrandslistmenu.update', props.ds.id), {
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
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 transition-all px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto animate-fade-in">
      <div class="px-8 pt-8 pb-2">
        <div class="flex items-center gap-2 mb-6">
          <svg class="w-7 h-7 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <h3 class="text-2xl font-bold text-gray-900">
            {{ mode === 'edit' ? 'Edit' : 'Tambah' }} Brand Lists
          </h3>
        </div>
        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
          <div class="space-y-6">
            <!-- Judul -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
              <input v-model="form.name" type="text" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200" placeholder="Masukkan judul...">
              <p v-if="errors?.name" class="text-sm text-red-500 mt-1">
                {{ errors.name }}
              </p>
            </div>

            <FancyUploader
              label="Menu PDF"
              name="menu_pdf"
              v-model="form.menu_pdf"
              note="File PDF max 2 MB"
              accept=".pdf"
            />

            <div class="flex justify-end gap-2 pt-4">
              <button type="button" :disabled="isSubClose" @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">Batal</button>
              <button type="submit" :disabled="isSubmitting" class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:opacity-60">
                {{ mode === 'edit' ? 'Update' : 'Simpan' }}
              </button>
            </div>
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