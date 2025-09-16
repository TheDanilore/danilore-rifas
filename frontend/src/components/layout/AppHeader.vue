<template>
  <header class="header">
    <div class="container">
      <div class="header-content">
        <router-link to="/" class="logo">
          <img src="@/assets/images/logo-sin-fondo.png" alt="Logo" height="75px">
        </router-link>

        <nav class="nav-desktop">
          <router-link to="/">Inicio</router-link>
          <router-link to="/ganadores">Ganadores</router-link>
          <router-link to="/como-funciona">Cómo Funciona</router-link>
          <router-link to="/terminos-condiciones">Términos</router-link>
        </nav>

        <div class="header-actions">
          <div v-if="!isAuthenticated" class="auth-buttons">
            <router-link to="/login" class="btn btn-outline">Iniciar Sesión</router-link>
            <router-link to="/register" class="btn btn-primary">Registrarse</router-link>
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
    <div v-if="showMobileMenu" class="mobile-overlay" @click="closeMobileMenu"></div>
    <div class="mobile-menu" :class="{ show: showMobileMenu }">
      <div class="mobile-menu-header">
        <h2>Menú</h2>
        <button class="mobile-close-btn" @click="closeMobileMenu">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="mobile-menu-content">
        <router-link to="/" @click="closeMobileMenu">Inicio</router-link>
        <router-link to="/ganadores" @click="closeMobileMenu">Ganadores</router-link>
        <router-link to="/como-funciona" @click="closeMobileMenu">Cómo Funciona</router-link>
        <router-link to="/terminos-condiciones" @click="closeMobileMenu">Términos</router-link>
        
        <div v-if="!isAuthenticated" class="mobile-auth">
          <router-link to="/login" class="btn btn-outline" @click="closeMobileMenu">
            Iniciar Sesión
          </router-link>
          <router-link to="/register" class="btn btn-primary" @click="closeMobileMenu">
            Registrarse
          </router-link>
        </div>
        
        <div v-else class="mobile-user">
          <div class="mobile-user-info">
            <i class="fas fa-user"></i>
            <span>{{ user?.nombre || 'Usuario' }}</span>
          </div>
          <a href="#profile" @click="closeMobileMenu">Mi Perfil</a>
          <a href="#my-rifas" @click="closeMobileMenu">Mis Rifas</a>
          <a href="#history" @click="closeMobileMenu">Historial</a>
          <a href="#" @click="handleLogout">Cerrar Sesión</a>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
import { ref } from 'vue'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'AppHeader',
  setup() {
    const { isAuthenticated, user, logout } = useAuthStore()
    const showDropdown = ref(false)
    const showMobileMenu = ref(false)

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
      // Prevenir scroll del body cuando el menú está abierto
      if (showMobileMenu.value) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = 'auto'
      }
    }

    const handleLogout = () => {
      logout()
      showDropdown.value = false
      showMobileMenu.value = false
    }

    return {
      isAuthenticated,
      user,
      showDropdown,
      showMobileMenu,
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
}

.logo-icon {
  background: linear-gradient(135deg, var(--primary-purple), var(--accent-purple));
  padding: 0.5rem;
  border-radius: var(--border-radius);
  color: var(--white);
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(135deg, var(--primary-purple), var(--accent-blue));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
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
  background: transparent;
  border: none;
  font-size: 1.5rem;
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
  z-index: 998;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Forzar que esté oculto en desktop */
.mobile-menu {
  display: none;
  position: fixed;
  top: 4rem;
  right: 0;
  width: 320px;
  height: calc(100vh - 4rem);
  background: var(--white);
  box-shadow: var(--shadow-xl);
  z-index: 999;
  transform: translateX(100%);
  transition: transform 0.3s ease;
  border-left: 1px solid var(--gray-200);
}

.mobile-menu.show {
  transform: translateX(0);
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--gray-200);
  background: var(--gray-50);
}

.mobile-menu-header h2 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
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
  padding: 1.5rem 2rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  height: calc(100% - 4rem);
  overflow-y: auto;
}

.mobile-menu-content a {
  text-decoration: none;
  color: var(--gray-700);
  font-weight: 500;
  padding: 1rem 0;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  align-items: center;
  transition: color 0.3s ease;
}

.mobile-menu-content a:hover {
  color: var(--primary-purple);
}

.mobile-menu-content a:last-child {
  border-bottom: none;
}

.mobile-auth {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid var(--gray-200);
}

.mobile-auth .btn {
  padding: 0.75rem 1rem;
  text-align: center;
  border-radius: var(--border-radius);
  font-size: 0.875rem;
}

.mobile-user {
  margin-top: 2rem;
  padding-top: 2rem;
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
    right: 0;
    top: 3.5rem;
    height: calc(100vh - 3.5rem);
  }

  .mobile-menu-content {
    padding: 1rem 1.5rem;
    height: calc(100% - 3.5rem);
  }

  .mobile-menu-header {
    padding: 1rem 1.5rem;
  }
}
</style>
