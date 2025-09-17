<template>
  <div class="historial-page">
    <!-- Hero Section -->
    <section class="hero hero-secondary">
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-history"></i>
          </div>
          <h1 class="hero-title">Historial</h1>
          <p class="hero-subtitle">Revisa tu actividad completa en la plataforma</p>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="historial-section">
      <div class="container">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            class="tab-btn"
            :class="{ active: activeTab === tab.id }"
          >
            <i :class="tab.icon"></i>
            {{ tab.name }}
          </button>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
          <div class="filter-group">
            <label>Período:</label>
            <select v-model="filters.periodo" @change="loadData" class="filter-select">
              <option value="all">Todo el tiempo</option>
              <option value="30">Últimos 30 días</option>
              <option value="90">Últimos 3 meses</option>
              <option value="365">Último año</option>
            </select>
          </div>
          
          <div class="filter-group" v-if="activeTab === 'ventas'">
            <label>Estado:</label>
            <select v-model="filters.estado" @change="loadData" class="filter-select">
              <option value="">Todos</option>
              <option value="completada">Completadas</option>
              <option value="pendiente">Pendientes</option>
              <option value="cancelada">Canceladas</option>
            </select>
          </div>
          
          <div class="filter-actions">
            <button @click="loadData" class="btn btn-outline">
              <i class="fas fa-refresh"></i>
              Actualizar
            </button>
            
            <button @click="exportData" class="btn btn-outline">
              <i class="fas fa-download"></i>
              Exportar
            </button>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Ventas Tab -->
          <div v-if="activeTab === 'ventas'" class="ventas-content">
            <div v-if="loading" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              <p>Cargando historial de ventas...</p>
            </div>
            
            <div v-else-if="error" class="error-state">
              <i class="fas fa-exclamation-triangle"></i>
              <p>{{ error }}</p>
              <button @click="loadData" class="btn btn-primary">Reintentar</button>
            </div>
            
            <div v-else-if="ventas.length === 0" class="empty-state">
              <i class="fas fa-shopping-cart"></i>
              <h3>No hay ventas registradas</h3>
              <p>Aún no has realizado ninguna compra de boletos.</p>
            </div>
            
            <div v-else class="ventas-list">
              <div v-for="venta in ventas" :key="venta.id" class="venta-card">
                <div class="venta-header">
                  <div class="venta-info">
                    <h3 class="venta-codigo">#{{ venta.codigo }}</h3>
                    <span class="venta-fecha">{{ formatDate(venta.created_at) }}</span>
                  </div>
                  <div class="venta-estado" :class="getEstadoClass(venta.estado)">
                    {{ getEstadoTexto(venta.estado) }}
                  </div>
                </div>
                
                <div class="venta-content">
                  <div class="venta-details">
                    <div class="detail-row">
                      <span class="label">Rifa:</span>
                      <span class="value">{{ venta.rifa?.nombre }}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Boletos:</span>
                      <span class="value">{{ venta.cantidad_boletos }}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Total:</span>
                      <span class="value total">S/ {{ venta.total }}</span>
                    </div>
                    <div class="detail-row" v-if="venta.metodo_pago">
                      <span class="label">Método de pago:</span>
                      <span class="value">{{ formatMetodoPago(venta.metodo_pago) }}</span>
                    </div>
                  </div>
                  
                  <div class="venta-actions">
                    <button @click="verDetalleVenta(venta)" class="btn btn-outline btn-sm">
                      <i class="fas fa-eye"></i>
                      Ver Detalle
                    </button>
                    
                    <button 
                      v-if="venta.estado === 'pendiente'"
                      @click="confirmarPagoVenta(venta)" 
                      class="btn btn-primary btn-sm"
                    >
                      <i class="fas fa-credit-card"></i>
                      Confirmar Pago
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagos Tab -->
          <div v-if="activeTab === 'pagos'" class="pagos-content">
            <div v-if="loading" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              <p>Cargando historial de pagos...</p>
            </div>
            
            <div v-else-if="error" class="error-state">
              <i class="fas fa-exclamation-triangle"></i>
              <p>{{ error }}</p>
              <button @click="loadData" class="btn btn-primary">Reintentar</button>
            </div>
            
            <div v-else-if="pagos.length === 0" class="empty-state">
              <i class="fas fa-credit-card"></i>
              <h3>No hay pagos registrados</h3>
              <p>Aún no has realizado ningún pago.</p>
            </div>
            
            <div v-else class="pagos-list">
              <div v-for="pago in pagos" :key="pago.id" class="pago-card">
                <div class="pago-header">
                  <div class="pago-info">
                    <h3 class="pago-referencia">#{{ pago.referencia }}</h3>
                    <span class="pago-fecha">{{ formatDate(pago.created_at) }}</span>
                  </div>
                  <div class="pago-monto">S/ {{ pago.monto }}</div>
                </div>
                
                <div class="pago-content">
                  <div class="pago-details">
                    <div class="detail-row">
                      <span class="label">Método:</span>
                      <span class="value">{{ formatMetodoPago(pago.metodo) }}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Estado:</span>
                      <span class="value" :class="getEstadoClass(pago.estado)">
                        {{ getEstadoTexto(pago.estado) }}
                      </span>
                    </div>
                    <div class="detail-row" v-if="pago.venta">
                      <span class="label">Venta:</span>
                      <span class="value">#{{ pago.venta.codigo }}</span>
                    </div>
                  </div>
                  
                  <div class="pago-actions">
                    <button @click="verDetallePago(pago)" class="btn btn-outline btn-sm">
                      <i class="fas fa-eye"></i>
                      Ver Detalle
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actividad Tab -->
          <div v-if="activeTab === 'actividad'" class="actividad-content">
            <div v-if="loading" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              <p>Cargando actividad...</p>
            </div>
            
            <div v-else-if="error" class="error-state">
              <i class="fas fa-exclamation-triangle"></i>
              <p>{{ error }}</p>
              <button @click="loadData" class="btn btn-primary">Reintentar</button>
            </div>
            
            <div v-else-if="actividades.length === 0" class="empty-state">
              <i class="fas fa-clock"></i>
              <h3>No hay actividad registrada</h3>
              <p>Tu actividad aparecerá aquí cuando realices acciones en la plataforma.</p>
            </div>
            
            <div v-else class="actividad-timeline">
              <div v-for="actividad in actividades" :key="actividad.id" class="actividad-item">
                <div class="actividad-icon" :class="getActividadIconClass(actividad.tipo)">
                  <i :class="getActividadIcon(actividad.tipo)"></i>
                </div>
                
                <div class="actividad-content">
                  <div class="actividad-header">
                    <h4 class="actividad-titulo">{{ actividad.titulo }}</h4>
                    <span class="actividad-fecha">{{ formatDate(actividad.created_at) }}</span>
                  </div>
                  
                  <p class="actividad-descripcion">{{ actividad.descripcion }}</p>
                  
                  <div v-if="actividad.metadata" class="actividad-metadata">
                    <span v-for="(value, key) in actividad.metadata" :key="key" class="metadata-item">
                      {{ key }}: {{ value }}
                    </span>
                  </div>
                </div>
              </div>
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

    <!-- Detail Modals -->
    <!-- Venta Detail Modal -->
    <div v-if="showVentaModal" class="modal-overlay" @click="showVentaModal = false">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h3>Detalle de Venta</h3>
          <button @click="showVentaModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div v-if="selectedVenta" class="venta-detail">
            <!-- Venta detail content -->
            <div class="detail-grid">
              <div class="detail-section">
                <h4>Información de la Venta</h4>
                <div class="detail-item">
                  <span class="label">Código:</span>
                  <span class="value">#{{ selectedVenta.codigo }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Estado:</span>
                  <span class="value" :class="getEstadoClass(selectedVenta.estado)">
                    {{ getEstadoTexto(selectedVenta.estado) }}
                  </span>
                </div>
                <div class="detail-item">
                  <span class="label">Total:</span>
                  <span class="value">S/ {{ selectedVenta.total }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Fecha:</span>
                  <span class="value">{{ formatDate(selectedVenta.created_at) }}</span>
                </div>
              </div>
              
              <div class="detail-section">
                <h4>Boletos Comprados</h4>
                <div v-if="selectedVenta.boletos" class="boletos-list">
                  <div v-for="boleto in selectedVenta.boletos" :key="boleto.id" class="boleto-item">
                    <span class="boleto-numero">#{{ boleto.numero }}</span>
                    <span class="boleto-precio">S/ {{ boleto.precio }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pago Detail Modal -->
    <div v-if="showPagoModal" class="modal-overlay" @click="showPagoModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Detalle de Pago</h3>
          <button @click="showPagoModal = false" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div v-if="selectedPago" class="pago-detail">
            <!-- Pago detail content -->
            <div class="detail-section">
              <div class="detail-item">
                <span class="label">Referencia:</span>
                <span class="value">#{{ selectedPago.referencia }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Monto:</span>
                <span class="value">S/ {{ selectedPago.monto }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Método:</span>
                <span class="value">{{ formatMetodoPago(selectedPago.metodo) }}</span>
              </div>
              <div class="detail-item">
                <span class="label">Estado:</span>
                <span class="value" :class="getEstadoClass(selectedPago.estado)">
                  {{ getEstadoTexto(selectedPago.estado) }}
                </span>
              </div>
              <div class="detail-item">
                <span class="label">Fecha:</span>
                <span class="value">{{ formatDate(selectedPago.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, reactive, watch } from 'vue'
import { userService } from '@/services/api/userService'
import { formatDate, showNotification } from '@/utils/helpers'

export default {
  name: 'HistorialView',
  setup() {
    const activeTab = ref('ventas')
    const loading = ref(false)
    const error = ref(null)
    
    const ventas = ref([])
    const pagos = ref([])
    const actividades = ref([])
    const pagination = ref({})
    
    const showVentaModal = ref(false)
    const showPagoModal = ref(false)
    const selectedVenta = ref(null)
    const selectedPago = ref(null)
    
    const tabs = [
      { id: 'ventas', name: 'Ventas', icon: 'fas fa-shopping-cart' },
      { id: 'pagos', name: 'Pagos', icon: 'fas fa-credit-card' },
      { id: 'actividad', name: 'Actividad', icon: 'fas fa-clock' }
    ]
    
    const filters = reactive({
      periodo: '30',
      estado: '',
      page: 1
    })

    const loadData = async () => {
      try {
        loading.value = true
        error.value = null
        
        const params = {
          ...filters,
          periodo: filters.periodo !== 'all' ? filters.periodo : undefined
        }
        
        if (activeTab.value === 'ventas') {
          const response = await userService.getMisVentas(params)
          ventas.value = response.data.ventas || []
          pagination.value = response.data.pagination || {}
        } else if (activeTab.value === 'pagos') {
          const response = await userService.getPagos(params)
          pagos.value = response.data.pagos || []
          pagination.value = response.data.pagination || {}
        } else if (activeTab.value === 'actividad') {
          const response = await userService.getActivity(params)
          actividades.value = response.data.actividades || []
          pagination.value = response.data.pagination || {}
        }
        
      } catch (err) {
        console.error('Error al cargar datos:', err)
        error.value = err.response?.data?.message || 'Error al cargar los datos'
      } finally {
        loading.value = false
      }
    }

    const getEstadoClass = (estado) => {
      return {
        'estado-completada': estado === 'completada' || estado === 'confirmado',
        'estado-pendiente': estado === 'pendiente',
        'estado-cancelada': estado === 'cancelada' || estado === 'cancelado'
      }
    }

    const getEstadoTexto = (estado) => {
      const estados = {
        'completada': 'Completada',
        'pendiente': 'Pendiente',
        'cancelada': 'Cancelada',
        'confirmado': 'Confirmado',
        'cancelado': 'Cancelado'
      }
      return estados[estado] || estado
    }

    const formatMetodoPago = (metodo) => {
      const metodos = {
        'yape': 'Yape',
        'plin': 'Plin',
        'transferencia': 'Transferencia Bancaria',
        'efectivo': 'Efectivo'
      }
      return metodos[metodo] || metodo
    }

    const getActividadIconClass = (tipo) => {
      return {
        'icon-compra': tipo === 'compra',
        'icon-pago': tipo === 'pago',
        'icon-transferencia': tipo === 'transferencia',
        'icon-ganador': tipo === 'ganador'
      }
    }

    const getActividadIcon = (tipo) => {
      const iconos = {
        'compra': 'fas fa-shopping-cart',
        'pago': 'fas fa-credit-card',
        'transferencia': 'fas fa-exchange-alt',
        'ganador': 'fas fa-trophy',
        'perfil': 'fas fa-user',
        'login': 'fas fa-sign-in-alt'
      }
      return iconos[tipo] || 'fas fa-circle'
    }

    const verDetalleVenta = (venta) => {
      selectedVenta.value = venta
      showVentaModal.value = true
    }

    const verDetallePago = (pago) => {
      selectedPago.value = pago
      showPagoModal.value = true
    }

    const confirmarPagoVenta = (venta) => {
      // Implementar confirmación de pago
      showNotification('Función en desarrollo', 'info')
    }

    const exportData = () => {
      // Implementar exportación de datos
      showNotification('Función en desarrollo', 'info')
    }

    const changePage = (page) => {
      filters.page = page
      loadData()
    }

    // Watch para recargar datos cuando cambie el tab
    watch(activeTab, () => {
      filters.page = 1
      loadData()
    })

    onMounted(() => {
      loadData()
    })

    return {
      activeTab,
      loading,
      error,
      ventas,
      pagos,
      actividades,
      pagination,
      showVentaModal,
      showPagoModal,
      selectedVenta,
      selectedPago,
      tabs,
      filters,
      loadData,
      getEstadoClass,
      getEstadoTexto,
      formatMetodoPago,
      getActividadIconClass,
      getActividadIcon,
      verDetalleVenta,
      verDetallePago,
      confirmarPagoVenta,
      exportData,
      changePage,
      formatDate
    }
  }
}
</script>

<style scoped>
.historial-page {
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
.historial-section {
  padding: 2rem 0;
}

/* Tab Navigation */
.tab-navigation {
  display: flex;
  background: white;
  border-radius: var(--border-radius-lg);
  padding: 0.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-sm);
  gap: 0.5rem;
}

.tab-btn {
  flex: 1;
  padding: 1rem;
  background: transparent;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-weight: 500;
  color: var(--gray-600);
}

.tab-btn:hover {
  background: var(--gray-50);
  color: var(--gray-800);
}

.tab-btn.active {
  background: var(--primary-purple);
  color: white;
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
  display: flex;
  gap: 0.5rem;
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

/* Card Lists */
.ventas-list,
.pagos-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.venta-card,
.pago-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  transition: box-shadow 0.3s ease;
}

.venta-card:hover,
.pago-card:hover {
  box-shadow: var(--shadow-md);
}

.venta-header,
.pago-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.venta-codigo,
.pago-referencia {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.venta-fecha,
.pago-fecha {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.venta-estado,
.pago-monto {
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-weight: 500;
}

.venta-estado {
  font-size: 0.875rem;
}

.estado-completada {
  background: var(--success-light);
  color: var(--success-color);
}

.estado-pendiente {
  background: var(--warning-light);
  color: var(--warning-color);
}

.estado-cancelada {
  background: var(--error-light);
  color: var(--error-color);
}

.pago-monto {
  background: var(--primary-purple);
  color: white;
  font-size: 1.125rem;
}

.venta-content,
.pago-content {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.venta-details,
.pago-details {
  flex: 1;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.25rem 0;
}

.detail-row .label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.detail-row .value {
  font-weight: 500;
  color: var(--gray-800);
}

.detail-row .value.total {
  color: var(--primary-purple);
  font-weight: 600;
}

.venta-actions,
.pago-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

/* Activity Timeline */
.actividad-timeline {
  position: relative;
}

.actividad-timeline::before {
  content: '';
  position: absolute;
  left: 2rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background: var(--gray-200);
}

.actividad-item {
  position: relative;
  display: flex;
  gap: 1.5rem;
  padding: 1.5rem 0;
  margin-left: 1rem;
}

.actividad-item:not(:last-child) {
  border-bottom: 1px solid var(--gray-100);
}

.actividad-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
  flex-shrink: 0;
  position: relative;
  z-index: 1;
}

.icon-compra {
  background: var(--primary-purple);
}

.icon-pago {
  background: var(--success-color);
}

.icon-transferencia {
  background: var(--info-color);
}

.icon-ganador {
  background: var(--accent-yellow);
}

.actividad-content {
  flex: 1;
  background: white;
  padding: 1.5rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
}

.actividad-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.actividad-titulo {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
}

.actividad-fecha {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.actividad-descripcion {
  color: var(--gray-600);
  margin-bottom: 1rem;
}

.actividad-metadata {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.metadata-item {
  background: var(--gray-100);
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.875rem;
  color: var(--gray-700);
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

.boletos-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.boleto-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
}

.boleto-numero {
  font-weight: 500;
  color: var(--primary-purple);
}

.boleto-precio {
  font-weight: 600;
  color: var(--gray-800);
}

/* Responsive */
@media (max-width: 768px) {
  .tab-navigation {
    flex-direction: column;
  }
  
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .filter-group {
    justify-content: space-between;
  }
  
  .filter-actions {
    margin-left: 0;
    justify-content: center;
  }
  
  .venta-content,
  .pago-content {
    flex-direction: column;
  }
  
  .venta-actions,
  .pago-actions {
    flex-direction: row;
    justify-content: center;
  }
  
  .actividad-timeline::before {
    left: 1rem;
  }
  
  .actividad-item {
    margin-left: 0;
  }
}
</style>