<template>
  <div>
    <div v-if="loading" class="flex justify-center p-6">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>
    
    <div v-else-if="error" class="p-4 bg-red-100 border border-red-400 text-red-700 rounded mb-4">
      {{ error }}
    </div>
    
    <div v-else-if="rates.length">
      <p v-if="lastUpdated" class="text-sm text-gray-500 mb-4">
        Exchange rates against {{ baseCurrency }} as of {{ formatDate(lastUpdated) }}
      </p>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Currency</th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
              <th v-if="showChange" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change (24h)</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="rate in rates" :key="rate.currency">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ rate.currency }}</div>
                    <div class="text-sm text-gray-500">{{ getCurrencyName(rate.currency) }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatNumber(rate.rate) }}
              </td>
              <td v-if="showChange && rate.change !== undefined" class="px-6 py-4 whitespace-nowrap text-sm">
                <span :class="getChangeClass(rate.change)">
                  {{ formatChange(rate.change) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div v-else class="text-center p-4">
      <p class="text-gray-500">No exchange rate data available</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface ExchangeRate {
  currency: string
  rate: number
  change?: number
}

const props = defineProps({
  rates: {
    type: Array as () => ExchangeRate[],
    required: true
  },
  baseCurrency: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  lastUpdated: {
    type: String,
    default: ''
  },
  showChange: {
    type: Boolean,
    default: false
  },
  currencyNames: {
    type: Object as () => Record<string, string>,
    default: () => ({})
  }
})

const formatNumber = (value: number): string => {
  return value.toFixed(4)
}

const formatChange = (change: number): string => {
  const prefix = change > 0 ? '+' : ''
  return `${prefix}${change.toFixed(2)}%`
}

const getChangeClass = (change: number): string => {
  if (change > 0) return 'text-green-600'
  if (change < 0) return 'text-red-600'
  return 'text-gray-500'
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleString()
}

const getCurrencyName = (code: string): string => {
  return props.currencyNames[code] || code
}
</script>