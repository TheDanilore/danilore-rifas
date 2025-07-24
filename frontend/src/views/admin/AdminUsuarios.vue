<template>
  <div class="admin-usuarios">
    <AdminHeader />
    
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <i class="fas fa-users"></i>
            Gestión de Usuarios
          </h1>
          <p class="hero-subtitle">
            Administra todos los usuarios del sistema
          </p>
        </div>
        
        <div class="hero-actions">
          <button @click="showCreateUserModal = true" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i>
            Crear Usuario
          </button>
          <button @click="exportUsers" class="btn btn-outline btn-lg">
            <i class="fas fa-download"></i>
            Exportar Usuarios
          </button>
        </div>
      </div>
    </section>

    <!-- Stats Cards -->
    <section class="stats-section">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon users">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">{{ totalUsuarios }}</h3>
              <p class="stat-label">Total Usuarios</p>
              <span class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +12 este mes
              </span>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon active">
              <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">{{ usuariosActivos }}</h3>
              <p class="stat-label">Usuarios Activos</p>
              <span class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +8 esta semana
              </span>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon purchases">
              <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">{{ usuariosConCompras }}</h3>
              <p class="stat-label">Con Compras</p>
              <span class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                85% conversión
              </span>
            </div>
          </div>
          
          <div class="stat-card">
            <div class="stat-icon revenue">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
              <h3 class="stat-number">S/. {{ ingresosPorUsuario }}</h3>
              <p class="stat-label">Promedio por Usuario</p>
              <span class="stat-change positive">
                <i class="fas fa-arrow-up"></i>
                +15% vs mes anterior
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Filters -->
    <section class="filters-section">
      <div class="container">
        <div class="filters-card">
          <div class="filters-row">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar usuarios por nombre, email..."
                class="search-input"
              />
            </div>
            
            <div class="filter-group">
              <select v-model="statusFilter" class="filter-select">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
                <option value="suspendido">Suspendidos</option>
              </select>
              
              <select v-model="sortBy" class="filter-select">
                <option value="fecha">Ordenar por fecha</option>
                <option value="nombre">Ordenar por nombre</option>
                <option value="compras">Por compras</option>
                <option value="gasto">Por gasto total</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Users Table -->
    <section class="users-table-section">
      <div class="container">
        <div class="table-card">
          <div class="table-header">
            <h3>Lista de Usuarios</h3>
            <div class="table-actions">
              <button class="btn btn-ghost btn-sm">
                <i class="fas fa-filter"></i>
                Filtros Avanzados
              </button>
            </div>
          </div>
          
          <div class="table-container">
            <table class="users-table">
              <thead>
                <tr>
                  <th>Usuario</th>
                  <th>Estado</th>
                  <th>Registro</th>
                  <th>Compras</th>
                  <th>Total Gastado</th>
                  <th>Último Acceso</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="usuario in filteredUsuarios" :key="usuario.id" class="table-row">
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <i class="fas fa-user"></i>
                      </div>
                      <div>
                        <h4 class="user-name">{{ usuario.nombre }}</h4>
                        <p class="user-email">{{ usuario.email }}</p>
                        <p class="user-phone">{{ usuario.telefono }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge" :class="usuario.estado">
                      {{ usuario.estado }}
                    </span>
                  </td>
                  <td>{{ formatDate(usuario.fechaRegistro) }}</td>
                  <td>
                    <div class="purchases-cell">
                      <span class="purchases-count">{{ usuario.totalCompras }}</span>
                      <span class="purchases-label">compras</span>
                    </div>
                  </td>
                  <td class="amount">S/. {{ usuario.totalGastado.toLocaleString() }}</td>
                  <td>
                    <span class="last-access">{{ formatRelativeTime(usuario.ultimoAcceso) }}</span>
                  </td>
                  <td>
                    <div class="actions-cell">
                      <button @click="viewUser(usuario)" class="action-btn view">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="editUser(usuario)" class="action-btn edit">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button 
                        @click="toggleUserStatus(usuario)" 
                        class="action-btn"
                        :class="usuario.estado === 'activo' ? 'suspend' : 'activate'"
                      >
                        <i :class="usuario.estado === 'activo' ? 'fas fa-ban' : 'fas fa-check'"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="table-pagination">
            <div class="pagination-info">
              Mostrando {{ filteredUsuarios.length }} de {{ usuarios.length }} usuarios
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="page-number">{{ currentPage }}</span>
              <button class="pagination-btn" :disabled="currentPage === totalPages">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Crear Usuario -->
    <div v-if="showCreateUserModal" class="modal-overlay" @click="closeCreateUserModal">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>Crear Nuevo Usuario</h3>
          <button @click="closeCreateUserModal" class="modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="createUser" class="modal-form">
          <div class="form-row">
            <div class="form-group">
              <label>Nombre Completo</label>
              <input
                v-model="newUser.nombre"
                type="text"
                placeholder="Ingrese el nombre completo"
                required
              />
            </div>
            <div class="form-group">
              <label>Email</label>
              <input
                v-model="newUser.email"
                type="email"
                placeholder="usuario@email.com"
                required
              />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Teléfono</label>
              <input
                v-model="newUser.telefono"
                type="tel"
                placeholder="+51 987 654 321"
                required
              />
            </div>
            <div class="form-group">
              <label>Rol</label>
              <select v-model="newUser.rol" required>
                <option value="">Seleccionar rol</option>
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Contraseña Temporal</label>
              <input
                v-model="newUser.password"
                type="password"
                placeholder="Contraseña temporal"
                required
              />
            </div>
            <div class="form-group">
              <label>Estado</label>
              <select v-model="newUser.estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeCreateUserModal" class="btn btn-ghost">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-user-plus"></i>
              Crear Usuario
            </button>
          </div>
        </form>
      </div>
    </div>

    <AdminFooter />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'

export default {
  name: 'AdminUsuarios',
  components: {
    AdminHeader,
    AdminFooter
  },
  setup() {
    const searchQuery = ref('')
    const statusFilter = ref('')
    const sortBy = ref('fecha')
    const currentPage = ref(1)
    const showCreateUserModal = ref(false)

    const newUser = ref({
      nombre: '',
      email: '',
      telefono: '',
      rol: '',
      estado: 'activo',
      password: ''
    })

    const usuarios = ref([
      {
        id: 'USR001',
        nombre: 'Carlos Mendoza',
        email: 'carlos.mendoza@email.com',
        telefono: '+51 987 654 321',
        rol: 'usuario',
        estado: 'activo',
        fechaRegistro: '2024-01-15',
        totalCompras: 12,
        totalGastado: 180,
        ultimoAcceso: '2024-02-10T10:30:00Z'
      },
      {
        id: 'USR002',
        nombre: 'María González',
        email: 'maria.gonzalez@email.com',
        telefono: '+51 987 654 322',
        rol: 'usuario',
        estado: 'activo',
        fechaRegistro: '2024-01-20',
        totalCompras: 8,
        totalGastado: 120,
        ultimoAcceso: '2024-02-09T15:45:00Z'
      },
      {
        id: 'USR003',
        nombre: 'Luis Ramírez',
        email: 'luis.ramirez@email.com',
        telefono: '+51 987 654 323',
        rol: 'usuario',
        estado: 'inactivo',
        fechaRegistro: '2024-01-10',
        totalCompras: 5,
        totalGastado: 75,
        ultimoAcceso: '2024-01-25T09:15:00Z'
      },
      {
        id: 'USR004',
        nombre: 'Ana Torres',
        email: 'ana.torres@email.com',
        telefono: '+51 987 654 324',
        rol: 'admin',
        estado: 'activo',
        fechaRegistro: '2024-02-01',
        totalCompras: 15,
        totalGastado: 225,
        ultimoAcceso: '2024-02-10T14:20:00Z'
      },
      {
        id: 'USR005',
        nombre: 'José Morales',
        email: 'jose.morales@email.com',
        telefono: '+51 987 654 325',
        rol: 'usuario',
        estado: 'suspendido',
        fechaRegistro: '2024-01-05',
        totalCompras: 3,
        totalGastado: 45,
        ultimoAcceso: '2024-01-30T11:00:00Z'
      }
    ])

    const filteredUsuarios = computed(() => {
      let filtered = usuarios.value

      // Filtro por búsqueda
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(usuario =>
          usuario.nombre.toLowerCase().includes(query) ||
          usuario.email.toLowerCase().includes(query) ||
          usuario.telefono.includes(query)
        )
      }

      // Filtro por estado
      if (statusFilter.value) {
        filtered = filtered.filter(usuario => usuario.estado === statusFilter.value)
      }

      return filtered
    })

    const totalUsuarios = computed(() => usuarios.value.length)
    const usuariosActivos = computed(() => usuarios.value.filter(u => u.estado === 'activo').length)
    const usuariosConCompras = computed(() => usuarios.value.filter(u => u.totalCompras > 0).length)
    const ingresosPorUsuario = computed(() => {
      const total = usuarios.value.reduce((sum, u) => sum + u.totalGastado, 0)
      return Math.round(total / usuarios.value.length)
    })

    const totalPages = computed(() => Math.ceil(filteredUsuarios.value.length / 10))

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }

    const formatRelativeTime = (dateString) => {
      const date = new Date(dateString)
      const now = new Date()
      const diffMs = now - date
      const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
      const diffDays = Math.floor(diffHours / 24)

      if (diffDays > 0) {
        return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`
      } else if (diffHours > 0) {
        return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`
      } else {
        return 'Hace menos de 1 hora'
      }
    }

    const viewUser = (usuario) => {
      console.log('Ver usuario:', usuario)
    }

    const editUser = (usuario) => {
      console.log('Editar usuario:', usuario)
    }

    const toggleUserStatus = (usuario) => {
      const newStatus = usuario.estado === 'activo' ? 'suspendido' : 'activo'
      const action = newStatus === 'activo' ? 'activar' : 'suspender'
      
      if (confirm(`¿Estás seguro de ${action} al usuario "${usuario.nombre}"?`)) {
        usuario.estado = newStatus
        console.log(`Usuario ${action}do:`, usuario)
      }
    }

    const exportUsers = () => {
      console.log('Exportar usuarios')
    }

    const createUser = () => {
      const nextId = `USR${String(usuarios.value.length + 1).padStart(3, '0')}`
      const currentDate = new Date().toISOString().split('T')[0]
      
      const usuario = {
        id: nextId,
        nombre: newUser.value.nombre,
        email: newUser.value.email,
        telefono: newUser.value.telefono,
        rol: newUser.value.rol,
        estado: newUser.value.estado,
        fechaRegistro: currentDate,
        totalCompras: 0,
        totalGastado: 0,
        ultimoAcceso: new Date().toISOString()
      }
      
      usuarios.value.push(usuario)
      closeCreateUserModal()
      
      alert(`Usuario ${usuario.nombre} creado exitosamente con rol ${usuario.rol}`)
    }

    const closeCreateUserModal = () => {
      showCreateUserModal.value = false
      newUser.value = {
        nombre: '',
        email: '',
        telefono: '',
        rol: '',
        estado: 'activo',
        password: ''
      }
    }

    onMounted(() => {
      console.log('Admin Usuarios cargado')
    })

    return {
      searchQuery,
      statusFilter,
      sortBy,
      currentPage,
      showCreateUserModal,
      newUser,
      usuarios,
      filteredUsuarios,
      totalUsuarios,
      usuariosActivos,
      usuariosConCompras,
      ingresosPorUsuario,
      totalPages,
      formatDate,
      formatRelativeTime,
      viewUser,
      editUser,
      toggleUserStatus,
      exportUsers,
      createUser,
      closeCreateUserModal
    }
  }
}
</script>

