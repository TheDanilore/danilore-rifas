<template>
  <div class="admin-rifas">
    <AdminHeader />
    
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="container">
        <div class="hero-content">
          <h1 class="hero-title">
            <i class="fas fa-ticket-alt"></i>
            Gestión de Rifas
          </h1>
          <p class="hero-subtitle">
            Administra todas las rifas del sistema
          </p>
        </div>
        
        <div class="hero-actions">
          <button @click="openCreateModal" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i>
            Nueva Rifa
          </button>
          <button @click="exportData" class="btn btn-outline btn-lg">
            <i class="fas fa-download"></i>
            Exportar
          </button>
        </div>
      </div>
    </section>

    <!-- Loading state -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Cargando rifas...</p>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button class="btn btn-primary" @click="loadRifas">Reintentar</button>
    </div>

    <!-- Filters and Stats -->
    <section v-else class="filters-section">
      <div class="container">
        <div class="filters-card">
          <div class="filters-row">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar rifas..."
                class="search-input"
                @input="filterRifas"
              />
            </div>
            
            <div class="filter-group">
              <select v-model="statusFilter" class="filter-select" @change="filterRifas">
                <option value="">Todos los estados</option>
                <option value="borrador">Borradores</option>
                <option value="activa">Activas</option>
                <option value="pausada">Pausadas</option>
                <option value="finalizada">Finalizadas</option>
                <option value="cancelada">Canceladas</option>
              </select>
              
              <select v-model="sortBy" class="filter-select" @change="sortRifas">
                <option value="fecha">Ordenar por fecha</option>
                <option value="nombre">Ordenar por nombre</option>
                <option value="precio">Ordenar por precio</option>
                <option value="vendidos">Tickets vendidos</option>
              </select>
            </div>
          </div>
          
          <div class="quick-stats">
            <div class="quick-stat">
              <span class="stat-number">{{ estadisticas.rifas_activas || 0 }}</span>
              <span class="stat-label">Activas</span>
            </div>
            <div class="quick-stat">
              <span class="stat-number">S/ {{ formatMoney(estadisticas.total_ingresos || 0) }}</span>
              <span class="stat-label">Total Ingresos</span>
            </div>
            <div class="quick-stat">
              <span class="stat-number">{{ estadisticas.total_tickets || 0 }}</span>
              <span class="stat-label">Tickets Vendidos</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Rifas Table -->
    <section class="rifas-table-section">
      <div class="container">
        <div class="table-card">
          <div class="table-header">
            <h3>Lista de Rifas ({{ filteredRifas.length }})</h3>
            <div class="table-actions">
              <button @click="loadRifas" class="btn btn-ghost btn-sm">
                <i class="fas fa-sync-alt"></i>
                Actualizar
              </button>
            </div>
          </div>
          
          <div v-if="filteredRifas.length === 0 && !loading" class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No hay rifas</h3>
            <p>{{ searchQuery ? 'No se encontraron rifas que coincidan con tu búsqueda' : 'Aún no has creado ninguna rifa' }}</p>
            <button v-if="!searchQuery" @click="openCreateModal" class="btn btn-primary">
              <i class="fas fa-plus"></i>
              Crear primera rifa
            </button>
          </div>
          
          <div v-else class="table-container">
            <table class="rifas-table">
              <thead>
                <tr>
                  <th>Rifa</th>
                  <th>Estado</th>
                  <th>Precio</th>
                  <th>Tickets (Min/Max)</th>
                  <th>Vendidos</th>
                  <th>Ingresos</th>
                  <th>Fecha Fin</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rifa in filteredRifas" :key="rifa.id" class="table-row">
                  <td>
                    <div class="rifa-info">
                      <img :src="rifa.imagen_principal || 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=100&h=100&fit=crop'" 
                           :alt="rifa.titulo" 
                           class="rifa-thumb" 
                           @error="handleImageError" />
                      <div>
                        <h4 class="rifa-name">{{ rifa.titulo }}</h4>
                        <p class="rifa-id">Código: {{ rifa.codigo_unico }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="status-badge" :class="getStatusClass(rifa.estado)">
                      {{ formatStatus(rifa.estado) }}
                    </span>
                  </td>
                  <td class="price">S/ {{ formatMoney(rifa.precio_boleto) }}</td>
                  <td>{{ rifa.boletos_minimos || 0 }} / {{ rifa.boletos_maximos || '∞' }}</td>
                  <td>
                    <div class="progress-cell">
                      <span>{{ rifa.boletos_vendidos || 0 }}</span>
                      <div class="mini-progress">
                        <div 
                          class="progress-fill" 
                          :style="{ width: getProgressPercentage(rifa) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td class="price">S/ {{ formatMoney(getIngresos(rifa)) }}</td>
                  <td>{{ formatDate(rifa.fecha_fin) }}</td>
                  <td>
                    <div class="actions-cell">
                      <button @click="editRifa(rifa)" class="action-btn edit" title="Editar">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="viewRifa(rifa)" class="action-btn view" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="toggleEstado(rifa)" class="action-btn toggle" :title="rifa.estado === 'activa' ? 'Pausar' : 'Activar'">
                        <i :class="rifa.estado === 'activa' ? 'fas fa-pause' : 'fas fa-play'"></i>
                      </button>
                      <button @click="deleteRifa(rifa)" class="action-btn delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal para Crear/Editar Rifa -->
    <div v-if="showCreateModal || showEditModal" class="modal-overlay" @click="closeModals">
      <div class="modal rifa-modal" @click.stop>
        <div class="modal-header">
          <h2>{{ showEditModal ? 'Editar Rifa' : 'Nueva Rifa' }}</h2>
          <button class="close-btn" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-content">
          <form @submit.prevent="saveRifa" class="rifa-form">
            <div class="form-grid">
              <div class="form-group">
                <label for="titulo">Título de la Rifa *</label>
                <input 
                  id="titulo"
                  v-model="rifaForm.titulo" 
                  type="text" 
                  required
                  autocomplete="off"
                  placeholder="Ej: iPhone 15 Pro Max"
                >
              </div>
              
              <div class="form-group">
                <label for="codigo_unico">Código Único *</label>
                <input 
                  id="codigo_unico"
                  v-model="rifaForm.codigo_unico" 
                  type="text" 
                  required
                  autocomplete="off"
                  placeholder="Ej: RIFA001"
                  :disabled="showEditModal"
                >
              </div>
              
              <div class="form-group full-width">
                <label for="descripcion">Descripción</label>
                <textarea 
                  id="descripcion"
                  v-model="rifaForm.descripcion" 
                  rows="3"
                  autocomplete="off"
                  placeholder="Describe la rifa y sus premios"
                ></textarea>
              </div>
              
              <div class="form-group">
                <label for="precio_boleto">Precio por Boleto (S/) *</label>
                <input 
                  id="precio_boleto"
                  v-model="rifaForm.precio_boleto" 
                  type="number" 
                  step="0.01"
                  min="0.01"
                  required
                  autocomplete="off"
                  placeholder="2.00"
                >
              </div>
              
              <div class="form-group">
                <label for="categoria_id">Categoría</label>
                <select id="categoria_id" v-model="rifaForm.categoria_id" autocomplete="off">
                  <option value="">Seleccionar categoría</option>
                  <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                    {{ categoria.nombre }}
                  </option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="boletos_minimos">Boletos Mínimos *</label>
                <input 
                  id="boletos_minimos"
                  v-model="rifaForm.boletos_minimos" 
                  type="number" 
                  min="1"
                  required
                  autocomplete="off"
                  placeholder="100"
                >
              </div>
              
              <div class="form-group">
                <label for="boletos_maximos">Boletos Máximos</label>
                <input 
                  id="boletos_maximos"
                  v-model="rifaForm.boletos_maximos" 
                  type="number" 
                  min="1"
                  autocomplete="off"
                  placeholder="500"
                >
              </div>
              
              <div class="form-group">
                <label for="max_boletos_por_persona">Máx. Boletos por Persona</label>
                <input 
                  id="max_boletos_por_persona"
                  v-model="rifaForm.max_boletos_por_persona" 
                  type="number" 
                  min="1"
                  autocomplete="off"
                  placeholder="10"
                >
              </div>
              
              <div class="form-group">
                <label for="fecha_inicio">Fecha de Inicio *</label>
                <input 
                  id="fecha_inicio"
                  v-model="rifaForm.fecha_inicio" 
                  type="date" 
                  required
                  autocomplete="off"
                >
              </div>
              
              <div class="form-group">
                <label for="fecha_fin">Fecha de Fin *</label>
                <input 
                  id="fecha_fin"
                  v-model="rifaForm.fecha_fin" 
                  type="date" 
                  required
                  autocomplete="off"
                >
              </div>
              
              <div class="form-group">
                <label for="fecha_sorteo">Fecha de Sorteo</label>
                <input 
                  id="fecha_sorteo"
                  v-model="rifaForm.fecha_sorteo" 
                  type="datetime-local" 
                  autocomplete="off"
                >
              </div>
              
              <div class="form-group">
                <label for="tipo">Tipo de Rifa *</label>
                <select id="tipo" v-model="rifaForm.tipo" autocomplete="off" required>
                  <option value="actual">Actual (En venta)</option>
                  <option value="futura">Futura (Programada)</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="estado">Estado *</label>
                <select id="estado" v-model="rifaForm.estado" autocomplete="off" required>
                  <option value="borrador">Borrador</option>
                  <option value="activa">Activa</option>
                  <option value="pausada">Pausada</option>
                  <option value="finalizada">Finalizada</option>
                  <option value="cancelada">Cancelada</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="orden">Orden (para rifas futuras)</label>
                <input 
                  id="orden"
                  v-model="rifaForm.orden" 
                  type="number" 
                  min="1"
                  autocomplete="off"
                  placeholder="1"
                >
              </div>
              
              <div class="form-group">
                <label for="es_destacada">¿Es destacada?</label>
                <select id="es_destacada" v-model="rifaForm.es_destacada" autocomplete="off">
                  <option value="false">No</option>
                  <option value="true">Sí</option>
                </select>
              </div>
              
              <div class="form-group full-width">
                <ImageSelector
                  v-model="rifaForm.imagen_principal"
                  label="Imagen Principal de la Rifa"
                  placeholder="https://ejemplo.com/imagen.jpg"
                  upload-endpoint="/api/v1/upload/rifa-image"
                />
              </div>
              
              <div class="form-group full-width">
                <label for="terminos_condiciones">Términos y Condiciones</label>
                <textarea 
                  id="terminos_condiciones"
                  v-model="rifaForm.terminos_condiciones" 
                  rows="3"
                  autocomplete="off"
                  placeholder="Términos y condiciones específicos de esta rifa"
                ></textarea>
              </div>
              
              <div class="form-group full-width">
                <label for="notas_admin">Notas del Administrador</label>
                <textarea 
                  id="notas_admin"
                  v-model="rifaForm.notas_admin" 
                  rows="2"
                  autocomplete="off"
                  placeholder="Notas internas para el administrador"
                ></textarea>
              </div>
            </div>

            <!-- Sección de Premios -->
            <div class="premios-section">
              <div class="section-header">
                <h3>Premios y Niveles</h3>
                <button type="button" @click="addPremio" class="btn btn-outline btn-sm">
                  <i class="fas fa-plus"></i>
                  Agregar Premio
                </button>
              </div>

              <div v-for="(premio, premioIndex) in rifaForm.premios" :key="premioIndex" class="premio-card">
                <div class="premio-header">
                  <h4>Premio {{ premioIndex + 1 }}</h4>
                  <button 
                    v-if="rifaForm.premios.length > 1"
                    type="button" 
                    @click="removePremio(premioIndex)" 
                    class="btn-remove"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <div class="premio-fields">
                  <div class="form-group">
                    <label>Código del Premio *</label>
                    <input 
                      v-model="premio.codigo" 
                      type="text" 
                      required
                      autocomplete="off"
                      placeholder="Ej: P1, P2, P3"
                      maxlength="10"
                    >
                  </div>

                  <div class="form-group">
                    <label>Título del Premio *</label>
                    <input 
                      v-model="premio.titulo" 
                      type="text" 
                      required
                      autocomplete="off"
                      placeholder="Ej: AirPods Pro"
                    >
                  </div>

                  <div class="form-group">
                    <ImageSelector
                      v-model="premio.imagen_principal"
                      label="Imagen del Premio"
                      placeholder="https://ejemplo.com/premio.jpg"
                      upload-endpoint="/api/v1/upload/premio-image"
                    />
                  </div>

                  <div class="form-group">
                    <label>Estado del Premio</label>
                    <select v-model="premio.estado" autocomplete="off">
                      <option value="bloqueado">Bloqueado</option>
                      <option value="activo">Activo</option>
                      <option value="completado">Completado</option>
                    </select>
                  </div>

                  <div class="form-group full-width">
                    <label>Descripción del Premio</label>
                    <textarea 
                      v-model="premio.descripcion" 
                      rows="2"
                      autocomplete="off"
                      placeholder="Describe este premio específico"
                    ></textarea>
                  </div>

                  <div class="form-group full-width">
                    <label>Notas del Administrador</label>
                    <textarea 
                      v-model="premio.notas_admin" 
                      rows="1"
                      autocomplete="off"
                      placeholder="Notas internas sobre este premio"
                    ></textarea>
                  </div>
                </div>

                <!-- Niveles del Premio -->
                <div class="niveles-section">
                  <div class="niveles-header">
                    <h5>Niveles del Premio</h5>
                    <button 
                      type="button" 
                      @click="addNivel(premioIndex)" 
                      class="btn btn-outline btn-sm"
                    >
                      <i class="fas fa-plus"></i>
                      Agregar Nivel
                    </button>
                  </div>

                  <div v-for="(nivel, nivelIndex) in premio.niveles" :key="nivelIndex" class="nivel-card">
                    <div class="nivel-header">
                      <span>Nivel {{ nivelIndex + 1 }}</span>
                      <button 
                        v-if="premio.niveles.length > 1"
                        type="button" 
                        @click="removeNivel(premioIndex, nivelIndex)" 
                        class="btn-remove-small"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </div>

                    <div class="nivel-fields">
                      <div class="form-group">
                        <label>Código del Nivel *</label>
                        <input 
                          v-model="nivel.codigo" 
                          type="text" 
                          required
                          autocomplete="off"
                          placeholder="Ej: N1, N2, N3"
                          maxlength="10"
                        >
                      </div>

                      <div class="form-group">
                        <label>Título del Nivel *</label>
                        <input 
                          v-model="nivel.titulo" 
                          type="text" 
                          required
                          autocomplete="off"
                          placeholder="Ej: Nivel Básico"
                        >
                      </div>

                      <div class="form-group">
                        <label>Tickets Necesarios *</label>
                        <input 
                          v-model="nivel.tickets_necesarios" 
                          type="number" 
                          min="1"
                          required
                          autocomplete="off"
                          placeholder="100"
                        >
                      </div>

                      <div class="form-group">
                        <label>Valor Aproximado (S/)</label>
                        <input 
                          v-model="nivel.valor_aproximado" 
                          type="number" 
                          min="0"
                          step="0.01"
                          autocomplete="off"
                          placeholder="0.00"
                        >
                      </div>

                      <div class="form-group">
                        <ImageSelector
                          v-model="nivel.imagen"
                          label="Imagen del Nivel"
                          placeholder="https://ejemplo.com/nivel.jpg"
                          upload-endpoint="/api/v1/upload/nivel-image"
                        />
                      </div>

                      <div class="form-group">
                        <label>¿Es el nivel actual?</label>
                        <select v-model="nivel.es_actual" autocomplete="off">
                          <option value="false">No</option>
                          <option value="true">Sí</option>
                        </select>
                      </div>

                      <div class="form-group full-width">
                        <label>Descripción del Nivel</label>
                        <textarea 
                          v-model="nivel.descripcion" 
                          rows="2"
                          autocomplete="off"
                          placeholder="Describe este nivel específico"
                        ></textarea>
                      </div>

                      <div class="form-group full-width">
                        <label>Especificaciones Técnicas (JSON)</label>
                        <textarea 
                          v-model="nivel.especificaciones" 
                          rows="2"
                          autocomplete="off"
                          placeholder='{"color": "negro", "memoria": "256GB"}'
                        ></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="form-actions">
              <button type="button" @click="closeModals" class="btn btn-outline">
                Cancelar
              </button>
              <button type="submit" class="btn btn-primary" :disabled="formLoading">
                <i v-if="formLoading" class="fas fa-spinner fa-spin"></i>
                {{ showEditModal ? 'Actualizar' : 'Crear' }} Rifa
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    <div v-if="showDeleteModal" class="modal-overlay" @click="closeModals">
      <div class="modal delete-modal" @click.stop>
        <div class="modal-header">
          <h2>Confirmar Eliminación</h2>
          <button class="close-btn" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-content">
          <div class="delete-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p>¿Estás seguro de que deseas eliminar la rifa "<strong>{{ rifaToDelete?.titulo }}</strong>"?</p>
            <p class="warning-text">Esta acción no se puede deshacer.</p>
          </div>
          
          <div class="form-actions">
            <button @click="closeModals" class="btn btn-outline">
              Cancelar
            </button>
            <button @click="confirmDelete" class="btn btn-danger" :disabled="deleteLoading">
              <i v-if="deleteLoading" class="fas fa-spinner fa-spin"></i>
              Eliminar Rifa
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import ImageSelector from '@/components/common/ImageSelector.vue'
import { adminRifaService } from '@/services/adminRifaService'
import { showNotification } from '@/utils/helpers'

export default {
  name: 'AdminRifas',
  components: {
    AdminHeader,
    ImageSelector
  },
  setup() {
    // Estados reactivos
    const loading = ref(false)
    const error = ref(null)
    const rifas = ref([])
    const estadisticas = ref({})
    const categorias = ref([])
    
    // Filtros y búsqueda
    const searchQuery = ref('')
    const statusFilter = ref('')
    const sortBy = ref('fecha')
    
    // Modales
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showDeleteModal = ref(false)
    const formLoading = ref(false)
    const deleteLoading = ref(false)
    
    // Datos para edición/eliminación
    const editingRifa = ref(null)
    const rifaToDelete = ref(null)
    
    // Formulario de rifa
    const rifaForm = reactive({
      titulo: '',
      codigo_unico: '',
      descripcion: '',
      precio_boleto: 2.00,
      categoria_id: '',
      boletos_minimos: 100,
      boletos_maximos: null,
      max_boletos_por_persona: 10,
      fecha_inicio: '',
      fecha_fin: '',
      fecha_sorteo: '',
      tipo: 'actual',
      estado: 'borrador',
      orden: 1,
      es_destacada: false,
      imagen_principal: '',
      terminos_condiciones: '',
      notas_admin: '',
      premios: [
        {
          codigo: 'p1',
          titulo: '',
          descripcion: '',
          imagen_principal: '',
          estado: 'bloqueado',
          orden: 1,
          notas_admin: '',
          niveles: [
            {
              codigo: 'n1',
              titulo: '',
              descripcion: '',
              tickets_necesarios: 100,
              valor_aproximado: 0,
              imagen: '',
              es_actual: false,
              especificaciones: '',
              orden: 1
            }
          ]
        }
      ]
    })
    
    // Watchers para actualizar códigos automáticamente
    const openCreateModal = () => {
      resetForm()
      showCreateModal.value = true
    }
    
    // Computed properties
    const filteredRifas = computed(() => {
      let result = rifas.value
      
      // Filtrar por búsqueda
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(rifa => 
          rifa.titulo?.toLowerCase().includes(query) ||
          rifa.codigo?.toLowerCase().includes(query) ||
          rifa.descripcion?.toLowerCase().includes(query)
        )
      }
      
      // Filtrar por estado
      if (statusFilter.value) {
        result = result.filter(rifa => rifa.estado === statusFilter.value)
      }
      
      // Ordenar
      result.sort((a, b) => {
        switch (sortBy.value) {
          case 'nombre':
            return a.titulo?.localeCompare(b.titulo) || 0
          case 'precio':
            return (a.precio_boleto || 0) - (b.precio_boleto || 0)
          case 'vendidos':
            return (b.boletos_vendidos || 0) - (a.boletos_vendidos || 0)
          case 'fecha':
          default:
            return new Date(b.created_at || 0) - new Date(a.created_at || 0)
        }
      })
      
      return result
    })
    
    // Métodos principales
    const loadRifas = async () => {
      try {
        loading.value = true
        error.value = null
        
        const data = await adminRifaService.getAllRifas()
        rifas.value = data || []
        
        // Cargar estadísticas
        const stats = await adminRifaService.getEstadisticas()
        estadisticas.value = stats || {}
        
        console.log('Rifas cargadas:', rifas.value)
        
      } catch (err) {
        console.error('Error al cargar rifas:', err)
        error.value = err.message || 'Error al cargar las rifas'
        showNotification('Error al cargar las rifas', 'error')
      } finally {
        loading.value = false
      }
    }
    
    const loadCategorias = async () => {
      try {
        // Cargar categorías desde la API
        const response = await fetch('/api/v1/categorias')
        if (response.ok) {
          const data = await response.json()
          categorias.value = data.data || []
        } else {
          // Si no hay API de categorías, usar datos por defecto
          categorias.value = [
            { id: 1, nombre: 'Tecnología' },
            { id: 2, nombre: 'Electrodomésticos' },
            { id: 3, nombre: 'Moda' },
            { id: 4, nombre: 'Hogar' },
            { id: 5, nombre: 'Deportes' },
            { id: 6, nombre: 'Entretenimiento' }
          ]
        }
        console.log('Categorías cargadas:', categorias.value)
      } catch (err) {
        console.error('Error al cargar categorías:', err)
        // Usar categorías por defecto en caso de error
        categorias.value = [
          { id: 1, nombre: 'Tecnología' },
          { id: 2, nombre: 'Electrodomésticos' },
          { id: 3, nombre: 'Moda' },
          { id: 4, nombre: 'Hogar' },
          { id: 5, nombre: 'Deportes' },
          { id: 6, nombre: 'Entretenimiento' }
        ]
      }
    }
    
    const resetForm = () => {
      const newCodigoUnico = generateCodigoUnico()
      Object.assign(rifaForm, {
        titulo: '',
        codigo_unico: newCodigoUnico,
        descripcion: '',
        precio_boleto: 2.00,
        categoria_id: '',
        boletos_minimos: 100,
        boletos_maximos: null,
        max_boletos_por_persona: 10,
        fecha_inicio: '',
        fecha_fin: '',
        fecha_sorteo: '',
        tipo: 'actual',
        estado: 'borrador',
        orden: 1,
        es_destacada: false,
        imagen_principal: '',
        terminos_condiciones: '',
        notas_admin: '',
        premios: [
          {
            codigo: 'p1',
            titulo: '',
            descripcion: '',
            imagen_principal: '',
            estado: 'bloqueado',
            orden: 1,
            notas_admin: '',
            niveles: [
              {
                codigo: 'n1',
                titulo: '',
                descripcion: '',
                tickets_necesarios: 100,
                valor_aproximado: 0,
                imagen: '',
                es_actual: false,
                especificaciones: '',
                orden: 1
              }
            ]
          }
        ]
      })
    }
    
    // Función para generar código único automático
    const generateCodigoUnico = () => {
      const totalRifas = rifas.value.length + 1
      return `RIFA-${totalRifas.toString().padStart(3, '0')}`
    }
    
    // Funciones para manejar premios
    const addPremio = () => {
      const nuevoOrden = rifaForm.premios.length + 1
      rifaForm.premios.push({
        codigo: `p${nuevoOrden}`,
        titulo: '',
        descripcion: '',
        imagen_principal: '',
        estado: 'bloqueado',
        orden: nuevoOrden,
        notas_admin: '',
        niveles: [
          {
            codigo: `n1`,
            titulo: '',
            descripcion: '',
            tickets_necesarios: rifaForm.boletos_minimos || 100,
            valor_aproximado: 0,
            imagen: '',
            es_actual: false,
            especificaciones: '',
            orden: 1
          }
        ]
      })
    }
    
    const removePremio = (index) => {
      if (rifaForm.premios.length > 1) {
        rifaForm.premios.splice(index, 1)
        // Reordenar premios
        rifaForm.premios.forEach((premio, idx) => {
          premio.orden = idx + 1
          premio.codigo = `p${idx + 1}`
        })
      }
    }
    
    // Funciones para manejar niveles
    const addNivel = (premioIndex) => {
      const premio = rifaForm.premios[premioIndex]
      const nuevoOrden = premio.niveles.length + 1
      const ticketsBase = rifaForm.boletos_minimos || 100
      const ticketsNecesarios = ticketsBase * nuevoOrden
      
      premio.niveles.push({
        codigo: `n${nuevoOrden}`,
        titulo: '',
        descripcion: '',
        tickets_necesarios: ticketsNecesarios,
        valor_aproximado: 0,
        imagen: '',
        es_actual: false,
        especificaciones: '',
        orden: nuevoOrden
      })
    }
    
    const removeNivel = (premioIndex, nivelIndex) => {
      const premio = rifaForm.premios[premioIndex]
      if (premio.niveles.length > 1) {
        premio.niveles.splice(nivelIndex, 1)
        // Reordenar niveles
        premio.niveles.forEach((nivel, idx) => {
          nivel.orden = idx + 1
          nivel.codigo = `n${idx + 1}`
        })
      }
    }
    
    const saveRifa = async () => {
      try {
        formLoading.value = true
        
        // Validaciones básicas
        if (!rifaForm.titulo || !rifaForm.codigo_unico) {
          showNotification('Título y código único son obligatorios', 'error')
          return
        }
        
        if (rifaForm.fecha_fin <= rifaForm.fecha_inicio) {
          showNotification('La fecha de fin debe ser posterior a la fecha de inicio', 'error')
          return
        }
        
        // Validar premios
        if (!rifaForm.premios || rifaForm.premios.length === 0) {
          showNotification('Debe agregar al menos un premio', 'error')
          return
        }
        
        // Validar cada premio
        for (let i = 0; i < rifaForm.premios.length; i++) {
          const premio = rifaForm.premios[i]
          
          if (!premio.titulo) {
            showNotification(`El título del premio ${i + 1} es obligatorio`, 'error')
            return
          }
          
          if (!premio.niveles || premio.niveles.length === 0) {
            showNotification(`El premio ${i + 1} debe tener al menos un nivel`, 'error')
            return
          }
          
          // Validar cada nivel
          for (let j = 0; j < premio.niveles.length; j++) {
            const nivel = premio.niveles[j]
            
            if (!nivel.titulo) {
              showNotification(`El título del nivel ${j + 1} del premio ${i + 1} es obligatorio`, 'error')
              return
            }
            
            if (!nivel.tickets_necesarios || nivel.tickets_necesarios < 1) {
              showNotification(`Los tickets necesarios del nivel ${j + 1} del premio ${i + 1} deben ser mayor a 0`, 'error')
              return
            }
          }
        }
        
        let result
        if (showEditModal.value) {
          // Actualizar rifa existente
          result = await adminRifaService.updateRifa(editingRifa.value.id, rifaForm)
          showNotification('Rifa actualizada exitosamente', 'success')
        } else {
          // Crear nueva rifa
          result = await adminRifaService.createRifa(rifaForm)
          showNotification('Rifa creada exitosamente', 'success')
        }
        
        console.log('Rifa guardada:', result)
        
        // Recargar lista
        await loadRifas()
        
        // Cerrar modal
        closeModals()
        
      } catch (err) {
        console.error('Error al guardar rifa:', err)
        showNotification(err.message || 'Error al guardar la rifa', 'error')
      } finally {
        formLoading.value = false
      }
    }
    
    const editRifa = (rifa) => {
      editingRifa.value = rifa
      
      // Llenar formulario con datos de la rifa
      Object.assign(rifaForm, {
        titulo: rifa.titulo || '',
        codigo_unico: rifa.codigo_unico || '',
        descripcion: rifa.descripcion || '',
        precio_boleto: rifa.precio_boleto || 2.00,
        categoria_id: rifa.categoria_id || '',
        boletos_minimos: rifa.boletos_minimos || 100,
        boletos_maximos: rifa.boletos_maximos || null,
        max_boletos_por_persona: rifa.max_boletos_por_persona || 10,
        fecha_inicio: rifa.fecha_inicio ? formatDateForInput(rifa.fecha_inicio) : '',
        fecha_fin: rifa.fecha_fin ? formatDateForInput(rifa.fecha_fin) : '',
        fecha_sorteo: rifa.fecha_sorteo ? formatDateTimeForInput(rifa.fecha_sorteo) : '',
        tipo: rifa.tipo || 'actual',
        estado: rifa.estado || 'borrador',
        orden: rifa.orden || 1,
        es_destacada: rifa.es_destacada || false,
        imagen_principal: rifa.imagen_principal || '',
        terminos_condiciones: rifa.terminos_condiciones || '',
        notas_admin: rifa.notas_admin || '',
        // Cargar premios y niveles si existen
        premios: rifa.premios?.length > 0 ? rifa.premios.map((premio, premioIndex) => ({
          codigo: premio.codigo || `p${premioIndex + 1}`,
          titulo: premio.titulo || '',
          descripcion: premio.descripcion || '',
          imagen_principal: premio.imagen_principal || '',
          estado: premio.estado || 'bloqueado',
          orden: premio.orden || (premioIndex + 1),
          notas_admin: premio.notas_admin || '',
          niveles: premio.niveles?.length > 0 ? premio.niveles.map((nivel, nivelIndex) => ({
            codigo: nivel.codigo || `n${nivelIndex + 1}`,
            titulo: nivel.titulo || '',
            descripcion: nivel.descripcion || '',
            tickets_necesarios: nivel.tickets_necesarios || 100,
            valor_aproximado: nivel.valor_aproximado || 0,
            imagen: nivel.imagen || '',
            es_actual: nivel.es_actual || false,
            especificaciones: nivel.especificaciones || '',
            orden: nivel.orden || (nivelIndex + 1)
          })) : [
            {
              codigo: 'n1',
              titulo: '',
              descripcion: '',
              tickets_necesarios: rifaForm.boletos_minimos || 100,
              valor_aproximado: 0,
              imagen: '',
              es_actual: false,
              especificaciones: '',
              orden: 1
            }
          ]
        })) : [
          {
            codigo: 'p1',
            titulo: '',
            descripcion: '',
            imagen_principal: '',
            estado: 'bloqueado',
            orden: 1,
            notas_admin: '',
            niveles: [
              {
                codigo: 'n1',
                titulo: '',
                descripcion: '',
                tickets_necesarios: rifaForm.boletos_minimos || 100,
                valor_aproximado: 0,
                imagen: '',
                es_actual: false,
                especificaciones: '',
                orden: 1
              }
            ]
          }
        ]
      })
      
      showEditModal.value = true
    }
    
    const deleteRifa = (rifa) => {
      rifaToDelete.value = rifa
      showDeleteModal.value = true
    }
    
    const confirmDelete = async () => {
      if (!rifaToDelete.value) return
      
      try {
        deleteLoading.value = true
        
        await adminRifaService.deleteRifa(rifaToDelete.value.id)
        showNotification('Rifa eliminada exitosamente', 'success')
        
        // Recargar lista
        await loadRifas()
        
        // Cerrar modal
        closeModals()
        
      } catch (err) {
        console.error('Error al eliminar rifa:', err)
        showNotification(err.message || 'Error al eliminar la rifa', 'error')
      } finally {
        deleteLoading.value = false
      }
    }
    
    const toggleEstado = async (rifa) => {
      try {
        const nuevoEstado = rifa.estado === 'activa' ? 'pausada' : 'activa'
        
        await adminRifaService.changeEstado(rifa.id, nuevoEstado)
        showNotification(`Rifa ${nuevoEstado === 'activa' ? 'activada' : 'pausada'} exitosamente`, 'success')
        
        // Recargar lista
        await loadRifas()
        
      } catch (err) {
        console.error('Error al cambiar estado:', err)
        showNotification(err.message || 'Error al cambiar el estado', 'error')
      }
    }
    
    const viewRifa = (rifa) => {
      // Redirigir a la vista pública de la rifa
      window.open(`/rifa/${rifa.codigo_unico}`, '_blank')
    }
    
    const exportData = async () => {
      try {
        await adminRifaService.exportRifas({
          estado: statusFilter.value,
          busqueda: searchQuery.value
        })
        showNotification('Exportación iniciada', 'success')
      } catch (err) {
        console.error('Error al exportar:', err)
        showNotification('Error al exportar los datos', 'error')
      }
    }
    
    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      showDeleteModal.value = false
      editingRifa.value = null
      rifaToDelete.value = null
      resetForm()
    }
    
    const filterRifas = () => {
      // El filtrado se maneja automáticamente a través de computed
      console.log('Filtrando rifas...')
    }
    
    const sortRifas = () => {
      // El ordenamiento se maneja automáticamente a través de computed
      console.log('Ordenando rifas...')
    }
    
    // Métodos auxiliares
    const formatDate = (dateString) => {
      if (!dateString) return 'No definida'
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }
    
    const formatDateForInput = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toISOString().slice(0, 10) // formato YYYY-MM-DD
    }
    
    const formatDateTimeForInput = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toISOString().slice(0, 16) // formato YYYY-MM-DDTHH:MM
    }
    
    const formatMoney = (amount) => {
      if (typeof amount !== 'number') return '0'
      return amount.toLocaleString('es-PE', { minimumFractionDigits: 2 })
    }
    
    const formatStatus = (status) => {
      const statusMap = {
        borrador: 'Borrador',
        activa: 'Activa',
        pausada: 'Pausada',
        finalizada: 'Finalizada',
        cancelada: 'Cancelada'
      }
      return statusMap[status] || status
    }
    
    const getStatusClass = (status) => {
      return `status-${status}`
    }
    
    const getProgressPercentage = (rifa) => {
      const vendidos = rifa.boletos_vendidos || 0
      const total = rifa.boletos_minimos || 1
      return Math.min((vendidos / total) * 100, 100)
    }
    
    const getIngresos = (rifa) => {
      const vendidos = rifa.boletos_vendidos || 0
      const precio = rifa.precio_boleto || 0
      return vendidos * precio
    }
    
    const handleImageError = (event) => {
      event.target.src = 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?w=100&h=100&fit=crop'
    }
    
    // Lifecycle
    onMounted(() => {
      loadRifas()
      loadCategorias()
    })
    
    return {
      // Estados
      loading,
      error,
      rifas,
      estadisticas,
      categorias,
      
      // Filtros
      searchQuery,
      statusFilter,
      sortBy,
      filteredRifas,
      
      // Modales
      showCreateModal,
      showEditModal,
      showDeleteModal,
      formLoading,
      deleteLoading,
      
      // Datos
      editingRifa,
      rifaToDelete,
      rifaForm,
      
      // Métodos
      loadRifas,
      loadCategorias,
      saveRifa,
      editRifa,
      deleteRifa,
      confirmDelete,
      toggleEstado,
      viewRifa,
      exportData,
      closeModals,
      filterRifas,
      sortRifas,
      openCreateModal,
      generateCodigoUnico,
      
      // Funciones para premios y niveles
      addPremio,
      removePremio,
      addNivel,
      removeNivel,
      
      // Helpers
      formatDate,
      formatDateForInput,
      formatDateTimeForInput,
      formatMoney,
      formatStatus,
      getStatusClass,
      getProgressPercentage,
      getIngresos,
      handleImageError
    }
  }
}
</script>

