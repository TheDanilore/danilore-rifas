<template>
  <div class="media-gallery">
    <div class="gallery-main">
      <!-- Vista principal -->
      <div class="main-media">
        <div v-if="currentMedia.type === 'image'" class="main-image">
          <img 
            :src="currentMedia.url" 
            :alt="currentMedia.alt"
            class="gallery-main-image"
            @load="handleImageLoad"
            @error="handleImageError"
          />
          <div class="image-zoom-overlay" @click="openFullscreen">
            <i class="fas fa-search-plus"></i>
            <span>Ver en pantalla completa</span>
          </div>
        </div>
        
        <div v-if="currentMedia.type === 'video'" class="main-video">
          <div class="video-container">
            <video 
              :src="currentMedia.url"
              :poster="currentMedia.thumbnail"
              controls
              preload="metadata"
              class="gallery-main-video"
              @loadeddata="handleVideoLoad"
            >
              Tu navegador no soporta videos HTML5.
            </video>
            <div class="video-info">
              <h4>{{ currentMedia.title }}</h4>
              <span class="video-duration">{{ currentMedia.duration }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Indicadores de navegación -->
      <div class="navigation-controls">
        <button 
          class="nav-btn prev-btn"
          :disabled="currentIndex === 0"
          @click="previousMedia"
        >
          <i class="fas fa-chevron-left"></i>
        </button>
        
        <span class="media-counter">
          {{ currentIndex + 1 }} / {{ totalMediaCount }}
        </span>
        
        <button 
          class="nav-btn next-btn"
          :disabled="currentIndex === totalMediaCount - 1"
          @click="nextMedia"
        >
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Thumbnails -->
    <div class="gallery-thumbnails">
      <div class="thumbnails-wrapper">
        <!-- Imágenes -->
        <div 
          v-for="(image, index) in mediaGallery.images" 
          :key="`img-${index}`"
          class="thumbnail"
          :class="{ 
            'active': currentIndex === index,
            'main-thumb': image.isMain
          }"
          @click="selectMedia(index, 'image')"
        >
          <img :src="image.url" :alt="image.alt" class="thumbnail-image" />
          <div v-if="image.isMain" class="main-badge">
            <i class="fas fa-star"></i>
          </div>
        </div>
        
        <!-- Videos -->
        <div 
          v-for="(video, index) in mediaGallery.videos" 
          :key="`vid-${index}`"
          class="thumbnail video-thumbnail"
          :class="{ 
            'active': currentIndex === (mediaGallery.images.length + index)
          }"
          @click="selectMedia(mediaGallery.images.length + index, 'video')"
        >
          <img :src="video.thumbnail" :alt="video.title" class="thumbnail-image" />
          <div class="video-play-overlay">
            <i class="fas fa-play"></i>
          </div>
          <div class="video-duration-badge">{{ video.duration }}</div>
        </div>
      </div>
    </div>

    <!-- Controles adicionales -->
    <div class="gallery-controls">
      <div class="media-type-tabs">
        <button 
          class="type-tab"
          :class="{ active: showingImages }"
          @click="showImages"
        >
          <i class="fas fa-images"></i>
          Imágenes ({{ mediaGallery.images.length }})
        </button>
        <button 
          class="type-tab"
          :class="{ active: showingVideos }"
          @click="showVideos"
        >
          <i class="fas fa-video"></i>
          Videos ({{ mediaGallery.videos.length }})
        </button>
      </div>
    </div>

    <!-- Modal de pantalla completa -->
    <div v-if="fullscreenModal" class="fullscreen-modal" @click="closeFullscreen">
      <div class="fullscreen-content" @click.stop>
        <button class="fullscreen-close" @click="closeFullscreen">
          <i class="fas fa-times"></i>
        </button>
        
        <div class="fullscreen-media">
          <img 
            v-if="currentMedia.type === 'image'"
            :src="currentMedia.url" 
            :alt="currentMedia.alt"
            class="fullscreen-image"
          />
          
          <video 
            v-if="currentMedia.type === 'video'"
            :src="currentMedia.url"
            :poster="currentMedia.thumbnail"
            controls
            autoplay
            class="fullscreen-video"
          >
          </video>
        </div>
        
        <div class="fullscreen-info">
          <h3>{{ currentMedia.title || currentMedia.alt }}</h3>
          <p v-if="currentMedia.duration">Duración: {{ currentMedia.duration }}</p>
        </div>
        
        <div class="fullscreen-navigation">
          <button 
            class="fullscreen-nav-btn"
            :disabled="currentIndex === 0"
            @click="previousMedia"
          >
            <i class="fas fa-chevron-left"></i>
          </button>
          
          <button 
            class="fullscreen-nav-btn"
            :disabled="currentIndex === totalMediaCount - 1"
            @click="nextMedia"
          >
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'MediaGallery',
  props: {
    mediaGallery: {
      type: Object,
      required: true,
      default: () => ({
        images: [],
        videos: []
      })
    }
  },
  setup(props) {
    const currentIndex = ref(0)
    const fullscreenModal = ref(false)
    const showingImages = ref(true)
    const showingVideos = ref(false)

    // Computed properties
    const allMedia = computed(() => {
      const images = props.mediaGallery.images.map(img => ({
        ...img,
        type: 'image'
      }))
      const videos = props.mediaGallery.videos.map(vid => ({
        ...vid,
        type: 'video'
      }))
      return [...images, ...videos]
    })

    const currentMedia = computed(() => {
      return allMedia.value[currentIndex.value] || {}
    })

    const totalMediaCount = computed(() => {
      return allMedia.value.length
    })

    // Methods
    const selectMedia = (index, type) => {
      currentIndex.value = index
    }

    const nextMedia = () => {
      if (currentIndex.value < totalMediaCount.value - 1) {
        currentIndex.value++
      }
    }

    const previousMedia = () => {
      if (currentIndex.value > 0) {
        currentIndex.value--
      }
    }

    const openFullscreen = () => {
      fullscreenModal.value = true
    }

    const closeFullscreen = () => {
      fullscreenModal.value = false
    }

    const showImages = () => {
      showingImages.value = true
      showingVideos.value = false
      // Ir a la primera imagen
      if (props.mediaGallery.images.length > 0) {
        currentIndex.value = 0
      }
    }

    const showVideos = () => {
      showingImages.value = false
      showingVideos.value = true
      // Ir al primer video
      if (props.mediaGallery.videos.length > 0) {
        currentIndex.value = props.mediaGallery.images.length
      }
    }

    const handleImageLoad = () => {
      // Manejar carga exitosa de imagen
    }

    const handleImageError = (event) => {
      // Manejar error de carga de imagen
      event.target.src = '/placeholder-image.jpg'
    }

    const handleVideoLoad = () => {
      // Manejar carga exitosa de video
    }

    // Keyboard navigation
    const handleKeydown = (event) => {
      if (!fullscreenModal.value) return
      
      switch (event.key) {
        case 'ArrowLeft':
          previousMedia()
          break
        case 'ArrowRight':
          nextMedia()
          break
        case 'Escape':
          closeFullscreen()
          break
      }
    }

    onMounted(() => {
      // Inicializar con la imagen principal si existe
      const mainImageIndex = props.mediaGallery.images.findIndex(img => img.isMain)
      if (mainImageIndex !== -1) {
        currentIndex.value = mainImageIndex
      }

      // Agregar listeners de teclado
      document.addEventListener('keydown', handleKeydown)
    })

    return {
      currentIndex,
      currentMedia,
      totalMediaCount,
      fullscreenModal,
      showingImages,
      showingVideos,
      selectMedia,
      nextMedia,
      previousMedia,
      openFullscreen,
      closeFullscreen,
      showImages,
      showVideos,
      handleImageLoad,
      handleImageError,
      handleVideoLoad
    }
  }
}
</script>

