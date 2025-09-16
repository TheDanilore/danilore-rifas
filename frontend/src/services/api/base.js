import axios from 'axios'
import { backendConfig } from '@/config/backend.js'

// Crear instancia de axios
const apiClient = axios.create({
  baseURL: backendConfig.API_BASE_URL,
  headers: backendConfig.DEFAULT_HEADERS,
  timeout: backendConfig.TIMEOUT
})

// Interceptor para agregar el token de autenticación
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para manejar respuestas y errores
apiClient.interceptors.response.use(
  (response) => {
    return response.data
  },
  (error) => {
    // Solo loggear errores que no sean verificaciones de perfil sin autenticar
    if (!(error.response?.status === 401 && error.config?.url?.includes('/auth/profile'))) {
      console.error('Error interceptado:', error)
      console.error('Respuesta del error:', error.response?.data)
    }
    
    // Manejar errores de autenticación
    if (error.response?.status === 401) {
      // Solo hacer redirect automático si no es una verificación de perfil
      if (!error.config?.url?.includes('/auth/profile')) {
        // Token expirado o inválido - limpiar y redirigir
        localStorage.removeItem('auth_token')
        localStorage.removeItem('isAuthenticated')
        localStorage.removeItem('currentUser')
        localStorage.removeItem('isAdmin')
        
        // Solo redirigir si estamos en una ruta que requiere autenticación
        const currentPath = window.location.pathname
        if (currentPath.startsWith('/admin') || currentPath.startsWith('/dashboard')) {
          if (currentPath.startsWith('/admin')) {
            window.location.href = '/admin'
          } else {
            window.location.href = '/login'
          }
        }
      }
    }
    
    // Manejar errores de validación (422)
    if (error.response?.status === 422) {
      const validationErrors = error.response.data?.errors
      if (validationErrors) {
        console.error('Errores de validación:', validationErrors)
        // Crear mensaje más específico
        const errorMessages = Object.values(validationErrors).flat()
        const errorMessage = errorMessages.length > 0 ? errorMessages.join(', ') : 'Datos de validación incorrectos'
        return Promise.reject(new Error(errorMessage))
      }
    }
    
    // Manejar otros errores
    const errorMessage = error.response?.data?.message || error.message || 'Error de conexión'
    return Promise.reject(new Error(errorMessage))
  }
)

export default apiClient
export { backendConfig }
