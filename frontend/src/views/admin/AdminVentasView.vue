<template>
  <div class="admin-ventas">
    <AdminHeader />
    
    <main class="admin-main">
      <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header">
          <div class="page-title-section">
            <h1 class="admin-page-title">
              <i class="fas fa-chart-bar"></i>
              Reportes de Ventas
            </h1>
            <p class="admin-page-subtitle">
              Analiza las ventas y estadísticas del negocio
            </p>
          </div>
          
          <div class="page-actions">
            <div class="date-range-picker">
              <input 
                type="date" 
                v-model="dateRange.start" 
                class="admin-input sm"
                style="max-width: 150px"
              />
              <span class="date-separator">a</span>
              <input 
                type="date" 
                v-model="dateRange.end" 
                class="admin-input sm"
                style="max-width: 150px"
              />
            </div>
            <button @click="exportReport" class="admin-btn secondary">
              <i class="fas fa-download"></i>
              Exportar
            </button>
          </div>
        </div>

        <!-- Revenue Stats -->
        <div class="admin-stats-grid">
          <div class="admin-stat-card featured primary">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +23.5%
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">S/. {{ formatPrice(totalIngresos) }}</div>
              <div class="stat-label">Ingresos Totales</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">vs mes anterior</span>
            </div>
          </div>

          <div class="admin-stat-card success">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +15%
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ totalVentas }}</div>
              <div class="stat-label">Ventas Realizadas</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Este mes</span>
            </div>
          </div>

          <div class="admin-stat-card info">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-calculator"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +8%
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">S/. {{ formatPrice(promedioVenta) }}</div>
              <div class="stat-label">Promedio por Venta</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Este mes</span>
            </div>
          </div>

          <div class="admin-stat-card warning">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i>
              </div>
              <div class="stat-trend negative">
                <i class="fas fa-arrow-down"></i>
                -5%
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ ventasPendientes }}</div>
              <div class="stat-label">Ventas Pendientes</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Requieren atención</span>
            </div>
          </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-section">
          <div class="admin-filters">
            <div class="filter-group">
              <div class="admin-search">
                <div class="admin-input-group">
                  <i class="fas fa-search input-icon"></i>
                  <input
                    v-model="searchQuery"
                    type="text"
                    class="admin-input"
                    placeholder="Buscar ventas..."
                    @input="handleSearch"
                  />
                </div>
              </div>
              
              <select v-model="filterStatus" class="admin-select" @change="applyFilters">
                <option value="">Todos los estados</option>
                <option value="completada">Completadas</option>
                <option value="pendiente">Pendientes</option>
                <option value="cancelada">Canceladas</option>
              </select>
              
              <select v-model="filterPaymentMethod" class="admin-select" @change="applyFilters">
                <option value="">Todos los métodos</option>
                <option value="yape">Yape</option>
                <option value="plin">Plin</option>
                <option value="transferencia">Transferencia</option>
              </select>
              
              <button @click="clearFilters" class="admin-btn secondary sm">
                <i class="fas fa-times"></i>
                Limpiar
              </button>
            </div>
            
            <div class="results-info">
              <span class="results-count">
                {{ filteredVentas.length }} ventas encontradas
              </span>
            </div>
          </div>
        </div>

        <!-- Sales Table -->
        <div class="admin-section">
          <div class="admin-table-container">
            <div v-if="loading" class="admin-loading">
              <div class="loading-spinner"></div>
              <span>Cargando ventas...</span>
            </div>

            <div v-else-if="filteredVentas.length === 0" class="admin-empty-state">
              <i class="fas fa-chart-bar"></i>
              <h3>No hay ventas</h3>
              <p>No se encontraron ventas con los criterios especificados</p>
              <button @click="clearFilters" class="admin-btn primary">
                <i class="fas fa-refresh"></i>
                Limpiar filtros
              </button>
            </div>

            <table v-else class="admin-table">
              <thead>
                <tr>
                  <th class="sortable" @click="sortBy('id')">
                    <span>ID</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('usuario')">
                    <span>Cliente</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('rifa')">
                    <span>Rifa</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('cantidad')">
                    <span>Cantidad</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('monto')">
                    <span>Monto</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('metodo_pago')">
                    <span>Método de Pago</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('estado')">
                    <span>Estado</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('created_at')">
                    <span>Fecha</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="venta in paginatedVentas" :key="venta.id" class="table-row">
                  <td>
                    <div class="sale-id">
                      <span class="id-badge">#{{ venta.id }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <i class="fas fa-user"></i>
                      </div>
                      <div class="user-details">
                        <div class="user-name">{{ venta.usuario?.name }}</div>
                        <div class="user-email">{{ venta.usuario?.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="rifa-info">
                      <div class="rifa-title">{{ venta.rifa?.titulo }}</div>
                      <div class="rifa-price">S/. {{ formatPrice(venta.rifa?.precio) }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="quantity-cell">
                      <span class="quantity-badge">{{ venta.cantidad }} tickets</span>
                    </div>
                  </td>
                  <td>
                    <div class="amount-cell">
                      <span class="amount">S/. {{ formatPrice(venta.monto) }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="payment-method" :class="venta.metodo_pago">
                      {{ formatPaymentMethod(venta.metodo_pago) }}
                    </span>
                  </td>
                  <td>
                    <span class="status-badge" :class="venta.estado">
                      {{ formatStatus(venta.estado) }}
                    </span>
                  </td>
                  <td>
                    <div class="date-cell">
                      <div class="date">{{ formatDate(venta.created_at) }}</div>
                      <div class="time">{{ formatTime(venta.created_at) }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="table-actions">
                      <button 
                        @click="viewSale(venta)"
                        class="action-btn view"
                        title="Ver detalles"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button 
                        v-if="venta.estado === 'pendiente'"
                        @click="approveSale(venta)"
                        class="action-btn approve"
                        title="Aprobar venta"
                      >
                        <i class="fas fa-check"></i>
                      </button>
                      <button 
                        v-if="venta.estado === 'pendiente'"
                        @click="rejectSale(venta)"
                        class="action-btn reject"
                        title="Rechazar venta"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                      <button 
                        @click="downloadReceipt(venta)"
                        class="action-btn download"
                        title="Descargar comprobante"
                      >
                        <i class="fas fa-download"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="admin-pagination">
            <button 
              @click="previousPage"
              :disabled="currentPage === 1"
              class="pagination-btn"
            >
              <i class="fas fa-chevron-left"></i>
              Anterior
            </button>
            
            <div class="pagination-numbers">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                class="pagination-number"
                :class="{ active: page === currentPage }"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
            >
              Siguiente
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Sale Details Modal -->
    <div v-if="showSaleDetailsModal" class="admin-modal-overlay" @click="closeSaleModal">
      <div class="admin-modal large" @click.stop>
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">
            <i class="fas fa-receipt"></i>
            Detalles de la Venta #{{ selectedSale?.id }}
          </h3>
          <button @click="closeSaleModal" class="admin-modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-modal-body">
          <div v-if="selectedSale" class="sale-details">
            <div class="sale-header">
              <div class="sale-status-large" :class="selectedSale.estado">
                <i :class="getStatusIcon(selectedSale.estado)"></i>
                {{ formatStatus(selectedSale.estado) }}
              </div>
              <div class="sale-amount-large">
                S/. {{ formatPrice(selectedSale.monto) }}
              </div>
            </div>
            
            <div class="sale-details-grid">
              <div class="detail-group">
                <h4>Información de la Venta</h4>
                <div class="detail-item">
                  <span class="detail-label">ID de Venta:</span>
                  <span class="detail-value">#{{ selectedSale.id }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Fecha:</span>
                  <span class="detail-value">{{ formatDate(selectedSale.created_at) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Cantidad:</span>
                  <span class="detail-value">{{ selectedSale.cantidad }} tickets</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Método de Pago:</span>
                  <span class="detail-value">{{ formatPaymentMethod(selectedSale.metodo_pago) }}</span>
                </div>
              </div>
              
              <div class="detail-group">
                <h4>Información del Cliente</h4>
                <div class="detail-item">
                  <span class="detail-label">Nombre:</span>
                  <span class="detail-value">{{ selectedSale.usuario?.name }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Email:</span>
                  <span class="detail-value">{{ selectedSale.usuario?.email }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Teléfono:</span>
                  <span class="detail-value">{{ selectedSale.usuario?.phone || 'No especificado' }}</span>
                </div>
              </div>
              
              <div class="detail-group">
                <h4>Información de la Rifa</h4>
                <div class="detail-item">
                  <span class="detail-label">Título:</span>
                  <span class="detail-value">{{ selectedSale.rifa?.titulo }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Precio por ticket:</span>
                  <span class="detail-value">S/. {{ formatPrice(selectedSale.rifa?.precio) }}</span>
                </div>
              </div>
            </div>
            
            <div v-if="selectedSale.comprobante_pago" class="payment-proof">
              <h4>Comprobante de Pago</h4>
              <div class="proof-image">
                <img :src="selectedSale.comprobante_pago" alt="Comprobante de pago" />
              </div>
            </div>
          </div>
        </div>
        
        <div class="admin-modal-footer">
          <button 
            v-if="selectedSale?.estado === 'pendiente'"
            @click="approveSale(selectedSale)"
            class="admin-btn success"
          >
            <i class="fas fa-check"></i>
            Aprobar Venta
          </button>
          <button 
            v-if="selectedSale?.estado === 'pendiente'"
            @click="rejectSale(selectedSale)"
            class="admin-btn danger"
          >
            <i class="fas fa-times"></i>
            Rechazar Venta
          </button>
          <button @click="downloadReceipt(selectedSale)" class="admin-btn secondary">
            <i class="fas fa-download"></i>
            Descargar
          </button>
          <button @click="closeSaleModal" class="admin-btn secondary">
            Cerrar
          </button>
        </div>
      </div>
    </div>
    
    <AdminFooter />
  </div>
</template>
        
<script>
import { ref, computed, onMounted } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminVentas',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('')
    const filterPaymentMethod = ref('')
    const sortField = ref('created_at')
    const sortDirection = ref('desc')
    const currentPage = ref(1)
    const itemsPerPage = 10

    // Modals
    const showSaleDetailsModal = ref(false)
    const selectedSale = ref(null)

    // Date range
    const dateRange = ref({
      start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      end: new Date().toISOString().split('T')[0]
    })

    // Stats data
    const totalIngresos = ref(45230)
    const totalVentas = ref(892)
    const promedioVenta = ref(51)
    const ventasPendientes = ref(12)

    // Mock sales data
    const ventas = ref([
      {
        id: 1,
        usuario: {
          name: 'Carlos Mendoza',
          email: 'carlos@email.com',
          phone: '+51 987 654 321'
        },
        rifa: {
          titulo: 'iPhone 15 Pro Max',
          precio: 15
        },
        cantidad: 3,
        monto: 45,
        metodo_pago: 'yape',
        estado: 'completada',
        created_at: '2024-01-15T10:30:00Z',
        comprobante_pago: '/images/comprobantes/comp1.jpg'
      },
      {
        id: 2,
        usuario: {
          name: 'María García',
          email: 'maria@email.com',
          phone: '+51 987 654 322'
        },
        rifa: {
          titulo: 'MacBook Pro M3',
          precio: 25
        },
        cantidad: 2,
        monto: 50,
        metodo_pago: 'plin',
        estado: 'pendiente',
        created_at: '2024-01-15T09:15:00Z',
        comprobante_pago: '/images/comprobantes/comp2.jpg'
      },
      {
        id: 3,
        usuario: {
          name: 'Luis Torres',
          email: 'luis@email.com',
          phone: '+51 987 654 323'
        },
        rifa: {
          titulo: 'AirPods Pro',
          precio: 8
        },
        cantidad: 5,
        monto: 40,
        metodo_pago: 'transferencia',
        estado: 'completada',
        created_at: '2024-01-14T16:20:00Z',
        comprobante_pago: null
      },
      {
        id: 4,
        usuario: {
          name: 'Ana Silva',
          email: 'ana@email.com',
          phone: '+51 987 654 324'
        },
        rifa: {
          titulo: 'Samsung Galaxy S24',
          precio: 12
        },
        cantidad: 4,
        monto: 48,
        metodo_pago: 'yape',
        estado: 'cancelada',
        created_at: '2024-01-14T11:45:00Z',
        comprobante_pago: '/images/comprobantes/comp4.jpg'
      }
    ])

    // Computed properties
    const filteredVentas = computed(() => {
      let filtered = ventas.value

      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(venta => 
          venta.usuario.name.toLowerCase().includes(query) ||
          venta.usuario.email.toLowerCase().includes(query) ||
          venta.rifa.titulo.toLowerCase().includes(query) ||
          venta.id.toString().includes(query)
        )
      }

      // Status filter
      if (filterStatus.value) {
        filtered = filtered.filter(venta => venta.estado === filterStatus.value)
      }

      // Payment method filter
      if (filterPaymentMethod.value) {
        filtered = filtered.filter(venta => venta.metodo_pago === filterPaymentMethod.value)
      }

      // Sort
      filtered.sort((a, b) => {
        let aValue = a[sortField.value]
        let bValue = b[sortField.value]
        
        if (sortField.value === 'usuario') {
          aValue = a.usuario.name
          bValue = b.usuario.name
        } else if (sortField.value === 'rifa') {
          aValue = a.rifa.titulo
          bValue = b.rifa.titulo
        }
        
        if (typeof aValue === 'string') {
          aValue = aValue.toLowerCase()
          bValue = bValue.toLowerCase()
        }
        
        if (sortDirection.value === 'asc') {
          return aValue > bValue ? 1 : -1
        } else {
          return aValue < bValue ? 1 : -1
        }
      })

      return filtered
    })

    const totalPages = computed(() => 
      Math.ceil(filteredVentas.value.length / itemsPerPage)
    )

    const paginatedVentas = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return filteredVentas.value.slice(start, end)
    })

    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        pages.push(1)
        if (current > 4) pages.push('...')
        
        const start = Math.max(2, current - 1)
        const end = Math.min(total - 1, current + 1)
        
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }
        
        if (current < total - 3) pages.push('...')
        pages.push(total)
      }
      
      return pages
    })

    // Methods
    const handleSearch = () => {
      currentPage.value = 1
    }

    const applyFilters = () => {
      currentPage.value = 1
    }

    const clearFilters = () => {
      searchQuery.value = ''
      filterStatus.value = ''
      filterPaymentMethod.value = ''
      currentPage.value = 1
    }

    const sortBy = (field) => {
      if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortField.value = field
        sortDirection.value = 'asc'
      }
    }

    const goToPage = (page) => {
      if (page !== '...' && page >= 1 && page <= totalPages.value) {
        currentPage.value = page
      }
    }

    const previousPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
      }
    }

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++
      }
    }

    const viewSale = (sale) => {
      selectedSale.value = sale
      showSaleDetailsModal.value = true
    }

    const approveSale = async (sale) => {
      try {
        // Simular API call
        await new Promise(resolve => setTimeout(resolve, 500))
        sale.estado = 'completada'
        console.log(`Venta ${sale.id} aprobada`)
        closeSaleModal()
      } catch (error) {
        console.error('Error al aprobar venta:', error)
      }
    }

    const rejectSale = async (sale) => {
      if (confirm(`¿Estás seguro de rechazar la venta #${sale.id}?`)) {
        try {
          // Simular API call
          await new Promise(resolve => setTimeout(resolve, 500))
          sale.estado = 'cancelada'
          console.log(`Venta ${sale.id} rechazada`)
          closeSaleModal()
        } catch (error) {
          console.error('Error al rechazar venta:', error)
        }
      }
    }

    const downloadReceipt = (sale) => {
      console.log(`Descargando comprobante de venta ${sale.id}`)
      // Implementar descarga
    }

    const closeSaleModal = () => {
      showSaleDetailsModal.value = false
      selectedSale.value = null
    }

    const exportReport = () => {
      console.log('Exportando reporte de ventas...')
      // Implementar exportación
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('es-PE', {
        style: 'decimal',
        minimumFractionDigits: 2
      }).format(price)
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const formatTime = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const formatStatus = (status) => {
      const statuses = {
        'completada': 'Completada',
        'pendiente': 'Pendiente',
        'cancelada': 'Cancelada'
      }
      return statuses[status] || status
    }

    const formatPaymentMethod = (method) => {
      const methods = {
        'yape': 'Yape',
        'plin': 'Plin',
        'transferencia': 'Transferencia Bancaria'
      }
      return methods[method] || method
    }

    const getStatusIcon = (status) => {
      const icons = {
        'completada': 'fas fa-check-circle',
        'pendiente': 'fas fa-clock',
        'cancelada': 'fas fa-times-circle'
      }
      return icons[status] || 'fas fa-question-circle'
    }

    onMounted(() => {
      // Cargar datos iniciales
    })

    return {
      loading,
      searchQuery,
      filterStatus,
      filterPaymentMethod,
      currentPage,
      dateRange,
      totalIngresos,
      totalVentas,
      promedioVenta,
      ventasPendientes,
      filteredVentas,
      paginatedVentas,
      totalPages,
      visiblePages,
      showSaleDetailsModal,
      selectedSale,
      handleSearch,
      applyFilters,
      clearFilters,
      sortBy,
      goToPage,
      previousPage,
      nextPage,
      viewSale,
      approveSale,
      rejectSale,
      downloadReceipt,
      closeSaleModal,
      exportReport,
      formatPrice,
      formatDate,
      formatTime,
      formatStatus,
      formatPaymentMethod,
      getStatusIcon
    }
  }
}
</script>

