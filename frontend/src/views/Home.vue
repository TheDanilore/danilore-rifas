<template>
  <div class="home-page">
    <AppHeader />
    
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-bg"></div>
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-trophy trophy-icon"></i>
            <h1 class="hero-title">Danilore Rifas</h1>
            <i class="fas fa-trophy trophy-icon"></i>
          </div>
          <p class="hero-subtitle">
            🎉 Participa, gana y celebra desde <span class="price-highlight">S/2</span>
          </p>
          <div class="hero-features">
            <div class="feature-badge">
              <i class="fas fa-bolt"></i>
              <span>Sorteos transparentes</span>
            </div>
            <div class="feature-badge">
              <i class="fas fa-gift"></i>
              <span>Premios progresivos</span>
            </div>
            <div class="feature-badge">
              <i class="fas fa-users"></i>
              <span>Comunidad activa</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Rifa Actual Section -->
    <section id="rifa-actual" class="rifa-actual-section">
      <div class="container">
        <!-- Filtros -->
        <div class="filters">
          <button 
            v-for="filter in filters" 
            :key="filter.value"
            class="filter-btn" 
            :class="{ active: currentFilter === filter.value }"
            @click="setFilter(filter.value)"
          >
            <i :class="filter.icon"></i>
            {{ filter.label }}
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando rifas...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <p>Error al cargar las rifas: {{ error }}</p>
          <button class="btn btn-primary" @click="loadRifas">Reintentar</button>
        </div>

        <div v-else-if="filteredRifas.length === 0" class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-search"></i>
          </div>
          <h3>No hay rifas disponibles</h3>
          <p>No se encontraron rifas para el filtro seleccionado.</p>
        </div>

        <!-- Mostrar rifas según el filtro -->
        <div v-else>
          <!-- Para rifa actual, mostrar rifa + premios -->
          <div v-if="currentFilter === 'actual'" class="rifa-actual-container">
            <div v-for="rifa in filteredRifas" :key="rifa.id" class="rifa-section">
              <!-- Header de la Rifa -->
              <div class="rifa-actual-header">
                <div class="rifa-info">
                  <h2 class="rifa-title">{{ rifa.nombre }}</h2>
                  <p class="rifa-description">{{ rifa.descripcion }}</p>
                  <div class="rifa-stats">
                    <div class="stat-item">
                      <i class="fas fa-ticket-alt"></i>
                      <span>{{ rifa.ticketsVendidos }}/{{ rifa.ticketsMinimos }} tickets</span>
                    </div>
                    <div class="stat-item">
                      <i class="fas fa-calendar"></i>
                      <span>Sorteo: {{ formatDate(rifa.fechaSorteo) }}</span>
                    </div>
                    <div class="stat-item">
                      <i class="fas fa-coins"></i>
                      <span>S/ {{ rifa.precio }} por ticket</span>
                    </div>
                  </div>
                </div>
                <div class="rifa-progress">
                  <div class="progress-info">
                    <span class="progress-label">Progreso General</span>
                    <span class="progress-value">{{ getPremiosCompletados(rifa.id) }}/{{ getPremiosProgresivos(rifa.id).length }}</span>
                  </div>
                  <div class="progress-details">
                    <div class="progress-detail-item">
                      <span class="detail-label">Premios Completados:</span>
                      <span class="detail-value">{{ getPremiosCompletados(rifa.id) }}</span>
                    </div>
                    <div class="progress-detail-item">
                      <span class="detail-label">Tickets Vendidos:</span>
                      <span class="detail-value">{{ rifa.ticketsVendidos }}</span>
                    </div>
                    <div class="progress-detail-item">
                      <span class="detail-label">Siguiente Meta:</span>
                      <span class="detail-value">{{ getSiguienteMeta(rifa.id) }}</span>
                    </div>
                  </div>
                  
                  <!-- Barra de progreso con premios -->
                  <div class="progress-with-milestones">
                    <div class="progress-bar-container">
                      <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: `${getProgressPercentage(rifa)}%` }"></div>
                      </div>
                      
                      <!-- Marcadores de premios en la barra -->
                      <div class="progress-milestones">
                        <div 
                          v-for="(milestone, index) in getProgressMilestones(rifa.id)" 
                          :key="milestone.id"
                          class="milestone"
                          :class="{
                            'milestone-completed': milestone.completed,
                            'milestone-active': milestone.active,
                            'milestone-pending': !milestone.completed && !milestone.active
                          }"
                          :style="{ left: `${milestone.position}%` }"
                          :title="`${milestone.title} - ${milestone.tickets} tickets`"
                        >
                          <div class="milestone-marker">
                            <i v-if="milestone.completed" class="fas fa-check"></i>
                            <i v-else-if="milestone.active" class="fas fa-clock"></i>
                            <i v-else class="fas fa-lock"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                  </div>
                </div>
              </div>

              <!-- Premios de esta rifa -->
              <div class="premios-grid">
                <div 
                  v-for="(premio, index) in getPremiosProgresivos(rifa.id)" 
                  :key="premio.id"
                  class="premio-card"
                  :class="{ 
                    'premio-active': premio.esta_activo,
                    'premio-completed': premio.completado,
                    'premio-locked': !premio.desbloqueado 
                  }"
                  @click="handlePremioClick(rifa.id, premio)"
                >
                  <!-- Badge de Estado -->
                  <div class="premio-status-badge" :class="{
                    'badge-active': premio.esta_activo,
                    'badge-completed': premio.completado,
                    'badge-locked': !premio.desbloqueado
                  }">
                    <i v-if="premio.completado" class="fas fa-check"></i>
                    <i v-else-if="!premio.desbloqueado" class="fas fa-lock"></i>
                    <i v-else class="fas fa-clock"></i>
                    {{ premio.estado_texto }}
                  </div>

                  <!-- Imagen del Premio -->
                  <div class="premio-image-container">
                    <img :src="premio.imagen" :alt="premio.titulo" class="premio-image" @error="handleImageError">
                    <div v-if="!premio.desbloqueado" class="premio-overlay">
                      <i class="fas fa-lock"></i>
                      <span>Bloqueado</span>
                    </div>
                  </div>

                  <!-- Información del Premio -->
                  <div class="premio-content">
                    <h3 class="premio-title">{{ premio.titulo }}</h3>
                    <p class="premio-description">{{ premio.descripcion }}</p>
                    
                    <!-- Progreso del Premio -->
                    <div v-if="premio.desbloqueado" class="premio-progress">
                      <div class="premio-stats">
                        <div class="premio-stat">
                          <span class="stat-number">{{ premio.niveles?.filter(n => n.desbloqueado).length || 0 }}</span>
                          <span class="stat-label">/ {{ premio.niveles?.length || 0 }} niveles</span>
                        </div>
                      </div>
                      
                      <!-- Nivel Actual -->
                      <div v-if="premio.esta_activo" class="nivel-actual">
                        <div v-for="nivel in premio.niveles" :key="nivel.id">
                          <div v-if="nivel.es_actual" class="nivel-progress">
                            <div class="nivel-header">
                              <span class="nivel-name">{{ nivel.titulo }}</span>
                              <span class="nivel-meta">{{ nivel.tickets_necesarios }} tickets</span>
                            </div>
                            <div class="nivel-info">
                              <span class="nivel-current">{{ rifa.ticketsVendidos }}</span>
                              <span class="nivel-separator">/</span>
                              <span class="nivel-target">{{ nivel.tickets_necesarios }}</span>
                            </div>
                            <div class="nivel-progress-bar">
                              <div class="progress-fill" :style="{ width: `${Math.min((rifa.ticketsVendidos / nivel.tickets_necesarios) * 100, 100)}%` }"></div>
                            </div>
                            
                          </div>
                        </div>
                      </div>
                      
                      <!-- Premio Completado -->
                      <div v-else-if="premio.completado" class="premio-completed-info">
                        <div class="completed-badge">
                          <i class="fas fa-check-circle"></i>
                          <span>¡Premio Completado!</span>
                        </div>
                        <p class="completed-message">Todos los niveles han sido desbloqueados</p>
                      </div>
                    </div>

                    <!-- Premio Bloqueado Info -->
                    <div v-else class="premio-locked-info">
                      <div class="locked-message">
                        <i class="fas fa-lock"></i>
                        <p>Se desbloqueará al completar: <strong>{{ premio.premio_requerido }}</strong></p>
                      </div>
                    </div>

                    <!-- Action Button -->
                    <button 
                      class="premio-btn"
                      :class="{
                        'btn-primary': premio.desbloqueado,
                        'btn-disabled': !premio.desbloqueado
                      }"
                      :disabled="!premio.desbloqueado"
                      @click.stop="handlePremioAction(rifa.id, premio)"
                    >
                      <i v-if="premio.desbloqueado" class="fas fa-eye"></i>
                      <i v-else class="fas fa-lock"></i>
                      {{ premio.desbloqueado ? 'Ver Detalle' : 'Bloqueado' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Para rifas futuras, mostrar solo las rifas -->
          <div v-else class="rifas-futuras-container">
            <div class="rifas-grid">
              <div 
                v-for="rifa in filteredRifas" 
                :key="rifa.id"
                class="rifa-card"
                @click="handleRifaClick(rifa)"
              >
                <!-- Imagen de la rifa -->
                <div class="rifa-image-container">
                  <img :src="rifa.imagen" :alt="rifa.nombre" class="rifa-image" @error="handleImageError">
                  <div class="rifa-badge" :class="{
                    'badge-actual': rifa.tipo === 'actual',
                    'badge-futura': rifa.tipo === 'futura'
                  }">
                    <i v-if="rifa.tipo === 'actual'" class="fas fa-play-circle"></i>
                    <i v-else class="fas fa-clock"></i>
                    {{ rifa.tipo === 'actual' ? 'En Progreso' : 'Próximamente' }}
                  </div>
                </div>

                <!-- Contenido de la rifa -->
                <div class="rifa-content">
                  <h3 class="rifa-title">{{ rifa.nombre }}</h3>
                  <p class="rifa-description">{{ rifa.descripcion }}</p>
                  
                  <div class="rifa-info">
                    <div class="info-item">
                      <i class="fas fa-calendar"></i>
                      <span>{{ formatDate(rifa.fechaSorteo) }}</span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-coins"></i>
                      <span>S/ {{ rifa.precio }} por ticket</span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-trophy"></i>
                      <span>{{ getPremiosProgresivos(rifa.id).length }} premios</span>
                    </div>
                  </div>

                  <button class="rifa-btn btn-secondary" @click.stop="handleRifaClick(rifa)">
                    <i class="fas fa-eye"></i>
                    Ver Próximos Premios
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<script>
import { useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useRifasWithFilters } from '@/composables/useRifasWithFilters'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'Home',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const router = useRouter()
    const { isAuthenticated } = useAuthStore()
    
    // Usar el composable de filtros
    const {
      rifas,
      filteredRifas,
      currentFilter,
      loading,
      error,
      filters,
      setFilter,
      loadRifas,
      getPremiosProgresivos
    } = useRifasWithFilters()

    // Cargar rifas al montar el componente
    loadRifas()

    // Métodos para navegación
    const handlePremioClick = (rifaId, premio) => {
      if (premio.desbloqueado) {
        router.push(`/premio/${rifaId}/${premio.id}`)
      }
    }

    const handlePremioAction = (rifaId, premio) => {
      if (!isAuthenticated.value) {
        router.push('/login')
        return
      }

      if (premio.desbloqueado) {
        router.push(`/premio/${rifaId}/${premio.id}`)
      }
    }

    const handleRifaClick = (rifa) => {
      // Navegar a RifaDetail para rifas futuras
      router.push(`/rifa/${rifa.id}`)
    }

    // Método para formatear fechas
    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      })
    }

    // Obtener cantidad de premios completados
    const getPremiosCompletados = (rifaId) => {
      const premios = getPremiosProgresivos(rifaId)
      return premios.filter(p => p.completado).length
    }

    // Obtener la siguiente meta de tickets
    const getSiguienteMeta = (rifaId) => {
      const premios = getPremiosProgresivos(rifaId)
      const premioActivo = premios.find(p => p.esta_activo)
      
      if (premioActivo && premioActivo.niveles) {
        const nivelActual = premioActivo.niveles.find(n => n.es_actual)
        if (nivelActual) {
          return `${nivelActual.tickets_necesarios} tickets`
        }
      }
      
      // Si no hay premio activo, buscar el primer premio no desbloqueado
      const siguientePremio = premios.find(p => !p.desbloqueado)
      if (siguientePremio && siguientePremio.niveles && siguientePremio.niveles.length > 0) {
        return `${siguientePremio.niveles[0].tickets_necesarios} tickets`
      }
      
      return 'Meta alcanzada'
    }

    // Obtener porcentaje de progreso general
    const getProgressPercentage = (rifa) => {
      const maxTickets = 75 // Máximo fijo para consistencia
      return Math.min((rifa.ticketsVendidos / maxTickets) * 100, 100)
    }

    // Obtener hitos de progreso para la barra
    const getProgressMilestones = (rifaId) => {
      const premios = getPremiosProgresivos(rifaId)
      const rifa = filteredRifas.value.find(r => r.id === rifaId)
      if (!rifa) return []

      const milestones = []
      const maxTickets = 75 // Máximo fijo

      premios.forEach(premio => {
        if (premio.niveles) {
          premio.niveles.forEach(nivel => {
            const position = (nivel.tickets_necesarios / maxTickets) * 100
            const completed = rifa.ticketsVendidos >= nivel.tickets_necesarios
            const active = !completed && rifa.ticketsVendidos < nivel.tickets_necesarios && premio.desbloqueado
            
            milestones.push({
              id: `${premio.id}-${nivel.id}`,
              title: `${premio.titulo} - ${nivel.nombre}`,
              tickets: nivel.tickets_necesarios,
              position: Math.min(position, 100),
              completed,
              active
            })
          })
        }
      })

      return milestones.sort((a, b) => a.tickets - b.tickets)
    }

    // Manejar errores de imagen
    const handleImageError = (event) => {
      // Imagen por defecto si falla la carga
      event.target.src = 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop&crop=center'
    }

    return {
      // Estado del composable
      rifas,
      filteredRifas,
      currentFilter,
      loading,
      error,
      filters,
      
      // Métodos del composable
      setFilter,
      loadRifas,
      getPremiosProgresivos,
      
      // Métodos locales
      handlePremioClick,
      handlePremioAction,
      handleRifaClick,
      formatDate,
      handleImageError,
      getPremiosCompletados,
      getSiguienteMeta,
      getProgressPercentage,
      getProgressMilestones,
      isAuthenticated
    }
  }
}
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* Hero Section */
.hero {
  position: relative;
  padding: 5rem 0;
  text-align: center;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(147, 51, 234, 0.1) 0%,
    rgba(236, 72, 153, 0.1) 50%,
    rgba(59, 130, 246, 0.1) 100%
  );
}

