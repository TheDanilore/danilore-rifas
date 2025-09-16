<template>
  <div class="auth-container">
    <div class="auth-card auth-card--wide">
      <div class="auth-header">
        <div class="auth-logo">
          <i class="fas fa-trophy"></i>
        </div>
        <h1 class="auth-title">Crear Cuenta</h1>
        <p class="auth-subtitle">Únete a miles de personas que ya participan</p>
      </div>

      <div class="auth-content">
        <form @submit.prevent="handleRegister" class="auth-form">
          <!-- Mensaje de error -->
          <div v-if="errorMessage" class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ errorMessage }}
          </div>

          <!-- Información Personal -->
          <div class="form-section">
            <h3 class="section-title">Información Personal</h3>

            <div class="form-row">
              <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-input" placeholder="Tus nombres" v-model="form.nombres"
                  autocomplete="given-name" required>
              </div>

              <div class="input-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" class="form-input" placeholder="Tus apellidos" v-model="form.apellidos"
                  autocomplete="family-name" required>
              </div>
            </div>

            <div class="form-row">
              <div class="input-group">
                <div class="input-group">
                  <i class="fas fa-calendar input-icon"></i>
                  <input type="date" class="form-input" v-model="form.fechaNacimiento" required>

                </div>
                <p class="form-help">Fecha de nacimiento</p>
              </div>

              <div class="input-group">
                <div class="input-group">
                  <i class="fas fa-venus-mars input-icon"></i>
                  <select class="form-input" v-model="form.genero" required>
                    <option value="">Seleccionar género</option>
                    <option value="masculino">Masculino</option>
                    <option value="femenino">Femenino</option>
                    <option value="otro">Otro</option>
                    <option value="no_especificar">Prefiero no especificar</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Contacto -->
          <div class="form-section">
            <h3 class="section-title">Información de Contacto</h3>

            <div class="form-row">
              <div class="input-group">
                <div class="input-group">
                  <div class="country-selector">
                    <select v-model="form.pais" class="country-select">
                      <option v-for="country in countryList" :key="country.code" :value="country.code">
                        {{ country.flag }} {{ country.phoneCode }}
                      </option>
                    </select>
                  </div>
                  <input type="tel" class="form-input phone-input" placeholder="999 888 777" v-model="form.celular"
                    autocomplete="tel" required>
                </div>
                <p class="form-help">Para notificaciones importantes</p>
              </div>

              <div class="input-group">
                <div class="input-group">
                  <i class="fas fa-envelope input-icon"></i>
                  <input type="email" class="form-input" placeholder="tu@email.com (opcional)" v-model="form.email"
                    autocomplete="email">
                </div>
              </div>
            </div>
          </div>

          <!-- Documento de Identidad -->
          <div class="form-section">
            <h3 class="section-title">Documento de Identidad</h3>

            <div class="form-row">
              <div class="input-group">
                <i class="fas fa-id-card input-icon"></i>
                <select class="form-input" v-model="form.tipoDocumento" required>
                  <option value="dni">DNI</option>
                  <option value="ce">Carné de Extranjería</option>
                  <option value="passport">Pasaporte</option>
                  <option value="ruc">RUC</option>
                  <option value="otros">Otros</option>
                </select>
              </div>

              <div class="input-group">
                <i class="fas fa-hashtag input-icon"></i>
                <input type="text" class="form-input" placeholder="Número de documento" v-model="form.numeroDocumento"
                  required>
              </div>
            </div>
          </div>

          <!-- Dirección -->
          <div class="form-section">
            <h3 class="section-title">Dirección</h3>

            <div class="form-group">
              <div class="input-group">
                <i class="fas fa-map-marker-alt input-icon"></i>
                <input type="text" class="form-input" placeholder="Dirección completa" v-model="form.direccion">
              </div>
            </div>

            <div class="form-row">
              <div class="input-group">
                <i class="fas fa-city input-icon"></i>
                <input type="text" class="form-input" placeholder="Ciudad" v-model="form.ciudad">
              </div>

              <div class="input-group">
                <i class="fas fa-map input-icon"></i>
                <input type="text" class="form-input" placeholder="Departamento/Estado" v-model="form.departamento">
              </div>
            </div>

            <div class="form-row">
              <div class="input-group">
                <i class="fas fa-mail-bulk input-icon"></i>
                <input type="text" class="form-input" placeholder="Código postal" v-model="form.codigoPostal">
              </div>

              <div style="flex: 1;"></div> <!-- Spacer -->
            </div>
          </div>

          <!-- Seguridad -->
          <div class="form-section">
            <h3 class="section-title">Seguridad</h3>

            <div class="form-row">
              <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input :type="showPassword ? 'text' : 'password'" class="form-input" placeholder="Mínimo 8 caracteres"
                  v-model="form.password" autocomplete="new-password" minlength="8" required>
                <button type="button" class="password-toggle" @click="togglePassword">
                  <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
              </div>

              <div class="input-group">
                <i class="fas fa-lock input-icon"></i>
                <input :type="showConfirmPassword ? 'text' : 'password'" class="form-input"
                  placeholder="Repite tu contraseña" v-model="form.confirmPassword" autocomplete="new-password"
                  required>
                <button type="button" class="password-toggle" @click="toggleConfirmPassword">
                  <i class="fas" :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
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
          <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1.5rem;"
            :disabled="isLoading">
            <i v-if="isLoading" class="fas fa-spinner fa-spin"></i>
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
                <path fill="currentColor"
                  d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                <path fill="currentColor"
                  d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                <path fill="currentColor"
                  d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                <path fill="currentColor"
                  d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
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
import { ref, reactive, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { getCountryList, formatPhoneWithCountry, isValidPhone, COUNTRY_CODES } from '@/utils/phoneUtils'

export default {
  name: 'Register',
  setup() {
    const router = useRouter()
    const { login, register } = useAuthStore()

    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const isLoading = ref(false)
    const errorMessage = ref('')

    const countryList = getCountryList()

    const form = reactive({
      nombres: '',
      apellidos: '',
      celular: '',
      email: '',
      password: '',
      confirmPassword: '',
      acceptTerms: false,
      acceptMarketing: false,
      tipoDocumento: 'dni',
      numeroDocumento: '',
      fechaNacimiento: '',
      genero: '',
      direccion: '',
      ciudad: '',
      departamento: '',
      codigoPostal: '',
      pais: 'PE' // País por defecto
    })

    const togglePassword = () => {
      showPassword.value = !showPassword.value
    }

    const toggleConfirmPassword = () => {
      showConfirmPassword.value = !showConfirmPassword.value
    }

    // Computed para el teléfono completo
    const fullPhoneNumber = computed(() => {
      if (form.celular && form.pais) {
        return formatPhoneWithCountry(form.celular, form.pais)
      }
      return form.celular
    })

    // Limpiar error cuando el usuario escriba
    watch([() => form.nombres, () => form.apellidos, () => form.celular, () => form.email, () => form.password, () => form.confirmPassword], () => {
      if (errorMessage.value) {
        errorMessage.value = ''
      }
    })

    const validateForm = () => {
      if (!form.nombres.trim()) {
        return 'Por favor ingresa tus nombres'
      }

      if (!form.apellidos.trim()) {
        return 'Por favor ingresa tus apellidos'
      }

      if (!form.fechaNacimiento) {
        return 'Por favor ingresa tu fecha de nacimiento'
      }

      // Validar edad mínima (18 años)
      const birthDate = new Date(form.fechaNacimiento)
      const today = new Date()
      const age = today.getFullYear() - birthDate.getFullYear()
      const monthDiff = today.getMonth() - birthDate.getMonth()

      if (age < 18 || (age === 18 && monthDiff < 0) || (age === 18 && monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        return 'Debes ser mayor de 18 años para registrarte'
      }

      if (!form.genero) {
        return 'Por favor selecciona tu género'
      }

      if (!form.celular.trim()) {
        return 'Por favor ingresa tu número de celular'
      }

      if (!isValidPhone(form.celular, form.pais)) {
        return 'El número de teléfono no es válido para el país seleccionado'
      }

      if (!form.tipoDocumento) {
        return 'Por favor selecciona el tipo de documento'
      }

      if (!form.numeroDocumento.trim()) {
        return 'Por favor ingresa tu número de documento'
      }

      if (!form.password) {
        return 'Por favor ingresa una contraseña'
      }

      if (form.password.length < 8) {
        return 'La contraseña debe tener al menos 8 caracteres'
      }

      if (form.password !== form.confirmPassword) {
        return 'Las contraseñas no coinciden'
      }

      if (!form.acceptTerms) {
        return 'Debes aceptar los términos y condiciones'
      }

      return null
    }

    const handleRegister = async () => {
      // Limpiar mensaje de error previo
      errorMessage.value = ''

      // Validar formulario
      const validationError = validateForm()
      if (validationError) {
        errorMessage.value = validationError
        return
      }

      try {
        isLoading.value = true

        // Preparar datos para el registro
        const userData = {
          nombres: form.nombres,
          apellidos: form.apellidos,
          telefono: fullPhoneNumber.value, // Usar teléfono con código de país
          email: form.email || null, // Email opcional
          password: form.password,
          tipo_documento: form.tipoDocumento,
          numero_documento: form.numeroDocumento,
          fecha_nacimiento: form.fechaNacimiento,
          genero: form.genero,
          direccion: form.direccion || null,
          ciudad: form.ciudad || null,
          departamento: form.departamento || null,
          codigo_postal: form.codigoPostal || null,
          pais: form.pais
        }

        console.log('Registrando usuario:', userData)
        await register(userData)

        // Después del registro exitoso, hacer login automático
        await login({
          identifier: fullPhoneNumber.value, // Usar teléfono completo para login
          password: userData.password,
          pais: form.pais
        })

        router.push('/')
      } catch (error) {
        console.error('Error al registrar:', error)

        // Mostrar mensajes de error específicos
        if (error.message.includes('email') && error.message.includes('already')) {
          errorMessage.value = 'Este email ya está registrado. Intenta con otro email'
        } else if (error.message.includes('telefono') && error.message.includes('already')) {
          errorMessage.value = 'Este número de teléfono ya está registrado'
        } else if (error.message.includes('numero_documento') && error.message.includes('already')) {
          errorMessage.value = 'Este número de documento ya está registrado'
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
      countryList,
      fullPhoneNumber,
      COUNTRY_CODES,
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
/* Los estilos ahora están centralizados en auth.css */
.checkbox-label {
  font-size: 0.875rem;
  line-height: 1.5;
  color: #d1d5db !important;
  cursor: pointer;
}

.checkbox-label a {
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
}

.checkbox-label a:hover {
  text-decoration: underline;
  color: #4f46e5;
}

/* Estilos específicos para el selector de teléfono */
.country-selector {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 3;
  border-right: 1px solid #d1d5db;
  background: #ffffff;
  border-radius: 12px 0 0 12px;
  display: flex;
  align-items: center;
  width: 75px;
}

.country-select {
  height: 100%;
  border: none;
  background: transparent;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23374151' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.3rem center;
  background-repeat: no-repeat;
  background-size: 1rem 1rem;
  padding: 0 1.2rem 0 0.5rem;
  font-size: 0.9rem;
  outline: none;
  width: 100%;
  color: #111827 !important;
  font-weight: 700;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  transition: background-color 0.2s ease;
  border-radius: 12px 0 0 12px;
}

.country-select:hover {
  background-color: rgba(24, 24, 24, 0.5);
}

.country-select:focus {
  background-color: rgba(243, 244, 246, 0.8);
  outline: none;
}

.phone-input {
  padding-left: 85px !important;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-row {
  gap: 1rem;
}

.form-section {
  margin-bottom: 2rem;
}

.section-title {
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #4b5563;
  color: #e5e7eb !important;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 1rem;
  }

  .form-row>* {
    width: 100%;
  }

  .country-selector {
    width: 70px;
  }

  .country-select {
    min-width: 60px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #111827 !important;
    padding: 0 0.8rem 0 0.25rem;
    background-size: 0.7rem 0.7rem;
    background-position: right 0.15rem center;
  }

  .phone-input {
    padding-left: 80px !important;
  }

  .form-input {
    font-size: 16px !important;
    /* Prevenir zoom en iOS */
    color: #111827 !important;
    font-weight: 600;
  }

  .section-title {
    font-size: 1rem;
    margin-bottom: 1rem;
  }
}
</style>
