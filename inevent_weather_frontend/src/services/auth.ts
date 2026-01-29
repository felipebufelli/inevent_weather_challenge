import { API_URL } from '@/config/api'

export interface User {
  id: number
  name: string
  email: string
  city: string
}

export interface AuthResponse {
  success: boolean
  message?: string
  user?: User
  token?: string
  error?: boolean
}

export interface RegisterData {
  name: string
  email: string
  password: string
  city: string
}

export async function loginApi(email: string, password: string): Promise<AuthResponse> {
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
      return {
        success: false,
        message: data.message || 'Erro ao fazer login',
      }
    }

    return {
      success: true,
      user: data.user,
      token: data.token,
    }
  } catch (error) {
    return {
      success: false,
      message: 'Erro de conexão com o servidor',
    }
  }
}

export async function registerApi(userData: RegisterData): Promise<AuthResponse> {
  try {
    const response = await fetch(`${API_URL}/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(userData),
    })

    const data = await response.json()

    if (!response.ok) {
      return {
        success: false,
        message: data.message || 'Erro ao cadastrar usuário',
      }
    }

    return {
      success: true,
      message: data.message,
      user: data.user,
      token: data.token,
    }
  } catch (error) {
    return {
      success: false,
      message: 'Erro de conexão com o servidor',
    }
  }
}

export async function deleteAccountApi(token: string): Promise<AuthResponse> {
  try {
    const response = await fetch(`${API_URL}/user`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      return {
        success: false,
        message: data.message || 'Erro ao excluir conta',
      }
    }

    return {
      success: true,
      message: data.message,
    }
  } catch (error) {
    return {
      success: false,
      message: 'Erro de conexão com o servidor',
    }
  }
}

export async function getProfileApi(token: string): Promise<AuthResponse> {
  try {
    const response = await fetch(`${API_URL}/auth/me`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })

    const data = await response.json()

    if (!response.ok) {
      return {
        success: false,
        message: data.message || 'Erro ao buscar perfil',
      }
    }

    return {
      success: true,
      user: data.user,
    }
  } catch (error) {
    return {
      success: false,
      message: 'Erro de conexão com o servidor',
    }
  }
}
