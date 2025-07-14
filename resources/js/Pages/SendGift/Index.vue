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

const fileSizeLimit = 1024 * 1024 // 1MB
const originalFile = ref(null)
const compressedFile = ref(null)
const previewUrl = ref('')
const error = ref('')

const form = useForm({
  // Your form fields here
  type: 'all',
  target: '',
  is_promo: '0',
  title: '',
  body: '',
  gambar: null,
  berlaku_hingga: '',
});

function handleImageUpload(event) {
  const file = event.target.files[0]

  if (!file || !file.type.startsWith('image/')) return

  if (file.size <= fileSizeLimit) {
    originalFile.value = file
    compressedFile.value = file
    previewUrl.value = URL.createObjectURL(file)
    error.value = ''
    return
  }

  // Compress with canvas
  const reader = new FileReader()
  reader.onload = () => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const MAX_WIDTH = 1000
      const scale = Math.min(1, MAX_WIDTH / img.width)
      canvas.width = img.width * scale
      canvas.height = img.height * scale
      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height)

      // JPEG compress
      canvas.toBlob(blob => {
        if (blob.size > fileSizeLimit) {
          error.value = `Ukuran setelah kompres masih terlalu besar (${(blob.size / 1024 / 1024).toFixed(2)} MB)`
          compressedFile.value = null
          previewUrl.value = ''
        } else {
          compressedFile.value = new File([blob], file.name, { type: 'image/jpeg' })
          previewUrl.value = URL.createObjectURL(blob)
          error.value = ''
        }
      }, 'image/jpeg', 0.75) // adjust quality (0.7–0.8 is good balance)
    }
    img.src = reader.result
  }
  reader.readAsDataURL(file)
}

watch(() => form.type, (newType) => {
  if (newType === 'target') {
    form.target = '';
  } else {
    form.target = 'all';
  }
});

watch(() => form.is_promo, (newValue) => {
  if (newValue === '1') {
    form.is_promo = '1';
  } else {
    form.is_promo = '0';
    form.berlaku_hingga = ''; // Reset berlaku_hingga if promo is not selected
  }
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
  // if (form.type === 'all') {
  //   form.target = 'all';
  // }

  form.gambar = compressedFile.value;
  
  form.post(route('sendgiftproses.proses'), {
    forceFormData: true,
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

</script>

<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-700">Send Gift Member</h2>
        <Link
          :href="route('sendgift.index')"
          class="bg-violet-500 hover:bg-violet-600 text-white text-sm px-4 py-2 rounded shadow"
        >
          History Send Gift
        </Link>
      </div>

      <form @submit.prevent="onSubmit" class="space-y-5" enctype="multipart/form-data">
        <div>
          <label class="block text-sm font-medium text-gray-600">Pilih Tipe</label>
          <select v-model="form.type" class="input">
            <option value="all">All</option>
            <option value="target">Target</option>
          </select>
        </div>

        <div v-if="form.type === 'target'">
          <label class="block text-sm font-medium text-gray-600">Target</label>
          <input
            v-model="form.target"
            type="text"
            class="input"
            placeholder="List Email"
          />
          <p class="text-xs text-gray-400">masukan email, pisahkan dengan tanda koma ( , )</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600">Program Promo</label>
          <div class="flex items-center gap-6 mt-2">
            <label class="flex items-center space-x-2">
              <input v-model="form.is_promo" type="radio" value="1" />
              <span>Ya</span>
            </label>
            <label class="flex items-center space-x-2">
              <input v-model="form.is_promo" type="radio" value="0" />
              <span>Tidak</span>
            </label>
          </div>
        </div>

        <div v-if="form.is_promo === '1'">
          <label class="block text-sm font-medium text-gray-600">Berlaku Hingga</label>
          <input
            v-model="form.berlaku_hingga"
            type="date"
            class="input"
            placeholder="Tanggal Berlaku"
          />
          <p class="text-xs text-gray-400">masukan email, pisahkan dengan tanda koma ( , )</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600">Title</label>
          <input
            v-model="form.title"
            type="text"
            class="input"
            placeholder="Title"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-600">Body</label>
          <textarea
            v-model="form.body"
            class="input h-28"
            placeholder="Body"
          ></textarea>
        </div>

        <div class="pl-4">
          <label class="block mb-2 font-semibold text-gray-700">Upload Gambar</label>
          <input type="file" accept="image/*" @change="handleImageUpload" />

          <div v-if="previewUrl" class="mt-4">
            <img :src="previewUrl" class="w-60 rounded shadow" />
          </div>

          <p v-if="error" class="text-sm text-red-500 mt-2">{{ error }}</p>
          <p v-else-if="compressedFile" class="text-xs text-gray-500 mt-2">
            Ukuran setelah kompres: {{ (compressedFile.size / 1024).toFixed(1) }} kb
          </p>
        </div>

        <button
          type="submit"
          class="bg-violet-500 hover:bg-violet-600 text-white text-sm px-6 py-2 rounded shadow"
        >
          Submit
        </button>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.bg-3d {
  box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15), 0 1.5px 4px 0 rgba(31, 38, 135, 0.08);
}
.input {
  @apply mt-1 block w-full rounded-md border border-gray-300 shadow-sm text-sm focus:ring-violet-500 focus:border-violet-500;
}
</style> 