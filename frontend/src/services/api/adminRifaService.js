import apiClient from './api.js'

export class AdminRifaService {
  
  // Obtener todas las rifas para admin
  async getAllRifas() {
    try {
      const response = await apiClient.get('/admin/rifas')
      console.log('Respuesta admin rifas:', response)
      
      if (response.success) {
        return response.data?.data || response.data
      }
      throw new Error(response.message || 'Error al obtener rifas')
    } catch (error) {
      console.error('Error al obtener rifas admin:', error)
      throw error
    }
  }

  // Crear nueva rifa
  async createRifa(rifaData) {
    try {
      // Transformar datos para que coincidan con el backend
      const dataToSend = {
        titulo: rifaData.titulo,
        codigo_unico: rifaData.codigo_unico,
        descripcion: rifaData.descripcion,
        precio_boleto: parseFloat(rifaData.precio_boleto),
        boletos_minimos: parseInt(rifaData.boletos_minimos),
        boletos_maximos: rifaData.boletos_maximos ? parseInt(rifaData.boletos_maximos) : null,
        max_boletos_por_persona: rifaData.max_boletos_por_persona ? parseInt(rifaData.max_boletos_por_persona) : 10,
        fecha_inicio: rifaData.fecha_inicio,
        fecha_fin: rifaData.fecha_fin,
        fecha_sorteo: rifaData.fecha_sorteo || null,
        categoria_id: rifaData.categoria_id || null,
        tipo: rifaData.tipo || 'actual',
        estado: rifaData.estado,
        orden: rifaData.orden ? parseInt(rifaData.orden) : 1,
        es_destacada: rifaData.es_destacada === 'true' || rifaData.es_destacada === true,
        imagen_principal: rifaData.imagen_principal || null,
        terminos_condiciones: rifaData.terminos_condiciones || null,
        notas_admin: rifaData.notas_admin || null,
        premios: rifaData.premios?.map(premio => ({
          codigo: premio.codigo,
          titulo: premio.titulo,
          descripcion: premio.descripcion,
          imagen_principal: premio.imagen_principal,
          estado: premio.estado || 'bloqueado',
          orden: premio.orden,
          notas_admin: premio.notas_admin,
          niveles: premio.niveles?.map(nivel => ({
            codigo: nivel.codigo,
            titulo: nivel.titulo,
            descripcion: nivel.descripcion,
            tickets_necesarios: parseInt(nivel.tickets_necesarios),
            valor_aproximado: nivel.valor_aproximado ? parseFloat(nivel.valor_aproximado) : 0,
            imagen: nivel.imagen,
            es_actual: nivel.es_actual === 'true' || nivel.es_actual === true,
            especificaciones: nivel.especificaciones,
            orden: nivel.orden
          })) || []
        })) || []
      }

      const response = await apiClient.post('/admin/rifas', dataToSend)
      console.log('Respuesta crear rifa:', response)
      
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al crear rifa')
    } catch (error) {
      console.error('Error al crear rifa:', error)
      throw error
    }
  }

  // Actualizar rifa
  async updateRifa(rifaId, rifaData) {
    try {
      // Transformar datos para que coincidan con el backend
      const dataToSend = {
        titulo: rifaData.titulo,
        codigo_unico: rifaData.codigo_unico,
        descripcion: rifaData.descripcion,
        precio_boleto: parseFloat(rifaData.precio_boleto),
        boletos_minimos: parseInt(rifaData.boletos_minimos),
        boletos_maximos: rifaData.boletos_maximos ? parseInt(rifaData.boletos_maximos) : null,
        max_boletos_por_persona: rifaData.max_boletos_por_persona ? parseInt(rifaData.max_boletos_por_persona) : 10,
        fecha_inicio: rifaData.fecha_inicio,
        fecha_fin: rifaData.fecha_fin,
        fecha_sorteo: rifaData.fecha_sorteo || null,
        categoria_id: rifaData.categoria_id || null,
        tipo: rifaData.tipo || 'actual',
        estado: rifaData.estado,
        orden: rifaData.orden ? parseInt(rifaData.orden) : 1,
        es_destacada: rifaData.es_destacada === 'true' || rifaData.es_destacada === true,
        imagen_principal: rifaData.imagen_principal || null,
        terminos_condiciones: rifaData.terminos_condiciones || null,
        notas_admin: rifaData.notas_admin || null,
        premios: rifaData.premios?.map(premio => ({
          codigo: premio.codigo,
          titulo: premio.titulo,
          descripcion: premio.descripcion,
          imagen_principal: premio.imagen_principal,
          estado: premio.estado || 'bloqueado',
          orden: premio.orden,
          notas_admin: premio.notas_admin,
          niveles: premio.niveles?.map(nivel => ({
            codigo: nivel.codigo,
            titulo: nivel.titulo,
            descripcion: nivel.descripcion,
            tickets_necesarios: parseInt(nivel.tickets_necesarios),
            valor_aproximado: nivel.valor_aproximado ? parseFloat(nivel.valor_aproximado) : 0,
            imagen: nivel.imagen,
            es_actual: nivel.es_actual === 'true' || nivel.es_actual === true,
            especificaciones: nivel.especificaciones,
            orden: nivel.orden
          })) || []
        })) || []
      }

      const response = await apiClient.put(`/admin/rifas/${rifaId}`, dataToSend)
      console.log('Respuesta actualizar rifa:', response)
      
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al actualizar rifa')
    } catch (error) {
      console.error('Error al actualizar rifa:', error)
      throw error
    }
  }

  // Eliminar rifa
  async deleteRifa(rifaId) {
    try {
      const response = await apiClient.delete(`/admin/rifas/${rifaId}`)
      console.log('Respuesta eliminar rifa:', response)
      
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al eliminar rifa')
    } catch (error) {
      console.error('Error al eliminar rifa:', error)
      throw error
    }
  }

  // Obtener estadísticas de rifas
  async getEstadisticas() {
    try {
      const response = await apiClient.get('/admin/rifas/estadisticas')
      console.log('Respuesta estadísticas rifas:', response)
      
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al obtener estadísticas')
    } catch (error) {
      console.error('Error al obtener estadísticas:', error)
      throw error
    }
  }

  // Cambiar estado de rifa
  async changeEstado(rifaId, nuevoEstado) {
    try {
      const response = await apiClient.patch(`/admin/rifas/${rifaId}/estado`, {
        estado: nuevoEstado
      })
      console.log('Respuesta cambiar estado:', response)
      
      if (response.success) {
        return response.data
      }
      throw new Error(response.message || 'Error al cambiar estado')
    } catch (error) {
      console.error('Error al cambiar estado:', error)
      throw error
    }
  }

  // Exportar datos de rifas
  async exportRifas(filtros = {}) {
    try {
      const response = await apiClient.get('/admin/rifas/exportar', {
        params: filtros,
        responseType: 'blob'
      })
      
      // Crear y descargar archivo
      const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
      const url = window.URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.download = `rifas_${new Date().toISOString().split('T')[0]}.xlsx`
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      window.URL.revokeObjectURL(url)
      
      return true
    } catch (error) {
      console.error('Error al exportar rifas:', error)
      throw error
    }
  }
}

export const adminRifaService = new AdminRifaService()
