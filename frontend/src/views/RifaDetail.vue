<template>
    <div class="rifa-detail-page">
        <AppHeader />

        <div v-if="loading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando información de la rifa...</p>
        </div>

        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
            <button class="btn btn-primary" @click="loadRifa(rifaId)">Reintentar</button>
        </div>


        <div v-else-if="rifa" class="rifa-detail-container">

            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <router-link to="/">Inicio</router-link>
                <span> / </span>
                <span>{{ rifa.nombre }}</span>
            </nav>

            <div class="rifa-detail-grid">
                <!-- Contenido Principal -->
                <div class="rifa-main">
                    <!-- Header Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="rifa-header">
                                <div>
                                    <h1>{{ rifa.nombre }}</h1>
                                    <div class="rifa-status-badge" :class="`status-${rifa.estado}`">
                                        {{ getEstadoTexto(rifa.estado) }}
                                    </div>
                                </div>
                                <div class="rifa-price-display">
                                    <div class="rifa-price-amount">S/ {{ rifa.precio }}</div>
                                    <div class="rifa-price-label">por ticket</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <!-- Contenido principal -->
                            <div class="rifa-detail-content">
                                <!-- Galería de medios mejorada -->
                                <div class="rifa-media">
                                    <MediaGallery v-if="rifa.mediaGallery" :mediaGallery="rifa.mediaGallery" />
                                    <!-- Fallback para rifas sin galería -->
                                    <div v-else class="rifa-image-container">
                                        <img :src="rifa.imagen" :alt="rifa.nombre" class="rifa-detail-image"
                                            @error="handleImageError">
                                    </div>
                                </div>

                                <div class="rifa-info">
                                    <div class="rifa-description">
                                        <h3>Descripción del Premio</h3>
                                        <p>{{ rifa.descripcion || 'sin descripcion' }}</p>
                                    </div>

                                    <!-- Especificaciones técnicas -->
                                    <div v-if="rifa.specifications" class="rifa-specifications">
                                        <h3>Especificaciones Técnicas</h3>
                                        <div class="specs-grid">
                                            <div v-for="(value, key) in rifa.specifications" :key="key"
                                                class="spec-item">
                                                <span class="spec-label">{{ key }}:</span>
                                                <span class="spec-value">{{ value }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stats como cards -->
                                    <div class="rifa-stats">
                                        <div class="stat-card">
                                            <div class="stat-value">{{ rifa.ticketsVendidos }}</div>
                                            <div class="stat-label">Tickets Vendidos</div>
                                        </div>
                                        <div class="stat-card">
                                            <div class="stat-value">{{ rifa.participantes?.length || 0 }}</div>
                                            <div class="stat-label">Participantes</div>
                                        </div>
                                    </div>

                                    <!-- Fecha de sorteo -->
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <div class="date-title">Fecha de Sorteo</div>
                                            <div class="date-value">{{ formatDate(rifa.fechaSorteo) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso del Sorteo -->
                    <div class="card">
                        <div class="card-header">
                            <div class="progress-header">
                                <i class="fas fa-target"></i>
                                <h2>Progreso del Sorteo</h2>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="progress-current">
                                <span>Progreso actual</span>
                                <span class="progress-tickets">{{ rifa.ticketsVendidos }}/{{ rifa.ticketsMinimos }}
                                    tickets</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" :style="{ width: progressPercentage + '%' }"></div>
                            </div>
                            <div class="progress-alert" :class="getProgressAlertClass()">
                                <i :class="getProgressIcon()"></i>
                                <span>{{ getProgressMessage() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Premios Progresivos Multinivel -->
                    <div class="card">
                        <div class="card-header">
                            <div class="prizes-header">
                                <i class="fas fa-trophy"></i>
                                <h2>Premios Progresivos</h2>
                            </div>
                        </div>
                        <div class="card-content">
                            <div class="prizes-progression">
                                <!-- Premio Actual Activo -->
                                <div v-for="(premio, premioIndex) in getPremiosProgresivos()" :key="premioIndex"
                                    class="premio-section" :class="{
                                        'premio-active': premio.esta_activo,
                                        'premio-completed': premio.completado,
                                        'premio-locked': !premio.desbloqueado
                                    }">
                                    <!-- Header del Premio -->
                                    <div class="premio-header">
                                        <div class="premio-icon">
                                            <i :class="premio.icono || 'fas fa-gift'"></i>
                                        </div>
                                        <div class="premio-info">
                                            <h4 class="premio-title">{{ premio.titulo }}</h4>
                                            <p class="premio-description">{{ premio.descripcion }}</p>
                                        </div>
                                        <div class="premio-status">
                                            <span class="premio-badge" :class="{
                                                'badge-active': premio.esta_activo,
                                                'badge-completed': premio.completado,
                                                'badge-locked': !premio.desbloqueado
                                            }">
                                                <i v-if="premio.completado" class="fas fa-check"></i>
                                                <i v-else-if="!premio.desbloqueado" class="fas fa-lock"></i>
                                                <i v-else class="fas fa-clock"></i>
                                                {{ premio.estado_texto }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Botón Ver Detalles del Premio -->
                                    <div class="premio-actions">
                                        <button class="premio-detail-btn" :class="{
                                            'btn-primary': premio.desbloqueado,
                                            'btn-secondary': !premio.desbloqueado
                                        }" @click="handlePremioClick(premio)">
                                            <i class="fas fa-eye"></i>
                                            {{ premio.desbloqueado ? 'Ver Galería y Detalles' : 'Ver Información' }}
                                        </button>
                                    </div>

                                    <!-- Niveles del Premio (solo si está desbloqueado) -->
                                    <div v-if="premio.desbloqueado" class="premio-niveles">
                                        <div v-for="(nivel, nivelIndex) in premio.niveles" :key="nivelIndex"
                                            class="nivel-item" :class="{
                                                'nivel-unlocked': nivel.desbloqueado,
                                                'nivel-current': nivel.es_actual
                                            }">
                                            <div class="nivel-progress">
                                                <div class="nivel-number" :class="{ 'unlocked': nivel.desbloqueado }">
                                                    {{ nivelIndex + 1 }}
                                                </div>
                                                <div class="nivel-details">
                                                    <h5 class="nivel-title">{{ nivel.titulo }}</h5>
                                                    <p class="nivel-description">{{ nivel.descripcion }}</p>
                                                    <div class="nivel-requirement">
                                                        <i class="fas fa-ticket-alt"></i>
                                                        {{ nivel.tickets_necesarios }} tickets necesarios
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Progreso del nivel actual -->
                                            <div v-if="nivel.es_actual" class="nivel-progress-bar">
                                                <div class="progress-info">
                                                    <span class="progress-label">Progreso actual</span>
                                                    <span class="progress-value">{{ rifa.ticketsVendidos }}/{{
                                                        nivel.tickets_necesarios }}</span>
                                                </div>
                                                <div class="progress-bar">
                                                    <div class="progress-fill"
                                                        :style="{ width: `${Math.min((rifa.ticketsVendidos / nivel.tickets_necesarios) * 100, 100)}%` }">
                                                    </div>
                                                </div>
                                                <div class="tickets-remaining">
                                                    <i class="fas fa-target"></i>
                                                    Faltan {{ Math.max(0, nivel.tickets_necesarios -
                                                    rifa.ticketsVendidos) }} tickets
                                                </div>
                                            </div>

                                            <!-- Estado del nivel -->
                                            <div class="nivel-status">
                                                <span class="nivel-badge" :class="{
                                                    'badge-completed': nivel.desbloqueado,
                                                    'badge-current': nivel.es_actual,
                                                    'badge-pending': !nivel.desbloqueado && !nivel.es_actual
                                                }">
                                                    <i v-if="nivel.desbloqueado" class="fas fa-check"></i>
                                                    <i v-else-if="nivel.es_actual" class="fas fa-clock"></i>
                                                    <i v-else class="fas fa-lock"></i>
                                                    {{ nivel.estado_texto }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Premio Bloqueado -->
                                    <div v-if="!premio.desbloqueado" class="premio-locked-info">
                                        <i class="fas fa-lock"></i>
                                        <p>Se desbloqueará al completar: <strong>{{ premio.premio_requerido }}</strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="rifa-sidebar">
                    <!-- Participar Card -->
                    <div class="card participate-card">
                        <div class="participate-content">
                            <div class="participate-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3 class="participate-title">¡Participa Ahora!</h3>
                            <p class="participate-subtitle">Solo S/ {{ rifa.precio }} por ticket. ¡No te quedes sin
                                participar!</p>

                            <!-- Mostrar tickets del usuario si está autenticado -->
                            <div v-if="isAuthenticated && userTicketsForRifa.length > 0" class="user-tickets-info">
                                <div class="tickets-owned">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span>Tienes {{ userTicketsForRifa.length }} ticket(s) en esta rifa</span>
                                </div>
                                <div class="ticket-numbers">
                                    <span v-for="ticket in userTicketsForRifa" :key="ticket.id" class="ticket-number">
                                        #{{ ticket.numero }}
                                    </span>
                                </div>
                            </div>

                            <button class="participate-btn"
                                :disabled="rifa.estado === 'cancelada' || rifa.estado === 'sorteada'"
                                @click="showPaymentModal">
                                {{ isAuthenticated ? '🎫 Comprar Ticket' : '🔒 Inicia Sesión para Participar' }}
                            </button>

                            <div v-if="!isAuthenticated" class="auth-notice">
                                <i class="fas fa-info-circle"></i>
                                <span>Necesitas iniciar sesión para participar en las rifas</span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Sorteo -->
                    <div class="card info-card">
                        <div class="card-header">
                            <h3>Información del Sorteo</h3>
                        </div>
                        <div class="card-content">
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Sorteo: {{ formatDate(rifa.fechaSorteo) }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <span>{{ rifa.participantes?.length || 0 }} participantes</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-bolt"></i>
                                <span>Validación automática</span>
                            </div>
                        </div>
                    </div>

                    <!-- Participantes Recientes -->
                    <div v-if="rifa.participantes?.length" class="card participants-card">
                        <div class="card-header">
                            <h3>Participantes Recientes</h3>
                        </div>
                        <div class="card-content">
                            <div class="participants-list">
                                <div v-for="(participante, index) in rifa.participantes.slice(0, 6)" :key="index"
                                    class="participant-item">
                                    <div class="participant-avatar">
                                        {{ participante.charAt(0) }}
                                    </div>
                                    <span class="participant-name">{{ participante }}</span>
                                </div>
                                <div v-if="rifa.participantes.length > 6" class="participants-more">
                                    +{{ rifa.participantes.length - 6 }} más
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <AppFooter />

    <!-- Modal de pago -->
    <div v-if="paymentModal" class="modal-overlay" @click="closePaymentModal">
        <div class="modal payment-modal" @click.stop>
            <div class="modal-header">
                <h2 class="modal-title">Comprar Ticket - {{ rifa.nombre }}</h2>
                <p class="modal-description">Sigue estos pasos para participar en la rifa</p>
                <button class="close-btn" @click="closePaymentModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-content">
                <!-- Paso 0: Selección de Número de Ticket -->
                <div v-if="!selectedTicketNumber" class="payment-step step-purple">
                    <h4 class="step-title">Paso 0: Selecciona tu número de la suerte</h4>

                    <div class="ticket-selection-section">
                        <div class="selection-options">
                            <button class="random-btn" @click="selectRandomTicket">
                                <i class="fas fa-dice"></i>
                                Número Aleatorio
                            </button>
                            <span class="separator">o</span>
                            <span class="manual-text">Elige tu número de la suerte:</span>
                        </div>

                        <div class="tickets-grid">
                            <button v-for="numero in availableTickets" :key="numero" class="ticket-number" :class="{
                                'sold': soldTickets.includes(numero),
                                'selected': numero === tempSelectedNumber
                            }" :disabled="soldTickets.includes(numero)" @click="selectTicketNumber(numero)">
                                {{ numero }}
                            </button>
                        </div>

                        <div class="ticket-info">
                            <div class="info-item">
                                <span class="legend available"></span>
                                <span>Disponible</span>
                            </div>
                            <div class="info-item">
                                <span class="legend sold"></span>
                                <span>Vendido</span>
                            </div>
                            <div class="info-item">
                                <span class="legend selected"></span>
                                <span>Tu selección</span>
                            </div>
                        </div>

                        <div v-if="tempSelectedNumber" class="selected-ticket-info">
                            <div class="selected-display">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Número seleccionado: <strong>{{ tempSelectedNumber }}</strong></span>
                            </div>
                            <button class="confirm-selection-btn" @click="confirmTicketSelection">
                                ✓ Confirmar y Continuar con el Pago
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pasos de pago (solo se muestran después de seleccionar número) -->
                <div v-if="selectedTicketNumber">
                    <!-- Mostrar número seleccionado -->
                    <div class="selected-ticket-banner">
                        <div class="ticket-banner-content">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tu ticket: <strong>{{ selectedTicketNumber }}</strong></span>
                            <button class="change-ticket-btn" @click="changeTicketNumber">
                                <i class="fas fa-edit"></i>
                                Cambiar
                            </button>
                        </div>
                    </div>

                    <!-- Paso 1: Código de pago -->
                    <div class="payment-step step-blue">
                        <h4 class="step-title">Paso 1: Tu código de pago</h4>
                        <div class="code-display">
                            <span class="code-text">{{ paymentCode }}</span>
                            <button class="btn btn-outline copy-btn" @click="copyPaymentCode">
                                <i class="fas fa-copy"></i>
                                Copiar
                            </button>
                        </div>
                        <p class="payment-warning">
                            ⚠️ <strong>Importante:</strong> Debes incluir este código en el mensaje de tu pago Yape o
                            Plin
                        </p>
                    </div>

                    <!-- Paso 2: Realizar pago -->
                    <div class="payment-step step-green">
                        <h4 class="step-title">Paso 2: Realizar pago</h4>
                        <div class="payment-content">
                            <div class="payment-info-left">
                                <div class="payment-info">• Monto: <strong>S/ {{ rifa.precio }}</strong></div>
                                <div class="payment-info">• Escanea el código QR con tu app:</div>
                                <div class="payment-apps">
                                    <div class="payment-app">
                                        <img src="https://logosenvector.com/logo/img/yape-37283.png" alt="Yape"
                                            class="app-icon">
                                        <span>Yape</span>
                                    </div>
                                    <div class="payment-app">
                                        <img src="https://images.seeklogo.com/logo-png/38/1/plin-logo-png_seeklogo-386806.png"
                                            alt="Plin" class="app-icon">
                                        <span>Plin</span>
                                    </div>
                                </div>
                                <div class="payment-warning">• <strong>Mensaje obligatorio:</strong> {{ paymentCode }}
                                </div>
                            </div>
                            <div class="payment-qr">
                                <div class="qr-container">
                                    <div class="qr-header">
                                        <i class="fas fa-qrcode"></i>
                                        <span>Escanea para pagar</span>
                                    </div>
                                    <div class="qr-image-wrapper">
                                        <img src="@/assets/yape-qr.png" alt="Código QR Yape/Plin" class="qr-image">
                                    </div>
                                    <div class="qr-footer">
                                        <p class="qr-title">Código QR</p>
                                        <small class="qr-subtitle">Compatible con Yape y Plin</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Subir comprobante -->
                    <div class="payment-step step-yellow">
                        <h4 class="step-title">Paso 3: Confirmar tu pago</h4>
                        
                        <!-- Información sobre el proceso automático -->
                        <div class="auto-process-info">
                            <div class="info-header">
                                <i class="fas fa-robot"></i>
                                <span>Proceso Automático</span>
                            </div>
                            <p>Detectaremos tu pago en unos minutos. No necesitas hacer nada más.</p>
                        </div>

                        <!-- Separador -->
                        <div class="process-separator">
                            <span>O en caso de problemas</span>
                        </div>

                        <!-- Proceso manual como backup -->
                        <div class="manual-process-section">
                            <div class="info-header">
                                <i class="fas fa-user"></i>
                                <span>Confirmación Manual</span>
                                <small>(Solo si el proceso automático falla)</small>
                            </div>
                            
                            <div class="file-upload-section">
                                <label class="file-upload-label">
                                    <i class="fas fa-camera"></i>
                                    Subir comprobante de pago
                                </label>
                                <input 
                                    type="file" 
                                    ref="fileInput"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                    class="file-input"
                                >
                                <div class="file-upload-area" @click="triggerFileInput">
                                    <div v-if="!selectedFile" class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Haz clic aquí para subir tu comprobante</p>
                                        <small>Formatos soportados: JPG, PNG (máx. 5MB)</small>
                                    </div>
                                    <div v-else class="file-preview">
                                        <img v-if="filePreview" :src="filePreview" alt="Comprobante" class="preview-image">
                                        <div class="file-info">
                                            <i class="fas fa-file-image"></i>
                                            <span>{{ selectedFile.name }}</span>
                                            <button type="button" @click.stop="removeFile" class="remove-file-btn">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button 
                                class="confirm-btn manual-confirm"
                                :disabled="paymentLoading || !selectedFile"
                                @click="confirmPaymentManually"
                            >
                                <i v-if="paymentLoading" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-paper-plane"></i>
                                <span>{{ paymentLoading ? 'Enviando...' : 'Enviar Comprobante Manual' }}</span>
                            </button>
                            
                            <p v-if="!selectedFile" class="manual-requirement">
                                <i class="fas fa-info-circle"></i>
                                Debes subir un comprobante para usar la confirmación manual
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import MediaGallery from '@/components/MediaGallery.vue'
import { useRifaDetail } from '@/composables/useRifaDetail'
import { useAuthStore } from '@/store/auth'
import { formatDate, getEstadoTexto, getButtonText, copyToClipboard, showNotification } from '@/utils/helpers'

export default {
    name: 'RifaDetail',
    inheritAttrs: false,
    components: {
        AppHeader,
        AppFooter,
        MediaGallery
    },
    setup() {
        const route = useRoute()
        const router = useRouter()
        const rifaId = computed(() => route.params.id)
        const { isAuthenticated, getUserTicketsForRifa } = useAuthStore()

        // Variables para selección de tickets
        const selectedTicketNumber = ref(null)
        const tempSelectedNumber = ref(null)

        // Variables para manejo de archivos
        const selectedFile = ref(null)
        const filePreview = ref(null)
        const fileInput = ref(null)

        const {
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
        } = useRifaDetail()

        const progressPercentage = computed(() => {
            return getProgressPercentage()
        })

        const userTicketsForRifa = computed(() => {
            if (!isAuthenticated.value || !rifa.value) return []
            return getUserTicketsForRifa(rifa.value.id)
        })

        // Computed properties para la selección de tickets
        const availableTickets = computed(() => {
            if (!rifa.value) return []
            // Usar ticketsMinimos como base para el total de tickets disponibles
            // En una rifa real, esto vendría del backend como un campo específico
            const totalTickets = rifa.value.ticketsMinimos || 100
            const tickets = []
            for (let i = 1; i <= totalTickets; i++) {
                tickets.push(String(i).padStart(3, '0'))
            }
            return tickets
        })

        const soldTickets = computed(() => {
            if (!rifa.value) return []
            // Simular tickets vendidos basado en el número total vendido
            // En una implementación real, esto vendría del backend con la lista exacta de números vendidos
            const ticketsVendidos = rifa.value.ticketsVendidos || 0
            const soldNumbers = []
            
            // Generar números vendidos de forma determinística (siempre los mismos para la misma rifa)
            for (let i = 1; i <= ticketsVendidos; i++) {
                soldNumbers.push(String(i).padStart(3, '0'))
            }
            
            return soldNumbers
        })

        const getProgressMessage = () => {
            if (!rifa.value) return ''

            const restantes = rifa.value.ticketsMinimos - rifa.value.ticketsVendidos
            if (restantes <= 0) {
                return "🎉 ¡Sorteo confirmado! El premio será sorteado en la fecha programada."
            } else {
                return `¡Faltan ${restantes} tickets para confirmar el sorteo!`
            }
        }

        const getProgressAlertClass = () => {
            if (!rifa.value) return 'alert-success'

            const restantes = rifa.value.ticketsMinimos - rifa.value.ticketsVendidos
            if (restantes <= 0) {
                return 'alert-success'
            } else {
                return 'alert-warning'
            }
        }

        const getProgressIcon = () => {
            if (!rifa.value) return 'fas fa-check-circle'

            const restantes = rifa.value.ticketsMinimos - rifa.value.ticketsVendidos
            if (restantes <= 0) {
                return 'fas fa-check-circle'
            } else {
                return 'fas fa-exclamation-triangle'
            }
        }

        const handleImageError = (event) => {
            event.target.src = 'https://via.placeholder.com/600x400?text=Imagen+no+disponible'
        }

        const copyPaymentCode = async () => {
            const success = await copyToClipboard(paymentCode.value)
            if (success) {
                showNotification('Código copiado al portapapeles', 'success')
            } else {
                showNotification('Error al copiar el código', 'error')
            }
        }

        const handleConfirmPayment = async () => {
            const result = await confirmPayment()
            if (result) {
                showNotification(result.message, result.success ? 'success' : 'error')
            }
        }

        const getPremios = () => {
            if (!rifa.value || !rifa.value.premios) return []

            return rifa.value.premios.map(premio => ({
                nombre: premio.premio,
                ticketsRequeridos: premio.tickets,
                unlocked: rifa.value.ticketsVendidos >= premio.tickets,
                nivel: premio.nivel
            }))
        }

        const handlePremioClick = (premio) => {
            router.push(`/premio/${rifaId.value}/${premio.id}`)
        }

        // Métodos para la selección de tickets
        const selectTicketNumber = (numero) => {
            if (!soldTickets.value.includes(numero)) {
                tempSelectedNumber.value = numero
            }
        }

        const selectRandomTicket = () => {
            const available = availableTickets.value.filter(ticket =>
                !soldTickets.value.includes(ticket)
            )
            if (available.length > 0) {
                const randomIndex = Math.floor(Math.random() * available.length)
                tempSelectedNumber.value = available[randomIndex]
            }
        }

        const confirmTicketSelection = () => {
            if (tempSelectedNumber.value) {
                selectedTicketNumber.value = tempSelectedNumber.value
                tempSelectedNumber.value = null
            }
        }

        const changeTicketNumber = () => {
            selectedTicketNumber.value = null
            tempSelectedNumber.value = null
        }

        // Métodos para manejo de archivos
        const triggerFileInput = () => {
            fileInput.value?.click()
        }

        const handleFileUpload = (event) => {
            const file = event.target.files[0]
            if (!file) return

            // Validar tipo de archivo
            if (!file.type.startsWith('image/')) {
                showNotification('Por favor selecciona una imagen válida', 'error')
                return
            }

            // Validar tamaño (5MB máximo)
            const maxSize = 5 * 1024 * 1024 // 5MB en bytes
            if (file.size > maxSize) {
                showNotification('El archivo es demasiado grande. Máximo 5MB', 'error')
                return
            }

            selectedFile.value = file

            // Crear preview de la imagen
            const reader = new FileReader()
            reader.onload = (e) => {
                filePreview.value = e.target.result
            }
            reader.readAsDataURL(file)

            showNotification('Comprobante cargado correctamente', 'success')
        }

        const removeFile = () => {
            selectedFile.value = null
            filePreview.value = null
            if (fileInput.value) {
                fileInput.value.value = ''
            }
        }

        // Método para confirmación manual con comprobante
        const confirmPaymentManually = async () => {
            if (!selectedFile.value) {
                showNotification('Debes subir un comprobante para confirmar manualmente', 'error')
                return
            }

            try {
                // Aquí implementarías la lógica para enviar el comprobante
                // Por ejemplo, subir la imagen a un servidor y crear un ticket manual
                
                showNotification('Comprobante enviado para revisión manual. Te notificaremos cuando sea procesado.', 'success')
                
                // Cerrar el modal después de enviar
                closePaymentModal()
                
                // Limpiar el archivo seleccionado
                removeFile()
                
            } catch (error) {
                console.error('Error al enviar comprobante:', error)
                showNotification('Error al enviar el comprobante. Inténtalo de nuevo.', 'error')
            }
        }

        onMounted(() => {
            loadRifa(rifaId.value)
        })

        return {
            rifa,
            loading,
            error,
            paymentModal,
            paymentCode,
            paymentLoading,
            rifaId,
            progressPercentage,
            userTicketsForRifa,
            isAuthenticated,
            showPaymentModal,
            closePaymentModal,
            confirmPayment: handleConfirmPayment,
            getProgressMessage,
            getProgressAlertClass,
            getProgressIcon,
            copyPaymentCode,
            handleImageError,
            formatDate,
            getEstadoTexto,
            getButtonText,
            getPremios,
            getPremiosProgresivos,
            getNivelActual,
            handlePremioClick,
            // Variables y métodos para selección de tickets
            selectedTicketNumber,
            tempSelectedNumber,
            availableTickets,
            soldTickets,
            selectTicketNumber,
            selectRandomTicket,
            confirmTicketSelection,
            changeTicketNumber,
            // Variables y métodos para archivos
            selectedFile,
            filePreview,
            fileInput,
            triggerFileInput,
            handleFileUpload,
            removeFile,
            confirmPaymentManually
        }
    }
}
</script>

<style scoped>
.rifa-detail-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--gray-50);
}

