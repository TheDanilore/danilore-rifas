<template>
  <footer class="admin-footer">
    <div class="container">
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
.admin-footer {
  background: var(--gray-800);
  color: var(--gray-300);
  padding: 2rem 0;
  margin-top: auto;
}

.footer-content {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 2rem;
  align-items: center;
}

.footer-left {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  color: white;
}

.footer-logo i {
  color: var(--accent-yellow);
  font-size: 1.25rem;
}

.footer-text {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.footer-center {
  display: flex;
  justify-content: center;
}

.footer-stats {
  display: flex;
  gap: 2rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: var(--gray-400);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-value {
  font-weight: 600;
  color: white;
  font-size: 0.875rem;
}

.footer-right {
  display: flex;
  justify-content: flex-end;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--gray-400);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: white;
  text-decoration: none;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 1.5rem;
  }

  .footer-stats {
    flex-direction: column;
    gap: 1rem;
  }

  .footer-links {
    justify-content: center;
    flex-wrap: wrap;
  }
}
</style>
