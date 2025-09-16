# 📚 API Documentation - Danilore Rifas

## 🌐 **Base URL**
```
http://localhost:8000/api/v1
```

## 🔐 **Autenticación**
La API utiliza **Laravel Sanctum** con Personal Access Tokens.

### Headers requeridos para rutas autenticadas:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

# 🔑 **AUTENTICACIÓN** (`/auth`)

## 1. Registro de Usuario
**POST** `/auth/register`

### Propósito:
Registrar un nuevo usuario en el sistema.

### Headers:
```
Content-Type: application/json
Accept: application/json
```

### Body (JSON):
```json
{
    "name": "string (requerido, max:255)",
    "email": "string (requerido, email único)",
    "password": "string (requerido, min:8)",
    "password_confirmation": "string (requerido, igual a password)",
    "telefono": "string (requerido, max:15)",
    "tipo_documento": "string (requerido, valores: dni|ce|passport|ruc|otros)",
    "numero_documento": "string (requerido, max:20, único)"
}
```

### Ejemplo:
```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "telefono": "987654321",
    "tipo_documento": "dni",
    "numero_documento": "12345678"
}
```

### Respuesta exitosa (201):
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "roles": ["usuario"]
        },
        "token": "1|abcd1234...",
        "abilities": ["perfil.ver", "perfil.editar", "ventas.crear"]
    }
}
```

---

## 2. Iniciar Sesión
**POST** `/auth/login`

### Propósito:
Autenticar usuario y obtener token de acceso.

### Headers:
```
Content-Type: application/json
Accept: application/json
```

### Body (JSON):
```json
{
    "email": "string (requerido, email)",
    "password": "string (requerido)"
}
```

### Ejemplo:
```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

### Respuesta exitosa (200):
```json
{
    "success": true,
    "message": "Inicio de sesión exitoso",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "roles": ["usuario"]
        },
        "token": "1|abcd1234...",
        "abilities": ["perfil.ver", "perfil.editar", "ventas.crear"]
    }
}
```

---

## 3. Obtener Usuario Autenticado
**GET** `/auth/me`

