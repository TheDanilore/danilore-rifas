<template>
  <div class="dashboard-page">
    <!-- Hero Section -->
    <section class="dashboard-hero">
      <div class="container">
        <div class="hero-content">
          <div class="user-welcome">
            <div class="user-avatar-large">
              <i class="fas fa-user"></i>
            </div>
            <div class="welcome-text">
              <h1>¡Hola, {{ user?.nombre }}!</h1>
              <p>Bienvenido a tu dashboard personal</p>
            </div>
          </div>

          <div class="user-stats">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-ticket-alt"></i>
              </div>
              <div class="stat-info">
                <span class="stat-number">{{ user?.totalTickets || 0 }}</span>
                <span class="stat-label">Tickets Comprados</span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-trophy"></i>
              </div>
              <div class="stat-info">
                <span class="stat-number">{{ user?.rifasGanadas || 0 }}</span>
                <span class="stat-label">Rifas Ganadas</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Dashboard Navigation -->
    <section class="dashboard-nav">
      <div class="container">
        <div class="nav-cards">
          <router-link to="/perfil" class="nav-card">
            <div class="nav-card-icon">
              <i class="fas fa-user"></i>
            </div>
            <div class="nav-card-content">
              <h3>Mi Perfil</h3>
              <p>Gestiona tu información personal y configuración</p>
            </div>
            <div class="nav-card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </router-link>

          <router-link to="/mis-rifas" class="nav-card">
            <div class="nav-card-icon">
              <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="nav-card-content">
              <h3>Mis Rifas</h3>
              <p>Ve todos tus boletos y participa en rifas activas</p>
            </div>
            <div class="nav-card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </router-link>

          <router-link to="/historial" class="nav-card">
            <div class="nav-card-icon">
              <i class="fas fa-history"></i>
            </div>
            <div class="nav-card-content">
              <h3>Historial</h3>
              <p>Revisa tu actividad, compras y transacciones</p>
            </div>
            <div class="nav-card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Dashboard Content -->
    <section class="dashboard-content">
      <div class="container">
        <!-- Resumen de Actividad -->
        <div class="dashboard-overview">
          <div class="overview-header">
            <h2>Resumen de tu Actividad</h2>
            <p>Un vistazo rápido a tu participación en nuestras rifas</p>
          </div>

          <div class="overview-grid">
            <!-- Últimos Boletos -->
            <div class="overview-card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-ticket-alt"></i>
                  Últimos Boletos
                </h3>
                <router-link to="/mis-rifas" class="view-all-link">
                  Ver todos
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
              <div class="card-content">
                <div v-if="recentTickets.length === 0" class="empty-mini">
                  <i class="fas fa-ticket-alt"></i>
                  <span>No tienes boletos aún</span>
                  <router-link to="/" class="mini-cta">Comprar ahora</router-link>
                </div>
                <div v-else class="mini-list">
                  <div v-for="ticket in recentTickets.slice(0, 3)" :key="ticket.id" class="mini-item">
                    <div class="mini-info">
                      <span class="mini-title">Boleto #{{ ticket.numero }}</span>
                      <span class="mini-subtitle">{{ ticket.rifaNombre }}</span>
                    </div>
                    <div class="mini-status" :class="ticket.estado">
                      {{ getStatusText(ticket.estado) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="overview-card">
              <div class="card-header">
                <h3>
                  <i class="fas fa-clock"></i>
                  Actividad Reciente
                </h3>
                <router-link to="/historial" class="view-all-link">
                  Ver todo
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
              <div class="card-content">
                <div v-if="recentActivity.length === 0" class="empty-mini">
                  <i class="fas fa-history"></i>
                  <span>Sin actividad reciente</span>
                </div>
                <div v-else class="mini-list">
                  <div v-for="activity in recentActivity.slice(0, 3)" :key="activity.id" class="mini-item">
                    <div class="mini-info">
                      <span class="mini-title">{{ activity.descripcion }}</span>
                      <span class="mini-subtitle">{{ formatDate(activity.fecha) }}</span>
                    </div>
                    <div v-if="activity.monto" class="mini-amount">
                      S/ {{ activity.monto }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Rifas Activas -->
            <div class="overview-card full-width">
              <div class="card-header">
                <h3>
                  <i class="fas fa-fire"></i>
                  Rifas Activas
                </h3>
                <router-link to="/" class="view-all-link">
                  Ver todas
                  <i class="fas fa-arrow-right"></i>
                </router-link>
              </div>
              <div class="card-content">
                <div v-if="activeRifas.length === 0" class="empty-mini">
                  <i class="fas fa-trophy"></i>
                  <span>No hay rifas activas en este momento</span>
                </div>
                <div v-else class="rifas-mini-grid">
                  <div v-for="rifa in activeRifas.slice(0, 4)" :key="rifa.id" class="rifa-mini-card">
                    <div class="rifa-mini-image">
                      <img :src="rifa.imagen || '/images/default-rifa.jpg'" :alt="rifa.titulo" @error="handleImageError">
                    </div>
                    <div class="rifa-mini-info">
                      <h4>{{ rifa.titulo }}</h4>
                      <p class="rifa-mini-price">S/ {{ rifa.precio }} por boleto</p>
                      <div class="rifa-mini-progress">
                        <div class="progress-bar">
                          <div class="progress-fill" :style="{ width: rifa.progreso + '%' }"></div>
                        </div>
                        <span class="progress-text">{{ rifa.progreso }}% completado</span>
                      </div>
                    </div>
                    <router-link :to="`/rifa/${rifa.id}`" class="rifa-mini-action">
                      Participar
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useRifas } from '@/composables/api/useRifas'

export default {
  name: 'Dashboard',
  setup() {
    const router = useRouter()
    const { user, tickets, history, isAuthenticated } = useAuthStore()
    const { rifas } = useRifas()

    // Datos computados para mostrar resúmenes
    const recentTickets = computed(() => {
      return tickets.value?.slice(0, 3) || []
    })

    const recentActivity = computed(() => {
      return history.value?.slice(0, 3) || []
    })

    const activeRifas = computed(() => {
      return rifas.value?.filter(rifa => rifa.estado === 'activa').slice(0, 4) || []
    })

    onMounted(() => {
      // Verificar autenticación
      if (!isAuthenticated.value) {
        router.push('/login')
        return
      }
    })

    // Funciones de utilidad
    const getStatusText = (estado) => {
      const estados = {
        'activo': 'Activo',
        'ganador': 'Ganador',
        'perdedor': 'Perdedor',
        'pendiente': 'Pendiente'
      }
      return estados[estado] || estado
    }

    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const handleImageError = (event) => {
      event.target.src = '/images/default-rifa.jpg'
    }

    return {
      user,
      recentTickets,
      recentActivity,
      activeRifas,
      getStatusText,
      formatDate,
      handleImageError
    }
  }
}
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--gray-50);
}