.hero-content {
  position: relative;
  max-width: 4xl;
  margin: 0 auto;
}

.hero-icons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.trophy-icon {
  font-size: 2rem;
  color: var(--warning-yellow);
}

.hero-title {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo), var(--accent-yellow));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle {
  font-size: clamp(1.25rem, 3vw, 1.5rem);
  color: var(--gray-700);
  margin-bottom: 2rem;
}

.price-highlight {
  font-weight: 700;
  color: var(--success-green);
}

.hero-features {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 1rem;
}

.feature-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-weight: 600;
}

.feature-badge i {
  font-size: 1.125rem;
  color: var(--primary-purple);
}

/* Rifa Actual Section */
.rifa-actual-section {
  padding: 2rem 0 5rem 0;
  background: var(--gray-50);
}

.rifa-actual-container {
  max-width: 1200px;
  margin: 0 auto;
}

.rifa-actual-header {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  padding: 2rem;
  margin-bottom: 3rem;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  align-items: center;
}

.rifa-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 1rem 0;
}

.rifa-description {
  color: var(--gray-600);
  font-size: 1.125rem;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.rifa-stats {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
}

.stat-item i {
  color: var(--primary-purple);
}

.rifa-progress {
  text-align: right;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.progress-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.progress-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-purple);
}

