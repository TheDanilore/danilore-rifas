<template>
    <div class="premio-detail-page">
        <AppHeader />

        <div v-if="loading" class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando información del premio...</p>
        </div>

        <div v-else-if="error" class="error-state">
            <i class="fas fa-exclamation-triangle"></i>
            <p>{{ error }}</p>
            <button class="btn btn-primary" @click="loadPremio">Reintentar</button>
        </div>

        <div v-else-if="premio" class="premio-detail-container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <router-link to="/">Inicio</router-link>
                <span> / </span>
                <span>{{ premio.titulo }}</span>
            </nav>

            <div class="premio-detail-grid">
                <!-- Contenido Principal -->
                <div class="premio-main">
                    <!-- Header Card -->
                    <div class="card">
                        <div class="card-header">
                            <div class="premio-header">
                                <div>
                                    <h1>{{ premio.titulo }}</h1>
                                    <div class="premio-status-badge" :class="`status-${(premio.estado_texto || 'bloqueado').toLowerCase().replace(' ', '_')}`">
                                        {{ premio.estado_texto || 'Bloqueado' }}
                                    </div>
                                </div>
                                <div class="premio-order">
                                    <div class="order-number">Premio {{ premio.orden || (premios.findIndex(p => p.id === premio.id) + 1) }}</div>
                                    <div class="order-label">de {{ totalPremios }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-content">
                            <!-- Galería de medios del premio -->
                            <div class="premio-media">
                                <MediaGallery 
                                    v-if="premio.mediaGallery"
                                    :mediaGallery="premio.mediaGallery"
                                />
                                <!-- Fallback para premios sin galería -->
                                <div v-else class="premio-image-container">
                                    <img :src="premio.imagen" :alt="premio.titulo" class="premio-detail-image"
                                        @error="handleImageError">
                                </div>
                            </div>

                            <div class="premio-info">
                                <div class="premio-description">
                                    <h3>Descripción del Premio</h3>
                                    <p>{{ premio.descripcion }}</p>
                                </div>

                                <!-- Estado del premio en la rifa -->
                                <div class="premio-rifa-status">
                                    <h3>Estado en la Rifa</h3>
                                    <div class="status-card" :class="{
                                        'status-active': premio.esta_activo,
                                        'status-completed': premio.completado,
                                        'status-locked': !premio.desbloqueado
                                    }">
                                        <div class="status-icon">
                                            <i v-if="premio.completado" class="fas fa-check-circle"></i>
                                            <i v-else-if="!premio.desbloqueado" class="fas fa-lock"></i>
                                            <i v-else class="fas fa-clock"></i>
                                        </div>
                                        <div class="status-content">
                                            <h4>{{ premio.estado_texto }}</h4>
                                            <p v-if="premio.completado">¡Todos los niveles han sido completados!</p>
                                            <p v-else-if="!premio.desbloqueado">Se desbloqueará al completar: <strong>{{ premio.premio_requerido }}</strong></p>
                                            <p v-else>Premio activo - Sigue participando para desbloquear niveles</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Niveles del Premio -->
                    <div class="card">
                        <div class="card-header">
                            <div class="niveles-header">
                                <i class="fas fa-layer-group"></i>
                                <h2>Niveles del Premio</h2>
                                <span class="niveles-count">({{ premio.niveles?.length || 0 }} niveles)</span>
                            </div>
                        </div>
                        <div class="card-content">
                            <div v-if="premio.desbloqueado" class="niveles-list">
                                <div 
                                    v-for="(nivel, index) in premio.niveles" 
                                    :key="nivel.id"
                                    class="nivel-card"
                                    :class="{ 
                                        'nivel-unlocked': nivel.desbloqueado,
                                        'nivel-current': nivel.es_actual,
                                        'nivel-pending': !nivel.desbloqueado && !nivel.es_actual
                                    }"
                                >
                                    <!-- Nivel Header -->
                                    <div class="nivel-header">
                                        <div class="nivel-number" :class="{ 'unlocked': nivel.desbloqueado }">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="nivel-info">
                                            <h4 class="nivel-title">{{ nivel.titulo }}</h4>
                                            <p class="nivel-description">{{ nivel.descripcion }}</p>
                                            <div class="nivel-valor">
                                                <i class="fas fa-gift"></i>
                                                Valor: {{ nivel.valor }}
                                            </div>
                                        </div>
                                        <div class="nivel-status">
                                            <span 
                                                class="nivel-badge" 
                                                :class="{
                                                    'badge-completed': nivel.desbloqueado,
                                                    'badge-current': nivel.es_actual,
                                                    'badge-pending': !nivel.desbloqueado && !nivel.es_actual
                                                }"
                                            >
                                                <i v-if="nivel.desbloqueado" class="fas fa-check"></i>
                                                <i v-else-if="nivel.es_actual" class="fas fa-clock"></i>
                                                <i v-else class="fas fa-lock"></i>
                                                {{ nivel.estado_texto }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Nivel Image -->
                                    <div class="nivel-image-container">
                                        <img :src="nivel.imagen" :alt="nivel.titulo" class="nivel-image" @error="handleImageError">
                                    </div>

                                    <!-- Progreso del nivel si es el actual -->
                                    <div v-if="nivel.es_actual" class="nivel-progress-section">
                                        <div class="progress-info">
                                            <span class="progress-label">Progreso actual</span>
                                            <span class="progress-value">{{ rifaActual.ticketsVendidos }}/{{ nivel.tickets_necesarios }}</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div 
                                                class="progress-fill" 
                                                :style="{ width: `${Math.min((rifaActual.ticketsVendidos / nivel.tickets_necesarios) * 100, 100)}%` }"
                                            ></div>
                                        </div>
                                        <div class="tickets-remaining">
                                            <i class="fas fa-target"></i>
                                            Faltan {{ Math.max(0, nivel.tickets_necesarios - rifaActual.ticketsVendidos) }} tickets para desbloquear
                                        </div>
                                    </div>

                                    <!-- Requirement info -->
                                    <div class="nivel-requirement">
                                        <i class="fas fa-ticket-alt"></i>
                                        <strong>{{ nivel.tickets_necesarios }} tickets necesarios</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Premio Bloqueado -->
                            <div v-else class="premio-locked-section">
                                <div class="locked-icon">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h3>Premio Bloqueado</h3>
                                <p>Este premio se desbloqueará automáticamente cuando se complete: <strong>{{ premio.premio_requerido }}</strong></p>
                                <router-link to="/" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i>
                                    Volver al Inicio
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="premio-sidebar">
                    <!-- Participar Card -->
                    <div class="card participate-card">
                        <div class="participate-content">
                            <div class="participate-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3 class="participate-title">¡Participa por este Premio!</h3>
                            <p class="participate-subtitle">Solo S/ {{ rifaActual?.precio || 2 }} por ticket</p>
                            
                            <!-- Estado de participación -->
                            <div v-if="userTicketsForPremio.length > 0" class="participation-status">
                                <div class="status-info">
                                    <i class="fas fa-check-circle"></i>
                                    <span>¡Ya estás participando!</span>
                                </div>
                                <p class="tickets-info">Tienes {{ userTicketsForPremio.length }} ticket{{ userTicketsForPremio.length > 1 ? 's' : '' }} para este premio</p>
                                
                                <button 
                                    class="participate-btn secondary"
                                    :disabled="!premio.desbloqueado"
                                    @click="showPaymentModal"
                                >
                                    🎫 Comprar Otro Ticket
                                </button>
                            </div>
                            
                            <!-- Primera participación -->
                            <div v-else>
                                <button 
                                    class="participate-btn"
                                    :disabled="!premio.desbloqueado"
                                    @click="showPaymentModal"
                                >
                                    {{ isAuthenticated ? '🎫 Comprar Ticket' : '🔒 Inicia Sesión para Participar' }}
                                </button>
                            </div>
                            
                            <div v-if="!isAuthenticated" class="auth-notice">
                                <i class="fas fa-info-circle"></i>
                                <span>Necesitas iniciar sesión para participar</span>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Premio -->
                    <div class="card info-card">
                        <div class="card-header">
                            <h3>Información del Premio</h3>
                        </div>
                        <div class="card-content">
                            <div class="info-item">
                                <i class="fas fa-layer-group"></i>
                                <span>{{ premio.niveles?.length || 0 }} niveles</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-trophy"></i>
                                <span>Premio {{ premio.orden }} de {{ totalPremios }}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>{{ premio.estado_texto }}</span>
                            </div>
                            <div v-if="premio.desbloqueado" class="info-item">
                                <i class="fas fa-check-circle"></i>
                                <span>{{ premio.niveles?.filter(n => n.desbloqueado).length || 0 }} completados</span>
                            </div>
                        </div>
                    </div>

                    <!-- Progreso del Premio Actual -->
                    <div v-if="rifaActual && premio && premio.desbloqueado" class="card progress-card">
                        <div class="card-header">
                            <h3>Progreso del Premio</h3>
                        </div>
                        <div class="card-content">
                            <div class="progress-info-header">
                                <div class="progress-detail-item">
                                    <span class="detail-label">Tickets Vendidos:</span>
                                    <span class="detail-value">{{ rifaActual.ticketsVendidos }}</span>
                                </div>
                                <div class="progress-detail-item">
                                    <span class="detail-label">{{ nivelActual ? 'Meta del Nivel Actual:' : 'Meta del Primer Nivel:' }}</span>
                                    <span class="detail-value">{{ metaNivelActual }}</span>
                                </div>
                                <div class="progress-detail-item">
                                    <span class="detail-label">Progreso:</span>
                                    <span class="detail-value">{{ Math.round((rifaActual.ticketsVendidos / metaNivelActual) * 100) }}%</span>
                                </div>
                            </div>
                            
                            <!-- Barra de progreso del nivel actual -->
                            <div class="rifa-progress-bar-container">
                                <div class="rifa-progress-bar">
                                    <div class="progress-fill" :style="{ width: `${Math.min((rifaActual.ticketsVendidos / metaNivelActual) * 100, 100)}%` }"></div>
                                </div>
                            </div>
                            
                            <div v-if="nivelActual" class="nivel-actual-info">
                                <p><strong>Nivel Actual:</strong> {{ nivelActual.titulo }}</p>
                                <p>{{ nivelActual.descripcion }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AppFooter />

        <!-- Modal de pago (reutilizar el existente) -->
        <div v-if="paymentModal" class="modal-overlay" @click="closePaymentModal">
            <div class="modal payment-modal" @click.stop>
                <!-- Contenido del modal de pago igual que en RifaDetail -->
                <div class="modal-header">
                    <h2 class="modal-title">Comprar Ticket - {{ premio?.titulo }}</h2>
                    <p class="modal-description">Participa por este increíble premio</p>
                    <button class="close-btn" @click="closePaymentModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-content">
                    <!-- Reutilizar el contenido del modal de pago de RifaDetail -->
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
                            ⚠️ <strong>Importante:</strong> Debes incluir este código en el mensaje de tu pago Yape o Plin
                        </p>
                    </div>

                    <div class="payment-step step-green">
                        <h4 class="step-title">Paso 2: Realizar pago</h4>
                        <div class="payment-content">
                            <div class="payment-info-left">
                                <div class="payment-info">• Monto: <strong>S/ {{ rifaActual?.precio || 2 }}</strong></div>
                                <div class="payment-info">• Escanea el código QR con tu app:</div>
                                <div class="payment-apps">
                                    <div class="payment-app">
                                        <img src="https://logosenvector.com/logo/img/yape-37283.png" alt="Yape" class="app-icon">
                                        <span>Yape</span>
                                    </div>
                                    <div class="payment-app">
                                        <img src="https://images.seeklogo.com/logo-png/38/1/plin-logo-png_seeklogo-386806.png" alt="Plin" class="app-icon">
                                        <span>Plin</span>
                                    </div>
                                </div>
                                <div class="payment-warning">• <strong>Mensaje obligatorio:</strong> {{ paymentCode }}</div>
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

                    <div class="payment-step step-yellow">
                        <h4 class="step-title">Paso 3: Confirmar pago</h4>
                        <button 
                            class="confirm-btn"
                            :disabled="paymentLoading"
                            @click="confirmPayment"
                        >
                            <i v-if="paymentLoading" class="fas fa-spinner fa-spin"></i>
                            <span>{{ paymentLoading ? 'Confirmando...' : '✅ Confirmar Pago Realizado' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import MediaGallery from '@/components/MediaGallery.vue'
import { useRifaDetail } from '@/composables/useRifaDetail'
import { useAuthStore } from '@/store/auth'
import { copyToClipboard, showNotification } from '@/utils/helpers'

export default {
    name: 'PremioDetail',
    components: {
        AppHeader,
        AppFooter,
        MediaGallery
    },
    setup() {
        const route = useRoute()
        const router = useRouter()
        const rifaId = computed(() => route.params.rifaId)
        const premioId = computed(() => {
            const id = route.params.premioId
            console.log('Raw premioId from route:', id, 'type:', typeof id)
            // No hacer parseInt ya que los IDs son strings como "p1", "p2", etc.
            return id
        })
        const { isAuthenticated } = useAuthStore()

        const {
            rifa: rifaActual,
            loading,
            error,
            paymentModal,
            paymentCode,
            paymentLoading,
            loadRifa,
            showPaymentModal,
            closePaymentModal,
            confirmPayment,
            getPremiosProgresivos
        } = useRifaDetail()

        const premio = computed(() => {
            if (!rifaActual.value) {
                console.log('No rifaActual available')
                return null
            }
            const premios = getPremiosProgresivos()
            console.log('Buscando premio con ID:', premioId.value, 'en premios:', premios)
            console.log('Premios IDs disponibles:', premios.map(p => ({ id: p.id, type: typeof p.id })))
            
            // Buscar por ID como string
            const foundPremio = premios.find(p => p.id === premioId.value)
            
            console.log('Premio encontrado:', foundPremio)
            return foundPremio
        })

        const premios = computed(() => {
            if (!rifaActual.value) return []
            return getPremiosProgresivos()
        })

        const totalPremios = computed(() => {
            if (!rifaActual.value) return 0
            return getPremiosProgresivos().length
        })

        // Computed para el nivel actual del premio
        const nivelActual = computed(() => {
            if (!premio.value || !premio.value.niveles) return null
            return premio.value.niveles.find(n => n.es_actual) || premio.value.niveles[0]
        })

        // Meta del nivel actual (tickets necesarios)
        const metaNivelActual = computed(() => {
            return nivelActual.value?.tickets_necesarios || premio.value?.niveles?.[0]?.tickets_necesarios || 0
        })

        // Tickets del usuario para este premio específico
        const userTicketsForPremio = computed(() => {
            if (!isAuthenticated.value) return []
            const { getUserTicketsForRifa } = useAuthStore()
            const allTickets = getUserTicketsForRifa(rifaId.value)
            // Por ahora retornamos todos los tickets de la rifa
            // En una implementación real, filtrarías por premio específico
            return allTickets || []
        })

        const loadPremio = () => {
            console.log('Loading premio for rifaId:', rifaId.value)
            loadRifa(rifaId.value)
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

        const formatDate = (dateString) => {
            if (!dateString) return 'Fecha no disponible'
            const date = new Date(dateString)
            return date.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            })
        }

        const getProgressPercentage = (actual, objetivo) => {
            if (!objetivo || objetivo === 0) return 0
            return Math.min((actual / objetivo) * 100, 100)
        }

        onMounted(() => {
            console.log('PremioDetail mounted - rifaId:', rifaId.value, 'premioId:', premioId.value)
            loadPremio()
        })

        return {
            rifaActual,
            premio,
            premios,
            totalPremios,
            nivelActual,
            metaNivelActual,
            userTicketsForPremio,
            loading,
            error,
            paymentModal,
            paymentCode,
            paymentLoading,
            isAuthenticated,
            loadPremio,
            handleImageError,
            showPaymentModal,
            closePaymentModal,
            confirmPayment: handleConfirmPayment,
            copyPaymentCode,
            formatDate,
            getProgressPercentage
        }
    }
}
</script>

<style scoped>
.premio-detail-page {
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

.premio-detail-container {
    flex: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

@media (max-width: 768px) {
    .premio-detail-container {
        padding: 1rem 0.5rem;
    }
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

.premio-detail-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    box-sizing: border-box;
}

/* Responsive design */
@media (max-width: 768px) {
    .premio-detail-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        max-width: 100%;
    }
}

@media (max-width: 1200px) and (min-width: 769px) {
    .premio-detail-grid {
        max-width: 95%;
    }
}

.premio-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    min-width: 0 !important; /* Permite que el contenido se comprima si es necesario */
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    overflow: hidden;
}

.premio-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .premio-sidebar {
        gap: 1rem;
    }
}

.card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
    width: 100%;
    box-sizing: border-box;
}

.card-content {
    padding: 1.5rem;
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    overflow: hidden;
}

.premio-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.premio-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--gray-900);
    margin: 0 0 0.5rem 0;
}

