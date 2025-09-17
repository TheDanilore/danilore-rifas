<template>
  <header class="header app-header" :class="{ scrolled: isScrolled }">
    <div class="container">
      <div class="header-content">
        <router-link to="/" class="logo logo-enhanced">
          <div class="logo-icon">
            <i class="fas fa-trophy"></i>
          </div>
          <div class="logo-content">
            <h1 class="logo-text">Danilore</h1>
            <p class="logo-subtitle">Rifas</p>
          </div>
        </router-link>

        <nav class="nav-desktop main-nav">
          <ul class="nav-links">
            <li><router-link to="/" class="nav-link"><i class="fas fa-home"></i>Inicio</router-link></li>
            <li><router-link to="/ganadores" class="nav-link"><i class="fas fa-crown"></i>Ganadores</router-link></li>
            <li><router-link to="/como-funciona" class="nav-link"><i class="fas fa-question-circle"></i>Cómo
                Funciona</router-link></li>
            <li><router-link to="/terminos-condiciones" class="nav-link"><i
                  class="fas fa-file-contract"></i>Términos</router-link></li>
          </ul>
        </nav>

        <div class="header-actions">
          <div v-if="!isAuthenticated" class="auth-buttons">
            <router-link to="/login" class="login-btn">
              <i class="fas fa-sign-in-alt"></i>
              Iniciar Sesión
            </router-link>
            <router-link to="/register" class="register-btn">
              <i class="fas fa-user-plus"></i>
              Registrarse
            </router-link>
          </div>

          <div v-else class="user-menu" :class="{ show: showDropdown }">
            <button class="user-button" @click="toggleDropdown">
              <div class="user-avatar">
                <i class="fas fa-user"></i>
              </div>
              <span>{{ user?.nombres || user?.nombre || 'Usuario' }}</span>
              <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" :class="{ show: showDropdown }">
              <router-link to="/dashboard" @click="closeDropdown">
                <i class="fas fa-trophy"></i> Dashboard
              </router-link>
              <router-link to="/perfil" @click="closeDropdown">
                <i class="fas fa-user"></i> Mi Perfil
              </router-link>
              <router-link to="/mis-rifas" @click="closeDropdown">
                <i class="fas fa-ticket-alt"></i> Mis Rifas
              </router-link>
              <router-link to="/historial" @click="closeDropdown">
                <i class="fas fa-history"></i> Historial
              </router-link>
              <hr>
              <a href="#" @click="handleLogout">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </a>
            </div>
          </div>

          <button class="mobile-menu-btn" @click="toggleMobileMenu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="showMobileMenu" class="mobile-overlay" @click="closeMobileMenu"></div>
    <div class="mobile-menu" :class="{ show: showMobileMenu }">
      <div class="mobile-menu-header">
        <h3>Menú</h3>
        <button class="mobile-close-btn" @click="closeMobileMenu">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="mobile-menu-content">
        <router-link to="/" @click="closeMobileMenu">
          <i class="fas fa-home"></i>
          <span>Inicio</span>
        </router-link>
        <router-link to="/ganadores" @click="closeMobileMenu">
          <i class="fas fa-crown"></i>
          <span>Ganadores</span>
        </router-link>
        <router-link to="/como-funciona" @click="closeMobileMenu">
          <i class="fas fa-question-circle"></i>
          <span>Cómo Funciona</span>
        </router-link>
        <router-link to="/terminos-condiciones" @click="closeMobileMenu">
          <i class="fas fa-file-contract"></i>
          <span>Términos</span>
        </router-link>

        <div v-if="!isAuthenticated" class="mobile-auth">
          <router-link to="/login" @click="closeMobileMenu">
            <i class="fas fa-sign-in-alt"></i>
            <span>Iniciar Sesión</span>
          </router-link>
          <router-link to="/register" @click="closeMobileMenu">
            <i class="fas fa-user-plus"></i>
            <span>Registrarse</span>
          </router-link>
        </div>

        <div v-else class="mobile-user">
          <div class="mobile-user-info">
            <i class="fas fa-user"></i>
            <span>{{ user?.nombres || user?.nombre || 'Usuario' }}</span>
          </div>
          <router-link to="/dashboard" @click="closeDropdown">
            <i class="fas fa-trophy"></i> 
            <span>Dashboard</span>
          </router-link>
          <router-link to="/perfil" @click="closeMobileMenu">
            <i class="fas fa-user"></i>
            <span>Mi Perfil</span>
          </router-link>
          <router-link to="/mis-rifas" @click="closeMobileMenu">
            <i class="fas fa-ticket-alt"></i>
            <span>Mis Rifas</span>
          </router-link>
          <router-link to="/historial" @click="closeMobileMenu">
            <i class="fas fa-history"></i>
            <span>Historial</span>
          </router-link>
          <a href="#" @click="handleLogout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
          </a>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'AppHeader',
  setup() {
    const { isAuthenticated, user, logout } = useAuthStore()
    const showDropdown = ref(false)
    const showMobileMenu = ref(false)
    const isScrolled = ref(false)

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 50
    }

    const toggleDropdown = () => {
      showDropdown.value = !showDropdown.value
    }

    const closeDropdown = () => {
      showDropdown.value = false
    }

    const closeMobileMenu = () => {
      showMobileMenu.value = false
      document.body.style.overflow = 'auto'
    }

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value

      // Bloquear/desbloquear scroll del body
      if (showMobileMenu.value) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = 'auto'
      }
    }

    const handleLogout = async (event) => {
      event.preventDefault()

      try {
        // Cerrar cualquier menú abierto inmediatamente
        showDropdown.value = false
        showMobileMenu.value = false

        // Restaurar scroll del body
        document.body.style.overflow = 'auto'

        await logout()

        // Asegurar redirección a inicio
        setTimeout(() => {
          if (window.location.pathname !== '/') {
            window.location.href = '/'
          }
        }, 100)
      } catch (error) {
        console.error('Error en logout:', error)
        // Incluso si hay error, limpiar UI
        showDropdown.value = false
        showMobileMenu.value = false
        document.body.style.overflow = 'auto'
      }
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)

      // Cerrar dropdown al hacer click fuera
      document.addEventListener('click', (e) => {
        const userMenu = e.target.closest('.user-menu')
        if (!userMenu && showDropdown.value) {
          showDropdown.value = false
        }
      })
    })

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll)
    })

    return {
      isAuthenticated,
      user,
      showDropdown,
      showMobileMenu,
      isScrolled,
      toggleDropdown,
      closeDropdown,
      toggleMobileMenu,
      closeMobileMenu,
      handleLogout
    }
  }
}
</script>

