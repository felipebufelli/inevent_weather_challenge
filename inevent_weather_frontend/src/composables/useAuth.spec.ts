import { describe, it, expect, vi, beforeEach } from 'vitest'
import { useAuth } from './useAuth'

// vi.mock is hoisted - use vi.hoisted for values needed inside mock factories
const { mockPush } = vi.hoisted(() => ({ mockPush: vi.fn() }))

vi.mock('@/config/api', () => ({ API_URL: 'http://test.api' }))
vi.mock('vue-router', () => ({
  useRouter: () => ({ push: mockPush }),
}))

describe('useAuth composable', () => {
  beforeEach(() => {
    vi.stubGlobal('fetch', vi.fn())
    localStorage.clear()
    mockPush.mockClear()
  })

  it('exposes user, token, isAuthenticated and methods', () => {
    const auth = useAuth()
    expect(auth).toHaveProperty('user')
    expect(auth).toHaveProperty('token')
    expect(auth).toHaveProperty('isAuthenticated')
    expect(auth).toHaveProperty('login')
    expect(auth).toHaveProperty('register')
    expect(auth).toHaveProperty('logout')
    expect(auth).toHaveProperty('updateProfile')
    expect(auth).toHaveProperty('deleteAccount')
  })

  it('isAuthenticated is false when no token', async () => {
    // Reset module so we get fresh composable state (no token from previous tests)
    vi.resetModules()
    const { useAuth: useAuthFresh } = await import('./useAuth')
    const { isAuthenticated } = useAuthFresh()
    expect(isAuthenticated.value).toBe(false)
  })

  it('login success sets user, token and localStorage', async () => {
    const mockUser = { id: 1, name: 'U', email: 'u@b.com', city: 'SP' }
    const mockToken = 'jwt-123'
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: () => Promise.resolve({ user: mockUser, token: mockToken }),
    } as Response)

    const { login, user, token, isAuthenticated } = useAuth()
    const result = await login('u@b.com', '123456')

    expect(result.success).toBe(true)
    expect(user.value).toEqual(mockUser)
    expect(token.value).toBe(mockToken)
    expect(isAuthenticated.value).toBe(true)
    expect(localStorage.getItem('auth_token')).toBe(mockToken)
    expect(localStorage.getItem('auth_user')).toBe(JSON.stringify(mockUser))
  })

  it('login failure returns message', async () => {
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: false,
      json: () => Promise.resolve({ message: 'Credenciais inválidas' }),
    } as Response)

    const { login } = useAuth()
    const result = await login('u@b.com', 'wrong')

    expect(result.success).toBe(false)
    expect(result.message).toBe('Credenciais inválidas')
  })

  it('register success sets user and token', async () => {
    const mockUser = { id: 1, name: 'U', email: 'u@b.com', city: 'SP' }
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: () =>
        Promise.resolve({ user: mockUser, token: 'token' }),
    } as Response)

    const { register, user, token } = useAuth()
    const result = await register('U', 'u@b.com', '123456', 'SP')

    expect(result.success).toBe(true)
    expect(user.value).toEqual(mockUser)
    expect(token.value).toBe('token')
  })

  it('logout clears state and navigates to login', async () => {
    const mockUser = { id: 1, name: 'U', email: 'u@b.com', city: 'SP' }
    vi.mocked(fetch).mockResolvedValueOnce({
      ok: true,
      json: () =>
        Promise.resolve({ user: mockUser, token: 't' }),
    } as Response)

    const { login, logout, user, token } = useAuth()
    await login('u@b.com', '123456')
    expect(user.value).not.toBeNull()

    logout()

    expect(user.value).toBeNull()
    expect(token.value).toBeNull()
    expect(localStorage.getItem('auth_token')).toBeNull()
    expect(mockPush).toHaveBeenCalledWith('/login')
  })

  it('updateProfile without token returns error', async () => {
    vi.resetModules()
    const { useAuth: useAuthFresh } = await import('./useAuth')
    const { updateProfile } = useAuthFresh()
    const result = await updateProfile({ name: 'X' })
    expect(result.success).toBe(false)
    expect(result.message).toBe('Não autenticado')
  })

  it('deleteAccount without token returns error', async () => {
    vi.resetModules()
    const { useAuth: useAuthFresh } = await import('./useAuth')
    const { deleteAccount } = useAuthFresh()
    const result = await deleteAccount()
    expect(result.success).toBe(false)
    expect(result.message).toBe('Não autenticado')
  })
})
