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
        <div class="nav-tabs">
          <button class="nav-tab" :class="{ active: activeTab === 'tickets' }" @click="activeTab = 'tickets'">
            <i class="fas fa-ticket-alt"></i>
            Mis Tickets
          </button>
          <button class="nav-tab" :class="{ active: activeTab === 'history' }" @click="activeTab = 'history'">
            <i class="fas fa-history"></i>
            Historial
          </button>
          <button class="nav-tab" :class="{ active: activeTab === 'profile' }" @click="activeTab = 'profile'">
            <i class="fas fa-user"></i>
            Mi Perfil
          </button>
        </div>
      </div>
    </section>

    <!-- Dashboard Content -->
    <section class="dashboard-content">
      <div class="container">
        <!-- Mis Tickets -->
        <div v-if="activeTab === 'tickets'" class="tab-content">
          <div class="content-header">
            <h2>Mis Tickets</h2>
            <p>Todos los tickets que has comprado en nuestras rifas</p>
          </div>

          <div v-if="tickets.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="fas fa-ticket-alt"></i>
            </div>
            <h3>No tienes tickets aún</h3>
            <p>¡Participa en nuestras rifas y aparecerán aquí!</p>
            <router-link to="/" class="btn btn-primary">
              <i class="fas fa-plus"></i>
              &nbsp;
              Comprar Tickets
            </router-link>
          </div>

          <div v-else class="tickets-grid">
            <div v-for="ticket in tickets" :key="ticket.id" class="ticket-card"
              :class="{ 'ticket-winner': ticket.estado === 'ganador' }">
              <div class="ticket-header">
                <div class="ticket-number">
                  Ticket #{{ ticket.numero }}
                </div>
                <div class="ticket-status" :class="ticket.estado">
                  <i :class="getStatusIcon(ticket.estado)"></i>
                  {{ getStatusText(ticket.estado) }}
                </div>
              </div>

              <div class="ticket-info">
                <div class="info-row">
                  <span class="info-label">Rifa:</span>
                  <span class="info-value">{{ getRifaName(ticket.rifaId) }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Fecha:</span>
                  <span class="info-value">{{ formatDate(ticket.fechaCompra) }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Monto:</span>
                  <span class="info-value">S/ {{ ticket.monto }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Pago:</span>
                  <span class="info-value">{{ ticket.metodoPago }}</span>
                </div>
              </div>

              <div class="ticket-actions">
                <button class="btn btn-outline btn-sm" @click="viewRifaDetails(ticket.rifaId)">
                  <i class="fas fa-eye"></i>
                  Ver Rifa
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial -->
        <div v-if="activeTab === 'history'" class="tab-content">
          <div class="content-header">
            <h2>Historial de Actividades</h2>
            <p>Registro completo de todas tus actividades</p>
          </div>

          <div v-if="history.length === 0" class="empty-state">
            <div class="empty-icon">
              <i class="fas fa-history"></i>
            </div>
            <h3>Sin actividad aún</h3>
            <p>Tu historial aparecerá aquí cuando empieces a participar</p>
          </div>

          <div v-else class="history-list">
            <div v-for="entry in history" :key="entry.id" class="history-item">
              <div class="history-icon">
                <i :class="getHistoryIcon(entry.tipo)"></i>
              </div>

              <div class="history-content">
                <div class="history-title">{{ entry.descripcion }}</div>
                <div class="history-date">{{ formatDate(entry.fecha) }}</div>
              </div>

              <div class="history-amount" v-if="entry.monto">
                <span class="amount">S/ {{ entry.monto }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Mi Perfil -->
        <div v-if="activeTab === 'profile'" class="tab-content">
          <div class="content-header">
            <h2>Mi Perfil</h2>
            <p>Administra tu información personal</p>
          </div>

          <div class="profile-content">
            <div class="profile-form">
              <form @submit.prevent="updateProfile">
                <div class="form-section">
                  <h3>Información Personal</h3>

                  <div class="input-group">
                    <label>Nombre Completo</label>
                    <input type="text" class="form-input" v-model="profileForm.nombre" required>
                  </div>

                  <div class="input-group">
                    <label>Email</label>
                    <input type="email" class="form-input" v-model="profileForm.email" required>
                  </div>

                  <div class="input-group">
                    <label>Teléfono</label>
                    <input type="tel" class="form-input" v-model="profileForm.telefono" required>
                  </div>
                </div>

                <div class="form-section">
                  <h3>Información de la Cuenta</h3>

                  <div class="info-readonly">
                    <div class="info-item">
                      <span class="info-label">Fecha de Registro:</span>
                      <span class="info-value">{{ formatDate(user?.fechaRegistro) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="info-label">ID de Usuario:</span>
                      <span class="info-value">{{ user?.id }}</span>
                    </div>
                  </div>
                </div>

                <div class="form-actions">
                  <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    &nbsp;
                    Guardar Cambios
                  </button>
                </div>
              </form>
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
    const { user, tickets, history, isAuthenticated, updateUserProfile } = useAuthStore()
    const { rifas } = useRifas()

    const activeTab = ref('tickets')
    const profileForm = ref({
      nombre: '',
      email: '',
      telefono: ''
    })

    onMounted(() => {
      // Verificar autenticación
      if (!isAuthenticated.value) {
        router.push('/login')
        return
      }

      // Inicializar formulario de perfil
      if (user.value) {
        profileForm.value = {
          nombre: user.value.nombre || '',
          email: user.value.email || '',
          telefono: user.value.telefono || ''
        }
      }
    })

    const getRifaName = (rifaId) => {
      const rifa = rifas.value.find(r => r.id === rifaId)
      return rifa ? rifa.titulo : 'Rifa no encontrada'
    }

    const getStatusIcon = (status) => {
      switch (status) {
        case 'ganador': return 'fas fa-crown'
        case 'perdedor': return 'fas fa-times-circle'
        default: return 'fas fa-clock'
      }
    }

    const getStatusText = (status) => {
      switch (status) {
        case 'ganador': return 'Ganador'
        case 'perdedor': return 'No ganó'
        default: return 'En proceso'
      }
    }

    const getHistoryIcon = (tipo) => {
      switch (tipo) {
        case 'compra': return 'fas fa-shopping-cart'
        case 'ganancia': return 'fas fa-trophy'
        case 'reembolso': return 'fas fa-undo'
        default: return 'fas fa-circle'
      }
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const viewRifaDetails = (rifaId) => {
      router.push(`/rifa/${rifaId}`)
    }

    const updateProfile = () => {
      updateUserProfile(profileForm.value)
      // Mostrar mensaje de éxito (podrías usar una librería de toasts)
      alert('Perfil actualizado correctamente')
    }

    return {
      user,
      tickets,
      history,
      activeTab,
      profileForm,
      getRifaName,
      getStatusIcon,
      getStatusText,
      getHistoryIcon,
      formatDate,
      viewRifaDetails,
      updateProfile
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
  padding: 0;
}

.nav-tabs {
  display: flex;
  gap: 0;
}

.nav-tab {
  flex: 1;
  padding: 1rem 2rem;
  border: none;
  background: transparent;
  color: var(--gray-600);
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  border-bottom: 3px solid transparent;
}

.nav-tab:hover {
  background: var(--gray-50);
  color: var(--gray-800);
}

.nav-tab.active {
  color: var(--primary-purple);
  border-bottom-color: var(--primary-purple);
  background: var(--gray-50);
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

  .nav-tabs {
    flex-direction: column;
  }

  .nav-tab {
    justify-content: flex-start;
    padding: 1rem 1.5rem;
  }

  .tickets-grid {
    grid-template-columns: 1fr;
  }

  .user-welcome {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
}
</style>
