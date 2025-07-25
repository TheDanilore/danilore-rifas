<template>
  <div class="register-container">
    <div class="register-card">
      <div class="auth-header">
        <div class="auth-logo">
          <i class="fas fa-trophy"></i>
        </div>
        <h1 class="auth-title">Crear Cuenta</h1>
        <p class="auth-subtitle">Únete a miles de personas que ya participan</p>
      </div>

      <div class="register-content">
        <form @submit.prevent="handleRegister">
          <!-- Mensaje de error -->
          <div v-if="errorMessage" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMessage }}
          </div>

          <!-- Información Personal -->
          <div class="form-section">
            <h3 class="section-title">Información Personal</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Nombres *</label>
                <div class="input-group">
                  <i class="fas fa-user input-icon"></i>
                  <input 
                    type="text" 
                    class="form-input" 
                    placeholder="Tus nombres"
                    v-model="form.nombres"
                    required
                  >
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Apellidos *</label>
                <div class="input-group">
                  <i class="fas fa-user input-icon"></i>
                  <input 
                    type="text" 
                    class="form-input" 
                    placeholder="Tus apellidos"
                    v-model="form.apellidos"
                    required
                  >
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Número de Celular *</label>
                <div class="input-group">
                  <i class="fas fa-phone input-icon"></i>
                  <input 
                    type="tel" 
                    class="form-input" 
                    placeholder="999 888 777"
                    v-model="form.celular"
                    required
                  >
                </div>
                <p class="form-help">Se usará para notificaciones importantes</p>
              </div>

              <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <div class="input-group">
                  <i class="fas fa-envelope input-icon"></i>
                  <input 
                    type="email" 
                    class="form-input" 
                    placeholder="tu@email.com (opcional)"
                    v-model="form.email"
                  >
                </div>
              </div>
            </div>
          </div>

          <!-- Seguridad -->
          <div class="form-section">
            <h3 class="section-title">Seguridad</h3>
            
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Contraseña *</label>
                <div class="input-group">
                  <i class="fas fa-lock input-icon"></i>
                  <input 
                    :type="showPassword ? 'text' : 'password'"
                    class="form-input" 
                    placeholder="Mínimo 8 caracteres"
                    v-model="form.password"
                    minlength="8"
                    required
                  >
                  <button type="button" class="password-toggle" @click="togglePassword">
                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Confirmar Contraseña *</label>
                <div class="input-group">
                  <i class="fas fa-lock input-icon"></i>
                  <input 
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="form-input" 
                    placeholder="Repite tu contraseña"
                    v-model="form.confirmPassword"
                    required
                  >
                  <button type="button" class="password-toggle" @click="toggleConfirmPassword">
                    <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Términos y Condiciones -->
          <div class="form-section">
            <div class="checkbox-group">
              <input type="checkbox" id="acceptTerms" v-model="form.acceptTerms" required>
              <label class="checkbox-label" for="acceptTerms">
                Acepto los <a href="#terminos">términos y condiciones</a> y la 
                <a href="#privacidad">política de privacidad</a> *
              </label>
            </div>

            <div class="checkbox-group">
              <input type="checkbox" id="acceptMarketing" v-model="form.acceptMarketing">
              <label class="checkbox-label" for="acceptMarketing">
                Quiero recibir notificaciones sobre nuevas rifas y promociones
              </label>
            </div>
          </div>

          <!-- Botón de registro -->
          <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.75rem; margin-bottom: 1.5rem;" :disabled="isLoading">
            {{ isLoading ? 'Creando cuenta...' : 'Crear Mi Cuenta' }}
          </button>

          <!-- Divider -->
          <div class="divider">
            <span>O regístrate con</span>
          </div>

          <!-- Botones sociales -->
          <div class="social-buttons">
            <a href="#" class="social-btn" @click.prevent="registerWithGoogle">
              <svg class="google-icon" viewBox="0 0 24 24">
                <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
              </svg>
              Continuar con Google
            </a>

            <a href="#" class="social-btn" @click.prevent="registerWithFacebook">
              <i class="fab fa-facebook" style="color: #1877f2;"></i>
              Continuar con Facebook
            </a>
          </div>
        </form>
      </div>

      <div class="auth-footer">
        <p>¿Ya tienes cuenta? <router-link to="/login">Inicia sesión aquí</router-link></p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'Register',
  setup() {
    const router = useRouter()
    const { login, register } = useAuthStore()
    
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const isLoading = ref(false)
    const errorMessage = ref('')
    
    const form = reactive({
      nombres: '',
      apellidos: '',
      celular: '',
      email: '',
      password: '',
      confirmPassword: '',
      acceptTerms: false,
      acceptMarketing: false,
      tipoDocumento: 'dni', // Valor en minúsculas para coincidir con el backend
      numeroDocumento: '',
      fechaNacimiento: '',
      genero: ''
    })

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    const toggleConfirmPassword = () => {
      showConfirmPassword.value = !showConfirmPassword.value
    }

    // Limpiar error cuando el usuario escriba
    watch([() => form.nombres, () => form.apellidos, () => form.celular, () => form.email, () => form.password, () => form.confirmPassword], () => {
      if (errorMessage.value) {
        errorMessage.value = ''
      }
    })

    const handleRegister = async () => {
      // Limpiar mensaje de error previo
      errorMessage.value = ''

      // Validaciones del frontend
      if (!form.nombres.trim()) {
        errorMessage.value = 'Por favor ingresa tus nombres'
        return
      }

      if (!form.apellidos.trim()) {
        errorMessage.value = 'Por favor ingresa tus apellidos'
        return
      }

      if (!form.celular.trim()) {
        errorMessage.value = 'Por favor ingresa tu número de celular'
        return
      }

      if (!form.password) {
        errorMessage.value = 'Por favor ingresa una contraseña'
        return
      }

      if (form.password.length < 8) {
        errorMessage.value = 'La contraseña debe tener al menos 8 caracteres'
        return
      }

      if (form.password !== form.confirmPassword) {
        errorMessage.value = 'Las contraseñas no coinciden'
        return
      }

      if (!form.acceptTerms) {
        errorMessage.value = 'Debes aceptar los términos y condiciones'
        return
      }

      try {
        isLoading.value = true
        
        // Registrar usando el servicio real
        const userData = {
          nombre: `${form.nombres} ${form.apellidos}`,
          email: form.email || form.celular,
          password: form.password,
          password_confirmation: form.confirmPassword,
          telefono: form.celular,
          tipo_documento: (form.tipoDocumento || 'DNI').toLowerCase(), // Convertir a minúsculas
          numero_documento: form.numeroDocumento || form.celular, // Usar el celular como número de documento si no se proporciona
          fecha_nacimiento: form.fechaNacimiento || null,
          genero: form.genero ? form.genero.toLowerCase() : null // También convertir género a minúsculas
        }
        
        console.log('Registrando usuario:', userData)
        await register(userData)
        
        // Después del registro exitoso, hacer login automático
        await login({
          email: userData.email,
          password: userData.password
        })
        
        router.push('/')
      } catch (error) {
        console.error('Error al registrar:', error)
        
        // Mostrar mensajes de error específicos
        if (error.message.includes('email') && error.message.includes('already')) {
          errorMessage.value = 'Este email ya está registrado. Intenta con otro email'
        } else if (error.message.includes('telefono') && error.message.includes('already')) {
          errorMessage.value = 'Este número de teléfono ya está registrado'
        } else if (error.message.includes('The selected tipo documento is invalid')) {
          errorMessage.value = 'Tipo de documento inválido'
        } else if (error.message.includes('validation') || error.message.includes('validación')) {
          errorMessage.value = 'Por favor verifica que todos los datos sean correctos'
        } else if (error.message.includes('conexión') || error.message.includes('Network')) {
          errorMessage.value = 'Error de conexión. Verifica tu internet e intenta nuevamente'
        } else if (error.message.includes('servidor') || error.message.includes('500')) {
          errorMessage.value = 'Error del servidor. Intenta nuevamente más tarde'
        } else {
          errorMessage.value = error.message || 'Error al crear la cuenta. Intenta nuevamente'
        }
      } finally {
        isLoading.value = false
      }
    }

    const registerWithGoogle = () => {
      console.log('Register with Google')
    }

    const registerWithFacebook = () => {
      console.log('Register with Facebook')
    }

    return {
      showPassword,
      showConfirmPassword,
      isLoading,
      errorMessage,
      form,
      togglePassword,
      toggleConfirmPassword,
      handleRegister,
      registerWithGoogle,
      registerWithFacebook
    }
  }
}
</script>

<style scoped>
.register-container {
  min-height: calc(100vh - 4rem);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.register-card {
  width: 100%;
  max-width: 600px;
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

.register-content {
  padding: 2rem;
}

.form-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.error-message {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  border: 1px solid #fca5a5;
  color: #dc2626;
  padding: 0.75rem 1rem;
  border-radius: var(--border-radius);
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  animation: slideDown 0.3s ease-out;
}

.error-message i {
  flex-shrink: 0;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  font-weight: 500;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.input-group {
  position: relative;
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

.form-help {
  font-size: 0.75rem;
  color: var(--gray-500);
  margin-top: 0.25rem;
}

.checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.checkbox-group input[type="checkbox"] {
  margin-top: 0.25rem;
}

.checkbox-label {
  font-size: 0.875rem;
  line-height: 1.4;
}

.checkbox-label a {
  color: var(--primary-purple);
  text-decoration: none;
}

.checkbox-label a:hover {
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

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