.premio-status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: var(--border-radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--white);
}

.status-en_progreso {
    background: var(--primary-blue);
}

.status-completado {
    background: var(--success-green);
}

.status-bloqueado {
    background: var(--gray-500);
}

.premio-order {
    text-align: right;
}

.order-number {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-purple);
}

.order-label {
    font-size: 0.875rem;
    color: var(--gray-500);
}

.premio-media {
    margin-bottom: 2rem;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

/* Asegurar que MediaGallery tenga ancho consistente */
.premio-media .media-gallery {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 100% !important;
    flex-shrink: 0 !important;
    box-sizing: border-box;
}

/* Forzar el ancho de la vista principal */
.premio-media .media-gallery .gallery-main {
    width: 100% !important;
}

.premio-media .media-gallery .main-media {
    width: 100% !important;
    height: 400px !important;
}

.premio-image-container {
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.premio-detail-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: var(--border-radius-lg);
}

.premio-description {
    margin-bottom: 2rem;
}

.premio-description h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 1rem;
}

.premio-description p {
    color: var(--gray-600);
    line-height: 1.6;
}

.premio-rifa-status h3 {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 1rem;
}

.status-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    border: 2px solid;
}

.status-card.status-active {
    border-color: var(--primary-blue);
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
}

.status-card.status-completed {
    border-color: var(--success-green);
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.status-card.status-locked {
    border-color: var(--gray-300);
    background: var(--gray-50);
}

.status-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--white);
}

