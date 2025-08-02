<template>
  <footer class="admin-footer">
    <div class="admin-container">
      <div class="footer-content">
        <div class="footer-left">
          <div class="footer-logo">
            <i class="fas fa-crown"></i>
            <span>Danilore Rifas Admin</span>
          </div>
          <p class="footer-text">
            Panel de administración © {{ currentYear }} - Todos los derechos reservados
          </p>
        </div>

        <div class="footer-center">
          <div class="footer-stats">
            <div class="stat-item">
              <span class="stat-label">Sistema</span>
              <span class="stat-value">v2.0.1</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Uptime</span>
              <span class="stat-value">99.9%</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Último backup</span>
              <span class="stat-value">{{ lastBackup }}</span>
            </div>
          </div>
        </div>

        <div class="footer-right">
          <div class="footer-links">
            <a href="#" class="footer-link">
              <i class="fas fa-life-ring"></i>
              Soporte
            </a>
            <a href="#" class="footer-link">
              <i class="fas fa-book"></i>
              Documentación
            </a>
            <a href="#" class="footer-link">
              <i class="fas fa-bug"></i>
              Reportar Bug
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'AdminFooter',
  setup() {
    const currentYear = computed(() => new Date().getFullYear())
    const lastBackup = computed(() => {
      const now = new Date()
      const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000)
      return yesterday.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    })

    return {
      currentYear,
      lastBackup
    }
  }
}
</script>

<style scoped>
/* Usar clases del admin.css global */
</style>
