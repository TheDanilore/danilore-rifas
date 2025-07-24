<template>
  <div class="admin-rifas">
    <AdminHeader />
    
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <i class="fas fa-ticket-alt"></i>
            Gestión de Rifas
          </h1>
          <p class="hero-subtitle">
            Administra todas las rifas del sistema
          </p>
        </div>
        
        <div class="hero-actions">
          <button @click="showCreateModal = true" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i>
            Nueva Rifa
          </button>
          <button @click="exportData" class="btn btn-outline btn-lg">
            <i class="fas fa-download"></i>
            Exportar
          </button>
        </div>
      </div>
    </section>

    <!-- Filters and Stats -->
    <section class="filters-section">
      <div class="container">
        <div class="filters-card">
          <div class="filters-row">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar rifas..."
                class="search-input"
              />
            </div>
            
            <div class="filter-group">
              <select v-model="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="activa">Activas</option>
                <option value="finalizada">Finalizadas</option>
                <option value="borrador">Borradores</option>
              </select>
              
              <select v-model="sortBy" class="filter-select">
                <option value="fecha">Ordenar por fecha</option>
                <option value="nombre">Ordenar por nombre</option>
                <option value="precio">Ordenar por precio</option>
                <option value="vendidos">Tickets vendidos</option>
              </select>
            </div>
          </div>
          
          <div class="quick-stats">
            <div class="quick-stat">
              <span class="stat-number">{{ rifasActivas }}</span>
              <span class="stat-label">Activas</span>
            </div>
            <div class="quick-stat">
              <span class="stat-number">{{ totalVentas }}</span>
              <span class="stat-label">Total Ventas</span>
            </div>
            <div class="quick-stat">
              <span class="stat-number">{{ ticketsVendidos }}</span>
              <span class="stat-label">Tickets Vendidos</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Rifas Table -->
    <section class="rifas-table-section">
      <div class="container">
        <div class="table-card">
          <div class="table-header">
            <h3>Lista de Rifas</h3>
            <div class="table-actions">
              <button class="btn btn-ghost btn-sm">
                <i class="fas fa-filter"></i>
                Filtros
              </button>
            </div>
          </div>
          
          <div class="table-container">
            <table class="rifas-table">
              <thead>
                <tr>
                  <th>Rifa</th>
                  <th>Estado</th>
                  <th>Precio</th>
                  <th>Tickets</th>
                  <th>Vendidos</th>
                  <th>Ingresos</th>
                  <th>Fecha Fin</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rifa in filteredRifas" :key="rifa.id" class="table-row">
                  <td>
                    <div class="rifa-info">
                      <img :src="rifa.imagen" :alt="rifa.nombre" class="rifa-thumb" />
                      <div>
                        <h4 class="rifa-name">{{ rifa.nombre }}</h4>
                        <p class="rifa-id">ID: {{ rifa.id }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge" :class="rifa.estado">
                      {{ rifa.estado }}
                    </span>
                  </td>
                  <td class="price">S/. {{ rifa.precioTicket }}</td>
                  <td>{{ rifa.totalTickets }}</td>
                  <td>
                    <div class="progress-cell">
                      <span>{{ rifa.ticketsVendidos }}</span>
                      <div class="mini-progress">
                        <div 
                          class="progress-fill" 
                          :style="{ width: (rifa.ticketsVendidos / rifa.totalTickets * 100) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td class="price">S/. {{ (rifa.ticketsVendidos * rifa.precioTicket).toLocaleString() }}</td>
                  <td>{{ formatDate(rifa.fechaFin) }}</td>
                  <td>
                    <div class="actions-cell">
                      <button @click="editRifa(rifa)" class="action-btn edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="viewRifa(rifa)" class="action-btn view">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="deleteRifa(rifa)" class="action-btn delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="table-pagination">
            <div class="pagination-info">
              Mostrando {{ filteredRifas.length }} de {{ rifas.length }} rifas
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="page-number">{{ currentPage }}</span>
              <button class="pagination-btn" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AdminFooter />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminRifas',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const searchQuery = ref('')
    const statusFilter = ref('')
    const sortBy = ref('fecha')
    const currentPage = ref(1)
    const showCreateModal = ref(false)

    const rifas = ref([
      {
        id: 'RF001',
        nombre: 'iPhone 15 Pro Max',
        imagen: '/api/placeholder/60/60',
        estado: 'activa',
        precioTicket: 15,
        totalTickets: 500,
        ticketsVendidos: 450,
        fechaFin: '2024-02-15',
        fechaCreacion: '2024-01-15'
      },
      {
        id: 'RF002',
        nombre: 'MacBook Pro M3',
        imagen: '/api/placeholder/60/60',
        estado: 'activa',
        precioTicket: 25,
        totalTickets: 400,
        ticketsVendidos: 280,
        fechaFin: '2024-02-20',
        fechaCreacion: '2024-01-10'
      },
      {
        id: 'RF003',
        nombre: 'PlayStation 5',
        imagen: '/api/placeholder/60/60',
        estado: 'finalizada',
        precioTicket: 20,
        totalTickets: 300,
        ticketsVendidos: 300,
        fechaFin: '2024-01-30',
        fechaCreacion: '2024-01-01'
      },
      {
        id: 'RF004',
        nombre: 'AirPods Pro',
        imagen: '/api/placeholder/60/60',
        estado: 'borrador',
        precioTicket: 10,
        totalTickets: 200,
        ticketsVendidos: 0,
        fechaFin: '2024-03-01',
        fechaCreacion: '2024-02-01'
      }
    ])

    const filteredRifas = computed(() => {
      let filtered = rifas.value

      // Filtro por búsqueda
      if (searchQuery.value) {
        filtered = filtered.filter(rifa =>
          rifa.nombre.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
          rifa.id.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
      }

      // Filtro por estado
      if (statusFilter.value) {
        filtered = filtered.filter(rifa => rifa.estado === statusFilter.value)
      }

      return filtered
    })

    const rifasActivas = computed(() => {
      return rifas.value.filter(r => r.estado === 'activa').length
    })

    const totalVentas = computed(() => {
      return rifas.value.reduce((total, rifa) => {
        return total + (rifa.ticketsVendidos * rifa.precioTicket)
      }, 0).toLocaleString()
    })

    const ticketsVendidos = computed(() => {
      return rifas.value.reduce((total, rifa) => total + rifa.ticketsVendidos, 0)
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredRifas.value.length / 10)
    })

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }

    const editRifa = (rifa) => {
      console.log('Editar rifa:', rifa)
    }

    const viewRifa = (rifa) => {
      console.log('Ver rifa:', rifa)
    }

    const deleteRifa = (rifa) => {
      if (confirm(`¿Estás seguro de eliminar la rifa "${rifa.nombre}"?`)) {
        console.log('Eliminar rifa:', rifa)
      }
    }

    const exportData = () => {
      console.log('Exportar datos')
    }

    onMounted(() => {
      console.log('Admin Rifas cargado')
    })

    return {
      searchQuery,
      statusFilter,
      sortBy,
      currentPage,
      showCreateModal,
      rifas,
      filteredRifas,
      rifasActivas,
      totalVentas,
      ticketsVendidos,
      totalPages,
      formatDate,
      editRifa,
      viewRifa,
      deleteRifa,
      exportData
    }
  }
}
</script>

