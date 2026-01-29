<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { login, register } = useAuth()

// Mode: 'login' or 'register'
const mode = ref<'login' | 'register'>('login')

// Form fields
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const city = ref('')

const loading = ref(false)
const error = ref<string | null>(null)
const showPassword = ref(false)

const isLoginMode = computed(() => mode.value === 'login')

const isFormValid = computed(() => {
  if (isLoginMode.value) {
    return email.value.trim() !== '' && password.value.length >= 6
  }
  return (
    name.value.trim().length >= 2 &&
    email.value.trim() !== '' &&
    password.value.length >= 6 &&
    password.value === confirmPassword.value &&
    city.value.trim() !== ''
  )
})

function toggleMode() {
  mode.value = isLoginMode.value ? 'register' : 'login'
  error.value = null
  // Clear fields when switching
  if (mode.value === 'login') {
    name.value = ''
    confirmPassword.value = ''
    city.value = ''
  }
}

async function handleSubmit() {
  if (!isFormValid.value) return

  loading.value = true
  error.value = null

  let result

  if (isLoginMode.value) {
    result = await login(email.value, password.value)
  } else {
    // Validate password confirmation
    if (password.value !== confirmPassword.value) {
      error.value = 'As senhas não coincidem'
      loading.value = false
      return
    }
    result = await register(name.value, email.value, password.value, city.value)
  }

  if (result.success) {
    router.push('/dashboard')
  } else {
    error.value = result.message || 'Erro ao processar requisição'
  }

  loading.value = false
}
</script>

<template>
  <div class="login-wrapper">
    <!-- Background Decoration -->
    <div class="background-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
      <div class="circle circle-3"></div>
    </div>

    <main class="login-content">
      <!-- Logo Section -->
      <div class="logo-section">
        <div class="logo">
          <svg class="logo-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="26" cy="26" r="10" fill="currentColor" opacity="0.9" />
            <path
              d="M26 8V4M26 48V44M44 26H48M4 26H8M38.5 13.5L41.3 10.7M10.7 41.3L13.5 38.5M38.5 38.5L41.3 41.3M10.7 10.7L13.5 13.5"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
            />
            <path
              d="M44 38C44 33.6 47.6 30 52 30C51.2 26.4 48 24 44 24C43.2 24 42.4 24.1 41.7 24.3C40.1 20.6 36.4 18 32 18C26.5 18 22 22.5 22 28C22 28.3 22 28.7 22.1 29C18.1 29.5 15 32.9 15 37C15 41.4 18.6 45 23 45H44C48.4 45 52 41.4 52 37C52 36.3 51.9 35.6 51.7 35"
              stroke="currentColor"
              stroke-width="3"
              stroke-linecap="round"
              fill="rgba(255,255,255,0.1)"
            />
          </svg>
          <span class="logo-text">InEvent</span>
        </div>
        <p class="logo-tagline">Todas as informações meteorológicas em um só lugar</p>
      </div>

      <!-- Login/Register Card -->
      <div class="login-card glass-card">
        <div class="card-header">
          <h1>{{ isLoginMode ? 'Bem-vindo' : 'Criar Conta' }}</h1>
          <p>{{ isLoginMode ? 'Faça login para acessar' : 'Preencha os dados para se cadastrar' }}</p>
        </div>

        <form @submit.prevent="handleSubmit" class="login-form">
          <!-- Name Field (Register only) -->
          <transition name="slide">
            <div v-if="!isLoginMode" class="form-group">
              <label for="name">Nome</label>
              <div class="input-wrapper">
                <svg
                  class="input-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                  <circle cx="12" cy="7" r="4" />
                </svg>
                <input
                  id="name"
                  v-model="name"
                  type="text"
                  placeholder="Seu nome completo"
                  autocomplete="name"
                  :disabled="loading"
                  required
                />
              </div>
            </div>
          </transition>

          <!-- Email Field -->
          <div class="form-group">
            <label for="email">E-mail</label>
            <div class="input-wrapper">
              <svg
                class="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <rect x="2" y="4" width="20" height="16" rx="2" />
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
              </svg>
              <input
                id="email"
                v-model="email"
                type="email"
                placeholder="seu@email.com"
                autocomplete="email"
                :disabled="loading"
                required
              />
            </div>
          </div>

          <!-- City Field (Register only) -->
          <transition name="slide">
            <div v-if="!isLoginMode" class="form-group">
              <label for="city">Cidade</label>
              <div class="input-wrapper">
                <svg
                  class="input-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                <input
                  id="city"
                  v-model="city"
                  type="text"
                  placeholder="Sua cidade"
                  autocomplete="address-level2"
                  :disabled="loading"
                  required
                />
              </div>
            </div>
          </transition>

          <!-- Password Field -->
          <div class="form-group">
            <label for="password">Senha</label>
            <div class="input-wrapper">
              <svg
                class="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Mínimo 6 caracteres"
                autocomplete="current-password"
                :disabled="loading"
                required
              />
              <button
                type="button"
                class="toggle-password"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
              >
                <svg
                  v-if="!showPassword"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
                <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path
                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                  />
                  <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Confirm Password Field (Register only) -->
          <transition name="slide">
            <div v-if="!isLoginMode" class="form-group">
              <label for="confirmPassword">Confirmar Senha</label>
              <div class="input-wrapper">
                <svg
                  class="input-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
                <input
                  id="confirmPassword"
                  v-model="confirmPassword"
                  type="password"
                  placeholder="Confirme sua senha"
                  autocomplete="new-password"
                  :disabled="loading"
                  required
                />
              </div>
              <span
                v-if="confirmPassword && password !== confirmPassword"
                class="field-error"
              >
                As senhas não coincidem
              </span>
            </div>
          </transition>

          <!-- Error Message -->
          <transition name="fade">
            <div v-if="error" class="error-message">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 8v4M12 16h.01" />
              </svg>
              <span>{{ error }}</span>
            </div>
          </transition>

          <!-- Submit Button -->
          <button
            type="submit"
            class="submit-btn"
            :disabled="!isFormValid || loading"
            :class="{ loading }"
          >
            <span v-if="!loading">{{ isLoginMode ? 'Entrar' : 'Cadastrar' }}</span>
            <span v-else class="loading-spinner"></span>
          </button>
        </form>

        <div class="card-footer">
          <p v-if="isLoginMode">
            Não tem uma conta?
            <button type="button" class="link-btn" @click="toggleMode">Cadastre-se</button>
          </p>
          <p v-else>
            Já tem uma conta?
            <button type="button" class="link-btn" @click="toggleMode">Faça login</button>
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.login-wrapper {
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

/* Background Decoration */
.background-decoration {
  position: fixed;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(169, 207, 226, 0.1);
  filter: blur(60px);
}

.circle-1 {
  width: 400px;
  height: 400px;
  top: -100px;
  right: -100px;
  animation: float 8s ease-in-out infinite;
}

.circle-2 {
  width: 300px;
  height: 300px;
  bottom: -50px;
  left: -50px;
  animation: float 10s ease-in-out infinite reverse;
}

.circle-3 {
  width: 200px;
  height: 200px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

/* Main Content */
.login-content {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-lg) var(--spacing-md);
  gap: var(--spacing-lg);
}

/* Logo Section */
.logo-section {
  text-align: center;
  animation: fadeIn 0.6s ease-out;
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-xs);
}

