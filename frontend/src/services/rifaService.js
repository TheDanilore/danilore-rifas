import apiClient from './api.js'
import { backendConfig } from '../config/backend.js'

// Interfaz para el servicio de rifas (Interface Segregation Principle)
export class RifaService {
  constructor() {
    // Configuración para usar API real o datos simulados
    this.useRealAPI = true // Cambiar a false para usar datos simulados durante desarrollo
    // URL base para imágenes del backend - ahora centralizada
    this.imageBaseURL = backendConfig.BASE_URL
  }

  /**
   * Procesa una URL de imagen para asegurar que apunte al backend
   */
  processImageURL(imagePath) {
    if (!imagePath) return null
    
    // Si ya es una URL completa, devolverla tal como está
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath
    }
    
    // Si es una ruta relativa, agregar la URL base del backend
    // Asegurar que comience con /
    const cleanPath = imagePath.startsWith('/') ? imagePath : `/${imagePath}`
    return `${this.imageBaseURL}${cleanPath}`
  }

  // Single Responsibility Principle: cada método tiene una responsabilidad específica
  async getAllRifas() {
    if (this.useRealAPI) {
      try {
        const response = await apiClient.get('/rifas')
        
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
        
        if (response.success) {
          // Extraer los datos de la estructura de respuesta
          const premioData = response.data.premio
          const rifaData = response.data.rifa
          
          // Formatear el premio para procesar URLs de imágenes
          const premioFormateado = this.formatearPremio(premioData)
          
          // Devolver tanto el premio como la rifa
          return {
            premio: premioFormateado,
            rifa: rifaData
          }
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
      codigo: rifaAPI.codigo_unico, // Alias para compatibilidad
      nombre: rifaAPI.titulo,
      titulo: rifaAPI.titulo, // Alias para compatibilidad
      descripcion: rifaAPI.descripcion,
      imagen: rifaAPI.imagen || rifaAPI.imagen_principal, // Usar imagen procesada del backend
      precio: rifaAPI.precio_boleto,
      ticketsVendidos: rifaAPI.ticketsVendidos || rifaAPI.boletos_vendidos || 0,
      boletos_vendidos: rifaAPI.boletos_vendidos || 0, // Alias para compatibilidad
      ticketsMinimos: rifaAPI.ticketsMinimos || rifaAPI.boletos_minimos,
      boletos_minimos: rifaAPI.boletos_minimos, // Alias para compatibilidad
      ticketsMaximos: rifaAPI.boletos_maximos,
      boletos_maximos: rifaAPI.boletos_maximos, // Alias para compatibilidad
      fechaSorteo: rifaAPI.fechaSorteo || rifaAPI.fecha_sorteo,
      fecha_sorteo: rifaAPI.fecha_sorteo, // Alias para compatibilidad
      fechaInicio: rifaAPI.fecha_inicio,
      fechaFin: rifaAPI.fecha_fin,
      estado: rifaAPI.estado,
      tipo: rifaAPI.tipo || (rifaAPI.estado === 'activa' ? 'actual' : 'futura'),
      categoria: rifaAPI.categoria,
      premios: rifaAPI.premios || [],
      destacada: rifaAPI.es_destacada || false,
      activa: rifaAPI.estado === 'activa',
      // Información adicional para compatibilidad con RifaDetail
      mediaGallery: this.processMediaGallery(rifaAPI.media_gallery || []),
      imagenes_adicionales: rifaAPI.imagenes_adicionales || [],
      terminos_condiciones: rifaAPI.terminos_condiciones,
      max_boletos_por_persona: rifaAPI.max_boletos_por_persona,
      
      // Datos de progreso calculados por el backend
      progreso_general: rifaAPI.progreso_general || null,
      niveles_completados_general: rifaAPI.niveles_completados_general || 0,
      total_niveles_general: rifaAPI.total_niveles_general || 0,
      porcentaje_general: rifaAPI.porcentaje_general || 0
    }
  }

  /**
   * Formatear premio de la API para el frontend
   */
  formatearPremio(premioAPI) {
    return {
      id: premioAPI.id?.toString() || '',
      codigo: premioAPI.codigo || '',
      titulo: premioAPI.titulo || '',
      descripcion: premioAPI.descripcion || '',
      imagen: premioAPI.imagen || premioAPI.imagen_principal, // Usar imagen procesada del backend
      imagen_principal: premioAPI.imagen || premioAPI.imagen_principal, // Mantener ambos para compatibilidad
      orden: premioAPI.orden || 0,
      estado: premioAPI.estado || 'bloqueado',
      
      // Propiedades calculadas del backend
      desbloqueado: Boolean(premioAPI.desbloqueado), 
      completado: Boolean(premioAPI.completado), 
      esta_activo: Boolean(premioAPI.esta_activo),
      estado_texto: premioAPI.estado_texto || '',
      premio_requerido: premioAPI.premio_requerido || '',
      
      premio_requerido_id: premioAPI.premio_requerido_id?.toString(),
      media_gallery: premioAPI.media_gallery || [],
      notas_admin: premioAPI.notas_admin || '',
      niveles: premioAPI.niveles || []
    }
  }

  /**
   * Formatear nivel de la API para el frontend
   */
  formatearNivel(nivelAPI) {
    return {
      id: nivelAPI.id?.toString() || '',
      codigo: nivelAPI.codigo || '',
      titulo: nivelAPI.titulo || '',
      nombre: nivelAPI.nombre || nivelAPI.titulo || '', // Alias para compatibilidad
      descripcion: nivelAPI.descripcion || '',
      tickets_necesarios: nivelAPI.tickets_necesarios || 0,
      valor_aproximado: nivelAPI.valor_aproximado || 0,
      imagen: nivelAPI.imagen, // Usar imagen procesada del backend
      orden: nivelAPI.orden || 0,
      especificaciones: nivelAPI.especificaciones || '',
      
      // Propiedades calculadas del backend
      es_actual: Boolean(nivelAPI.es_actual),
      desbloqueado: Boolean(nivelAPI.desbloqueado)
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
          // El API devuelve response.data.rifa, no response.data directamente
          return this.formatearRifa(response.data.rifa)
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
      // Calcular estados de niveles basado en tickets vendidos reales
      const nivelesConEstado = premio.niveles?.map(nivel => {
        const nivelCompletado = rifa.ticketsVendidos >= nivel.tickets_necesarios
        const nivelActual = !nivelCompletado && (
          premio.niveles.filter(n => n.orden < nivel.orden && rifa.ticketsVendidos >= n.tickets_necesarios).length === nivel.orden - 1
        )
        
        return {
          ...nivel,
          completado: nivelCompletado,
          es_actual: nivelActual,
          progreso: Math.min((rifa.ticketsVendidos / nivel.tickets_necesarios) * 100, 100),
          tickets_restantes: Math.max(0, nivel.tickets_necesarios - rifa.ticketsVendidos)
        }
      }) || []

      // El premio está completado si todos sus niveles están completados
      const completado = nivelesConEstado.length > 0 && nivelesConEstado.every(n => n.completado)
      
      // El premio tiene al menos un nivel completado
      const tieneProgreso = nivelesConEstado.some(n => n.completado)

      return {
        ...premio,
        codigo: premio.codigo || `p${index + 1}`,
        completado,
        tiene_progreso: tieneProgreso,
        activo: false, // Se calculará después
        bloqueado: true, // Se calculará después  
        desbloqueado: false, // Se calculará después
        esta_activo: false, // Se calculará después
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
        const premioRequerido = premiosConEstado.find(p => 
          p.id?.toString() === premio.premio_requerido_id?.toString()
        )
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
      const esta_activo = desbloqueado && !premio.completado

      // Estado texto para mostrar en UI
      let estado_texto = 'Bloqueado'
      if (desbloqueado) {
        if (premio.completado) {
          estado_texto = 'Completado'
        } else if (premio.tiene_progreso) {
          estado_texto = 'En Progreso'
        } else {
          estado_texto = 'Activo'
        }
      }

      const premioFinal = {
        ...premio,
        activo: esta_activo,
        esta_activo,
        bloqueado,
        desbloqueado,
        estado_texto
      }

      console.log(`Premio ${premio.titulo}:`, {
        completado: premioFinal.completado,
        desbloqueado: premioFinal.desbloqueado,
        bloqueado: premioFinal.bloqueado,
        esta_activo: premioFinal.esta_activo,
        estado_texto: premioFinal.estado_texto
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

  /**
   * Procesar media_gallery del backend para formato esperado por MediaGallery
   */
  processMediaGallery(mediaGallery) {
    if (!Array.isArray(mediaGallery)) {
      return { images: [], videos: [] }
    }

    const images = []
    const videos = []

    mediaGallery.forEach((media, index) => {
      if (typeof media === 'string') {
        // Es una URL directa - determinar tipo por extensión
        const isVideo = /\.(mp4|mov|avi|webm|mkv)$/i.test(media)
        
        if (isVideo) {
          videos.push({
            id: `video-${index}`,
            url: media,
            title: `Video ${index + 1}`,
            thumbnail: null,
            duration: null
          })
        } else {
          images.push({
            id: `image-${index}`,
            url: media,
            alt: `Imagen ${index + 1}`,
            title: `Imagen ${index + 1}`
          })
        }
      } else if (typeof media === 'object' && media.url) {
        // Es un objeto con estructura
        const isVideo = media.type === 'video' || /\.(mp4|mov|avi|webm|mkv)$/i.test(media.url)
        
        if (isVideo) {
          videos.push({
            id: media.id || `video-${index}`,
            url: media.url,
            title: media.title || `Video ${index + 1}`,
            thumbnail: media.thumbnail || null,
            duration: media.duration || null
          })
        } else {
          images.push({
            id: media.id || `image-${index}`,
            url: media.url,
            alt: media.alt || `Imagen ${index + 1}`,
            title: media.title || `Imagen ${index + 1}`
          })
        }
      }
    })

    return { images, videos }
  }
}

export const rifaService = new RifaService()
