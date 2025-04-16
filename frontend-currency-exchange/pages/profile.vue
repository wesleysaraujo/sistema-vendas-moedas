<template>
  <div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Meu Perfil</h2>
      
      <div v-if="user" class="space-y-4">
        <div class="flex items-center justify-center mb-6">
          <div class="h-24 w-24 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-bold">
            {{ userInitials }}
          </div>
        </div>
        
        <div>
          <h3 class="text-sm font-medium text-gray-500">Nome</h3>
          <p class="text-lg">{{ user.name }}</p>
        </div>
        
        <div>
          <h3 class="text-sm font-medium text-gray-500">Email</h3>
          <p class="text-lg">{{ user.email }}</p>
        </div>
        
        <div>
          <h3 class="text-sm font-medium text-gray-500">ID de Usuário</h3>
          <p class="text-sm text-gray-600">{{ user.id }}</p>
        </div>
        
        <div class="pt-4 border-t border-gray-200">
          <button 
            @click="logout" 
            class="w-full py-2 px-4 bg-red-600 text-white font-semibold rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          >
            Sair da Conta
          </button>
        </div>
      </div>
      
      <div v-else class="text-center text-gray-500">
        <p>Você não está autenticado. <NuxtLink to="/login" class="text-blue-600 hover:underline">Faça login</NuxtLink> para visualizar seu perfil.</p>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { useAuth } from '~/composables/useAuth'
import { computed } from 'vue'

// Aplicar middleware de autenticação
definePageMeta({
  middleware: ['auth']
})

const auth = useAuth()
const router = useRouter()

const user = computed(() => auth.user.value)

// Obter as iniciais do nome do usuário para o avatar
const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  
  return user.value.name
    .split(' ')
    .map(name => name.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
})

// Função para fazer logout
const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>