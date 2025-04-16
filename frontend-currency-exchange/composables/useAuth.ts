import { ref, computed } from 'vue'

interface User {
  id: string
  name: string
  email: string,
  email_verified_at?: string,
  created_at: string,
  updated_at: string
}

interface LoginPayload {
  email: string
  password: string
}

interface RegisterPayload extends LoginPayload {
  name: string,
  password_confirmation: string
}

interface AuthResponse {
  user: User
  token_type: string
  access_token: string
  error?: string
}

// Estado da autenticação (persistido localmente)
export const useAuth = () => {
  const api = useApi()
  const user = ref<User | null>(null)
  const token = ref<string | null>(null)
  const loading = ref<boolean>(false)
  const error = ref<string | null>(null)

  // Verificar se o usuário está autenticado
  const isAuthenticated = computed(() => !!token.value && !!user.value)

  // Carregar o estado de autenticação do localStorage ao iniciar
  const initAuth = () => {
    if (process.client) {
      const storedToken = localStorage.getItem('auth_token')
      const storedUser = localStorage.getItem('auth_user')
      
      if (storedToken) {
        token.value = storedToken
      }
      
      if (storedUser) {
        try {
          user.value = JSON.parse(storedUser)
        } catch (err) {
          // Se houver erro no parsing, limpa os dados
          logout()
        }
      }
    }
  }

  // Login do usuário
  const login = async (payload: LoginPayload) => {
    loading.value = true
    error.value = null
    
    try {
      const { data, error: apiError } = await api.post<AuthResponse>('/api/auth/login', payload)
      
      if (apiError.value) {
        throw apiError.value
      }
      
      if (data.value?.token_type && data.value.access_token && data.value.user) {
        // Armazenar token e usuário
        token.value = data.value.access_token
        user.value = data.value.user
        
        // Persistir no localStorage
        if (process.client) {
          localStorage.setItem('auth_token', data.value.access_token)
          localStorage.setItem('auth_user', JSON.stringify(data.value.user))
        }
        
        return true
      } else if (data.value?.error) {
        throw new Error(data.value.error)
      }
      
      return false
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Falha ao fazer login'
      return false
    } finally {
      loading.value = false
    }
  }

  // Registro de novo usuário
  const register = async (payload: RegisterPayload) => {
    loading.value = true
    error.value = null
    
    try {
      const { data, error: apiError } = await api.post<AuthResponse>('/api/auth/register', payload)
      
      if (apiError.value) {
        throw apiError.value
      }
      
      if (data.value?.token_type && data.value.access_token && data.value.user) {
        // Armazenar token e usuário
        token.value = data.value.access_token
        user.value = data.value.user
        
        // Persistir no localStorage
        if (process.client) {
          localStorage.setItem('auth_token', data.value.access_token)
          localStorage.setItem('auth_user', JSON.stringify(data.value.user))
        }
        
        return true
      } else if (data.value?.error) {
        throw new Error(data.value.error)
      }
      
      return false
    } catch (err) {
      error.value = err instanceof Error ? err.message : 'Falha ao registrar novo usuário'
      return false
    } finally {
      loading.value = false
    }
  }

  // Logout do usuário
  const logout = () => {
    user.value = null
    token.value = null
    
    // Remover do localStorage
    if (process.client) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  // Obter o cabeçalho de autorização para requisições
  const getAuthHeader = () => {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    initAuth,
    login,
    register,
    logout,
    getAuthHeader
  }
}
