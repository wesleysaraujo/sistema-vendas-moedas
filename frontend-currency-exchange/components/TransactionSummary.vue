<template>
  <div class="bg-gray-50 p-4 rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ title }}</h3>
    
    <div class="space-y-3">
      <div v-for="(item, index) in items" :key="index" class="flex justify-between">
        <span class="text-gray-600">{{ item.label }}:</span>
        <span class="font-medium">{{ item.value }}</span>
      </div>
      
      <div v-if="showDivider" class="border-t border-gray-200 pt-2 mt-2">
        <div v-for="(total, index) in totals" :key="`total-${index}`" class="flex justify-between mt-1">
          <span class="text-gray-700 font-semibold">{{ total.label }}:</span>
          <span class="font-bold">{{ total.value }}</span>
        </div>
      </div>
    </div>
    
    <div v-if="successMessage" class="mt-4 p-2 bg-green-100 border border-green-400 text-green-700 rounded">
      {{ successMessage }}
    </div>
    
    <div v-if="errorMessage" class="mt-4 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
      {{ errorMessage }}
    </div>
    
    <slot></slot>
  </div>
</template>

<script setup lang="ts">
interface SummaryItem {
  label: string;
  value: string | number;
}

defineProps({
  title: {
    type: String,
    default: 'Resumo da transação'
  },
  items: {
    type: Array as () => SummaryItem[],
    required: true
  },
  totals: {
    type: Array as () => SummaryItem[],
    default: () => []
  },
  showDivider: {
    type: Boolean,
    default: true
  },
  successMessage: {
    type: String,
    default: ''
  },
  errorMessage: {
    type: String,
    default: ''
  }
});
</script>