<template>
  <div class="flex flex-col min-h-screen">
    <header class="bg-blue-600 text-white shadow-md">
      <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
          <h1 class="text-2xl font-bold">Comprar Moedas Online</h1>
          <nav>
            <ul class="flex space-x-4">
              <li><NuxtLink to="/" class="hover:text-blue-200">Home</NuxtLink></li>
              <li><NuxtLink to="/buy" class="hover:text-blue-200">Comprar</NuxtLink></li>
              
              <template v-if="isAuthenticated">
                <li><a @click="logout" class="hover:text-blue-200 cursor-pointer">Sair</a></li>
              </template>
              <template v-else>
                <li><NuxtLink to="/login" class="hover:text-blue-200">Login</NuxtLink></li>
                <li><NuxtLink to="/register" class="hover:text-blue-200">Cadastro</NuxtLink></li>
              </template>
            </ul>
          </nav>
        </div>
      </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
      <slot />
    </main>

    <footer class="bg-gray-800 text-white">
      <div class="container mx-auto px-4 py-6">
        <p class="text-center">&copy; 2025 Comprar Moedas Online.</p>
      </div>
    </footer>
  </div>
</template>

<script lang="ts" setup>
import { useAuth } from '~/composables/useAuth'
import { computed, onMounted } from 'vue'

const auth = useAuth()
const isAuthenticated = computed(() => auth.isAuthenticated.value)

// Inicializa autenticação no carregamento
onMounted(() => {
  auth.initAuth()
})

// Função para fazer logout
const logout = () => {
  auth.logout()
  navigateTo('/login')
}
</script>
