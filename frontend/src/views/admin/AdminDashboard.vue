<template>
  <div class="admin-dashboard">
    <AdminHeader />
    
    <!-- Dashboard Content -->
    <main class="admin-main">
      <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header">
          <div class="page-title-section">
            <h1 class="admin-page-title">
              <i class="fas fa-tachometer-alt"></i>
              Dashboard
            </h1>
            <p class="admin-page-subtitle">
              Resumen general de Danilore Rifas
            </p>
          </div>
          
          <div class="page-actions">
            <button class="admin-btn secondary" @click="refreshData">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Actualizar
            </button>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="admin-stats-grid">
          <div 
            v-for="stat in stats" 
            :key="stat.id"
            class="admin-stat-card"
            :class="stat.type"
          >
            <div class="stat-header">
              <div class="stat-icon">
                <i :class="stat.icon"></i>
              </div>
              <div class="stat-trend" :class="stat.trendType">
                <i :class="stat.trendIcon"></i>
                {{ stat.trend }}
              </div>
            </div>
            
            <div class="stat-content">
              <div class="stat-value">{{ stat.value }}</div>
              <div class="stat-label">{{ stat.label }}</div>
            </div>
            
            <div class="stat-footer">
              <span class="stat-period">{{ stat.period }}</span>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="admin-section">
          <h2 class="admin-section-title">Acciones Rápidas</h2>
          <div class="admin-actions-grid">
            <router-link
              v-for="action in quickActions"
              :key="action.id"
              :to="action.route"
              class="admin-action-card"
            >
              <div class="action-icon">
                <i :class="action.icon"></i>
              </div>
              <div class="action-content">
                <h3 class="action-title">{{ action.title }}</h3>
                <p class="action-description">{{ action.description }}</p>
              </div>
              <div class="action-arrow">
                <i class="fas fa-arrow-right"></i>
              </div>
            </router-link>
          </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="admin-dashboard-grid">
          <!-- Recent Rifas -->
          <div class="admin-widget">
            <div class="widget-header">
              <h3 class="widget-title">
                <i class="fas fa-ticket-alt"></i>
                Rifas Recientes
              </h3>
              <router-link to="/admin/rifas" class="widget-action">
                Ver todas
              </router-link>
            </div>
            
            <div class="widget-content">
              <div v-if="recentRifas.length === 0" class="admin-empty-state">
                <i class="fas fa-ticket-alt"></i>
                <p>No hay rifas recientes</p>
              </div>
              
              <div v-else class="rifas-list">
                <div 
                  v-for="rifa in recentRifas" 
                  :key="rifa.id"
                  class="rifa-item"
                >
                  <div class="rifa-image">
                    <img 
                      :src="rifa.image || '/images/default-rifa.jpg'" 
                      :alt="rifa.titulo"
                      @error="handleImageError"
                    />
                  </div>
                  
                  <div class="rifa-info">
                    <h4 class="rifa-title">{{ rifa.titulo }}</h4>
                    <p class="rifa-price">${{ formatPrice(rifa.precio) }}</p>
                    <div class="rifa-meta">
                      <span class="rifa-status" :class="rifa.estado">
                        {{ formatStatus(rifa.estado) }}
                      </span>
                      <span class="rifa-date">
                        {{ formatDate(rifa.created_at) }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="rifa-actions">
                    <router-link 
                      :to="`/admin/rifas/${rifa.id}`"
                      class="admin-btn sm secondary"
                    >
                      Ver
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Sales -->
          <div class="admin-widget">
            <div class="widget-header">
              <h3 class="widget-title">
                <i class="fas fa-chart-line"></i>
                Ventas Recientes
              </h3>
              <router-link to="/admin/ventas" class="widget-action">
                Ver todas
              </router-link>
            </div>
            
            <div class="widget-content">
              <div v-if="recentSales.length === 0" class="admin-empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No hay ventas recientes</p>
              </div>
              
              <div v-else class="sales-list">
                <div 
                  v-for="sale in recentSales" 
                  :key="sale.id"
                  class="sale-item"
                >
                  <div class="sale-user">
                    <div class="user-avatar">
                      <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                      <div class="user-name">{{ sale.usuario?.name }}</div>
                      <div class="user-email">{{ sale.usuario?.email }}</div>
                    </div>
                  </div>
                  
                  <div class="sale-details">
                    <div class="sale-amount">${{ formatPrice(sale.monto) }}</div>
                    <div class="sale-rifa">{{ sale.rifa?.titulo }}</div>
                    <div class="sale-date">{{ formatDate(sale.created_at) }}</div>
                  </div>
                  
                  <div class="sale-status">
                    <span class="status-badge" :class="sale.estado">
                      {{ formatSaleStatus(sale.estado) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <AdminFooter />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminDashboard',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const loading = ref(false)
    const error = ref(null)
    
    const stats = ref([
      {
        id: 1,
        type: 'primary',
        icon: 'fas fa-ticket-alt',
        value: '24',
        label: 'Rifas Activas',
        trend: '+12%',
        trendType: 'positive',
        trendIcon: 'fas fa-arrow-up',
        period: 'vs mes anterior'
      },
      {
        id: 2,
        type: 'success', 
        icon: 'fas fa-dollar-sign',
        value: '$12,350',
        label: 'Ingresos del Mes',
        trend: '+8.5%',
        trendType: 'positive',
        trendIcon: 'fas fa-arrow-up',
        period: 'vs mes anterior'
      },
      {
        id: 3,
        type: 'info',
        icon: 'fas fa-users',
        value: '1,247',
        label: 'Usuarios Registrados',
        trend: '+15%',
        trendType: 'positive',
        trendIcon: 'fas fa-arrow-up',
        period: 'vs mes anterior'
      },
      {
        id: 4,
        type: 'warning',
        icon: 'fas fa-chart-line',
        value: '89',
        label: 'Ventas Hoy',
        trend: '-2.1%',
        trendType: 'negative',
        trendIcon: 'fas fa-arrow-down',
        period: 'vs ayer'
      }
    ])

    const quickActions = ref([
      {
        id: 1,
        title: 'Nueva Rifa',
        description: 'Crear una nueva rifa',
        icon: 'fas fa-plus-circle',
        route: '/admin/rifas/nueva'
      },
      {
        id: 2,
        title: 'Gestionar Usuarios',
        description: 'Ver y administrar usuarios',
        icon: 'fas fa-users-cog',
        route: '/admin/usuarios'
      },
      {
        id: 3,
        title: 'Ver Ventas',
        description: 'Revisar historial de ventas',
        icon: 'fas fa-chart-bar',
        route: '/admin/ventas'
      },
      {
        id: 4,
        title: 'Configuración',
        description: 'Ajustes del sistema',
        icon: 'fas fa-cog',
        route: '/admin/settings'
      }
    ])

    const recentRifas = ref([
      {
        id: 1,
        titulo: 'iPhone 15 Pro Max',
        precio: 15,
        estado: 'activa',
        image: '/images/iphone15.jpg',
        created_at: '2024-01-15T10:00:00Z'
      },
      {
        id: 2,
        titulo: 'MacBook Pro M3',
        precio: 25,
        estado: 'completada',
        image: '/images/macbook.jpg',
        created_at: '2024-01-14T08:30:00Z'
      },
      {
        id: 3,
        titulo: 'AirPods Pro',
        precio: 8,
        estado: 'pendiente',
        image: '/images/airpods.jpg',
        created_at: '2024-01-13T15:20:00Z'
      }
    ])

    const recentSales = ref([
      {
        id: 1,
        monto: 45,
        estado: 'completada',
        created_at: '2024-01-15T11:30:00Z',
        usuario: {
          name: 'Carlos Mendoza',
          email: 'carlos@email.com'
        },
        rifa: {
          titulo: 'iPhone 15 Pro Max'
        }
      },
      {
        id: 2,
        monto: 75,
        estado: 'pendiente',
        created_at: '2024-01-15T09:15:00Z',
        usuario: {
          name: 'María García',
          email: 'maria@email.com'
        },
        rifa: {
          titulo: 'MacBook Pro M3'
        }
      }
    ])

    const refreshData = async () => {
      loading.value = true
      try {
        // Simular carga de datos
        await new Promise(resolve => setTimeout(resolve, 1000))
        console.log('Datos actualizados')
      } catch (err) {
        error.value = 'Error al actualizar datos'
        console.error(err)
      } finally {
        loading.value = false
      }
    }

    const formatPrice = (price) => {
      return new Intl.NumberFormat('es-PE', {
        style: 'decimal',
        minimumFractionDigits: 2
      }).format(price)
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const formatStatus = (status) => {
      const statusMap = {
        'activa': 'Activa',
        'completada': 'Completada',
        'pendiente': 'Pendiente',
        'cancelada': 'Cancelada'
      }
      return statusMap[status] || status
    }

    const formatSaleStatus = (status) => {
      const statusMap = {
        'completada': 'Completada',
        'pendiente': 'Pendiente',
        'cancelada': 'Cancelada'
      }
      return statusMap[status] || status
    }

    const handleImageError = (event) => {
      event.target.src = '/images/default-rifa.jpg'
    }

    onMounted(() => {
      refreshData()
    })

    return {
      loading,
      error,
      stats,
      quickActions,
      recentRifas,
      recentSales,
      refreshData,
      formatPrice,
      formatDate,
      formatStatus,
      formatSaleStatus,
      handleImageError
    }
  }
}
</script>

<style scoped>
/* Usar clases del admin.css global */

.admin-dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.rifas-list,
.sales-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.rifa-item,
.sale-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--admin-bg-light);
  border: 1px solid var(--admin-border-light);
  border-radius: 8px;
  transition: all 0.3s ease;
}