<style scoped>
/* Header Styles */
.header {
  position: sticky;
  top: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--gray-200);
  box-shadow: var(--shadow-sm);
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 4rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  transition: opacity 0.3s ease;
}

.logo:hover {
  opacity: 0.8;
}

.logo-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.logo-icon {
  background: linear-gradient(135deg, var(--primary-purple, #6366f1), var(--accent-purple, #8b5cf6));
  padding: 0.5rem;
  border-radius: var(--border-radius, 8px);
  color: var(--white, #ffffff);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #6366f1;
  /* Fallback color */
  background: linear-gradient(135deg, var(--primary-purple, #6366f1), var(--accent-blue, #3b82f6));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  line-height: 1.2;
}

/* Fallback para navegadores que no soportan background-clip */
@supports not (-webkit-background-clip: text) {
  .logo-text {
    color: #6366f1 !important;
    background: none !important;
  }
}

.logo-subtitle {
  font-size: 0.75rem;
  color: #6b7280;
  margin: 0;
  line-height: 1;
  font-weight: 500;
}

/* DESKTOP FIRST - SOLUCIÓN DEFINITIVA */

/* Estilos por defecto para DESKTOP */
.nav-desktop {
  display: flex;
  gap: 2rem;
}

.auth-buttons {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.login-btn,
.register-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  text-decoration: none;
  border-radius: var(--border-radius, 8px);
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.login-btn {
  color: var(--primary-purple, #6366f1);
  border-color: var(--primary-purple, #6366f1);
  background: transparent;
}

.login-btn:hover {
  background: var(--primary-purple, #6366f1);
  color: var(--white, #ffffff);
}

.register-btn {
  background: var(--primary-purple, #6366f1);
  color: var(--white, #ffffff);
  border-color: var(--primary-purple, #6366f1);
}

.register-btn:hover {
  background: var(--primary-dark, #4f46e5);
  border-color: var(--primary-dark, #4f46e5);
}

.user-menu {
  position: relative;
  display: block;
}

.mobile-menu-btn {
  display: none;
}

.mobile-menu {
  display: none;
}

/* MOBILE - Ocultar navegación y mostrar hamburger */
@media (max-width: 768px) {
  .nav-desktop {
    display: none !important;
  }

  .auth-buttons {
    display: none !important;
  }

  .user-menu {
    display: none !important;
  }

  .mobile-menu-btn {
    display: block !important;
  }

  .mobile-menu {
    display: block;
  }
}

.nav-desktop a {
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  transition: color 0.3s ease;
}

.nav-desktop a:hover,
.nav-desktop a.router-link-active {
  color: var(--primary-purple);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: var(--border-radius, 8px);
  transition: all 0.3s ease;
  color: var(--gray-700, #374151);
  font-weight: 500;
}

.user-button:hover {
  background: var(--gray-100, #f3f4f6);
}

.user-button i.fas.fa-chevron-down {
  font-size: 0.75rem;
  transition: transform 0.3s ease;
}

.user-menu.show .user-button i.fas.fa-chevron-down {
  transform: rotate(180deg);
}

.user-avatar {
  background: linear-gradient(135deg, var(--primary-purple, #6366f1), var(--accent-purple, #8b5cf6));
  padding: 0.5rem;
  border-radius: var(--border-radius-full, 50%);
  color: var(--white, #ffffff);
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--white, #ffffff);
  border: 1px solid var(--gray-200, #e5e7eb);
  border-radius: var(--border-radius, 8px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  min-width: 200px;
  padding: 0.5rem 0;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
  z-index: 1000;
  margin-top: 0.5rem;
}

.dropdown-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-menu a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  text-decoration: none;
  color: var(--gray-700, #374151);
  transition: all 0.3s ease;
  font-weight: 500;
}

.dropdown-menu a:hover {
  background: var(--gray-50, #f9fafb);
  color: var(--primary-purple, #6366f1);
}

.dropdown-menu hr {
  margin: 0.5rem 0;
  border: none;
  border-top: 1px solid var(--gray-200, #e5e7eb);
}

.mobile-menu-btn {
  display: none;
  background: transparent;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: var(--gray-700);
  padding: 0.5rem;
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
}

.mobile-menu-btn:hover {
  background: var(--gray-100);
  color: var(--primary-purple);
}

.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1000;
  opacity: 1;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }

  to {
    opacity: 1;
  }
}

.mobile-menu {
  display: none;
  position: fixed;
  top: 0;
  right: 0;
  width: 85%;
  max-width: 320px;
  height: 100vh;
  background: var(--white);
  box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 1001;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  overflow-y: auto;
}

.mobile-menu.show {
  transform: translateX(0);
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-200);
  background: var(--gray-50);
}

.mobile-menu-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
}

.mobile-close-btn {
  background: transparent;
  border: none;
  font-size: 1.25rem;
  color: var(--gray-600);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: var(--border-radius);
  transition: all 0.3s ease;
}

.mobile-close-btn:hover {
  background: var(--gray-200);
  color: var(--gray-800);
}

.mobile-menu-content {
  padding: 0;
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 4rem);
}

.mobile-menu-content>a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
  transition: all 0.3s ease;
}

.mobile-menu-content>a i {
  font-size: 1rem;
  width: 1.25rem;
  text-align: center;
}

.mobile-menu-content>a:hover,
.mobile-menu-content>a.router-link-active {
  background: var(--gray-50);
  color: var(--primary-purple);
}

.mobile-auth {
  display: flex;
  flex-direction: column;
  gap: 0;
  margin-top: 1rem;
  padding: 1rem 0;
  border-top: 2px solid var(--gray-100);
}

.mobile-auth a,
.mobile-user a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
  transition: all 0.3s ease;
}

.mobile-auth a i,
.mobile-user a i {
  font-size: 1rem;
  width: 1.25rem;
  text-align: center;
}

.mobile-auth a:hover,
.mobile-user a:hover {
  background: var(--gray-50);
  color: var(--primary-purple);
}

.mobile-auth a:last-child {
  border-bottom: none;
}

.mobile-user {
  margin-top: 1rem;
  padding: 1rem 0;
  border-top: 2px solid var(--gray-100);
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  border-bottom: 1px solid var(--gray-100);
  margin-bottom: 0;
  background: var(--gray-50);
}

.mobile-user-info i {
  color: var(--primary-purple);
  font-size: 1.125rem;
}

.mobile-user a {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--gray-100);
  transition: all 0.3s ease;
}

.mobile-user a:hover {
  background: var(--gray-50);
  color: var(--primary-purple);
}

.mobile-user a:last-child {
  color: var(--error-color);
  border-bottom: none;
}

.mobile-user a:last-child:hover {
  background: var(--error-light);
  color: var(--error-color);
}

/* MOBILE - Mostrar menú móvil */
@media (max-width: 768px) {
  .nav-desktop {
    display: none !important;
  }

  .auth-buttons {
    display: none !important;
  }

  .user-menu {
    display: none !important;
  }

  .mobile-menu-btn {
    display: block !important;
  }
}

.mobile-user a {
  padding: 0.75rem 0;
}

/* RESPONSIVE DESIGN - Mobile First Approach */
@media (max-width: 768px) {
  .mobile-menu {
    display: block;
  }

  .header-content {
    position: relative;
  }

  .logo-text {
    font-size: 1.125rem;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 0.75rem;
  }

  .header-content {
    height: 3.5rem;
  }

  .logo-text {
    font-size: 1rem;
  }

  .logo-icon {
    padding: 0.375rem;
  }

  .mobile-menu {
    width: 100%;
    max-width: none;
  }

  .mobile-menu-header {
    padding: 1rem;
  }

  .mobile-menu-content>a,
  .mobile-auth a,
  .mobile-user a {
    padding: 0.875rem 1rem;
  }

  .mobile-user-info {
    padding: 0.875rem 1rem;
  }
}
</style>
