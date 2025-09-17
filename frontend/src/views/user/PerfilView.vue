<template>
  <div class="perfil-page">
    <!-- Hero Section -->
    <section class="hero hero-secondary">
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-user-circle"></i>
          </div>
          <h1 class="hero-title">Mi Perfil</h1>
          <p class="hero-subtitle">Gestiona tu información personal y configuración de cuenta</p>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="perfil-section">
      <div class="container">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando información del perfil...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <button @click="loadUserProfile" class="btn btn-primary">
            <i class="fas fa-refresh"></i>
            Reintentar
          </button>
        </div>

        <!-- Profile Content -->
        <div v-else class="perfil-content">
          <div class="perfil-grid">
            <!-- Profile Info Card -->
            <div class="perfil-card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-user"></i>
                  Información Personal
                </h3>
                <button @click="toggleEdit" class="btn btn-outline">
                  <i class="fas fa-edit"></i>
                  {{ editMode ? 'Cancelar' : 'Editar' }}
                </button>
              </div>
              
              <div class="card-content">
                <form v-if="editMode" @submit.prevent="updateProfile" class="profile-form">
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">Nombres</label>
                      <input 
                        v-model="editForm.nombres" 
                        type="text" 
                        class="form-input"
                        required
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Apellidos</label>
                      <input 
                        v-model="editForm.apellidos" 
                        type="text" 
                        class="form-input"
                        required
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Email</label>
                      <input 
                        v-model="editForm.email" 
                        type="email" 
                        class="form-input"
                        required
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Teléfono</label>
                      <input 
                        v-model="editForm.telefono" 
                        type="tel" 
                        class="form-input"
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Fecha de Nacimiento</label>
                      <input 
                        v-model="editForm.fecha_nacimiento" 
                        type="date" 
                        class="form-input"
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Género</label>
                      <select v-model="editForm.genero" class="form-input">
                        <option value="">Seleccionar</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Dirección</label>
                      <input 
                        v-model="editForm.direccion" 
                        type="text" 
                        class="form-input"
                      >
                    </div>
                    
                    <div class="form-group">
                      <label class="form-label">Ciudad</label>
                      <input 
                        v-model="editForm.ciudad" 
                        type="text" 
                        class="form-input"
                      >
                    </div>
                  </div>
                  
                  <div class="form-actions">
                    <button type="submit" class="btn btn-primary" :disabled="updating">
                      <i class="fas fa-save"></i>
                      {{ updating ? 'Guardando...' : 'Guardar Cambios' }}
                    </button>
                    <button type="button" @click="toggleEdit" class="btn btn-outline">
                      <i class="fas fa-times"></i>
                      Cancelar
                    </button>
                  </div>
                </form>

                <!-- View Mode -->
                <div v-else class="profile-info">
                  <div class="info-grid">
                    <div class="info-item">
                      <span class="info-label">Nombre Completo:</span>
                      <span class="info-value">{{ (user.nombres || user.nombre || '') + ' ' + (user.apellidos || user.apellido || '') }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Email:</span>
                      <span class="info-value">{{ user.email }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Teléfono:</span>
                      <span class="info-value">{{ user.telefono || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Fecha de Nacimiento:</span>
                      <span class="info-value">{{ formatDate(user.fecha_nacimiento) || 'No especificada' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Género:</span>
                      <span class="info-value">{{ formatGender(user.genero) || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Dirección:</span>
                      <span class="info-value">{{ user.direccion || 'No especificada' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Ciudad:</span>
                      <span class="info-value">{{ user.ciudad || 'No especificada' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Departamento:</span>
                      <span class="info-value">{{ user.departamento || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">País:</span>
                      <span class="info-value">{{ user.pais || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Tipo de Documento:</span>
                      <span class="info-value">{{ user.tipo_documento?.toUpperCase() || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Número de Documento:</span>
                      <span class="info-value">{{ user.numero_documento || 'No especificado' }}</span>
                    </div>
                    
                    <div class="info-item">
                      <span class="info-label">Miembro desde:</span>
                      <span class="info-value">{{ formatDate(user.created_at) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stats Card -->
            <div class="stats-card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-chart-bar"></i>
                  Estadísticas
                </h3>
              </div>
              
              <div class="card-content">
                <div class="stats-grid">
                  <div class="stat-item">
                    <div class="stat-icon">
                      <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                      <span class="stat-number">{{ userStats.total_boletos || 0 }}</span>
                      <span class="stat-label">Boletos Comprados</span>
                    </div>
                  </div>
                  
                  <div class="stat-item">
                    <div class="stat-icon">
                      <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                      <span class="stat-number">{{ userStats.rifas_ganadas || 0 }}</span>
                      <span class="stat-label">Rifas Ganadas</span>
                    </div>
                  </div>
                  
                  <div class="stat-item">
                    <div class="stat-icon">
                      <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                      <span class="stat-number">{{ userStats.favoritos || 0 }}</span>
                      <span class="stat-label">Favoritos</span>
                    </div>
                  </div>
                  
                  <div class="stat-item">
                    <div class="stat-icon">
                      <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                      <span class="stat-number">S/ {{ userStats.total_gastado || '0.00' }}</span>
                      <span class="stat-label">Total Invertido</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Security Card -->
            <div class="security-card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-shield-alt"></i>
                  Seguridad
                </h3>
              </div>
              
              <div class="card-content">
                <div class="security-actions">
                  <button @click="showPasswordModal = true" class="btn btn-outline">
                    <i class="fas fa-key"></i>
                    Cambiar Contraseña
                  </button>
                  
                  <button @click="loadTokens" class="btn btn-outline">
                    <i class="fas fa-mobile-alt"></i>
                    Gestionar Dispositivos
                  </button>
                  
                  <button @click="logoutAllDevices" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión en Todos los Dispositivos
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Password Change Modal -->
    <div v-if="showPasswordModal" class="modal-overlay" @click="showPasswordModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Cambiar Contraseña</h3>
          <button @click="showPasswordModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="changePassword" class="password-form">
            <div class="form-group">
              <label class="form-label">Contraseña Actual</label>
              <input 
                v-model="passwordForm.current_password" 
                type="password" 
                class="form-input"
                required
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">Nueva Contraseña</label>
              <input 
                v-model="passwordForm.new_password" 
                type="password" 
                class="form-input"
                required
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">Confirmar Nueva Contraseña</label>
              <input 
                v-model="passwordForm.new_password_confirmation" 
                type="password" 
                class="form-input"
                required
              >
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="changingPassword">
                <i class="fas fa-save"></i>
                {{ changingPassword ? 'Cambiando...' : 'Cambiar Contraseña' }}
              </button>
              <button type="button" @click="showPasswordModal = false" class="btn btn-outline">
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue'
import { useAuthStore } from '@/store/auth'
import { userService } from '@/services/api/userService'
import { formatDate, showNotification, showConfirm } from '@/utils/helpers'

export default {
  name: 'PerfilView',
  setup() {
    const { user: authUser } = useAuthStore()
    
    const loading = ref(true)
    const error = ref(null)
    const editMode = ref(false)
    const updating = ref(false)
    const showPasswordModal = ref(false)
    const changingPassword = ref(false)
    
    const user = ref({})
    const userStats = ref({})
    
    const editForm = reactive({
      nombres: '',
      apellidos: '',
      email: '',
      telefono: '',
      fecha_nacimiento: '',
      genero: '',
      direccion: '',
      ciudad: ''
    })
    
    const passwordForm = reactive({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    })

    const loadUserProfile = async () => {
      try {
        loading.value = true
        error.value = null
        
        const response = await userService.getProfile()
        console.log('Respuesta del perfil:', response) // Debug
        
        // El backend devuelve { user: {...}, stats: {...} }
        if (response.user) {
          user.value = response.user
          userStats.value = response.stats || {}
        } else {
          // Si la respuesta es directamente el usuario
          user.value = response
          userStats.value = {}
        }
        
        // Llenar el formulario de edición con los valores correctos
        editForm.nombres = user.value.nombres || user.value.nombre || ''
        editForm.apellidos = user.value.apellidos || user.value.apellido || ''
        editForm.email = user.value.email || ''
        editForm.telefono = user.value.telefono || ''
        editForm.fecha_nacimiento = user.value.fecha_nacimiento || ''
        editForm.genero = user.value.genero || ''
        editForm.direccion = user.value.direccion || ''
        editForm.ciudad = user.value.ciudad || ''
        
      } catch (err) {
        console.error('Error al cargar perfil:', err)
        error.value = err.message || 'Error al cargar el perfil'
      } finally {
        loading.value = false
      }
    }

    const toggleEdit = () => {
      if (editMode.value) {
        // Restaurar valores originales
        editForm.nombres = user.value.nombres || user.value.nombre || ''
        editForm.apellidos = user.value.apellidos || user.value.apellido || ''
        editForm.email = user.value.email || ''
        editForm.telefono = user.value.telefono || ''
        editForm.fecha_nacimiento = user.value.fecha_nacimiento || ''
        editForm.genero = user.value.genero || ''
        editForm.direccion = user.value.direccion || ''
        editForm.ciudad = user.value.ciudad || ''
      }
      editMode.value = !editMode.value
    }

    const updateProfile = async () => {
      try {
        updating.value = true
        
        const response = await userService.updateProfile(editForm)
        console.log('Respuesta de actualización:', response) // Debug
        
        // El backend devuelve { user: {...} }
        if (response.user) {
          user.value = response.user
        } else {
          user.value = response
        }
        
        showNotification('Perfil actualizado correctamente', 'success')
        editMode.value = false
        
      } catch (err) {
        console.error('Error al actualizar perfil:', err)
        showNotification(
          err.message || 'Error al actualizar el perfil',
          'error'
        )
      } finally {
        updating.value = false
      }
    }

    const changePassword = async () => {
      if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
        showNotification('Las contraseñas no coinciden', 'error')
        return
      }
      
      try {
        changingPassword.value = true
        
        await userService.changePassword(passwordForm)
        
        showNotification('Contraseña cambiada correctamente', 'success')
        showPasswordModal.value = false
        
        // Limpiar formulario
        Object.keys(passwordForm).forEach(key => {
          passwordForm[key] = ''
        })
        
      } catch (err) {
        console.error('Error al cambiar contraseña:', err)
        showNotification(
          err.response?.data?.message || err.message || 'Error al cambiar la contraseña',
          'error'
        )
      } finally {
        changingPassword.value = false
      }
    }

    const loadTokens = async () => {
      try {
        const devices = await userService.getDevices()
        console.log('Dispositivos:', devices)
        // Aquí podrías abrir un modal con la lista de dispositivos
        showNotification('Dispositivos cargados en consola', 'info')
      } catch (err) {
        console.error('Error al cargar dispositivos:', err)
        showNotification('Error al cargar dispositivos', 'error')
      }
    }

    const logoutAllDevices = async () => {
      const confirmed = await showConfirm({
        title: '¿Cerrar todas las sesiones?',
        message: '¿Estás seguro de que deseas cerrar sesión en todos los dispositivos? Tendrás que volver a iniciar sesión.',
        type: 'warning',
        confirmText: 'Cerrar sesiones',
        cancelText: 'Cancelar'
      })
      
      if (!confirmed) {
        return
      }
      
      try {
        await userService.logoutAllDevices()
        showNotification('Sesión cerrada en todos los dispositivos', 'success')
        // Redirigir al login después de unos segundos
        setTimeout(() => {
          window.location.href = '/login'
        }, 2000)
      } catch (err) {
        console.error('Error al cerrar sesiones:', err)
        showNotification('Error al cerrar las sesiones', 'error')
      }
    }

    const formatGender = (gender) => {
      const genders = {
        'masculino': 'Masculino',
        'femenino': 'Femenino',
        'otro': 'Otro'
      }
      return genders[gender] || gender
    }

    onMounted(() => {
      loadUserProfile()
    })

    return {
      loading,
      error,
      editMode,
      updating,
      showPasswordModal,
      changingPassword,
      user,
      userStats,
      editForm,
      passwordForm,
      loadUserProfile,
      toggleEdit,
      updateProfile,
      changePassword,
      loadTokens,
      logoutAllDevices,
      formatDate,
      formatGender
    }
  }
}
</script>

<style scoped>
.perfil-page {
  min-height: 100vh;
  background: var(--gray-50);
}

/* Hero Section */
.hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 4rem 0 2rem;
}

.hero-icons {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.hero-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
}

/* Main Content */
.perfil-section {
  padding: 2rem 0;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 3rem 1rem;
}

.loading-state i,
.error-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.6;
}

.loading-state i {
  color: var(--primary-purple);
}

.error-state i {
  color: var(--error-color);
}

/* Profile Grid */
.perfil-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 768px) {
  .perfil-grid {
    grid-template-columns: 1fr;
  }
}

/* Cards */
.perfil-card,
.stats-card,
.security-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-content {
  padding: 1.5rem;
}

/* Form Styles */
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  font-weight: 500;
  color: var(--gray-700);
  margin-bottom: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  transition: border-color 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(91, 44, 135, 0.1);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.5rem;
  flex-wrap: wrap;
}

/* Profile Info */
.info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--gray-100);
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 500;
  color: var(--gray-600);
  min-width: 150px;
}

.info-value {
  color: var(--gray-800);
  flex: 1;
}

/* Stats */
.stats-grid {
  display: grid;
  gap: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: var(--border-radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.stat-info {
  flex: 1;
}

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gray-800);
  text-align: center;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

/* Security Actions */
.security-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: var(--border-radius-lg);
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--gray-500);
  cursor: pointer;
  padding: 0.25rem;
}

.modal-close:hover {
  color: var(--gray-700);
}

.modal-body {
  padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
  }
  
  .security-actions {
    gap: 0.75rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>