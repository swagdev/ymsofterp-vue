<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { sub } from 'date-fns';
import id from '@/lang/id';
import FancyUploader from '@/Components/FancyUploader.vue';
import Editor from '@tinymce/tinymce-vue';

// Core TinyMCE
import 'tinymce/tinymce'

// Theme dan ikon
import 'tinymce/themes/silver/theme'
import 'tinymce/icons/default/icons'

// Model DOM (penting untuk Vue)
import 'tinymce/models/dom/model'

// Skin UI (agar style-nya muncul)
import 'tinymce/skins/ui/oxide/skin.min.css'

// Plugin (sesuaikan yang kamu mau pakai)
import 'tinymce/plugins/link'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/code'
import 'tinymce/plugins/table'
import 'tinymce/plugins/advlist'

const props = defineProps({
  show: Boolean,
  mode: String, // 'create' | 'edit'
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
  title: '',
  thumbnail: null,     // <-- File
  image: null,     // <-- File
  content: '',
});

watch(() => props.show, (val) => {
  if (val && props.mode === 'edit' && props.ds) {
    form.title = props.ds.title || '';
    form.thumbnail = props.ds.thumbnail || null;
    form.image = props.ds.image || null;
    formDeskripsi.value = props.ds.content || '';
  } else if (val && props.mode === 'create') {
    form.title = '';
    form.thumbnail = null;
    form.image = null;
    formDeskripsi.value = '';
  }
});

const isSubmitting = ref(false);
const isSubClose = ref(false);

async function submit() {
  isSubmitting.value = true;

  const fd = new FormData()
  fd.append('title', form.title)
  fd.append('thumbnail', form.thumbnail)
  fd.append('image', form.image)
  form.content = formDeskripsi.value

  if (props.mode === 'create') {
    form.post(route('newswebprofile.store'), {
      forceFormData: true,
      onSuccess: () => {
        Swal.fire('Berhasil', 'Data Brands berhasil ditambahkan!', 'success');
        emit('success');
        emit('close');
      },
      onError: () => isSubmitting.value = false,
      onFinish: () => isSubmitting.value = false,
    });
  } else if (props.mode === 'edit' && props.ds) {
    form._method = 'POST';
    form.post(route('newswebprofile.update', props.ds.id), {
      forceFormData: true,
      onSuccess: () => {
        Swal.fire('Berhasil', 'Brands berhasil diupdate!', 'success');
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
            {{ mode === 'edit' ? 'Edit' : 'Tambah' }} News Web Profile
          </h3>
        </div>
        <form @submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
          <div class="space-y-6">
            <!-- Judul -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
              <input v-model="form.title" type="text" class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200" placeholder="Masukkan judul...">
              <p v-if="errors?.title" class="text-sm text-red-500 mt-1">
                {{ errors.title }}
              </p>
            </div>

            <FancyUploader
              label="Gambar Thumbnail"
              name="thumbnail"
              v-model="form.thumbnail"
              note="Ukuran disarankan: max 2 MB"
            />

            <FancyUploader
              label="Gambar"
              name="image"
              v-model="form.image"
              note="Ukuran disarankan max 2 MB"
            />

            <!-- Editor Deskripsi -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <Editor
                  v-model="formDeskripsi"
                  :init="{
                    height: 300,
                    menubar: false,
                    skin: false,
                    content_css: false,
                    plugins: 'lists link code table advlist',
                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link code',
                    license_key: 'gpl'
                  }"
                />
                <!-- <p v-if="props.errors?.content" class="text-sm text-red-500 mt-1">{{ errors.content }}</p> -->
            </div>
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