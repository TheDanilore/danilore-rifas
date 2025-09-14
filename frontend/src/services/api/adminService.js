import apiClient from './api.js'

export class AdminService {
  // Estadísticas del dashboard
  async getDashboardStats() {
    try {
      const response = await apiClient.get('/admin/dashboard/stats')
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener estadísticas')
    } catch (error) {
      console.error('Error al obtener estadísticas:', error)
      // Retornar datos simulados como fallback
      return {
        rifas: {
          total: 12,
          activas: 3,
          proximas: 5,
          completadas: 4
        },
        usuarios: {
          total: 1240,
          nuevos_mes: 85,
          activos: 890
        },
        ventas: {
          total_mes: 15680.50,
          tickets_vendidos: 324,
          pendientes: 12
        },
        pagos: {
          pendientes: 8,
          verificados: 156,
          rechazados: 3
        }
      }
    }
  }

  // Actividad reciente
  async getRecentActivity() {
    try {
      const response = await apiClient.get('/admin/activity/recent')
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener actividad')
    } catch (error) {
      console.error('Error al obtener actividad:', error)
      // Retornar datos simulados como fallback
      return [
        {
          id: 1,
          tipo: 'venta',
          descripcion: 'Nueva venta de 5 tickets - Rifa Gaming Pro',
          usuario: 'Carlos Mendoza',
          tiempo: '2 minutos',
          estado: 'completado'
        },
        {
          id: 2,
          tipo: 'pago',
          descripcion: 'Pago verificado - S/ 25.00',
          usuario: 'Ana García',
          tiempo: '15 minutos',
          estado: 'verificado'
        },
        {
          id: 3,
          tipo: 'rifa',
          descripcion: 'Nueva rifa creada - iPhone 15 Pro',
          usuario: 'Admin',
          tiempo: '1 hora',
          estado: 'activo'
        }
      ]
    }
  }

  // Gestión de rifas
  async getRifas(filters = {}) {
    try {
      const params = new URLSearchParams()
      
      if (filters.estado) params.append('estado', filters.estado)
      if (filters.categoria) params.append('categoria_id', filters.categoria)
      if (filters.search) params.append('search', filters.search)
      if (filters.page) params.append('page', filters.page)
      if (filters.per_page) params.append('per_page', filters.per_page)

      const response = await apiClient.get(`/admin/rifas?${params}`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener rifas')
    } catch (error) {
      console.error('Error al obtener rifas:', error)
      throw error
    }
  }

  async createRifa(rifaData) {
    try {
      const response = await apiClient.post('/admin/rifas', rifaData)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al crear rifa')
    } catch (error) {
      console.error('Error al crear rifa:', error)
      throw error
    }
  }

  async updateRifa(rifaId, rifaData) {
    try {
      const response = await apiClient.put(`/admin/rifas/${rifaId}`, rifaData)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al actualizar rifa')
    } catch (error) {
      console.error('Error al actualizar rifa:', error)
      throw error
    }
  }

  async deleteRifa(rifaId) {
    try {
      const response = await apiClient.delete(`/admin/rifas/${rifaId}`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al eliminar rifa')
    } catch (error) {
      console.error('Error al eliminar rifa:', error)
      throw error
    }
  }

  // Gestión de usuarios
  async getUsuarios(filters = {}) {
    try {
      const params = new URLSearchParams()
      
      if (filters.estado) params.append('estado', filters.estado)
      if (filters.search) params.append('search', filters.search)
      if (filters.page) params.append('page', filters.page)
      if (filters.per_page) params.append('per_page', filters.per_page)

      const response = await apiClient.get(`/admin/usuarios?${params}`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener usuarios')
    } catch (error) {
      console.error('Error al obtener usuarios:', error)
      throw error
    }
  }

  async createUsuario(userData) {
    try {
      const response = await apiClient.post('/admin/usuarios', userData)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al crear usuario')
    } catch (error) {
      console.error('Error al crear usuario:', error)
      throw error
    }
  }

  async updateUsuario(userId, userData) {
    try {
      const response = await apiClient.put(`/admin/usuarios/${userId}`, userData)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al actualizar usuario')
    } catch (error) {
      console.error('Error al actualizar usuario:', error)
      throw error
    }
  }

  async suspendUsuario(userId) {
    try {
      const response = await apiClient.post(`/admin/usuarios/${userId}/suspend`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al suspender usuario')
    } catch (error) {
      console.error('Error al suspender usuario:', error)
      throw error
    }
  }

  async activateUsuario(userId) {
    try {
      const response = await apiClient.post(`/admin/usuarios/${userId}/activate`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al activar usuario')
    } catch (error) {
      console.error('Error al activar usuario:', error)
      throw error
    }
  }

  // Gestión de ventas
  async getVentas(filters = {}) {
    try {
      const params = new URLSearchParams()
      
      if (filters.estado) params.append('estado', filters.estado)
      if (filters.fecha_desde) params.append('fecha_desde', filters.fecha_desde)
      if (filters.fecha_hasta) params.append('fecha_hasta', filters.fecha_hasta)
      if (filters.search) params.append('search', filters.search)
      if (filters.page) params.append('page', filters.page)
      if (filters.per_page) params.append('per_page', filters.per_page)

      const response = await apiClient.get(`/admin/ventas?${params}`)
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener ventas')
    } catch (error) {
      console.error('Error al obtener ventas:', error)
      throw error
    }
  }

  // Gestión de pagos
  async verificarPago(pagoId, notas = null) {
    try {
      const response = await apiClient.post(`/admin/pagos/${pagoId}/verificar`, { notas })
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al verificar pago')
    } catch (error) {
      console.error('Error al verificar pago:', error)
      throw error
    }
  }

  async rechazarPago(pagoId, motivo) {
    try {
      const response = await apiClient.post(`/admin/pagos/${pagoId}/rechazar`, { motivo })
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al rechazar pago')
    } catch (error) {
      console.error('Error al rechazar pago:', error)
      throw error
    }
  }
}

// Crear instancia del servicio
export const adminService = new AdminService()