<style scoped>
/* Variables CSS */
:root {
  --primary-purple: #8b5cf6;
  --primary-blue: #3b82f6;
  --success-green: #10b981;
  --warning-orange: #f59e0b;
  --danger-red: #ef4444;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;
  --white: #ffffff;
  --border-radius: 0.5rem;
  --border-radius-lg: 0.75rem;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Layout principal */
.admin-rifas {
  min-height: 100vh;
  background: var(--gray-50);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

/* Hero Section */
.admin-hero {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  color: var(--white);
  padding: 3rem 0;
}

.admin-hero .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.hero-content h1 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.hero-content p {
  font-size: 1.125rem;
  opacity: 0.9;
  margin: 0;
}

.hero-actions {
  display: flex;
  gap: 1rem;
}

/* Loading y Error states */
.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  color: var(--gray-600);
}

.loading-state i,
.error-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.loading-state i {
  color: var(--primary-purple);
}

.error-state i {
  color: var(--danger-red);
}

/* Filters Section */
.filters-section {
  padding: 2rem 0;
}

.filters-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow);
  padding: 1.5rem;
}

.filters-row {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 1.5rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.search-box {
  position: relative;
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
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.filter-group {
  display: flex;
  gap: 1rem;
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  background: var(--white);
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.5rem;
}

.quick-stat {
  text-align: center;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: var(--border-radius);
}

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary-purple);
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--gray-600);
}

