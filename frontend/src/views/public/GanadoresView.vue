<template>
  <div class="ganadores-page">
    <AppHeader />

    <!-- Hero Section -->
    <section class="hero hero-enhanced hero-secondary">
      <div class="hero-bg"></div>
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-crown crown-icon"></i>
            <h1 class="hero-title">🏆 Ganadores</h1>
            <i class="fas fa-crown crown-icon"></i>
          </div>
          <p class="hero-subtitle">
            Conoce a los afortunados ganadores de nuestras rifas
          </p>
        </div>
      </div>
    </section>

    <!-- Ganadores Recientes -->
    <section class="ganadores-section">
      <div class="container">
        <h2 class="section-title">🎉 Ganadores Recientes</h2>
        
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando ganadores...</p>
        </div>

        <div v-else class="ganadores-grid">
          <div 
            v-for="ganador in ganadores" 
            :key="ganador.id" 
            class="ganador-card"
          >
            <div class="ganador-image">
              <img :src="ganador.premio.imagen" :alt="ganador.premio.nombre" @error="handleImageError">
              <div class="ganador-badge">
                <i class="fas fa-trophy"></i>
                <span>Ganador</span>
              </div>
            </div>
            
            <div class="ganador-content">
              <div class="ganador-header">
                <div class="ganador-avatar">
                  {{ ganador.nombre.charAt(0) }}
                </div>
                <div class="ganador-info">
                  <h3 class="ganador-nombre">{{ ganador.nombre }}</h3>
                  <p class="ganador-fecha">{{ formatDate(ganador.fechaSorteo) }}</p>
                </div>
              </div>

              <div class="premio-info">
                <h4 class="premio-nombre">{{ ganador.premio.nombre }}</h4>
                <p class="premio-descripcion">{{ ganador.premio.descripcion }}</p>
              </div>

              <div class="rifa-stats">
                <div class="stat-item">
                  <i class="fas fa-ticket-alt"></i>
                  <span>Ticket: #{{ ganador.ticketGanador }}</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-users"></i>
                  <span>{{ ganador.totalParticipantes }} participantes</span>
                </div>
              </div>

              <div class="ganador-actions">
                <button class="btn btn-outline" @click="verCertificado(ganador)">
                  <i class="fas fa-certificate"></i>
                  &nbsp;
                  Ver Certificado
                </button>
                <button class="btn btn-primary" @click="verDetalles(ganador)">
                  <i class="fas fa-eye"></i>
                  &nbsp;
                  Ver Detalles
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas Generales -->
        <div class="estadisticas-section">
          <h3 class="stats-title">📊 Estadísticas Generales</h3>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-trophy"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number">{{ totalGanadores }}</div>
                <div class="stat-label">Ganadores Totales</div>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-gift"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number">S/ {{ totalPremiosEntregados.toLocaleString() }}</div>
                <div class="stat-label">En Premios Entregados</div>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-calendar"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number">{{ rifasCompletadas }}</div>
                <div class="stat-label">Rifas Completadas</div>
              </div>
            </div>
            
            <div class="stat-card">
              <div class="stat-icon">
                <i class="fas fa-smile"></i>
              </div>
              <div class="stat-content">
                <div class="stat-number">98%</div>
                <div class="stat-label">Satisfacción</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonios -->
    <section class="testimonios-section">
      <div class="container">
        <h2 class="section-title">💬 Lo que dicen nuestros ganadores</h2>
        <div class="testimonios-grid">
          <div 
            v-for="testimonio in testimonios" 
            :key="testimonio.id"
            class="testimonio-card"
          >
            <div class="testimonio-content">
              <div class="quote-icon">
                <i class="fas fa-quote-left"></i>
              </div>
              <p class="testimonio-texto">{{ testimonio.texto }}</p>
            </div>
            <div class="testimonio-author">
              <div class="author-avatar">
                {{ testimonio.nombre.charAt(0) }}
              </div>
              <div class="author-info">
                <span class="author-name">{{ testimonio.nombre }}</span>
                <span class="author-premio">Ganó: {{ testimonio.premio }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <AppFooter />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import { formatDate } from '@/utils/helpers'

export default {
  name: 'Ganadores',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const router = useRouter()
    const loading = ref(true)
    const ganadores = ref([])
    const testimonios = ref([])
    
    const totalGanadores = ref(156)
    const totalPremiosEntregados = ref(45000)
    const rifasCompletadas = ref(23)

    const loadGanadores = async () => {
      loading.value = true
      
      // Simular carga de datos
      setTimeout(() => {
        ganadores.value = [
          {
            id: 1,
            nombre: "María González",
            fechaSorteo: "2024-12-15",
            ticketGanador: "RF001-A7B9",
            totalParticipantes: 156,
            premio: {
              nombre: "iPhone 15 Pro 256GB",
              descripcion: "Último modelo con cámara profesional y chip A17 Pro",
              imagen: "https://pe.tiendasishop.com/cdn/shop/files/IMG-10935051_c217d008-b374-4d0d-adbf-16768c816296.jpg?v=1722624560&width=823"
            }
          },
          {
            id: 2,
            nombre: "Carlos Mendoza",
            fechaSorteo: "2024-12-10",
            ticketGanador: "RF002-X3M5",
            totalParticipantes: 89,
            premio: {
              nombre: "MacBook Air M3",
              descripcion: "Laptop ultraligera con chip M3 y 16GB RAM",
              imagen: "https://pe.tiendasishop.com/cdn/shop/files/macbook-air-midnight-select-20220606.jpg?v=1722624560&width=823"
            }
          },
          {
            id: 3,
            nombre: "Ana Ruiz",
            fechaSorteo: "2024-12-05",
            ticketGanador: "RF003-K8L2",
            totalParticipantes: 203,
            premio: {
              nombre: "Apple Watch Ultra 2",
              descripcion: "Smartwatch para deportes extremos con GPS precisión dual",
              imagen: "https://oechsle.vteximg.com.br/arquivos/ids/15748293-1000-1000/image-1d379c72d9244b4194d2d185fd70cb4d.jpg?v=638280695205200000"
            }
          },
          {
            id: 4,
            nombre: "Luis Torres",
            fechaSorteo: "2024-11-28",
            ticketGanador: "RF004-P9Q1",
            totalParticipantes: 67,
            premio: {
              nombre: "AirPods Pro 2da Gen",
              descripcion: "Auriculares inalámbricos con cancelación activa de ruido",
              imagen: "https://pe.tiendasishop.com/cdn/shop/files/IMG-14912675.jpg?v=1727286009&width=1000"
            }
          }
        ]

        testimonios.value = [
          {
            id: 1,
            nombre: "María González",
            premio: "iPhone 15 Pro",
            texto: "¡No podía creerlo cuando vi mi nombre! El proceso fue transparente y muy fácil. Definitivamente seguiré participando en más rifas."
          },
          {
            id: 2,
            nombre: "Carlos Mendoza", 
            premio: "MacBook Air M3",
            texto: "Excelente servicio y muy rápida la entrega. La MacBook llegó en perfectas condiciones. ¡Gracias Danilore Rifas!"
          },
          {
            id: 3,
            nombre: "Ana Ruiz",
            premio: "Apple Watch Ultra 2",
            texto: "Mi primer sorteo ganado y qué sorpresa tan increíble. El Apple Watch es exactamente lo que necesitaba para mis entrenamientos."
          }
        ]

        loading.value = false
      }, 1000)
    }

    const handleImageError = (event) => {
      event.target.src = 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop&crop=center'
    }

    const verCertificado = (ganador) => {
      // Implementar vista de certificado
      console.log('Ver certificado de:', ganador.nombre)
    }

    const verDetalles = (ganador) => {
      // Navegar a detalles del sorteo
      router.push(`/sorteo/${ganador.id}`)
    }

    onMounted(() => {
      loadGanadores()
    })

    return {
      loading,
      ganadores,
      testimonios,
      totalGanadores,
      totalPremiosEntregados,
      rifasCompletadas,
      handleImageError,
      verCertificado,
      verDetalles,
      formatDate
    }
  }
}
</script>

