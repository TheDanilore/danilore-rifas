<template>
  <div class="admin-dashboard">
    <AdminHeader />
    
    <!-- Dashboard Stats -->
    <section class="admin-hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <i class="fas fa-tachometer-alt"></i>
            Panel de Administración
          </h1>
          <p class="hero-subtitle">
            Gestiona todas las operaciones de Danilore Rifas
          </p>
        </div>
        
        <div class="stats-grid">
          <div class="stat-card" v-for="stat in stats" :key="stat.id">
            <div class="stat-icon" :style="{ background: stat.color }">
              <i :class="stat.icon"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">{{ stat.value }}</h3>
              <p class="stat-label">{{ stat.label }}</p>
              <span class="stat-change" :class="stat.changeType">
                <i :class="stat.changeIcon"></i>
                {{ stat.change }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Actions -->
    <section class="quick-actions">
      <div class="container">
        <h2 class="section-title">Acciones Rápidas</h2>
        <div class="actions-grid">
          <router-link
            v-for="action in quickActions"
            :key="action.id"
            :to="action.route"
            class="action-card"
          >
            <div class="action-icon">
              <i :class="action.icon"></i>
            </div>
            <h3 class="action-title">{{ action.title }}</h3>
            <p class="action-description">{{ action.description }}</p>
            <div class="action-arrow">
              <i class="fas fa-arrow-right"></i>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Recent Activity -->
    <section class="recent-activity">
      <div class="container">
        <div class="activity-header">
          <h2 class="section-title">Actividad Reciente</h2>
          <button class="btn btn-outline">
            <i class="fas fa-refresh"></i>
            Actualizar
          </button>
        </div>
        
        <div class="activity-list">
          <div
            v-for="activity in recentActivity"
            :key="activity.id"
            class="activity-item"
          >
            <div class="activity-icon" :class="activity.type">
              <i :class="activity.icon"></i>
            </div>
            <div class="activity-content">
              <h4 class="activity-title">{{ activity.title }}</h4>
              <p class="activity-description">{{ activity.description }}</p>
              <span class="activity-time">{{ activity.time }}</span>
            </div>
            <div class="activity-status" :class="activity.status">
              {{ activity.statusText }}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Charts Section -->
    <section class="charts-section">
      <div class="container">
        <div class="charts-grid">
          <div class="chart-card">
            <div class="chart-header">
              <h3>Ventas de la Semana</h3>
              <div class="chart-filters">
                <button class="filter-btn active">7D</button>
                <button class="filter-btn">30D</button>
                <button class="filter-btn">90D</button>
              </div>
            </div>
            <div class="chart-placeholder">
              <i class="fas fa-chart-line"></i>
              <p>Gráfico de ventas aquí</p>
            </div>
          </div>
          
          <div class="chart-card">
            <div class="chart-header">
              <h3>Rifas Más Populares</h3>
            </div>
            <div class="popular-rifas">
              <div
                v-for="rifa in popularRifas"
                :key="rifa.id"
                class="popular-rifa-item"
              >
                <div class="rifa-info">
                  <h4>{{ rifa.nombre }}</h4>
                  <p>{{ rifa.tickets }} tickets vendidos</p>
                </div>
                <div class="rifa-progress">
                  <div class="progress-bar">
                    <div 
                      class="progress-fill" 
                      :style="{ width: rifa.percentage + '%' }"
                    ></div>
                  </div>
                  <span class="percentage">{{ rifa.percentage }}%</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AdminFooter />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminDashboard',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const stats = ref([
      {
        id: 1,
        label: 'Ventas Totales',
        value: 'S/. 45,230',
        change: '+12.5%',
        changeType: 'positive',
        changeIcon: 'fas fa-arrow-up',
        icon: 'fas fa-dollar-sign',
        color: 'linear-gradient(135deg, var(--success-green), #34d399)'
      },
      {
        id: 2,
        label: 'Rifas Activas',
        value: '8',
        change: '+2',
        changeType: 'positive',
        changeIcon: 'fas fa-arrow-up',
        icon: 'fas fa-ticket-alt',
        color: 'linear-gradient(135deg, var(--primary-purple), var(--primary-indigo))'
      },
      {
        id: 3,
        label: 'Usuarios Registrados',
        value: '1,247',
        change: '+8.3%',
        changeType: 'positive',
        changeIcon: 'fas fa-arrow-up',
        icon: 'fas fa-users',
        color: 'linear-gradient(135deg, var(--primary-blue), #3b82f6)'
      },
      {
        id: 4,
        label: 'Tickets Vendidos',
        value: '3,892',
        change: '+15.2%',
        changeType: 'positive',
        changeIcon: 'fas fa-arrow-up',
        icon: 'fas fa-chart-line',
        color: 'linear-gradient(135deg, var(--accent-orange), #f97316)'
      }
    ])

    const quickActions = ref([
      {
        id: 1,
        title: 'Crear Nueva Rifa',
        description: 'Configura una nueva rifa con todos los detalles',
        icon: 'fas fa-plus-circle',
        route: '/admin/rifas?action=create'
      },
      {
        id: 2,
        title: 'Gestionar Rifas',
        description: 'Ver, editar y administrar rifas existentes',
        icon: 'fas fa-cogs',
        route: '/admin/rifas'
      },
      {
        id: 3,
        title: 'Ver Usuarios',
        description: 'Administrar usuarios y sus perfiles',
        icon: 'fas fa-user-friends',
        route: '/admin/usuarios'
      },
      {
        id: 4,
        title: 'Reportes de Ventas',
        description: 'Analizar ventas y estadísticas detalladas',
        icon: 'fas fa-chart-bar',
        route: '/admin/ventas'
      }
    ])

    const recentActivity = ref([
      {
        id: 1,
        title: 'Nueva compra de ticket',
        description: 'Usuario Carlos Mendoza compró 3 tickets de iPhone 15',
        time: 'Hace 5 minutos',
        type: 'sale',
        icon: 'fas fa-shopping-cart',
        status: 'success',
        statusText: 'Completado'
      },
      {
        id: 2,
        title: 'Rifa finalizada',
        description: 'Rifa "MacBook Pro M3" ha sido sorteada',
        time: 'Hace 1 hora',
        type: 'rifa',
        icon: 'fas fa-trophy',
        status: 'completed',
        statusText: 'Finalizada'
      },
      {
        id: 3,
        title: 'Nuevo usuario registrado',
        description: 'María González se registró en la plataforma',
        time: 'Hace 2 horas',
        type: 'user',
        icon: 'fas fa-user-plus',
        status: 'info',
        statusText: 'Nuevo'
      },
      {
        id: 4,
        title: 'Pago verificado',
        description: 'Pago Yape de S/. 15 verificado correctamente',
        time: 'Hace 3 horas',
        type: 'payment',
        icon: 'fas fa-credit-card',
        status: 'success',
        statusText: 'Verificado'
      }
    ])

    const popularRifas = ref([
      {
        id: 1,
        nombre: 'iPhone 15 Pro Max',
        tickets: 450,
        percentage: 90
      },
      {
        id: 2,
        nombre: 'MacBook Pro M3',
        tickets: 280,
        percentage: 70
      },
      {
        id: 3,
        nombre: 'PlayStation 5',
        tickets: 320,
        percentage: 80
      },
      {
        id: 4,
        nombre: 'AirPods Pro',
        tickets: 180,
        percentage: 45
      }
    ])

    onMounted(() => {
      // Cargar datos del dashboard
      console.log('Dashboard de admin cargado')
    })

    return {
      stats,
      quickActions,
      recentActivity,
      popularRifas
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background: var(--gray-50);
}

