import { ref, computed } from 'vue'

// Estado global de autenticación (usando el patrón singleton)
const isLoggedIn = ref(false)
const currentUser = ref(null)
const userTickets = ref([])
const userHistory = ref([])
const isAdminUser = ref(false)

export function useAuthStore() {
  const login = (userData) => {
    // Simular datos completos del usuario
    const fullUserData = {
      id: userData.id || Math.random().toString(36).substr(2, 9),
      nombre: userData.nombre,
      email: userData.email,
      telefono: userData.telefono,
      fechaRegistro: userData.fechaRegistro || new Date().toISOString(),
      avatar: userData.avatar || null,
      totalTickets: 0,
      rifasGanadas: 0,
      montoInvertido: 0,
      role: userData.role || 'user' // user o admin
    }

    isLoggedIn.value = true
    currentUser.value = fullUserData
    isAdminUser.value = fullUserData.role === 'admin'
    
    localStorage.setItem('isAuthenticated', 'true')
    localStorage.setItem('currentUser', JSON.stringify(fullUserData))
    localStorage.setItem('isAdmin', isAdminUser.value.toString())
    
    // Cargar datos del usuario
    loadUserData()
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

  const logout = () => {
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

  const checkAuthStatus = () => {
    const isAuth = localStorage.getItem('isAuthenticated') === 'true'
    const userData = localStorage.getItem('currentUser')
    const adminStatus = localStorage.getItem('isAdmin') === 'true'
    
    if (isAuth && userData) {
      isLoggedIn.value = true
      currentUser.value = JSON.parse(userData)
      isAdminUser.value = adminStatus
      loadUserData()
    }
  }

  const loadUserData = () => {
    // Cargar tickets del usuario
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
    adminLogin,
    logout,
    checkAuthStatus,
    purchaseTicket,
    updateUserProfile,
    getUserTicketsForRifa,
    loadUserData
  }
}