/* Dashboard Hero */
.dashboard-hero {
  background: linear-gradient(135deg, var(--primary-purple) 0%, var(--primary-indigo) 50%, var(--primary-blue) 100%);
  padding: 2rem 0;
  color: white;
}

.hero-content {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 2rem;
  align-items: center;
}

.user-welcome {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar-large {
  width: 4rem;
  height: 4rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.welcome-text h1 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.welcome-text p {
  margin: 0;
  opacity: 0.9;
  color: var(--gray-100);
}

.user-stats {
  display: flex;
  gap: 1rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.1);
  border-radius: var(--border-radius-lg);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  backdrop-filter: blur(10px);
  min-width: 140px;
}

.stat-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.stat-number {
  display: block;
  font-size: 1.25rem;
  font-weight: 700;
}

.stat-label {
  display: block;
  font-size: 0.75rem;
  opacity: 0.8;
}

/* Dashboard Navigation */
.dashboard-nav {
  background: var(--white);
  border-bottom: 1px solid var(--gray-200);
  padding: 2rem 0;
}

.nav-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
}

.nav-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 2px solid var(--gray-100);
  display: flex;
  align-items: center;
  gap: 1rem;
  text-decoration: none;
  color: inherit;
  transition: all 0.3s ease;
  cursor: pointer;
}

.nav-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
  border-color: var(--primary-purple);
}

.nav-card-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: var(--border-radius-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.nav-card-content {
  flex: 1;
}

.nav-card-content h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0 0 0.25rem 0;
}

.nav-card-content p {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin: 0;
}

