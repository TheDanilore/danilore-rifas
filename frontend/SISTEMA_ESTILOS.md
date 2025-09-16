# Sistema de Estilos - Danilore Rifas Frontend

## Arquitectura CSS Modular

Este documento describe la estructura y organización del sistema de estilos CSS del frontend de Danilore Rifas.

### Orden de Importación CSS

Los estilos se importan en el siguiente orden estratégico en `main.js`:

```javascript
import '@/assets/styles/main.css'                    // Estilos base y variables globales
import '@/assets/styles/components/layout.css'       // Estructura y layout general
import '@/assets/styles/components/hero.css'         // Secciones hero base
import '@/assets/styles/components/navigation.css'   // Navegación y header
import '@/assets/styles/components/forms.css'        // Formularios y controles
import '@/assets/styles/components/auth.css'         // Autenticación específica
import '@/assets/styles/components/store.css'        // Tienda y experiencia pública
import '@/assets/styles/components/admin.css'        // Administración
```

### Estructura de Archivos CSS

#### 1. `main.css` - Estilos Base
- **Propósito**: Variables CSS, reset básico, utilidades globales
- **Contenido**: Colores, tipografía, espaciado, shadows, transiciones
- **Dependencias**: Ninguna
- **Uso**: Base para todos los demás estilos

#### 2. `layout.css` - Layout General
- **Propósito**: Estructura general, contenedores, grid systems
- **Contenido**: Container classes, grid layouts, flexbox utilities
- **Dependencias**: Variables de main.css
- **Uso**: Estructura de páginas y componentes

#### 3. `hero.css` - Secciones Hero
- **Propósito**: Estilos consistentes para secciones hero
- **Contenido**: Hero base, variantes (secondary, content-page, admin, dashboard)
- **Dependencias**: Variables y layout
- **Uso**: Headers de páginas, secciones destacadas

#### 4. `navigation.css` - Navegación
- **Propósito**: Header, navegación, menús móviles
- **Contenido**: App header, nav links, mobile menu, breadcrumbs
- **Dependencias**: Variables, layout, hero
- **Uso**: Específico para páginas públicas (`.app-public`)

#### 5. `forms.css` - Formularios
- **Propósito**: Controles de formulario, validación, estados
- **Contenido**: Inputs, buttons, select, radio, checkbox, validation
- **Dependencias**: Variables
- **Uso**: Todos los formularios de la aplicación

#### 6. `auth.css` - Autenticación
- **Propósito**: Estilos específicos para login/register
- **Contenido**: Auth containers, forms, cards, responsive design
- **Dependencias**: Variables, forms
- **Uso**: LoginView, RegisterView, páginas de autenticación

#### 7. `store.css` - Tienda Pública
- **Propósito**: Experiencia de usuario final en tienda
- **Contenido**: Hero mejorado, tarjetas de rifa, filtros, estados
- **Dependencias**: Variables, layout, hero, navigation
- **Uso**: Específico para páginas públicas (`.app-public`)

#### 8. `admin.css` - Administración
- **Propósito**: Interfaz administrativa
- **Contenido**: Dashboards, tablas, formularios admin
- **Dependencias**: Variables, layout, forms
- **Uso**: Específico para páginas admin (`.app-admin`)

### Sistema de Clases CSS

#### Clases de Contexto de Aplicación
- `.app-public` - Aplicada en páginas públicas (tienda)
- `.app-authenticated` - Aplicada en páginas de usuario autenticado
- `.app-admin` - Aplicada en páginas de administración

#### Clases Mejoradas (Enhanced)
Las clases con sufijo `-enhanced` proporcionan versiones optimizadas:
- `.hero-enhanced` - Hero con efectos y animaciones
- `.rifa-card-enhanced` - Tarjetas de rifa optimizadas
- `.filter-btn-enhanced` - Filtros con transiciones
- `.rifas-grid-enhanced` - Grid optimizado para tarjetas

#### Clases de Estado
- `.loading-state-enhanced` - Estados de carga mejorados
- `.error-state-enhanced` - Estados de error con iconografía
- `.empty-state-enhanced` - Estados vacíos informativos

### Responsive Design

#### Breakpoints Estándar
```css
@media (max-width: 768px)  /* Tablets y móviles grandes */
@media (max-width: 480px)  /* Móviles pequeños */
```

#### Principios Responsive
- **Mobile First**: Estilos base para móvil, media queries para desktop
- **Flexbox y Grid**: Layouts adaptativos
- **Clamp()**: Tipografía fluida
- **Spacing Variables**: Espaciado consistente

### Variables CSS Principales

#### Colores
```css
--primary-color: #5b2c87
--secondary-color: #10b981
--primary-blue: #4f46e5
--primary-indigo: #6366f1
--accent-yellow: #f59e0b
--primary-gold: #d97706
```

#### Espaciado
```css
--spacing-1: 0.25rem
--spacing-2: 0.5rem
--spacing-3: 0.75rem
/* ... hasta spacing-32 */
```

#### Tipografía
```css
--font-size-xs: 0.75rem
--font-size-sm: 0.875rem
--font-size-base: 1rem
/* ... hasta font-size-9xl */
```

### Optimizaciones Implementadas

#### 1. Eliminación de Duplicaciones
- **Problema**: CSS duplicado en múltiples archivos Vue
- **Solución**: Centralización en archivos de componentes específicos
- **Resultado**: ~500 líneas de CSS eliminadas de LoginView/RegisterView

#### 2. Modularización
- **Problema**: Estilos mezclados sin organización clara
- **Solución**: Separación por funcionalidad y contexto
- **Resultado**: 8 archivos CSS especializados

#### 3. Cascada Optimizada
- **Problema**: Conflictos de especificidad
- **Solución**: Orden de importación estratégico y clases de contexto
- **Resultado**: Estilos predecibles y mantenibles

#### 4. Clases de Contexto
- **Problema**: Estilos aplicados globalmente sin control
- **Solución**: Prefijos `.app-public`, `.app-authenticated`, `.app-admin`
- **Resultado**: Aislamiento de estilos por tipo de página

### Mejores Prácticas

#### 1. Nomenclatura
- **BEM Modificado**: `.component-element--modifier`
- **Prefijos de Contexto**: `.app-public .component`
- **Estados**: `.component--active`, `.component--disabled`

#### 2. Organización
- **Un archivo por funcionalidad**
- **Variables antes que propiedades**
- **Mobile first en media queries**

#### 3. Mantenimiento
- **Documentar cambios importantes**
- **Revisar duplicaciones periódicamente**
- **Testear en múltiples dispositivos**

### Performance

#### Métricas de Optimización
- **CSS eliminado**: ~500 líneas de duplicaciones
- **Archivos organizados**: 8 módulos especializados
- **Carga optimizada**: Orden de importación estratégico
- **Especificidad controlada**: Clases de contexto

#### Recomendaciones de Monitoreo
1. **Revisar duplicaciones** mensualmente
2. **Validar responsive design** en nuevas funcionalidades
3. **Monitorear tamaño de archivos CSS** en builds
4. **Testing de performance** en diferentes dispositivos

---

**Última actualización**: Diciembre 2024  
**Versión del sistema**: 1.0  
**Mantenedor**: Equipo Frontend Danilore Rifas