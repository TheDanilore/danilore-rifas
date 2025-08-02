<template>
  <div class="admin-login-page">
    <div class="admin-container">
      <div class="admin-login-card">
        <!-- Header -->
        <div class="login-header">
          <div class="admin-logo">
            <i class="fas fa-crown logo-icon"></i>
            <div class="logo-text">
              <h1 class="logo-title">Panel de Administración</h1>
              <p class="logo-subtitle">Danilore Rifas</p>
            </div>
          </div>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleAdminLogin" class="admin-form">
          <div class="admin-form-group">
            <label for="email" class="admin-label">Email Administrador</label>
            <div class="admin-input-group">
              <i class="fas fa-user-shield input-icon"></i>
              <input
                id="email"
                v-model="adminForm.email"
                type="email"
                class="admin-input"
                placeholder="admin@danilorerifas.com"
                autocomplete="username"
                required
              />
            </div>
          </div>

          <div class="admin-form-group">
            <label for="password" class="admin-label">Contraseña</label>
            <div class="admin-input-group">
              <i class="fas fa-lock input-icon"></i>
              <input
                id="password"
                v-model="adminForm.password"
                :type="showPassword ? 'text' : 'password'"
                class="admin-input"
                placeholder="••••••••"
                autocomplete="current-password"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="password-toggle-btn"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="admin-form-group">
            <div class="admin-checkbox-group">
              <input
                id="remember"
                v-model="adminForm.remember"
                type="checkbox"
                class="admin-checkbox"
              />
              <label for="remember" class="checkbox-label">
                Recordar sesión
              </label>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="admin-alert error">
            <i class="fas fa-exclamation-triangle"></i>
            {{ error }}
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="admin-loading">
            <div class="loading-spinner"></div>
            <span>Verificando credenciales...</span>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="admin-btn primary full-width"
          >
            <i class="fas fa-sign-in-alt" v-if="!loading"></i>
            <span>{{ loading ? 'Ingresando...' : 'Iniciar Sesión' }}</span>
          </button>
        </form>

        <!-- Security Info -->
        <div class="security-info">
          <div class="security-item">
            <i class="fas fa-shield-alt"></i>
            <span>Acceso seguro con SSL</span>
          </div>
          <div class="security-item">
            <i class="fas fa-lock"></i>
            <span>Datos protegidos</span>
          </div>
          <div class="security-item">
            <i class="fas fa-user-check"></i>
            <span>Solo administradores</span>
          </div>
        </div>

        <!-- Footer -->
        <div class="login-footer">
          <p>&copy; 2024 Danilore Rifas. Todos los derechos reservados.</p>
          <div class="footer-links">
            <a href="/privacy" target="_blank">Política de Privacidad</a>
            <a href="/terms" target="_blank">Términos de Uso</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Background Animation -->
    <div class="background-animation">
      <div class="floating-element" v-for="n in 6" :key="n"></div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'AdminLogin',
  setup() {
    const router = useRouter()
    
    const adminForm = ref({
      email: '',
      password: '',
      remember: false
    })
    
    const showPassword = ref(false)
    const loading = ref(false)
    const error = ref('')

    const handleAdminLogin = async () => {
      loading.value = true
      error.value = ''

      try {
        // Simulación de autenticación admin
        // En un proyecto real, aquí harías la llamada a la API
        await new Promise(resolve => setTimeout(resolve, 1000)) // Simular delay
        
        // Credenciales de prueba
        if (adminForm.value.email === 'admin@danilorerifas.com' && 
            adminForm.value.password === 'admin123') {
          
          // Guardar datos de autenticación en localStorage
          const adminUser = {
            id: 1,
            name: 'Admin Usuario',
            email: adminForm.value.email,
            role: 'admin'
          }
          
          const token = 'mock_admin_token_' + Date.now()
          
          localStorage.setItem('admin_token', token)
          localStorage.setItem('admin_user', JSON.stringify(adminUser))
          
          // Redirigir al dashboard
          router.push('/admin/dashboard')
        } else {
          error.value = 'Credenciales inválidas. Usa: admin@danilorerifas.com / admin123'
        }
      } catch (err) {
        console.error('Error en login admin:', err)
        error.value = 'Error de conexión. Inténtalo de nuevo.'
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
/* Usar clases del admin.css global */

.admin-login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--admin-primary-teal) 0%, var(--admin-secondary-emerald) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
  overflow: hidden;
}

.admin-login-card {
  background: white;
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  padding: 3rem;
  width: 100%;
  max-width: 450px;
  position: relative;
  z-index: 10;
}

.login-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.admin-logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.logo-icon {
  font-size: 3rem;
  color: var(--admin-primary-teal);
  text-shadow: 0 4px 12px rgba(20, 184, 166, 0.3);
}

.logo-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--admin-text-dark);
  margin: 0;
}

.logo-subtitle {
  font-size: 1rem;
  color: var(--admin-text-muted);
  margin: 0;
}

.password-toggle-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--admin-text-muted);
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.password-toggle-btn:hover {
  color: var(--admin-primary-teal);
  background-color: var(--admin-bg-light);
}

.security-info {
  display: flex;
  justify-content: space-around;
  margin: 2rem 0;
  padding: 1.5rem;
  background: var(--admin-bg-light);
  border-radius: 12px;
  border: 1px solid var(--admin-border-light);
}

.security-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  text-align: center;
  color: var(--admin-text-muted);
  font-size: 0.85rem;
}

.security-item i {
  color: var(--admin-success);
  font-size: 1.2rem;
}

.login-footer {
  text-align: center;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--admin-border-light);
}

.login-footer p {
  color: var(--admin-text-muted);
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.footer-links {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
}

.footer-links a {
  color: var(--admin-primary-teal);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.3s ease;
}

.footer-links a:hover {
  color: var(--admin-secondary-emerald);
}

.background-animation {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
}

.floating-element {
  position: absolute;
  width: 60px;
  height: 60px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
  animation: float 6s ease-in-out infinite;
}

.floating-element:nth-child(1) {
  top: 10%;
  left: 10%;
  animation-delay: 0s;
}

.floating-element:nth-child(2) {
  top: 20%;
  right: 10%;
  animation-delay: 1s;
}

.floating-element:nth-child(3) {
  bottom: 20%;
  left: 20%;
  animation-delay: 2s;
}

.floating-element:nth-child(4) {
  bottom: 10%;
  right: 20%;
  animation-delay: 3s;
}

.floating-element:nth-child(5) {
  top: 50%;
  left: 5%;
  animation-delay: 4s;
}

.floating-element:nth-child(6) {
  top: 60%;
  right: 5%;
  animation-delay: 5s;
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(180deg);
  }
}

@media (max-width: 768px) {
  .admin-login-page {
    padding: 1rem;
  }
  
  .admin-login-card {
    padding: 2rem;
    border-radius: 16px;
  }
  
  .logo-title {
    font-size: 1.5rem;
  }
  
  .security-info {
    flex-direction: column;
    gap: 1rem;
  }
  
  .security-item {
    flex-direction: row;
    justify-content: center;
  }
  
  .footer-links {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>