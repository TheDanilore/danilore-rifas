<template>
  <header class="admin-header">
    <div class="admin-container">
      <div class="header-content">
        <!-- Logo -->
        <div class="admin-logo">
          <router-link to="/admin/dashboard" class="logo-link">
            <i class="fas fa-crown logo-icon"></i>
            <div class="logo-text">
              <span class="logo-title">Danilore Rifas</span>
              <span class="logo-subtitle">Admin Panel</span>
            </div>
          </router-link>
        </div>

        <!-- Navigation -->
        <nav class="admin-nav">
          <router-link to="/admin/dashboard" class="nav-link" active-class="active">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
          </router-link>
          <router-link to="/admin/rifas" class="nav-link" active-class="active">
            <i class="fas fa-ticket-alt"></i>
            Rifas
          </router-link>
          <router-link to="/admin/usuarios" class="nav-link" active-class="active">
            <i class="fas fa-users"></i>
            Usuarios
          </router-link>
          <router-link to="/admin/ventas" class="nav-link" active-class="active">
            <i class="fas fa-chart-line"></i>
            Ventas
          </router-link>
        </nav>

        <!-- Actions -->
        <div class="admin-actions">
          <!-- Notifications -->
          <div class="notification-dropdown" v-if="false">
            <button 
              class="notification-btn" 
              @click="showNotifications = !showNotifications"
              title="Notificaciones"
            >
              <i class="fas fa-bell"></i>
              <span v-if="notificationCount > 0" class="notification-count">
                {{ notificationCount > 99 ? '99+' : notificationCount }}
              </span>
            </button>

            <div v-if="showNotifications" class="notification-panel">
              <div class="panel-header">
                <h3>Notificaciones</h3>
                <button class="mark-read-btn" @click="markAllAsRead">
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
                    <div class="notification-title">{{ notification.title }}</div>
                    <div class="notification-text">{{ notification.message }}</div>
                    <div class="notification-time">{{ formatTime(notification.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Admin Profile -->
          <div class="admin-profile" @click="showProfileDropdown = !showProfileDropdown">
            <div class="profile-info">
              <div class="profile-name">{{ user?.name || 'Admin' }}</div>
              <div class="profile-role">Administrador</div>
            </div>
            <div class="profile-avatar">
              <i class="fas fa-user-shield"></i>
            </div>

            <div v-if="showProfileDropdown" class="profile-dropdown">
              <router-link to="/admin/profile" class="dropdown-item">
                <i class="fas fa-user"></i>
                Mi Perfil
              </router-link>
              <router-link to="/admin/settings" class="dropdown-item">
                <i class="fas fa-cog"></i>
                Configuración
              </router-link>
              <div class="dropdown-divider"></div>
              <button @click="handleLogout" class="dropdown-item logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
              </button>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button class="mobile-menu-btn" @click="showMobileMenu = !showMobileMenu">
            <i class="fas fa-bars"></i>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <nav v-if="showMobileMenu" class="mobile-nav">
        <router-link to="/admin/dashboard" class="mobile-nav-link" @click="showMobileMenu = false">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </router-link>
        <router-link to="/admin/rifas" class="mobile-nav-link" @click="showMobileMenu = false">
          <i class="fas fa-ticket-alt"></i>
          Rifas
        </router-link>
        <router-link to="/admin/usuarios" class="mobile-nav-link" @click="showMobileMenu = false">
          <i class="fas fa-users"></i>
          Usuarios
        </router-link>
        <router-link to="/admin/ventas" class="mobile-nav-link" @click="showMobileMenu = false">
          <i class="fas fa-chart-line"></i>
          Ventas
        </router-link>
      </nav>
    </div>
  </header>
</template>

<script>
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'AdminHeader',
  setup() {
    const router = useRouter()
    
    const showNotifications = ref(false)
    const showProfileDropdown = ref(false)
    const showMobileMenu = ref(false)
    
    // Simulamos el usuario desde localStorage o datos mock
    const user = ref({
      name: 'Admin Usuario',
      email: 'admin@danilorerifas.com',
      role: 'admin'
    })
    
    const notifications = ref([])
    const notificationCount = computed(() => 
      notifications.value.filter(n => !n.read).length
    )

    const handleLogout = async () => {
      try {
        // Limpiar datos de autenticación del localStorage
        localStorage.removeItem('admin_token')
        localStorage.removeItem('admin_user')
        
        // Redirigir al login
        router.push('/admin/login')
      } catch (error) {
        console.error('Error al cerrar sesión:', error)
      }
    }

    const markAllAsRead = () => {
      notifications.value.forEach(n => n.read = true)
    }

    const formatTime = (dateString) => {
      const date = new Date(dateString)
      const now = new Date()
      const diffInHours = (now - date) / (1000 * 60 * 60)
      
      if (diffInHours < 1) {
        return 'Hace pocos minutos'
      } else if (diffInHours < 24) {
        return `Hace ${Math.floor(diffInHours)} horas`
      } else {
        return date.toLocaleDateString()
      }
    }

    onMounted(() => {
      // Cargar usuario desde localStorage si existe
      const savedUser = localStorage.getItem('admin_user')
      if (savedUser) {
        try {
          user.value = JSON.parse(savedUser)
        } catch (error) {
          console.error('Error al cargar usuario:', error)
        }
      }
      
      // Cargar notificaciones desde la API (simulado)
      // En el futuro aquí iría la llamada real a la API
    })

    return {
      user,
      showNotifications,
      showProfileDropdown,
      showMobileMenu,
      notifications,
      notificationCount,
      handleLogout,
      markAllAsRead,
      formatTime
    }
  }
}
</script>

<style scoped>
/* Usar clases del admin.css global */
</style>