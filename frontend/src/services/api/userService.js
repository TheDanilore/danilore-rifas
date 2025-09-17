import { apiClient } from './api'

/**
 * Servicio para manejo de datos de usuario autenticado
 * Consume las rutas de user.php y auth.php
 */
export const userService = {
  // Perfil de usuario
  async getProfile() {
    try {
      const response = await apiClient.get('/user/perfil')
      // El backend devuelve { success: true, data: { user: {...}, stats: {...} } }
      if (response.success && response.data) {
        return response.data
      }
      throw new Error('Estructura de respuesta inválida')
    } catch (error) {
      console.error('Error al obtener perfil:', error)
      throw error
    }
  },

  async updateProfile(profileData) {
    try {
      const response = await apiClient.put('/user/perfil/actualizar', profileData)
      // El backend devuelve { success: true, data: { user: {...} }, message: "..." }
      if (response.success && response.data) {
        return response.data
      }
      throw new Error('Estructura de respuesta inválida')
    } catch (error) {
      console.error('Error al actualizar perfil:', error)
      throw error
    }
  },

  // Cambiar contraseña
  async changePassword(passwordData) {
    try {
      const response = await apiClient.put('/auth/change-password', passwordData)
      return response
    } catch (error) {
      console.error('Error al cambiar contraseña:', error)
      throw error
    }
  },

  // Gestión de dispositivos/tokens
  async getDevices() {
    try {
      const response = await apiClient.get('/auth/tokens')
      if (response.success && response.data) {
        return response.data
      }
      return []
    } catch (error) {
      console.error('Error al obtener dispositivos:', error)
      throw error
    }
  },

  async revokeDevice(tokenId) {
    try {
      const response = await apiClient.delete(`/auth/tokens/${tokenId}`)
      return response
    } catch (error) {
      console.error('Error al revocar dispositivo:', error)
      throw error
    }
  },

  async logoutAllDevices() {
    try {
      const response = await apiClient.post('/auth/logout-all')
      return response
    } catch (error) {
      console.error('Error al cerrar todas las sesiones:', error)
      throw error
    }
  },

  async getActivity() {
    try {
      const response = await apiClient.get('/user/perfil/actividad')
      if (response.success && response.data) {
        return response.data
      }
      return response.data || []
    } catch (error) {
      console.error('Error al obtener actividad:', error)
      throw error
    }
  },

  // Boletos del usuario
  async getBoletos(params = {}) {
    try {
      const response = await apiClient.get('/user/boletos', { params })
      if (response.success && response.data) {
        return response.data
      }
      return response.data || []
    } catch (error) {
      console.error('Error al obtener boletos:', error)
      throw error
    }
  },

  async getBoleto(id) {
    try {
      const response = await apiClient.get(`/user/boletos/${id}`)
      if (response.success && response.data) {
        return response.data
      }
      return response.data
    } catch (error) {
      console.error('Error al obtener boleto:', error)
      throw error
    }
  },

  async transferirBoleto(boletoId, destinatarioEmail) {
    try {
      const response = await apiClient.post(`/user/boletos/${boletoId}/transferir`, {
        destinatario_email: destinatarioEmail
      })
      return response.data
    } catch (error) {
      console.error('Error al transferir boleto:', error)
      throw error
    }
  },

  async getHistorialTransferencias(boletoId) {
    try {
      const response = await apiClient.get(`/user/boletos/${boletoId}/historial-transferencias`)
      return response.data
    } catch (error) {
      console.error('Error al obtener historial de transferencias:', error)
      throw error
    }
  },

  async verificarEstadoBoletos(rifaId) {
    try {
      const response = await apiClient.get(`/user/boletos/rifa/${rifaId}/verificar-estado`)
      return response.data
    } catch (error) {
      console.error('Error al verificar estado de boletos:', error)
      throw error
    }
  },

  // Ventas del usuario
  async getMisVentas(params = {}) {
    try {
      const response = await apiClient.get('/user/ventas/mis-ventas', { params })
      return response.data
    } catch (error) {
      console.error('Error al obtener ventas:', error)
      throw error
    }
  },

  async confirmarPago(codigo, pagoData) {
    try {
      const response = await apiClient.post(`/user/ventas/${codigo}/confirmar-pago`, pagoData)
      return response.data
    } catch (error) {
      console.error('Error al confirmar pago:', error)
      throw error
    }
  },

  // Pagos
  async getPagos(params = {}) {
    try {
      const response = await apiClient.get('/user/pagos', { params })
      return response.data
    } catch (error) {
      console.error('Error al obtener pagos:', error)
      throw error
    }
  },

  async getPago(id) {
    try {
      const response = await apiClient.get(`/user/pagos/${id}`)
      return response.data
    } catch (error) {
      console.error('Error al obtener pago:', error)
      throw error
    }
  },

  async crearPago(pagoData) {
    try {
      const response = await apiClient.post('/user/pagos', pagoData)
      return response.data
    } catch (error) {
      console.error('Error al crear pago:', error)
      throw error
    }
  },

  // Favoritos
  async getFavoritos() {
    try {
      const response = await apiClient.get('/user/favoritos')
      return response.data
    } catch (error) {
      console.error('Error al obtener favoritos:', error)
      throw error
    }
  },

  async toggleFavorito(rifaId) {
    try {
      const response = await apiClient.post('/user/favoritos/toggle', { rifa_id: rifaId })
      return response.data
    } catch (error) {
      console.error('Error al toggle favorito:', error)
      throw error
    }
  },

  async verificarFavorito(rifaId) {
    try {
      const response = await apiClient.get(`/user/favoritos/verificar/${rifaId}`)
      return response.data
    } catch (error) {
      console.error('Error al verificar favorito:', error)
      throw error
    }
  },

  // Notificaciones
  async getNotificaciones(params = {}) {
    try {
      const response = await apiClient.get('/user/notificaciones', { params })
      return response.data
    } catch (error) {
      console.error('Error al obtener notificaciones:', error)
      throw error
    }
  },

  async getResumenNotificaciones() {
    try {
      const response = await apiClient.get('/user/notificaciones/resumen')
      return response.data
    } catch (error) {
      console.error('Error al obtener resumen de notificaciones:', error)
      throw error
    }
  },

  async marcarNotificacionLeida(notificacionId) {
    try {
      const response = await apiClient.patch(`/user/notificaciones/${notificacionId}/leida`)
      return response.data
    } catch (error) {
      console.error('Error al marcar notificación como leída:', error)
      throw error
    }
  },

  async marcarTodasNotificacionesLeidas() {
    try {
      const response = await apiClient.patch('/user/notificaciones/marcar-todas-leidas')
      return response.data
    } catch (error) {
      console.error('Error al marcar todas las notificaciones como leídas:', error)
      throw error
    }
  },

  // Tokens de autenticación
  async getTokens() {
    try {
      const response = await apiClient.get('/auth/tokens')
      return response.data
    } catch (error) {
      console.error('Error al obtener tokens:', error)
      throw error
    }
  },

  async revokeToken(tokenId) {
    try {
      const response = await apiClient.delete(`/auth/tokens/${tokenId}`)
      return response.data
    } catch (error) {
      console.error('Error al revocar token:', error)
      throw error
    }
  }
}

export default userService
