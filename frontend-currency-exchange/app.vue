<template>
  <div class="min-h-screen bg-gray-50">
    <NuxtLayout>
      <NuxtPage />
    </NuxtLayout>
  </div>
</template>

<script setup lang="ts">
// Set app metadata
useHead({
  title: 'Comprar Moedas Online',
  meta: [
    { name: 'description', content: 'Compre vários tipos de moedas Online' }
  ]
})
</script>
