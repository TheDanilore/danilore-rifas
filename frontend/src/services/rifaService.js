import apiClient from './api.js'

// Interfaz para el servicio de rifas (Interface Segregation Principle)
export class RifaService {
  constructor() {
    // Configuración para usar API real o datos simulados
    this.useRealAPI = true // Cambiar a false para usar datos simulados durante desarrollo
  }

  // Single Responsibility Principle: cada método tiene una responsabilidad específica
  async getAllRifas() {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get('/rifas')
        console.log('Respuesta completa de API rifas:', response)
        
        if (response.success) {
          // Laravel devuelve datos paginados: {data: array, current_page, total, etc}
          const rifasArray = response.data?.data || response.data
          
          if (Array.isArray(rifasArray)) {
            return rifasArray.map(rifa => this.formatearRifa(rifa))
          } else {
            console.warn('No se encontró array de rifas en la respuesta:', response.data)
            throw new Error('Formato de respuesta inesperado')
          }
        }
        throw new Error(response.message || 'Error al obtener rifas')
      } catch (error) {
        console.error('Error al obtener rifas:', error)
        console.error('Detalles del error:', error.response?.data || error.message)
        // Fallback a datos simulados si la API falla
        return this.getRifasSimuladas()
      }
    }
    return this.getRifasSimuladas()
  }

  async getRifasActuales() {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get('/rifas/actuales')
        console.log('Respuesta API rifas actuales:', response)
        
        if (response.success) {
          const rifasArray = response.data?.data || response.data
          if (Array.isArray(rifasArray)) {
            return rifasArray.map(rifa => this.formatearRifa(rifa))
          }
        }
        throw new Error(response.message || 'Error al obtener rifas actuales')
      } catch (error) {
        console.error('Error al obtener rifas actuales:', error)
        return this.getRifasSimuladas().filter(rifa => rifa.tipo === 'actual')
      }
    }
    return this.getRifasSimuladas().filter(rifa => rifa.tipo === 'actual')
  }

  async getRifasFuturas() {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get('/rifas/futuras')
        console.log('Respuesta API rifas futuras:', response)
        
        if (response.success) {
          const rifasArray = response.data?.data || response.data
          if (Array.isArray(rifasArray)) {
            return rifasArray.map(rifa => this.formatearRifa(rifa))
          }
        }
        throw new Error(response.message || 'Error al obtener rifas futuras')
      } catch (error) {
        console.error('Error al obtener rifas futuras:', error)
        return this.getRifasSimuladas().filter(rifa => rifa.tipo === 'futura')
      }
    }
    return this.getRifasSimuladas().filter(rifa => rifa.tipo === 'futura')
  }

  async getRifasDestacadas() {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get('/rifas/destacadas')
        console.log('Respuesta API rifas destacadas:', response)
        
        if (response.success) {
          const rifasArray = response.data?.data || response.data
          if (Array.isArray(rifasArray)) {
            return rifasArray.map(rifa => this.formatearRifa(rifa))
          }
        }
        throw new Error(response.message || 'Error al obtener rifas destacadas')
      } catch (error) {
        console.error('Error al obtener rifas destacadas:', error)
        return this.getRifasSimuladas().filter(rifa => rifa.destacada)
      }
    }
    return this.getRifasSimuladas().filter(rifa => rifa.destacada)
  }

  async getRifaByCode(codigo) {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get(`/rifas/${codigo}`)
        if (response.success) {
          return this.formatearRifa(response.data)
        }
        throw new Error(response.message || 'Rifa no encontrada')
      } catch (error) {
        console.error('Error al obtener rifa:', error)
        return this.getRifasSimuladas().find(rifa => rifa.codigo_unico === codigo)
      }
    }
    return this.getRifasSimuladas().find(rifa => rifa.codigo_unico === codigo)
  }

  async getRifaProgreso(codigo) {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get(`/rifas/${codigo}/progreso`)
        if (response.success) {
          return response.data
        }
        throw new Error(response.message || 'Error al obtener progreso')
      } catch (error) {
        console.error('Error al obtener progreso:', error)
        // Fallback a cálculo local
        const rifa = this.getRifasSimuladas().find(r => r.codigo_unico === codigo)
        return this.calcularProgreso(rifa)
      }
    }
    const rifa = this.getRifasSimuladas().find(r => r.codigo_unico === codigo)
    return this.calcularProgreso(rifa)
  }

  async getPremioDetail(rifaId, codigoPremio) {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get(`/premios/${rifaId}/${codigoPremio}`)
        console.log('Respuesta API premio detail:', response)
        
        if (response.success) {
          return response.data
        }
        throw new Error(response.message || 'Premio no encontrado')
      } catch (error) {
        console.error('Error al obtener detalle del premio:', error)
        throw error
      }
    }
    // Fallback para datos simulados si es necesario
    throw new Error('API no disponible')
  }

  /**
   * Formatear rifa de la API para el frontend
   */
  formatearRifa(rifaAPI) {
    return {
      id: rifaAPI.id.toString(),
      codigo_unico: rifaAPI.codigo_unico,
      nombre: rifaAPI.titulo,
      descripcion: rifaAPI.descripcion,
      imagen: rifaAPI.imagen_principal,
      precio: rifaAPI.precio_boleto,
      ticketsVendidos: rifaAPI.boletos_vendidos || 0,
      ticketsMinimos: rifaAPI.boletos_minimos,
      ticketsMaximos: rifaAPI.boletos_maximos,
      fechaSorteo: rifaAPI.fecha_sorteo,
      fechaInicio: rifaAPI.fecha_inicio,
      fechaFin: rifaAPI.fecha_fin,
      estado: rifaAPI.estado,
      tipo: rifaAPI.tipo || (rifaAPI.estado === 'en_venta' ? 'actual' : 'futura'),
      categoria: rifaAPI.categoria,
      premios: rifaAPI.premios?.map(premio => this.formatearPremio(premio)) || [],
      destacada: rifaAPI.destacada || false,
      activa: rifaAPI.estado === 'en_venta'
    }
  }

  /**
   * Formatear premio de la API para el frontend
   */
  formatearPremio(premioAPI) {
    return {
      id: premioAPI.id.toString(),
      codigo: premioAPI.codigo,
      titulo: premioAPI.titulo,
      descripcion: premioAPI.descripcion,
      imagen: premioAPI.imagen_principal, // Cambiado de premioAPI.imagen a premioAPI.imagen_principal
      imagen_principal: premioAPI.imagen_principal, // Mantener ambos para compatibilidad
      orden: premioAPI.orden,
      estado: premioAPI.estado,
      premio_requerido_id: premioAPI.premio_requerido_id?.toString(),
      media_gallery: premioAPI.media_gallery,
      notas_admin: premioAPI.notas_admin,
      niveles: premioAPI.niveles?.map(nivel => this.formatearNivel(nivel)) || []
    }
  }

  /**
   * Formatear nivel de la API para el frontend
   */
  formatearNivel(nivelAPI) {
    return {
      id: nivelAPI.id.toString(),
      codigo: nivelAPI.codigo,
      titulo: nivelAPI.titulo,
      descripcion: nivelAPI.descripcion,
      tickets_necesarios: nivelAPI.tickets_necesarios,
      valor_aproximado: nivelAPI.valor_aproximado,
      imagen: nivelAPI.imagen,
      orden: nivelAPI.orden,
      es_actual: nivelAPI.es_actual,
      especificaciones: nivelAPI.especificaciones
    }
  }

  /**
   * Calcular progreso de una rifa
   */
  calcularProgreso(rifa) {
    if (!rifa) return null

    const porcentaje = Math.min((rifa.ticketsVendidos / rifa.ticketsMinimos) * 100, 100)
    const ticketsRestantes = Math.max(rifa.ticketsMinimos - rifa.ticketsVendidos, 0)
    
    return {
      tickets_vendidos: rifa.ticketsVendidos,
      tickets_minimos: rifa.ticketsMinimos,
      tickets_restantes: ticketsRestantes,
      porcentaje_completado: porcentaje,
      estado: porcentaje >= 100 ? 'completado' : 'en_progreso'
    }
  }
  // =================== MÉTODOS ADICIONALES ===================

  async getAllRifasWithBlocked() {
    // Método para compatibilidad con el frontend existente
    return await this.getAllRifas()
  }

  async getRifaById(id) {
    if (this.useRealAPI) {
      try {
        // Buscar por código único si es string, por ID si es número
        const endpoint = isNaN(id) ? `/rifas/${id}` : `/rifas/${id}`
        const response = await apiClient.get(endpoint)
        if (response.success) {
          return this.formatearRifa(response.data)
        }
        throw new Error(response.message || 'Rifa no encontrada')
      } catch (error) {
        console.error('Error al obtener rifa:', error)
        return this.getRifasSimuladas().find(rifa => rifa.id === id.toString())
      }
    }
    return this.getRifasSimuladas().find(rifa => rifa.id === id.toString())
  }

  async getRifasByFilter(filter) {
    switch (filter) {
      case 'actual':
        return await this.getRifasActuales()
      case 'futura':
        return await this.getRifasFuturas()
      case 'destacadas':
        return await this.getRifasDestacadas()
      default:
        return await this.getAllRifas()
    }
  }

  // Método para calcular los estados de los premios
  calcularEstadosPremios(rifa) {
    if (!rifa || !rifa.premios) return []

    console.log('Calculando estados de premios para rifa:', rifa.nombre)
    console.log('Tickets vendidos:', rifa.ticketsVendidos)
    console.log('Premios originales:', rifa.premios)

    // Primero calculamos los estados básicos sin dependencias
    const premiosConEstado = rifa.premios.map((premio, index) => {
      // Calcular estados de niveles
      const nivelesConEstado = premio.niveles?.map(nivel => {
        const nivelCompletado = rifa.ticketsVendidos >= nivel.tickets_necesarios
        return {
          ...nivel,
          completado: nivelCompletado
        }
      }) || []

      // El premio está completado si todos sus niveles están completados
      const completado = nivelesConEstado.length > 0 && nivelesConEstado.every(n => n.completado)

      return {
        ...premio,
        codigo: premio.codigo || `p${index + 1}`, // Asignar código por defecto
        completado,
        activo: false, // Se calculará después
        bloqueado: true, // Se calculará después
        desbloqueado: false, // Se calculará después
        niveles: nivelesConEstado
      }
    })

    // Ahora calculamos las dependencias (bloqueado/activo)
    const premiosFinales = premiosConEstado.map(premio => {
      let desbloqueado = false
      let bloqueado = true
      
      // Verificar si el premio requerido está completado
      if (!premio.premio_requerido_id) {
        // Es el primer premio, siempre está desbloqueado
        desbloqueado = true
        bloqueado = false
        console.log(`Premio ${premio.titulo} - Es el primer premio, desbloqueado por defecto`)
      } else {
        // Buscar el premio requerido en la lista ya calculada
        const premioRequerido = premiosConEstado.find(p => p.id === premio.premio_requerido_id)
        if (premioRequerido?.completado) {
          desbloqueado = true
          bloqueado = false
          console.log(`Premio ${premio.titulo} - Desbloqueado porque ${premioRequerido.titulo} está completado`)
        } else {
          desbloqueado = false
          bloqueado = true
          console.log(`Premio ${premio.titulo} - Bloqueado porque ${premioRequerido?.titulo || 'premio requerido'} no está completado`)
        }
      }

      // El premio está activo si está desbloqueado y no está completado
      const activo = desbloqueado && !premio.completado

      const premioFinal = {
        ...premio,
        activo,
        bloqueado,
        desbloqueado
      }

      console.log(`Premio ${premio.titulo}:`, {
        completado: premioFinal.completado,
        desbloqueado: premioFinal.desbloqueado,
        bloqueado: premioFinal.bloqueado,
        activo: premioFinal.activo
      })

      return premioFinal
    })

    console.log('Premios finales calculados:', premiosFinales)
    return premiosFinales
  }

  // =================== MÉTODOS SIMULADOS (PARA DESARROLLO) ===================

  async participateInRifa(rifaId, paymentData) {
    // Simular participación - en producción usará ventaService
    console.log('Participando en rifa:', rifaId, paymentData)
    return {
      success: true,
      ticket: this.generatePaymentCode(),
      message: 'Participación exitosa'
    }
  }

  simulateTicketSale(rifaId) {
    const rifas = this.getRifasSimuladas()
    const rifa = rifas.find(r => r.id === rifaId)
    if (rifa && rifa.ticketsVendidos < rifa.ticketsMinimos) {
      rifa.ticketsVendidos += 1
    }
  }

  completeRifa(rifaId) {
    const rifas = this.getRifasSimuladas()
    const rifa = rifas.find(r => r.id === rifaId)
    if (rifa) {
      rifa.ticketsVendidos = rifa.ticketsMinimos
      rifa.estado = 'completado'
    }
  }

  unlockNextRifa(completedRifaId) {
    const rifas = this.getRifasSimuladas()
    const nextRifa = rifas.find(r => r.tipo === 'futura')
    if (nextRifa) {
      nextRifa.tipo = 'actual'
      nextRifa.estado = 'en_venta'
      nextRifa.activa = true
    }
  }

  getRifasUnlockStatus() {
    return {
      currentRifa: '1',
      unlockedRifas: ['1'],
      nextRifa: '2'
    }
  }

  generatePaymentCode() {
    return Math.random().toString(36).substring(2, 15).toUpperCase()
  }

  // =================== DATOS SIMULADOS ===================

  getRifasSimuladas() {
    return [
      {
        id: "1",
        codigo_unico: "PS5-2025-001",
        nombre: "PlayStation 5 y Accesorios",
        descripcion: "Rifa actual con sistema de premios progresivos",
        imagen: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=600&h=400&fit=crop&crop=center",
        precio: 2,
        ticketsVendidos: 47,
        ticketsMinimos: 75,
        ticketsMaximos: 100,
        fechaSorteo: "2025-01-29",
        fechaInicio: "2025-01-15",
        fechaFin: "2025-01-28",
        estado: "en_venta",
        tipo: "actual",
        destacada: true,
        activa: true,
        categoria: {
          id: 1,
          nombre: "Gaming",
          descripcion: "Productos gaming y tecnología"
        },
        premios: [
          {
            id: "p1",
            titulo: "Primer Premio - Accesorios Gaming",
            descripcion: "Auriculares Gaming de alta calidad y controles adicionales",
            imagen: "https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=300&fit=crop&crop=center",
            orden: 1,
            premio_requerido_id: null,
            niveles: [
              {
                id: "n1",
                titulo: "Auriculares Gaming",
                descripcion: "Auriculares gaming con sonido envolvente 7.1",
                tickets_necesarios: 20,
                valor_aproximado: 150
              },
              {
                id: "n2", 
                titulo: "Control Adicional",
                descripcion: "Control inalámbrico adicional oficial",
                tickets_necesarios: 30,
                valor_aproximado: 80
              }
            ]
          },
          {
            id: "p2",
            titulo: "Segundo Premio - Juegos Exclusivos", 
            descripcion: "Los mejores juegos exclusivos de PlayStation 5",
            imagen: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&h=300&fit=crop&crop=center",
            orden: 2,
            premio_requerido_id: "p1",
            niveles: [
              {
                id: "n3",
                titulo: "God of War Ragnarök",
                descripcion: "El épico juego de acción y aventura",
                tickets_necesarios: 40,
                valor_aproximado: 70
              },
              {
                id: "n4",
                titulo: "The Last of Us Part I",
                descripcion: "La obra maestra remasterizada",
                tickets_necesarios: 50,
                valor_aproximado: 70
              }
            ]
          },
          {
            id: "p3",
            titulo: "Gran Premio - PlayStation 5",
            descripcion: "La consola PlayStation 5 completa",
            imagen: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=300&fit=crop&crop=center",
            orden: 3,
            premio_requerido_id: "p2",
            niveles: [
              {
                id: "n5",
                titulo: "PlayStation 5 Standard",
                descripcion: "Consola PlayStation 5 con lector de discos",
                tickets_necesarios: 75,
                valor_aproximado: 500
              }
            ]
          }
        ]
      },
      {
        id: "2",
        codigo_unico: "IPHONE-2025-001",
        nombre: "iPhone 15 Pro Max",
        descripcion: "El último iPhone con toda la tecnología de Apple",
        imagen: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600&h=400&fit=crop&crop=center",
        precio: 5,
        ticketsVendidos: 0,
        ticketsMinimos: 200,
        ticketsMaximos: 300,
        fechaSorteo: "2025-02-15",
        fechaInicio: "2025-02-01",
        fechaFin: "2025-02-14",
        estado: "futura",
        tipo: "futura",
        destacada: false,
        activa: false,
        categoria: {
          id: 2,
          nombre: "Tecnología",
          descripcion: "Smartphones y dispositivos móviles"
        },
        premios: [
          {
            id: "p4",
            titulo: "Accesorios iPhone",
            descripcion: "Fundas, cargadores y accesorios premium",
            imagen: "https://images.unsplash.com/photo-1572364942961-d890d28e3f48?w=400&h=300&fit=crop&crop=center",
            orden: 1,
            premio_requerido_id: null,
            niveles: [
              {
                id: "n6",
                titulo: "Funda Premium",
                descripcion: "Funda de cuero genuino",
                tickets_necesarios: 50,
                valor_aproximado: 100
              }
            ]
          },
          {
            id: "p5",
            titulo: "iPhone 15 Pro Max",
            descripcion: "iPhone 15 Pro Max 256GB",
            imagen: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=300&fit=crop&crop=center",
            orden: 2,
            premio_requerido_id: "p4",
            niveles: [
              {
                id: "n7",
                titulo: "iPhone 15 Pro Max 256GB",
                descripcion: "El iPhone más avanzado",
                tickets_necesarios: 200,
                valor_aproximado: 1200
              }
            ]
          }
        ]
      }
    ]
  }
}

// Singleton instance (Dependency Inversion Principle)
export const rifaService = new RifaService()
