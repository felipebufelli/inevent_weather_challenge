import { API_URL } from '@/config/api'

export interface LoginResponse {
  success: boolean
  message?: string
  user?: {
    email: string
    name: string
  }
  token?: string
}

export async function loginApi(email: string, password: string): Promise<LoginResponse> {
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
}
