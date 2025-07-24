// Datos de la rifa actual
let currentRifa = null
let paymentCode = ""

// Datos simulados de rifa detallada
const rifaDetalleData = {
  1: {
    id: "1",
    nombre: "AirPods Pro 2da Generación",
    descripcion:
      "Los nuevos AirPods Pro con cancelación activa de ruido, audio espacial personalizado y hasta 6 horas de reproducción con una sola carga.",
    imagen: "https://images.unsplash.com/photo-1606220945770-b5b6c2c55bf1?w=600&h=400&fit=crop",
    precio: 2,
    ticketsVendidos: 42,
    ticketsMinimos: 50,
    fechaSorteo: "2025-01-30",
    estado: "en_venta",
    premios: [
      { nivel: 1, tickets: 50, premio: "AirPods Pro 2da Gen", desbloqueado: false },
      { nivel: 2, tickets: 100, premio: "AirPods Pro + Funda Premium", desbloqueado: false },
      { nivel: 3, tickets: 150, premio: "AirPods Pro + Funda + Apple Care", desbloqueado: false },
    ],
    participantes: [
      "Juan P.",
      "María G.",
      "Carlos R.",
      "Ana L.",
      "Pedro M.",
      "Sofia T.",
      "Diego V.",
      "Lucia H.",
      "Roberto C.",
      "Carmen S.",
    ],
  },
  2: {
    id: "2",
    nombre: "Perfume Dior Sauvage 100ml",
    descripcion:
      "Fragancia masculina fresca y especiada, con notas de bergamota, pimienta de Sichuan y ambroxan. Presentación de 100ml.",
    imagen: "https://images.unsplash.com/photo-1541643600914-78b084683601?w=600&h=400&fit=crop",
    precio: 2,
    ticketsVendidos: 78,
    ticketsMinimos: 50,
    fechaSorteo: "2025-01-28",
    estado: "confirmada",
    premios: [
      { nivel: 1, tickets: 50, premio: "Perfume Dior Sauvage 100ml", desbloqueado: true },
      { nivel: 2, tickets: 100, premio: "Perfume + Set de viaje", desbloqueado: false },
      { nivel: 3, tickets: 150, premio: "Perfume + Set completo", desbloqueado: false },
    ],
    participantes: [
      "Luis M.",
      "Andrea S.",
      "Miguel T.",
      "Carla V.",
      "José R.",
      "Patricia L.",
      "Fernando H.",
      "Mónica C.",
      "Ricardo P.",
      "Elena G.",
    ],
  },
}

// Funciones auxiliares
function showNotification(message, type) {
  console.log(`Notification (${type}): ${message}`)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.remove("show")
    document.body.style.overflow = ""
  }
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(
    () => {
      console.log("Text copied to clipboard")
    },
    (err) => {
      console.error("Failed to copy text: ", err)
    },
  )
}

// Inicialización
document.addEventListener("DOMContentLoaded", () => {
  initializeRifaDetail()
})

function initializeRifaDetail() {
  const urlParams = new URLSearchParams(window.location.search)
  const rifaId = urlParams.get("id")

  if (!rifaId || !rifaDetalleData[rifaId]) {
    showNotification("Rifa no encontrada", "error")
    setTimeout(() => {
      window.location.href = "index.html"
    }, 2000)
    return
  }

  currentRifa = rifaDetalleData[rifaId]
  generatePaymentCode()
  renderRifaDetail()
  setupEventListeners()
}

function generatePaymentCode() {
  const randomSuffix = Math.random().toString(36).substr(2, 3).toUpperCase()
  paymentCode = `RIFA${currentRifa.id}-${randomSuffix}`
}

function renderRifaDetail() {
  if (!currentRifa) return

  // Breadcrumb
  document.getElementById("rifaBreadcrumb").textContent = currentRifa.nombre

  // Información principal
  document.getElementById("rifaTitle").textContent = currentRifa.nombre
  document.getElementById("rifaPrice").textContent = `S/${currentRifa.precio}`
  document.getElementById("rifaImage").src = currentRifa.imagen
  document.getElementById("rifaImage").alt = currentRifa.nombre
  document.getElementById("rifaDescription").textContent = currentRifa.descripcion

  // Estado
  const statusElement = document.getElementById("rifaStatus")
  const statusClass = `status-${currentRifa.estado}`
  const statusText = getEstadoTexto(currentRifa.estado)
  statusElement.innerHTML = `<span class="badge ${statusClass}">${statusText}</span>`

  // Estadísticas
  document.getElementById("ticketsVendidos").textContent = currentRifa.ticketsVendidos
  document.getElementById("participantesCount").textContent = currentRifa.participantes.length

  // Fecha de sorteo
  const fechaFormateada = formatDateTime(currentRifa.fechaSorteo)
  document.getElementById("fechaSorteo").textContent = fechaFormateada
  document.getElementById("infoFechaSorteo").textContent = formatDate(currentRifa.fechaSorteo)

  // Progreso
  renderProgress()

  // Premios
  renderPrizes()

  // Participantes
  renderParticipants()

  // Modal
  document.getElementById("modalRifaTitle").textContent = currentRifa.nombre
  document.getElementById("paymentCode").textContent = paymentCode
  document.getElementById("paymentCodeDisplay").textContent = paymentCode

  // Info sidebar
  document.getElementById("infoParticipantes").textContent = currentRifa.participantes.length
}

