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
const points = ref([]);

const form = useForm({

});

watch(() => props.show, (val) => {
  // console.log(props.customer);
  // axios.post(route('member.view_detail'), {
  //   data: props.customer,
  // }).then(response => {
  //   console.log(response.data);

  //   points.value = response.data;
  //   // Jika ada data yang ingin diisi pada form saat edit
  //   data_points = response.data;
  //   if (props.mode === 'edit' && props.customer) {
  //     Object.assign(form, response.data.customer);
  //   } else {
  //     form.reset();
  //   }
  // });
});

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
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl mx-auto max-h-[90vh] overflow-y-auto p-0 animate-fade-in">
      <div class="bg-white rounded-2xl shadow-2xl overflow-x-auto transition-all p-6">
        <table class="w-full min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">TRANS DATE</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">OUTLET NAME</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">TOTAL TRANS</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">POINT</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">POINT TYPE</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="customer.length === 0">
              <td colspan="6" class="text-center py-10 text-gray-400">Tidak ada data member.</td>
            </tr>
            <tr v-for="customers in customer" :key="customer.id" class="hover:bg-blue-50 transition shadow-sm">
              <td class="px-6 py-3 font-semibold">{{ customers.created_at }}</td>
              <td class="px-6 py-3">{{ customers.nama_outlet }}</td>
              <td class="px-6 py-3">{{ customers.jml_trans }}</td>
              <td class="px-6 py-3">{{ customers.point }}</td>
              <td class="px-6 py-3">{{ customers.type == 1 ? 'Top Up' : customers.type == 2 ? 'Reedem' : '-' }}</td>
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