.loading-state,
.error-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
}

.loading-state i,
.error-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.loading-state i {
    color: var(--primary-purple);
}

.error-state i {
    color: var(--danger-red);
}

.rifa-detail-container {
    flex: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.breadcrumb {
    margin-bottom: 2rem;
    font-size: 0.875rem;
}

.breadcrumb a {
    color: var(--primary-purple);
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.rifa-detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.rifa-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.rifa-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.card-header h2,
.card-header h3,
.card-header h4 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-800);
}

.card-content {
    padding: 1.5rem;
}

.rifa-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.rifa-title-section h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
}

.rifa-status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
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

.rifa-price-display {
    text-align: right;
}

.rifa-price-amount {
    font-size: 2rem;
    font-weight: 700;
    color: var(--success-green);
    display: block;
}

.rifa-price-label {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.rifa-detail-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.rifa-media {
    grid-column: 1 / -1;
    margin-bottom: 2rem;
}

.rifa-info {
    grid-column: 1 / -1;
}

.rifa-description {
    margin-bottom: 2rem;
}

.rifa-description h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 1rem;
}

.rifa-description p {
    color: var(--gray-600);
    line-height: 1.6;
}

/* Especificaciones técnicas */
.rifa-specifications {
    margin-bottom: 2rem;
}

