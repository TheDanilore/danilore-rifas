<template>
  <header class="admin-header">
    <div class="container">
      <div class="header-content">
        <!-- Logo Admin -->
        <div class="admin-logo">
          <router-link to="/admin/dashboard" class="logo-link">
            <i class="fas fa-crown logo-icon"></i>
            <div class="logo-text">
              <span class="logo-title">Danilore</span>
              <span class="logo-subtitle">Admin</span>
            </div>
          </router-link>
        </div>

        <!-- Navigation -->
        <nav class="admin-nav">
          <router-link
            v-for="item in navItems"
            :key="item.name"
            :to="item.route"
            class="nav-link"
            :class="{ active: $route.path.includes(item.path) }"
          >
            <i :class="item.icon"></i>
            <span>{{ item.name }}</span>
          </router-link>
        </nav>

        <!-- Admin Actions -->
        <div class="admin-actions">
          <!-- Notifications -->
          <div class="notification-dropdown" @click="toggleNotifications">
            <button class="notification-btn">
              <i class="fas fa-bell"></i>
              <span v-if="unreadCount > 0" class="notification-count">{{ unreadCount }}</span>
            </button>

            <div v-if="showNotifications" class="notification-panel">
              <div class="panel-header">
                <h3>Notificaciones</h3>
                <button @click="markAllAsRead" class="mark-read-btn">
                  Marcar todas como leídas
                </button>
              </div>
              <div class="notifications-list">
                <div
                  v-for="notification in notifications"
                  :key="notification.id"
                  class="notification-item"
                  :class="{ unread: !notification.read }"
                >
                  <div class="notification-icon" :class="notification.type">
                    <i :class="notification.icon"></i>
                  </div>
                  <div class="notification-content">
                    <p class="notification-title">{{ notification.title }}</p>
                    <p class="notification-text">{{ notification.message }}</p>
                    <span class="notification-time">{{ notification.time }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Admin Profile -->
          <div class="admin-profile" @click="toggleProfile">
            <div class="profile-info">
              <span class="profile-name">{{ user?.nombre }}</span>
              <span class="profile-role">Administrador</span>
            </div>
            <div class="profile-avatar">
              <i class="fas fa-user-shield"></i>
            </div>

            <div v-if="showProfile" class="profile-dropdown">
              <router-link to="/admin/profile" class="dropdown-item">
                <i class="fas fa-user-cog"></i>
                Mi Perfil
              </router-link>
              <router-link to="/admin/settings" class="dropdown-item">
                <i class="fas fa-cog"></i>
                Configuración
              </router-link>
              <div class="dropdown-divider"></div>
              <router-link to="/" class="dropdown-item">
                <i class="fas fa-eye"></i>
                Ver Sitio Cliente
              </router-link>
              <button @click="handleLogout" class="dropdown-item logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <div v-if="showMobileMenu" class="mobile-nav">
      <router-link
        v-for="item in navItems"
        :key="item.name"
        :to="item.route"
        class="mobile-nav-link"
        @click="closeMobileMenu"
      >
        <i :class="item.icon"></i>
        <span>{{ item.name }}</span>
      </router-link>
    </div>
  </header>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'AdminHeader',
  setup() {
    const router = useRouter()
    const { user, logout } = useAuthStore()
    
    const showNotifications = ref(false)
    const showProfile = ref(false)
    const showMobileMenu = ref(false)

    const navItems = ref([
      {
        name: 'Dashboard',
        route: '/admin/dashboard',
        path: 'dashboard',
        icon: 'fas fa-tachometer-alt'
      },
      {
        name: 'Rifas',
        route: '/admin/rifas',
        path: 'rifas',
        icon: 'fas fa-ticket-alt'
      },
      {
        name: 'Usuarios',
        route: '/admin/usuarios',
        path: 'usuarios',
        icon: 'fas fa-users'
      },
      {
        name: 'Ventas',
        route: '/admin/ventas',
        path: 'ventas',
        icon: 'fas fa-chart-bar'
      }
    ])

    const notifications = ref([
      {
        id: 1,
        title: 'Nueva venta',
        message: 'Se vendieron 5 tickets de iPhone 15',
        time: 'Hace 5 min',
        type: 'success',
        icon: 'fas fa-shopping-cart',
        read: false
      },
      {
        id: 2,
        title: 'Rifa próxima a finalizar',
        message: 'MacBook Pro M3 termina en 2 horas',
        time: 'Hace 30 min',
        type: 'warning',
        icon: 'fas fa-clock',
        read: false
      },
      {
        id: 3,
        title: 'Nuevo usuario',
        message: 'Carlos Mendoza se registró',
        time: 'Hace 1 hora',
        type: 'info',
        icon: 'fas fa-user-plus',
        read: true
      }
    ])

    const unreadCount = computed(() => {
      return notifications.value.filter(n => !n.read).length
    })

    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value
      showProfile.value = false
    }

    const toggleProfile = () => {
      showProfile.value = !showProfile.value
      showNotifications.value = false
    }

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value
    }

    const closeMobileMenu = () => {
      showMobileMenu.value = false
    }

    const markAllAsRead = () => {
      notifications.value.forEach(n => n.read = true)
    }

    const handleLogout = () => {
      logout()
      router.push('/admin')
    }

    const handleClickOutside = (event) => {
      if (!event.target.closest('.notification-dropdown')) {
        showNotifications.value = false
      }
      if (!event.target.closest('.admin-profile')) {
        showProfile.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      user,
      navItems,
      notifications,
      unreadCount,
      showNotifications,
      showProfile,
      showMobileMenu,
      toggleNotifications,
      toggleProfile,
      toggleMobileMenu,
      closeMobileMenu,
      markAllAsRead,
      handleLogout
    }
  }
}
</script>

