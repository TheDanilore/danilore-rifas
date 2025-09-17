import apiClient from './api.js'

export class AuthService {
  /**
   * Registro de usuario
   */
  async register(userData) {
    try {
      console.log('Datos enviados para registro:', userData)
      const response = await apiClient.post('/auth/register', {
        nombres: userData.nombres,
        apellidos: userData.apellidos,
        telefono: userData.telefono, // Enviar con el nombre correcto que espera el backend
        email: userData.email,
        password: userData.password,
        tipo_documento: userData.tipo_documento,
        numero_documento: userData.numero_documento,
        fecha_nacimiento: userData.fecha_nacimiento,
        genero: userData.genero,
        direccion: userData.direccion,
        ciudad: userData.ciudad,
        departamento: userData.departamento,
        codigo_postal: userData.codigo_postal,
        pais: userData.pais,
        // Nuevos campos de términos y preferencias
        accept_terms: userData.accept_terms,
        accept_marketing: userData.accept_marketing,
        preferencias_notificacion: userData.preferencias_notificacion
      })
      
      if (response.success) {
        // Guardar token - el backend devuelve el token en data.token.access_token
        const token = response.data.token?.access_token || response.data.token
        localStorage.setItem('auth_token', token)
        return response.data.user
      }
      
      throw new Error(response.message || 'Error en el registro')
    } catch (error) {
      console.error('Error en registro:', error.response?.data?.message || error.message)
      throw error
    }
  }

  /**
   * Inicio de sesión
   */
  async login(credentials) {
    try {
      const response = await apiClient.post('/auth/login', {
        identifier: credentials.identifier,
        password: credentials.password
      })
      
      if (response.success) {
        // Guardar token - el backend devuelve el token en data.token.access_token
        const token = response.data.token?.access_token || response.data.token
        localStorage.setItem('auth_token', token)
        return response.data.user
      }
      
      throw new Error(response.message || 'Credenciales incorrectas')
    } catch (error) {
      console.error('Error en login:', error.response?.data?.message || error.message)
      throw error
    }
  }

  /**
   * Cerrar sesión
   */
  async logout() {
    try {
      await apiClient.post('/auth/logout')
    } catch (error) {
      console.error('Error al cerrar sesión:', error)
    } finally {
      // Limpiar almacenamiento local
      localStorage.removeItem('auth_token')
      localStorage.removeItem('isAuthenticated')
      localStorage.removeItem('currentUser')
    }
  }

  /**
   * Obtener perfil del usuario
   */
  async getProfile() {
    try {
      const response = await apiClient.get('/auth/me')
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener perfil')
    } catch (error) {
      throw error
    }
  }

  /**
   * Actualizar perfil del usuario
   */
  async updateProfile(profileData) {
    try {
      const response = await apiClient.put('/auth/profile', profileData)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al actualizar perfil')
    } catch (error) {
      throw error
    }
  }

  /**
   * Verificar si el usuario está autenticado
   */
  isAuthenticated() {
    return !!localStorage.getItem('auth_token')
  }

  /**
   * Obtener token de autenticación
   */
  getToken() {
    return localStorage.getItem('auth_token')
  }
}

// Singleton instance
export const authService = new AuthService()
