<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const router = useRouter()
const { user, logout, updateProfile, deleteAccount } = useAuth()

// Form state
const formData = ref({
  name: '',
  email: '',
  city: '',
  password: '',
  confirmPassword: ''
})

const loading = ref(false)
const saving = ref(false)
const error = ref<string | null>(null)
const success = ref<string | null>(null)

// Delete account modal
const showDeleteModal = ref(false)
const deletingAccount = ref(false)
const deleteError = ref<string | null>(null)

// Initialize form with user data
onMounted(() => {
  if (user.value) {
    formData.value.name = user.value.name || ''
    formData.value.email = user.value.email || ''
    formData.value.city = user.value.city || ''
  }
})

async function handleSubmit() {
  error.value = null
  success.value = null

  // Validate passwords match
  if (formData.value.password && formData.value.password !== formData.value.confirmPassword) {
    error.value = 'As senhas não coincidem'
    return
  }

  saving.value = true

  try {
    const updateData: Record<string, string> = {}
    
    if (formData.value.name !== user.value?.name) {
      updateData.name = formData.value.name
    }
    if (formData.value.email !== user.value?.email) {
      updateData.email = formData.value.email
    }
    if (formData.value.city !== user.value?.city) {
      updateData.city = formData.value.city
    }
    if (formData.value.password) {
      updateData.password = formData.value.password
    }

    if (Object.keys(updateData).length === 0) {
      error.value = 'Nenhuma alteração detectada'
      saving.value = false
      return
    }

    const result = await updateProfile(updateData)
    
    if (result.success) {
      success.value = 'Perfil atualizado com sucesso!'
      formData.value.password = ''
      formData.value.confirmPassword = ''
    } else {
      error.value = result.message || 'Erro ao atualizar perfil'
    }
  } catch (err: any) {
    error.value = err.message || 'Erro ao atualizar perfil'
  } finally {
    saving.value = false
  }
}

async function handleDeleteAccount() {
  deletingAccount.value = true
  deleteError.value = null

  const result = await deleteAccount()

  if (result.success) {
    router.push('/login')
  } else {
    deleteError.value = result.message || 'Erro ao excluir conta'
  }

  deletingAccount.value = false
}

function goBack() {
  router.push('/dashboard')
}
</script>

