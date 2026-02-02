import { describe, it, expect, vi, beforeEach } from 'vitest'
import {
  loginApi,
  registerApi,
  deleteAccountApi,
  getProfileApi,
  type RegisterData,
} from './auth'

// vi.mock is hoisted - use string literal inside factory (no top-level variables)
vi.mock('@/config/api', () => ({
  API_URL: 'http://test.api',
}))

describe('auth service', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
  })

  describe('loginApi', () => {
    it('returns user and token on success', async () => {
      const mockUser = { id: 1, name: 'Test', email: 'a@b.com', city: 'SP' }
      const mockToken = 'jwt-token'
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({ user: mockUser, token: mockToken }),
      } as Response)

      const result = await loginApi('a@b.com', '123456')

      expect(result.success).toBe(true)
      expect(result.user).toEqual(mockUser)
      expect(result.token).toBe(mockToken)
      expect(fetch).toHaveBeenCalledWith(
        'http://test.api/auth/login',
        expect.objectContaining({
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email: 'a@b.com', password: '123456' }),
        })
      )
    })

    it('returns error when response not ok', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Credenciais inválidas' }),
      } as Response)

      const result = await loginApi('a@b.com', 'wrong')

      expect(result.success).toBe(false)
      expect(result.message).toBe('Credenciais inválidas')
    })

    it('returns connection error on fetch failure', async () => {
      vi.mocked(fetch).mockRejectedValueOnce(new Error('Network error'))

      const result = await loginApi('a@b.com', '123456')

      expect(result.success).toBe(false)
      expect(result.message).toBe('Erro de conexão com o servidor')
    })
  })

  describe('registerApi', () => {
    it('returns user and token on success', async () => {
      const userData: RegisterData = {
        name: 'User',
        email: 'u@b.com',
        password: '123456',
        city: 'RJ',
      }
      const mockUser = { id: 1, ...userData }
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () =>
          Promise.resolve({
            message: 'Cadastrado',
            user: mockUser,
            token: 'token',
          }),
      } as Response)

      const result = await registerApi(userData)

      expect(result.success).toBe(true)
      expect(result.user).toEqual(mockUser)
      expect(result.token).toBe('token')
      expect(fetch).toHaveBeenCalledWith(
        'http://test.api/auth/register',
        expect.objectContaining({
          method: 'POST',
          body: JSON.stringify(userData),
        })
      )
    })

    it('returns error when response not ok', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'E-mail já cadastrado' }),
      } as Response)

      const result = await registerApi({
        name: 'U',
        email: 'u@b.com',
        password: '123',
        city: 'SP',
      })

      expect(result.success).toBe(false)
      expect(result.message).toBe('E-mail já cadastrado')
    })
  })

  describe('deleteAccountApi', () => {
    it('sends Bearer token and returns success', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({ message: 'Conta excluída' }),
      } as Response)

      const result = await deleteAccountApi('my-token')

      expect(result.success).toBe(true)
      expect(fetch).toHaveBeenCalledWith(
        'http://test.api/user',
        expect.objectContaining({
          method: 'DELETE',
          headers: expect.objectContaining({
            Authorization: 'Bearer my-token',
          }),
        })
      )
    })

    it('returns error when response not ok', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Erro ao excluir' }),
      } as Response)

      const result = await deleteAccountApi('token')

      expect(result.success).toBe(false)
      expect(result.message).toBe('Erro ao excluir')
    })
  })

  describe('getProfileApi', () => {
    it('sends Bearer token and returns user', async () => {
      const mockUser = { id: 1, name: 'U', email: 'u@b.com', city: 'SP' }
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: true,
        json: () => Promise.resolve({ user: mockUser }),
      } as Response)

      const result = await getProfileApi('token')

      expect(result.success).toBe(true)
      expect(result.user).toEqual(mockUser)
      expect(fetch).toHaveBeenCalledWith(
        'http://test.api/auth/me',
        expect.objectContaining({
          method: 'GET',
          headers: expect.objectContaining({
            Authorization: 'Bearer token',
          }),
        })
      )
    })

    it('returns error when response not ok', async () => {
      vi.mocked(fetch).mockResolvedValueOnce({
        ok: false,
        json: () => Promise.resolve({ message: 'Token inválido' }),
      } as Response)

      const result = await getProfileApi('bad-token')

      expect(result.success).toBe(false)
      expect(result.message).toBe('Token inválido')
    })
  })
})
