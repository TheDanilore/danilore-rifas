<template>
  <div class="image-selector">
    <label class="form-label">{{ label }}</label>
    
    <!-- Pestañas para elegir método -->
    <div class="selector-tabs">
      <button 
        type="button"
        class="tab-btn"
        :class="{ active: method === 'upload' }"
        @click="setMethod('upload')"
      >
        <i class="fas fa-upload"></i>
        Subir Imagen
      </button>
      <button 
        type="button"
        class="tab-btn"
        :class="{ active: method === 'url' }"
        @click="setMethod('url')"
      >
        <i class="fas fa-link"></i>
        URL Externa
      </button>
    </div>

    <!-- Contenido según el método seleccionado -->
    <div class="selector-content">
      <!-- Subir archivo -->
      <div v-if="method === 'upload'" class="upload-section">
        <div class="upload-zone" :class="{ 'dragover': isDragOver }" @drop="handleDrop" @dragover.prevent @dragenter.prevent @dragleave="isDragOver = false">
          <input 
            ref="fileInput"
            type="file" 
            accept="image/*" 
            @change="handleFileSelect"
            class="file-input"
          >
          <div class="upload-content">
            <i class="fas fa-cloud-upload-alt upload-icon"></i>
            <p class="upload-text">
              Arrastra una imagen aquí o 
              <button type="button" @click="$refs.fileInput.click()" class="upload-link">
                haz clic para seleccionar
              </button>
            </p>
            <p class="upload-hint">PNG, JPG, GIF hasta 2MB</p>
          </div>
        </div>
        
        <!-- Progreso de subida -->
        <div v-if="uploading" class="upload-progress">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
          </div>
          <span class="progress-text">{{ uploadProgress }}%</span>
        </div>
      </div>

      <!-- URL Externa -->
      <div v-if="method === 'url'" class="url-section">
        <input 
          v-model="urlValue"
          type="url" 
          class="url-input"
          :placeholder="placeholder || 'https://ejemplo.com/imagen.jpg'"
          @input="updateValue"
        >
      </div>
    </div>

    <!-- Preview de la imagen -->
    <div v-if="previewUrl" class="image-preview">
      <img :src="previewUrl" :alt="label" class="preview-img">
      <button type="button" @click="removeImage" class="remove-btn">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Error message -->
    <div v-if="error" class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      {{ error }}
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'ImageSelector',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    label: {
      type: String,
      required: true
    },
    placeholder: {
      type: String,
      default: ''
    },
    uploadEndpoint: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const method = ref('url')
    const urlValue = ref(props.modelValue || '')
    const isDragOver = ref(false)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const error = ref('')
    const fileInput = ref(null)

    const previewUrl = computed(() => {
      return props.modelValue || urlValue.value
    })

    // Watchers
    watch(() => props.modelValue, (newValue) => {
      urlValue.value = newValue || ''
    })

    // Methods
    const setMethod = (newMethod) => {
      method.value = newMethod
      error.value = ''
    }

    const updateValue = () => {
      emit('update:modelValue', urlValue.value)
    }

    const handleFileSelect = (event) => {
      const file = event.target.files[0]
      if (file) {
        uploadFile(file)
      }
    }

    const handleDrop = (event) => {
      event.preventDefault()
      isDragOver.value = false
      
      const files = event.dataTransfer.files
      if (files.length > 0) {
        uploadFile(files[0])
      }
    }

    const uploadFile = async (file) => {
      // Validaciones
      if (!file.type.startsWith('image/')) {
        error.value = 'Por favor selecciona una imagen válida'
        return
      }

      if (file.size > 2 * 1024 * 1024) { // 2MB
        error.value = 'La imagen debe ser menor a 2MB'
        return
      }

      error.value = ''
      uploading.value = true
      uploadProgress.value = 0

      try {
        const formData = new FormData()
        formData.append('image', file)
        formData.append('tipo', 'principal')

        const response = await fetch(props.uploadEndpoint, {
          method: 'POST',
          body: formData,
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        })

        if (!response.ok) {
          throw new Error('Error al subir la imagen')
        }

        const result = await response.json()
        
        if (result.success) {
          const imageUrl = result.data.url
          urlValue.value = imageUrl
          emit('update:modelValue', imageUrl)
          uploadProgress.value = 100
        } else {
          throw new Error(result.message || 'Error al subir la imagen')
        }

      } catch (err) {
        error.value = err.message || 'Error al subir la imagen'
      } finally {
        uploading.value = false
        setTimeout(() => {
          uploadProgress.value = 0
        }, 1000)
      }
    }

    const removeImage = () => {
      urlValue.value = ''
      emit('update:modelValue', '')
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }

    return {
      method,
      urlValue,
      isDragOver,
      uploading,
      uploadProgress,
      error,
      fileInput,
      previewUrl,
      setMethod,
      updateValue,
      handleFileSelect,
      handleDrop,
      removeImage
    }
  }
}
</script>

<style scoped>
.image-selector {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.form-label {
  font-weight: 600;
  color: var(--gray-700);
  font-size: 0.875rem;
}

.selector-tabs {
  display: flex;
  gap: 0.5rem;
  border-bottom: 1px solid var(--gray-200);
  padding-bottom: 0.5rem;
}

.tab-btn {
  padding: 0.5rem 1rem;
  border: 1px solid var(--gray-300);
  background: var(--white);
  border-radius: var(--border-radius);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.tab-btn:hover {
  background: var(--gray-50);
}

.tab-btn.active {
  background: var(--primary-purple);
  color: var(--white);
  border-color: var(--primary-purple);
}

.selector-content {
  margin-top: 0.5rem;
}

/* Upload Section */
.upload-zone {
  border: 2px dashed var(--gray-300);
  border-radius: var(--border-radius-lg);
  padding: 2rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.upload-zone:hover,
.upload-zone.dragover {
  border-color: var(--primary-purple);
  background: rgba(139, 92, 246, 0.05);
}

.file-input {
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.upload-icon {
  font-size: 2rem;
  color: var(--gray-400);
  margin-bottom: 0.5rem;
}

.upload-text {
  color: var(--gray-600);
  margin: 0;
}

.upload-link {
  color: var(--primary-purple);
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
}

.upload-hint {
  font-size: 0.75rem;
  color: var(--gray-500);
  margin: 0.5rem 0 0 0;
}

/* Progress */
.upload-progress {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}

.progress-bar {
  flex: 1;
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

.progress-text {
  font-size: 0.875rem;
  color: var(--gray-600);
  min-width: 3rem;
}

/* URL Section */
.url-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--gray-300);
  border-radius: var(--border-radius);
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.url-input:focus {
  outline: none;
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

/* Preview */
.image-preview {
  position: relative;
  display: inline-block;
  margin-top: 1rem;
}

.preview-img {
  max-width: 200px;
  max-height: 150px;
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
}

.remove-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--danger-red);
  color: var(--white);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

.remove-btn:hover {
  background: #dc2626;
}

/* Error */
.error-message {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--danger-red);
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
</style>
