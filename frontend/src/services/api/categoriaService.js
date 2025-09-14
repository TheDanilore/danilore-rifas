import apiClient from './api.js'

export class CategoriaService {
  /**
   * Obtener todas las categorías
   */
  async obtenerCategorias() {
    try {
      const response = await apiClient.get('/categorias')
      
      if (response.success) {
        return response.data
      }
      
      throw new Error(response.message || 'Error al obtener categorías')
    } catch (error) {
      console.error('Error al obtener categorías:', error)
      // Fallback a categorías simuladas
      return this.getCategoriasSimuladas()
    }
  }

  /**
   * Formatear categoría para el frontend
   */
  formatearCategoria(categoriaAPI) {
    return {
      id: categoriaAPI.id,
      nombre: categoriaAPI.nombre,
      descripcion: categoriaAPI.descripcion,
      icono: categoriaAPI.icono,
      color: categoriaAPI.color,
      activa: categoriaAPI.activa,
      orden: categoriaAPI.orden
    }
  }

  /**
   * Categorías simuladas para desarrollo
   */
  getCategoriasSimuladas() {
    return [
      {
        id: 1,
        nombre: "Gaming",
        descripcion: "Productos gaming y tecnología",
        icono: "fas fa-gamepad",
        color: "#8B5CF6",
        activa: true,
        orden: 1
      },
      {
        id: 2,
        nombre: "Tecnología",
        descripcion: "Smartphones y dispositivos móviles",
        icono: "fas fa-mobile-alt",
        color: "#3B82F6",
        activa: true,
        orden: 2
      },
      {
        id: 3,
        nombre: "Belleza",
        descripcion: "Perfumes y productos de belleza",
        icono: "fas fa-heart",
        color: "#EC4899",
        activa: true,
        orden: 3
      },
      {
        id: 4,
        nombre: "Hogar",
        descripcion: "Electrodomésticos y artículos para el hogar",
        icono: "fas fa-home",
        color: "#10B981",
        activa: true,
        orden: 4
      }
    ]
  }
}

// Singleton instance
export const categoriaService = new CategoriaService()