<template>
  <div class="users-wrapper">
    <!-- Background -->
    <div class="background-decoration">
      <div class="circle circle-1"></div>
      <div class="circle circle-2"></div>
      <div class="circle circle-3"></div>
    </div>

    <!-- Header -->
    <header class="app-header glass-card">
      <div class="header-content">
        <div class="logo">
          <svg class="logo-icon" viewBox="0 0 64 64" fill="none">
            <circle cx="26" cy="26" r="10" fill="currentColor" opacity="0.9"/>
            <path d="M26 8V4M26 48V44M44 26H48M4 26H8M38.5 13.5L41.3 10.7M10.7 41.3L13.5 38.5M38.5 38.5L41.3 41.3M10.7 10.7L13.5 13.5" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            <path d="M44 38C44 33.6 47.6 30 52 30C51.2 26.4 48 24 44 24C43.2 24 42.4 24.1 41.7 24.3C40.1 20.6 36.4 18 32 18C26.5 18 22 22.5 22 28C22 28.3 22 28.7 22.1 29C18.1 29.5 15 32.9 15 37C15 41.4 18.6 45 23 45H44C48.4 45 52 41.4 52 37C52 36.3 51.9 35.6 51.7 35" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="rgba(255,255,255,0.1)"/>
          </svg>
          <span class="logo-text">Previsão climática</span>
        </div>

        <div class="user-menu">
          <span class="user-name">{{ user?.name }}</span>
          <button class="back-btn" @click="goBack" title="Voltar ao Dashboard">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
          </button>
          <button class="logout-btn" @click="logout" title="Sair">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
      <div class="profile-container">
        <section class="profile-card glass-card">
          <div class="profile-header">
            <div class="avatar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div class="profile-title">
              <h1>Meu Perfil</h1>
              <p>Gerencie suas informações pessoais</p>
            </div>
          </div>

          <!-- Success/Error Messages -->
          <transition name="fade">
            <div v-if="success" class="success-message">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
              <span>{{ success }}</span>
            </div>
          </transition>

          <transition name="fade">
            <div v-if="error" class="error-message">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 8v4M12 16h.01"/>
              </svg>
              <span>{{ error }}</span>
            </div>
          </transition>

          <!-- Profile Form -->
          <form @submit.prevent="handleSubmit" class="profile-form">
            <div class="form-group">
              <label for="name">Nome</label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <input
                  id="name"
                  v-model="formData.name"
                  type="text"
                  placeholder="Seu nome completo"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="email">E-mail</label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
                <input
                  id="email"
                  v-model="formData.email"
                  type="email"
                  placeholder="seu@email.com"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="city">Cidade Padrão</label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <input
                  id="city"
                  v-model="formData.city"
                  type="text"
                  placeholder="Ex: São Paulo"
                />
              </div>
              <span class="form-hint">Esta cidade será usada como padrão ao carregar o dashboard</span>
            </div>

            <div class="form-divider">
              <span>Alterar Senha</span>
            </div>

            <div class="form-group">
              <label for="password">Nova Senha</label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                  id="password"
                  v-model="formData.password"
                  type="password"
                  placeholder="Deixe em branco para manter a atual"
                />
              </div>
            </div>

            <div class="form-group">
              <label for="confirmPassword">Confirmar Nova Senha</label>
              <div class="input-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <input
                  id="confirmPassword"
                  v-model="formData.confirmPassword"
                  type="password"
                  placeholder="Confirme a nova senha"
                />
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="saving">
                <span v-if="saving" class="loading-spinner-small"></span>
                <span v-else>Salvar Alterações</span>
              </button>
            </div>
          </form>

          <!-- Danger Zone -->
          <div class="danger-zone">
            <h3>Zona de Perigo</h3>
            <p>Ações irreversíveis para sua conta</p>
            <button class="btn btn-danger" @click="showDeleteModal = true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                <line x1="10" y1="11" x2="10" y2="17"/>
                <line x1="14" y1="11" x2="14" y2="17"/>
              </svg>
              Excluir Minha Conta
            </button>
          </div>
        </section>
      </div>
    </main>

    <!-- Footer -->
    <footer class="app-footer">
      <p>Powered by <a href="https://github.com/felipebufelli" target="_blank" rel="noopener">Felipe Bufelli</a></p>
    </footer>

    <!-- Delete Account Modal -->
    <transition name="modal">
      <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
        <div class="modal glass-card">
          <div class="modal-header">
            <h2>Excluir Conta</h2>
            <button class="close-btn" @click="showDeleteModal = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="warning-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <p class="warning-text">Tem certeza que deseja excluir sua conta?</p>
            <p class="warning-subtext">Esta ação é irreversível e todos os seus dados serão perdidos.</p>

            <transition name="fade">
              <div v-if="deleteError" class="error-message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 8v4M12 16h.01"/>
                </svg>
                <span>{{ deleteError }}</span>
              </div>
            </transition>
          </div>

          <div class="modal-actions">
            <button class="btn btn-secondary" @click="showDeleteModal = false" :disabled="deletingAccount">
              Cancelar
            </button>
            <button class="btn btn-danger" @click="handleDeleteAccount" :disabled="deletingAccount">
              <span v-if="deletingAccount" class="loading-spinner-small"></span>
              <span v-else>Excluir Minha Conta</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.users-wrapper {
  min-height: 100vh;
  position: relative;
}

/* Background */
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

.circle-1 { width: 400px; height: 400px; top: -100px; right: -100px; animation: float 8s ease-in-out infinite; }
.circle-2 { width: 300px; height: 300px; bottom: -50px; left: -50px; animation: float 10s ease-in-out infinite reverse; }
.circle-3 { width: 200px; height: 200px; top: 40%; left: 30%; animation: float 6s ease-in-out infinite; }

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Header */
.app-header {
  position: sticky;
  top: 0;
  z-index: 100;
  border-radius: 0;
  border-bottom: 1px solid var(--glass-border);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--spacing-md) var(--spacing-lg);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.logo-icon { width: 32px; height: 32px; color: var(--primary-light); }
.logo-text { font-size: 1.25rem; font-weight: 700; color: var(--text-light); }

/* User Menu */
.user-menu {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.user-name {
  font-weight: 500;
  color: var(--text-light);
  text-transform: capitalize;
}

.back-btn,
.logout-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  color: var(--text-muted);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.back-btn:hover {
  background: rgba(169, 207, 226, 0.2);
  border-color: var(--primary-light);
  color: var(--primary-light);
}

.logout-btn:hover {
  background: rgba(229, 57, 53, 0.2);
  border-color: var(--error);
  color: var(--error);
}

.back-btn svg,
.logout-btn svg { width: 18px; height: 18px; }

/* Main Content */
.main-content {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--spacing-xl) var(--spacing-lg);
}

