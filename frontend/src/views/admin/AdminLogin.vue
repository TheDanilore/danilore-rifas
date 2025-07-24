<template>
  <div class="admin-login-page">
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="container">
        <div class="admin-login-container">
          <div class="admin-header">
            <div class="admin-logo">
              <i class="fas fa-crown"></i>
              <h1>Panel de Administración</h1>
              <p>Danilore Rifas</p>
            </div>
          </div>

          <form @submit.prevent="handleAdminLogin" class="admin-form">
            <div class="form-group">
              <label for="email">Email Administrador</label>
              <div class="input-group">
                <i class="fas fa-user-shield"></i>
                <input
                  id="email"
                  v-model="adminForm.email"
                  type="email"
                  placeholder="admin@danilorerifas.com"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label for="password">Contraseña</label>
              <div class="input-group password-input">
                <i class="fas fa-lock"></i>
                <input
                  id="password"
                  v-model="adminForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  required
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="password-toggle"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label class="checkbox-label">
                <input
                  v-model="adminForm.rememberMe"
                  type="checkbox"
                  class="checkbox"
                />
                <span class="checkmark"></span>
                Recordar sesión
              </label>
            </div>

            <button type="submit" class="admin-btn" :disabled="loading">
              <i v-if="loading" class="fas fa-spinner fa-spin"></i>
              <i v-else class="fas fa-sign-in-alt"></i>
              {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
            </button>

            <div v-if="error" class="error-message">
              <i class="fas fa-exclamation-triangle"></i>
              {{ error }}
            </div>
          </form>

          <div class="admin-footer">
            <p>
              <i class="fas fa-shield-alt"></i>
              Acceso restringido solo para administradores
            </p>
            <router-link to="/" class="back-link">
              <i class="fas fa-arrow-left"></i>
              Volver al sitio principal
            </router-link>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'AdminLogin',
  setup() {
    const router = useRouter()
    const { adminLogin } = useAuthStore()
    
    const adminForm = ref({
      email: '',
      password: '',
      rememberMe: false
    })
    
    const showPassword = ref(false)
    const loading = ref(false)
    const error = ref('')

    const handleAdminLogin = async () => {
      loading.value = true
      error.value = ''

      try {
        // Validación simple de credenciales de admin
        if (adminForm.value.email === 'admin@danilorerifas.com' && 
            adminForm.value.password === 'DaniloreAdmin2024!') {
          
          await adminLogin({
            nombre: 'Administrador',
            email: adminForm.value.email
          })
          
          router.push('/admin/dashboard')
        } else {
          error.value = 'Credenciales de administrador incorrectas'
        }
      } catch (err) {
        error.value = 'Error al iniciar sesión. Inténtalo de nuevo.'
      } finally {
        loading.value = false
      }
    }

    return {
      adminForm,
      showPassword,
      loading,
      error,
      handleAdminLogin
    }
  }
}
</script>

<style scoped>
.admin-login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--primary-indigo) 0%, var(--primary-purple) 50%, var(--gray-900) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 0;
}

.admin-hero {
  width: 100%;
}

.admin-login-container {
  max-width: 480px;
  margin: 0 auto;
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-xl);
  overflow: hidden;
}

.admin-header {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 2rem 1.5rem;
  text-align: center;
}

.admin-logo i {
  font-size: 2rem;
  margin-bottom: 0.75rem;
  color: var(--accent-yellow);
}

.admin-logo h1 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.375rem;
}

.admin-logo p {
  font-size: 0.9rem;
  opacity: 0.9;
}

.admin-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.375rem;
  font-weight: 500;
  color: var(--gray-700);
  font-size: 0.875rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 1rem;
  color: var(--gray-400);
  z-index: 2;
}

.input-group input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 1px solid var(--gray-300);
  border-radius: 6px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
}

.password-input input {
  padding-right: 2.75rem;
}

.input-group input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 2px rgba(91, 44, 135, 0.1);
}

.password-toggle {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  color: var(--gray-400);
  cursor: pointer;
  padding: 0.375rem;
  z-index: 2;
  border-radius: 4px;
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.password-toggle:hover {
  color: var(--primary-purple);
  background: rgba(91, 44, 135, 0.1);
}

.password-toggle:active {
  transform: scale(0.95);
}

.checkbox-label {
  display: flex;
  align-items: center;
  font-weight: normal;
  cursor: pointer;
}

.checkbox {
  margin-right: 0.5rem;
}

.admin-btn {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.admin-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.admin-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  margin-top: 1rem;
  padding: 0.75rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid var(--danger-red);
  border-radius: var(--border-radius);
  color: var(--danger-red);
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.admin-footer {
  padding: 1.5rem;
  background: var(--gray-50);
  text-align: center;
  border-top: 1px solid var(--gray-200);
}

.admin-footer p {
  font-size: 0.8rem;
  color: var(--gray-600);
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.back-link {
  color: var(--primary-purple);
  text-decoration: none;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: color 0.3s ease;
}

.back-link:hover {
  color: var(--primary-indigo);
}

@media (max-width: 640px) {
  .admin-login-container {
    margin: 1rem;
    border-radius: var(--border-radius);
  }
  
  .admin-header {
    padding: 2rem 1rem;
  }
  
  .admin-form {
    padding: 1.5rem;
  }
}
</style>