<style scoped>
.media-gallery {
  background: var(--white);
  border-radius: var(--border-radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  width: 100%;
  max-width: 100%;
  flex-shrink: 0;
  min-width: 0;
}

/* Vista principal */
.gallery-main {
  position: relative;
  width: 100%;
}

.main-media {
  position: relative;
  width: 100%;
  height: 400px;
  background: var(--gray-100);
  overflow: hidden;
}

.main-image,
.main-video {
  position: relative;
  width: 100%;
  height: 100%;
  flex-shrink: 0;
}

.gallery-main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
  display: block;
}

.gallery-main-image:hover {
  transform: scale(1.05);
}

.image-zoom-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s ease;
  cursor: pointer;
}

.main-image:hover .image-zoom-overlay {
  opacity: 1;
}

.image-zoom-overlay i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
}

/* Video */
.video-container {
  position: relative;
  width: 100%;
  height: 100%;
  flex-shrink: 0;
}

.gallery-main-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.video-info {
  position: absolute;
  bottom: 1rem;
  left: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 1rem;
  border-radius: var(--border-radius-md);
}

.video-info h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
}

.video-duration {
  font-size: 0.875rem;
  opacity: 0.9;
}

/* Controles de navegación */
.navigation-controls {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.75rem 1rem;
  border-radius: var(--border-radius-full);
}

.nav-btn {
  background: transparent;
  border: none;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: background-color 0.3s ease;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
}