.logo-icon {
  width: 48px;
  height: 48px;
  color: var(--primary-light);
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
}

.logo-text {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-light);
  letter-spacing: -0.02em;
}

.logo-tagline {
  color: var(--text-muted);
  font-size: 1rem;
}

/* Login Card */
.login-card {
  width: 100%;
  max-width: 420px;
  padding: var(--spacing-xl);
  animation: fadeIn 0.6s ease-out 0.1s both;
}

.card-header {
  text-align: center;
  margin-bottom: var(--spacing-lg);
}

.card-header h1 {
  font-size: 1.75rem;
  color: var(--text-light);
  margin-bottom: var(--spacing-xs);
}

.card-header p {
  color: var(--text-muted);
  font-size: 0.9375rem;
}

/* Form */
.login-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-light);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 16px;
  width: 20px;
  height: 20px;
  color: var(--text-muted);
  pointer-events: none;
}

.input-wrapper input {
  width: 100%;
  padding: 14px 48px;
  font-size: 1rem;
  font-family: inherit;
  color: var(--text-light);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-md);
  outline: none;
  transition: all var(--transition-fast);
}

.input-wrapper input::placeholder {
  color: var(--text-muted);
}

.input-wrapper input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--primary-light);
  box-shadow: 0 0 0 3px rgba(169, 207, 226, 0.2);
}

.input-wrapper input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.toggle-password {
  position: absolute;
  right: 12px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  transition: color var(--transition-fast);
}

.toggle-password:hover {
  color: var(--text-light);
}

.toggle-password svg {
  width: 20px;
  height: 20px;
}

.field-error {
  font-size: 0.75rem;
  color: #ff6b6b;
  margin-top: 4px;
}

/* Error Message */
.error-message {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm) var(--spacing-md);
  background: var(--error-light);
  border: 1px solid rgba(229, 57, 53, 0.3);
  border-radius: var(--radius-sm);
  color: #ff6b6b;
  font-size: 0.875rem;
}

.error-message svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

/* Submit Button */
.submit-btn {
  margin-top: var(--spacing-sm);
  padding: 16px;
  font-size: 1rem;
  font-weight: 600;
  font-family: inherit;
  color: var(--primary-dark);
  background: var(--primary-light);
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  justify-content: center;
}

.submit-btn:hover:not(:disabled) {
  background: var(--text-light);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(169, 207, 226, 0.4);
}

.submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid transparent;
  border-top-color: var(--primary-dark);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Card Footer */
.card-footer {
  margin-top: var(--spacing-lg);
  text-align: center;
  padding-top: var(--spacing-md);
  border-top: 1px solid var(--glass-border);
}

.card-footer p {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.link-btn {
  background: none;
  border: none;
  color: var(--primary-light);
  font-weight: 600;
  cursor: pointer;
  font-size: inherit;
  font-family: inherit;
  transition: color var(--transition-fast);
}

.link-btn:hover {
  color: var(--text-light);
  text-decoration: underline;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 480px) {
  .login-card {
    padding: var(--spacing-lg);
  }

  .logo-text {
    font-size: 1.75rem;
  }
}
</style>
