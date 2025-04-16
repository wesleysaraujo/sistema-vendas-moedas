import { ref } from 'vue'

export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBaseUrl

  const get = async <T>(endpoint: string) => {
    const data = ref<T | null>(null)
    const error = ref<Error | null>(null)
    const loading = ref<boolean>(false)

    try {
      loading.value = true
      
      // Obter o token de autenticação do localStorage
      const headers: Record<string, string> = {}
      
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
      }
      
      const response = await fetch(`${baseURL}${endpoint}`, {
        headers
      })
      
      if (!response.ok) {
        const contentErrors = await response.json()

        if (contentErrors.errors) {
          // Lidar com erros específicos da API
          // preciso pegar os erros de cada campo dentro objeto errors  e concatenar em uma única string
          const errorMessages = Object.values(contentErrors.errors)
            .flat()
            .join(', ')
          throw new Error(`API error: ${response.status} - ${errorMessages}`)
        }
      }
      
      data.value = await response.json()
    } catch (err) {
      error.value = err instanceof Error ? err : new Error('Unknown error')
    } finally {
      loading.value = false
    }

    return { data, error, loading }
  }

  const post = async <T>(endpoint: string, body: any) => {
    const data = ref<T | null>(null)
    const error = ref<Error | null>(null)
    const loading = ref<boolean>(false)

    try {
      loading.value = true
      
      const headers: Record<string, string> = {
        'Content-Type': 'application/json'
      }
      
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        if (token) {
          headers['Authorization'] = `Bearer ${token}`
        }
      }
      
      const response = await fetch(`${baseURL}${endpoint}`, {
        method: 'POST',
        headers,
        body: JSON.stringify(body)
      })
      
      if (!response.ok) {
        // Preciso retornar o erro da API em Laravel
        const contentErrors = await response.json()

        if (contentErrors.errors) {
          // Lidar com erros específicos da API
          // preciso pegar os erros de cada campo dentro objeto errors  e concatenar em uma única string
          const errorMessages = Object.values(contentErrors.errors)
            .flat()
            .join(', ')
          throw new Error(`API error: ${response.status} - ${errorMessages}`)
        }
      }
      
      data.value = await response.json()
    } catch (err) {
      error.value = err instanceof Error ? err : new Error('Unknown error')
    } finally {
      loading.value = false
    }

    return { data, error, loading }
  }

  return {
    get,
    post
  }
}
