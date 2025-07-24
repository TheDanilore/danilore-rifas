<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">
          <i class="fas fa-trophy"></i>
        </div>
        <h1 class="auth-title">Iniciar Sesión</h1>
        <p class="auth-subtitle">Accede a tu cuenta y participa en las rifas</p>
      </div>

      <div class="auth-content">
        <!-- Tabs -->
        <div class="auth-tabs">
          <div class="auth-tab" :class="{ active: currentTab === 'email' }" @click="switchTab('email')">
            <i class="fas fa-envelope"></i>
            Email
          </div>
          <div class="auth-tab" :class="{ active: currentTab === 'phone' }" @click="switchTab('phone')">
            <i class="fas fa-phone"></i>
            Celular
          </div>
        </div>

        <!-- Formulario -->
        <form @submit.prevent="handleLogin">
          <!-- Tab Email -->
          <div class="tab-content" :class="{ active: currentTab === 'email' }">
            <div class="input-group">
              <i class="fas fa-envelope input-icon"></i>
              <input 
                type="email" 
                class="form-input" 
                placeholder="tu@email.com"
                v-model="form.email"
                :required="currentTab === 'email'"
              >
            </div>
          </div>

          <!-- Tab Phone -->
          <div class="tab-content" :class="{ active: currentTab === 'phone' }">
            <div class="input-group">
              <i class="fas fa-phone input-icon"></i>
              <input 
                type="tel" 
                class="form-input" 
                placeholder="999 888 777"
                v-model="form.phone"
                :required="currentTab === 'phone'"
              >
            </div>
          </div>

          <!-- Contraseña -->
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input 
              :type="showPassword ? 'text' : 'password'"
              class="form-input" 
              placeholder="Tu contraseña"
              v-model="form.password"
              required
            >
            <button type="button" class="password-toggle" @click="togglePassword">
              <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
            </button>
          </div>

          <!-- Opciones -->
          <div class="form-options">
            <div class="checkbox-group">
              <input type="checkbox" id="remember" v-model="form.remember">
              <label for="remember">Recordarme</label>
            </div>
            <a href="#forgot-password" class="forgot-link">¿Olvidaste tu contraseña?</a>
          </div>

          <!-- Botón de login -->
          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem;" :disabled="isLoading">
            {{ isLoading ? 'Iniciando...' : 'Iniciar Sesión' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="divider">
          <span>O continúa con</span>
        </div>

        <!-- Botones sociales -->
        <div class="social-buttons">
          <a href="#" class="social-btn" @click.prevent="loginWithGoogle">
            <svg class="google-icon" viewBox="0 0 24 24">
              <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuar con Google
          </a>

          <a href="#" class="social-btn" @click.prevent="loginWithFacebook">
            <i class="fab fa-facebook" style="color: #1877f2;"></i>
            Continuar con Facebook
          </a>
        </div>
      </div>

      <div class="auth-footer">
        <p>¿No tienes cuenta? <router-link to="/register">Regístrate aquí</router-link></p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'Login',
  setup() {
    const router = useRouter()
    const { login } = useAuthStore()
    
    const currentTab = ref('email')
    const showPassword = ref(false)
    const isLoading = ref(false)
    
    const form = reactive({
      email: '',
      phone: '',
      password: '',
      remember: false
    })

    const switchTab = (tab) => {
      currentTab.value = tab
    }

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    const handleLogin = async () => {
      try {
        isLoading.value = true
        
        const loginData = {
          password: form.password,
          remember: form.remember
        }

        if (currentTab.value === 'email') {
          loginData.email = form.email
        } else {
          loginData.phone = form.phone
        }

        await login(loginData)
        router.push('/')
      } catch (error) {
        console.error('Error al iniciar sesión:', error)
      } finally {
        isLoading.value = false
      }
    }

    const loginWithGoogle = () => {
      console.log('Login with Google')
    }

    const loginWithFacebook = () => {
      console.log('Login with Facebook')
    }

    return {
      currentTab,
      showPassword,
      isLoading,
      form,
      switchTab,
      togglePassword,
      handleLogin,
      loginWithGoogle,
      loginWithFacebook
    }
  }
}
</script>


<style scoped>
.auth-container {
  min-height: calc(100vh - 4rem);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.auth-card {
  width: 100%;
  max-width: 400px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-xl);
  overflow: hidden;
}

.auth-header {
  text-align: center;
  padding: 2rem 2rem 1rem;
}

.auth-logo {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
  padding: 1rem;
  border-radius: var(--border-radius-full);
  display: inline-flex;
  margin-bottom: 1rem;
}

.auth-logo i {
  font-size: 2rem;
  color: var(--white);
}

.auth-title {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.auth-subtitle {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.auth-content {
  padding: 0 2rem 2rem;
}

.auth-tabs {
  display: flex;
  background: var(--gray-100);
  border-radius: var(--border-radius);
  padding: 0.25rem;
  margin-bottom: 1.5rem;
}

.auth-tab {
  flex: 1;
  padding: 0.5rem;
  text-align: center;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.auth-tab.active {
  background: var(--white);
  color: var(--primary-purple);
  box-shadow: var(--shadow-sm);
}

.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.input-group {
  position: relative;
  margin-bottom: 1rem;
}

.input-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray-400);
  z-index: 1;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-left: 2.5rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--gray-400);
  cursor: pointer;
  z-index: 1;
}

.password-toggle:hover {
  color: var(--gray-600);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  font-size: 0.875rem;
}

.checkbox-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.checkbox-group input[type="checkbox"] {
  margin: 0;
}

.forgot-link {
  color: var(--primary-purple);
  text-decoration: none;
}

.forgot-link:hover {
  text-decoration: underline;
}

.divider {
  position: relative;
  margin: 1.5rem 0;
  text-align: center;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--gray-300);
}

.divider span {
  background: var(--white);
  padding: 0 1rem;
  color: var(--gray-500);
  font-size: 0.875rem;
}

.social-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.social-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  transition: all 0.3s ease;
}

.social-btn:hover {
  background: var(--gray-50);
  border-color: var(--gray-400);
}

.google-icon {
  width: 1rem;
  height: 1rem;
}

.auth-footer {
  text-align: center;
  padding: 1rem 2rem;
  border-top: 1px solid var(--gray-200);
  font-size: 0.875rem;
}

.auth-footer a {
  color: var(--primary-purple);
  text-decoration: none;
  font-weight: 600;
}

.auth-footer a:hover {
  text-decoration: underline;
}
</style>
