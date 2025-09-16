<template>
  <div class="como-funciona-page">
    <AppHeader />

    <!-- Hero Section -->
    <section class="hero hero-enhanced hero-content-page">
      <div class="hero-bg"></div>
      <div class="container">
        <div class="hero-content">
          <div class="hero-icons">
            <i class="fas fa-info-circle info-icon"></i>
            <h1 class="hero-title">¿Cómo Funciona?</h1>
            <i class="fas fa-info-circle info-icon"></i>
          </div>
          <p class="hero-subtitle">
            Todo lo que necesitas saber sobre nuestras rifas
          </p>
        </div>
      </div>
    </section>

    <!-- Proceso Simple -->
    <section class="proceso-section">
      <div class="container">
        <h2 class="section-title">🚀 Proceso Simple en 4 Pasos</h2>
        
        <div class="pasos-grid">
          <div 
            v-for="(paso, index) in pasos" 
            :key="paso.id"
            class="paso-card"
            :class="{ 'paso-destacado': index === 0 }"
          >
            <div class="paso-numero">
              {{ index + 1 }}
            </div>
            <div class="paso-icon">
              <i :class="paso.icon"></i>
            </div>
            <h3 class="paso-titulo">{{ paso.titulo }}</h3>
            <p class="paso-descripcion">{{ paso.descripcion }}</p>
            
            <div v-if="paso.detalles" class="paso-detalles">
              <ul>
                <li v-for="detalle in paso.detalles" :key="detalle">
                  <i class="fas fa-check"></i>
                  {{ detalle }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Características -->
    <section class="caracteristicas-section">
      <div class="container">
        <h2 class="section-title">✨ ¿Por Qué Elegir Danilore Rifas?</h2>
        
        <div class="caracteristicas-grid">
          <div 
            v-for="caracteristica in caracteristicas" 
            :key="caracteristica.id"
            class="caracteristica-card"
          >
            <div class="caracteristica-icon">
              <i :class="caracteristica.icon"></i>
            </div>
            <h3 class="caracteristica-titulo">{{ caracteristica.titulo }}</h3>
            <p class="caracteristica-descripcion">{{ caracteristica.descripcion }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Sistema de Desbloqueo -->
    <section class="desbloqueo-section">
      <div class="container">
        <h2 class="section-title">🔓 Sistema de Desbloqueo Progresivo</h2>
        
        <div class="desbloqueo-content">
          <div class="desbloqueo-text">
            <h3>¿Cómo funcionan las rifas secuenciales?</h3>
            <p>
              Nuestro innovador sistema de desbloqueo progresivo hace que cada rifa sea más emocionante:
            </p>
            
            <div class="desbloqueo-pasos">
              <div class="desbloqueo-paso">
                <div class="paso-icon-small">
                  <i class="fas fa-play"></i>
                </div>
                <div class="paso-texto">
                  <strong>Rifa Activa:</strong> Solo una rifa está disponible para participar en cada momento
                </div>
              </div>
              
              <div class="desbloqueo-paso">
                <div class="paso-icon-small">
                  <i class="fas fa-target"></i>
                </div>
                <div class="paso-texto">
                  <strong>Objetivo Alcanzado:</strong> Cuando se venden suficientes tickets, la rifa se confirma
                </div>
              </div>
              
              <div class="desbloqueo-paso">
                <div class="paso-icon-small">
                  <i class="fas fa-unlock"></i>
                </div>
                <div class="paso-texto">
                  <strong>Desbloqueo Automático:</strong> ¡La siguiente rifa se habilita automáticamente!
                </div>
              </div>
            </div>
          </div>
          
          <div class="desbloqueo-visual">
            <div class="rifa-sequence">
              <div class="rifa-item active">
                <div class="rifa-icon">
                  <i class="fas fa-headphones"></i>
                </div>
                <span>AirPods Pro</span>
                <div class="rifa-status">Activa</div>
              </div>
              
              <div class="arrow">
                <i class="fas fa-arrow-down"></i>
              </div>
              
              <div class="rifa-item next">
                <div class="rifa-icon">
                  <i class="fas fa-spray-can"></i>
                </div>
                <span>Perfume Dior</span>
                <div class="rifa-status">Próxima</div>
              </div>
              
              <div class="arrow">
                <i class="fas fa-arrow-down"></i>
              </div>
              
              <div class="rifa-item locked">
                <div class="rifa-icon">
                  <i class="fas fa-mobile-alt"></i>
                </div>
                <span>iPhone 15</span>
                <div class="rifa-status">Bloqueada</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Métodos de Pago -->
    <section class="pagos-section">
      <div class="container">
        <h2 class="section-title">💳 Métodos de Pago</h2>
        
        <div class="pagos-grid">
          <div class="pago-card">
            <div class="pago-icon">
              <img src="https://logosenvector.com/logo/img/yape-37283.png" alt="Yape">
            </div>
            <h3>Yape</h3>
            <p>Pago instantáneo con código QR. Validación automática en tiempo real.</p>
            <div class="pago-ventajas">
              <span class="ventaja">
                <i class="fas fa-check"></i>
                Instantáneo
              </span>
              <span class="ventaja">
                <i class="fas fa-check"></i>
                Seguro
              </span>
            </div>
          </div>
          
          <div class="pago-card">
            <div class="pago-icon">
              <img src="https://images.seeklogo.com/logo-png/38/1/plin-logo-png_seeklogo-386806.png" alt="Plin">
            </div>
            <h3>Plin</h3>
            <p>Transferencia rápida entre bancos. Compatible con todos los bancos del Perú.</p>
            <div class="pago-ventajas">
              <span class="ventaja">
                <i class="fas fa-check"></i>
                Multi-banco
              </span>
              <span class="ventaja">
                <i class="fas fa-check"></i>
                24/7
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Preguntas Frecuentes -->
    <section class="faq-section">
      <div class="container">
        <h2 class="section-title">❓ Preguntas Frecuentes</h2>
        
        <div class="faq-grid">
          <div 
            v-for="faq in faqs" 
            :key="faq.id"
            class="faq-card"
            :class="{ 'faq-open': faq.open }"
            @click="toggleFAQ(faq)"
          >
            <div class="faq-header">
              <h3 class="faq-pregunta">{{ faq.pregunta }}</h3>
              <div class="faq-toggle">
                <i :class="faq.open ? 'fas fa-minus' : 'fas fa-plus'"></i>
              </div>
            </div>
            <div class="faq-respuesta" v-show="faq.open">
              <p>{{ faq.respuesta }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>¿Listo para Participar?</h2>
          <p>Únete a miles de personas que ya han ganado premios increíbles</p>
          <router-link to="/" class="btn btn-primary btn-lg">
            <i class="fas fa-rocket"></i>
            &nbsp;
            Ver Rifas Disponibles
          </router-link>
        </div>
      </div>
    </section>

    <AppFooter />
  </div>
</template>

<script>
import { ref } from 'vue'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

export default {
  name: 'ComoFunciona',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const pasos = ref([
      {
        id: 1,
        icon: 'fas fa-search',
        titulo: 'Elige tu Rifa',
        descripcion: 'Explora nuestras rifas disponibles y selecciona la que más te guste.',
        detalles: [
          'Revisa los premios disponibles',
          'Verifica la fecha de sorteo',
          'Consulta cuántos tickets faltan'
        ]
      },
      {
        id: 2,
        icon: 'fas fa-credit-card',
        titulo: 'Compra tu Ticket',
        descripcion: 'Paga solo S/2 por ticket usando Yape o Plin de forma segura.',
        detalles: [
          'Pago instantáneo con código QR',
          'Validación automática',
          'Confirmación inmediata'
        ]
      },
      {
        id: 3,
        icon: 'fas fa-clock',
        titulo: 'Espera el Sorteo',
        descripcion: 'Cuando se alcance el mínimo de participantes, realizamos el sorteo transparente.',
        detalles: [
          'Sorteo en vivo por redes sociales',
          'Sistema completamente transparente',
          'Notificación automática de resultados'
        ]
      },
      {
        id: 4,
        icon: 'fas fa-trophy',
        titulo: '¡Disfruta tu Premio!',
        descripcion: 'Si ganas, coordinaremos la entrega de tu premio de forma rápida y segura.',
        detalles: [
          'Entrega a domicilio gratuita',
          'Certificado de autenticidad',
          'Garantía del fabricante'
        ]
      }
    ])

    const caracteristicas = ref([
      {
        id: 1,
        icon: 'fas fa-shield-alt',
        titulo: 'Transparencia Total',
        descripcion: 'Todos los sorteos son transmitidos en vivo y utilizamos sistemas aleatorios verificables.'
      },
      {
        id: 2,
        icon: 'fas fa-clock',
        titulo: 'Resultados Rápidos',
        descripcion: 'Los sorteos se realizan automáticamente cuando se alcanza el número mínimo de participantes.'
      },
      {
        id: 3,
        icon: 'fas fa-truck',
        titulo: 'Entrega Garantizada',
        descripcion: 'Envío gratuito a nivel nacional. Tu premio llegará en perfecto estado a tu domicilio.'
      },
      {
        id: 4,
        icon: 'fas fa-dollar-sign',
        titulo: 'Precio Accesible',
        descripcion: 'Solo S/2 por ticket. Todos pueden participar y tener la oportunidad de ganar.'
      },
      {
        id: 5,
        icon: 'fas fa-mobile-alt',
        titulo: 'Pago Instantáneo',
        descripción: 'Compatible con Yape y Plin. Confirmación automática de tu participación.'
      },
      {
        id: 6,
        icon: 'fas fa-gift',
        titulo: 'Premios Originales',
        descripcion: 'Todos nuestros productos son 100% originales con garantía del fabricante.'
      }
    ])

    const faqs = ref([
      {
        id: 1,
        pregunta: '¿Cómo funciona el sistema de desbloqueo de rifas?',
        respuesta: 'Solo una rifa está activa a la vez. Cuando se completa (alcanza el mínimo de tickets), automáticamente se habilita la siguiente rifa en la secuencia.',
        open: false
      },
      {
        id: 2,
        pregunta: '¿Qué pasa si una rifa no alcanza el mínimo de participantes?',
        respuesta: 'Si una rifa no se completa en el tiempo establecido, se devuelve el 100% del dinero a todos los participantes de forma automática.',
        open: false
      },
      {
        id: 3,
        pregunta: '¿Cómo sé que el sorteo es justo?',
        respuesta: 'Todos los sorteos son transmitidos en vivo por nuestras redes sociales. Utilizamos sistemas aleatorios verificables y transparentes.',
        open: false
      },
      {
        id: 4,
        pregunta: '¿Cuánto tiempo tengo para reclamar mi premio?',
        respuesta: 'Los ganadores tienen 7 días para reclamar su premio. Nos comunicamos inmediatamente después del sorteo. Pasado este plazo, el premio se le entrega al segundo lugar. Si no se contacta con el segundo lugar, el premio se entrega al tercer lugar. Si no se contacta con el tercer lugar, el premio se declara desierto.',
        open: false
      },
      {
        id: 5,
        pregunta: '¿Puedo comprar múltiples tickets?',
        respuesta: 'Sí, puedes comprar tantos tickets como desees. Cada ticket adicional aumenta tus probabilidades de ganar.',
        open: false
      },
      {
        id: 6,
        pregunta: '¿Los envíos tienen costo adicional?',
        respuesta: 'No, todos los envíos son completamente gratuitos a nivel nacional. El premio llega directamente a tu domicilio.',
        open: false
      }
    ])

    const toggleFAQ = (faq) => {
      faq.open = !faq.open
    }

    return {
      pasos,
      caracteristicas,
      faqs,
      toggleFAQ
    }
  }
}
</script>

<style scoped>
.como-funciona-page {
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
  background: linear-gradient(135deg, var(--primary-indigo) 0%, var(--primary-blue) 50%, var(--primary-purple) 100%);
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

.info-icon {
  font-size: 2.5rem;
  color: #60a5fa;
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

/* Proceso Section */
.proceso-section {
  padding: 4rem 0;
  background: var(--white);
}

.section-title {
  text-align: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--gray-800);
  margin-bottom: 3rem;
}

.pasos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.paso-card {
  text-align: center;
  padding: 2rem;
  background: var(--gray-50);
  border-radius: var(--border-radius-lg);
  position: relative;
  transition: all 0.3s ease;
}

.paso-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.paso-destacado {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
}

.paso-numero {
  position: absolute;
  top: -1rem;
  left: 50%;
  transform: translateX(-50%);
  width: 2rem;
  height: 2rem;
  background: var(--primary-purple);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.125rem;
}

.paso-destacado .paso-numero {
  background: white;
  color: var(--primary-purple);
}

.paso-icon {
  font-size: 3rem;
  color: var(--primary-purple);
  margin: 1rem 0;
}

.paso-destacado .paso-icon {
  color: white;
}

.paso-titulo {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.paso-descripcion {
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.paso-detalles ul {
  list-style: none;
  padding: 0;
  text-align: left;
}

.paso-detalles li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.paso-detalles i {
  color: var(--success-green);
  font-size: 0.75rem;
}

.paso-destacado .paso-detalles i {
  color: white;
}

/* Características */
.caracteristicas-section {
  padding: 4rem 0;
  background: var(--gray-50);
}

.caracteristicas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.caracteristica-card {
  background: var(--white);
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  text-align: center;
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
}

.caracteristica-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.caracteristica-icon {
  width: 4rem;
  height: 4rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
  color: white;
  font-size: 1.5rem;
}

.caracteristica-titulo {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--gray-800);
}

.caracteristica-descripcion {
  color: var(--gray-600);
  line-height: 1.6;
}

/* Sistema de Desbloqueo */
.desbloqueo-section {
  padding: 4rem 0;
  background: var(--white);
}

.desbloqueo-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
}

.desbloqueo-text h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 1rem;
}

.desbloqueo-text p {
  color: var(--gray-600);
  line-height: 1.6;
  margin-bottom: 2rem;
}

.desbloqueo-pasos {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.desbloqueo-paso {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.paso-icon-small {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.paso-texto {
  font-size: 0.9rem;
  line-height: 1.5;
}

.rifa-sequence {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.rifa-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1.5rem;
  border-radius: var(--border-radius-lg);
  min-width: 150px;
  transition: all 0.3s ease;
}

.rifa-item.active {
  background: linear-gradient(135deg, var(--success-green), #10b981);
  color: white;
  transform: scale(1.05);
}

.rifa-item.next {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: white;
}

.rifa-item.locked {
  background: var(--gray-300);
  color: var(--gray-600);
  opacity: 0.7;
}

.rifa-icon {
  font-size: 2rem;
}

.rifa-status {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  background: rgba(255, 255, 255, 0.2);
}

.arrow {
  color: var(--gray-400);
  font-size: 1.5rem;
}

/* Métodos de Pago */
.pagos-section {
  padding: 4rem 0;
  background: var(--gray-50);
}

.pagos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.pago-card {
  background: var(--white);
  padding: 2rem;
  border-radius: var(--border-radius-lg);
  text-align: center;
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
}

.pago-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.pago-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pago-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.pago-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: var(--gray-800);
}

.pago-card p {
  color: var(--gray-600);
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.pago-ventajas {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.ventaja {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
  color: var(--success-green);
  font-weight: 500;
}

/* FAQ Section */
.faq-section {
  padding: 4rem 0;
  background: var(--white);
}

.faq-grid {
  display: grid;
  gap: 1rem;
  max-width: 800px;
  margin: 0 auto;
}

.faq-card {
  background: var(--gray-50);
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
}

.faq-card:hover {
  background: var(--gray-100);
}

.faq-open {
  background: var(--white);
  box-shadow: var(--shadow-md);
}

.faq-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
}

.faq-pregunta {
  font-size: 1rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.faq-toggle {
  width: 2rem;
  height: 2rem;
  background: var(--primary-purple);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
}

.faq-respuesta {
  padding: 0 1.5rem 1.5rem;
  color: var(--gray-600);
  line-height: 1.6;
}

/* CTA Section */
.cta-section {
  padding: 4rem 0;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  text-align: center;
}

.cta-content {
  color: white;
}

.cta-content h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.cta-content p {
  font-size: 1.125rem;
  margin-bottom: 2rem;
  opacity: 0.9;
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1.125rem;
}

/* Responsive */
@media (max-width: 768px) {
  .pasos-grid {
    grid-template-columns: 1fr;
  }

  .caracteristicas-grid {
    grid-template-columns: 1fr;
  }

  .desbloqueo-content {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .pagos-grid {
    grid-template-columns: 1fr;
  }
  
  .pago-ventajas {
    flex-direction: column;
    gap: 0.5rem;
  }

  .section-title {
    font-size: 2rem;
  }

  .cta-content h2 {
    font-size: 2rem;
  }
}
</style>