### Propósito:
Obtener información del usuario autenticado.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Respuesta exitosa (200):
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "telefono": "987654321",
        "tipo_documento": "dni",
        "numero_documento": "12345678",
        "roles": ["usuario"],
        "permissions": ["perfil.ver", "perfil.editar"]
    }
}
```

---

## 4. Actualizar Perfil
**PUT** `/auth/profile`

### Propósito:
Actualizar información del perfil del usuario autenticado.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Body (JSON):
```json
{
    "name": "string (opcional, max:255)",
    "telefono": "string (opcional, max:15)",
    "tipo_documento": "string (opcional, valores: dni|ce|passport|ruc|otros)",
    "numero_documento": "string (opcional, max:20)"
}
```

---

## 5. Cerrar Sesión
**POST** `/auth/logout`

### Propósito:
Revocar el token actual del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

---

## 6. Cerrar Sesión en Todos los Dispositivos
**POST** `/auth/logout-all`

### Propósito:
Revocar todos los tokens del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

---

## 7. Obtener Tokens del Usuario
**GET** `/auth/tokens`

### Propósito:
Listar todos los tokens activos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

---

## 8. Revocar Token Específico
**DELETE** `/auth/tokens/{tokenId}`

### Propósito:
Revocar un token específico.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Parámetros de Ruta:
- `tokenId`: ID del token a revocar

---

# 🌐 **RUTAS PÚBLICAS** (`/public`)

## 1. Rifas

### 1.1 Listar Rifas
**GET** `/rifas`

### Propósito:
Obtener lista de rifas públicas activas.

### Query Parameters (opcionales):
```
categoria_id: integer (filtrar por categoría)
estado: string (activa|pausada|finalizada)
destacada: boolean (true|false)
per_page: integer (elementos por página, default: 15)
page: integer (página actual)
```

### Ejemplo:
```
GET /rifas?categoria_id=1&destacada=true&per_page=10
```

---

### 1.2 Rifas Actuales
**GET** `/rifas/actuales`

### Propósito:
Obtener rifas que están actualmente en venta.

---

### 1.3 Rifas Futuras
**GET** `/rifas/futuras`

### Propósito:
Obtener rifas programadas para el futuro.

---

### 1.4 Rifas Destacadas
**GET** `/rifas/destacadas`

### Propósito:
Obtener rifas marcadas como destacadas.

---

### 1.5 Ver Rifa Específica
**GET** `/rifas/{codigo}`

### Propósito:
Obtener detalles de una rifa específica.

### Parámetros de Ruta:
- `codigo`: Código único de la rifa

### Ejemplo:
```
GET /rifas/RIFA001
```

---

### 1.6 Progreso de Rifa
**GET** `/rifas/{codigo}/progreso`

### Propósito:
Obtener progreso de ventas de una rifa.

### Parámetros de Ruta:
- `codigo`: Código único de la rifa

---

## 2. Categorías

### 2.1 Listar Categorías
**GET** `/categorias`

### Propósito:
Obtener todas las categorías públicas.

---

### 2.2 Ver Categoría Específica
**GET** `/categorias/{id}`

### Propósito:
Obtener detalles de una categoría específica.

### Parámetros de Ruta:
- `id`: ID de la categoría

---

## 3. Premios

### 3.1 Ver Premio Específico
**GET** `/premios/{rifaCodigoUnico}/{codigoPremio}`

### Propósito:
Obtener detalles de un premio específico.

### Parámetros de Ruta:
- `rifaCodigoUnico`: Código único de la rifa
- `codigoPremio`: Código del premio

### Ejemplo:
```
GET /premios/RIFA001/PREMIO001
```

---

## 4. Ventas

### 4.1 Consultar Venta
**GET** `/ventas/{codigo}`

### Propósito:
Consultar estado de una venta por código.

### Parámetros de Ruta:
- `codigo`: Código de la venta

---

## 5. Configuraciones

### 5.1 Listar Configuraciones
**GET** `/configuraciones`

### Propósito:
Obtener todas las configuraciones públicas.

---

### 5.2 Configuraciones por Categoría
**GET** `/configuraciones/categoria/{categoria}`

### Propósito:
Obtener configuraciones de una categoría específica.

### Parámetros de Ruta:
- `categoria`: Nombre de la categoría

---

### 5.3 Ver Configuración Específica
**GET** `/configuraciones/{clave}`

### Propósito:
Obtener una configuración específica.

### Parámetros de Ruta:
- `clave`: Clave de la configuración

---

### 5.4 Valor de Configuración
**GET** `/configuraciones/{clave}/valor`

### Propósito:
Obtener solo el valor de una configuración.

### Parámetros de Ruta:
- `clave`: Clave de la configuración

---

## 6. Cupones

### 6.1 Listar Cupones Activos
**GET** `/cupones`

### Propósito:
Obtener cupones públicos activos.

---

### 6.2 Validar Cupón
**POST** `/cupones/validar`

### Propósito:
Validar si un cupón es válido para un monto específico.

### Headers:
```
Content-Type: application/json
Accept: application/json
```

### Body (JSON):
```json
{
    "codigo": "string (requerido, código del cupón)",
    "monto_compra": "number (requerido, monto de la compra)"
}
```

### Ejemplo:
```json
{
    "codigo": "DESCUENTO10",
    "monto_compra": 100.00
}
```

---

## 7. Sorteos

### 7.1 Listar Sorteos
**GET** `/sorteos`

### Propósito:
Obtener sorteos públicos.

---

### 7.2 Ver Sorteo Específico
**GET** `/sorteos/{id}`

### Propósito:
Obtener detalles de un sorteo específico.

### Parámetros de Ruta:
- `id`: ID del sorteo

---

## 8. Niveles

### 8.1 Listar Niveles
**GET** `/niveles`

### Propósito:
Obtener todos los niveles públicos.

---

### 8.2 Ver Nivel Específico
**GET** `/niveles/{id}`

### Propósito:
Obtener detalles de un nivel específico.

### Parámetros de Ruta:
- `id`: ID del nivel

---

## 9. Comentarios

### 9.1 Comentarios de Rifa
**GET** `/rifas/{rifaId}/comentarios`

### Propósito:
Obtener comentarios públicos de una rifa.

### Parámetros de Ruta:
- `rifaId`: ID de la rifa

### Query Parameters (opcionales):
```
per_page: integer (elementos por página)
page: integer (página actual)
```

---

# 👤 **RUTAS DE USUARIO** (`/user`)

> **Nota**: Todas las rutas de usuario requieren autenticación y permisos específicos.

## 1. Perfil de Usuario

### 1.1 Ver Perfil
**GET** `/user/perfil`

### Propósito:
Obtener información completa del perfil del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

### 1.2 Actualizar Perfil
**PUT** `/user/perfil/actualizar`

### Propósito:
Actualizar información del perfil del usuario.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "name": "string (opcional, max:255)",
    "telefono": "string (opcional, max:15)",
    "tipo_documento": "string (opcional, valores: dni|ce|passport|ruc|otros)",
    "numero_documento": "string (opcional, max:20)"
}
```

