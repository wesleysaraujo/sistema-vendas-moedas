<template>
  <div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Comprar Moeda Estrangeira</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Formulário de Compra -->
        <div class="space-y-4">
          <div>
            <CurrencyInput
              id="localAmount"
              v-model="localAmount"
              label="Valor em moeda local"
              :currency="localCurrency"
              showSymbol
              symbol="R$"
              :placeholder="'Valor em ' + localCurrency"
            />
          </div>

          <!-- Loading -->
           <div v-if="loading">
            <div class="flex justify-center items-center h-64">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
           </div>
          
          <div v-if="!loading">
            <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Moeda estrangeira</label>
            <select 
              v-model="foreignCurrency"
              class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
            >
              <option v-for="currency in availableCurrencies" :key="currency.code" :value="currency.code">
                {{ currency.code }} - {{ currency.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valor a receber</label>
            <div class="p-2 bg-gray-100 border border-gray-300 rounded-md">
              {{ foreignAmount.toFixed(2) }} {{ foreignCurrency }}
            </div>
          </div>
          
          <button 
            @click="confirmPurchase" 
            class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            :disabled="loading || !canPurchase"
          >
            <span v-if="loading">Processando...</span>
            <span v-else>Confirmar Compra</span>
          </button>
          </div>
        </div>
        
        <TransactionSummary
          :items="summaryItems"
          :totals="summaryTotals"
          :successMessage="success ? 'Compra realizada com sucesso!' : ''"
          :errorMessage="error || ''"
        />
      </div>
    </div>
    
    <!-- Lista de transações -->
    <TransactionList ref="transactionList" />
  </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch } from 'vue'
import CurrencyInput from '~/components/CurrencyInput.vue'
import TransactionSummary from '~/components/TransactionSummary.vue'
import TransactionList from '~/components/TransactionList.vue'

// Aplicar middleware de autenticação
definePageMeta({
  middleware: ['auth']
})

interface Currency {
  code: string
  name: string
  exchange_rate?: number
}

interface CurrenciesResponse {
  data: Currency[]
  success?: boolean
  error?: string
}

interface PurchaseResponse {
  success: boolean
  transaction_id?: string
  error?: string
}

interface SimulateTransactionResponse {
  data: {
    amount: number,
    exchange_rate: number,
    fee_percentage: number,
    fee_amount: number,
    foreign_amount: number,
    total_amount: number,
  }
}

const localCurrency = ref<string>('BRL')
const foreignCurrency = ref<string>()
const localAmount = ref<number>(50)
const serviceFeeAmount = ref<number>(0)
const foreignAmount = ref<number>(0)
const currentRate = ref<number>(0)
const availableCurrencies = ref<Currency[]>([])
const loading = ref<boolean>(false)
const error = ref<string | null>(null)
const success = ref<boolean>(false)
const transactionList = ref<InstanceType<typeof TransactionList> | null>(null)

const config = useRuntimeConfig()
const api = useApi()
const route = useRoute()

const loadCurrencies = async () => {
  loading.value = true
  try {
    const { data, error: apiError } = await api.get<CurrenciesResponse>('/api/currencies')
    if (apiError.value) {
      throw apiError.value
    }
    availableCurrencies.value = data.value?.data || []
    foreignCurrency.value = route.query.code ?? 'USD'
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Failed to load currencies'
  } finally {
    loading.value = false
  }
}

const serviceFee = computed<number>(() => config.public.serviceFee)
const totalAmount = computed<number>(() => localAmount.value + serviceFeeAmount.value)

// Itens do resumo da transação
const summaryItems = computed(() => [
  {
    label: `Valor em ${localCurrency.value}`,
    value: `${localAmount.value?.toFixed(2)} ${localCurrency.value}`
  },
  {
    label: 'Taxa de serviço',
    value: `${serviceFeeAmount.value?.toFixed(2)} ${localCurrency.value} (${serviceFee.value}%)`
  },
  {
    label: 'Taxa de câmbio',
    value: `1 ${foreignCurrency.value} = ${parseFloat(currentRate.value).toFixed(2)} ${localCurrency.value}`
  }
])

// Totais do resumo da transação
const summaryTotals = computed(() => [
  {
    label: 'Total a pagar',
    value: `${totalAmount.value?.toFixed(2)} ${localCurrency.value}`
  },
  {
    label: 'Total a receber',
    value: `${foreignAmount.value?.toFixed(2)} ${foreignCurrency.value}`
  }
])

// Verificar se é possível comprar
const canPurchase = computed<boolean>(() => 
  localAmount.value >= 50 && 
  foreignCurrency.value !== localCurrency.value &&
  availableCurrencies.value.length > 0
)

// Buscar taxa de câmbio
const fetchExchangeRate = async () => {
  loading.value = true
  error.value = null
  
  try {
    const { data, error: apiError } = await api.get<{ data: { exchange_rate: number } }>(
      `/api/currencies/show/${foreignCurrency.value}`
    )
    
    if (apiError.value) {
      throw apiError.value
    }
    
    if (data.value?.data) {
      currentRate.value = data.value.data.exchange_rate
      simulateTransaction()
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Falha ao obter taxa de câmbio'
    currentRate.value = 0
    foreignAmount.value = 0
  } finally {
    loading.value = false
  }
}

// Simular a transação para obter o valor exato
const simulateTransaction = async () => {
  if (localAmount.value <= 0 || foreignCurrency.value === localCurrency.value) {
    return
  }
  
  loading.value = true
  error.value = null
  
  try {
    const { data, error: apiError } = await api.post<SimulateTransactionResponse>(
      '/api/transactions/simulate',
      {
        currency_code: foreignCurrency.value,
        amount: localAmount.value
      }
    )
    
    if (apiError.value) {
      throw apiError.value
    }
    
    if (data.value?.data) {
      foreignAmount.value = data.value.data.foreign_amount
      currentRate.value = data.value.data.exchange_rate
      serviceFeeAmount.value = data.value.data.fee_amount
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Falha ao simular transação'
    foreignAmount.value = 0
  } finally {
    loading.value = false
  }
}

// Confirmar a compra
const confirmPurchase = async () => {
  if (!canPurchase.value) return
  
  loading.value = true
  error.value = null
  success.value = false
  
  try {
    const purchaseData = {
      currency_code: foreignCurrency.value,
      amount: localAmount.value
    }
    
    const { data, error: apiError } = await api.post<PurchaseResponse>('/api/transactions', purchaseData)
    
    if (apiError.value) {
      throw apiError.value
    }
    
    if (data.value?.success) {
      success.value = true
      // Recarregar a lista de transações após uma compra bem-sucedida
      if (transactionList.value) {
        transactionList.value.loadTransactions()
      }
    } else if (data.value?.error) {
      throw new Error(data.value.error)
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Falha ao processar compra'
  } finally {
    loading.value = false
  }
}

// Simular a transação quando o valor mudar
watch(localAmount, simulateTransaction)

// Atualizar a taxa de câmbio quando a moeda mudar
watch(foreignCurrency, fetchExchangeRate)

onMounted(() => {
  loadCurrencies()
})
</script>