.rifa-specifications h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 1rem;
}

.specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
    border-left: 3px solid var(--primary-purple);
}

.spec-label {
    font-weight: 600;
    color: var(--gray-800);
}

.spec-value {
    color: var(--gray-600);
    text-align: right;
}

.rifa-image-container {
    position: relative;
}

.rifa-detail-image {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
}

.rifa-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stat-card {
    background: var(--gray-50);
    padding: 1rem;
    border-radius: var(--border-radius);
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-purple);
    display: block;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--gray-600);
}

.date-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #fef3c7;
    border-radius: var(--border-radius);
}

.date-info i {
    color: #f59e0b;
}

.date-title {
    font-weight: 600;
    color: #92400e;
}

.date-value {
    font-size: 0.875rem;
    color: #78350f;
}

.progress-current {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.progress-tickets {
    font-weight: 700;
    font-size: 1.125rem;
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
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), var(--success-green));
    border-radius: var(--border-radius-full);
    transition: width 0.3s ease;
}

.progress-alert {
    padding: 1rem;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-warning {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}

/* Premios Progresivos Multinivel */
.prizes-progression {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.premio-section {
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    background: var(--white);
    transition: all 0.3s ease;
    overflow: hidden;
}

.premio-section.premio-active {
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 1px var(--primary-blue);
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
}

.premio-section.premio-completed {
    border-color: var(--success-green);
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.premio-section.premio-locked {
    border-color: var(--gray-300);
    background: var(--gray-50);
    opacity: 0.7;
}

.premio-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.premio-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-purple);
    color: var(--white);
    font-size: 1.25rem;
}

.premio-section.premio-completed .premio-icon {
    background: var(--success-green);
}

.premio-section.premio-locked .premio-icon {
    background: var(--gray-400);
}

.premio-info {
    flex: 1;
}

.premio-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--gray-800);
    margin: 0 0 0.5rem 0;
}

