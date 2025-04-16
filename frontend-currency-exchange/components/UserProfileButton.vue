<template>
  <div class="relative">
    <button 
      @click="toggleMenu" 
      class="flex items-center space-x-1 py-1 px-2 rounded hover:bg-blue-700 transition duration-150 ease-in-out focus:outline-none"
    >
      <span v-if="userName" class="text-sm">{{ userName }}</span>
      <span v-else class="text-sm">Minha Conta</span>
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
      </svg>
    </button>
    
    <div 
      v-if="isMenuOpen" 
      class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
    >
      <template v-if="isAuthenticated">
        <NuxtLink 
          to="/profile" 
          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          @click="isMenuOpen = false"
        >
          Meu Perfil
        </NuxtLink>
        <NuxtLink 
          to="/transactions" 
          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          @click="isMenuOpen = false"
        >
          Minhas Transações
        </NuxtLink>
        <div 
          @click="handleLogout" 
          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
        >
          Sair
        </div>
      </template>
      <template v-else>
        <NuxtLink 
          to="/login" 
          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          @click="isMenuOpen = false"
        >
          Login
        </NuxtLink>
        <NuxtLink 
          to="/register" 
          class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
          @click="isMenuOpen = false"
        >
          Cadastro
        </NuxtLink>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()

const isMenuOpen = ref(false)
const isAuthenticated = computed(() => auth.isAuthenticated.value)
const userName = computed(() => auth.user.value?.name || '')

// Fechar menu ao clicar fora
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (isMenuOpen.value && !target.closest('.relative')) {
    isMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const handleLogout = () => {
  auth.logout()
  isMenuOpen.value = false
  navigateTo('/login')
}
</script>