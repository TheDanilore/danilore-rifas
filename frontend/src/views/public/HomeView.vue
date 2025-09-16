<template>
  <div class="home-page">
    <AppHeader />
    
    <!-- Hero Section -->
    <section class="hero hero-enhanced">
      <div class="hero-bg"></div>
      <div class="container">
        <div class="hero-content hero-content-enhanced">
          <div class="hero-badge">
            <i class="fas fa-star"></i>
            <span>¡Nueva temporada de rifas!</span>
          </div>
          
          <h1 class="hero-title hero-title-enhanced">Danilore Rifas</h1>
          <p class="hero-subtitle hero-subtitle-enhanced">
            🎉 Participa, gana y celebra desde <span class="price-highlight">S/2</span>
          </p>
          
          <div class="hero-cta-group">
            <a href="#rifa-actual" class="cta-primary">
              <i class="fas fa-rocket"></i>
              Ver Rifas Activas
            </a>
            <router-link to="/como-funciona" class="cta-secondary">
              <i class="fas fa-info-circle"></i>
              ¿Cómo Funciona?
            </router-link>
          </div>
          
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
        <div class="filters filters-enhanced">
          <button 
            v-for="filter in filters" 
            :key="filter.value"
            class="filter-btn filter-btn-enhanced" 
            :class="{ active: currentFilter === filter.value }"
            @click="setFilter(filter.value)"
          >
            <i :class="filter.icon"></i>
            {{ filter.label }}
          </button>
        </div>

        <div v-if="loading" class="loading-state loading-state-enhanced">
          <i class="fas fa-spinner fa-spin"></i>
          <h3 class="state-title">Cargando rifas...</h3>
          <p class="state-message">Estamos buscando las mejores rifas para ti</p>
        </div>

        <div v-else-if="error" class="error-state error-state-enhanced">
          <i class="fas fa-exclamation-triangle"></i>
          <h3 class="state-title">¡Ups! Algo salió mal</h3>
          <p class="state-message">Error al cargar las rifas: {{ error }}</p>
          <button class="btn btn-primary cta-primary" @click="loadRifas" style="margin-top: var(--spacing-4);">
            <i class="fas fa-redo"></i>
            Reintentar
          </button>
        </div>

        <div v-else-if="filteredRifas.length === 0" class="empty-state empty-state-enhanced">
          <i class="fas fa-search"></i>
          <h3 class="state-title">No hay rifas disponibles</h3>
          <p class="state-message">No se encontraron rifas para el filtro seleccionado. Prueba con otro filtro o vuelve más tarde.</p>
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
                    <span class="progress-value">{{ rifa.progreso_general ? rifa.progreso_general.niveles_completados : 0 }}/{{ rifa.progreso_general ? rifa.progreso_general.total_niveles : 0 }}</span>
                  </div>
                  <div class="progress-details">
                    <div class="progress-detail-item">
                      <span class="detail-label">Niveles Completados:</span>
                      <span class="detail-value">{{ rifa.progreso_general ? rifa.progreso_general.niveles_completados : 0 }}</span>
                    </div>
                    <div class="progress-detail-item">
                      <span class="detail-label">Total Niveles:</span>
                      <span class="detail-value">{{ rifa.progreso_general ? rifa.progreso_general.total_niveles : 0 }}</span>
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
                          @click="handleMilestoneClick(milestone, $event)"
                        >
                          <div class="milestone-marker">
                            <i v-if="milestone.completed" class="fas fa-check"></i>
                            <i v-else-if="milestone.active" class="fas fa-clock"></i>
                            <i v-else class="fas fa-lock"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Modal de detalles del nivel posicionado -->
                    <div v-if="levelDetailModal" class="level-detail-tooltip" 
                         :style="{ 
                           left: `${modalPosition.x}px`, 
                           top: `${modalPosition.y}px`,
                           transform: 'translateX(-50%) translateY(-100%)'
                         }"
                         @click.stop>
                      <div class="tooltip-arrow"></div>
                      
                      <div class="tooltip-header">
                        <h4 class="tooltip-title">{{ selectedLevel?.premio_titulo }}</h4>
                        <button class="tooltip-close" @click="closeLevelDetailModal">
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                      
                      <div class="tooltip-content" v-if="selectedLevel">
                        <div class="tooltip-level-info">
                          <div class="tooltip-level-image" v-if="selectedLevel.imagen">
                            <img :src="selectedLevel.imagen" :alt="selectedLevel.nivel_titulo" @error="handleImageError">
                          </div>
                          
                          <div class="tooltip-level-details">
                            <h5 class="tooltip-level-title">{{ selectedLevel.nivel_titulo }}</h5>
                            <p class="tooltip-level-description" v-if="selectedLevel.nivel_descripcion">
                              {{ selectedLevel.nivel_descripcion }}
                            </p>
                            
                            <div class="tooltip-level-stats">
                              <div class="tooltip-stat-item">
                                <i class="fas fa-ticket-alt"></i>
                                <span>{{ selectedLevel.tickets }} tickets</span>
                              </div>
                              
                              <div class="tooltip-stat-item" v-if="selectedLevel.valor_aproximado">
                                <i class="fas fa-tag"></i>
                                <span>S/ {{ selectedLevel.valor_aproximado.toFixed(2) }}</span>
                              </div>
                              
                              <div class="tooltip-stat-item">
                                <i class="fas fa-flag"></i>
                                <span :class="{
                                  'status-completed': selectedLevel.completed,
                                  'status-active': selectedLevel.active,
                                  'status-pending': !selectedLevel.completed && !selectedLevel.active
                                }">
                                  <i v-if="selectedLevel.completed" class="fas fa-check"></i>
                                  <i v-else-if="selectedLevel.active" class="fas fa-clock"></i>
                                  <i v-else class="fas fa-lock"></i>
                                  {{ selectedLevel.completed ? 'Completado' : selectedLevel.active ? 'En Progreso' : 'Bloqueado' }}
                                </span>
                              </div>
                            </div>
                            
                            <div class="tooltip-specifications" v-if="selectedLevel.especificaciones && Object.keys(selectedLevel.especificaciones).length > 0">
                              <h6>Especificaciones:</h6>
                              <div class="tooltip-specs-grid">
                                <div v-for="(value, key) in selectedLevel.especificaciones" :key="key" class="tooltip-spec-item">
                                  <span class="tooltip-spec-label">{{ key.charAt(0).toUpperCase() + key.slice(1) }}:</span>
                                  <span class="tooltip-spec-value">{{ value }}</span>
                                </div>
                              </div>
                            </div>
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
                    <img 
                      :src="premio.imagen || premio.imagen_principal || getDefaultPremioImage()" 
                      :alt="premio.titulo" 
                      class="premio-image" 
                      @error="handleImageError"
                    >
                    <div v-if="!premio.desbloqueado" class="premio-overlay-blocked">
                      <div class="blocked-content">
                        <div class="blocked-icon">
                          <i class="fas fa-lock"></i>
                        </div>
                        <h3 class="blocked-title">BLOQUEADA</h3>
                        <p class="blocked-subtitle">PRÓXIMAMENTE</p>
                      </div>
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
                      class="premio-btn btn-primary"
                      @click.stop="handlePremioClick(rifa.id, premio)"
                    >
                      <i class="fas fa-eye"></i>
                      Ver Detalle
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Para rifas futuras, mostrar solo las rifas -->
          <div v-else class="rifas-futuras-container">
            <div class="rifas-grid rifas-grid-enhanced">
              <div 
                v-for="rifa in filteredRifas" 
                :key="rifa.id"
                class="rifa-card rifa-card-enhanced"
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

    <!-- Overlay para cerrar tooltip al hacer clic fuera -->
    <div v-if="levelDetailModal" class="tooltip-overlay" @click="closeLevelDetailModal"></div>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<script>
