<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="isVisible" class="modal-overlay" @click="handleOverlayClick">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <div class="modal-icon" :class="`modal-icon--${type}`">
              <i :class="getIconClass(type)"></i>
            </div>
            <h3 class="modal-title">{{ title }}</h3>
          </div>
          
          <div class="modal-body">
            <p class="modal-message">{{ message }}</p>
          </div>
          
          <div class="modal-footer">
            <button 
              class="btn btn-secondary"
              @click="handleCancel"
              v-if="showCancel"
            >
              {{ cancelText }}
            </button>
            <button 
              class="btn"
              :class="confirmButtonClass"
              @click="handleConfirm"
              :disabled="loading"
            >
              <span v-if="loading" class="btn-loading">
                <i class="fas fa-spinner fa-spin"></i>
              </span>
              {{ loading ? 'Procesando...' : confirmText }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { ref, computed } from 'vue'

// Estado global del modal
const modalState = ref({
  isVisible: false,
  title: '',
  message: '',
  type: 'info',
  confirmText: 'Aceptar',
  cancelText: 'Cancelar',
  showCancel: true,
  loading: false,
  onConfirm: null,
  onCancel: null
})

export const useConfirmModal = () => {
  const showConfirm = (options) => {
    return new Promise((resolve) => {
      modalState.value = {
        isVisible: true,
        title: options.title || '¿Estás seguro?',
        message: options.message || '',
        type: options.type || 'warning',
        confirmText: options.confirmText || 'Confirmar',
        cancelText: options.cancelText || 'Cancelar',
        showCancel: options.showCancel !== false,
        loading: false,
        onConfirm: () => resolve(true),
        onCancel: () => resolve(false)
      }
    })
  }
  
  const hideModal = () => {
    modalState.value.isVisible = false
  }
  
  const setLoading = (loading) => {
    modalState.value.loading = loading
  }
  
  return {
    showConfirm,
    hideModal,
    setLoading
  }
}

export default {
  name: 'ConfirmModal',
  setup() {
    const isVisible = computed(() => modalState.value.isVisible)
    const title = computed(() => modalState.value.title)
    const message = computed(() => modalState.value.message)
    const type = computed(() => modalState.value.type)
    const confirmText = computed(() => modalState.value.confirmText)
    const cancelText = computed(() => modalState.value.cancelText)
    const showCancel = computed(() => modalState.value.showCancel)
    const loading = computed(() => modalState.value.loading)
    
    const confirmButtonClass = computed(() => {
      const classes = ['btn']
      switch (type.value) {
        case 'danger':
          classes.push('btn-danger')
          break
        case 'warning':
          classes.push('btn-warning')
          break
        case 'success':
          classes.push('btn-success')
          break
        default:
          classes.push('btn-primary')
      }
      return classes.join(' ')
    })
    
    const getIconClass = (type) => {
      const icons = {
        danger: 'fas fa-exclamation-triangle',
        warning: 'fas fa-exclamation-circle',
        success: 'fas fa-check-circle',
        info: 'fas fa-info-circle'
      }
      return icons[type] || icons.info
    }
    
    const handleConfirm = () => {
      if (modalState.value.onConfirm) {
        modalState.value.onConfirm()
      }
      modalState.value.isVisible = false
    }
    
    const handleCancel = () => {
      if (modalState.value.onCancel) {
        modalState.value.onCancel()
      }
      modalState.value.isVisible = false
    }
    
    const handleOverlayClick = () => {
      if (showCancel.value && !loading.value) {
        handleCancel()
      }
    }
    
    return {
      isVisible,
      title,
      message,
      type,
      confirmText,
      cancelText,
      showCancel,
      loading,
      confirmButtonClass,
      getIconClass,
      handleConfirm,
      handleCancel,
      handleOverlayClick
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 20px;
}

.modal-container {
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 20px 64px rgba(0, 0, 0, 0.2);
  max-width: 400px;
  width: 100%;
  overflow: hidden;
}

.modal-header {
  text-align: center;
  padding: 24px 24px 16px;
}

.modal-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  margin-bottom: 16px;
  font-size: 24px;
}

.modal-icon--danger {
  background: #fef2f2;
  color: #ef4444;
}

.modal-icon--warning {
  background: #fffbeb;
  color: #f59e0b;
}

.modal-icon--success {
  background: #f0fdf4;
  color: #10b981;
}

.modal-icon--info {
  background: #eff6ff;
  color: #3b82f6;
}

.modal-title {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
}

.modal-body {
  padding: 0 24px 24px;
  text-align: center;
}

.modal-message {
  margin: 0;
  color: #6b7280;
  line-height: 1.6;
}

.modal-footer {
  display: flex;
  gap: 12px;
  padding: 0 24px 24px;
}

.btn {
  flex: 1;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #3b82f6;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-danger {
  background: #ef4444;
  color: #ffffff;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-warning {
  background: #f59e0b;
  color: #ffffff;
}

.btn-warning:hover:not(:disabled) {
  background: #d97706;
}

.btn-success {
  background: #10b981;
  color: #ffffff;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
}

.btn-secondary {
  background: #f3f4f6;
  color: #374151;
}

.btn-secondary:hover:not(:disabled) {
  background: #e5e7eb;
}

.btn-loading {
  display: flex;
  align-items: center;
}

/* Animaciones */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .modal-container,
.modal-leave-to .modal-container {
  transform: scale(0.8) translateY(-20px);
}

/* Responsive */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 16px;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>