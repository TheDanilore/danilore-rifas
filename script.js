// Datos de las rifas
const rifasData = [
  {
    id: "1",
    nombre: "AirPods Pro 2da Gen",
    imagen: "https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=400&h=300&fit=crop",
    precio: 2,
    ticketsVendidos: 42,
    ticketsMinimos: 50,
    fechaSorteo: "2025-01-30",
    estado: "en_venta",
    premiosDesbloqueados: 1,
    totalPremios: 3,
  },
  {
    id: "2",
    nombre: "Perfume Dior Sauvage 100ml",
    imagen: "https://images.unsplash.com/photo-1541643600914-78b084683601?w=400&h=300&fit=crop",
    precio: 2,
    ticketsVendidos: 78,
    ticketsMinimos: 50,
    fechaSorteo: "2025-01-28",
    estado: "confirmada",
    premiosDesbloqueados: 2,
    totalPremios: 3,
  },
  {
    id: "3",
    nombre: "iPhone 15 128GB",
    imagen: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=300&fit=crop",
    precio: 2,
    ticketsVendidos: 156,
    ticketsMinimos: 150,
    fechaSorteo: "2025-02-05",
    estado: "confirmada",
    premiosDesbloqueados: 3,
    totalPremios: 3,
  },
  {
    id: "4",
    nombre: "Smart Watch Series 9",
    imagen: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=300&fit=crop",
    precio: 2,
    ticketsVendidos: 23,
    ticketsMinimos: 50,
    fechaSorteo: "2025-02-10",
    estado: "en_venta",
    premiosDesbloqueados: 1,
    totalPremios: 2,
  },
]

// Estado de la aplicación
let currentFilter = "todas"
let isLoggedIn = false

// Inicialización
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

function initializeApp() {
  setupEventListeners()
  renderRifas()
  checkAuthStatus()
}

function setupEventListeners() {
  // Mobile menu
  const mobileMenuBtn = document.getElementById("mobileMenuBtn")
  const mobileMenu = document.getElementById("mobileMenu")

  if (mobileMenuBtn && mobileMenu) {
    mobileMenuBtn.addEventListener("click", () => {
      mobileMenu.classList.toggle("show")
    })
  }

  // User dropdown
  const userButton = document.getElementById("userButton")
  const dropdownMenu = document.getElementById("dropdownMenu")

  if (userButton && dropdownMenu) {
    userButton.addEventListener("click", (e) => {
      e.stopPropagation()
      dropdownMenu.classList.toggle("show")
    })

    document.addEventListener("click", () => {
      dropdownMenu.classList.remove("show")
    })
  }

  // Filtros
  const filterButtons = document.querySelectorAll(".filter-btn")
  filterButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const filter = btn.dataset.filter
      setActiveFilter(filter)
      renderRifas()
    })
  })

  // Cerrar modal al hacer click fuera
  document.addEventListener("click", (e) => {
    if (e.target.classList.contains("modal-overlay")) {
      closeModal()
    }
  })
}

function setActiveFilter(filter) {
  currentFilter = filter

  // Actualizar botones activos
  document.querySelectorAll(".filter-btn").forEach((btn) => {
    btn.classList.remove("active")
  })

  document.querySelector(`[data-filter="${filter}"]`).classList.add("active")
}

function renderRifas() {
  const rifasGrid = document.getElementById("rifasGrid")
  const emptyState = document.getElementById("emptyState")

  if (!rifasGrid) return

  // Filtrar rifas
  const filteredRifas = rifasData.filter((rifa) => {
    if (currentFilter === "todas") return true
    return rifa.estado === currentFilter
  })

  // Mostrar/ocultar empty state
  if (filteredRifas.length === 0) {
    rifasGrid.classList.add("hidden")
    emptyState?.classList.remove("hidden")
    return
  } else {
    rifasGrid.classList.remove("hidden")
    emptyState?.classList.add("hidden")
  }

  // Renderizar rifas
  rifasGrid.innerHTML = filteredRifas.map((rifa) => createRifaCard(rifa)).join("")

  // Agregar event listeners a las cards
  rifasGrid.querySelectorAll(".rifa-card").forEach((card) => {
    card.addEventListener("click", () => {
      const rifaId = card.dataset.rifaId
      window.location.href = `rifa-detalle.html?id=${rifaId}`
    })
  })
}

function createRifaCard(rifa) {
  const porcentaje = Math.min((rifa.ticketsVendidos / rifa.ticketsMinimos) * 100, 100)
  const mensajeMotivacional = getMensajeMotivacional(rifa)
  const estadoTexto = getEstadoTexto(rifa.estado)
  const estadoClass = `status-${rifa.estado}`

  return `
        <div class="rifa-card" data-rifa-id="${rifa.id}">
            <div class="rifa-image">
                <img src="${rifa.imagen}" alt="${rifa.nombre}" loading="lazy">
                <div class="rifa-status ${estadoClass}">${estadoTexto}</div>
                <div class="rifa-price">S/${rifa.precio}</div>
            </div>
            <div class="rifa-content">
                <h3 class="rifa-title">${rifa.nombre}</h3>
                
                <div class="rifa-progress">
                    <div class="progress-info">
                        <span class="progress-label">Progreso</span>
                        <span class="progress-value">${rifa.ticketsVendidos}/${rifa.ticketsMinimos}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${porcentaje}%"></div>
                    </div>
                    <p class="progress-message">${mensajeMotivacional}</p>
                </div>

                <div class="rifa-meta">
                    <div class="rifa-prizes">
                        <i class="fas fa-star"></i>
                        <span>Premios: ${rifa.premiosDesbloqueados}/${rifa.totalPremios}</span>
                    </div>
                    <div class="rifa-date">
                        <i class="fas fa-clock"></i>
                        <span>Sorteo: ${formatDate(rifa.fechaSorteo)}</span>
                    </div>
                </div>

                <div class="rifa-action">
                    <button class="btn rifa-btn" ${rifa.estado === "cancelada" || rifa.estado === "sorteada" ? "disabled" : ""}>
                        ${getButtonText(rifa.estado)}
                    </button>
                </div>
            </div>
        </div>
    `
}

