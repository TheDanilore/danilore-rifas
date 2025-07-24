import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { rifaService } from '@/services/rifaService'
import { useAuthStore } from '@/store/auth'

export function useRifaDetail() {
  const router = useRouter()
  const { isAuthenticated, user, purchaseTicket } = useAuthStore()
  const rifa = ref(null)
  const loading = ref(false)
  const error = ref(null)
  const paymentModal = ref(false)
  const paymentCode = ref('')
  const paymentLoading = ref(false)

  const loadRifa = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const rifaData = await rifaService.getRifaById(id)
      rifa.value = rifaData
    } catch (err) {
      error.value = err.message
      console.error('Error loading rifa:', err)
    } finally {
      loading.value = false
    }
  }

  const showPaymentModal = () => {
    // Verificar autenticación primero
    if (!isAuthenticated.value) {
      // Redirigir al login si no está autenticado
      router.push('/login')
      return
    }

    paymentModal.value = true
    paymentCode.value = rifaService.generatePaymentCode()
  }

  const closePaymentModal = () => {
    paymentModal.value = false
    paymentCode.value = ''
  }

  const confirmPayment = async () => {
    if (!rifa.value || !isAuthenticated.value) return

    paymentLoading.value = true
    
    try {
      const result = await rifaService.participateInRifa(rifa.value.id, {
        code: paymentCode.value
      })
      
      if (result.success) {
        // Generar número de ticket único
        const ticketNumber = Math.floor(Math.random() * 9999) + 1
        
        // Registrar la compra en el store del usuario
        purchaseTicket(rifa.value.id, {
          numero: ticketNumber,
          monto: rifa.value.precio,
          metodoPago: 'Yape'
        })
        
        // Recargar la rifa completa para obtener datos actualizados
        await loadRifa(rifa.value.id)
        closePaymentModal()
        return { success: true, message: '¡Participación confirmada! ✨ Si se completa la rifa, se desbloqueará automáticamente la siguiente.' }
      }
    } catch (err) {
      return { success: false, message: err.message }
    } finally {
      paymentLoading.value = false
    }
  }

  const getProgressPercentage = () => {
    if (!rifa.value) return 0
    return Math.min((rifa.value.ticketsVendidos / rifa.value.ticketsMinimos) * 100, 100)
  }

  // Función para obtener premios progresivos estructurados
  const getPremiosProgresivos = () => {
    if (!rifa.value) return []
    
    // Usar el mismo método que en useRifasWithFilters
    return rifaService.calcularEstadosPremios(rifa.value)
  }

  // Función para obtener el nivel actual activo
  const getNivelActual = () => {
    if (!rifa.value) return null
    
    const premiosProgresivos = getPremiosProgresivos()
    
    for (const premio of premiosProgresivos) {
      if (!premio.desbloqueado) continue
      
      for (const nivel of premio.niveles) {
        if (nivel.es_actual) {
          return {
            premio: premio.titulo,
            nivel: nivel.titulo,
            tickets_necesarios: nivel.tickets_necesarios,
            tickets_actuales: rifa.value.ticketsVendidos,
            tickets_restantes: Math.max(0, nivel.tickets_necesarios - rifa.value.ticketsVendidos),
            porcentaje: Math.min((rifa.value.ticketsVendidos / nivel.tickets_necesarios) * 100, 100)
          }
        }
      }
    }
    
    return null
  }

  return {
    rifa,
    loading,
    error,
    paymentModal,
    paymentCode,
    paymentLoading,
    loadRifa,
    showPaymentModal,
    closePaymentModal,
    confirmPayment,
    getProgressPercentage,
    getPremiosProgresivos,
    getNivelActual
  }
}
