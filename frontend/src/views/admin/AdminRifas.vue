<template>
  <div class="admin-page">
    <AdminHeader />
    
    <!-- Hero Section -->
    <section class="admin-hero">
      <div class="admin-container">
        <div class="admin-hero-content">
          <h1>
            <i class="fas fa-ticket-alt"></i>
            Gestión de Rifas
          </h1>
          <p>
            Administra todas las rifas del sistema
          </p>
        </div>
        
        <div class="admin-hero-actions">
          <button @click="openCreateModal" class="admin-btn admin-btn-primary admin-btn-lg">
            <i class="fas fa-plus"></i>
            Nueva Rifa
          </button>
          <button @click="exportData" class="admin-btn admin-btn-outline admin-btn-lg">
            <i class="fas fa-download"></i>
            Exportar
          </button>
        </div>
      </div>
    </section>

    <!-- Loading state -->
    <div v-if="loading" class="admin-loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Cargando rifas...</p>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="admin-error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button class="admin-btn admin-btn-primary" @click="loadRifas">Reintentar</button>
    </div>

    <!-- Filters and Stats -->
    <section v-else class="admin-filters-section">
      <div class="admin-container">
        <div class="admin-filters-card">
          <div class="admin-filters-row">
            <div class="admin-search-box">
              <i class="fas fa-search"></i>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar rifas..."
                class="admin-search-input"
                @input="filterRifas"
              />
            </div>
            
            <div class="admin-filter-group">
              <select v-model="statusFilter" class="admin-filter-select" @change="filterRifas">
                <option value="">Todos los estados</option>
                <option value="borrador">Borradores</option>
                <option value="activa">Activas</option>
                <option value="bloqueada">Bloqueadas</option>
                <option value="pausada">Pausadas</option>
                <option value="finalizada">Finalizadas</option>
                <option value="cancelada">Canceladas</option>
              </select>
              
              <select v-model="sortBy" class="admin-filter-select" @change="sortRifas">
                <option value="fecha">Ordenar por fecha</option>
                <option value="nombre">Ordenar por nombre</option>
                <option value="precio">Ordenar por precio</option>
                <option value="vendidos">Tickets vendidos</option>
              </select>
            </div>
          </div>
          
          <div class="admin-stats">
            <div class="admin-stat">
              <span class="admin-stat-number">{{ estadisticas.rifas_activas || 0 }}</span>
              <span class="admin-stat-label">Activas</span>
            </div>
            <div class="admin-stat">
              <span class="admin-stat-number">S/ {{ formatMoney(estadisticas.total_ingresos || 0) }}</span>
              <span class="admin-stat-label">Total Ingresos</span>
            </div>
            <div class="admin-stat">
              <span class="admin-stat-number">{{ estadisticas.total_tickets || 0 }}</span>
              <span class="admin-stat-label">Tickets Vendidos</span>
            </div>
            <div class="admin-stat">
              <span class="admin-stat-number">{{ rifas.length || 0 }}</span>
              <span class="admin-stat-label">Total Rifas</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Rifas Table -->
    <section class="admin-table-section">
      <div class="admin-container">
        <div class="admin-card">
          <div class="admin-card-header">
            <h3>Lista de Rifas ({{ filteredRifas.length }})</h3>
            <div class="admin-card-actions">
              <button @click="loadRifas" class="admin-btn admin-btn-ghost admin-btn-sm">
                <i class="fas fa-sync-alt"></i>
                Actualizar
              </button>
            </div>
          </div>
          
          <div v-if="filteredRifas.length === 0 && !loading" class="admin-empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No hay rifas</h3>
            <p>{{ searchQuery ? 'No se encontraron rifas que coincidan con tu búsqueda' : 'Aún no has creado ninguna rifa' }}</p>
            <button v-if="!searchQuery" @click="openCreateModal" class="admin-btn admin-btn-primary">
              <i class="fas fa-plus"></i>
              Crear primera rifa
            </button>
          </div>
          
          <div v-else class="admin-table-container">
            <table class="admin-table">
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
                <tr v-for="rifa in filteredRifas" :key="rifa.id" class="admin-table-row">
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
                    <span class="admin-status-badge" :class="getStatusClass(rifa.estado)">
                      {{ formatStatus(rifa.estado) }}
                    </span>
                  </td>
                  <td class="price">S/ {{ formatMoney(rifa.precio_boleto) }}</td>
                  <td>{{ rifa.boletos_minimos || 0 }} / {{ rifa.boletos_maximos || '∞' }}</td>
                  <td>
                    <div class="admin-progress-cell">
                      <span>{{ rifa.boletos_vendidos || 0 }}</span>
                      <div class="admin-mini-progress">
                        <div 
                          class="admin-progress-fill" 
                          :style="{ width: getProgressPercentage(rifa) + '%' }"
                        ></div>
                      </div>
                    </div>
                  </td>
                  <td class="price">S/ {{ formatMoney(getIngresos(rifa)) }}</td>
                  <td>{{ formatDate(rifa.fecha_fin) }}</td>
                  <td>
                    <div class="admin-actions-cell">
                      <button @click="editRifa(rifa)" class="admin-action-btn edit" title="Editar">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button @click="viewRifa(rifa)" class="admin-action-btn view" title="Ver detalles">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button @click="duplicateRifa(rifa)" class="admin-action-btn duplicate" title="Duplicar rifa">
                        <i class="fas fa-copy"></i>
                      </button>
                      <button @click="toggleEstado(rifa)" class="admin-action-btn toggle" :title="rifa.estado === 'activa' ? 'Pausar' : 'Activar'">
                        <i :class="rifa.estado === 'activa' ? 'fas fa-pause' : 'fas fa-play'"></i>
                      </button>
                      <button @click="deleteRifa(rifa)" class="admin-action-btn delete" title="Eliminar">
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
    <div v-if="showCreateModal || showEditModal" class="admin-modal-overlay" @click="closeModals">
      <div class="admin-modal-content admin-modal-xl" @click.stop>
        <div class="admin-modal-header">
          <h2>{{ showEditModal ? 'Editar Rifa' : 'Nueva Rifa' }}</h2>
          <button class="admin-modal-close" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-modal-body">
          <form @submit.prevent="saveRifa" class="admin-form">
            <div class="admin-form-grid">
              <div class="admin-form-group">
                <label for="titulo">Título de la Rifa *</label>
                <input 
                  id="titulo"
                  v-model="rifaForm.titulo" 
                  type="text" 
                  class="admin-form-input"
                  required
                  autocomplete="off"
                  placeholder="Ej: iPhone 15 Pro Max"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="codigo_unico">Código Único *</label>
                <input 
                  id="codigo_unico"
                  v-model="rifaForm.codigo_unico" 
                  type="text" 
                  class="admin-form-input"
                  required
                  autocomplete="off"
                  placeholder="Ej: RIFA001"
                  :disabled="showEditModal"
                >
              </div>
              
              <div class="admin-form-group admin-form-full-width">
                <label for="descripcion">Descripción</label>
                <textarea 
                  id="descripcion"
                  v-model="rifaForm.descripcion" 
                  class="admin-form-textarea"
                  rows="3"
                  autocomplete="off"
                  placeholder="Describe la rifa y sus premios"
                ></textarea>
              </div>
              
              <div class="admin-form-group">
                <label for="precio_boleto">Precio por Boleto (S/) *</label>
                <input 
                  id="precio_boleto"
                  v-model="rifaForm.precio_boleto" 
                  type="number" 
                  class="admin-form-input"
                  step="0.01"
                  min="0.01"
                  required
                  autocomplete="off"
                  placeholder="2.00"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="categoria_id">Categoría</label>
                <select id="categoria_id" v-model="rifaForm.categoria_id" class="admin-form-select" autocomplete="off">
                  <option value="">Seleccionar categoría</option>
                  <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                    {{ categoria.nombre }}
                  </option>
                </select>
              </div>
              
              <div class="admin-form-group">
                <label for="boletos_minimos">Boletos Mínimos *</label>
                <input 
                  id="boletos_minimos"
                  v-model="rifaForm.boletos_minimos" 
                  type="number" 
                  class="admin-form-input"
                  min="1"
                  required
                  autocomplete="off"
                  placeholder="100"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="boletos_maximos">Boletos Máximos</label>
                <input 
                  id="boletos_maximos"
                  v-model="rifaForm.boletos_maximos" 
                  type="number" 
                  class="admin-form-input"
                  min="1"
                  autocomplete="off"
                  placeholder="500"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="max_boletos_por_persona">Máx. Boletos por Persona</label>
                <input 
                  id="max_boletos_por_persona"
                  v-model="rifaForm.max_boletos_por_persona" 
                  type="number" 
                  class="admin-form-input"
                  min="1"
                  autocomplete="off"
                  placeholder="10"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="fecha_inicio">Fecha de Inicio *</label>
                <input 
                  id="fecha_inicio"
                  v-model="rifaForm.fecha_inicio" 
                  type="date" 
                  class="admin-form-input"
                  required
                  autocomplete="off"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="fecha_fin">Fecha de Fin *</label>
                <input 
                  id="fecha_fin"
                  v-model="rifaForm.fecha_fin" 
                  type="date" 
                  class="admin-form-input"
                  required
                  autocomplete="off"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="fecha_sorteo">Fecha de Sorteo</label>
                <input 
                  id="fecha_sorteo"
                  v-model="rifaForm.fecha_sorteo" 
                  type="datetime-local" 
                  class="admin-form-input"
                  autocomplete="off"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="tipo">Tipo de Rifa *</label>
                <select id="tipo" v-model="rifaForm.tipo" class="admin-form-select" autocomplete="off" required>
                  <option value="actual">Actual (En venta)</option>
                  <option value="futura">Futura (Programada)</option>
                </select>
              </div>
              
              <div class="admin-form-group">
                <label for="estado">Estado *</label>
                <select id="estado" v-model="rifaForm.estado" class="admin-form-select" autocomplete="off" required>
                  <option value="borrador">Borrador</option>
                  <option value="activa">Activa</option>
                  <option value="bloqueada">Bloqueada</option>
                  <option value="pausada">Pausada</option>
                  <option value="finalizada">Finalizada</option>
                  <option value="cancelada">Cancelada</option>
                </select>
              </div>
              
              <div class="admin-form-group">
                <label for="orden">Orden (para rifas futuras)</label>
                <input 
                  id="orden"
                  v-model="rifaForm.orden" 
                  type="number" 
                  class="admin-form-input"
                  min="1"
                  autocomplete="off"
                  placeholder="1"
                >
              </div>
              
              <div class="admin-form-group">
                <label for="es_destacada">¿Es destacada?</label>
                <select id="es_destacada" v-model="rifaForm.es_destacada" class="admin-form-select" autocomplete="off">
                  <option value="false">No</option>
                  <option value="true">Sí</option>
                </select>
              </div>
              
              <div class="admin-form-group admin-form-full-width">
                <ImageSelector
                  v-model="rifaForm.imagen_principal"
                  label="Imagen Principal de la Rifa"
                  placeholder="https://ejemplo.com/imagen.jpg"
                  upload-endpoint="/api/v1/upload/rifa-image"
                />
              </div>

              <div class="admin-form-group admin-form-full-width">
                <label>Galería de Medios (Imágenes Adicionales)</label>
                <div class="admin-media-gallery-section">
                  <div class="admin-current-images" v-if="rifaForm.media_gallery && rifaForm.media_gallery.length > 0">
                    <div class="admin-gallery-grid">
                      <div v-for="(image, index) in rifaForm.media_gallery" :key="index" class="admin-gallery-item">
                        <img :src="image" :alt="`Imagen ${index + 1}`" class="admin-gallery-thumb" />
                        <button type="button" @click="removeGalleryImage(index)" class="admin-remove-gallery-btn">
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="admin-add-images-section">
                    <ImageSelector
                      v-model="newGalleryImage"
                      label="Agregar Nueva Imagen"
                      placeholder="https://ejemplo.com/imagen.jpg"
                      upload-endpoint="/api/v1/upload/rifa-gallery"
                      @change="addGalleryImage"
                    />
                  </div>
                </div>
              </div>
              
              <div class="admin-form-group admin-form-full-width">
                <label for="terminos_condiciones">Términos y Condiciones</label>
                <textarea 
                  id="terminos_condiciones"
                  v-model="rifaForm.terminos_condiciones" 
                  class="admin-form-textarea"
                  rows="3"
                  autocomplete="off"
                  placeholder="Términos y condiciones específicos de esta rifa"
                ></textarea>
              </div>
              
              <div class="admin-form-group admin-form-full-width">
                <label for="notas_admin">Notas del Administrador</label>
                <textarea 
                  id="notas_admin"
                  v-model="rifaForm.notas_admin" 
                  class="admin-form-textarea"
                  rows="2"
                  autocomplete="off"
                  placeholder="Notas internas para el administrador"
                ></textarea>
              </div>
            </div>

            <!-- Sección de Premios -->
            <div class="admin-premios-section">
              <div class="admin-section-header">
                <h3>Premios y Niveles</h3>
                <button type="button" @click="addPremio" class="admin-btn admin-btn-outline admin-btn-sm">
                  <i class="fas fa-plus"></i>
                  Agregar Premio
                </button>
              </div>

              <div v-for="(premio, premioIndex) in rifaForm.premios" :key="premioIndex" class="admin-premio-card">
                <div class="admin-premio-header">
                  <h4>Premio {{ premioIndex + 1 }}</h4>
                  <button 
                    v-if="rifaForm.premios.length > 1"
                    type="button" 
                    @click="removePremio(premioIndex)" 
                    class="admin-btn-remove"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <div class="admin-premio-fields">
                  <div class="admin-form-group">
                    <label>Código del Premio *</label>
                    <input 
                      v-model="premio.codigo" 
                      type="text" 
                      class="admin-form-input"
                      required
                      autocomplete="off"
                      placeholder="Ej: P1, P2, P3"
                      maxlength="10"
                    >
                  </div>

                  <div class="admin-form-group">
                    <label>Título del Premio *</label>
                    <input 
                      v-model="premio.titulo" 
                      type="text" 
                      class="admin-form-input"
                      required
                      autocomplete="off"
                      placeholder="Ej: AirPods Pro"
                    >
                  </div>

                  <div class="admin-form-group">
                    <ImageSelector
                      v-model="premio.imagen_principal"
                      label="Imagen del Premio"
                      placeholder="https://ejemplo.com/premio.jpg"
                      upload-endpoint="/api/v1/upload/premio-image"
                    />
                  </div>

                  <div class="admin-form-group">
                    <label>Estado del Premio</label>
                    <select v-model="premio.estado" class="admin-form-select" autocomplete="off">
                      <option value="bloqueado">Bloqueado</option>
                      <option value="activo">Activo</option>
                      <option value="completado">Completado</option>
                    </select>
                  </div>

                  <div class="admin-form-group admin-form-full-width">
                    <label>Descripción del Premio</label>
                    <textarea 
                      v-model="premio.descripcion" 
                      class="admin-form-textarea"
                      rows="2"
                      autocomplete="off"
                      placeholder="Describe este premio específico"
                    ></textarea>
                  </div>

                  <div class="admin-form-group admin-form-full-width">
                    <label>Notas del Administrador</label>
                    <textarea 
                      v-model="premio.notas_admin" 
                      class="admin-form-textarea"
                      rows="1"
                      autocomplete="off"
                      placeholder="Notas internas sobre este premio"
                    ></textarea>
                  </div>
                </div>

                <!-- Niveles del Premio -->
                <div class="admin-niveles-section">
                  <div class="admin-niveles-header">
                    <h5>Niveles del Premio</h5>
                    <button 
                      type="button" 
                      @click="addNivel(premioIndex)" 
                      class="admin-btn admin-btn-outline admin-btn-sm"
                    >
                      <i class="fas fa-plus"></i>
                      Agregar Nivel
                    </button>
                  </div>

                  <div v-for="(nivel, nivelIndex) in premio.niveles" :key="nivelIndex" class="admin-nivel-card">
                    <div class="admin-nivel-header">
                      <span>Nivel {{ nivelIndex + 1 }}</span>
                      <button 
                        v-if="premio.niveles.length > 1"
                        type="button" 
                        @click="removeNivel(premioIndex, nivelIndex)" 
                        class="admin-btn-remove-small"
                      >
                        <i class="fas fa-times"></i>
                      </button>
                    </div>

                    <div class="admin-nivel-fields">
                      <div class="admin-form-group">
                        <label>Código del Nivel *</label>
                        <input 
                          v-model="nivel.codigo" 
                          type="text" 
                          class="admin-form-input"
                          required
                          autocomplete="off"
                          placeholder="Ej: N1, N2, N3"
                          maxlength="10"
                        >
                      </div>

                      <div class="admin-form-group">
                        <label>Título del Nivel *</label>
                        <input 
                          v-model="nivel.titulo" 
                          type="text" 
                          class="admin-form-input"
                          required
                          autocomplete="off"
                          placeholder="Ej: Nivel Básico"
                        >
                      </div>

                      <div class="admin-form-group">
                        <label>Tickets Necesarios *</label>
                        <input 
                          v-model="nivel.tickets_necesarios" 
                          type="number" 
                          class="admin-form-input"
                          min="1"
                          required
                          autocomplete="off"
                          placeholder="100"
                        >
                      </div>

                      <div class="admin-form-group">
                        <label>Valor Aproximado (S/)</label>
                        <input 
                          v-model="nivel.valor_aproximado" 
                          type="number" 
                          class="admin-form-input"
                          min="0"
                          step="0.01"
                          autocomplete="off"
                          placeholder="0.00"
                        >
                      </div>

                      <div class="admin-form-group">
                        <ImageSelector
                          v-model="nivel.imagen"
                          label="Imagen del Nivel"
                          placeholder="https://ejemplo.com/nivel.jpg"
                          upload-endpoint="/api/v1/upload/nivel-image"
                        />
                      </div>

                      <div class="admin-form-group">
                        <label>¿Es el nivel actual?</label>
                        <select v-model="nivel.es_actual" class="admin-form-select" autocomplete="off">
                          <option value="false">No</option>
                          <option value="true">Sí</option>
                        </select>
                      </div>

                      <div class="admin-form-group admin-form-full-width">
                        <label>Descripción del Nivel</label>
                        <textarea 
                          v-model="nivel.descripcion" 
                          class="admin-form-textarea"
                          rows="2"
                          autocomplete="off"
                          placeholder="Describe este nivel específico"
                        ></textarea>
                      </div>

                      <div class="admin-form-group admin-form-full-width">
                        <label>Especificaciones Técnicas (JSON)</label>
                        <textarea 
                          v-model="nivel.especificaciones" 
                          class="admin-form-textarea"
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
            
            <div class="admin-form-actions">
              <button type="button" @click="closeModals" class="admin-btn admin-btn-outline">
                Cancelar
              </button>
              <button type="submit" class="admin-btn admin-btn-primary" :disabled="formLoading">
                <i v-if="formLoading" class="fas fa-spinner fa-spin"></i>
                {{ showEditModal ? 'Actualizar' : 'Crear' }} Rifa
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    <div v-if="showDeleteModal" class="admin-modal-overlay" @click="closeModals">
      <div class="admin-modal-content" @click.stop>
        <div class="admin-modal-header">
          <h2>Confirmar Eliminación</h2>
          <button class="admin-modal-close" @click="closeModals">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="admin-modal-body">
          <div class="admin-delete-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <p>¿Estás seguro de que deseas eliminar la rifa "<strong>{{ rifaToDelete?.titulo }}</strong>"?</p>
            <p class="admin-warning-text">Esta acción no se puede deshacer.</p>
          </div>
          
          <div class="admin-form-actions">
            <button @click="closeModals" class="admin-btn admin-btn-outline">
              Cancelar
            </button>
            <button @click="confirmDelete" class="admin-btn admin-btn-danger" :disabled="deleteLoading">
              <i v-if="deleteLoading" class="fas fa-spinner fa-spin"></i>
              Eliminar Rifa
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <AdminFooter />
  </div>