---

### 1.3 Actividad del Usuario
**GET** `/user/perfil/actividad`

### Propósito:
Obtener historial de actividad del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

## 2. Ventas de Usuario

### 2.1 Crear Venta
**POST** `/user/ventas`

### Propósito:
Crear una nueva venta (reservar boletos).

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `ventas.crear`

### Body (JSON):
```json
{
    "rifa_codigo": "string (requerido, código de la rifa)",
    "numeros_boletos": ["array de strings (requerido, números de boletos)"],
    "comprador_nombre": "string (requerido, max:255)",
    "comprador_email": "string (requerido, email)",
    "comprador_telefono": "string (requerido, max:15)",
    "comprador_tipo_documento": "string (requerido, valores: dni|ce|passport|ruc|otros)",
    "comprador_numero_documento": "string (requerido, max:20)",
    "metodo_pago": "string (requerido, valores: yape|plin|transferencia|efectivo)"
}
```

### Ejemplo:
```json
{
    "rifa_codigo": "RIFA001",
    "numeros_boletos": ["001", "002", "003"],
    "comprador_nombre": "Juan Pérez",
    "comprador_email": "juan@example.com",
    "comprador_telefono": "987654321",
    "comprador_tipo_documento": "dni",
    "comprador_numero_documento": "12345678",
    "metodo_pago": "yape"
}
```

---

### 2.2 Confirmar Pago
**POST** `/user/ventas/{codigo}/confirmar-pago`

### Propósito:
Confirmar pago de una venta subiendo comprobante.

### Headers:
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
Accept: application/json
```

### Permisos requeridos:
- `ventas.confirmar.pago`

### Parámetros de Ruta:
- `codigo`: Código de la venta

### Body (Form-data):
```
numero_operacion: string (requerido, max:50)
monto_pagado: number (requerido, min:0)
comprobante: file (requerido, imagen: jpeg|png|jpg, max:2MB)
```

---

### 2.3 Mis Ventas
**GET** `/user/ventas/mis-ventas`

### Propósito:
Obtener todas las ventas del usuario autenticado.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Query Parameters (opcionales):
```
per_page: integer (elementos por página, default: 10)
page: integer (página actual)
```

---

## 3. Favoritos

### 3.1 Ver Favoritos
**GET** `/user/favoritos`

### Propósito:
Obtener rifas favoritas del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

### 3.2 Toggle Favorito
**POST** `/user/favoritos/toggle`

### Propósito:
Agregar o quitar una rifa de favoritos.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "rifa_id": "integer (requerido, ID de la rifa)"
}
```

---

### 3.3 Verificar Favorito
**GET** `/user/favoritos/verificar/{rifaId}`

### Propósito:
Verificar si una rifa está en favoritos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

### Parámetros de Ruta:
- `rifaId`: ID de la rifa

---

### 3.4 Rifas con Favoritos
**GET** `/user/favoritos/rifas-con-favoritos`

### Propósito:
Obtener rifas con información de favoritos.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

### 3.5 Agregar Múltiples Favoritos
**POST** `/user/favoritos/agregar-multiples`

### Propósito:
Agregar múltiples rifas a favoritos.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "rifa_ids": ["array de integers (requerido, IDs de las rifas)"]
}
```

---

### 3.6 Eliminar Múltiples Favoritos
**DELETE** `/user/favoritos/eliminar-multiples`

### Propósito:
Eliminar múltiples rifas de favoritos.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "rifa_ids": ["array de integers (requerido, IDs de las rifas)"]
}
```

---

### 3.7 Limpiar Favoritos
**DELETE** `/user/favoritos/limpiar`

### Propósito:
Eliminar todos los favoritos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

---

### 3.8 Estadísticas de Favoritos
**GET** `/user/favoritos/estadisticas`

### Propósito:
Obtener estadísticas de favoritos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

## 4. Comentarios

### 4.1 Crear Comentario
**POST** `/user/comentarios`