.status-active .status-icon {
    background: var(--primary-blue);
}

.status-completed .status-icon {
    background: var(--success-green);
}

.status-locked .status-icon {
    background: var(--gray-400);
}

.status-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
}

.status-content p {
    margin: 0;
    color: var(--gray-600);
}

.niveles-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.niveles-header i {
    color: var(--primary-purple);
}

.niveles-count {
    color: var(--gray-500);
    font-size: 0.875rem;
}

.niveles-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.nivel-card {
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    background: var(--white);
    transition: all 0.3s ease;
    overflow: hidden;
}

.nivel-card.nivel-unlocked {
    border-color: var(--success-green);
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
}

.nivel-card.nivel-current {
    border-color: var(--primary-blue);
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    box-shadow: 0 0 0 1px var(--primary-blue);
}

.nivel-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.nivel-number {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    background: var(--gray-300);
    color: var(--white);
}

.nivel-number.unlocked {
    background: var(--success-green);
}

.nivel-card.nivel-current .nivel-number {
    background: var(--primary-blue);
}

.nivel-info {
    flex: 1;
}

.nivel-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--gray-800);
    margin: 0 0 0.5rem 0;
}

.nivel-description {
    color: var(--gray-600);
    margin: 0 0 0.75rem 0;
    line-height: 1.4;
}