.premio-description {
    color: var(--gray-600);
    margin: 0;
    font-size: 0.875rem;
    line-height: 1.4;
}

.premio-status {
    display: flex;
    align-items: center;
}

.premio-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius-full);
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-active {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #3b82f6;
}

.badge-completed {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #22c55e;
}

.badge-locked {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.premio-niveles {
    padding: 1rem 1.5rem 1.5rem;
}

.nivel-item {
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
    background: var(--white);
    transition: all 0.3s ease;
}

.nivel-item:last-child {
    margin-bottom: 0;
}

.nivel-item.nivel-unlocked {
    border-color: var(--success-green);
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.nivel-item.nivel-current {
    border-color: var(--primary-blue);
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    box-shadow: 0 0 0 1px var(--primary-blue);
}

.nivel-progress {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
}

.nivel-number {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.875rem;
    background: var(--gray-300);
    color: var(--white);
    transition: all 0.3s ease;
}

.nivel-number.unlocked {
    background: var(--success-green);
}

.nivel-item.nivel-current .nivel-number {
    background: var(--primary-blue);
}

.nivel-details {
    flex: 1;
}

.nivel-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0 0 0.25rem 0;
}

.nivel-description {
    color: var(--gray-600);
    font-size: 0.875rem;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.nivel-requirement {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-700);
    font-weight: 600;
}

.nivel-requirement i {
    color: var(--primary-purple);
}

.nivel-progress-bar {
    padding: 0 1rem 1rem;
    border-top: 1px solid rgba(59, 130, 246, 0.2);
    margin-top: 1rem;
}

.nivel-progress-bar .progress-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.nivel-progress-bar .progress-label {
    color: var(--gray-600);
}

.nivel-progress-bar .progress-value {
    font-weight: 700;
    color: var(--primary-blue);
}

.nivel-progress-bar .progress-bar {
    height: 0.5rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: var(--border-radius-full);
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.nivel-progress-bar .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-blue), #3b82f6);
    border-radius: var(--border-radius-full);
    transition: width 0.3s ease;
}