.progress-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.progress-detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  color: var(--gray-600);
}

.detail-value {
  font-weight: 600;
  color: var(--gray-800);
}

/* Barra de progreso con hitos */
.progress-with-milestones {
  margin-top: 1.5rem;
}

.progress-bar-container {
  position: relative;
  margin-bottom: 1rem;
  padding-top: 0.5rem;
}

.progress-bar {
  width: 100%;
  height: 12px;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
  position: relative;
  z-index: 1;
}

.progress-milestones {
  position: absolute;
  top: -10px;
  left: 0;
  right: 0;
  height: auto;
  z-index: 2;
}

.milestone {
  position: absolute;
  top: 0;
  transform: translateX(-50%);
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 3;
}

.milestone:hover {
  transform: translateX(-50%) translateY(-3px);
}

.milestone-marker {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  border: 2px solid var(--white);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  position: relative;
  z-index: 4;
  transition: all 0.3s ease;
}

.milestone-completed .milestone-marker {
  background: var(--success-green);
  color: var(--white);
}

.milestone-active .milestone-marker {
  background: var(--primary-blue);
  color: var(--white);
  animation: pulse 2s infinite;
}

.milestone-pending .milestone-marker {
  background: var(--gray-300);
  color: var(--gray-600);
}

@keyframes pulse {
  0% { 
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2), 0 0 0 0 rgba(59, 130, 246, 0.7); 
  }
  70% { 
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2), 0 0 0 8px rgba(59, 130, 246, 0); 
  }
  100% { 
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2), 0 0 0 0 rgba(59, 130, 246, 0); 
  }
}