<style scoped>
.admin-usuarios {
  min-height: 100vh;
  background: var(--gray-50);
  display: flex;
  flex-direction: column;
}

.admin-hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  color: white;
  padding: 2rem 0;
}

.hero-content {
  text-align: center;
  margin-bottom: 1.5rem;
}

.hero-title {
  font-size: 1.75rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.hero-subtitle {
  font-size: 1rem;
  opacity: 0.9;
}

.hero-actions {
  display: flex;
  justify-content: center;
  gap: 0.75rem;
}

.stats-section {
  padding: 2rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.stat-icon.users {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
}

.stat-icon.active {
  background: linear-gradient(135deg, var(--success-green), #34d399);
}

.stat-icon.purchases {
  background: linear-gradient(135deg, var(--primary-blue), #3b82f6);
}

.stat-icon.revenue {
  background: linear-gradient(135deg, var(--accent-orange), #f97316);
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 0.25rem;
}

.stat-label {
  color: var(--gray-600);
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.stat-change {
  font-size: 0.8rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.stat-change.positive {
  color: var(--success-green);
}

.filters-section {
  padding: 0 0 1.5rem 0;
}

.filters-card {
  background: white;
  padding: 1.5rem;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
}

.filters-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray-400);
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--border-radius);
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-purple);
}

.filter-group {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.875rem;
  border: 2px solid var(--gray-200);
  border-radius: var(--border-radius);
  background: white;
  cursor: pointer;
}

.users-table-section {
  padding: 0 0 3rem 0;
  flex: 1;
}

.table-card {
  background: white;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.table-header {
  padding: 2rem;
  border-bottom: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
}

.table-container {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table th {
  background: var(--gray-50);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--gray-700);
  border-bottom: 1px solid var(--gray-200);
}

.table-row {
  border-bottom: 1px solid var(--gray-100);
  transition: background 0.3s ease;
}

.table-row:hover {
  background: var(--gray-50);
}

.users-table td {
  padding: 1rem;
  vertical-align: middle;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 3rem;
  height: 3rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-indigo));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1rem;
}

.user-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.user-email {
  font-size: 0.875rem;
  color: var(--gray-600);
  margin-bottom: 0.125rem;
}

.user-phone {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: var(--border-radius-full);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.activo {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-green);
}

.status-badge.inactivo {
  background: rgba(107, 114, 128, 0.1);
  color: var(--gray-600);
}

.status-badge.suspendido {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-red);
}

.purchases-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.purchases-count {
  font-weight: 600;
  font-size: 1.125rem;
  color: var(--primary-purple);
}

.purchases-label {
  font-size: 0.75rem;
  color: var(--gray-500);
}

.amount {
  font-weight: 600;
  color: var(--success-green);
}

.last-access {
  font-size: 0.875rem;
  color: var(--gray-600);
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 2rem;
  height: 2rem;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.action-btn.view {
  background: rgba(107, 114, 128, 0.1);
  color: var(--gray-600);
}

.action-btn.edit {
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary-blue);
}

.action-btn.suspend {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger-red);
}