.nivel-valor {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary-purple);
    font-weight: 600;
    font-size: 0.875rem;
}

.nivel-status {
    display: flex;
    align-items: center;
}

.nivel-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius-full);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
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

.nivel-image-container {
    padding: 0 1.5rem;
}

.nivel-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: var(--border-radius);
}

.nivel-progress-section {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(59, 130, 246, 0.2);
    background: rgba(59, 130, 246, 0.05);
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
    color: var(--primary-blue);
}

.progress-bar {
    width: 100%;
    height: 0.75rem;
    background: rgba(59, 130, 246, 0.1);
    border-radius: var(--border-radius-full);
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-fill {
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

.nivel-requirement {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    background: var(--gray-50);
}

.nivel-requirement i {
    color: var(--primary-purple);
}

.premio-locked-section {
    text-align: center;
    padding: 3rem 2rem;
}

.locked-icon {
    font-size: 4rem;
    color: var(--gray-400);
    margin-bottom: 1rem;
}

.premio-locked-section h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--gray-700);
    margin-bottom: 1rem;
}

.premio-locked-section p {
    color: var(--gray-600);
    margin-bottom: 2rem;
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

/* Estados de participación */
.participation-status {
    margin-bottom: 1rem;
}

.status-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: #22c55e;
    font-weight: 600;
}

