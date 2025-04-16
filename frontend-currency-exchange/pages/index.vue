<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Listagem de Moedas</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 content-center gap-6">
        <div class="space-y-4 p-4 bg-gray-100 rounded-lg">
          <table class="min-w-full bg-white border border-gray-200 p-2">
              <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal p-2">
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Taxa</th>
                </tr>
              </thead>
              <tbody>
                  <tr v-for="currency in currencies" :key="currency.code" class="border-b border-gray-200 p-2">
                      <td>{{ currency.code }}</td>
                      <td>{{ currency.name }}</td>
                      <td>{{ currency.exchange_rate }}</td>
                  </tr>
              </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'

interface Currency {
  code: string,
  name: string,
  exchange_rate: string
}

interface CurrenciesResponse {
  data: Currency[]
  success?: boolean
  error?: string
}

const loading = ref<boolean>(false)
const error = ref<string | null>(null)

const currencies = ref<Currency[]>([])

const api = useApi()

const loadCurrencies = async () => {
  loading.value = true
  try {
    const { data, error: apiError } = await api.get<CurrenciesResponse>('/api/currencies')
    if (apiError.value) {
      throw apiError.value
    }
    currencies.value = data.value?.data || []

    loading.value = false
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load currencies'
  }
}

onMounted(() => {
  loadCurrencies()
})
</script>
