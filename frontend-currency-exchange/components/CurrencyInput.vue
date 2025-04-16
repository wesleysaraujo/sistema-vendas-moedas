<template>
  <div>
    <label :for="id" class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
    <div class="mt-1 relative rounded-md shadow-sm">
      <div v-if="showSymbol" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <span class="text-gray-500 sm:text-sm">{{ symbol }}</span>
      </div>
      <input
        :id="id"
        type="number"
        :value="modelValue"
        @input="$emit('update:modelValue', Number(($event.target as HTMLInputElement).value))"
        class="block w-full pr-12 sm:text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
        :class="{'pl-7': showSymbol, 'pl-3': !showSymbol}"
        :placeholder="placeholder"
        :disabled="disabled"
        :min="min"
        :max="max"
        :step="step"
      />
      <div class="absolute inset-y-0 right-0 flex items-center">
        <select
          v-if="currencySelect"
          :value="currency"
          @change="$emit('update:currency', ($event.target as HTMLSelectElement).value)"
          class="h-full py-0 pl-2 pr-7 border-transparent bg-transparent text-gray-500 sm:text-sm rounded-md focus:ring-blue-500 focus:border-blue-500"
        >
          <option v-for="option in currencyOptions" :key="option.value" :value="option.value">
            {{ option.label }}
          </option>
        </select>
        <span v-else-if="currency" class="pr-3 text-gray-500 sm:text-sm">
          {{ currency }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface CurrencyOption {
  value: string;
  label: string;
}

defineProps({
  modelValue: {
    type: Number,
    required: true
  },
  currency: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    required: true
  },
  id: {
    type: String,
    required: true
  },
  placeholder: {
    type: String,
    default: '0.00'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  min: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: undefined
  },
  step: {
    type: Number,
    default: 0.01
  },
  showSymbol: {
    type: Boolean,
    default: false
  },
  symbol: {
    type: String,
    default: '$'
  },
  currencySelect: {
    type: Boolean,
    default: false
  },
  currencyOptions: {
    type: Array as () => CurrencyOption[],
    default: () => []
  }
});

defineEmits(['update:modelValue', 'update:currency']);
</script>