<style scoped>
.admin-rifas {
  min-height: 100vh;
  background: var(--gray-50);
  display: flex;
  flex-direction: column;
}

.admin-hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 3rem 0;
}

.hero-content {
  text-align: center;
  margin-bottom: 2rem;
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.hero-subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
}

.hero-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.filters-section {
  padding: 2rem 0;
}

.filters-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.filters-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 2rem;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray-400);
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-purple);
}

.filter-group {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.875rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--border-radius);
  background: white;
  cursor: pointer;
}

.quick-stats {
  display: flex;
  gap: 3rem;
  justify-content: center;
}

.quick-stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-purple);
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.rifas-table-section {
  padding: 0 0 3rem 0;
  flex: 1;
}

.table-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.table-header {
  padding: 2rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.table-container {
  overflow-x: auto;
}

.rifas-table {
  width: 100%;
  border-collapse: collapse;
}

.rifas-table th {
  background: var(--gray-50);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--gray-700);
  border-bottom: 1px solid var(--gray-200);
}

.table-row {
  border-bottom: 1px solid var(--gray-100);
  transition: background 0.3s ease;
}

.table-row:hover {
  background: var(--gray-50);
}

.rifas-table td {
  padding: 1rem;
  vertical-align: middle;
}

.rifa-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.rifa-thumb {
  width: 3rem;
  height: 3rem;
  border-radius: var(--border-radius);
  object-fit: cover;
}

.rifa-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.rifa-id {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.activa {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-green);
}

.status-badge.finalizada {
  background: rgba(107, 114, 128, 0.1);
  color: var(--gray-600);
}

.status-badge.borrador {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning-yellow);
}

.price {
  font-weight: 600;
  color: var(--primary-purple);
}

.progress-cell {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mini-progress {
  width: 60px;
  height: 4px;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  transition: width 0.3s ease;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 2rem;
  height: 2rem;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.action-btn.edit {
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary-blue);
}

.action-btn.view {
  background: rgba(107, 114, 128, 0.1);
  color: var(--gray-600);
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-red);
}

.action-btn:hover {
  transform: scale(1.1);
}

.table-pagination {
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-info {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-btn {
  width: 2rem;
  height: 2rem;
  border: 1px solid var(--gray-300);
  background: white;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--primary-purple);
  color: white;
  border-color: var(--primary-purple);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-number {
  font-weight: 600;
}

@media (max-width: 1024px) {
  .filters-row {
    flex-direction: column;
    gap: 1rem;
  }

  .search-box {
    max-width: none;
  }

  .table-container {
    font-size: 0.875rem;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
    flex-direction: column;
    gap: 0.5rem;
  }

  .hero-actions {
    flex-direction: column;
    align-items: center;
  }

  .quick-stats {
    flex-direction: column;
    gap: 1rem;
  }

  .table-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .rifas-table {
    font-size: 0.75rem;
  }

  .rifa-info {
    flex-direction: column;
    text-align: center;
  }
}
</style>
