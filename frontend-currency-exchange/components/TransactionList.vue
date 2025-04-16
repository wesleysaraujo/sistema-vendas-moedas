<template>
  <div class="bg-white rounded-lg shadow-md p-6 my-8">
    <h2 class="text-xl font-bold mb-4">{{ title }}</h2>
    
    <div v-if="loading" class="py-4 text-center text-gray-500">
      Carregando transações...
    </div>
    
    <div v-else-if="error" class="py-4 text-center text-red-500">
      {{ error }}
    </div>
    
    <div v-else-if="transactions.length === 0" class="py-4 text-center text-gray-500">
      Nenhuma transação encontrada.
    </div>
    
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              ID
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Data
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Moeda
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Taxa de Câmbio
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Valor Comprado (BRL)
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Valor Pago (BRL)
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Valor recebido
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ transaction.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(transaction.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ transaction.metadata.currency_code }} - {{ transaction.metadata.currency_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ transaction.exchange_rate }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatCurrency(transaction.amount, 'BRL') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ formatCurrency(transaction.amount_paid, 'BRL') }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              {{ formatCurrency(transaction.foreign_amount, transaction.metadata.currency_code) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <span 
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                :class="statusClass(transaction.status)"
              >
                {{ transaction.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div v-if="pagination && pagination.last_page > 1" class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4">
      <div class="flex-1 flex justify-between sm:hidden">
        <button 
          @click="loadTransactions(pagination.current_page - 1)" 
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          :disabled="!pagination.links.prev"
          :class="{'opacity-50 cursor-not-allowed': !pagination.links.prev}"
        >
          Anterior
        </button>
        <button 
          @click="loadTransactions(pagination.current_page + 1)" 
          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          :disabled="!pagination.links.next"
          :class="{'opacity-50 cursor-not-allowed': !pagination.links.next}"
        >
          Próxima
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Mostrando <span class="font-medium">{{ pagination.meta?.from || 0 }}</span> a 
            <span class="font-medium">{{ pagination.meta?.to || 0 }}</span> de 
            <span class="font-medium">{{ pagination.total }}</span> resultados
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button 
              @click="loadTransactions(pagination.current_page - 1)" 
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              :disabled="!pagination.links.prev"
              :class="{'opacity-50 cursor-not-allowed': !pagination.links.prev}"
            >
              <span class="sr-only">Anterior</span>
              &larr;
            </button>
            
            <template v-for="page in pagination.last_page" :key="page">
              <button
                v-if="showPageButton(page)"
                @click="loadTransactions(page)"
                :class="[
                  page === pagination.current_page ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                ]"
              >
                {{ page }}
              </button>
              
              <span
                v-if="showEllipsis(page)"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
              >
                ...
              </span>
            </template>
            
            <button
              @click="loadTransactions(pagination.current_page + 1)"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
              :disabled="!pagination.links.next"
              :class="{'opacity-50 cursor-not-allowed': !pagination.links.next}"
            >
              <span class="sr-only">Próxima</span>
              &rarr;
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface PaginationMeta {
  current_page: number;
  from: number;
  last_page: number;
  path: string;
  per_page: number;
  to: number;
  total: number;
}

interface PaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}

interface Paginator {
  data: Object[];
  meta: PaginationMeta;
  links: {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
  };
  current_page: number;
  last_page: number;
  total: number;
}

interface TransactionsResponse {
  data: Paginator;
  success: boolean;
  error?: string;
}

const props = defineProps({
  title: {
    type: String,
    default: 'Minhas Transações'
  }
});

const transactions = ref<Transaction[]>([])
const pagination = ref<Omit<Paginator, 'data'> | null>(null)
const loading = ref<boolean>(false)
const error = ref<string | null>(null)
const currentPage = ref<number>(1)

const api = useApi()

const loadTransactions = async (page = 1) => {
  loading.value = true
  error.value = null
  currentPage.value = page
  
  try {
    const { data, error: apiError } = await api.get<TransactionsResponse>(`/api/transactions?page=${page}`)
    
    if (apiError.value) {
      throw apiError.value
    }
    
    if (data.value?.data) {
      transactions.value = data.value.data.data || []
      
      pagination.value = {
        meta: data.value.data.meta,
        links: data.value.data.links,
        current_page: data.value.data.current_page,
        last_page: data.value.data.last_page,
        total: data.value.data.total
      }
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Falha ao carregar transações'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(date)
}

const formatCurrency = (amount: number, currency: string) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: currency,
  }).format(amount)
}

const statusClass = (status: string) => {
  switch (status.toLowerCase()) {
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'failed':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

onMounted(() => {
  loadTransactions()
})

// Função para determinar se mostrar um botão de página específico
const showPageButton = (page: number) => {
  if (!pagination.value) return false
  
  const currentPage = pagination.value.current_page
  const lastPage = pagination.value.last_page
  
  if (page === 1 || page === lastPage) return true
  
  if (page >= currentPage - 2 && page <= currentPage + 2) return true
  
  return false
}

// Função para determinar se mostrar uma elipse 
const showEllipsis = (page: number) => {
  if (!pagination.value) return false
  
  const currentPage = pagination.value.current_page
  const lastPage = pagination.value.last_page
  
  if (page === 2 && currentPage > 4) return true
  if (page === lastPage - 1 && currentPage < lastPage - 3) return true
  
  return false
}

defineExpose({
  loadTransactions
})
</script>