### Propósito:
Crear un comentario en una rifa.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "rifa_id": "integer (requerido, ID de la rifa)",
    "comentario": "string (requerido, contenido del comentario)",
    "calificacion": "integer (opcional, 1-5)"
}
```

---

### 4.2 Actualizar Comentario
**PUT** `/user/comentarios/{id}`

### Propósito:
Actualizar un comentario propio.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Parámetros de Ruta:
- `id`: ID del comentario

### Body (JSON):
```json
{
    "comentario": "string (opcional, nuevo contenido)",
    "calificacion": "integer (opcional, 1-5)"
}
```

---

### 4.3 Eliminar Comentario
**DELETE** `/user/comentarios/{id}`

### Propósito:
Eliminar un comentario propio.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Parámetros de Ruta:
- `id`: ID del comentario

---

### 4.4 Reportar Comentario
**POST** `/user/comentarios/{id}/reportar`

### Propósito:
Reportar un comentario inapropiado.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Parámetros de Ruta:
- `id`: ID del comentario

### Body (JSON):
```json
{
    "motivo": "string (requerido, motivo del reporte)",
    "descripcion": "string (opcional, descripción detallada)"
}
```

---

## 5. Notificaciones

### 5.1 Listar Notificaciones
**GET** `/user/notificaciones`

### Propósito:
Obtener notificaciones del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

### Query Parameters (opcionales):
```
tipo: string (filtrar por tipo)
leida: boolean (true|false)
per_page: integer (elementos por página)
page: integer (página actual)
```

---

### 5.2 Resumen de Notificaciones
**GET** `/user/notificaciones/resumen`

### Propósito:
Obtener resumen de notificaciones (contadores).

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

### 5.3 Marcar Notificación como Leída
**PATCH** `/user/notificaciones/{id}/leida`

### Propósito:
Marcar una notificación específica como leída.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Parámetros de Ruta:
- `id`: ID de la notificación

---

### 5.4 Marcar Todas como Leídas
**PATCH** `/user/notificaciones/marcar-todas-leidas`

### Propósito:
Marcar todas las notificaciones como leídas.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

---

### 5.5 Marcar Múltiples como Leídas
**PATCH** `/user/notificaciones/marcar-multiples-leidas`

### Propósito:
Marcar múltiples notificaciones como leídas.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "notificacion_ids": ["array de integers (requerido, IDs de notificaciones)"]
}
```

---

### 5.6 Eliminar Notificación
**DELETE** `/user/notificaciones/{id}`

### Propósito:
Eliminar una notificación específica.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Parámetros de Ruta:
- `id`: ID de la notificación

---

### 5.7 Eliminar Múltiples Notificaciones
**DELETE** `/user/notificaciones/eliminar-multiples`

### Propósito:
Eliminar múltiples notificaciones.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "notificacion_ids": ["array de integers (requerido, IDs de notificaciones)"]
}
```

---

### 5.8 Limpiar Notificaciones Antiguas
**DELETE** `/user/notificaciones/limpiar-antiguas`

### Propósito:
Eliminar notificaciones más antiguas que X días.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "dias": "integer (opcional, días de antigüedad, default: 30)"
}
```

---

### 5.9 Configurar Preferencias
**POST** `/user/notificaciones/configurar-preferencias`

### Propósito:
Configurar preferencias de notificaciones.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `perfil.editar`

### Body (JSON):
```json
{
    "email_ventas": "boolean (opcional)",
    "email_sorteos": "boolean (opcional)",
    "push_ventas": "boolean (opcional)",
    "push_sorteos": "boolean (opcional)"
}
```

---

### 5.10 Obtener Preferencias
**GET** `/user/notificaciones/preferencias`

### Propósito:
Obtener preferencias de notificaciones del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

---

## 6. Cupones de Usuario

### 6.1 Aplicar Cupón
**POST** `/user/cupones/aplicar`

### Propósito:
Aplicar un cupón a una venta.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `ventas.crear`

### Body (JSON):
```json
{
    "codigo": "string (requerido, código del cupón)",
    "venta_id": "integer (requerido, ID de la venta)"
}
```

---

## 7. Sorteos de Usuario

### 7.1 Verificar Ganador
**GET** `/user/sorteos/{sorteoId}/verificar-ganador`

### Propósito:
Verificar si el usuario es ganador en un sorteo.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `perfil.ver`

### Parámetros de Ruta:
- `sorteoId`: ID del sorteo

---

## 8. Boletos de Usuario

### 8.1 Mis Boletos
**GET** `/user/boletos`

### Propósito:
Obtener todos los boletos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Query Parameters (opcionales):
```
estado: string (filtrar por estado)
rifa_id: integer (filtrar por rifa)
per_page: integer (elementos por página)
page: integer (página actual)
```

---

### 8.2 Ver Boleto Específico
**GET** `/user/boletos/{id}`

### Propósito:
Obtener detalles de un boleto específico.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Parámetros de Ruta:
- `id`: ID del boleto

---

