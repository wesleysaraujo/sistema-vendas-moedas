export default defineNuxtRouteMiddleware((to, from) => {
  // Ignorar middleware no servidor
  if (process.server) return
  
  const auth = useAuth()
  
  // Inicializar autenticação (carrega dados do localStorage)
  auth.initAuth()
  
  // Verificar se o usuário está autenticado
  if (!auth.isAuthenticated.value) {
    // Redireciona para o login se não estiver autenticado
    return navigateTo('/login')
  }
})