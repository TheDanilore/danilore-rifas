<template>
  <div class="mis-rifas-page">
    <!-- Hero Section -->
    <section class="hero hero-secondary">
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-ticket-alt"></i>
          </div>
          <h1 class="hero-title">Mis Rifas</h1>
          <p class="hero-subtitle">Gestiona tus boletos comprados y participa en los sorteos</p>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="rifas-section">
      <div class="container">
        <!-- Filter Bar -->
        <div class="filter-bar">
          <div class="filter-group">
            <label>Estado:</label>
            <select v-model="filters.estado" @change="loadBoletos" class="filter-select">
              <option value="">Todos</option>
              <option value="activo">Activos</option>
              <option value="ganador">Ganadores</option>
              <option value="finalizado">Finalizados</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Ordenar por:</label>
            <select v-model="filters.sort" @change="loadBoletos" class="filter-select">
              <option value="fecha_desc">Más recientes</option>
              <option value="fecha_asc">Más antiguos</option>
              <option value="rifa_nombre">Nombre de rifa</option>
            </select>
          </div>
          
          <div class="filter-actions">
            <button @click="loadBoletos" class="btn btn-outline">
              <i class="fas fa-refresh"></i>
              Actualizar
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando tus rifas...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <button @click="loadBoletos" class="btn btn-primary">
            <i class="fas fa-refresh"></i>
            Reintentar
          </button>
        </div>

        <!-- Empty State -->
        <div v-else-if="boletos.length === 0" class="empty-state">
          <i class="fas fa-ticket-alt"></i>
          <h3>No tienes rifas aún</h3>
          <p>¡Compra tu primer boleto y participa en nuestras increíbles rifas!</p>
          <router-link to="/" class="btn btn-primary">
            <i class="fas fa-shopping-cart"></i>
            Ver Rifas Disponibles
          </router-link>
        </div>

        <!-- Boletos Grid -->
        <div v-else class="boletos-grid">
          <div 
            v-for="boleto in boletos" 
            :key="boleto.id" 
            class="boleto-card"
            :class="getBoletoClass(boleto)"
          >
            <div class="boleto-header">
              <div class="boleto-info">
                <h3 class="boleto-title">{{ boleto.rifa?.nombre || 'Rifa' }}</h3>
                <div class="boleto-meta">
                  <span class="boleto-numero">
                    <i class="fas fa-hashtag"></i>
                    {{ boleto.numero }}
                  </span>
                  <span class="boleto-estado" :class="getEstadoClass(boleto.estado)">
                    {{ getEstadoTexto(boleto.estado) }}
                  </span>
                </div>
              </div>
              
              <div class="boleto-actions">
                <button @click="verDetalle(boleto)" class="btn btn-outline btn-sm">
                  <i class="fas fa-eye"></i>
                  Ver
                </button>
                
                <div class="dropdown" v-if="boleto.estado === 'activo'">
                  <button @click="toggleDropdown(boleto.id)" class="btn btn-outline btn-sm">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <div v-if="dropdownOpen === boleto.id" class="dropdown-menu">
                    <button @click="transferirBoleto(boleto)" class="dropdown-item">
                      <i class="fas fa-exchange-alt"></i>
                      Transferir
                    </button>
                    <button @click="verHistorial(boleto)" class="dropdown-item">
                      <i class="fas fa-history"></i>
                      Historial
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="boleto-content">
              <div class="rifa-image">
                <img 
                  :src="boleto.rifa?.imagen || '/images/default-rifa.jpg'" 
                  :alt="boleto.rifa?.nombre"
                  @error="handleImageError"
                >
              </div>
              
              <div class="boleto-details">
                <div class="detail-row">
                  <span class="detail-label">Precio:</span>
                  <span class="detail-value">S/ {{ boleto.precio }}</span>
                </div>
                
                <div class="detail-row">
                  <span class="detail-label">Fecha de compra:</span>
                  <span class="detail-value">{{ formatDate(boleto.created_at) }}</span>
                </div>
                
                <div class="detail-row" v-if="boleto.rifa?.fecha_sorteo">
                  <span class="detail-label">Fecha de sorteo:</span>
                  <span class="detail-value">{{ formatDate(boleto.rifa.fecha_sorteo) }}</span>
                </div>
                
                <div class="detail-row" v-if="boleto.estado === 'ganador'">
                  <span class="detail-label">Premio:</span>
                  <span class="detail-value premio">{{ boleto.rifa?.premio?.nombre || 'Premio principal' }}</span>
                </div>
                
                <div class="progress-bar" v-if="boleto.rifa">
                  <div class="progress-info">
                    <span>Boletos vendidos</span>
                    <span>{{ boleto.rifa.boletos_vendidos || 0 }} / {{ boleto.rifa.boletos_totales || 0 }}</span>
                  </div>
                  <div class="progress">
                    <div 
                      class="progress-fill" 
                      :style="{ width: getProgressPercentage(boleto.rifa) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Ganador Badge -->
            <div v-if="boleto.estado === 'ganador'" class="ganador-badge">
              <i class="fas fa-crown"></i>
              ¡GANADOR!
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="pagination">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="btn btn-outline"
          >
            <i class="fas fa-chevron-left"></i>
            Anterior
          </button>
          
          <span class="pagination-info">
            Página {{ pagination.current_page }} de {{ Math.ceil(pagination.total / pagination.per_page) }}
          </span>
          
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= Math.ceil(pagination.total / pagination.per_page)"
            class="btn btn-outline"
          >
            Siguiente
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- Modals -->
    <!-- Transfer Modal -->
    <div v-if="showTransferModal" class="modal-overlay" @click="showTransferModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Transferir Boleto</h3>
          <button @click="showTransferModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <p>Boleto #{{ selectedBoleto?.numero }} - {{ selectedBoleto?.rifa?.nombre }}</p>
          
          <form @submit.prevent="confirmarTransferencia" class="transfer-form">
            <div class="form-group">
              <label class="form-label">Email del destinatario:</label>
              <input 
                v-model="transferForm.email" 
                type="email" 
                class="form-input"
                placeholder="ejemplo@correo.com"
                required
              >
            </div>
            
            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="transferring">
                <i class="fas fa-exchange-alt"></i>
                {{ transferring ? 'Transfiriendo...' : 'Transferir' }}
              </button>
              <button type="button" @click="showTransferModal = false" class="btn btn-outline">
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal" class="modal-overlay" @click="showDetailModal = false">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h3>Detalle del Boleto</h3>
          <button @click="showDetailModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div v-if="selectedBoleto" class="boleto-detail">
            <div class="detail-grid">
              <div class="detail-section">
                <h4>Información del Boleto</h4>
                <div class="detail-item">
                  <span class="label">Número:</span>
                  <span class="value">#{{ selectedBoleto.numero }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Estado:</span>
                  <span class="value" :class="getEstadoClass(selectedBoleto.estado)">
                    {{ getEstadoTexto(selectedBoleto.estado) }}
                  </span>
                </div>
                <div class="detail-item">
                  <span class="label">Precio:</span>
                  <span class="value">S/ {{ selectedBoleto.precio }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Fecha de compra:</span>
                  <span class="value">{{ formatDate(selectedBoleto.created_at) }}</span>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Información de la Rifa</h4>
                <div class="detail-item">
                  <span class="label">Nombre:</span>
                  <span class="value">{{ selectedBoleto.rifa?.nombre }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Descripción:</span>
                  <span class="value">{{ selectedBoleto.rifa?.descripcion }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Fecha de sorteo:</span>
                  <span class="value">{{ formatDate(selectedBoleto.rifa?.fecha_sorteo) }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Premio:</span>
                  <span class="value">{{ selectedBoleto.rifa?.premio?.nombre }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, reactive } from 'vue'
import { userService } from '@/services/api/userService'
import { formatDate, showNotification } from '@/utils/helpers'

export default {
  name: 'MisRifasView',
  setup() {
    const loading = ref(true)
    const error = ref(null)
    const boletos = ref([])
    const pagination = ref({})
    const dropdownOpen = ref(null)
    const showTransferModal = ref(false)
    const showDetailModal = ref(false)
    const selectedBoleto = ref(null)
    const transferring = ref(false)
    
    const filters = reactive({
      estado: '',
      sort: 'fecha_desc',
      page: 1
    })
    
    const transferForm = reactive({
      email: ''
    })

    const loadBoletos = async () => {
      try {
        loading.value = true
        error.value = null
        
        const response = await userService.getBoletos(filters)
        
        // Validar que la respuesta tenga la estructura esperada
        if (response && response.data) {
          boletos.value = response.data.boletos || []
          pagination.value = response.data.pagination || {}
        } else {
          console.error('Respuesta de API inválida:', response)
          boletos.value = []
          pagination.value = {}
          showNotification('Error: Respuesta de servidor inválida', 'error')
        }
        
      } catch (err) {
        console.error('Error al cargar boletos:', err)
        error.value = err.response?.data?.message || 'Error al cargar los boletos'
        showNotification(error.value, 'error')
        boletos.value = []
        pagination.value = {}
      } finally {
        loading.value = false
      }
    }

    const getBoletoClass = (boleto) => {
      return {
        'boleto-ganador': boleto.estado === 'ganador',
        'boleto-finalizado': boleto.estado === 'finalizado'
      }
    }

    const getEstadoClass = (estado) => {
      return {
        'estado-activo': estado === 'activo',
        'estado-ganador': estado === 'ganador',
        'estado-finalizado': estado === 'finalizado'
      }
    }

    const getEstadoTexto = (estado) => {
      const estados = {
        'activo': 'Activo',
        'ganador': 'Ganador',
        'finalizado': 'Finalizado',
        'cancelado': 'Cancelado'
      }
      return estados[estado] || estado
    }

    const getProgressPercentage = (rifa) => {
      if (!rifa.boletos_totales) return 0
      return Math.min((rifa.boletos_vendidos / rifa.boletos_totales) * 100, 100)
    }

    const toggleDropdown = (boletoId) => {
      dropdownOpen.value = dropdownOpen.value === boletoId ? null : boletoId
    }

    const verDetalle = (boleto) => {
      selectedBoleto.value = boleto
      showDetailModal.value = true
      dropdownOpen.value = null
    }

    const transferirBoleto = (boleto) => {
      selectedBoleto.value = boleto
      transferForm.email = ''
      showTransferModal.value = true
      dropdownOpen.value = null
    }

    const confirmarTransferencia = async () => {
      try {
        transferring.value = true
        
        await userService.transferirBoleto(selectedBoleto.value.id, transferForm.email)
        
        showNotification('Boleto transferido correctamente', 'success')
        showTransferModal.value = false
        await loadBoletos()
        
      } catch (err) {
        console.error('Error al transferir boleto:', err)
        showNotification(
          err.response?.data?.message || 'Error al transferir el boleto',
          'error'
        )
      } finally {
        transferring.value = false
      }
    }

    const verHistorial = async (boleto) => {
      try {
        const response = await userService.getHistorialTransferencias(boleto.id)
        // Mostrar historial en modal o nueva vista
        showNotification('Función en desarrollo', 'info')
      } catch (err) {
        console.error('Error al cargar historial:', err)
        showNotification('Error al cargar el historial', 'error')
      }
      dropdownOpen.value = null
    }

    const changePage = (page) => {
      filters.page = page
      loadBoletos()
    }

    const handleImageError = (event) => {
      event.target.src = '/images/default-rifa.jpg'
    }

    // Close dropdown when clicking outside
    const handleClickOutside = (event) => {
      if (!event.target.closest('.dropdown')) {
        dropdownOpen.value = null
      }
    }

    onMounted(() => {
      loadBoletos()
      document.addEventListener('click', handleClickOutside)
    })

    return {
      loading,
      error,
      boletos,
      pagination,
      filters,
      dropdownOpen,
      showTransferModal,
      showDetailModal,
      selectedBoleto,
      transferring,
      transferForm,
      loadBoletos,
      getBoletoClass,
      getEstadoClass,
      getEstadoTexto,
      getProgressPercentage,
      toggleDropdown,
      verDetalle,
      transferirBoleto,
      confirmarTransferencia,
      verHistorial,
      changePage,
      handleImageError,
      formatDate
    }
  }
}
</script>

<style scoped>
.mis-rifas-page {
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
.rifas-section {
  padding: 2rem 0;
}

/* Filter Bar */
.filter-bar {
  background: white;
  padding: 1.5rem;
  border-radius: var(--border-radius-lg);
  margin-bottom: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  flex-wrap: wrap;
  box-shadow: var(--shadow-sm);
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group label {
  font-weight: 500;
  color: var(--gray-700);
  white-space: nowrap;
}

.filter-select {
  padding: 0.5rem 1rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  background: white;
}

.filter-actions {
  margin-left: auto;
}

/* States */
.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 3rem 1rem;
}

.loading-state i,
.error-state i,
.empty-state i {
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

.empty-state i {
  color: var(--gray-400);
}

.empty-state h3 {
  font-size: 1.5rem;
  color: var(--gray-600);
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: var(--gray-500);
  margin-bottom: 1.5rem;
}

/* Boletos Grid */
.boletos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .boletos-grid {
    grid-template-columns: 1fr;
  }
}

/* Boleto Card */
.boleto-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
}

.boleto-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.boleto-card.boleto-ganador {
  border: 2px solid var(--accent-yellow);
  box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);
}

.boleto-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.boleto-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.boleto-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.boleto-numero {
  font-weight: 500;
  color: var(--primary-purple);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.boleto-estado {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.875rem;
  font-weight: 500;
}

.estado-activo {
  background: var(--success-light);
  color: var(--success-color);
}

.estado-ganador {
  background: var(--accent-yellow);
  color: white;
}

.estado-finalizado {
  background: var(--gray-200);
  color: var(--gray-600);
}

.boleto-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  z-index: 10;
  min-width: 150px;
}

.dropdown-item {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background 0.3s ease;
}

.dropdown-item:hover {
  background: var(--gray-50);
}

.boleto-content {
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
}

.rifa-image {
  width: 80px;
  height: 80px;
  border-radius: var(--border-radius);
  overflow: hidden;
  flex-shrink: 0;
}

.rifa-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.boleto-details {
  flex: 1;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.25rem 0;
}

.detail-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.detail-value {
  font-weight: 500;
  color: var(--gray-800);
}

.detail-value.premio {
  color: var(--accent-yellow);
  font-weight: 600;
}

.progress-bar {
  margin-top: 1rem;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.progress {
  height: 6px;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-purple), var(--primary-indigo));
  transition: width 0.3s ease;
}

.ganador-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: var(--accent-yellow);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* Pagination */
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination-info {
  color: var(--gray-600);
  font-size: 0.875rem;
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

.modal-content.modal-large {
  max-width: 800px;
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
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 768px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}

.detail-section h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--gray-100);
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-item .label {
  font-weight: 500;
  color: var(--gray-600);
}

.detail-item .value {
  color: var(--gray-800);
}

/* Responsive */
@media (max-width: 768px) {
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group {
    justify-content: space-between;
  }
  
  .filter-actions {
    margin-left: 0;
  }
  
  .boleto-content {
    flex-direction: column;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>