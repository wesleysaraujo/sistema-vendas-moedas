<template>
  <div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login</h2>
      
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input 
            type="email" 
            id="email" 
            v-model="email" 
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
            required
          />
        </div>
        
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
            required
          />
        </div>
        
        <button 
          type="submit" 
          class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="loading"
        >
          <span v-if="loading">Entrando...</span>
          <span v-else>Entrar</span>
        </button>
        
        <div v-if="error" class="p-2 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ error }}
        </div>
      </form>
      
      <div class="mt-4 text-center text-sm text-gray-600">
        <p>Não tem uma conta? <NuxtLink to="/register" class="text-blue-600 hover:underline">Cadastre-se</NuxtLink></p>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

// Verificar se o usuário já está autenticado
onMounted(() => {
  auth.initAuth()
  if (auth.isAuthenticated.value) {
    router.push('/buy')
  }
})

// Função para fazer login
const handleLogin = async () => {
  if (!email.value || !password.value) {
    error.value = 'Por favor, preencha todos os campos'
    return
  }
  
  loading.value = true
  error.value = ''
  
  const success = await auth.login({
    email: email.value,
    password: password.value
  })
  
  loading.value = false
  
  if (success) {
    router.push('/buy')
  } else {
    error.value = auth.error.value || 'Falha ao fazer login. Verifique suas credenciais.'
  }
}
</script>