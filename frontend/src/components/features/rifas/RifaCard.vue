<template>
  <div class="rifa-card rifa-card-enhanced"
    :class="{ 'rifa-card--blocked': rifa.isPreview, 'rifa-card--preview': rifa.isPreview }" @click="navigateToDetail">
    <div class="rifa-image rifa-image-enhanced">
      <img :src="rifa.imagen" :alt="rifa.nombre" @error="handleImageError">
      <div class="rifa-status rifa-status-enhanced" :class="`status-${rifa.estado}`">
        {{ getEstadoTexto(rifa.estado) }}
      </div>
      <div v-if="rifa.isPreview" class="rifa-lock-overlay">
        <i class="fas fa-lock"></i>
        <span>Próximamente</span>
      </div>
      <div class="rifa-price rifa-price-badge" :class="{ 'price-disabled': rifa.isPreview }">
        S/ {{ rifa.precio }}
      </div>
    </div>

    <div class="rifa-content rifa-content-enhanced">
      <h3 class="rifa-title rifa-title-enhanced" :class="{ 'title-disabled': rifa.isPreview }">{{ rifa.nombre }}</h3>

      <div class="rifa-progress rifa-progress-enhanced" :class="{ 'progress-disabled': rifa.isPreview }">
        <div class="progress-header">
          <span class="progress-label progress-label-enhanced">Progreso</span>
          <span class="progress-value progress-value-enhanced">{{ rifa.ticketsVendidos }}/{{ rifa.ticketsMinimos
          }}</span>
        </div>
        <div class="progress-bar progress-bar-enhanced">
          <div class="progress-fill progress-fill-enhanced" :style="{ width: progressPercentage + '%' }"></div>
        </div>
        <p class="progress-message">{{ getMensajeMotivacional(rifa) }}</p>
      </div>

      <div class="rifa-meta rifa-meta-enhanced">
        <div class="rifa-prizes">
          <i class="fas fa-star"></i>
          <span>Premios: {{ rifa.premiosDesbloqueados || 0 }}/{{ rifa.totalPremios }}</span>
        </div>
        <div class="rifa-date">
          <i class="fas fa-clock"></i>
          <span>Sorteo: {{ formatDate(rifa.fechaSorteo) }}</span>
        </div>
      </div>

      <div class="rifa-action rifa-action-enhanced">
        <button class="btn rifa-btn rifa-btn-enhanced" :class="{ 'btn-disabled': rifa.isPreview }"
          :disabled="rifa.estado === 'cancelada' || rifa.estado === 'sorteada' || rifa.isPreview"
          @click.stop="handleAction">
          {{ rifa.isPreview ? '🔒 Bloqueada' : getButtonText(rifa.estado) }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { formatDate, getEstadoTexto, getButtonText } from '@/utils/helpers'

export default {
  name: 'RifaCard',
  props: {
    rifa: {
      type: Object,
      required: true
    }
  },
  emits: ['action'],
  setup(props, { emit }) {
    const router = useRouter()

    const progressPercentage = computed(() => {
      return Math.min((props.rifa.ticketsVendidos / props.rifa.ticketsMinimos) * 100, 100)
    })

    const getMensajeMotivacional = (rifa) => {
      // Si es una rifa de preview (bloqueada)
      if (rifa.isPreview) {
        return "🔒 Se desbloqueará al completar la rifa anterior"
      }

      const faltantes = rifa.ticketsMinimos - rifa.ticketsVendidos
      const nextMilestone = Math.ceil(rifa.ticketsVendidos / 50) * 50
      const faltantesNextPremio = nextMilestone - rifa.ticketsVendidos

      if (faltantes > 0) {
        return `🎯 ¡Faltan ${faltantes} tickets para confirmar el sorteo!`
      } else if (faltantesNextPremio > 0 && faltantesNextPremio <= 10) {
        return `🔥 ¡Faltan ${faltantesNextPremio} tickets para desbloquear el siguiente premio!`
      }
      return "🎉 ¡Sorteo confirmado! ¡Participa ahora!"
    }

    const navigateToDetail = () => {
      // No permitir navegación a rifas bloqueadas
      if (props.rifa.isPreview) {
        return
      }
      router.push(`/rifa/${props.rifa.id}`)
    }

    const handleAction = () => {
      // No permitir acciones en rifas bloqueadas
      if (props.rifa.isPreview || props.rifa.estado === 'cancelada') {
        return
      }
      emit('action', props.rifa)
    }

    const handleImageError = (event) => {
      event.target.src = 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop&crop=center'
    }

    return {
      progressPercentage,
      getMensajeMotivacional,
      navigateToDetail,
      handleAction,
      handleImageError,
      formatDate,
      getEstadoTexto,
      getButtonText
    }
  }
}
</script>

<style scoped>
.rifa-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  transition: all 0.3s ease;
  cursor: pointer;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.rifa-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-xl);
}

