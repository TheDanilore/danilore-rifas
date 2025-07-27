// Configuración centralizada del backend
const BACKEND_CONFIG = {
  // URL base del servidor backend (sin /api/v1)
  BASE_URL: 'http://localhost:8000',
  
  // URL base para las APIs (con /api/v1)
  API_BASE_URL: 'http://localhost:8000/api/v1',
  
  // Configuración de timeout
  TIMEOUT: 10000,
  
  // Otras configuraciones
  DEFAULT_HEADERS: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
}

// Para desarrollo futuro - poder cambiar fácilmente entre entornos
const getBackendConfig = () => {
  // En el futuro se puede leer de variables de entorno
  // if (process.env.NODE_ENV === 'production') {
  //   return {
  //     ...BACKEND_CONFIG,
  //     BASE_URL: 'https://mi-servidor-produccion.com',
  //     API_BASE_URL: 'https://mi-servidor-produccion.com/api/v1'
  //   }
  // }
  
  return BACKEND_CONFIG
}

export const backendConfig = getBackendConfig()
export default backendConfig
