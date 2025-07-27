// Utilidades de formato de fecha
export const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

export const formatDateTime = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

export const getTimeRemaining = (dateString) => {
  const now = new Date()
  const target = new Date(dateString)
  const diff = target - now

  if (diff <= 0) return "Expirado"

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))

  if (days > 0) {
    return `${days} día${days > 1 ? 's' : ''}`
  } else if (hours > 0) {
    return `${hours} hora${hours > 1 ? 's' : ''}`
  } else {
    return "Menos de 1 hora"
  }
}

// Utilidades de formato de moneda
export const formatCurrency = (amount) => {
  return `S/ ${amount.toFixed(2)}`
}

export const formatNumber = (number) => {
  return number.toLocaleString('es-PE')
}

// Utilidades de validación
export const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

export const validatePhone = (phone) => {
  const phoneRegex = /^\+?[\d\s-()]+$/
  return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 9
}

// Utilidades de estado
export const getEstadoTexto = (estado) => {
  const estados = {
    borrador: "Borrador",
    activa: "En Progreso",
    pausada: "Pausada",
    finalizada: "Finalizada",
    cancelada: "Cancelada",
    bloqueada: "Bloqueada",
    proximamente: "Próximamente",
    // Estados legacy para compatibilidad
    en_venta: "En Progreso",
    confirmada: "Finalizada",
    sorteada: "Finalizada"
  }
  return estados[estado] || estado
}

export const getButtonText = (estado) => {
  const buttonTexts = {
    borrador: "No Disponible",
    activa: "Participar Ahora",
    pausada: "Pausada",
    finalizada: "Ver Sorteo",
    cancelada: "No Disponible",
    bloqueada: "🔒 Bloqueada",
    // Estados legacy para compatibilidad
    en_venta: "Participar Ahora",
    confirmada: "Ver Sorteo",
    sorteada: "Ver Ganador"
  }
  return buttonTexts[estado] || "Participar"
}

// Utilidades de UI
export const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch (err) {
    console.error('Error copying to clipboard:', err)
    return false
  }
}

export const showNotification = (message, type = 'info') => {
  // Esta función se puede expandir con una librería de notificaciones
  console.log(`${type.toUpperCase()}: ${message}`)
  
  // Por ahora usamos alert, pero se puede mejorar
  if (type === 'error') {
    alert(`Error: ${message}`)
  } else if (type === 'success') {
    alert(`Éxito: ${message}`)
  }
}

// Debounce function para optimizar eventos
export const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Throttle function para optimizar scroll events
export const throttle = (func, limit) => {
  let inThrottle
  return function() {
    const args = arguments
    const context = this
    if (!inThrottle) {
      func.apply(context, args)
      inThrottle = true
      setTimeout(() => inThrottle = false, limit)
    }
  }
}