/* Estilos para rifas bloqueadas */
.rifa-card--blocked {
  opacity: 0.7;
  background: rgba(200, 200, 200, 0.3);
  cursor: not-allowed;
}

.rifa-card--blocked:hover {
  transform: none;
  box-shadow: var(--shadow-lg);
}

.rifa-card--preview {
  border: 2px dashed #9ca3af;
  position: relative;
}

.rifa-image {
  position: relative;
  overflow: hidden;
  height: 200px;
}

.rifa-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.rifa-card:hover .rifa-image img {
  transform: scale(1.1);
}

.rifa-card--blocked:hover .rifa-image img {
  transform: none;
}

/* Overlay para rifas bloqueadas */
.rifa-lock-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  z-index: 10;
}

.rifa-lock-overlay i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  color: #fbbf24;
}

.rifa-lock-overlay span {
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.rifa-status {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--white);
}

.status-en_venta {
  background: var(--primary-blue);
}

.status-confirmada {
  background: var(--success-green);
}

.status-sorteada {
  background: var(--primary-purple);
}

.status-cancelada {
  background: var(--danger-red);
}

.status-bloqueada {
  background: #6b7280;
}

.rifa-price {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  background: rgba(0, 0, 0, 0.7);
  color: var(--white);
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-weight: 700;
  font-size: 0.875rem;
}

.price-disabled {
  opacity: 0.5;
}

.rifa-content {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.rifa-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--gray-800);
  margin-bottom: 1rem;
  line-height: 1.4;
}

.title-disabled {
  color: var(--gray-500);
}

.rifa-progress {
  margin-bottom: 1rem;
  flex: 1;
}

.progress-disabled {
  opacity: 0.6;
}

.progress-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.progress-label {
  color: var(--gray-600);
  font-weight: 500;
}

.progress-value {
  font-weight: 700;
  color: var(--gray-800);
}

.progress-bar {
  width: 100%;
  height: 0.75rem;
  background: var(--gray-200);
  border-radius: var(--border-radius-full);
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--primary-blue), var(--success-green));
  border-radius: var(--border-radius-full);
  transition: width 0.3s ease;
}

.progress-message {
  font-size: 0.75rem;
  color: var(--gray-700);
  font-weight: 500;
  text-align: center;
  margin-top: 0.5rem;
}

.rifa-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.rifa-prizes,
.rifa-date {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--gray-600);
}

.rifa-prizes i {
  color: var(--warning-yellow);
  width: 1rem;
}

.rifa-date i {
  color: var(--gray-600);
  width: 1rem;
}

.rifa-action {
  margin-top: auto;
}

.rifa-btn {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: var(--white);
  border: none;
  border-radius: var(--border-radius-full);
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.rifa-btn:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.rifa-btn:disabled {
  background: var(--gray-400);
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-disabled {
  background: #6b7280 !important;
  color: #ffffff !important;
  cursor: not-allowed !important;
  opacity: 0.7;
}

.btn-disabled:hover {
  transform: none !important;
  box-shadow: none !important;
}
</style>