.tickets-remaining {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--primary-blue);
    font-weight: 600;
}

.tickets-remaining i {
    color: var(--primary-blue);
}

.nivel-status {
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
}

.nivel-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.badge-completed {
    background: #dcfce7;
    color: #166534;
}

.badge-current {
    background: #dbeafe;
    color: #1e40af;
}

.badge-pending {
    background: #f3f4f6;
    color: #6b7280;
}

.premio-locked-info {
    padding: 2rem 1.5rem;
    text-align: center;
    color: var(--gray-500);
}

.premio-locked-info i {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: var(--gray-400);
}

.premio-locked-info p {
    margin: 0;
    font-size: 0.875rem;
}

/* Responsive para premios progresivos */
@media (max-width: 768px) {
    .premio-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .premio-icon {
        align-self: center;
    }

    .nivel-progress {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .nivel-details {
        text-align: center;
    }

    .nivel-requirement {
        justify-content: center;
    }
}

.prizes-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.prize-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    transition: all 0.3s ease;
}

.prize-item.unlocked {
    border-color: #6ee7b7;
    background: #d1fae5;
}

.prize-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.prize-number {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: var(--white);
    background: var(--gray-400);
    font-size: 0.875rem;
}

.prize-number.unlocked {
    background: var(--success-green);
}