<style scoped>
/* Usar clases del admin.css global */

.date-range-picker {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.date-separator {
  color: var(--admin-text-muted);
  font-weight: 500;
}

.sale-details {
  max-width: 100%;
}

.sale-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--admin-border-light);
}

.sale-status-large {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1.1rem;
}

.sale-status-large.completada {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.sale-status-large.pendiente {
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-warning);
}

.sale-status-large.cancelada {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.sale-amount-large {
  font-size: 2rem;
  font-weight: 700;
  color: var(--admin-primary-teal);
}

.sale-details-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.detail-group h4 {
  margin: 0 0 1rem 0;
  color: var(--admin-text-dark);
  font-weight: 600;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--admin-border-light);
}

.detail-label {
  font-weight: 500;
  color: var(--admin-text-muted);
}

.detail-value {
  color: var(--admin-text-dark);
  font-weight: 500;
}

.payment-proof {
  margin-top: 2rem;
}

.payment-proof h4 {
  margin: 0 0 1rem 0;
  color: var(--admin-text-dark);
  font-weight: 600;
}

.proof-image {
  border: 1px solid var(--admin-border-light);
  border-radius: 8px;
  overflow: hidden;
  max-width: 300px;
}

.proof-image img {
  width: 100%;
  height: auto;
  display: block;
}

