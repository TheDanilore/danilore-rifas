<template>
  <div id="app" :class="appClasses">
    <AppHeader v-if="!route.path.startsWith('/admin') && !route.path.startsWith('/login') && !route.path.startsWith('/register')" />
    <main class="main-content">
      <router-view />
    </main>
    <AppFooter v-if="!route.path.startsWith('/admin') && !route.path.startsWith('/login') && !route.path.startsWith('/register')" />
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

export default {
  name: 'App',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const route = useRoute()
    const { isAuthenticated, user, checkAuthStatus } = useAuthStore()

    // Inicializar estado de autenticación al cargar la app
    onMounted(async () => {
      await checkAuthStatus()
    })

    const appClasses = computed(() => {
      const classes = []
      
      // Determinar el tipo de usuario y aplicar la clase correspondiente
      if (route.path.startsWith('/admin')) {
        classes.push('app-admin')
      } else if (isAuthenticated.value) {
        classes.push('app-authenticated')
      } else {
        classes.push('app-public')
      }
      
      // Agregar clase para el tipo de página
      if (route.meta?.layout) {
        classes.push(`layout-${route.meta.layout}`)
      }
      
      return classes
    })

    return {
      route,
      appClasses
    }
  }
}
</script>

<style>
/* Estilos críticos directos para asegurar que se vean los elementos */
#app {
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background-color: #f9fafb;
  color: #1f2937;
}

/* Asegurar que los elementos básicos se vean */
* {
  box-sizing: border-box;
}

body {
  margin: 0;
  padding: 0;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background-color: #f9fafb;
  color: #1f2937;
}

/* Contenedores básicos */
.container {
  width: 100%;
  max-width: 1280px;
  margin-left: auto;
  margin-right: auto;
  padding-left: 1rem;
  padding-right: 1rem;
}

/* Estilos de texto básicos */
h1, h2, h3, h4, h5, h6 {
  font-weight: 700;
  line-height: 1.25;
  color: #111827;
  margin-bottom: 1rem;
}

h1 { font-size: 2.25rem; }
h2 { font-size: 1.875rem; }
h3 { font-size: 1.5rem; }

p {
  margin-bottom: 1rem;
  line-height: 1.625;
  color: #4b5563;
}

/* Botones básicos */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: 1px solid transparent;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 1rem;
  text-decoration: none;
  cursor: pointer;
  transition: all 150ms ease-in-out;
  background: #ffffff;
  color: #374151;
}

.btn:hover {
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #14b8a6, #10b981);
  border-color: #14b8a6;
  color: #ffffff;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #0f766e, #059669);
  transform: translateY(-1px);
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.cta-primary {
  background: linear-gradient(135deg, #14b8a6, #10b981);
  color: #ffffff;
  padding: 1rem 2rem;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1.125rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 250ms ease-in-out;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border: none;
  cursor: pointer;
}

.cta-primary:hover {
  background: linear-gradient(135deg, #0f766e, #059669);
  transform: translateY(-3px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  text-decoration: none;
}

.cta-secondary {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  color: #1f2937;
  padding: 1rem 2rem;
  border-radius: 0.75rem;
  font-weight: 500;
  font-size: 1.125rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 250ms ease-in-out;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.cta-secondary:hover {
  background: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  text-decoration: none;
}

/* Hero básico */
.hero {
  position: relative;
  min-height: 400px;
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(249, 250, 251, 0.9) 100%);
  overflow: hidden;
}

.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  width: 100%;
  padding: 3rem 0;
}

.hero-enhanced {
  background: linear-gradient(135deg, 
    rgba(91, 44, 135, 0.1) 0%, 
    rgba(79, 70, 229, 0.1) 25%,
    rgba(16, 185, 129, 0.1) 50%,
    rgba(245, 158, 11, 0.1) 75%,
    rgba(236, 72, 153, 0.1) 100%
  );
}

.hero-title-enhanced {
  font-size: clamp(2.5rem, 6vw, 4.5rem);
  font-weight: 800;
  line-height: 1.25;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #5B2C87 0%, #4F46E5 25%, #6366F1 50%, #F59E0B 75%, #D97706 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-subtitle-enhanced {
  font-size: clamp(1.25rem, 3vw, 1.75rem);
  color: #374151;
  margin-bottom: 2rem;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.625;
}

.hero-cta-group {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 3rem;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #14b8a6;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.feature-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(8px);
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Estados */
.loading-state-enhanced,
.error-state-enhanced,
.empty-state-enhanced {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  min-height: 400px;
}

.state-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.75rem;
}

.state-message {
  font-size: 1.125rem;
  color: #4b5563;
  max-width: 400px;
  line-height: 1.625;
}

/* Utilidades */
.price-highlight {
  color: #F59E0B;
  font-weight: 700;
  font-size: 1.1em;
}

/* Responsive */
@media (max-width: 768px) {
  .hero-cta-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .cta-primary,
  .cta-secondary {
    text-align: center;
    justify-content: center;
  }
}
</style>