/* Table Section */
.rifas-table-section {
  padding-bottom: 3rem;
}

.table-card {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow);
  overflow: hidden;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.table-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: var(--gray-500);
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--gray-300);
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  color: var(--gray-700);
}

.empty-state p {
  margin: 0 0 1.5rem 0;
}

/* Table */
.table-container {
  overflow-x: auto;
}

.rifas-table {
  width: 100%;
  border-collapse: collapse;
}

.rifas-table th {
  background: var(--gray-50);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  color: var(--gray-700);
  font-size: 0.875rem;
  border-bottom: 1px solid var(--gray-200);
}

.rifas-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--gray-100);
  vertical-align: middle;
}

.table-row:hover {
  background: var(--gray-50);
}

/* Rifa Info */
.rifa-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.rifa-thumb {
  width: 50px;
  height: 50px;
  border-radius: var(--border-radius);
  object-fit: cover;
  border: 2px solid var(--gray-200);
}

.rifa-name {
  font-weight: 600;
  color: var(--gray-800);
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
}

.rifa-id {
  font-size: 0.75rem;
  color: var(--gray-500);
  margin: 0;
}

/* Status Badge */
.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-borrador {
  background: #e0e7ff;
  color: #3730a3;
}

.status-activa {
  background: #dcfce7;
  color: #166534;
}

