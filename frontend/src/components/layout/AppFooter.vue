<template>
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-main">
          <div class="footer-logo">

            <img src="@/assets/images/logo-sin-fondo.png" alt="Logo" height="100px">
          </div>
          <p class="footer-description">
            La plataforma de rifas más confiable del Perú. Participa desde S/2 y gana premios increíbles con total
            transparencia.
          </p>
          <div class="social-links">
            <a href="#">
              <i class="fab fa-facebook"></i>
            </a>
            <a href="#">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#">
              <i class="fab fa-youtube"></i>
            </a>
          </div>
        </div>

        <div class="footer-links">
          <h3>Enlaces Rápidos</h3>
          <ul>
            <li><router-link to="/">Inicio</router-link></li>
            <li><router-link to="/como-funciona">Cómo Funciona</router-link></li>
            <li><router-link to="/ganadores">Ganadores</router-link></li>
            <li><router-link to="/terminos-condiciones">Términos y Condiciones</router-link></li>
          </ul>
        </div>

        <div class="footer-contact">
          <h3>Contacto</h3>
          <ul>
            <!-- <li>
              <i class="fas fa-envelope"></i>
              info@danilorerifas.com
            </li> -->
            <li>
              <i class="fab fa-whatsapp"></i>
              +51 936 081 881
            </li>
            <li>
              <i class="fas fa-map-marker-alt"></i>
              Chimbote, Perú
            </li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; {{ currentYear }} Danilore Rifas. Todos los derechos reservados. Rifas realizadas con total transparencia</p>
      </div>
    </div>
  </footer>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AppFooter',
  setup() {
    const currentYear = computed(() => new Date().getFullYear())

    return {
      currentYear
    }
  }
}
</script>

<style scoped>
.footer {
  background: var(--gray-900);
  color: var(--gray-300);
  margin-top: auto;
}

.footer-content {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 3rem;
  padding: 3rem 0;
}

.footer-main {
  max-width: 400px;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  font-weight: 700;
  font-size: 1.5rem;
}

.logo-icon {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
  padding: 0.75rem;
  border-radius: var(--border-radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon i {
  color: var(--white);
  font-size: 1.25rem;
}

.logo-text {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.footer-description {
  line-height: 1.6;
  margin-bottom: 2rem;
  color: var(--gray-400);
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-links a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  background: var(--gray-800);
  border-radius: var(--border-radius-full);
  color: var(--gray-400);
  text-decoration: none;
  transition: all 0.2s;
}

.social-links a:hover {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-pink));
  color: var(--white);
  transform: translateY(-2px);
}

.footer-links h3,
.footer-contact h3 {
  color: var(--white);
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.footer-links ul,
.footer-contact ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li,
.footer-contact li {
  margin-bottom: 0.75rem;
}

.footer-links a {
  color: var(--gray-400);
  text-decoration: none;
  transition: color 0.2s;
}

.footer-links a:hover {
  color: var(--primary-purple);
}

.footer-contact li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--gray-400);
}

.footer-contact i {
  color: var(--primary-purple);
  width: 1rem;
  text-align: center;
  font-size: 0.875rem;
}

.footer-bottom {
  border-top: 1px solid var(--gray-800);
  padding: 1.5rem 0;
  text-align: center;
}

.footer-bottom p {
  color: var(--gray-500);
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 2rem;
    text-align: center;
  }

  .footer-main {
    max-width: none;
  }

  .social-links {
    justify-content: center;
  }

  .footer-contact li {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .footer-content {
    padding: 2rem 0;
  }

  .footer-logo {
    justify-content: center;
  }

  .social-links {
    gap: 0.75rem;
  }

  .social-links a {
    width: 2rem;
    height: 2rem;
  }
}
</style>
