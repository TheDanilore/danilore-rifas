import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './store/auth'

// Importar estilos globales
import '@/assets/styles/main.css'
import '@/assets/styles/components/admin.css'

// Configurar feature flags para evitar warnings
if (typeof __VUE_PROD_HYDRATION_MISMATCH_DETAILS__ === 'undefined') {
  globalThis.__VUE_PROD_HYDRATION_MISMATCH_DETAILS__ = false
}

const app = createApp(App)

app.use(router)

// Verificar estado de autenticación al iniciar la aplicación
const { checkAuthStatus } = useAuthStore()
checkAuthStatus()

app.mount('#app')