.prize-details h4 {
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: var(--gray-900);
}

.prize-details p {
    font-size: 0.875rem;
    color: var(--gray-600);
    margin: 0;
}

.prize-status {
    font-size: 1.25rem;
}

.prize-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.prize-badge-unlocked {
    background: #10b981;
    color: var(--white);
}

.prize-badge-locked {
    background: #6b7280;
    color: var(--white);
}

.participate-card {
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
    color: var(--white);
}

.participate-content {
    text-align: center;
    padding: 2rem;
}

.participate-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.participate-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.participate-subtitle {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1.5rem;
}

.participate-btn {
    background: var(--white);
    color: var(--primary-purple);
    font-weight: 700;
    padding: 0.75rem 1.5rem;
    width: 100%;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.participate-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.participate-btn:disabled {
    background: var(--gray-400);
    color: var(--white);
    cursor: not-allowed;
}

/* Estilos para botones de premio */
.premio-actions {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--gray-200);
}

.premio-detail-btn {
    background: var(--primary-blue);
    color: var(--white);
    font-weight: 600;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    width: 100%;
    justify-content: center;
}

.premio-detail-btn.btn-primary {
    background: var(--primary-blue);
}

.premio-detail-btn.btn-secondary {
    background: var(--gray-600);
}

.premio-detail-btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.premio-detail-btn.btn-primary:hover {
    background: #1e40af;
}

.premio-detail-btn.btn-secondary:hover {
    background: var(--gray-700);
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.info-item i {
    color: var(--gray-500);
    width: 1rem;
}

.participants-list {
    max-height: 200px;
    overflow-y: auto;
}

.participant-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.participant-avatar {
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 0.875rem;
    font-weight: 700;
}

.participant-name {
    flex: 1;
}

.participants-more {
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.5rem;
    text-align: center;
}

/* Modal */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 2rem;
}

