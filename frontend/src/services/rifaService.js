// Interfaz para el servicio de rifas (Interface Segregation Principle)
export class RifaService {
  constructor() {
    this.rifasData = [
      {
        id: "1",
        nombre: "PlayStation 5 y Accesorios",
        descripcion: "Rifa actual con sistema de premios progresivos",
        imagen: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=600&h=400&fit=crop&crop=center",
        precio: 2,
        ticketsVendidos: 47,
        ticketsMinimos: 75,
        fechaSorteo: "2025-01-29",
        estado: "en_venta",
        tipo: "actual",
        premios: [
          {
            id: "p1",
            titulo: "Primer Premio - Accesorios Gaming",
            descripcion: "Auriculares Gaming de alta calidad y controles adicionales",
            imagen: "https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=300&fit=crop&crop=center",
            orden: 1,
            premio_requerido_id: null,
            premio_requerido: null,
            mediaGallery: {
              images: [
                {
                  url: "https://images.unsplash.com/photo-1599669454699-248893623440?w=800&h=600&fit=crop",
                  alt: "Auriculares Gaming HyperX",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=800&h=600&fit=crop",
                  alt: "Control DualSense PlayStation 5",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&h=600&fit=crop",
                  alt: "Setup Gaming Completo",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4",
                  thumbnail: "https://images.unsplash.com/photo-1599669454699-248893623440?w=400&h=300&fit=crop",
                  title: "Review Auriculares Gaming",
                  duration: "2:15"
                }
              ]
            },
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
            premio_requerido: "Primer Premio - Accesorios Gaming",
            mediaGallery: {
              images: [
                {
                  url: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&h=600&fit=crop",
                  alt: "God of War Ragnarök",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1511512578047-dfb367046420?w=800&h=600&fit=crop",
                  alt: "The Last of Us Part I",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1493711662062-fa541adb3fc8?w=800&h=600&fit=crop",
                  alt: "Marvel's Spider-Man 2",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&h=600&fit=crop",
                  alt: "PlayStation 5 Console",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_2mb.mp4",
                  thumbnail: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&h=300&fit=crop",
                  title: "God of War Ragnarök - Gameplay",
                  duration: "3:42"
                },
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4",
                  thumbnail: "https://images.unsplash.com/photo-1511512578047-dfb367046420?w=400&h=300&fit=crop",
                  title: "The Last of Us Part I - Trailer",
                  duration: "2:28"
                }
              ]
            },
            niveles: [
              {
                id: "n3",
                titulo: "God of War Ragnarök",
                descripcion: "El épico juego de aventuras nórdicas",
                tickets_necesarios: 45,
                valor_aproximado: 60
              },
              {
                id: "n4",
                titulo: "The Last of Us Part I",
                descripcion: "Remasterización completa del clásico",
                tickets_necesarios: 50,
                valor_aproximado: 70
              },
              {
                id: "n5",
                titulo: "Marvel's Spider-Man 2",
                descripcion: "La aventura definitiva del hombre araña",
                tickets_necesarios: 60,
                valor_aproximado: 70
              }
            ]
          },
          {
            id: "p3",
            titulo: "Premio Principal - PlayStation 5",
            descripcion: "La consola más avanzada de Sony",
            imagen: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=300&fit=crop&crop=center",
            orden: 3,
            premio_requerido_id: "p2", 
            premio_requerido: "Segundo Premio - Juegos Exclusivos",
            mediaGallery: {
              images: [
                {
                  url: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=800&h=600&fit=crop",
                  alt: "PlayStation 5 Standard Edition",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1599669454699-248893623440?w=800&h=600&fit=crop",
                  alt: "PlayStation 5 - Accesorios incluidos",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=800&h=600&fit=crop",
                  alt: "PlayStation 5 - Juegos incluidos",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=800&h=600&fit=crop",
                  alt: "PlayStation 5 - Setup completo",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_5mb.mp4",
                  thumbnail: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=300&fit=crop",
                  title: "PlayStation 5 - Características",
                  duration: "4:32"
                }
              ]
            },
            niveles: [
              {
                id: "n6",
                titulo: "PlayStation 5 Standard",
                descripcion: "Consola completa con lector de discos",
                tickets_necesarios: 75,
                valor_aproximado: 500
              }
            ]
          }
        ]
      },
      {
        id: "2",
        nombre: "Perfume Dior Sauvage 100ml",
        descripcion: "Fragancia masculina fresca y especiada, con notas de bergamota, pimienta de Sichuan y ambroxan",
        imagen: "https://media.falabella.com/falabellaPE/16998948_3/w=800,h=800,fit=pad",
        precio: 2,
        ticketsVendidos: 0,
        ticketsMinimos: 50,
        fechaSorteo: "2025-01-28",
        estado: "bloqueada", // Se habilita cuando se complete la rifa 1
        tipo: "futura",
        premiosDesbloqueados: 0,
        totalPremios: 1,
        requisito: "1",
        orden: 2,
        premios: [
          {
            id: "p4",
            titulo: "Premio Principal - Perfume Dior Sauvage",
            descripcion: "Fragancia masculina de 100ml con notas frescas y especiadas",
            imagen: "https://media.falabella.com/falabellaPE/16998948_3/w=800,h=800,fit=pad",
            orden: 1,
            premio_requerido_id: null,
            premio_requerido: null,
            mediaGallery: {
              images: [
                {
                  url: "https://media.falabella.com/falabellaPE/16998948_3/w=800,h=800,fit=pad",
                  alt: "Dior Sauvage - Frasco completo",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1541643600914-78b084683601?w=800&h=600&fit=crop",
                  alt: "Dior Sauvage - Vista lateral",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1594035910387-fea47794261f?w=800&h=600&fit=crop",
                  alt: "Dior Sauvage - Atomizador",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4",
                  thumbnail: "https://media.falabella.com/falabellaPE/16998948_3/w=800,h=800,fit=pad",
                  title: "Campaña Dior Sauvage",
                  duration: "1:30"
                }
              ]
            },
            niveles: [
              {
                id: "n7",
                titulo: "Perfume Dior Sauvage 100ml",
                descripcion: "Fragancia masculina completa con empaque premium",
                tickets_necesarios: 50,
                valor_aproximado: 120
              }
            ]
          }
        ]
      },
      {
        id: "3",
        nombre: "iPhone 15 128GB",
        descripcion: "El nuevo iPhone 15 con USB-C, cámara de 48MP y chip A16 Bionic",
        imagen: "https://pe.tiendasishop.com/cdn/shop/files/IMG-10935051_c217d008-b374-4d0d-adbf-16768c816296.jpg?v=1722624560&width=823",
        precio: 2,
        ticketsVendidos: 0,
        ticketsMinimos: 150,
        fechaSorteo: "2025-02-05",
        estado: "bloqueada", // Se habilita cuando se complete la rifa 2
        tipo: "futura",
        premiosDesbloqueados: 0,
        totalPremios: 2,
        requisito: "2",
        orden: 3,
        premios: [
          {
            id: "p5",
            titulo: "Primer Premio - Accesorios iPhone",
            descripcion: "AirPods y accesorios oficiales para iPhone",
            imagen: "https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=400&h=300&fit=crop",
            orden: 1,
            premio_requerido_id: null,
            premio_requerido: null,
            mediaGallery: {
              images: [
                {
                  url: "https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=800&h=600&fit=crop",
                  alt: "AirPods Pro 2da Generación",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1572569511254-d8f925fe2cbb?w=800&h=600&fit=crop",
                  alt: "Cargador MagSafe",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&h=600&fit=crop",
                  alt: "Funda iPhone Premium",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_2mb.mp4",
                  thumbnail: "https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?w=400&h=300&fit=crop",
                  title: "AirPods Pro - Características",
                  duration: "1:45"
                }
              ]
            },
            niveles: [
              {
                id: "n8",
                titulo: "AirPods Pro 2da Gen",
                descripcion: "Auriculares con cancelación de ruido activa",
                tickets_necesarios: 75,
                valor_aproximado: 250
              }
            ]
          },
          {
            id: "p6",
            titulo: "Premio Principal - iPhone 15",
            descripcion: "iPhone 15 de 128GB con todas sus características avanzadas",
            imagen: "https://pe.tiendasishop.com/cdn/shop/files/IMG-10935051_c217d008-b374-4d0d-adbf-16768c816296.jpg?v=1722624560&width=823",
            orden: 2,
            premio_requerido_id: "p5",
            premio_requerido: "Primer Premio - Accesorios iPhone",
            mediaGallery: {
              images: [
                {
                  url: "https://pe.tiendasishop.com/cdn/shop/files/IMG-10935051_c217d008-b374-4d0d-adbf-16768c816296.jpg?v=1722624560&width=823",
                  alt: "iPhone 15 - Vista frontal",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&h=600&fit=crop",
                  alt: "iPhone 15 - Colores disponibles",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&h=600&fit=crop",
                  alt: "iPhone 15 - Sistema de cámaras",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1556656793-08538906a9f8?w=800&h=600&fit=crop",
                  alt: "iPhone 15 - Accesorios incluidos",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_5mb.mp4",
                  thumbnail: "https://pe.tiendasishop.com/cdn/shop/files/IMG-10935051_c217d008-b374-4d0d-adbf-16768c816296.jpg?v=1722624560&width=823",
                  title: "iPhone 15 - Presentación oficial",
                  duration: "2:30"
                }
              ]
            },
            niveles: [
              {
                id: "n9",
                titulo: "iPhone 15 128GB",
                descripcion: "iPhone completo con chip A16 Bionic y cámara de 48MP",
                tickets_necesarios: 150,
                valor_aproximado: 800
              }
            ]
          }
        ]
      },
      {
        id: "4",
        nombre: "Smart Watch Series 9",
        descripcion: "Apple Watch Series 9 con GPS, pantalla Always-On Retina y nuevas funciones de salud",
        imagen: "https://oechsle.vteximg.com.br/arquivos/ids/15748293-1000-1000/image-1d379c72d9244b4194d2d185fd70cb4d.jpg?v=638280695205200000",
        precio: 2,
        ticketsVendidos: 0,
        ticketsMinimos: 50,
        fechaSorteo: "2025-02-10",
        estado: "bloqueada", // Se habilita cuando se complete la rifa 3
        tipo: "futura",
        premiosDesbloqueados: 0,
        totalPremios: 1,
        requisito: "3",
        orden: 4,
        premios: [
          {
            id: "p7",
            titulo: "Premio Principal - Apple Watch Series 9",
            descripcion: "Smartwatch con todas las funciones avanzadas de salud y fitness",
            imagen: "https://oechsle.vteximg.com.br/arquivos/ids/15748293-1000-1000/image-1d379c72d9244b4194d2d185fd70cb4d.jpg?v=638280695205200000",
            orden: 1,
            premio_requerido_id: null,
            premio_requerido: null,
            mediaGallery: {
              images: [
                {
                  url: "https://oechsle.vteximg.com.br/arquivos/ids/15748293-1000-1000/image-1d379c72d9244b4194d2d185fd70cb4d.jpg?v=638280695205200000",
                  alt: "Apple Watch Series 9 - Vista principal",
                  isMain: true
                },
                {
                  url: "https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=800&h=600&fit=crop",
                  alt: "Apple Watch Series 9 - Correas disponibles",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop",
                  alt: "Apple Watch Series 9 - Funciones de salud",
                  isMain: false
                },
                {
                  url: "https://images.unsplash.com/photo-1579586337278-3f436f25d4d1?w=800&h=600&fit=crop",
                  alt: "Apple Watch Series 9 - Apps integradas",
                  isMain: false
                }
              ],
              videos: [
                {
                  url: "https://sample-videos.com/zip/10/mp4/SampleVideo_720x480_1mb.mp4",
                  thumbnail: "https://oechsle.vteximg.com.br/arquivos/ids/15748293-1000-1000/image-1d379c72d9244b4194d2d185fd70cb4d.jpg?v=638280695205200000",
                  title: "Apple Watch Series 9 - Características",
                  duration: "1:45"
                }
              ]
            },
            niveles: [
              {
                id: "n10",
                titulo: "Apple Watch Series 9 45mm",
                descripcion: "Smartwatch completo con todas las funciones premium",
                tickets_necesarios: 50,
                valor_aproximado: 400
              }
            ]
          }
        ]
      },
    ]
  }

  // Single Responsibility Principle: cada método tiene una responsabilidad específica
  async getAllRifas() {
    return new Promise((resolve) => {
      setTimeout(() => {
        // Solo retorna rifas que no estén bloqueadas
        const rifasDisponibles = this.rifasData.filter(rifa => rifa.estado !== 'bloqueada')
        resolve(rifasDisponibles)
      }, 100)
    })
  }

  async getAllRifasWithBlocked() {
    return new Promise((resolve) => {
      setTimeout(() => {
        // Retorna todas las rifas incluyendo las bloqueadas (para admin o preview)
        resolve(this.rifasData)
      }, 100)
    })
  }

  async getRifaById(id) {
    return new Promise((resolve, reject) => {
      setTimeout(() => {
        const rifa = this.rifasData.find(r => r.id === id)
        if (rifa) {
          // Procesar premios con estados calculados
          const premiosConEstados = this.calcularEstadosPremios(rifa)
          resolve({
            ...rifa,
            premios: premiosConEstados
          })
        } else {
          reject(new Error(`Rifa with id ${id} not found`))
        }
      }, 100)
    })
  }

  // Método para calcular los estados de los premios
  calcularEstadosPremios(rifa) {
    if (!rifa.premios) return []

    const ticketsVendidos = rifa.ticketsVendidos || 0
    
    return rifa.premios.map((premio, premioIndex) => {
      // Para el primer premio, siempre está desbloqueado
      let desbloqueado = premioIndex === 0
      
      // Para premios subsecuentes, se desbloquea cuando el anterior está completado
      if (premioIndex > 0) {
        const premioAnterior = rifa.premios[premioIndex - 1]
        if (premioAnterior && premioAnterior.niveles) {
          const ultimoNivelAnterior = premioAnterior.niveles[premioAnterior.niveles.length - 1]
          desbloqueado = ticketsVendidos >= ultimoNivelAnterior.tickets_necesarios
        }
      }

      // Calcular si está completado
      let completado = false
      if (premio.niveles && premio.niveles.length > 0) {
        const ultimoNivel = premio.niveles[premio.niveles.length - 1]
        completado = ticketsVendidos >= ultimoNivel.tickets_necesarios
      }

      // Calcular si está activo (desbloqueado pero no completado)
      const esta_activo = desbloqueado && !completado

      // Calcular estado del texto
      let estado_texto = 'Bloqueado'
      if (completado) {
        estado_texto = 'Completado'
      } else if (esta_activo) {
        estado_texto = 'En Progreso'
      }

      // Procesar niveles con estados
      const nivelesConEstados = premio.niveles ? premio.niveles.map(nivel => ({
        ...nivel,
        desbloqueado: ticketsVendidos >= nivel.tickets_necesarios,
        es_actual: !completado && desbloqueado && 
                   ticketsVendidos < nivel.tickets_necesarios &&
                   (nivel.id === premio.niveles.find(n => ticketsVendidos < n.tickets_necesarios)?.id)
      })) : []

      return {
        ...premio,
        desbloqueado,
        completado,
        esta_activo,
        estado_texto,
        niveles: nivelesConEstados
      }
    })
  }

  async getRifasByFilter(filter) {
    return new Promise((resolve) => {
      setTimeout(() => {
        if (filter === 'todas') {
          resolve(this.rifasData)
        } else {
          const filtered = this.rifasData.filter(rifa => rifa.estado === filter)
          resolve(filtered)
        }
      }, 100)
    })
  }

  async participateInRifa(rifaId, paymentData) {
    return new Promise((resolve) => {
      setTimeout(() => {
        // Simulación de proceso de pago
        const result = {
          success: true,
          paymentCode: this.generatePaymentCode()
        }

        // Simular venta de ticket
        this.simulateTicketSale(rifaId)

        resolve(result)
      }, 1000)
    })
  }

  // Simula la venta de un ticket y verifica si se debe desbloquear la siguiente rifa
  simulateTicketSale(rifaId) {
    // Actualizar tickets vendidos en rifasData
    const rifaIndex = this.rifasData.findIndex(r => r.id === rifaId)
    if (rifaIndex !== -1) {
      this.rifasData[rifaIndex].ticketsVendidos += 1
      
      // Verificar si se completó la rifa
      const rifa = this.rifasData[rifaIndex]
      if (rifa.ticketsVendidos >= rifa.ticketsMinimos) {
        this.completeRifa(rifaId)
      }
    }
  }

  // Completa una rifa y desbloquea la siguiente
  completeRifa(rifaId) {
    // Cambiar estado de la rifa actual a "confirmada" o "sorteada"
    const rifaIndex = this.rifasData.findIndex(r => r.id === rifaId)
    if (rifaIndex !== -1) {
      this.rifasData[rifaIndex].estado = 'confirmada'
    }

    // Desbloquear la siguiente rifa
    this.unlockNextRifa(rifaId)
  }

  // Desbloquea la siguiente rifa en la secuencia
  unlockNextRifa(completedRifaId) {
    // Buscar qué rifa tiene como requisito la rifa completada
    const nextRifaIndex = this.rifasData.findIndex(r => r.requisito === completedRifaId)
    
    if (nextRifaIndex !== -1) {
      this.rifasData[nextRifaIndex].estado = 'en_venta'

      console.log(`🎉 ¡Nueva rifa desbloqueada: ${this.rifasData[nextRifaIndex].nombre}!`)
    }
  }

  // Obtener el estado de desbloqueo de las rifas
  getRifasUnlockStatus() {
    return this.rifasData.map(rifa => ({
      id: rifa.id,
      nombre: rifa.nombre,
      estado: rifa.estado,
      requisito: rifa.requisito,
      orden: rifa.orden,
      isUnlocked: rifa.estado !== 'bloqueada'
    }))
  }

  generatePaymentCode() {
    // Generar número de sesión de rifa (normalmente sería de base de datos)
    const rifaSessionNumber = 1; // Aquí iría la lógica para obtener el número de sesión actual
    
    // Generar código aleatorio de 8 caracteres
    const randomCode = Math.random().toString(36).substring(2, 10).toUpperCase()
    
    return `RIFA${rifaSessionNumber}-${randomCode}`
  }
}

// Singleton instance (Dependency Inversion Principle)
export const rifaService = new RifaService()
