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

        <div v-else class="user-menu">
          <button class="user-button" @click="toggleDropdown">
            <div class="user-avatar">
              <i class="fas fa-user"></i>
            </div>
            <span>{{ user?.nombre || 'Usuario' }}</span>
            <i class="fas fa-chevron-down"></i>
          </button>
          <div class="dropdown-menu" :class="{ show: showDropdown }">
            <router-link to="/dashboard" @click="closeDropdown">
              <i class="fas fa-user"></i> Mi Perfil
            </router-link>
            <router-link to="/dashboard" @click="closeDropdown">
              <i class="fas fa-ticket-alt"></i> Mis Rifas
            </router-link>
            <router-link to="/dashboard" @click="closeDropdown">
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
    <div class="mobile-menu" :class="{ show: showMobileMenu }">
      <div class="mobile-menu-content">
        <router-link to="/" @click="closeMobileMenu">Inicio</router-link>
        <router-link to="/ganadores" @click="closeMobileMenu">Ganadores</router-link>
        <router-link to="/como-funciona" @click="closeMobileMenu">Cómo Funciona</router-link>
        <router-link to="/terminos-condiciones" @click="closeMobileMenu">Términos</router-link>

        <div v-if="!isAuthenticated" class="mobile-auth">
          <router-link to="/login" @click="closeMobileMenu">Iniciar Sesión</router-link>
          <router-link to="/register" @click="closeMobileMenu">Registrarse</router-link>
        </div>

        <div v-else class="mobile-user">
          <div class="mobile-user-info">
            <i class="fas fa-user"></i>
            <span>{{ user?.nombre || 'Usuario' }}</span>
          </div>
          <router-link to="/dashboard" @click="closeMobileMenu">Mi Perfil</router-link>
          <router-link to="/dashboard" @click="closeMobileMenu">Mis Rifas</router-link>
          <router-link to="/dashboard" @click="closeMobileMenu">Historial</router-link>
          <a href="#" @click="handleLogout">Cerrar Sesión</a>
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
    }

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value
    }

    const handleLogout = async () => {
      try {
        await logout()
        showDropdown.value = false
        showMobileMenu.value = false
        
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
      }
    }

    onMounted(() => {
      window.addEventListener('scroll', handleScroll)
      // Cerrar dropdown al hacer click fuera
      document.addEventListener('click', (e) => {
        if (!e.target.closest('.user-menu')) {
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
  color: #6366f1; /* Fallback color */
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
  gap: 0.5rem;
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
  gap: 0.5rem;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: var(--border-radius);
  transition: background 0.3s ease;
}

.user-button:hover {
  background: var(--gray-100);
}

.user-avatar {
  background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
  padding: 0.25rem;
  border-radius: var(--border-radius-full);
  color: var(--gray-600);
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  box-shadow: var(--shadow-lg);
  min-width: 200px;
  padding: 0.5rem 0;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s;
  z-index: 1000;
}

.dropdown-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-menu a {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  text-decoration: none;
  color: var(--gray-700);
  transition: background 0.3s ease;
}

.dropdown-menu a:hover {
  background: var(--gray-50);
}

.dropdown-menu hr {
  margin: 0.5rem 0;
  border: none;
  border-top: 1px solid var(--gray-200);
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

.mobile-menu {
  display: none;
  position: fixed;
  top: 4rem;
  right: 0;
  width: 300px;
  height: calc(100vh - 4rem);
  background: var(--white);
  box-shadow: var(--shadow-xl);
  z-index: 999;
  transform: translateX(100%);
  transition: transform 0.3s ease;
}

.mobile-menu.show {
  transform: translateX(0);
}

.mobile-menu-content {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mobile-menu-content a {
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--gray-200);
}

.mobile-menu-content a:hover {
  color: var(--primary-purple);
}

.mobile-auth {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid var(--gray-200);
}

.mobile-auth a {
  padding: 0.75rem 1rem;
  text-align: center;
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  font-weight: 600;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  border: 2px solid var(--primary-color);
}

.mobile-auth a:first-child {
  background: transparent;
  color: var(--primary-color);
}

.mobile-auth a:first-child:hover {
  background: var(--primary-color);
  color: var(--white);
}

.mobile-auth a:last-child {
  background: var(--primary-color);
  color: var(--white);
}

.mobile-auth a:last-child:hover {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

.mobile-user {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid var(--gray-200);
}

.mobile-user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 0;
  font-weight: 600;
  color: var(--gray-800);
  border-bottom: 1px solid var(--gray-200);
  margin-bottom: 1rem;
}

.mobile-user-info i {
  color: var(--primary-purple);
  font-size: 1.125rem;
}

.mobile-user a {
  padding: 0.75rem 0;
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
    width: 100vw;
    top: 4rem;
  }

  .mobile-menu-content {
    padding: 1rem 1.5rem;
  }
}
</style>