<style scoped>
.admin-header {
  background: white;
  border-bottom: 1px solid var(--gray-200);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 0;
}

.admin-logo .logo-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  color: var(--gray-800);
}

.logo-icon {
  font-size: 1.5rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--accent-yellow));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.logo-title {
  font-size: 1.125rem;
  font-weight: 600;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-subtitle {
  font-size: 0.7rem;
  color: var(--gray-500);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.admin-nav {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.6rem 0.875rem;
  border-radius: 6px;
  text-decoration: none;
  color: var(--gray-600);
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.nav-link:hover,
.nav-link.active {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  text-decoration: none;
}

.admin-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.notification-dropdown {
  position: relative;
}

.notification-btn {
  position: relative;
  padding: 0.75rem;
  background: var(--gray-100);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  color: var(--gray-600);
  transition: all 0.3s ease;
}

.notification-btn:hover {
  background: var(--gray-200);
  color: var(--gray-800);
}

.notification-count {
  position: absolute;
  top: 0;
  right: 0;
  background: var(--danger-red);
  color: white;
  border-radius: 50%;
  width: 1.25rem;
  height: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  font-weight: 700;
}

.notification-panel {
  position: absolute;
  top: 100%;
  right: 0;
  width: 320px;
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  z-index: 1000;
  margin-top: 0.5rem;
}

.panel-header {
  padding: 1rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.panel-header h3 {
  font-size: 1rem;
  font-weight: 600;
}

.mark-read-btn {
  background: none;
  border: none;
  color: var(--primary-purple);
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 600;
}

.notifications-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-bottom: 1px solid var(--gray-100);
  transition: background 0.3s ease;
}

.notification-item:hover {
  background: var(--gray-50);
}

.notification-item.unread {
  background: rgba(79, 70, 229, 0.05);
}

.notification-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.875rem;
}

.notification-icon.success {
  background: var(--success-green);
}

.notification-icon.warning {
  background: var(--warning-yellow);
}

.notification-icon.info {
  background: var(--primary-blue);
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 0.875rem;
}

.notification-text {
  color: var(--gray-600);
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.notification-time {
  color: var(--gray-500);
  font-size: 0.625rem;
}

.admin-profile {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: var(--gray-50);
  border-radius: var(--border-radius-full);
  cursor: pointer;
  transition: all 0.3s ease;
}

.admin-profile:hover {
  background: var(--gray-100);
}

.profile-info {
  display: flex;
  flex-direction: column;
  text-align: right;
}

.profile-name {
  font-weight: 600;
  font-size: 0.875rem;
}

.profile-role {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.profile-avatar {
  width: 2.5rem;
  height: 2.5rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.profile-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  width: 200px;
  background: white;
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  z-index: 1000;
  margin-top: 0.5rem;
  padding: 0.5rem 0;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: var(--gray-700);
  text-decoration: none;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 0.875rem;
  transition: background 0.3s ease;
}

.dropdown-item:hover {
  background: var(--gray-50);
  color: var(--gray-800);
}

.dropdown-item.logout {
  color: var(--danger-red);
}

.dropdown-item.logout:hover {
  background: rgba(239, 68, 68, 0.1);
}

.dropdown-divider {
  height: 1px;
  background: var(--gray-200);
  margin: 0.5rem 0;
}

.mobile-menu-btn {
  display: none;
  padding: 0.75rem;
  background: none;
  border: none;
  color: var(--gray-600);
  cursor: pointer;
  font-size: 1.25rem;
}

.mobile-nav {
  display: none;
  background: white;
  border-top: 1px solid var(--gray-200);
  padding: 1rem 0;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: var(--gray-600);
  text-decoration: none;
  transition: background 0.3s ease;
}

.mobile-nav-link:hover {
  background: var(--gray-50);
  color: var(--gray-800);
}

@media (max-width: 1024px) {
  .admin-nav {
    gap: 1rem;
  }

  .nav-link span {
    display: none;
  }
}

@media (max-width: 768px) {
  .admin-nav {
    display: none;
  }

  .mobile-menu-btn {
    display: block;
  }

  .mobile-nav {
    display: block;
  }

  .profile-info {
    display: none;
  }

  .notification-panel {
    width: 280px;
  }
}
</style>
