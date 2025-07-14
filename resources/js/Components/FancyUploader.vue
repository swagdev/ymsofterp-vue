<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  label: String,
  name: String,
  modelValue: File,
  note: String,
  accept: {
    type: String,
    default: 'image/*',
  }
})

const emit = defineEmits(['update:modelValue'])
const previewUrl = ref(null)

watch(() => props.modelValue, (val) => {
  if (val instanceof File) {
    const fileType = val.type;

    if (fileType.startsWith('image/')) {
      previewUrl.value = URL.createObjectURL(val); // preview gambar
    } else if (fileType === 'application/pdf') {
      previewUrl.value = null;
    } else {
      previewUrl.value = null;
    }

  } else if (typeof val === 'string') {
    // Jika val adalah string path dari server
    if (val.endsWith('.pdf')) {
      previewUrl.value = null;
    } else {
      previewUrl.value = val.startsWith('/')
        ? val
        : val;
    }

  } else {
    previewUrl.value = null;
  }
}, { immediate: true });

</script>

<template>
  <div class="mb-6">
    <label class="block font-semibold text-gray-800 mb-2">{{ label }}</label>
    <div
      class="relative border-2 border-dashed border-gray-300 rounded-xl p-4 flex justify-between items-center hover:border-blue-400 transition group"
    >
      <input
        type="file"
        :name="name"
        :accept="accept"
        @change="e => emit('update:modelValue', e.target.files[0])"
        class="absolute inset-0 opacity-0 cursor-pointer z-10"
      />

      <div class="text-sm text-gray-600">
        <span class="text-blue-600 font-medium">Klik atau tarik file</span>
        <p class="text-xs text-gray-400 italic">{{ note }}</p>
      </div>

      <div v-if="previewUrl != null" class="w-16 h-16 overflow-hidden rounded border">
        <img :src="previewUrl" class="w-full h-full object-cover" />
      </div>
    </div>
  </div>
</template>
