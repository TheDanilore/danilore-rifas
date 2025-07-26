import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { rifaService } from '@/services/rifaService'
import { useAuthStore } from '@/store/auth'

export function usePremioDetail() {
  const router = useRouter()
  const { isAuthenticated } = useAuthStore()
  
  const premio = ref(null)
  const rifa = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const loadPremioDetail = async (rifaId, codigoPremio) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await rifaService.getPremioDetail(rifaId, codigoPremio)
      premio.value = response.premio
      rifa.value = response.rifa
    } catch (err) {
      error.value = err.message || 'Error al cargar el detalle del premio'
      console.error('Error loading premio detail:', err)
    } finally {
      loading.value = false
    }
  }

  const handleCompra = () => {
    if (!isAuthenticated.value) {
      router.push('/login')
      return
    }
    
    // Lógica de compra aquí
    console.log('Iniciar proceso de compra para premio:', premio.value?.codigo)
  }

  const getNivelActual = () => {
    if (!premio.value?.niveles) return null
    return premio.value.niveles.find(nivel => nivel.es_actual)
  }

  const getNivelesCompletados = () => {
    if (!premio.value?.niveles) return []
    return premio.value.niveles.filter(nivel => nivel.completado)
  }

  const getNivelesPendientes = () => {
    if (!premio.value?.niveles) return []
    return premio.value.niveles.filter(nivel => !nivel.completado)
  }

  const getProgresoGeneral = () => {
    if (!premio.value?.niveles || premio.value.niveles.length === 0) return 0
    const completados = getNivelesCompletados().length
    const total = premio.value.niveles.length
    return Math.round((completados / total) * 100)
  }

  return {
    // Estado
    premio,
    rifa,
    loading,
    error,
    
    // Métodos
    loadPremioDetail,
    handleCompra,
    getNivelActual,
    getNivelesCompletados,
    getNivelesPendientes,
    getProgresoGeneral
  }
}
