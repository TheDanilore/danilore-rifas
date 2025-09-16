import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { useAuthStore } from './store/auth'

// Importar estilos globales en el orden correcto
import '@/assets/styles/main.css'
import '@/assets/styles/components/layout.css'
import '@/assets/styles/components/hero.css'
import '@/assets/styles/components/navigation.css'
import '@/assets/styles/components/forms.css'
import '@/assets/styles/components/auth.css'
import '@/assets/styles/components/store.css'
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