.profile-container {
  max-width: 600px;
  margin: 0 auto;
}

/* Profile Card */
.profile-card {
  padding: var(--spacing-xl);
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.profile-header {
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
  margin-bottom: var(--spacing-xl);
  padding-bottom: var(--spacing-lg);
  border-bottom: 1px solid var(--glass-border);
}

.avatar {
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(169, 207, 226, 0.2);
  border-radius: 50%;
  color: var(--primary-light);
}

.avatar svg {
  width: 40px;
  height: 40px;
}

.profile-title h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-light);
  margin-bottom: 4px;
}

.profile-title p {
  color: var(--text-muted);
}

/* Messages */
.success-message,
.error-message {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  border-radius: var(--radius-sm);
  margin-bottom: var(--spacing-lg);
  font-size: 0.875rem;
}

.success-message {
  background: rgba(76, 175, 80, 0.15);
  border: 1px solid rgba(76, 175, 80, 0.3);
  color: #81c784;
}

.error-message {
  background: var(--error-light);
  border: 1px solid rgba(229, 57, 53, 0.3);
  color: #ff6b6b;
}

.success-message svg,
.error-message svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

/* Form */
.profile-form {
  margin-bottom: var(--spacing-xl);
}

.form-group {
  margin-bottom: var(--spacing-lg);
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-light);
  margin-bottom: var(--spacing-xs);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrapper svg {
  position: absolute;
  left: 14px;
  width: 18px;
  height: 18px;
  color: var(--text-muted);
}

.input-wrapper input {
  width: 100%;
  padding: 14px 14px 14px 46px;
  font-size: 1rem;
  font-family: inherit;
  color: var(--text-light);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-sm);
  outline: none;
  transition: all var(--transition-fast);
}

.input-wrapper input::placeholder {
  color: var(--text-muted);
}

.input-wrapper input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--primary-light);
}

.form-hint {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: var(--spacing-xs);
}

.form-divider {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin: var(--spacing-xl) 0;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.form-divider::before,
.form-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--glass-border);
}

.form-actions {
  margin-top: var(--spacing-xl);
}

/* Danger Zone */
.danger-zone {
  padding-top: var(--spacing-lg);
  border-top: 1px solid var(--glass-border);
}

.danger-zone h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--error);
  margin-bottom: var(--spacing-xs);
}

.danger-zone p {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin-bottom: var(--spacing-md);
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  padding: 14px 24px;
  font-size: 1rem;
  font-weight: 600;
  font-family: inherit;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  width: 100%;
  color: var(--primary-dark);
  background: var(--primary-light);
}

.btn-primary:hover:not(:disabled) {
  background: var(--text-light);
  transform: translateY(-1px);
}

.btn-secondary {
  color: var(--text-light);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--glass-border);
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.btn-danger {
  color: var(--text-light);
  background: var(--error);
}

.btn-danger:hover:not(:disabled) {
  background: #ff6b6b;
}

.btn-danger svg {
  width: 18px;
  height: 18px;
}

.loading-spinner-small {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: currentColor;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Footer */
.app-footer {
  position: relative;
  z-index: 1;
  text-align: center;
  padding: var(--spacing-lg);
  color: var(--text-muted);
  font-size: 0.875rem;
}

.app-footer a {
  color: var(--primary-light);
  text-decoration: none;
}

.app-footer a:hover {
  text-decoration: underline;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-md);
}

.modal {
  width: 100%;
  max-width: 420px;
  padding: var(--spacing-lg);
  animation: modalIn 0.3s ease-out;
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-md);
}

.modal-header h2 {
  font-size: 1.25rem;
  color: var(--text-light);
}

.close-btn {
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

.close-btn:hover {
  color: var(--text-light);
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  text-align: center;
  margin-bottom: var(--spacing-lg);
}

.warning-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto var(--spacing-md);
  color: var(--error);
}

.warning-icon svg {
  width: 100%;
  height: 100%;
}

.warning-text {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-light);
  margin-bottom: var(--spacing-xs);
}

.warning-subtext {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.modal-body .error-message {
  margin-top: var(--spacing-md);
  text-align: left;
}

.modal-actions {
  display: flex;
  gap: var(--spacing-sm);
  justify-content: flex-end;
}

/* Transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Responsive */
@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .user-name {
    display: none;
  }
}
</style>