.legend-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.legend-icon {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid var(--white);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.legend-icon.completed {
  background: var(--success-green);
}

.legend-icon.active {
  background: var(--primary-blue);
}

.legend-icon.pending {
  background: var(--gray-300);
}

.progress-bar {
  width: 100%;
  height: 1rem;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-purple), var(--primary-blue));
  border-radius: var(--border-radius-full);
  transition: width 0.3s ease;
}

/* Premios Grid */
.premios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.premio-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
}

.premio-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1);
}

.premio-card.premio-active {
  border: 2px solid var(--primary-blue);
  box-shadow: 0 0 0 1px var(--primary-blue);
}

.premio-card.premio-completed {
  border: 2px solid var(--success-green);
}

.premio-card.premio-locked {
  opacity: 0.7;
  cursor: not-allowed;
}

.premio-card.premio-locked:hover {
  transform: none;
}

.premio-status-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 10;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.badge-active {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #3b82f6;
}

.badge-completed {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #22c55e;
}

.badge-locked {
  background: #f3f4f6;
  color: #6b7280;
  border: 1px solid #d1d5db;
}

.premio-image-container {
  position: relative;
  height: 250px;
  overflow: hidden;
}

.premio-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.premio-card:hover .premio-image {
  transform: scale(1.05);
}