.modal {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-xl);
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 2rem;
    border-bottom: 1px solid var(--gray-200);
    position: relative;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--gray-900);
}

.modal-description {
    color: var(--gray-600);
    font-size: 0.875rem;
}

.close-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--gray-500);
    cursor: pointer;
}

.modal-content {
    padding: 2rem;
}

.payment-step {
    padding: 1rem;
    border-radius: var(--border-radius);
    margin-bottom: 1rem;
}

.step-blue {
    background: #dbeafe;
}

.step-green {
    background: #d1fae5;
}

.step-yellow {
    background: #fef3c7;
}

.step-purple {
    background: #f3e8ff;
}

.step-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--gray-800);
}

.code-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    margin: 0.5rem 0;
}

.code-text {
    flex: 1;
    font-family: monospace;
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--primary-blue);
}

.copy-btn {
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    border: 1px solid var(--gray-300);
    background: var(--white);
    color: var(--gray-700);
    border-radius: var(--border-radius);
    cursor: pointer;
}

.payment-info {
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    color: var(--gray-700);
}

.payment-content {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1.5rem;
    align-items: start;
}

.payment-apps {
    display: flex;
    gap: 1rem;
    margin: 0.75rem 0;
}

.payment-app {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: var(--white);
    border-radius: var(--border-radius);
    border: 1px solid var(--gray-300);
    font-size: 0.875rem;
}

.app-icon {
    width: 1.5rem;
    height: 1.5rem;
    object-fit: contain;
}

.payment-qr {
    display: flex;
    justify-content: center;
    align-items: center;
}

.qr-container {
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    min-width: 200px;
}

.qr-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: var(--primary-purple);
    font-weight: 600;
    font-size: 0.9rem;
}

.qr-header i {
    font-size: 1.2rem;
}

.qr-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.5rem;
    background: var(--gray-50);
    border-radius: var(--border-radius);
}

.qr-image {
    width: 160px;
    height: 160px;
    object-fit: contain;
    display: block;
}

.qr-footer {
    border-top: 1px solid var(--gray-200);
    padding-top: 1rem;
}

.qr-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--gray-700);
    margin: 0 0 0.25rem 0;
}

