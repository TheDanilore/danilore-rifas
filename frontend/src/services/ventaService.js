import apiClient from './api.js'

export class VentaService {
  /**
   * Crear una nueva venta (reservar boletos)
   */
  async crearVenta(ventaData) {
    try {
      const response = await apiClient.post('/ventas', {
        rifa_codigo: ventaData.rifa_codigo,
        numeros_boletos: ventaData.numeros_boletos,
        comprador_nombre: ventaData.comprador_nombre,
        comprador_email: ventaData.comprador_email,
        comprador_telefono: ventaData.comprador_telefono,
        comprador_tipo_documento: ventaData.comprador_tipo_documento,
        comprador_numero_documento: ventaData.comprador_numero_documento,
        metodo_pago: ventaData.metodo_pago
      })
      
      if (response.success) {
        return response.data
      }
      
      throw new Error(response.message || 'Error al crear la venta')
    } catch (error) {
      throw error
    }
  }

  /**
   * Confirmar pago de una venta
   */
  async confirmarPago(codigoVenta, pagoData) {
    try {
      const formData = new FormData()
      formData.append('numero_operacion', pagoData.numero_operacion)
      formData.append('monto_pagado', pagoData.monto_pagado)
      formData.append('comprobante', pagoData.comprobante)

      const response = await apiClient.post(`/ventas/${codigoVenta}/confirmar-pago`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      if (response.success) {
        return response.data
      }
      
      throw new Error(response.message || 'Error al confirmar el pago')
    } catch (error) {
      throw error
    }
  }

  /**
   * Obtener detalles de una venta
   */
  async obtenerVenta(codigoVenta) {
    try {
      const response = await apiClient.get(`/ventas/${codigoVenta}`)
      
      if (response.success) {
        return response.data
      }
      
      throw new Error(response.message || 'Venta no encontrada')
    } catch (error) {
      throw error
    }
  }

  /**
   * Obtener ventas del usuario autenticado
   */
  async obtenerMisVentas(page = 1, perPage = 10) {
    try {
      const response = await apiClient.get('/ventas/mis-ventas', {
        params: {
          page,
          per_page: perPage
        }
      })
      
      if (response.success) {
        return response.data
      }
      
      throw new Error(response.message || 'Error al obtener las ventas')
    } catch (error) {
      throw error
    }
  }

  /**
   * Formatear datos de venta para el frontend
   */
  formatearVenta(venta) {
    return {
      id: venta.id,
      codigo: venta.codigo_venta,
      rifaId: venta.rifa_id,
      rifa: venta.rifa,
      cantidadBoletos: venta.cantidad_boletos,
      boletos: venta.boletos,
      subtotal: venta.subtotal,
      total: venta.total,
      estado: venta.estado,
      metodoPago: venta.metodo_pago,
      fechaCreacion: venta.created_at,
      fechaExpiracion: venta.fecha_expiracion,
      fechaPago: venta.fecha_pago,
      montoPagado: venta.monto_pagado,
      comprobantePago: venta.comprobante_pago,
      referenciaPago: venta.referencia_pago,
      comprador: {
        nombre: venta.comprador_nombre,
        email: venta.comprador_email,
        telefono: venta.comprador_telefono,
        tipoDocumento: venta.comprador_tipo_documento,
        numeroDocumento: venta.comprador_numero_documento
      },
      pagos: venta.pagos || []
    }
  }
}

// Singleton instance
export const ventaService = new VentaService()