import { useRouter } from 'vue-router'
import { ref } from 'vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { useRifasWithFilters } from '@/composables/api/useRifasWithFilters'
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
    
    // Estado para modal de detalles del nivel
    const levelDetailModal = ref(false)
    const selectedLevel = ref(null)
    const modalPosition = ref({ x: 0, y: 0 })
    
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
      // Navegar directamente sin verificar autenticación (vista pública)
      const rifa = filteredRifas.value.find(r => r.id === rifaId)
      const rifaCodigo = rifa?.codigo_unico || rifa?.codigo || rifaId
      const codigoPremio = premio.codigo || `p${premio.orden}` || 'p1'
      
      console.log('Navegando a premio:', rifaCodigo, codigoPremio, premio)
      router.push(`/premio/${rifaCodigo}/${codigoPremio}`)
    }

    const handlePremioAction = (rifaId, premio) => {
      if (!isAuthenticated.value) {
        router.push('/login')
        return
      }

      const rifa = filteredRifas.value.find(r => r.id === rifaId)
      const rifaCodigo = rifa?.codigo_unico || rifa?.codigo || rifaId
      const codigoPremio = premio.codigo || `p${premio.orden}` || 'p1'
      router.push(`/premio/${rifaCodigo}/${codigoPremio}`)
    }

    const handleRifaClick = (rifa) => {
      // Navegar a RifaDetail usando el codigo_unico para consistencia
      console.log('Navegando a rifa:', rifa.codigo_unico || rifa.codigo || rifa.id)
      const identificador = rifa.codigo_unico || rifa.codigo || rifa.id
      router.push(`/rifa/${identificador}`)
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

    // Funciones para modal de detalles del nivel
    const handleMilestoneClick = (milestone, event) => {
      const rect = event.target.getBoundingClientRect()
      const containerRect = event.target.closest('.progress-with-milestones').getBoundingClientRect()
      
      // Calcular posición relativa al contenedor de progreso
      modalPosition.value = {
        x: rect.left - containerRect.left + (rect.width / 2),
        y: rect.top - containerRect.top - 10 // Un poco arriba del milestone
      }
      
      selectedLevel.value = milestone
      levelDetailModal.value = true
    }

    const closeLevelDetailModal = () => {
      levelDetailModal.value = false
      selectedLevel.value = null
    }

    // Obtener cantidad de premios completados
    const getPremiosCompletados = (rifaId) => {
      const rifa = filteredRifas.value.find(r => r.id === rifaId)
      if (!rifa || !rifa.progreso_general) {
        return 0
      }
      return rifa.progreso_general.niveles_completados || 0
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
      // Usar datos calculados del backend
      if (rifa.progreso_general) {
        return rifa.progreso_general.porcentaje || 0
      }
      
      // Fallback si no hay datos del backend
      const maxTickets = 1500 // Máximo basado en los datos reales
      return Math.min((rifa.ticketsVendidos / maxTickets) * 100, 100)
    }

    // Obtener hitos de progreso para la barra
    const getProgressMilestones = (rifaId) => {
      const rifa = filteredRifas.value.find(r => r.id === rifaId)
      if (!rifa || !rifa.progreso_general || !rifa.progreso_general.todos_los_niveles) {
        return []
      }

      const milestones = []
      const todosLosNiveles = rifa.progreso_general.todos_los_niveles
      const maxTickets = Math.max(...todosLosNiveles.map(n => n.tickets_necesarios))

      todosLosNiveles.forEach((nivel, index) => {
        const position = (nivel.tickets_necesarios / maxTickets) * 100
        const completed = nivel.completado
        const active = !completed && rifa.ticketsVendidos < nivel.tickets_necesarios

        milestones.push({
          id: `nivel-${nivel.id}`,
          title: `${nivel.premio_titulo} - ${nivel.titulo}`,
          tickets: nivel.tickets_necesarios,
          position: Math.min(position, 100),
          completed,
          active,
          // Datos adicionales para el tooltip
          premio_titulo: nivel.premio_titulo,
          nivel_titulo: nivel.titulo,
          nivel_descripcion: nivel.descripcion,
          valor_aproximado: nivel.valor_aproximado,
          imagen: nivel.imagen,
          especificaciones: nivel.especificaciones
        })
      })

      return milestones.sort((a, b) => a.tickets - b.tickets)
    }

    // Obtener imagen por defecto para premios
    const getDefaultPremioImage = () => {
      return 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=400&h=300&fit=crop&crop=center'
    }

    // Manejar errores de imagen
    const handleImageError = (event) => {
      // Imagen por defecto si falla la carga
      if (!event.target.src.includes('unsplash.com')) {
        event.target.src = getDefaultPremioImage()
      }
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
      getDefaultPremioImage,
      isAuthenticated,
      
      // Modal de detalles del nivel
      levelDetailModal,
      selectedLevel,
      modalPosition,
      handleMilestoneClick,
      closeLevelDetailModal,
      
      // Funciones actualizadas para usar datos del backend
      getPremiosCompletados: (rifaId) => {
        const rifa = filteredRifas.value.find(r => r.id === rifaId)
        if (!rifa || !rifa.progreso_general) {
          return 0
        }
        return rifa.progreso_general.niveles_completados || 0
      },
      
      getSiguienteMeta: (rifaId) => {
        const rifa = filteredRifas.value.find(r => r.id === rifaId)
        if (!rifa || !rifa.progreso_general || !rifa.progreso_general.todos_los_niveles) {
          return 'Sin datos'
        }
        
        const nivelesRestantes = rifa.progreso_general.todos_los_niveles
          .filter(n => !n.completado)
          .sort((a, b) => a.tickets_necesarios - b.tickets_necesarios)
        
        if (nivelesRestantes.length > 0) {
          return `${nivelesRestantes[0].tickets_necesarios} tickets`
        }
        
        return 'Meta alcanzada'
      },
      
      getProgressPercentage: (rifa) => {
        // Usar datos calculados del backend
        if (rifa.progreso_general) {
          return rifa.progreso_general.porcentaje || 0
        }
        
        // Fallback si no hay datos del backend
        const maxTickets = 1500 // Máximo basado en los datos reales
        return Math.min((rifa.ticketsVendidos / maxTickets) * 100, 100)
      },
      
      getProgressMilestones: (rifaId) => {
        const rifa = filteredRifas.value.find(r => r.id === rifaId)
        if (!rifa || !rifa.progreso_general || !rifa.progreso_general.todos_los_niveles) {
          return []
        }

        const milestones = []
        const todosLosNiveles = rifa.progreso_general.todos_los_niveles
        const maxTickets = Math.max(...todosLosNiveles.map(n => n.tickets_necesarios))

        todosLosNiveles.forEach((nivel, index) => {
          const position = (nivel.tickets_necesarios / maxTickets) * 100
          const completed = nivel.completado
          const active = !completed && rifa.ticketsVendidos < nivel.tickets_necesarios

          milestones.push({
            id: `nivel-${nivel.id}`,
            title: `${nivel.premio_titulo} - ${nivel.titulo}`,
            tickets: nivel.tickets_necesarios,
            position: Math.min(position, 100),
            completed,
            active,
            // Información adicional para el modal
            premio_titulo: nivel.premio_titulo,
            nivel_titulo: nivel.titulo,
            nivel_descripcion: nivel.descripcion || '',
            valor_aproximado: nivel.valor_aproximado || 0,
            especificaciones: nivel.especificaciones || {},
            imagen: nivel.imagen || ''
          })
        })

        return milestones.sort((a, b) => a.tickets - b.tickets)
      }
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
  position: relative; /* Necesario para posicionar el tooltip */
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

.milestone:hover .milestone-marker {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
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
  opacity: 0.9;
  cursor: not-allowed;
  position: relative;
}

.premio-card.premio-locked:hover {
  transform: none;
}

.premio-card.premio-locked .premio-image {
  filter: grayscale(0.3) brightness(0.8);
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

/* Nuevo overlay para premios bloqueados */
.premio-overlay-blocked {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.95));
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(4px);
  z-index: 5;
}

.blocked-content {
  text-align: center;
  color: white;
  padding: 2rem;
}

.blocked-icon {
  font-size: 3.5rem;
  margin-bottom: 1rem;
  color: #f59e0b;
  filter: drop-shadow(0 4px 8px rgba(245, 158, 11, 0.3));
}

.blocked-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
  color: white;
  letter-spacing: 3px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
  text-transform: uppercase;
}