.status-pausada {
  background: #fef3c7;
  color: #92400e;
}

.status-finalizada {
  background: var(--gray-100);
  color: var(--gray-700);
}

.status-cancelada {
  background: #fee2e2;
  color: #991b1b;
}

/* Price */
.price {
  font-weight: 600;
  color: var(--gray-800);
}

/* Progress Cell */
.progress-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.mini-progress {
  width: 60px;
  height: 6px;
  background: var(--gray-200);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--primary-purple);
  transition: width 0.3s ease;
}

/* Actions Cell */
.actions-cell {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  padding: 0.5rem;
  border: none;
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
}

.action-btn.edit {
  background: #dbeafe;
  color: var(--primary-blue);
}

.action-btn.edit:hover {
  background: #bfdbfe;
}

.action-btn.view {
  background: #dcfce7;
  color: var(--success-green);
}

.action-btn.view:hover {
  background: #bbf7d0;
}

.action-btn.toggle {
  background: #fef3c7;
  color: var(--warning-orange);
}

.action-btn.toggle:hover {
  background: #fde68a;
}

.action-btn.delete {
  background: #fee2e2;
  color: var(--danger-red);
}

.action-btn.delete:hover {
  background: #fecaca;
}

/* Modales */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-lg);
  max-width: 1100px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--gray-200);
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.close-btn {
  padding: 0.5rem;
  border: none;
  background: none;
  color: var(--gray-400);
  cursor: pointer;
  border-radius: var(--border-radius);
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--gray-100);
  color: var(--gray-600);
}

