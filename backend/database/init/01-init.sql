-- Script de inicialización para la base de datos de Danilore Rifas
-- Este script se ejecuta cuando se crea el contenedor MySQL

-- Configurar charset y collation
ALTER DATABASE danilore_rifas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Mensaje de confirmación
SELECT 'Base de datos Danilore Rifas inicializada correctamente' as mensaje;
