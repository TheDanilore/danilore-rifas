<template>
  <div class="admin-usuarios">
    <AdminHeader />
    
    <main class="admin-main">
      <div class="admin-container">
        <!-- Page Header -->
        <div class="admin-page-header">
          <div class="page-title-section">
            <h1 class="admin-page-title">
              <i class="fas fa-users"></i>
              Gestión de Usuarios
            </h1>
            <p class="admin-page-subtitle">
              Administra todos los usuarios del sistema
            </p>
          </div>
          
          <div class="page-actions">
            <button 
              @click="showCreateUserModal = true" 
              class="admin-btn primary"
            >
              <i class="fas fa-plus"></i>
              Crear Usuario
            </button>
            <button 
              @click="exportUsers" 
              class="admin-btn secondary"
            >
              <i class="fas fa-download"></i>
              Exportar
            </button>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="admin-stats-grid">
          <div class="admin-stat-card primary">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-users"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +12
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ totalUsuarios }}</div>
              <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Este mes</span>
            </div>
          </div>

          <div class="admin-stat-card success">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-user-check"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +8
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ usuariosActivos }}</div>
              <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Última semana</span>
            </div>
          </div>

          <div class="admin-stat-card info">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="stat-trend positive">
                <i class="fas fa-arrow-up"></i>
                +5
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ nuevosUsuarios }}</div>
              <div class="stat-label">Nuevos Hoy</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Últimas 24h</span>
            </div>
          </div>

          <div class="admin-stat-card warning">
            <div class="stat-header">
              <div class="stat-icon">
                <i class="fas fa-user-times"></i>
              </div>
              <div class="stat-trend negative">
                <i class="fas fa-arrow-down"></i>
                -2
              </div>
            </div>
            <div class="stat-content">
              <div class="stat-value">{{ usuariosInactivos }}</div>
              <div class="stat-label">Usuarios Inactivos</div>
            </div>
            <div class="stat-footer">
              <span class="stat-period">Esta semana</span>
            </div>
          </div>
        </div>

        <!-- Filters and Search -->
        <div class="admin-section">
          <div class="admin-filters">
            <div class="filter-group">
              <div class="admin-search">
                <div class="admin-input-group">
                  <i class="fas fa-search input-icon"></i>
                  <input
                    v-model="searchQuery"
                    type="text"
                    class="admin-input"
                    placeholder="Buscar usuarios..."
                    @input="handleSearch"
                  />
                </div>
              </div>
              
              <select v-model="filterStatus" class="admin-select" @change="applyFilters">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
                <option value="bloqueado">Bloqueados</option>
              </select>
              
              <select v-model="filterRole" class="admin-select" @change="applyFilters">
                <option value="">Todos los roles</option>
                <option value="user">Usuario</option>
                <option value="admin">Administrador</option>
              </select>
              
              <button @click="clearFilters" class="admin-btn secondary sm">
                <i class="fas fa-times"></i>
                Limpiar
              </button>
            </div>
            
            <div class="results-info">
              <span class="results-count">
                {{ filteredUsuarios.length }} usuarios encontrados
              </span>
            </div>
          </div>
        </div>

        <!-- Users Table -->
        <div class="admin-section">
          <div class="admin-table-container">
            <div v-if="loading" class="admin-loading">
              <div class="loading-spinner"></div>
              <span>Cargando usuarios...</span>
            </div>

            <div v-else-if="filteredUsuarios.length === 0" class="admin-empty-state">
              <i class="fas fa-users"></i>
              <h3>No hay usuarios</h3>
              <p>No se encontraron usuarios con los criterios especificados</p>
              <button @click="clearFilters" class="admin-btn primary">
                <i class="fas fa-refresh"></i>
                Limpiar filtros
              </button>
            </div>

            <table v-else class="admin-table">
              <thead>
                <tr>
                  <th class="sortable" @click="sortBy('name')">
                    <span>Usuario</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('email')">
                    <span>Email</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('role')">
                    <span>Rol</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('status')">
                    <span>Estado</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('created_at')">
                    <span>Registro</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th class="sortable" @click="sortBy('last_login')">
                    <span>Último Acceso</span>
                    <i class="fas fa-sort"></i>
                  </th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="usuario in paginatedUsuarios" :key="usuario.id" class="table-row">
                  <td>
                    <div class="user-info">
                      <div class="user-avatar">
                        <img 
                          v-if="usuario.avatar" 
                          :src="usuario.avatar" 
                          :alt="usuario.name"
                          @error="handleImageError"
                        />
                        <i v-else class="fas fa-user"></i>
                      </div>
                      <div class="user-details">
                        <div class="user-name">{{ usuario.name }}</div>
                        <div class="user-id">ID: {{ usuario.id }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="email-cell">
                      <span class="email">{{ usuario.email }}</span>
                      <span v-if="usuario.email_verified_at" class="verified-badge">
                        <i class="fas fa-check-circle"></i>
                      </span>
                    </div>
                  </td>
                  <td>
                    <span class="role-badge" :class="usuario.role">
                      {{ formatRole(usuario.role) }}
                    </span>
                  </td>
                  <td>
                    <span class="status-badge" :class="usuario.status">
                      {{ formatStatus(usuario.status) }}
                    </span>
                  </td>
                  <td>
                    <div class="date-cell">
                      <div class="date">{{ formatDate(usuario.created_at) }}</div>
                      <div class="time">{{ formatTime(usuario.created_at) }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="date-cell">
                      <div class="date">{{ formatDate(usuario.last_login) }}</div>
                      <div class="time">{{ formatRelativeTime(usuario.last_login) }}</div>
                    </div>
                  </td>
                  <td>
                    <div class="table-actions">
                      <button 
                        @click="viewUser(usuario)"
                        class="action-btn view"
                        title="Ver detalles"
                      >
                        <i class="fas fa-eye"></i>
                      </button>
                      <button 
                        @click="editUser(usuario)"
                        class="action-btn edit"
                        title="Editar usuario"
                      >
                        <i class="fas fa-edit"></i>
                      </button>
                      <button 
                        @click="toggleUserStatus(usuario)"
                        class="action-btn" 
                        :class="usuario.status === 'activo' ? 'disable' : 'enable'"
                        :title="usuario.status === 'activo' ? 'Desactivar' : 'Activar'"
                      >
                        <i :class="usuario.status === 'activo' ? 'fas fa-ban' : 'fas fa-check'"></i>
                      </button>
                      <button 
                        @click="deleteUser(usuario)"
                        class="action-btn delete"
                        title="Eliminar usuario"
                      >
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="admin-pagination">
            <button 
              @click="previousPage"
              :disabled="currentPage === 1"
              class="pagination-btn"
            >
              <i class="fas fa-chevron-left"></i>
              Anterior
            </button>
            
            <div class="pagination-numbers">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                class="pagination-number"
                :class="{ active: page === currentPage }"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="pagination-btn"
            >
              Siguiente
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Create/Edit User Modal -->
    <div v-if="showCreateUserModal || showEditUserModal" class="admin-modal-overlay" @click="closeModals">
      <div class="admin-modal" @click.stop>
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">
            <i class="fas fa-user-plus" v-if="showCreateUserModal"></i>
            <i class="fas fa-user-edit" v-if="showEditUserModal"></i>
            {{ showCreateUserModal ? 'Crear Nuevo Usuario' : 'Editar Usuario' }}
          </h3>
          <button @click="closeModals" class="admin-modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-modal-body">
          <form @submit.prevent="submitUserForm" class="admin-form">
            <div class="admin-form-row">
              <div class="admin-form-group">
                <label class="admin-label">Nombre completo</label>
                <input
                  v-model="userForm.name"
                  type="text"
                  class="admin-input"
                  placeholder="Ingresa el nombre completo"
                  required
                />
              </div>
              
              <div class="admin-form-group">
                <label class="admin-label">Email</label>
                <input
                  v-model="userForm.email"
                  type="email"
                  class="admin-input"
                  placeholder="usuario@email.com"
                  required
                />
              </div>
            </div>

            <div class="admin-form-row" v-if="showCreateUserModal">
              <div class="admin-form-group">
                <label class="admin-label">Contraseña</label>
                <div class="admin-input-group">
                  <input
                    v-model="userForm.password"
                    :type="showPassword ? 'text' : 'password'"
                    class="admin-input"
                    placeholder="••••••••"
                    required
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="password-toggle-btn"
                  >
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </button>
                </div>
              </div>
              
              <div class="admin-form-group">
                <label class="admin-label">Confirmar contraseña</label>
                <input
                  v-model="userForm.password_confirmation"
                  type="password"
                  class="admin-input"
                  placeholder="••••••••"
                  required
                />
              </div>
            </div>

            <div class="admin-form-row">
              <div class="admin-form-group">
                <label class="admin-label">Rol</label>
                <select v-model="userForm.role" class="admin-select" required>
                  <option value="">Seleccionar rol</option>
                  <option value="user">Usuario</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
              
              <div class="admin-form-group">
                <label class="admin-label">Estado</label>
                <select v-model="userForm.status" class="admin-select" required>
                  <option value="">Seleccionar estado</option>
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                  <option value="bloqueado">Bloqueado</option>
                </select>
              </div>
            </div>

            <div class="admin-form-group">
              <label class="admin-label">Teléfono (opcional)</label>
              <input
                v-model="userForm.phone"
                type="tel"
                class="admin-input"
                placeholder="+51 999 999 999"
              />
            </div>

            <div class="admin-form-actions">
              <button type="button" @click="closeModals" class="admin-btn secondary">
                Cancelar
              </button>
              <button type="submit" :disabled="submitting" class="admin-btn primary">
                <i v-if="submitting" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ submitting ? 'Guardando...' : 'Guardar Usuario' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- User Details Modal -->
    <div v-if="showUserDetailsModal" class="admin-modal-overlay" @click="closeModals">
      <div class="admin-modal large" @click.stop>
        <div class="admin-modal-header">
          <h3 class="admin-modal-title">
            <i class="fas fa-user"></i>
            Detalles del Usuario
          </h3>
          <button @click="closeModals" class="admin-modal-close">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-modal-body">
          <div v-if="selectedUser" class="user-details">
            <div class="user-header">
              <div class="user-avatar large">
                <img 
                  v-if="selectedUser.avatar" 
                  :src="selectedUser.avatar" 
                  :alt="selectedUser.name"
                />
                <i v-else class="fas fa-user"></i>
              </div>
              <div class="user-info">
                <h3>{{ selectedUser.name }}</h3>
                <p>{{ selectedUser.email }}</p>
                <div class="user-badges">
                  <span class="role-badge" :class="selectedUser.role">
                    {{ formatRole(selectedUser.role) }}
                  </span>
                  <span class="status-badge" :class="selectedUser.status">
                    {{ formatStatus(selectedUser.status) }}
                  </span>
                </div>
              </div>
            </div>
            
            <div class="user-stats">
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="stat-value">{{ selectedUser.total_purchases || 0 }}</div>
                <div class="stat-label">Compras</div>
              </div>
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-value">${{ selectedUser.total_spent || 0 }}</div>
                <div class="stat-label">Gastado</div>
              </div>
              <div class="stat-item">
                <div class="stat-icon">
                  <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-value">{{ selectedUser.total_wins || 0 }}</div>
                <div class="stat-label">Premios</div>
              </div>
            </div>
            
            <div class="user-details-grid">
              <div class="detail-group">
                <h4>Información Personal</h4>
                <div class="detail-item">
                  <span class="detail-label">ID:</span>
                  <span class="detail-value">{{ selectedUser.id }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Teléfono:</span>
                  <span class="detail-value">{{ selectedUser.phone || 'No especificado' }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Registro:</span>
                  <span class="detail-value">{{ formatDate(selectedUser.created_at) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Último acceso:</span>
                  <span class="detail-value">{{ formatDate(selectedUser.last_login) }}</span>
                </div>
              </div>
              
              <div class="detail-group">
                <h4>Actividad Reciente</h4>
                <div class="activity-list">
                  <div class="activity-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Compró 3 tickets de "iPhone 15"</span>
                    <small>Hace 2 horas</small>
                  </div>
                  <div class="activity-item">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Inició sesión</span>
                    <small>Hace 5 horas</small>
                  </div>
                  <div class="activity-item">
                    <i class="fas fa-user-edit"></i>
                    <span>Actualizó su perfil</span>
                    <small>Hace 1 día</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="admin-modal-footer">
          <button @click="editUser(selectedUser)" class="admin-btn primary">
            <i class="fas fa-edit"></i>
            Editar Usuario
          </button>
          <button @click="closeModals" class="admin-btn secondary">
            Cerrar
          </button>
        </div>
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
    const loading = ref(false)
    const searchQuery = ref('')
    const filterStatus = ref('')
    const filterRole = ref('')
    const sortField = ref('created_at')
    const sortDirection = ref('desc')
    const currentPage = ref(1)
    const itemsPerPage = 10

    // Modals
    const showCreateUserModal = ref(false)
    const showEditUserModal = ref(false)
    const showUserDetailsModal = ref(false)
    const selectedUser = ref(null)
    const submitting = ref(false)
    const showPassword = ref(false)

    // User form
    const userForm = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: '',
      status: '',
      phone: ''
    })

    // Mock data
    const usuarios = ref([
      {
        id: 1,
        name: 'Carlos Mendoza',
        email: 'carlos.mendoza@email.com',
        role: 'user',
        status: 'activo',
        phone: '+51 987 654 321',
        created_at: '2024-01-15T10:30:00Z',
        last_login: '2024-02-10T10:30:00Z',
        email_verified_at: '2024-01-15T10:35:00Z',
        avatar: null,
        total_purchases: 12,
        total_spent: 180,
        total_wins: 2
      },
      {
        id: 2,
        name: 'María González',
        email: 'maria.gonzalez@email.com',
        role: 'user',
        status: 'activo',
        phone: '+51 987 654 322',
        created_at: '2024-01-20T09:15:00Z',
        last_login: '2024-02-09T15:45:00Z',
        email_verified_at: '2024-01-20T09:20:00Z',
        avatar: null,
        total_purchases: 8,
        total_spent: 120,
        total_wins: 1
      },
      {
        id: 3,
        name: 'Luis Ramírez',
        email: 'luis.ramirez@email.com',
        role: 'user',
        status: 'inactivo',
        phone: '+51 987 654 323',
        created_at: '2024-01-10T08:00:00Z',
        last_login: '2024-01-25T09:15:00Z',
        email_verified_at: null,
        avatar: null,
        total_purchases: 5,
        total_spent: 75,
        total_wins: 0
      },
      {
        id: 4,
        name: 'Ana Torres',
        email: 'ana.torres@email.com',
        role: 'admin',
        status: 'activo',
        phone: '+51 987 654 324',
        created_at: '2024-02-01T11:00:00Z',
        last_login: '2024-02-10T16:30:00Z',
        email_verified_at: '2024-02-01T11:05:00Z',
        avatar: null,
        total_purchases: 0,
        total_spent: 0,
        total_wins: 0
      },
      {
        id: 5,
        name: 'Pedro Silva',
        email: 'pedro.silva@email.com',
        role: 'user',
        status: 'bloqueado',
        phone: '+51 987 654 325',
        created_at: '2024-01-05T14:20:00Z',
        last_login: '2024-01-15T12:00:00Z',
        email_verified_at: '2024-01-05T14:25:00Z',
        avatar: null,
        total_purchases: 3,
        total_spent: 45,
        total_wins: 0
      }
    ])

    // Computed properties
    const totalUsuarios = computed(() => usuarios.value.length)
    
    const usuariosActivos = computed(() => 
      usuarios.value.filter(u => u.status === 'activo').length
    )
    
    const nuevosUsuarios = computed(() => {
      const today = new Date()
      const todayStart = new Date(today.getFullYear(), today.getMonth(), today.getDate())
      return usuarios.value.filter(u => new Date(u.created_at) >= todayStart).length
    })
    
    const usuariosInactivos = computed(() => 
      usuarios.value.filter(u => u.status === 'inactivo' || u.status === 'bloqueado').length
    )

    const filteredUsuarios = computed(() => {
      let filtered = usuarios.value

      // Search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(query) ||
          user.email.toLowerCase().includes(query)
        )
      }

      // Status filter
      if (filterStatus.value) {
        filtered = filtered.filter(user => user.status === filterStatus.value)
      }

      // Role filter
      if (filterRole.value) {
        filtered = filtered.filter(user => user.role === filterRole.value)
      }

      // Sort
      filtered.sort((a, b) => {
        let aValue = a[sortField.value]
        let bValue = b[sortField.value]
        
        if (typeof aValue === 'string') {
          aValue = aValue.toLowerCase()
          bValue = bValue.toLowerCase()
        }
        
        if (sortDirection.value === 'asc') {
          return aValue > bValue ? 1 : -1
        } else {
          return aValue < bValue ? 1 : -1
        }
      })

      return filtered
    })

    const totalPages = computed(() => 
      Math.ceil(filteredUsuarios.value.length / itemsPerPage)
    )

    const paginatedUsuarios = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return filteredUsuarios.value.slice(start, end)
    })

    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value
      
      if (total <= 7) {
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        pages.push(1)
        if (current > 4) pages.push('...')
        
        const start = Math.max(2, current - 1)
        const end = Math.min(total - 1, current + 1)
        
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }
        
        if (current < total - 3) pages.push('...')
        pages.push(total)
      }
      
      return pages
    })

    // Methods
    const handleSearch = () => {
      currentPage.value = 1
    }

    const applyFilters = () => {
      currentPage.value = 1
    }

    const clearFilters = () => {
      searchQuery.value = ''
      filterStatus.value = ''
      filterRole.value = ''
      currentPage.value = 1
    }

    const sortBy = (field) => {
      if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortField.value = field
        sortDirection.value = 'asc'
      }
    }

    const goToPage = (page) => {
      if (page !== '...' && page >= 1 && page <= totalPages.value) {
        currentPage.value = page
      }
    }

    const previousPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
      }
    }

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++
      }
    }

    const viewUser = (user) => {
      selectedUser.value = user
      showUserDetailsModal.value = true
    }

    const editUser = (user) => {
      selectedUser.value = user
      userForm.value = {
        name: user.name,
        email: user.email,
        role: user.role,
        status: user.status,
        phone: user.phone || '',
        password: '',
        password_confirmation: ''
      }
      showEditUserModal.value = true
    }

    const toggleUserStatus = async (user) => {
      const newStatus = user.status === 'activo' ? 'inactivo' : 'activo'
      
      try {
        // Simular API call
        await new Promise(resolve => setTimeout(resolve, 500))
        user.status = newStatus
        console.log(`Usuario ${user.name} ${newStatus}`)
      } catch (error) {
        console.error('Error al cambiar estado:', error)
      }
    }

    const deleteUser = async (user) => {
      if (confirm(`¿Estás seguro de eliminar al usuario ${user.name}?`)) {
        try {
          // Simular API call
          await new Promise(resolve => setTimeout(resolve, 500))
          const index = usuarios.value.findIndex(u => u.id === user.id)
          if (index > -1) {
            usuarios.value.splice(index, 1)
          }
          console.log(`Usuario ${user.name} eliminado`)
        } catch (error) {
          console.error('Error al eliminar usuario:', error)
        }
      }
    }

    const submitUserForm = async () => {
      submitting.value = true
      
      try {
        // Validaciones
        if (showCreateUserModal.value && userForm.value.password !== userForm.value.password_confirmation) {
          alert('Las contraseñas no coinciden')
          return
        }

        // Simular API call
        await new Promise(resolve => setTimeout(resolve, 1000))

        if (showCreateUserModal.value) {
          // Crear nuevo usuario
          const newUser = {
            id: usuarios.value.length + 1,
            name: userForm.value.name,
            email: userForm.value.email,
            role: userForm.value.role,
            status: userForm.value.status,
            phone: userForm.value.phone,
            created_at: new Date().toISOString(),
            last_login: null,
            email_verified_at: null,
            avatar: null,
            total_purchases: 0,
            total_spent: 0,
            total_wins: 0
          }
          usuarios.value.push(newUser)
          console.log('Usuario creado:', newUser)
        } else {
          // Editar usuario existente
          Object.assign(selectedUser.value, {
            name: userForm.value.name,
            email: userForm.value.email,
            role: userForm.value.role,
            status: userForm.value.status,
            phone: userForm.value.phone
          })
          console.log('Usuario actualizado:', selectedUser.value)
        }

        closeModals()
      } catch (error) {
        console.error('Error al guardar usuario:', error)
      } finally {
        submitting.value = false
      }
    }

    const closeModals = () => {
      showCreateUserModal.value = false
      showEditUserModal.value = false
      showUserDetailsModal.value = false
      selectedUser.value = null
      userForm.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
        status: '',
        phone: ''
      }
      showPassword.value = false
    }

    const exportUsers = () => {
      console.log('Exportando usuarios...')
      // Implementar exportación
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('es-PE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const formatTime = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleTimeString('es-PE', {
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const formatRelativeTime = (dateString) => {
      if (!dateString) return 'Nunca'
      const date = new Date(dateString)
      const now = new Date()
      const diffInHours = (now - date) / (1000 * 60 * 60)
      
      if (diffInHours < 1) {
        return 'Hace pocos minutos'
      } else if (diffInHours < 24) {
        return `Hace ${Math.floor(diffInHours)} horas`
      } else if (diffInHours < 168) { // 7 days
        return `Hace ${Math.floor(diffInHours / 24)} días`
      } else {
        return formatDate(dateString)
      }
    }

    const formatRole = (role) => {
      const roles = {
        'user': 'Usuario',
        'admin': 'Administrador'
      }
      return roles[role] || role
    }

    const formatStatus = (status) => {
      const statuses = {
        'activo': 'Activo',
        'inactivo': 'Inactivo',
        'bloqueado': 'Bloqueado'
      }
      return statuses[status] || status
    }

    const handleImageError = (event) => {
      event.target.style.display = 'none'
      event.target.nextElementSibling.style.display = 'flex'
    }

    onMounted(() => {
      // Cargar datos iniciales
    })

    return {
      loading,
      searchQuery,
      filterStatus,
      filterRole,
      currentPage,
      totalUsuarios,
      usuariosActivos,
      nuevosUsuarios,
      usuariosInactivos,
      filteredUsuarios,
      paginatedUsuarios,
      totalPages,
      visiblePages,
      showCreateUserModal,
      showEditUserModal,
      showUserDetailsModal,
      selectedUser,
      userForm,
      submitting,
      showPassword,
      handleSearch,
      applyFilters,
      clearFilters,
      sortBy,
      goToPage,
      previousPage,
      nextPage,
      viewUser,
      editUser,
      toggleUserStatus,
      deleteUser,
      submitUserForm,
      closeModals,
      exportUsers,
      formatDate,
      formatTime,
      formatRelativeTime,
      formatRole,
      formatStatus,
      handleImageError
    }
  }
}
</script>