.rifa-item:hover,
.sale-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.rifa-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.rifa-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.rifa-info {
  flex: 1;
}

.rifa-title {
  font-weight: 600;
  color: var(--admin-text-dark);
  margin: 0 0 4px 0;
  font-size: 0.95rem;
}

.rifa-price {
  font-weight: 700;
  color: var(--admin-primary-teal);
  margin: 0 0 8px 0;
}

.rifa-meta {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.rifa-status {
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.rifa-status.activa {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.rifa-status.completada {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
}

.rifa-status.pendiente {
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-warning);
}

.rifa-date {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
}

.sale-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 200px;
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
  font-size: 0.9rem;
}

.user-name {
  font-weight: 600;
  color: var(--admin-text-dark);
  font-size: 0.9rem;
}

.user-email {
  font-size: 0.8rem;
  color: var(--admin-text-muted);
}

.sale-details {
  flex: 1;
  text-align: center;
}

.sale-amount {
  font-weight: 700;
  color: var(--admin-primary-teal);
  font-size: 1.1rem;
}

.sale-rifa {
  font-size: 0.85rem;
  color: var(--admin-text-dark);
  margin: 4px 0;
}

.sale-date {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
}

.sale-status {
  text-align: right;
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

@media (max-width: 768px) {
  .admin-dashboard-grid {
    grid-template-columns: 1fr;
  }
  
  .rifa-item,
  .sale-item {
    flex-direction: column;
    text-align: center;
  }
  
  .sale-user {
    min-width: auto;
  }
}
</style>
        