.modal-content {
  padding: 1.5rem;
}

/* Formulario */
.rifa-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  color: var(--gray-700);
  font-size: 0.875rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-group input:disabled {
  background: var(--gray-100);
  color: var(--gray-500);
  cursor: not-allowed;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  border-top: 1px solid var(--gray-200);
  padding-top: 1.5rem;
}

/* Delete Modal */
.delete-modal {
  max-width: 400px;
}

.delete-warning {
  text-align: center;
  padding: 1rem 0;
}

.delete-warning i {
  font-size: 3rem;
  color: var(--danger-red);
  margin-bottom: 1rem;
}

.delete-warning p {
  color: var(--gray-700);
  margin: 0 0 0.5rem 0;
}

.warning-text {
  color: var(--danger-red);
  font-size: 0.875rem;
  font-weight: 500;
}

/* Botones */
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--border-radius);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  font-size: 0.875rem;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--primary-purple);
  color: var(--white);
}

.btn-primary:hover:not(:disabled) {
  background: #7c3aed;
}

.btn-outline {
  background: transparent;
  color: var(--gray-600);
  border: 1px solid var(--gray-300);
}

.btn-outline:hover:not(:disabled) {
  background: var(--gray-50);
  border-color: var(--gray-400);
}

.btn-danger {
  background: var(--danger-red);
  color: var(--white);
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-ghost {
  background: transparent;
  color: var(--gray-600);
}

.btn-ghost:hover:not(:disabled) {
  background: var(--gray-100);
}

.btn-lg {
  padding: 1rem 2rem;
  font-size: 1rem;
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
}

/* Premios y Niveles Sections */
.premios-section {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--gray-200);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--gray-800);
  margin: 0;
}

