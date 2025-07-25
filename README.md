# 🎯 Danilore Rifas - Sistema de Rifas Online

Sistema completo de rifas online con Laravel + Vue.js + MySQL usando Docker.

## 🚀 Instalación y Configuración

### Pre-requisitos
- Docker Desktop
- Docker Compose
- Git

### 🔧 Configuración Inicial

1. **Clonar el repositorio:**
```bash
git clone <repository-url>
cd danilore-rifas
```

2. **Configurar variables de entorno:**
```bash
# El archivo .env del backend ya está configurado
# Verificar configuración en backend/.env
```

3. **Construir y levantar los servicios:**
```bash
docker-compose up --build -d
```

4. **Ejecutar migraciones:**
```bash
docker exec danilore_backend php artisan migrate
```

## 📊 Servicios Disponibles

| Servicio | Puerto | URL | Descripción |
|----------|--------|-----|-------------|
| Frontend | 3000 | http://localhost:3000 | Aplicación Vue.js |
| Backend | 8000 | http://localhost:8000 | API Laravel |
| MySQL | 3306 | localhost:3306 | Base de datos |
| phpMyAdmin | 8080 | http://localhost:8080 | Gestión de BD |

## 🗄️ Base de Datos

### Credenciales MySQL:
- **Host:** localhost:3306
- **Database:** danilore_rifas
- **Usuario:** danilore
- **Contraseña:** danilore123
- **Root Password:** root123

### phpMyAdmin:
- **URL:** http://localhost:8080
- **Usuario:** danilore
- **Contraseña:** danilore123

## 🛠️ Comandos Útiles

### Docker
```bash
# Levantar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Reconstruir contenedores
docker-compose up --build
```

### Laravel (Backend)
```bash
# Ejecutar comandos artisan
docker exec danilore_backend php artisan migrate
docker exec danilore_backend php artisan db:seed
docker exec danilore_backend php artisan cache:clear

# Acceder al contenedor
docker exec -it danilore_backend bash
```

### Vue.js (Frontend)
```bash
# Instalar dependencias
docker exec danilore_frontend npm install

# Acceder al contenedor
docker exec -it danilore_frontend sh
```

## 📁 Estructura del Proyecto

```
danilore-rifas/
├── backend/               # Laravel API
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── Dockerfile
│   └── .env
├── frontend/              # Vue.js App
│   ├── src/
│   ├── public/
│   ├── Dockerfile
│   └── package.json
├── docker-compose.yml     # Configuración Docker
└── README.md
```

## 🎯 Desarrollo

1. **Frontend:** Vue.js con hot-reload en puerto 3000
2. **Backend:** Laravel con artisan serve en puerto 8000
3. **Base de datos:** MySQL 8.0 con phpMyAdmin

## 🚀 Producción

Para producción, modificar:
- Variables de entorno en `.env`
- Configuraciones de Docker para optimización
- Configurar dominio y SSL

---

**Desarrollado para Danilore Rifas** 🎲
