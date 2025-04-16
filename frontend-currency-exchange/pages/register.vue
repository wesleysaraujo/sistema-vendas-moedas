<template>
  <div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Cadastro</h2>
      
      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
          <input 
            type="text" 
            id="name" 
            v-model="name" 
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
            required
          />
        </div>
        
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
            minlength="6"
          />
        </div>
        
        <div>
          <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Senha</label>
          <input 
            type="password" 
            id="confirm-password" 
            v-model="confirmPassword" 
            class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
            required
          />
        </div>
        
        <button 
          type="submit" 
          class="w-full py-2 px-4 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="loading"
        >
          <span v-if="loading">Cadastrando...</span>
          <span v-else>Cadastrar</span>
        </button>
        
        <div v-if="error" class="p-2 bg-red-100 border border-red-400 text-red-700 rounded">
          {{ error }}
        </div>
      </form>
      
      <div class="mt-4 text-center text-sm text-gray-600">
        <p>Já tem uma conta? <NuxtLink to="/login" class="text-blue-600 hover:underline">Faça login</NuxtLink></p>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()
const router = useRouter()

const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)
const error = ref('')

// Verificar se o usuário já está autenticado
onMounted(() => {
  auth.initAuth()
  if (auth.isAuthenticated.value) {
    router.push('/buy')
  }
})

// Validar o formulário
const isFormValid = computed(() => {
  return (
    name.value.trim() !== '' &&
    email.value.trim() !== '' &&
    password.value.length >= 6 &&
    password.value === confirmPassword.value
  )
})

// Função para registrar-se
const handleRegister = async () => {
  // Validar se as senhas são iguais
  if (password.value !== confirmPassword.value) {
    error.value = 'As senhas não coincidem'
    return
  }
  
  // Validar tamanho mínimo da senha
  if (password.value.length < 6) {
    error.value = 'A senha deve ter pelo menos 6 caracteres'
    return
  }
  
  if (!isFormValid.value) {
    error.value = 'Por favor, preencha todos os campos corretamente'
    return
  }
  
  loading.value = true
  error.value = ''
  
  const success = await auth.register({
    name: name.value,
    email: email.value,
    password: password.value,
    password_confirmation: confirmPassword.value
  })
  
  loading.value = false
  
  if (success) {
    router.push('/buy')
    window.location.reload()
  } else {
    error.value = auth.error.value || 'Falha ao registrar. Tente novamente.'
  }
}
</script>
