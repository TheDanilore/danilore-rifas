<template>
  <aside class="admin-sidebar" :class="{ collapsed: isCollapsed }">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <router-link to="/admin/dashboard" class="logo-link">
          <i class="fas fa-crown logo-icon"></i>
          <div class="logo-text" v-if="!isCollapsed">
            <span class="logo-title">Danilore</span>
            <span class="logo-subtitle">Admin</span>
          </div>
        </router-link>
      </div>
      
      <button 
        class="collapse-btn"
        @click="toggleSidebar"
        title="Colapsar sidebar"
      >
        <i class="fas fa-angle-left" :class="{ 'fa-angle-right': isCollapsed }"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section">
        <div class="nav-title" v-if="!isCollapsed">Principal</div>
        
        <router-link 
          to="/admin/dashboard" 
          class="nav-item"
          active-class="active"
          exact
        >
          <i class="nav-icon fas fa-tachometer-alt"></i>
          <span class="nav-text" v-if="!isCollapsed">Dashboard</span>
        </router-link>

        <router-link 
          to="/admin/rifas" 
          class="nav-item"
          active-class="active"
        >
          <i class="nav-icon fas fa-ticket-alt"></i>
          <span class="nav-text" v-if="!isCollapsed">Rifas</span>
          <span class="nav-badge" v-if="!isCollapsed && activeRifasCount > 0">
            {{ activeRifasCount }}
          </span>
        </router-link>

        <router-link 
          to="/admin/usuarios" 
          class="nav-item"
          active-class="active"
        >
          <i class="nav-icon fas fa-users"></i>
          <span class="nav-text" v-if="!isCollapsed">Usuarios</span>
        </router-link>

        <router-link 
          to="/admin/ventas" 
          class="nav-item"
          active-class="active"
        >
          <i class="nav-icon fas fa-chart-line"></i>
          <span class="nav-text" v-if="!isCollapsed">Ventas</span>
        </router-link>
      </div>

      <div class="nav-section" v-if="!isCollapsed">
        <div class="nav-title">Configuración</div>
        
        <router-link 
          to="/admin/settings" 
          class="nav-item"
          active-class="active"
        >
          <i class="nav-icon fas fa-cog"></i>
          <span class="nav-text">Configuración</span>
        </router-link>

        <router-link 
          to="/admin/profile" 
          class="nav-item"
          active-class="active"
        >
          <i class="nav-icon fas fa-user"></i>
          <span class="nav-text">Mi Perfil</span>
        </router-link>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="user-info" v-if="!isCollapsed">
        <div class="user-avatar">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="user-details">
          <div class="user-name">{{ user?.name || 'Admin' }}</div>
          <div class="user-role">Administrador</div>
        </div>
      </div>
      
      <button 
        class="logout-btn"
        @click="handleLogout"
        :title="isCollapsed ? 'Cerrar Sesión' : ''"
      >
        <i class="fas fa-sign-out-alt"></i>
        <span v-if="!isCollapsed">Cerrar Sesión</span>
      </button>
    </div>
  </aside>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

export default {
  name: 'AdminSidebar',
  setup() {
    const router = useRouter()
    
    const isCollapsed = ref(false)
    const activeRifasCount = ref(0)
    
    // Simulamos el usuario desde localStorage
    const user = ref({
      name: 'Admin Usuario',
      email: 'admin@danilorerifas.com',
      role: 'admin'
    })

    const toggleSidebar = () => {
      isCollapsed.value = !isCollapsed.value
      // Emitir evento para ajustar el layout principal
      document.dispatchEvent(new CustomEvent('sidebar-toggle', { 
        detail: { collapsed: isCollapsed.value } 
      }))
    }

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

    const loadActiveRifasCount = async () => {
      try {
        // Cargar cuenta de rifas activas desde la API
        activeRifasCount.value = 5 // Placeholder
      } catch (error) {
        console.error('Error al cargar rifas activas:', error)
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
      
      loadActiveRifasCount()
    })

    return {
      isCollapsed,
      activeRifasCount,
      user,
      toggleSidebar,
      handleLogout
    }
  }
}
</script>
    
<style scoped>
/* Las clases están definidas en admin.css global */
</style>