.qr-subtitle {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.qr-placeholder {
    width: 120px;
    height: 120px;
    background: var(--white);
    border: 2px dashed var(--gray-300);
    border-radius: var(--border-radius);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.qr-placeholder i {
    font-size: 2rem;
    color: var(--gray-400);
    margin-bottom: 0.5rem;
}

.qr-placeholder p {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-600);
    margin: 0;
}

.qr-placeholder small {
    font-size: 0.75rem;
    color: var(--gray-500);
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
}

.file-help {
    font-size: 0.75rem;
    color: var(--gray-600);
    margin-top: 0.25rem;
}

.payment-warning {
    font-size: 0.875rem;
    color: #dc2626;
    font-weight: 600;
    margin-top: 0.5rem;
}

.confirm-btn {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
    color: var(--white);
    border: none;
    border-radius: var(--border-radius);
    padding: 0.75rem 1.5rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.confirm-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.confirm-btn:disabled {
    background: var(--gray-400);
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .rifa-detail-grid {
        grid-template-columns: 1fr;
    }

    .rifa-detail-content {
        grid-template-columns: 1fr;
    }

    .specs-grid {
        grid-template-columns: 1fr;
    }

    .spec-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .spec-value {
        text-align: left;
    }

    .rifa-header {
        flex-direction: column;
        gap: 1rem;
    }

    .rifa-price-display {
        text-align: left;
    }

    .rifa-stats {
        grid-template-columns: 1fr;
    }

    .modal {
        margin: 1rem;
    }

    .modal-header,
    .modal-content {
        padding: 1.5rem;
    }

    .payment-content {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .payment-apps {
        justify-content: center;
    }

    .qr-container {
        min-width: auto;
        width: 100%;
        max-width: 250px;
        margin: 0 auto;
    }

    .qr-image {
        width: 140px;
        height: 140px;
    }
}

/* User tickets info */
.user-tickets-info {
    background: linear-gradient(135deg, #e0f2fe, #b3e5fc);
    border: 1px solid #4fc3f7;
    border-radius: var(--border-radius);
    padding: 1rem;
    margin: 1rem 0;
    text-align: center;
}

.tickets-owned {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #0277bd;
    margin-bottom: 0.75rem;
}

.tickets-owned i {
    color: #0277bd;
}

.ticket-numbers {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
}

.ticket-number {
    background: #0277bd;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius-full);
    font-size: 0.875rem;
    font-weight: 600;
}

.auth-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    padding: 0.75rem;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: var(--border-radius);
    color: #856404;
    font-size: 0.875rem;
}

.auth-notice i {
    color: #ffc107;
}

/* Estilos para selección de tickets */
.ticket-selection-section {
    margin-top: 1rem;
}

.selection-options {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.random-btn {
    background: linear-gradient(135deg, #8b5cf6, #a855f7);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: var(--border-radius-full);
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(139, 92, 246, 0.3);
}

.random-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -1px rgba(139, 92, 246, 0.4);
}

.separator {
    color: var(--gray-500);
    font-weight: 500;
}

.manual-text {
    color: var(--gray-700);
    font-weight: 500;
}

.tickets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    max-height: 300px;
    overflow-y: auto;
    padding: 1rem;
    background: var(--gray-50);
    border-radius: var(--border-radius);
    border: 2px dashed var(--gray-300);
}

.ticket-number {
    background: white;
    border: 2px solid var(--gray-300);
    color: var(--gray-700);
    padding: 0.75rem 0.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
    min-height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ticket-number:hover:not(:disabled) {
    border-color: var(--primary-purple);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.ticket-number.selected {
    background: var(--primary-purple);
    border-color: var(--primary-purple);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
}

.ticket-number.sold {
    background: var(--gray-200);
    border-color: var(--gray-300);
    color: var(--gray-500);
    cursor: not-allowed;
    opacity: 0.6;
    position: relative;
}

.ticket-number.sold::after {
    content: '✗';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: var(--danger-red);
    font-weight: bold;
    font-size: 1rem;
}

.ticket-info {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    justify-content: center;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--gray-600);
}

.legend {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 2px solid;
}

.legend.available {
    background: white;
    border-color: var(--gray-300);
}

.legend.sold {
    background: var(--gray-200);
    border-color: var(--gray-300);
}

.legend.selected {
    background: var(--primary-purple);
    border-color: var(--primary-purple);
}

.selected-ticket-info {
    background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
    border: 2px solid var(--primary-purple);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    text-align: center;
}

.selected-display {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    font-size: 1.1rem;
    color: var(--gray-800);
}

.selected-display i {
    color: var(--primary-purple);
    font-size: 1.5rem;
}

.confirm-selection-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    padding: 0.875rem 2rem;
    border-radius: var(--border-radius-full);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
    font-size: 1rem;
}

.confirm-selection-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px -1px rgba(16, 185, 129, 0.4);
}

.selected-ticket-banner {
    background: linear-gradient(135deg, #ddd6fe, #c4b5fd);
    border: 2px solid var(--primary-purple);
    border-radius: var(--border-radius-lg);
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.ticket-banner-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.ticket-banner-content>span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.1rem;
    color: var(--gray-800);
}

.ticket-banner-content i {
    color: var(--primary-purple);
    font-size: 1.25rem;
}

.change-ticket-btn {
    background: var(--white);
    color: var(--primary-purple);
    border: 2px solid var(--primary-purple);
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.change-ticket-btn:hover {
    background: var(--primary-purple);
    color: white;
}

@media (max-width: 640px) {
    .tickets-grid {
        grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
        gap: 0.25rem;
        padding: 0.75rem;
    }

    .ticket-number {
        padding: 0.5rem 0.25rem;
        font-size: 0.75rem;
        min-height: 40px;
    }

    .ticket-info {
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }

    .selection-options {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.75rem;
    }

    .ticket-banner-content {
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }
}

/* File Upload Styles */
.file-upload-section {
    margin-bottom: 1.5rem;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.file-upload-label i {
    color: var(--primary-purple);
}

.file-input {
    display: none;
}

.file-upload-area {
    border: 2px dashed var(--gray-300);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--gray-50);
}

.file-upload-area:hover {
    border-color: var(--primary-purple);
    background: rgba(147, 51, 234, 0.05);
}

.file-upload-area.dragover {
    border-color: var(--primary-blue);
    background: rgba(59, 130, 246, 0.05);
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.upload-placeholder i {
    font-size: 2rem;
    color: var(--gray-400);
}

.upload-placeholder p {
    margin: 0;
    color: var(--gray-600);
    font-weight: 500;
}

.upload-placeholder small {
    color: var(--gray-500);
    font-size: 0.75rem;
}

.file-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.preview-image {
    max-width: 200px;
    max-height: 150px;
    object-fit: cover;
    border-radius: var(--border-radius);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.file-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--white);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    font-size: 0.875rem;
}

.file-info i {
    color: var(--primary-blue);
}

.remove-file-btn {
    background: var(--danger-red);
    color: var(--white);
    border: none;
    border-radius: 50%;
    width: 1.5rem;
    height: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.remove-file-btn:hover {
    background: #dc2626;
}

/* Process Info Styles */
.auto-process-info {
    background: linear-gradient(135deg, #e0f2fe, #f0f9ff);
    border: 1px solid #0ea5e9;
    border-radius: var(--border-radius);
    padding: 1rem;
    margin-bottom: 1rem;
}

.manual-process-section {
    background: #fefce8;
    border: 1px solid #facc15;
    border-radius: var(--border-radius);
    padding: 1rem;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.info-header i {
    color: var(--primary-blue);
}

.manual-process-section .info-header i {
    color: #f59e0b;
}

.info-header small {
    color: var(--gray-500);
    font-weight: 400;
    font-size: 0.75rem;
    margin-left: 0.25rem;
}

.auto-process-info p,
.manual-process-section p {
    margin: 0;
    color: var(--gray-600);
    font-size: 0.875rem;
    line-height: 1.4;
}

.process-separator {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.process-separator::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--gray-300);
    z-index: 1;
}

.process-separator span {
    background: #fef3c7;
    padding: 0 1rem;
    color: var(--gray-600);
    font-size: 0.875rem;
    font-weight: 500;
    position: relative;
    z-index: 2;
}

.manual-confirm {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    margin-top: 1rem;
}

.manual-confirm:disabled {
    background: var(--gray-400) !important;
    cursor: not-allowed;
    transform: none;
}

.manual-requirement {
    margin: 0.75rem 0 0 0;
    font-size: 0.75rem;
    color: #92400e;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.manual-requirement i {
    color: #f59e0b;
}

/* Responsive styles for file upload */
@media (max-width: 768px) {
    .file-upload-area {
        padding: 1rem;
    }
    
    .preview-image {
        max-width: 150px;
        max-height: 100px;
    }
    
    .upload-placeholder i {
        font-size: 1.5rem;
    }
}
</style>
