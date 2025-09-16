import { ref, computed } from 'vue'
import { rifaService } from '@/services/api/rifaService'

export function useRifas() {
  const rifas = ref([])
  const rifasWithBlocked = ref([]) // Para mostrar rifas bloqueadas como preview
  const loading = ref(false)
  const error = ref(null)
  const currentFilter = ref('todas')

  const filteredRifas = computed(() => {
    if (currentFilter.value === 'todas') {
      return rifas.value
    }
    return rifas.value.filter(rifa => rifa.estado === currentFilter.value)
  })

  // Rifas disponibles + próxima rifa bloqueada (como preview)
  const rifasWithPreview = computed(() => {
    const disponibles = rifas.value
    const bloqueadas = rifasWithBlocked.value.filter(r => r.estado === 'bloqueada')
    
    // Agregar solo la primera rifa bloqueada como preview
    const proximaBloqueada = bloqueadas.find(r => {
      const requiredRifa = disponibles.find(d => d.id === r.requisito)
      return requiredRifa && requiredRifa.estado === 'en_venta'
    })

    return proximaBloqueada ? [...disponibles, { ...proximaBloqueada, isPreview: true }] : disponibles
  })

  const loadRifas = async () => {
    loading.value = true
    error.value = null
    
    try {
      rifas.value = await rifaService.getAllRifas()
      rifasWithBlocked.value = await rifaService.getAllRifasWithBlocked()
    } catch (err) {
      error.value = err.message
    } finally {
      loading.value = false
    }
  }

  const setFilter = (filter) => {
    currentFilter.value = filter
  }

  const getMensajeMotivacional = (rifa) => {
    // Si es una rifa de preview (bloqueada)
    if (rifa.isPreview) {
      return "🔒 Próximamente disponible"
    }

    const restantes = rifa.ticketsMinimos - rifa.ticketsVendidos
    if (restantes <= 0) {
      return "¡Sorteo confirmado!"
    } else if (restantes <= 5) {
      return `¡Solo ${restantes} tickets para confirmar!`
    } else if (restantes <= 10) {
      return `¡${restantes} tickets para confirmar!`
    } else {
      return `${restantes} tickets restantes`
    }
  }

  const getRifaUnlockStatus = async () => {
    try {
      return await rifaService.getRifasUnlockStatus()
    } catch (err) {
      console.error('Error obteniendo estado de desbloqueo:', err)
      return []
    }
  }

  return {
    rifas,
    rifasWithBlocked,
    rifasWithPreview,
    filteredRifas,
    loading,
    error,
    currentFilter,
    loadRifas,
    setFilter,
    getMensajeMotivacional,
    getRifaUnlockStatus
  }
}
