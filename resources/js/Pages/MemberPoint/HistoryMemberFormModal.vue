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
  no_bill: '',
  jml_trans: '',
  point: '',
});

watch(() => props.show, (val) => {
  if (val && props.mode === 'edit' && props.ds) {
    form.no_bill = props.ds.points.no_bill || '';
    form.jml_trans = props.ds.points.jml_trans || '';
    form.no_bill = props.ds.points.point || '';
  } else if (val && props.mode === 'create') {
    form.no_bill = '';
    form.jml_trans = '';
    form.point  = '';
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

function formatCurrency(value) {
  return Number(value || 0).toLocaleString('id-ID')
}

function formatDate(value) {
  if (!value) return '-';
  return new Date(value).toISOString().split('T')[0]; // hasil: YYYY-MM-DD
}

function closeModal() {
  emit('close');
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-auto max-h-[90vh] overflow-y-auto p-0 animate-fade-in">
      <div class="bg-white rounded-2xl shadow-2xl overflow-x-auto transition-all p-6">
        <div class="flex justify-end gap-2 pt-4 px-6 pb-6">
          <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">Close</button>
        </div>
        <table class="w-full min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">No</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">No Bill</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Jml Trans</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Point</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="ds.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Tidak ada data member.</td>
            </tr>
            <tr v-for="(d, key) in ds.points" :key="ds.id" class="hover:bg-blue-50 transition shadow-sm">
              <td class="px-6 py-3 font-semibold">{{ key+1 }}</td>
              <td class="px-6 py-3">{{ d.type == 1 ? d.no_bill : d.type && (d.cabang_id != 0) == 2 ? d.no_bill_2 : d.cabang_id == 0 ? '-' : '-' }}</td>
              <td class="px-6 py-3">Rp. {{ formatCurrency(d.jml_trans) }}</td>
              <td class="px-6 py-3">{{ formatCurrency(d.point) }}</td>
              <td class="px-6 py-3">{{ formatDate(d.updated_at) }}</td>
              <td class="px-6 py-3">{{ d.type == 1 ? 'Point Masuk' : d.type && (d.cabang_id != 0) ? 'Point Keluar' : d.cabang_id == 0 ? 'Reset Point' : '-' }}</td>
            </tr>
          </tbody>
        </table>
        <div class="flex justify-end gap-2 pt-4 px-6 pb-6">
          <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200">Close</button>
        </div>
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