.sale-id .id-badge {
  background: var(--admin-primary-teal);
  color: white;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: var(--admin-primary-teal);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.user-details .user-name {
  font-weight: 600;
  color: var(--admin-text-dark);
  margin: 0 0 4px 0;
}

.user-details .user-email {
  font-size: 0.875rem;
  color: var(--admin-text-muted);
}

.rifa-info .rifa-title {
  font-weight: 600;
  color: var(--admin-text-dark);
  margin: 0 0 4px 0;
}

.rifa-info .rifa-price {
  font-size: 0.875rem;
  color: var(--admin-text-muted);
}

.quantity-cell .quantity-badge {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.amount-cell .amount {
  font-weight: 700;
  color: var(--admin-primary-teal);
  font-size: 1.1rem;
}

.payment-method {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.payment-method.yape {
  background: rgba(147, 51, 234, 0.1);
  color: #9333ea;
}

.payment-method.plin {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.payment-method.transferencia {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
}

.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-badge.completada {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.status-badge.pendiente {
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-warning);
}

.status-badge.cancelada {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.date-cell .date {
  font-weight: 500;
  color: var(--admin-text-dark);
}

.date-cell .time {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
}

.action-btn.view {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
}

.action-btn.view:hover {
  background: var(--admin-info);
  color: white;
}

.action-btn.approve {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.action-btn.approve:hover {
  background: var(--admin-success);
  color: white;
}

.action-btn.reject {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.action-btn.reject:hover {
  background: var(--admin-danger);
  color: white;
}

.action-btn.download {
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-warning);
}

.action-btn.download:hover {
  background: var(--admin-warning);
  color: white;
}

@media (max-width: 768px) {
  .date-range-picker {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .sale-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .sale-details-grid {
    grid-template-columns: 1fr;
  }
  
  .table-actions {
    flex-wrap: wrap;
  }
}
</style>
      