function getMensajeMotivacional(rifa) {
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

function getEstadoTexto(estado) {
  const estados = {
    en_venta: "En Venta",
    confirmada: "Confirmada",
    sorteada: "Sorteada",
    cancelada: "Cancelada",
  }
  return estados[estado] || "Desconocido"
}

function getButtonText(estado) {
  switch (estado) {
    case "sorteada":
      return "🏆 Sorteada"
    case "cancelada":
      return "❌ Cancelada"
    default:
      return "🎫 Participar Ahora"
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  })
}

function checkAuthStatus() {
  // Simular verificación de autenticación
  const authButtons = document.getElementById("authButtons")
  const userMenu = document.getElementById("userMenu")

  if (isLoggedIn) {
    authButtons?.classList.add("hidden")
    userMenu?.classList.remove("hidden")
  } else {
    authButtons?.classList.remove("hidden")
    userMenu?.classList.add("hidden")
  }
}

function logout() {
  isLoggedIn = false
  checkAuthStatus()
  alert("Sesión cerrada correctamente")
}

// Funciones para modales
function showModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("show")
    document.body.style.overflow = "hidden"
  }
}

function closeModal() {
  const modals = document.querySelectorAll(".modal-overlay")
  modals.forEach((modal) => {
    modal.classList.remove("show")
  })
  document.body.style.overflow = ""
}

// Función para copiar al portapapeles
function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showNotification("Código copiado al portapapeles", "success")
    })
    .catch(() => {
      // Fallback para navegadores que no soportan clipboard API
      const textArea = document.createElement("textarea")
      textArea.value = text
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand("copy")
      document.body.removeChild(textArea)
      showNotification("Código copiado al portapapeles", "success")
    })
}

// Sistema de notificaciones
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `

  document.body.appendChild(notification)

  // Mostrar notificación
  setTimeout(() => {
    notification.classList.add("show")
  }, 100)

  // Ocultar después de 3 segundos
  setTimeout(() => {
    notification.classList.remove("show")
    setTimeout(() => {
      document.body.removeChild(notification)
    }, 300)
  }, 3000)
}

function getNotificationIcon(type) {
  const icons = {
    success: "check-circle",
    error: "exclamation-circle",
    warning: "exclamation-triangle",
    info: "info-circle",
  }
  return icons[type] || "info-circle"
}

// Validación de formularios
function validateForm(formId) {
  const form = document.getElementById(formId)
  if (!form) return false

  const requiredFields = form.querySelectorAll("[required]")
  let isValid = true

  requiredFields.forEach((field) => {
    if (!field.value.trim()) {
      field.classList.add("error")
      isValid = false
    } else {
      field.classList.remove("error")
    }
  })

  return isValid
}

// Formateo de números
function formatCurrency(amount) {
  return `S/${amount.toFixed(2)}`
}

function formatNumber(number) {
  return number.toLocaleString("es-PE")
}

// Utilidades de fecha
function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
}

function getTimeRemaining(dateString) {
  const now = new Date().getTime()
  const target = new Date(dateString).getTime()
  const difference = target - now

  if (difference <= 0) {
    return "Finalizado"
  }

  const days = Math.floor(difference / (1000 * 60 * 60 * 24))
  const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))

  if (days > 0) {
    return `${days} días`
  } else if (hours > 0) {
    return `${hours} horas`
  } else {
    return `${minutes} minutos`
  }
}

// Lazy loading de imágenes
function setupLazyLoading() {
  const images = document.querySelectorAll('img[loading="lazy"]')

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src || img.src
          img.classList.remove("lazy")
          observer.unobserve(img)
        }
      })
    })

    images.forEach((img) => imageObserver.observe(img))
  }
}

// Smooth scroll
function smoothScrollTo(elementId) {
  const element = document.getElementById(elementId)
  if (element) {
    element.scrollIntoView({
      behavior: "smooth",
      block: "start",
    })
  }
}

// Debounce function para optimizar eventos
function debounce(func, wait) {
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
function throttle(func, limit) {
  let inThrottle
  return function () {
    const args = arguments
    
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => (inThrottle = false), limit)
    }
  }
}

// Inicializar lazy loading cuando se carga la página
document.addEventListener("DOMContentLoaded", setupLazyLoading)

// Manejar errores de imágenes
document.addEventListener(
  "error",
  (e) => {
    if (e.target.tagName === "IMG") {
      e.target.src = "https://via.placeholder.com/400x300/e5e7eb/9ca3af?text=Imagen+no+disponible"
    }
  },
  true,
)

// Agregar estilos para notificaciones
const notificationStyles = `
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 3000;
    padding: 1rem 1.5rem;
    border-radius: 0.5rem;
    color: white;
    font-weight: 500;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
}

.notification.show {
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.notification-success {
    background: #10b981;
}

.notification-error {
    background: #ef4444;
}

.notification-warning {
    background: #f59e0b;
}

.notification-info {
    background: #3b82f6;
}

.form-input.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
`

// Agregar estilos al head
const styleSheet = document.createElement("style")
styleSheet.textContent = notificationStyles
document.head.appendChild(styleSheet)