.nav-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.media-counter {
  font-size: 0.875rem;
  font-weight: 600;
}

/* Thumbnails */
.gallery-thumbnails {
  padding: 1rem;
  background: var(--gray-50);
  border-top: 1px solid var(--gray-200);
}

.thumbnails-wrapper {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.thumbnail {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: var(--border-radius-md);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  flex-shrink: 0;
}

.thumbnail:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.thumbnail.active {
  border-color: var(--primary-purple);
  box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.3);
}

.thumbnail.main-thumb {
  border-color: var(--warning-yellow);
}

.thumbnail-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.main-badge {
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
  background: var(--warning-yellow);
  color: white;
  border-radius: 50%;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
}

/* Video thumbnails */
.video-thumbnail {
  position: relative;
}

.video-play-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
}

.video-duration-badge {
  position: absolute;
  bottom: 0.25rem;
  right: 0.25rem;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.125rem 0.25rem;
  border-radius: var(--border-radius-sm);
  font-size: 0.625rem;
}

/* Controles de galería */
.gallery-controls {
  padding: 1rem;
  background: var(--white);
  border-top: 1px solid var(--gray-200);
}

.media-type-tabs {
  display: flex;
  gap: 0.5rem;
}

.type-tab {
  padding: 0.75rem 1rem;
  border: 1px solid var(--gray-300);
  background: var(--white);
  color: var(--gray-700);
  border-radius: var(--border-radius-md);
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.type-tab:hover {
  background: var(--gray-50);
}

.type-tab.active {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  color: white;
  border-color: var(--primary-purple);
}

/* Modal de pantalla completa */
.fullscreen-modal {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1rem;
}

.fullscreen-content {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.fullscreen-close {
  position: fixed;
  top: 2rem;
  right: 2rem;
  background: rgba(0, 0, 0, 0.7);
  border: none;
  color: white;
  font-size: 2rem;
  cursor: pointer;
  z-index: 10001;
  padding: 0.5rem;
  border-radius: 50%;
  width: 3rem;
  height: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.fullscreen-close:hover {
  background: rgba(0, 0, 0, 0.9);
}

.fullscreen-media {
  width: 100%;
  height: 85vh;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.fullscreen-image,
.fullscreen-video {
  max-width: 100%;
  max-height: 100%;
  width: auto;
  height: auto;
  object-fit: contain;
  border-radius: var(--border-radius-md);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
}

.fullscreen-info {
  color: white;
  text-align: center;
  background: rgba(0, 0, 0, 0.7);
  padding: 1rem 2rem;
  border-radius: var(--border-radius-lg);
  backdrop-filter: blur(10px);
  max-width: 600px;
}

.fullscreen-info h3 {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.fullscreen-info p {
  margin: 0;
  opacity: 0.9;
}

.fullscreen-navigation {
  position: fixed;
  top: 50%;
  transform: translateY(-50%);
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 0 2rem;
  pointer-events: none;
  z-index: 10000;
}

.fullscreen-nav-btn {
  background: rgba(0, 0, 0, 0.7);
  border: none;
  color: white;
  font-size: 2rem;
  padding: 1rem;
  border-radius: 50%;
  cursor: pointer;
  pointer-events: all;
  transition: all 0.3s ease;
  width: 4rem;
  height: 4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
}

.fullscreen-nav-btn:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.9);
  transform: scale(1.1);
}

.fullscreen-nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  transform: none;
}

/* Responsive */
@media (max-width: 768px) {
  .navigation-controls {
    bottom: 0.5rem;
    right: 0.5rem;
    padding: 0.5rem;
  }

  .nav-btn {
    padding: 0.375rem;
    font-size: 0.875rem;
  }

  .media-counter {
    font-size: 0.75rem;
  }

  .thumbnail {
    width: 60px;
    height: 60px;
  }

  .gallery-controls {
    padding: 0.75rem;
  }

  .type-tab {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }

  .fullscreen-modal {
    padding: 0.5rem;
  }

  .fullscreen-close {
    top: 1rem;
    right: 1rem;
    font-size: 1.5rem;
    width: 2.5rem;
    height: 2.5rem;
  }

  .fullscreen-media {
    height: 80vh;
  }

  .fullscreen-navigation {
    padding: 0 1rem;
  }

  .fullscreen-nav-btn {
    font-size: 1.5rem;
    padding: 0.75rem;
    width: 3rem;
    height: 3rem;
  }

  .fullscreen-info {
    padding: 0.75rem 1rem;
    max-width: 90%;
  }

  .fullscreen-info h3 {
    font-size: 1.25rem;
  }
}
</style>