.premio-card {
  background: var(--gray-50);
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius-lg);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.premio-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.premio-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--primary-purple);
  margin: 0;
}

.btn-remove {
  padding: 0.5rem;
  border: none;
  background: var(--danger-red);
  color: var(--white);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove:hover {
  background: #dc2626;
}

.premio-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.niveles-section {
  background: var(--white);
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  padding: 1rem;
}

.niveles-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--gray-100);
}

.niveles-header h5 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  margin: 0;
}

.nivel-card {
  background: var(--gray-50);
  border: 1px solid var(--gray-200);
  border-radius: var(--border-radius);
  padding: 1rem;
  margin-bottom: 1rem;
}

.nivel-card:last-child {
  margin-bottom: 0;
}

.nivel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.nivel-header span {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
}

.btn-remove-small {
  padding: 0.25rem;
  border: none;
  background: var(--danger-red);
  color: var(--white);
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  font-size: 0.75rem;
}

.btn-remove-small:hover {
  background: #dc2626;
}

.nivel-fields {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.nivel-fields .form-group {
  margin-bottom: 0;
}

.nivel-fields .form-group.full-width {
  grid-column: 1 / -1;
}

.nivel-fields label {
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.nivel-fields input,
.nivel-fields textarea {
  padding: 0.5rem;
  font-size: 0.75rem;
}

.nivel-fields textarea {
  resize: vertical;
  min-height: 60px;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-hero .container {
    flex-direction: column;
    text-align: center;
  }

  .premio-fields {
    grid-template-columns: 1fr;
  }

  .nivel-fields {
    grid-template-columns: 1fr;
  }

  .hero-actions {
    flex-direction: column;
    width: 100%;
  }

  .filters-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .filter-group {
    flex-direction: column;
  }

  .quick-stats {
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .rifas-table {
    font-size: 0.75rem;
  }

  .rifas-table th,
  .rifas-table td {
    padding: 0.5rem;
  }

  .rifa-thumb {
    width: 40px;
    height: 40px;
  }

  .actions-cell {
    flex-direction: column;
    gap: 0.25rem;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 0.5rem;
  }

  .hero-content h1 {
    font-size: 1.5rem;
  }

  .hero-content p {
    font-size: 1rem;
  }

  .modal {
    margin: 0.5rem;
    max-width: calc(100vw - 1rem);
  }
}
</style>
