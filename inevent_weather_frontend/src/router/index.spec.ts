import { describe, it, expect, beforeEach } from 'vitest'
import { createRouter, createWebHistory } from 'vue-router'

const LoginView = { template: '<div>Login</div>' }
const DashboardView = { template: '<div>Dashboard</div>' }
const UsersView = { template: '<div>Users</div>' }

function createTestRouter() {
  const router = createRouter({
    history: createWebHistory(),
    routes: [
      { path: '/', redirect: '/login' },
      {
        path: '/login',
        name: 'login',
        component: LoginView,
        meta: { requiresGuest: true },
      },
      {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { requiresAuth: true },
      },
      {
        path: '/users',
        name: 'users',
        component: UsersView,
        meta: { requiresAuth: true },
      },
      { path: '/:pathMatch(.*)*', redirect: '/login' },
    ],
  })
  router.beforeEach((to, _from, next) => {
    const token = localStorage.getItem('auth_token')
    const isAuthenticated = !!token
    if (to.meta.requiresAuth && !isAuthenticated) {
      next({ name: 'login' })
    } else if (to.meta.requiresGuest && isAuthenticated) {
      next({ name: 'dashboard' })
    } else {
      next()
    }
  })
  return router
}

describe('router', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('redirects / to /login', async () => {
    const router = createTestRouter()
    await router.push('/')
    expect(router.currentRoute.value.path).toBe('/login')
  })

  it('redirects unknown path to /login', async () => {
    const router = createTestRouter()
    await router.push('/unknown')
    expect(router.currentRoute.value.path).toBe('/login')
  })

  it('has login route with requiresGuest meta', async () => {
    const router = createTestRouter()
    await router.push('/login')
    expect(router.currentRoute.value.name).toBe('login')
    expect(router.currentRoute.value.meta.requiresGuest).toBe(true)
  })

  it('has dashboard route with requiresAuth meta', () => {
    const router = createTestRouter()
    // getRoutes() is synchronous - no navigation needed
    const dashboardRoute = router.getRoutes().find((r) => r.name === 'dashboard')
    expect(dashboardRoute).toBeDefined()
    expect(dashboardRoute!.name).toBe('dashboard')
    expect(dashboardRoute!.meta.requiresAuth).toBe(true)
  })

  it('redirects to login when accessing protected route without token', async () => {
    const router = createTestRouter()
    await router.push('/dashboard')
    await router.isReady()
    expect(router.currentRoute.value.name).toBe('login')
  })

  it('allows dashboard when token exists', async () => {
    localStorage.setItem('auth_token', 'fake-token')
    const router = createTestRouter()
    await router.push('/dashboard')
    await router.isReady()
    expect(router.currentRoute.value.name).toBe('dashboard')
  })

  it('redirects authenticated user from login to dashboard', async () => {
    localStorage.setItem('auth_token', 'fake-token')
    const router = createTestRouter()
    await router.push('/login')
    await router.isReady()
    expect(router.currentRoute.value.name).toBe('dashboard')
  })
})