<style scoped>
/* Usar clases del admin.css global */

.user-details {
  max-width: 100%;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--admin-border-light);
}

.user-avatar.large {
  width: 80px;
  height: 80px;
  font-size: 2rem;
}

.user-badges {
  display: flex;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.user-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  background: var(--admin-bg-light);
  border: 1px solid var(--admin-border-light);
  border-radius: 8px;
  padding: 1rem;
  text-align: center;
}

.stat-item .stat-icon {
  width: 40px;
  height: 40px;
  background: var(--admin-primary-teal);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  margin: 0 auto 0.5rem;
}

.stat-item .stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--admin-text-dark);
}

.stat-item .stat-label {
  font-size: 0.875rem;
  color: var(--admin-text-muted);
}

.user-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.detail-group h4 {
  margin: 0 0 1rem 0;
  color: var(--admin-text-dark);
  font-weight: 600;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--admin-border-light);
}

.detail-label {
  font-weight: 500;
  color: var(--admin-text-muted);
}

.detail-value {
  color: var(--admin-text-dark);
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: var(--admin-bg-light);
  border-radius: 8px;
}

.activity-item i {
  color: var(--admin-primary-teal);
  width: 20px;
  text-align: center;
}

.activity-item span {
  flex: 1;
  font-size: 0.875rem;
}