.status-info i {
    font-size: 1.25rem;
}

.tickets-info {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.nivel-actual-info {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(147, 51, 234, 0.1);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary-purple);
}

.nivel-actual-info p {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    color: var(--gray-700);
}

.nivel-actual-info p:last-child {
    margin-bottom: 0;
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
}

.participate-btn.secondary {
    background: rgba(255, 255, 255, 0.2);
    color: var(--white);
    border: 2px solid rgba(255, 255, 255, 0.3);
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

/* Progreso de la Rifa - Nuevo estilo */
.progress-info-header {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.875rem;
}

.progress-detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-label {
    color: var(--gray-600);
}

.detail-value {
    font-weight: 600;
    color: var(--gray-800);
}

.rifa-progress-bar-container {
    position: relative;
    margin-bottom: 1rem;
}

.rifa-progress-bar {
    width: 100%;
    height: 1rem;
    background: var(--gray-200);
    border-radius: var(--border-radius-full);
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.rifa-progress-bar .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-purple), var(--primary-blue));
    border-radius: var(--border-radius-full);
    transition: width 0.3s ease;
}

.progress-start,
.progress-end {
    font-weight: 600;
}

/* Modal styles - reutilizar del RifaDetail */
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

.payment-warning {
    font-size: 0.875rem;
    color: #dc2626;
    font-weight: 600;
    margin-top: 0.5rem;
}

.confirm-btn {
    width: 100%;
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
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

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
    color: var(--white);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(147, 51, 234, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .premio-detail-grid {
        grid-template-columns: 1fr;
    }

    .premio-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .premio-order {
        text-align: center;
    }

    .nivel-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }

    .status-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
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
</style>