.admin-hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 2rem 0;
}

.hero-content {
  text-align: center;
  margin-bottom: 2rem;
}

.hero-title {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.hero-subtitle {
  font-size: 1rem;
  opacity: 0.9;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.stat-label {
  color: var(--gray-600);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.stat-change {
  font-size: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.stat-change.positive {
  color: var(--success-green);
}

.quick-actions {
  padding: 2rem 0;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1.5rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.action-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  text-decoration: none;
  color: var(--gray-800);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.action-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
}

.action-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
  text-decoration: none;
  color: var(--gray-800);
}

.action-icon {
  width: 3.5rem;
  height: 3.5rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  margin-bottom: 1.5rem;
}

.action-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.action-description {
  color: var(--gray-600);
  margin-bottom: 1rem;
}

.action-arrow {
  color: var(--primary-purple);
  font-size: 1rem;
}

.recent-activity {
  padding: 3rem 0;
  background: white;
}

.activity-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.activity-list {
  space-y: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--gray-50);
  border-radius: var(--border-radius-lg);
  border-left: 4px solid var(--primary-purple);
  margin-bottom: 1rem;
}

.activity-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.activity-icon.sale {
  background: var(--success-green);
}

.activity-icon.rifa {
  background: var(--accent-orange);
}

.activity-icon.user {
  background: var(--primary-blue);
}

.activity-icon.payment {
  background: var(--primary-purple);
}

.activity-content {
  flex: 1;
}

.activity-title {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.activity-description {
  color: var(--gray-600);
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.activity-time {
  color: var(--gray-500);
  font-size: 0.75rem;
}

.activity-status {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
}

.activity-status.success {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-green);
}

.activity-status.completed {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning-yellow);
}

.activity-status.info {
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary-blue);
}

.charts-section {
  padding: 3rem 0;
}

.charts-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.chart-card {
  background: white;
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--gray-200);
}

.chart-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.chart-filters {
  display: flex;
  gap: 0.5rem;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--gray-300);
  background: white;
  border-radius: var(--border-radius);
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.filter-btn.active,
.filter-btn:hover {
  background: var(--primary-purple);
  color: white;
  border-color: var(--primary-purple);
}

.chart-placeholder {
  height: 300px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--gray-400);
  font-size: 3rem;
}

.chart-placeholder p {
  font-size: 1rem;
  margin-top: 1rem;
}

.popular-rifas {
  space-y: 1.5rem;
}

.popular-rifa-item {
  margin-bottom: 1.5rem;
}

.rifa-info {
  margin-bottom: 0.75rem;
}

.rifa-info h4 {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.rifa-info p {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.rifa-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  transition: width 0.3s ease;
}

.percentage {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  min-width: 3rem;
  text-align: right;
}

@media (max-width: 1024px) {
  .charts-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
    flex-direction: column;
    gap: 0.5rem;
  }

  .stats-grid,
  .actions-grid {
    grid-template-columns: 1fr;
  }

  .activity-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .activity-item {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
  }
}
</style>