.nav-card-arrow {
  color: var(--gray-400);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.nav-card:hover .nav-card-arrow {
  color: var(--primary-purple);
  transform: translateX(4px);
}

/* Dashboard Content */
.dashboard-content {
  flex: 1;
  padding: 2rem 0;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.content-header {
  text-align: center;
  margin-bottom: 2rem;
}

.content-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.content-header p {
  color: var(--gray-600);
  font-size: 1.125rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  background: var(--gray-200);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  font-size: 1.5rem;
  color: var(--gray-500);
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: var(--gray-600);
  margin-bottom: 2rem;
}

/* Tickets Grid */
.tickets-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.ticket-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.ticket-card:hover {
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.ticket-winner {
  border-color: var(--warning-yellow);
  background: linear-gradient(135deg, #fef3cd, #fff);
}

.ticket-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.ticket-number {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--gray-800);
}

.ticket-status {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.ticket-status.activo {
  background: #dbeafe;
  color: #1d4ed8;
}

.ticket-status.ganador {
  background: #fef3cd;
  color: #d97706;
}

.ticket-status.perdedor {
  background: #fecaca;
  color: #dc2626;
}

.ticket-info {
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.info-label {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.info-value {
  color: var(--gray-800);
  font-weight: 500;
  font-size: 0.875rem;
}

.ticket-actions {
  text-align: center;
}

/* History List */
.history-list {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}

.history-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
}

.history-item:last-child {
  border-bottom: none;
}

.history-icon {
  width: 2.5rem;
  height: 2.5rem;
  background: var(--gray-100);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray-600);
  flex-shrink: 0;
}

.history-content {
  flex: 1;
}

.history-title {
  font-weight: 500;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.history-date {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.history-amount {
  font-weight: 600;
  color: var(--primary-purple);
}

/* Profile Form */
.profile-content {
  max-width: 600px;
  margin: 0 auto;
}

.profile-form {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-sm);
}

.form-section {
  margin-bottom: 2rem;
}

.form-section h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.input-group {
  margin-bottom: 1rem;
}

.input-group label {
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
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.info-readonly {
  background: var(--gray-50);
  border-radius: var(--border-radius);
  padding: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-label {
  color: var(--gray-600);
  font-weight: 500;
}

.info-value {
  color: var(--gray-800);
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
}

.form-actions {
  text-align: center;
  padding-top: 1rem;
  border-top: 1px solid var(--gray-200);
}

/* Dashboard Overview */
.dashboard-overview {
  max-width: 1200px;
  margin: 0 auto;
}

.overview-header {
  text-align: center;
  margin-bottom: 3rem;
}

.overview-header h2 {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-800);
  margin-bottom: 0.5rem;
}

.overview-header p {
  color: var(--gray-600);
  font-size: 1.125rem;
}

.overview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.overview-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  border: 1px solid var(--gray-100);
}

.overview-card.full-width {
  grid-column: 1 / -1;
}

.card-header {
  padding: 1.5rem 1.5rem 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-header h3 i {
  color: var(--primary-purple);
}

.view-all-link {
  color: var(--primary-purple);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  transition: all 0.3s ease;
}

.view-all-link:hover {
  color: var(--primary-indigo);
}

.card-content {
  padding: 1.5rem;
}

.empty-mini {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--gray-500);
}

.empty-mini i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  color: var(--gray-400);
}

.mini-cta {
  display: inline-block;
  margin-top: 0.5rem;
  color: var(--primary-purple);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.875rem;
}

.mini-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mini-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
}

.mini-info {
  flex: 1;
}

.mini-title {
  display: block;
  font-weight: 500;
  color: var(--gray-800);
  font-size: 0.875rem;
}

.mini-subtitle {
  display: block;
  color: var(--gray-600);
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

.mini-status {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.mini-status.activo {
  background: #dbeafe;
  color: #1d4ed8;
}

.mini-status.ganador {
  background: #fef3cd;
  color: #d97706;
}

.mini-status.perdedor {
  background: #fecaca;
  color: #dc2626;
}

.mini-amount {
  font-weight: 600;
  color: var(--primary-purple);
  font-size: 0.875rem;
}

/* Rifas Mini Grid */
.rifas-mini-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
}

.rifa-mini-card {
  background: var(--gray-50);
  border-radius: var(--border-radius);
  overflow: hidden;
  border: 1px solid var(--gray-200);
  transition: all 0.3s ease;
}

.rifa-mini-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.rifa-mini-image {
  width: 100%;
  height: 120px;
  overflow: hidden;
}

.rifa-mini-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.rifa-mini-info {
  padding: 1rem;
}

.rifa-mini-info h4 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.rifa-mini-price {
  color: var(--primary-purple);
  font-weight: 600;
  font-size: 0.875rem;
  margin: 0 0 1rem 0;
}

.rifa-mini-progress {
  margin-bottom: 1rem;
}

.progress-bar {
  width: 100%;
  height: 6px;
  background: var(--gray-200);
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-purple), var(--primary-indigo));
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.75rem;
  color: var(--gray-600);
}

.rifa-mini-action {
  display: block;
  width: 100%;
  padding: 0.5rem;
  background: var(--primary-purple);
  color: white;
  text-decoration: none;
  text-align: center;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
}

.rifa-mini-action:hover {
  background: var(--primary-indigo);
}

/* Responsive */
@media (max-width: 768px) {
  .hero-content {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .user-stats {
    flex-direction: column;
  }

  .stat-card {
    min-width: auto;
  }

  .nav-cards {
    grid-template-columns: 1fr;
  }

  .overview-grid {
    grid-template-columns: 1fr;
  }

  .rifas-mini-grid {
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  }

  .user-welcome {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
}
</style>