.premio-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--white);
  gap: 0.5rem;
}

.premio-overlay i {
  font-size: 2rem;
}

.premio-content {
  padding: 1.5rem;
}

.premio-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0 0 0.5rem 0;
}

.premio-description {
  color: var(--gray-600);
  font-size: 0.875rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.premio-progress {
  margin-bottom: 1rem;
}

.premio-stats {
  text-align: center;
  margin-bottom: 1rem;
}

.premio-stat {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 0.25rem;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: var(--primary-purple);
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.nivel-actual {
  background: var(--gray-50);
  border-radius: var(--border-radius);
  padding: 1rem;
  border: 1px solid var(--gray-200);
}

.nivel-progress {
  margin-bottom: 0.75rem;
}

.nivel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.nivel-name {
  font-weight: 600;
  color: var(--gray-800);
  font-size: 0.875rem;
}

.nivel-meta {
  font-size: 0.75rem;
  color: var(--primary-purple);
  font-weight: 600;
  background: rgba(139, 92, 246, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: var(--border-radius);
}

.nivel-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  margin-bottom: 0.75rem;
}

.nivel-current {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-blue);
}

.nivel-separator {
  font-size: 1.25rem;
  color: var(--gray-400);
}

.nivel-target {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-600);
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: var(--gray-600);
}

.detail-item i {
  color: var(--primary-purple);
}

.premio-completed-info {
  text-align: center;
  margin-bottom: 1rem;
}

.completed-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, var(--success-green), #16a34a);
  color: var(--white);
  padding: 0.75rem;
  border-radius: var(--border-radius);
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.completed-message {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.premio-locked-info {
  margin-bottom: 1rem;
}

.locked-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--gray-500);
  font-size: 0.875rem;
  text-align: center;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  border: 1px solid var(--gray-200);
}

.locked-message i {
  color: var(--gray-400);
}

.niveles-info {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.niveles-completed {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-purple);
}

.niveles-total {
  color: var(--gray-600);
}

.nivel-progress-bar {
  width: 100%;
  height: 0.5rem;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
}

.nivel-progress-bar .progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-blue), #3b82f6);
  border-radius: var(--border-radius-full);
  transition: width 0.3s ease;
}

