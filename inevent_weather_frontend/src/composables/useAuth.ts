import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '@/config/api'

interface User {
  email: string
  name: string
}

const user = ref<User | null>(null)
const token = ref<string | null>(null)
const isInitialized = ref(false)

// Initialize from localStorage
function initAuth() {
  if (isInitialized.value) return

  const storedToken = localStorage.getItem('auth_token')
  const storedUser = localStorage.getItem('auth_user')

  if (storedToken && storedUser) {
    token.value = storedToken
    user.value = JSON.parse(storedUser)
  }

  isInitialized.value = true
}

export function useAuth() {
  const router = useRouter()

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string): Promise<{ success: boolean; message?: string }> {
    try {
      const response = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email, password }),
      })

      const data = await response.json()

      if (!response.ok) {
        return { success: false, message: data.message || 'Erro ao fazer login' }
      }

      // Store auth data
      token.value = data.token
      user.value = data.user

      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(data.user))

      return { success: true }
    } catch (error) {
      return { success: false, message: 'Erro de conexão. Tente novamente.' }
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    router.push('/login')
  }

  // Initialize on first use
  initAuth()

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout,
  }
}
