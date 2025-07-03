<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="text-2xl font-bold text-gray-800 mb-2">{{ value }}</div>
    <div class="text-gray-600">{{ title }}</div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  value: String
});
</script>