.blocked-subtitle {
  font-size: 0.875rem;
  color: #d1d5db;
  margin: 0;
  font-weight: 500;
  letter-spacing: 1px;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
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

  .blocked-content {
    padding: 1.5rem;
  }

  .blocked-icon {
    font-size: 3rem;
    margin-bottom: 0.75rem;
  }

  .blocked-title {
    font-size: 1.125rem;
    letter-spacing: 2px;
  }

  .blocked-subtitle {
    font-size: 0.8rem;
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

/* Modal de detalles del nivel */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.level-detail-modal {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalSlideUp 0.3s ease-out;
}

@keyframes modalSlideUp {
  from {
    opacity: 0;
    transform: translateY(50px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: var(--white);
  border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--white);
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: rgba(255, 255, 255, 0.2);
}

.modal-content {
  padding: 1.5rem;
}

.level-info {
  display: grid;
  grid-template-columns: 1fr 2fr;
  gap: 1.5rem;
  align-items: start;
}

.level-image {
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.level-image img {
  width: 100%;
  height: auto;
  display: block;
}

.level-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.level-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

.level-description {
  color: var(--gray-600);
  line-height: 1.6;
  margin: 0;
}

.level-stats {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  border: 1px solid var(--gray-200);
}

.stat-item i {
  color: var(--primary-purple);
  width: 16px;
  text-align: center;
}

.stat-label {
  color: var(--gray-600);
  font-weight: 500;
  min-width: 120px;
}

.stat-value {
  font-weight: 600;
  color: var(--gray-800);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stat-value.status-completed {
  color: var(--success-green);
}

.stat-value.status-active {
  color: var(--primary-blue);
}

.stat-value.status-pending {
  color: var(--gray-500);
}

.level-specifications {
  margin-top: 1rem;
}

.level-specifications h5 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0 0 0.75rem 0;
}

.specs-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
}

.spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  border: 1px solid var(--gray-200);
}

.spec-label {
  color: var(--gray-600);
  font-weight: 500;
}

.spec-value {
  font-weight: 600;
  color: var(--gray-800);
}

/* Responsive para el modal */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 0.5rem;
  }

  .level-detail-modal {
    max-height: 95vh;
  }

  .level-info {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .level-image {
    max-width: 200px;
    margin: 0 auto;
  }

  .modal-header {
    padding: 1rem;
  }

  .modal-content {
    padding: 1rem;
  }

  .stat-label {
    min-width: auto;
    flex: 1;
  }
}

/* Tooltip de detalles del nivel */
.tooltip-overlay {
  position: fixed;
  inset: 0;
  background: transparent;
  z-index: 1000;
}

.level-detail-tooltip {
  position: absolute;
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  border: 1px solid var(--gray-200);
  padding: 0;
  z-index: 1001;
  width: 320px;
  max-width: 90vw;
}

.tooltip-arrow {
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  width: 16px;
  height: 16px;
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-top: none;
  border-left: none;
  transform: translateX(-50%) rotate(45deg);
}

.tooltip-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid var(--gray-200);
  background: var(--gray-50);
  border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
}