### 8.3 Transferir Boleto
**POST** `/user/boletos/{id}/transferir`

### Propósito:
Transferir un boleto a otro usuario.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Parámetros de Ruta:
- `id`: ID del boleto

### Body (JSON):
```json
{
    "nuevo_propietario_email": "string (requerido, email del nuevo propietario)",
    "nuevo_propietario_nombre": "string (requerido, nombre del nuevo propietario)",
    "motivo": "string (opcional, motivo de la transferencia)"
}
```

---

### 8.4 Historial de Transferencias
**GET** `/user/boletos/{id}/historial-transferencias`

### Propósito:
Obtener historial de transferencias de un boleto.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Parámetros de Ruta:
- `id`: ID del boleto

---

### 8.5 Verificar Estado de Boletos en Rifa
**GET** `/user/boletos/rifa/{rifaId}/verificar-estado`

### Propósito:
Verificar estado de boletos del usuario en una rifa específica.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Parámetros de Ruta:
- `rifaId`: ID de la rifa

---

## 9. Pagos de Usuario

### 9.1 Mis Pagos
**GET** `/user/pagos`

### Propósito:
Obtener historial de pagos del usuario.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Query Parameters (opcionales):
```
estado: string (filtrar por estado)
metodo_pago: string (filtrar por método)
per_page: integer (elementos por página)
page: integer (página actual)
```

---

### 9.2 Ver Pago Específico
**GET** `/user/pagos/{id}`

### Propósito:
Obtener detalles de un pago específico.

### Headers:
```
Authorization: Bearer {token}
Accept: application/json
```

### Permisos requeridos:
- `ventas.ver.propias`

### Parámetros de Ruta:
- `id`: ID del pago

---

### 9.3 Crear Pago
**POST** `/user/pagos`

### Propósito:
Registrar un nuevo pago.

### Headers:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Permisos requeridos:
- `ventas.crear`

### Body (JSON):
```json
{
    "venta_id": "integer (requerido, ID de la venta)",
    "metodo_pago": "string (requerido, valores: yape|plin|transferencia|efectivo)",
    "monto": "number (requerido, monto del pago)",
    "numero_operacion": "string (requerido, número de operación)"
}
```

---

# 📝 **Códigos de Respuesta HTTP**

## Exitosas (2xx)
- **200 OK**: Solicitud exitosa
- **201 Created**: Recurso creado exitosamente
- **204 No Content**: Solicitud exitosa sin contenido

## Errores del Cliente (4xx)
- **400 Bad Request**: Solicitud mal formada
- **401 Unauthorized**: No autenticado
- **403 Forbidden**: Sin permisos suficientes
- **404 Not Found**: Recurso no encontrado
- **422 Unprocessable Entity**: Errores de validación

## Errores del Servidor (5xx)
- **500 Internal Server Error**: Error interno del servidor

---

# 🔒 **Permisos del Sistema**

## Permisos de Perfil
- `perfil.ver`: Ver información del perfil
- `perfil.editar`: Editar información del perfil

## Permisos de Ventas
- `ventas.crear`: Crear nuevas ventas
- `ventas.confirmar.pago`: Confirmar pagos
- `ventas.ver.propias`: Ver ventas propias

## Permisos de Boletos
- `boletos.transferir`: Transferir boletos

## Permisos de Cupones
- `cupones.usar`: Usar cupones

---

# 🚨 **Notas Importantes**

1. **Tokens**: Guardar el token obtenido en login para usarlo en requests autenticados
2. **Expiración**: Los tokens pueden expirar, manejar respuestas 401
3. **Validación**: Siempre verificar campos requeridos antes de enviar
4. **Archivos**: Para upload de archivos usar `multipart/form-data`
5. **Paginación**: Muchas rutas soportan paginación con `per_page` y `page`
6. **Filtros**: Las rutas de listado generalmente soportan filtros via query parameters

---

# 📊 **Ejemplos de Respuestas**

## Respuesta Exitosa Típica
```json
{
    "success": true,
    "message": "Operación exitosa",
    "data": {
        // ... datos del recurso
    }
}
```

## Respuesta de Error de Validación
```json
{
    "success": false,
    "message": "Datos inválidos",
    "errors": {
        "email": ["El campo email es requerido"],
        "password": ["El campo password debe tener al menos 8 caracteres"]
    }
}
```

## Respuesta de Error de Autenticación
```json
{
    "success": false,
    "message": "Token inválido o expirado"
}
```

## Respuesta de Error de Permisos
```json
{
    "success": false,
    "message": "No tienes permisos para realizar esta acción"
}
```