<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Listagem de Moedas</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 content-center gap-6">
        <div class="overflow-x-auto">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center h-64">
            <svg class="animate-spin h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 1 1 16 0A8 8 0 0 1 4 12zm2.5 0a5.5 5.5 0 1 0 11 0A5.5 5.5 0 0 0 6.5 12z"></path>
            </svg>
          </div>
          <table class="min-w-full bg-white border border-gray-200" v-if="!loading">
              <thead>
                <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal p-2">
                  <th>Código</th>
                  <th>Nome</th>
                  <th>Taxa</th>
                  <th>Ação</th>
                </tr>
              </thead>
              <tbody>
                  <tr v-for="currency in currencies" :key="currency.code" class="border-b border-gray-200 p-6 hover:bg-gray-100">
                      <td>{{ currency.code }}</td>
                      <td>{{ currency.name }}</td>
                      <td>{{ parseFloat(currency.exchange_rate).toFixed(2) }}</td>
                      <td>
                        <!-- Botão para comprar com link para Buy passando o parametro code -->
                        <NuxtLink :to="{ name: 'buy', query: { code: currency.code } }" class="bg-blue-500 text-white p-1 rounded hover:bg-blue-600">
                          Comprar  
                        </NuxtLink>
                      </td>
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
