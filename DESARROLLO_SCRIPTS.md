# Scripts de Desarrollo

## Desarrollo Local Rápido (Recomendado)
```bash
# Terminal 1: Ejecutar backend y BD con Docker
docker-compose up -d mysql backend

# Terminal 2: Ejecutar frontend local
cd frontend
npm run serve
```

**URLs:**
- Frontend: http://localhost:8080/
- Backend: http://localhost:8000/

## Docker Completo (Para testing)
```bash
# Cambiar vue.config.js target a 'http://backend:8000'
# Luego ejecutar:
docker-compose up -d --build
```

**URLs:**
- Frontend: http://localhost/
- Backend: http://localhost:8000/

## Comandos Útiles

### Para desarrollo diario:
```bash
# Parar todo
docker-compose down

# Solo backend + BD
docker-compose up -d mysql backend

# Ver logs del backend
docker-compose logs -f backend

# Frontend en modo dev
cd frontend && npm run serve
```

### Para build de producción:
```bash
# Build del frontend
cd frontend && npm run build

# Docker completo
docker-compose up -d --build
```