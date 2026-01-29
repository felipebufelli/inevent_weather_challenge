import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { API_URL } from '@/config/api'

interface User {
  id: number
  email: string
  name: string
  city: string
}

interface AuthResult {
  success: boolean
  message?: string
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

  async function login(email: string, password: string): Promise<AuthResult> {
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

  async function register(
    name: string,
    email: string,
    password: string,
    city: string,
  ): Promise<AuthResult> {
    try {
      const response = await fetch(`${API_URL}/auth/register`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name, email, password, city }),
      })

      const data = await response.json()

      if (!response.ok) {
        return { success: false, message: data.message || 'Erro ao cadastrar' }
      }

      // Auto-login after registration
      token.value = data.token
      user.value = data.user

      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('auth_user', JSON.stringify(data.user))

      return { success: true }
    } catch (error) {
      return { success: false, message: 'Erro de conexão. Tente novamente.' }
    }
  }

  async function updateProfile(data: Partial<User> & { password?: string }): Promise<AuthResult> {
    if (!token.value) {
      return { success: false, message: 'Não autenticado' }
    }

    try {
      const response = await fetch(`${API_URL}/user`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token.value}`,
        },
        body: JSON.stringify(data),
      })

      const result = await response.json()

      if (!response.ok) {
        return { success: false, message: result.message || 'Erro ao atualizar perfil' }
      }

      // Update local user data
      if (result.data) {
        user.value = result.data
        localStorage.setItem('auth_user', JSON.stringify(result.data))
      }

      return { success: true }
    } catch (error) {
      return { success: false, message: 'Erro de conexão. Tente novamente.' }
    }
  }

  async function deleteAccount(): Promise<AuthResult> {
    if (!token.value) {
      return { success: false, message: 'Não autenticado' }
    }

    try {
      const response = await fetch(`${API_URL}/user`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token.value}`,
        },
      })

      const data = await response.json()

      if (!response.ok) {
        return { success: false, message: data.message || 'Erro ao excluir conta' }
      }

      // Clear auth data after account deletion
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')

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
    register,
    updateProfile,
    deleteAccount,
    logout,
  }
}