.activity-item small {
  color: var(--admin-text-muted);
  font-size: 0.75rem;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
}

.action-btn.view {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
}

.action-btn.view:hover {
  background: var(--admin-info);
  color: white;
}

.action-btn.edit {
  background: rgba(251, 191, 36, 0.1);
  color: var(--admin-warning);
}

.action-btn.edit:hover {
  background: var(--admin-warning);
  color: white;
}

.action-btn.enable {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.action-btn.enable:hover {
  background: var(--admin-success);
  color: white;
}

.action-btn.disable {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.action-btn.disable:hover {
  background: var(--admin-danger);
  color: white;
}

.action-btn.delete {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.action-btn.delete:hover {
  background: var(--admin-danger);
  color: white;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: var(--admin-primary-teal);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.user-details .user-name {
  font-weight: 600;
  color: var(--admin-text-dark);
  margin: 0 0 4px 0;
}

.user-id {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
}

.email-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.verified-badge {
  color: var(--admin-success);
  font-size: 0.875rem;
}

.role-badge,
.status-badge {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 500;
}

.role-badge.user {
  background: rgba(59, 130, 246, 0.1);
  color: var(--admin-info);
}

.role-badge.admin {
  background: rgba(147, 51, 234, 0.1);
  color: #9333ea;
}

.status-badge.activo {
  background: rgba(34, 197, 94, 0.1);
  color: var(--admin-success);
}

.status-badge.inactivo {
  background: rgba(156, 163, 175, 0.1);
  color: #6b7280;
}

.status-badge.bloqueado {
  background: rgba(239, 68, 68, 0.1);
  color: var(--admin-danger);
}

.date-cell .date {
  font-weight: 500;
  color: var(--admin-text-dark);
}

.date-cell .time {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
}

@media (max-width: 768px) {
  .user-details-grid {
    grid-template-columns: 1fr;
  }
  
  .user-stats {
    grid-template-columns: 1fr;
  }
  
  .user-header {
    flex-direction: column;
    text-align: center;
  }
  
  .table-actions {
    flex-wrap: wrap;
  }
}
</style>
       