</template>

<script>
import { ref, computed, onMounted, reactive } from 'vue'
import AdminHeader from '@/components/admin/AdminHeader.vue'
import AdminFooter from '@/components/admin/AdminFooter.vue'
import ImageSelector from '@/components/common/ImageSelector.vue'
import { adminRifaService } from '@/services/adminRifaService'
import { showNotification } from '@/utils/helpers'

export default {
  name: 'AdminRifas',
  components: {
    AdminHeader,
    AdminFooter,
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
    
    // Variables para galería de medios
    const newGalleryImage = ref('')
    
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
      media_gallery: [],
      terminos_condiciones: '',
      notas_admin: '',
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
        media_gallery: [],
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
    
    // Funciones para manejar galería de medios
    const addGalleryImage = () => {
      if (newGalleryImage.value && newGalleryImage.value.trim()) {
        if (!rifaForm.media_gallery) {
          rifaForm.media_gallery = []
        }
        if (!rifaForm.media_gallery.includes(newGalleryImage.value)) {
          rifaForm.media_gallery.push(newGalleryImage.value)
          newGalleryImage.value = ''
        }
      }
    }
    
    const removeGalleryImage = (index) => {
      if (rifaForm.media_gallery && rifaForm.media_gallery.length > index) {
        rifaForm.media_gallery.splice(index, 1)
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
        media_gallery: rifa.media_gallery || [],
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
    
    const duplicateRifa = (rifa) => {
      // Llenar formulario con datos de la rifa a duplicar
      const newCodigoUnico = generateCodigoUnico()
      
      Object.assign(rifaForm, {
        titulo: `${rifa.titulo} (Copia)`,
        codigo_unico: newCodigoUnico,
        descripcion: rifa.descripcion || '',
        precio_boleto: rifa.precio_boleto || 2.00,
        categoria_id: rifa.categoria_id || '',
        boletos_minimos: rifa.boletos_minimos || 100,
        boletos_maximos: rifa.boletos_maximos || null,
        max_boletos_por_persona: rifa.max_boletos_por_persona || 10,
        fecha_inicio: '',
        fecha_fin: '',
        fecha_sorteo: '',
        tipo: rifa.tipo || 'actual',
        estado: 'borrador',
        orden: rifa.orden || 1,
        es_destacada: false,
        imagen_principal: rifa.imagen_principal || '',
        media_gallery: [...(rifa.media_gallery || [])],
        terminos_condiciones: rifa.terminos_condiciones || '',
        notas_admin: rifa.notas_admin || '',
        // Duplicar premios y niveles
        premios: rifa.premios?.length > 0 ? rifa.premios.map((premio, premioIndex) => ({
          codigo: premio.codigo || `p${premioIndex + 1}`,
          titulo: premio.titulo || '',
          descripcion: premio.descripcion || '',
          imagen_principal: premio.imagen_principal || '',
          estado: 'bloqueado',
          orden: premio.orden || (premioIndex + 1),
          notas_admin: premio.notas_admin || '',
          niveles: premio.niveles?.length > 0 ? premio.niveles.map((nivel, nivelIndex) => ({
            codigo: nivel.codigo || `n${nivelIndex + 1}`,
            titulo: nivel.titulo || '',
            descripcion: nivel.descripcion || '',
            tickets_necesarios: nivel.tickets_necesarios || 100,
            valor_aproximado: nivel.valor_aproximado || 0,
            imagen: nivel.imagen || '',
            es_actual: false,
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
      
      showCreateModal.value = true
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
      if (amount === null || amount === undefined) return '0.00'
      const numAmount = parseFloat(amount)
      if (isNaN(numAmount)) return '0.00'
      return numAmount.toLocaleString('es-PE', { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    }
    
    const formatStatus = (status) => {
      const statusMap = {
        borrador: 'Borrador',
        activa: 'Activa',
        bloqueada: 'Bloqueada',
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
      const vendidos = parseFloat(rifa.boletos_vendidos) || 0
      const total = parseFloat(rifa.boletos_minimos) || 1
      return Math.min((vendidos / total) * 100, 100)
    }
    
    const getIngresos = (rifa) => {
      const vendidos = parseFloat(rifa.boletos_vendidos) || 0
      const precio = parseFloat(rifa.precio_boleto) || 0
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
      duplicateRifa,
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
      
      // Funciones para galería de medios
      newGalleryImage,
      addGalleryImage,
      removeGalleryImage,
      
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

<style>
/* Estilos específicos para AdminRifas que no están en admin.css */
.rifa-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.rifa-thumb {
  width: 60px;
  height: 60px;
  border-radius: var(--admin-border-radius);
  object-fit: cover;
  border: 2px solid var(--admin-primary-teal);
  box-shadow: var(--admin-shadow-sm);
  transition: transform 0.2s ease;
}

.rifa-thumb:hover {
  transform: scale(1.05);
  box-shadow: var(--admin-shadow);
}

.rifa-name {
  font-weight: 600;
  color: var(--admin-text-primary);
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
}

.rifa-id {
  font-size: 0.75rem;
  color: var(--admin-text-muted);
  margin: 0;
}

.price {
  font-weight: 600;
  color: var(--admin-text-primary);
}
</style>
