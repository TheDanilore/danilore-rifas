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
          <!-- Mensaje de error -->
          <div v-if="errorMessage" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMessage }}
          </div>

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
                autocomplete="email"
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
                autocomplete="tel"
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
              autocomplete="current-password"
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
import { ref, reactive, watch } from 'vue'
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
    const errorMessage = ref('')
    
    const form = reactive({
      email: '',
      phone: '',
      password: '',
      remember: false
    })

    const switchTab = (tab) => {
      currentTab.value = tab
      errorMessage.value = '' // Limpiar error al cambiar tab
    }

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    // Limpiar error cuando el usuario escriba
    watch([() => form.email, () => form.phone, () => form.password], () => {
      if (errorMessage.value) {
        errorMessage.value = ''
      }
    })

    const handleLogin = async () => {
      // Limpiar mensaje de error previo
      errorMessage.value = ''
      
      try {
        isLoading.value = true
        
        const loginData = {
          password: form.password,
          remember: form.remember
        }

        if (currentTab.value === 'email') {
          if (!form.email) {
            errorMessage.value = 'Por favor ingresa tu email'
            return
          }
          loginData.email = form.email
        } else {
          if (!form.phone) {
            errorMessage.value = 'Por favor ingresa tu número de celular'
            return
          }
          loginData.email = form.phone // El backend espera email, pero puede ser teléfono
        }

        if (!form.password) {
          errorMessage.value = 'Por favor ingresa tu contraseña'
          return
        }

        await login(loginData)
        router.push('/')
      } catch (error) {
        console.error('Error al iniciar sesión:', error)
        
        // Mostrar mensajes de error específicos
        if (error.message.includes('Credenciales incorrectas')) {
          errorMessage.value = 'Email o contraseña incorrectos'
        } else if (error.message.includes('Usuario desactivado')) {
          errorMessage.value = 'Tu cuenta está desactivada. Contacta al administrador'
        } else if (error.message.includes('conexión') || error.message.includes('Network')) {
          errorMessage.value = 'Error de conexión. Verifica tu internet e intenta nuevamente'
        } else if (error.message.includes('servidor') || error.message.includes('500')) {
          errorMessage.value = 'Error del servidor. Intenta nuevamente más tarde'
        } else {
          errorMessage.value = error.message || 'Error al iniciar sesión. Intenta nuevamente'
        }
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
      errorMessage,
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
/* Estilos específicos de LoginView que no están en auth.css */
.tab-content {
  display: none;
}

.tab-content.active {
  display: block;
}

.password-toggle {
  position: absolute;
  right: var(--spacing-3);
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--gray-400);
  cursor: pointer;
  z-index: 1;
  padding: var(--spacing-1);
  border-radius: var(--border-radius-sm);
  transition: var(--transition-fast);
}

.password-toggle:hover {
  color: var(--gray-600);
  background: var(--gray-100);
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-6);
  font-size: var(--font-size-sm);
}

.checkbox-group {
  display: flex;
  align-items: center;
  gap: var(--spacing-2);
}

.checkbox-group input[type="checkbox"] {
  margin: 0;
}

.forgot-link {
  color: var(--primary-color);
  text-decoration: none;
  transition: var(--transition-fast);
}

.forgot-link:hover {
  text-decoration: underline;
  color: var(--primary-dark);
}
</style>