function renderProgress() {
  const porcentaje = Math.min((currentRifa.ticketsVendidos / currentRifa.ticketsMinimos) * 100, 100)
  const faltantes = Math.max(currentRifa.ticketsMinimos - currentRifa.ticketsVendidos, 0)

  document.getElementById("progressText").textContent =
    `${currentRifa.ticketsVendidos}/${currentRifa.ticketsMinimos} tickets`
  document.getElementById("progressFill").style.width = `${porcentaje}%`

  const alertElement = document.getElementById("progressAlert")

  if (faltantes > 0) {
    alertElement.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-circle"></i>
                <strong>¡Faltan ${faltantes} tickets para confirmar el sorteo!</strong>
            </div>
        `
  } else {
    alertElement.innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>🎉 ¡Sorteo confirmado! El premio será sorteado en la fecha programada.</strong>
            </div>
        `
  }
}

function renderPrizes() {
  const prizesList = document.getElementById("prizesList")

  prizesList.innerHTML = currentRifa.premios
    .map((premio) => {
      const isUnlocked = currentRifa.ticketsVendidos >= premio.tickets
      const faltantes = premio.tickets - currentRifa.ticketsVendidos

      return `
            <div class="prize-item ${isUnlocked ? "unlocked" : ""}">
                <div class="prize-info">
                    <div class="prize-number ${isUnlocked ? "unlocked" : ""}">${premio.nivel}</div>
                    <div class="prize-details">
                        <h4>${premio.premio}</h4>
                        <p>${premio.tickets} tickets necesarios</p>
                    </div>
                </div>
                <div>
                    ${
                      isUnlocked
                        ? '<span class="badge badge-success">Desbloqueado</span>'
                        : `<span class="badge badge-secondary">Faltan ${faltantes}</span>`
                    }
                </div>
            </div>
        `
    })
    .join("")
}

function renderParticipants() {
  const participantsList = document.getElementById("participantsList")
  const participantesToShow = currentRifa.participantes.slice(0, 10)
  const remaining = currentRifa.participantes.length - 10

  participantsList.innerHTML = `
        ${participantesToShow
          .map(
            (participante) => `
            <div class="participant-item">
                <div class="participant-avatar">${participante.charAt(0)}</div>
                <span>${participante}</span>
            </div>
        `,
          )
          .join("")}
        ${remaining > 0 ? `<p class="participants-more">Y ${remaining} participantes más...</p>` : ""}
    `
}

function setupEventListeners() {
  // Cerrar modal con ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeModal("paymentModal")
    }
  })
}

function showPaymentModal() {
  if (currentRifa.estado === "sorteada" || currentRifa.estado === "cancelada") {
    showNotification("Esta rifa ya no está disponible", "warning")
    return
  }

  showModal("paymentModal")
}

function copyPaymentCode() {
  copyToClipboard(paymentCode)

  // Cambiar icono temporalmente
  const copyIcon = document.getElementById("copyIcon")
  const copyText = document.getElementById("copyText")

  copyIcon.className = "fas fa-check"
  copyText.textContent = "Copiado"

  setTimeout(() => {
    copyIcon.className = "fas fa-copy"
    copyText.textContent = "Copiar"
  }, 2000)
}

function confirmPayment() {
  const comprobanteFile = document.getElementById("comprobanteFile").files[0]

  // Simular procesamiento
  showNotification("Procesando pago...", "info")

  setTimeout(() => {
    showNotification("¡Pago registrado! Te notificaremos cuando se valide tu ticket.", "success")
    closeModal("paymentModal")

    // Simular actualización de tickets (opcional)
    // currentRifa.ticketsVendidos++;
    // renderRifaDetail();
  }, 2000)
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

function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-PE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

// Función para mostrar modal específico
function showModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.classList.add("show")
    document.body.style.overflow = "hidden"
  }
}
