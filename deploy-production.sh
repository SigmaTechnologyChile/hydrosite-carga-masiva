#!/bin/bash
# Script para deploy en producción

echo "=== DEPLOY A PRODUCCIÓN ==="

# 1. Modo mantenimiento
php artisan down

# 2. Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Ejecutar migraciones (si hay)
php artisan migrate --force

# 5. Salir del modo mantenimiento
php artisan up

echo "=== DEPLOY COMPLETADO ==="