.tooltip-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--gray-900);
}

.tooltip-close {
  background: none;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: var(--border-radius-sm);
  transition: all 0.2s ease;
}

.tooltip-close:hover {
  background: var(--gray-200);
  color: var(--gray-700);
}

.tooltip-content {
  padding: 1rem;
}

.tooltip-level-info {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tooltip-level-image {
  width: 100%;
  height: 120px;
  overflow: hidden;
  border-radius: var(--border-radius-md);
  background: var(--gray-100);
}

.tooltip-level-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.tooltip-level-details {
  flex: 1;
}

.tooltip-level-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-900);
}

.tooltip-level-description {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  color: var(--gray-600);
  line-height: 1.4;
}

.tooltip-level-stats {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.tooltip-stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.tooltip-stat-item i {
  color: var(--primary-purple);
  width: 16px;
  text-align: center;
}

.status-completed {
  color: var(--success-green);
}

.status-active {
  color: var(--warning-yellow);
}

.status-pending {
  color: var(--gray-500);
}

.tooltip-specifications {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--gray-200);
}

.tooltip-specifications h6 {
  margin: 0 0 0.75rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
}

.tooltip-specs-grid {
  display: grid;
  gap: 0.5rem;
}

.tooltip-spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.75rem;
}

.tooltip-spec-label {
  font-weight: 500;
  color: var(--gray-600);
}

.tooltip-spec-value {
  font-weight: 600;
  color: var(--gray-900);
}

/* Responsive del tooltip */
@media (max-width: 768px) {
  .level-detail-tooltip {
    width: 280px;
  }
  
  .tooltip-header {
    padding: 0.75rem;
  }
  
  .tooltip-content {
    padding: 0.75rem;
  }
  
  .tooltip-level-image {
    height: 100px;
  }
}
</style>