<style scoped>
.ganadores-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--gray-50);
}

/* Hero Section */
.hero {
  position: relative;
  padding: 4rem 0;
  text-align: center;
  background: linear-gradient(135deg, var(--accent-yellow) 0%, var(--accent-orange) 50%, var(--primary-gold) 100%);
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.1);
}

.hero-content {
  position: relative;
  color: white;
}

.hero-icons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.crown-icon {
  font-size: 2.5rem;
  color: #fef3c7;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}

.hero-title {
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700;
  margin: 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.hero-subtitle {
  font-size: 1.25rem;
  margin: 0;
  opacity: 0.9;
}

/* Ganadores Section */
.ganadores-section {
  padding: 4rem 0;
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--gray-800);
  margin-bottom: 3rem;
}

.loading-state {
  text-align: center;
  padding: 4rem 2rem;
}

.loading-state i {
  font-size: 3rem;
  color: var(--primary-purple);
  margin-bottom: 1rem;
}

.ganadores-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
  margin-bottom: 4rem;
}

.ganador-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  transition: all 0.3s ease;
}

.ganador-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.ganador-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.ganador-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.ganador-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: linear-gradient(135deg, var(--accent-yellow), var(--accent-orange));
  color: white;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius-full);
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.ganador-content {
  padding: 1.5rem;
}

.ganador-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.ganador-avatar {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 1.25rem;
}

.ganador-info h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.ganador-fecha {
  font-size: 0.875rem;
  color: var(--gray-500);
  margin: 0;
}

.premio-info {
  margin-bottom: 1rem;
}

.premio-nombre {
  font-size: 1rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.premio-descripcion {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin: 0;
}

.rifa-stats {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: var(--gray-600);
}

.stat-item i {
  color: var(--primary-purple);
}

.ganador-actions {
  display: flex;
  gap: 0.75rem;
}

.ganador-actions .btn {
  flex: 1;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

/* Estadísticas */
.estadisticas-section {
  margin-top: 4rem;
  padding: 2rem;
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.stats-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
}

.stat-card:hover {
  background: var(--gray-100);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gray-800);
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

/* Testimonios */
.testimonios-section {
  padding: 4rem 0;
  background: var(--white);
}

.testimonios-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.testimonio-card {
  background: var(--gray-50);
  border-radius: var(--border-radius-lg);
  padding: 2rem;
  position: relative;
}

.testimonio-content {
  margin-bottom: 1.5rem;
}

.quote-icon {
  color: var(--primary-purple);
  font-size: 2rem;
  margin-bottom: 1rem;
}

.testimonio-texto {
  font-style: italic;
  color: var(--gray-700);
  line-height: 1.6;
}

.testimonio-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
}

.author-name {
  font-weight: 600;
  color: var(--gray-800);
  display: block;
}

.author-premio {
  font-size: 0.875rem;
  color: var(--gray-600);
}

/* Responsive */
@media (max-width: 768px) {
  .ganadores-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .stat-card {
    padding: 1rem;
  }

  .testimonios-grid {
    grid-template-columns: 1fr;
  }

  .ganador-actions {
    flex-direction: column;
  }
}
</style>
