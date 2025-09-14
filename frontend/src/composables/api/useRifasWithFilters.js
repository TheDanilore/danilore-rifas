import { ref, computed, onMounted } from 'vue'
import { rifaService } from '@/services/rifaService'

export function useRifasWithFilters() {
  const rifas = ref([])
  const loading = ref(true)
  const error = ref(null)
  const selectedFilter = ref('actual')

  // Filtros disponibles
  const filters = [
    { value: 'actual', label: 'Rifa Actual', icon: 'fas fa-play-circle' },
    { value: 'futura', label: 'Rifas Futuras', icon: 'fas fa-calendar-alt' },
    { value: 'todas', label: 'Todas las Rifas', icon: 'fas fa-list' }
  ]

  // Rifas filtradas
  const filteredRifas = computed(() => {
    if (selectedFilter.value === 'todas') {
      return rifas.value
    }
    return rifas.value.filter(rifa => rifa.tipo === selectedFilter.value)
  })

  // Rifa actual (para mostrar en el hero)
  const rifaActual = computed(() => {
    return rifas.value.find(rifa => rifa.tipo === 'actual') || null
  })

  // Función para obtener rifa actual con premios procesados
  const getRifaActualWithPremios = async () => {
    const rifa = rifaActual.value
    if (!rifa) return null
    
    try {
      const rifaDetail = await rifaService.getRifaById(rifa.id)
      return rifaDetail
    } catch (err) {
      console.error('Error getting rifa actual with premios:', err)
      return rifa
    }
  }

  // Rifas futuras
  const rifasFuturas = computed(() => {
    return rifas.value.filter(rifa => rifa.tipo === 'futura')
  })

  // Función para cargar rifas
  const loadRifas = async () => {
    try {
      loading.value = true
      error.value = null
      
      // Cargar todas las rifas usando los endpoints enriquecidos
      const [rifasActuales, rifasFuturas] = await Promise.all([
        rifaService.getRifasActuales(),
        rifaService.getRifasFuturas()
      ])
      
      rifas.value = [...rifasActuales, ...rifasFuturas]
    } catch (err) {
      error.value = err.message
      console.error('Error loading rifas:', err)
    } finally {
      loading.value = false
    }
  }

  // Función para cambiar filtro
  const setFilter = (filterId) => {
    selectedFilter.value = filterId
  }

  // Función para obtener milestones de progreso (sincrona)
  const getProgressMilestones = (rifaId) => {
    if (!rifaId) return []
    
    const rifa = rifas.value.find(r => r.id === rifaId)
    if (!rifa) return []
    
    const premiosConEstados = rifaService.calcularEstadosPremios(rifa)
    const milestones = []
    
    for (const premio of premiosConEstados) {
      if (premio.niveles) {
        for (const nivel of premio.niveles) {
          milestones.push({
            tickets: nivel.tickets_necesarios,
            title: nivel.titulo,
            completed: rifa.ticketsVendidos >= nivel.tickets_necesarios,
            active: rifa.ticketsVendidos < nivel.tickets_necesarios && 
                   milestones.length === 0 || 
                   (milestones.length > 0 && milestones[milestones.length - 1].completed)
          })
        }
      }
    }
    
    return milestones.sort((a, b) => a.tickets - b.tickets)
  }

  // Función para calcular porcentaje de progreso
  const getProgressPercentage = (rifa) => {
    if (!rifa) return 0
    return Math.min((rifa.ticketsVendidos / rifa.ticketsMinimos) * 100, 100)
  }

  // Rifa actual con premios procesados (computed)
  const rifaActualConPremios = computed(() => {
    const rifa = rifaActual.value
    if (!rifa) return null
    
    // Procesar premios usando rifaService y devolver la rifa completa con premios procesados
    const premiosConEstados = rifaService.calcularEstadosPremios(rifa)
    return {
      ...rifa,
      premios: premiosConEstados
    }
  })

  // Función para obtener premios progresivos de una rifa (sincrona)
  const getPremiosProgresivos = (rifaId) => {
    if (!rifaId) return []
    
    const rifa = rifas.value.find(r => r.id === rifaId)
    if (!rifa) return []
    
    return rifaService.calcularEstadosPremios(rifa)
  }

  // Función para contar premios completados (sincrona)
  const getPremiosCompletados = (rifaId) => {
    if (!rifaId) return 0
    
    const premios = getPremiosProgresivos(rifaId)
    if (!Array.isArray(premios)) return 0
    
    return premios.filter(premio => premio.completado).length
  }

  // Función para obtener la siguiente meta de tickets
  const getSiguienteMeta = (rifaId) => {
    if (!rifaId) return 'N/A'
    
    const rifa = rifas.value.find(r => r.id === rifaId)
    if (!rifa) return 'N/A'
    
    const premiosConEstados = rifaService.calcularEstadosPremios(rifa)
    
    // Buscar el siguiente nivel no completado
    let siguienteTickets = null
    
    for (const premio of premiosConEstados) {
      if (premio.niveles) {
        for (const nivel of premio.niveles) {
          if (rifa.ticketsVendidos < nivel.tickets_necesarios) {
            if (!siguienteTickets || nivel.tickets_necesarios < siguienteTickets) {
              siguienteTickets = nivel.tickets_necesarios
            }
          }
        }
      }
    }
    
    return siguienteTickets ? `${siguienteTickets} tickets` : 'Completado'
  }

  // Función para formatear fechas
  const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('es-ES', { 
      year: 'numeric', 
      month: 'long', 
      day: 'numeric' 
    })
  }

  // Función para navegar a detalle de premio
  const navigateToDetail = (rifaId, premioId) => {
    // Esta función se implementará cuando sea necesaria
    console.log('Navigate to detail:', rifaId, premioId)
  }

  // Cargar datos al montar
  onMounted(() => {
    loadRifas()
  })

  return {
    rifas,
    loading,
    error,
    selectedFilter,
    currentFilter: selectedFilter, // Alias para compatibilidad con Home.vue
    filters,
    filteredRifas,
    rifaActual,
    rifaActualConPremios,
    rifasFuturas,
    loadRifas,
    setFilter,
    getProgressMilestones,
    getProgressPercentage,
    getPremiosProgresivos,
    getPremiosCompletados,
    getSiguienteMeta,
    getRifaActualWithPremios,
    formatDate,
    navigateToDetail
  }
}
