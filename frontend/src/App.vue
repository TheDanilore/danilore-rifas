<template>
  <div id="app" :class="appClasses">
    <router-view />
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'App',
  setup() {
    const route = useRoute()
    const { isAuthenticated, user } = useAuthStore()

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
      appClasses
    }
  }
}
</script>

<style>
/* Solo importar Font Awesome aquí - los demás estilos se cargan desde main.js */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');

/* Asegurar que el app ocupe toda la altura */
#app {
  min-height: 100vh;
}
</style>
