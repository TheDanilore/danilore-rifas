# Configuración de Entornos

## 📁 Estructura de Configuración

- **Backend**: Editar `backend/.env` para todas las configuraciones (incluye variables de Docker)
- **Frontend**: `frontend/vue.config.js` usa `http://backend:8000` (nombre del servicio Docker)
- **Docker**: `docker-compose.yml` toma valores dinámicamente de `backend/.env`

## � Diferencia entre URLs

### URLs dentro de Docker (contenedor a contenedor)

- Frontend → Backend: `http://backend:8000` ✅ (vue.config.js)
- Backend → MySQL: `mysql:3306` ✅ (backend/.env)

### URLs desde fuera de Docker (tu navegador)

- Frontend: `http://localhost/` ✅
- Backend: `http://localhost:8000/` ✅ (APP_URL en backend/.env)

## 🚀 Desarrollo Local

### Variables actuales en `backend/.env`

```bash
# URLs públicas (desde tu navegador)
APP_URL=http://localhost:8000

# Configuración de base de datos (dentro de Docker)
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=danilore_rifas
DB_USERNAME=danilore
DB_PASSWORD=danilore123

# Variables para Docker Compose
MYSQL_ROOT_PASSWORD=root123
FRONTEND_PORT=80
BACKEND_PORT=8000
```

### Ejecutar

```bash
docker-compose up -d
```

**URLs de acceso:**

- Frontend: <http://localhost/>
- Backend: <http://localhost:8000/>
- API: <http://localhost:8000/api/>

## 🌐 Producción

### Cambios necesarios en `backend/.env`

```bash
# Cambiar entorno
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error

# URLs públicas para producción
APP_URL=https://api.tudominio.com
# O si usas un solo dominio: APP_URL=https://tudominio.com/api

# Base de datos segura
DB_PASSWORD=tu_password_seguro_aqui

# Correo SMTP (cambiar de 'log' a 'smtp')
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@tudominio.com"

# Puertos (opcional cambiar)
FRONTEND_PORT=80
BACKEND_PORT=8000
```

### Frontend

El `vue.config.js` **NO necesita cambios** porque `http://backend:8000` funciona igual en desarrollo y producción dentro de Docker.

## 📊 Configuración Dinámica

El `docker-compose.yml` toma valores dinámicamente de `backend/.env`:

```yaml
# MySQL usa variables del .env
mysql:
  environment:
    MYSQL_DATABASE: ${DB_DATABASE:-danilore_rifas}
    MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD:-danilore123}
    MYSQL_USER: ${DB_USERNAME:-danilore}
    MYSQL_PASSWORD: ${DB_PASSWORD:-danilore123}
  ports:
    - "${DB_PORT:-3306}:3306"

# Backend usa variables del .env
backend:
  ports:
    - "${BACKEND_PORT:-8000}:8000"
  env_file:
    - ./backend/.env

# Frontend usa variables del .env
frontend:
  ports:
    - "${FRONTEND_PORT:-80}:80"
  env_file:
    - ./backend/.env
```

## 🎯 URLs por Entorno

### Desarrollo (Docker)

- **Navegador → Frontend**: `http://localhost/`
- **Navegador → Backend**: `http://localhost:8000/`
- **Frontend → Backend** (interno): `http://backend:8000`
- **Backend → MySQL** (interno): `mysql:3306`

### Producción (Docker)

- **Navegador → Frontend**: `https://tudominio.com/`
- **Navegador → Backend**: `https://api.tudominio.com/`
- **Frontend → Backend** (interno): `http://backend:8000` (igual que desarrollo)
- **Backend → MySQL** (interno): `mysql:3306` (igual que desarrollo)

## ✅ Ventajas de esta Configuración

1. **Un solo archivo de configuración**: Todo se gestiona desde `backend/.env`
2. **Dinámico**: Cambiar puertos y configuraciones sin modificar docker-compose
3. **Separación clara**: URLs internas (Docker) vs URLs externas (navegador)
4. **Portabilidad**: Mismo código funciona en desarrollo y producción
5. **Simplicidad**: No necesitas crear archivos adicionales

## 🛡️ Checklist para Producción

- [ ] Cambiar `APP_ENV=production` en `backend/.env`
- [ ] Cambiar `APP_DEBUG=false` en `backend/.env`
- [ ] Actualizar `APP_URL` con tu dominio real
- [ ] Configurar `DB_PASSWORD` seguro
- [ ] Configurar correo SMTP real (cambiar `MAIL_MAILER=smtp`)
- [ ] Verificar CORS en Laravel para tu dominio frontend
- [ ] Configurar HTTPS con proxy reverso (Nginx/Apache)

## ⚠️ Notas Importantes

1. **Docker Network**: Los contenedores se comunican usando nombres de servicios (`backend`, `mysql`)
2. **Puerto 80**: En algunos sistemas requiere permisos de administrador
3. **Proxy Configuration**: `vue.config.js` usa `http://backend:8000` para comunicación interna
4. **Environment Variables**: Docker Compose carga automáticamente `backend/.env`