.action-btn.activate {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success-green);
}

.action-btn:hover {
  transform: scale(1.1);
}

.table-pagination {
  padding: 1.5rem 2rem;
  border-top: 1px solid var(--gray-200);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-info {
  color: var(--gray-600);
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-btn {
  width: 2rem;
  height: 2rem;
  border: 1px solid var(--gray-300);
  background: white;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--primary-purple);
  color: white;
  border-color: var(--primary-purple);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-number {
  font-weight: 600;
}

@media (max-width: 1024px) {
  .filters-row {
    flex-direction: column;
    gap: 1rem;
  }

  .search-box {
    max-width: none;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
    flex-direction: column;
    gap: 0.5rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .table-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .users-table {
    font-size: 0.75rem;
  }

  .user-info {
    flex-direction: column;
    text-align: center;
  }
}

/* Modal Styles - Diseño Compacto */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  width: 90%;
  max-width: 420px;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--gray-200);
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--gray-400);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  width: 1.75rem;
  height: 1.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--gray-100);
  color: var(--gray-600);
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-label {
  font-weight: 500;
  color: var(--text-primary);
  font-size: 0.8rem;
}

.form-input, .form-select {
  padding: 0.6rem;
  border: 1px solid var(--gray-300);
  border-radius: 6px;
  font-size: 0.9rem;
  transition: border-color 0.2s ease;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--gray-200);
}

.btn-cancel {
  padding: 0.6rem 1.25rem;
  border: 1px solid var(--gray-300);
  background: white;
  color: var(--gray-700);
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: var(--gray-50);
  border-color: var(--gray-400);
}

.btn-create {
  padding: 0.6rem 1.25rem;
  background: var(--primary-purple);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-create:hover {
  background: var(--primary-purple-dark);
}
</style>
