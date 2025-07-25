import { ref, computed } from 'vue'
import { authService } from '@/services/authService.js'

// Estado global de autenticación (usando el patrón singleton)
const isLoggedIn = ref(false)
const currentUser = ref(null)
const userTickets = ref([])
const userHistory = ref([])
const isAdminUser = ref(false)

export function useAuthStore() {
  const login = async (userData) => {
    try {
      // Llamar al servicio de autenticación real
      const user = await authService.login(userData)
      
      // Formatear datos del usuario para el frontend
      const fullUserData = {
        id: user.id,
        nombre: user.name,
        email: user.email,
        telefono: user.telefono,
        fechaRegistro: user.created_at,
        avatar: user.avatar || null,
        totalTickets: 0,
        rifasGanadas: 0,
        montoInvertido: 0,
        role: user.rol || 'usuario',
        activo: user.activo
      }

      isLoggedIn.value = true
      currentUser.value = fullUserData
      isAdminUser.value = fullUserData.role === 'admin'
      
      localStorage.setItem('isAuthenticated', 'true')
      localStorage.setItem('currentUser', JSON.stringify(fullUserData))
      localStorage.setItem('isAdmin', isAdminUser.value.toString())
      
      // Cargar datos del usuario
      await loadUserData()
      
      return fullUserData
    } catch (error) {
      console.error('Error en login:', error)
      throw error
    }
  }

  const adminLogin = (adminData) => {
    const adminUserData = {
      id: 'admin-001',
      nombre: adminData.nombre || 'Administrador',
      email: adminData.email,
      role: 'admin',
      fechaRegistro: new Date().toISOString(),
      avatar: null
    }

    isLoggedIn.value = true
    currentUser.value = adminUserData
    isAdminUser.value = true
    
    localStorage.setItem('isAuthenticated', 'true')
    localStorage.setItem('currentUser', JSON.stringify(adminUserData))
    localStorage.setItem('isAdmin', 'true')
  }

  const logout = async () => {
    try {
      // Llamar al servicio de logout real
      await authService.logout()
    } catch (error) {
      console.error('Error en logout:', error)
    } finally {
      // Limpiar estado local independientemente del resultado
      isLoggedIn.value = false
      currentUser.value = null
      userTickets.value = []
      userHistory.value = []
      isAdminUser.value = false
      
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('currentUser')
      localStorage.removeItem('userTickets')
      localStorage.removeItem('userHistory')
      localStorage.removeItem('isAdmin')
    }
  }

  const register = async (userData) => {
    try {
      // Llamar al servicio de registro real
      const user = await authService.register({
        nombre: userData.nombre,
        email: userData.email,
        password: userData.password,
        password_confirmation: userData.password_confirmation,
        telefono: userData.telefono,
        tipo_documento: userData.tipo_documento,
        numero_documento: userData.numero_documento,
        fecha_nacimiento: userData.fecha_nacimiento,
        genero: userData.genero
      })
      
      // Formatear datos del usuario para el frontend
      const fullUserData = {
        id: user.id,
        nombre: user.name,
        email: user.email,
        telefono: user.telefono,
        fechaRegistro: user.created_at,
        avatar: user.avatar || null,
        totalTickets: 0,
        rifasGanadas: 0,
        montoInvertido: 0,
        role: user.rol || 'usuario',
        activo: user.activo
      }

      isLoggedIn.value = true
      currentUser.value = fullUserData
      isAdminUser.value = fullUserData.role === 'admin'
      
      localStorage.setItem('isAuthenticated', 'true')
      localStorage.setItem('currentUser', JSON.stringify(fullUserData))
      localStorage.setItem('isAdmin', isAdminUser.value.toString())
      
      return fullUserData
    } catch (error) {
      console.error('Error en registro:', error)
      throw error
    }
  }

  const checkAuthStatus = async () => {
    const token = authService.getToken()
    const userData = localStorage.getItem('currentUser')
    
    if (token && userData) {
      try {
        // Verificar si el token sigue siendo válido obteniendo el perfil
        const profile = await authService.getProfile()
        
        isLoggedIn.value = true
        currentUser.value = JSON.parse(userData)
        isAdminUser.value = profile.rol === 'admin'
        
        await loadUserData()
      } catch (error) {
        // Token inválido o expirado, limpiar datos
        console.error('Token inválido:', error)
        await logout()
      }
    }
  }

  const loadUserData = async () => {
    if (!currentUser.value) return
    
    try {
      // Cargar datos reales del perfil
      const profile = await authService.getProfile()
      
      // Actualizar datos del usuario con información fresca
      currentUser.value = {
        ...currentUser.value,
        ...profile
      }
      
      // Aquí podrías cargar las ventas del usuario usando ventaService
      // const ventas = await ventaService.obtenerMisVentas()
      // userTickets.value = ventas.data || []
      
    } catch (error) {
      console.error('Error cargando datos del usuario:', error)
    }
    
    // Mantener datos locales por ahora
    const tickets = localStorage.getItem('userTickets')
    if (tickets) {
      userTickets.value = JSON.parse(tickets)
    }

    // Cargar historial del usuario
    const history = localStorage.getItem('userHistory')
    if (history) {
      userHistory.value = JSON.parse(history)
    }
  }

  const purchaseTicket = (rifaId, ticketData) => {
    const ticket = {
      id: Math.random().toString(36).substr(2, 9),
      rifaId,
      numero: ticketData.numero,
      fechaCompra: new Date().toISOString(),
      monto: ticketData.monto,
      metodoPago: ticketData.metodoPago,
      estado: 'activo' // activo, ganador, perdedor
    }

    userTickets.value.push(ticket)
    
    // Actualizar historial
    const historyEntry = {
      id: ticket.id,
      tipo: 'compra',
      rifaId,
      descripcion: `Ticket #${ticket.numero} comprado`,
      fecha: ticket.fechaCompra,
      monto: ticket.monto
    }
    
    userHistory.value.unshift(historyEntry)

    // Actualizar stats del usuario
    if (currentUser.value) {
      currentUser.value.totalTickets += 1
      currentUser.value.montoInvertido += ticket.monto
    }

    // Guardar en localStorage
    localStorage.setItem('userTickets', JSON.stringify(userTickets.value))
    localStorage.setItem('userHistory', JSON.stringify(userHistory.value))
    localStorage.setItem('currentUser', JSON.stringify(currentUser.value))
  }

  const updateUserProfile = (profileData) => {
    if (currentUser.value) {
      currentUser.value = { ...currentUser.value, ...profileData }
      localStorage.setItem('currentUser', JSON.stringify(currentUser.value))
    }
  }

  const getUserTicketsForRifa = (rifaId) => {
    return userTickets.value.filter(ticket => ticket.rifaId === rifaId)
  }

  const isAuthenticated = computed(() => isLoggedIn.value)
  const user = computed(() => currentUser.value)
  const tickets = computed(() => userTickets.value)
  const history = computed(() => userHistory.value)
  const isAdmin = computed(() => isAdminUser.value)

  return {
    isAuthenticated,
    user,
    tickets,
    history,
    isAdmin,
    login,
    register,
    adminLogin,
    logout,
    checkAuthStatus,
    purchaseTicket,
    updateUserProfile,
    getUserTicketsForRifa,
    loadUserData
  }
}