.premio-locked-info {
  color: var(--gray-500);
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.premio-btn {
  width: 100%;
  padding: 0.75rem 1rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  color: var(--white);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(147, 51, 234, 0.4);
}

.btn-disabled {
  background: var(--gray-300);
  color: var(--gray-500);
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .rifa-actual-header {
    grid-template-columns: 1fr;
    gap: 1.5rem;
    text-align: center;
    padding: 1.5rem;
  }

  .rifa-title {
    font-size: 1.5rem;
  }

  .rifa-description {
    font-size: 1rem;
  }

  .rifa-stats {
    justify-content: center;
    gap: 1rem;
  }

  .rifa-progress {
    text-align: center;
  }

  .progress-details {
    font-size: 0.75rem;
  }

  .progress-detail-item {
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
  }

  .progress-bar-container {
    margin-bottom: 3rem;
    padding-top: 1.5rem;
  }

  .milestone-label {
    min-width: 70px;
    padding: 0.375rem;
  }

  .milestone-title {
    font-size: 0.625rem;
    max-width: 60px;
  }

  .milestone-tickets {
    font-size: 0.5rem;
    padding: 0.125rem 0.375rem;
  }

  .milestone-marker {
    width: 20px;
    height: 20px;
  }

  .legend-item {
    font-size: 0.75rem;
  }

  .premios-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .premio-card {
    margin: 0 auto;
    max-width: 400px;
  }
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.loading-state i {
  font-size: 3rem;
  color: var(--primary-purple);
  margin-bottom: 1rem;
}

.error-state i {
  font-size: 3rem;
  color: var(--danger-red);
  margin-bottom: 1rem;
}

.empty-state {
  text-align: center;
  padding: 5rem 0;
}

.empty-icon {
  font-size: 4rem;
  color: var(--gray-400);
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-600);
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: var(--gray-500);
}

/* Filtros */
.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  justify-content: center;
  flex-wrap: wrap;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: var(--white);
  border: 2px solid var(--gray-300);
  border-radius: var(--border-radius-lg);
  color: var(--gray-600);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.filter-btn:hover {
  border-color: var(--primary-purple);
  color: var(--primary-purple);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
}

.filter-btn.active {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-color: var(--primary-purple);
  color: var(--white);
  box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
}

.filter-btn.active:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
}

/* Rifas Futuras */
.rifas-futuras-container {
  margin-top: 2rem;
}

.rifas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  margin-top: 1rem;
}

.rifa-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  cursor: pointer;
  border: 2px solid transparent;
}

.rifa-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
  border-color: var(--primary-purple);
}

.rifa-image-container {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.rifa-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.rifa-card:hover .rifa-image {
  transform: scale(1.05);
}

.rifa-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  color: var(--white);
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.rifa-badge.badge-actual {
  background: linear-gradient(135deg, var(--primary-blue), var(--primary-indigo));
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.rifa-badge.badge-futura {
  background: linear-gradient(135deg, var(--warning-yellow), var(--warning-orange));
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.rifa-content {
  padding: 1.5rem;
}

.rifa-card .rifa-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--gray-900);
  margin-bottom: 0.5rem;
}

.rifa-card .rifa-description {
  color: var(--gray-600);
  line-height: 1.6;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.rifa-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--gray-600);
  font-size: 0.875rem;
}

.info-item i {
  color: var(--primary-purple);
  width: 16px;
}

.rifa-btn {
  width: 100%;
  padding: 0.75rem;
  background: var(--gray-100);
  color: var(--gray-600);
  border: 2px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.rifa-btn:hover {
  background: var(--primary-purple);
  color: var(--white);
  border-color: var(--primary-purple);
}

@media (max-width: 768px) {
  .hero {
    padding: 3rem 0;
  }

  .hero-title {
    font-size: 2.5rem;
  }

  .hero-subtitle {
    font-size: 1.125rem;
    margin-bottom: 1.5rem;
  }

  .hero-features {
    gap: 0.75rem;
  }

  .feature-badge {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .filters {
    gap: 0.75rem;
    margin-bottom: 2rem;
  }

  .filter-btn {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }

  .rifas-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .rifas-section {
    padding: 1.5rem 0 3rem 0;
  }
}

@media (max-width: 480px) {
  .hero {
    padding: 2rem 0;
  }

  .hero-title {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .price-highlight {
    font-size: 1.5rem;
  }

  .hero-features {
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
  }

  .feature-badge {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
    width: fit-content;
  }

  .filters {
    gap: 0.5rem;
    margin-bottom: 1.5rem;
  }

  .filter-btn {
    padding: 0.5rem 1rem;
    font-size: 0.8rem;
  }

  .rifas-section {
    padding: 1rem 0 2rem 0;
  }

  .loading-state,
  .error-state,
  .empty-state {
    padding: 2rem 1rem;
  }
}
</style>
