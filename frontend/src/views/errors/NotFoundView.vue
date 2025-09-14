<template>
  <div class="not-found-page">
    <AppHeader />
    
    <main class="main-content">
      <div class="container">
        <div class="error-content">
          <div class="error-animation">
            <div class="error-number">4</div>
            <div class="error-rifa">
              <div class="rifa-ball">
                <span>0</span>
              </div>
            </div>
            <div class="error-number">4</div>
          </div>
          
          <h1 class="error-title">¡Oops! Página no encontrada</h1>
          <p class="error-description">
            La página que buscas parece haber ganado en otra rifa y ya no está aquí.
            Pero no te preocupes, ¡tenemos muchas otras oportunidades esperándote!
          </p>
          
          <div class="error-actions">
            <router-link to="/" class="btn btn-primary">
              <i class="fas fa-home"></i>
              Volver al Inicio
            </router-link>
            <router-link to="/ganadores" class="btn btn-outline">
              <i class="fas fa-trophy"></i>
              Ver Ganadores
            </router-link>
          </div>
          
          <div class="error-suggestions">
            <h3>¿Qué tal si intentas con estas opciones?</h3>
            <div class="suggestions-grid">
              <router-link to="/" class="suggestion-card">
                <i class="fas fa-ticket-alt"></i>
                <span>Rifas Activas</span>
              </router-link>
              <router-link to="/como-funciona" class="suggestion-card">
                <i class="fas fa-question-circle"></i>
                <span>Cómo Funciona</span>
              </router-link>
              <router-link to="/ganadores" class="suggestion-card">
                <i class="fas fa-crown"></i>
                <span>Ganadores</span>
              </router-link>
              <router-link to="/dashboard" class="suggestion-card" v-if="isAuthenticated">
                <i class="fas fa-user"></i>
                <span>Mi Perfil</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <AppFooter />
  </div>
</template>

<script>
import AppHeader from '@/components/AppHeader.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'NotFound',
  components: {
    AppHeader,
    AppFooter
  },
  setup() {
    const { isAuthenticated } = useAuthStore()
    
    return {
      isAuthenticated
    }
  }
}
</script>

<style scoped>
.not-found-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  display: flex;
  align-items: center;
  padding: 2rem 0;
  background: linear-gradient(135deg, #faf5ff 0%, #f3f4f6 50%, #fef3c7 100%);
}

.error-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.error-animation {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.error-number {
  font-size: 8rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 0 30px rgba(79, 70, 229, 0.3);
  animation: pulse 2s ease-in-out infinite;
}

.error-rifa {
  display: flex;
  align-items: center;
  justify-content: center;
}

.rifa-ball {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary-gold), var(--accent-yellow));
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 
    0 0 20px rgba(217, 119, 6, 0.3),
    inset 0 0 20px rgba(255, 255, 255, 0.2);
  animation: bounce 2s ease-in-out infinite;
  position: relative;
}

.rifa-ball::before {
  content: '';
  position: absolute;
  top: 20%;
  left: 30%;
  width: 30px;
  height: 30px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 50%;
  blur: 10px;
}

.rifa-ball span {
  font-size: 4rem;
  font-weight: 900;
  color: var(--primary-purple);
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

.error-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 1rem;
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.error-description {
  font-size: 1.1rem;
  color: var(--gray-600);
  margin-bottom: 2rem;
  line-height: 1.6;
}

.error-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-bottom: 3rem;
  flex-wrap: wrap;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: var(--border-radius-full);
  font-weight: 600;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-purple), var(--primary-blue));
  color: white;
  box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}

.btn-outline {
  background: transparent;
  color: var(--primary-purple);
  border: 2px solid var(--primary-purple);
}

.btn-outline:hover {
  background: var(--primary-purple);
  color: white;
  transform: translateY(-2px);
}

.error-suggestions {
  margin-top: 2rem;
}

.error-suggestions h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--gray-700);
  margin-bottom: 1.5rem;
}

.suggestions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.suggestion-card {
  background: white;
  border-radius: var(--border-radius-lg);
  padding: 1.5rem;
  text-decoration: none;
  color: var(--gray-700);
  box-shadow: var(--shadow-md);
  transition: all 0.3s ease;
  border: 1px solid var(--gray-200);
}

.suggestion-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-xl);
  border-color: var(--primary-purple);
}

.suggestion-card i {
  font-size: 2rem;
  color: var(--primary-purple);
  margin-bottom: 0.5rem;
  display: block;
}

.suggestion-card span {
  font-weight: 600;
  display: block;
}

/* Responsive */
@media (max-width: 768px) {
  .error-animation {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .error-number {
    font-size: 4rem;
  }
  
  .rifa-ball {
    width: 80px;
    height: 80px;
  }
  
  .rifa-ball span {
    font-size: 2.5rem;
  }
  
  .error-title {
    font-size: 1.8rem;
  }
  
  .error-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .suggestions-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }
  
  .suggestion-card {
    padding: 1rem;
  }
  
  .suggestion-card i {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .error-content {
    padding: 1rem;
  }
  
  .suggestions-grid {
    grid-template-columns: 1fr;
  }
}
</style>
