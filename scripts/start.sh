#!/bin/sh
set -e

echo "🚀 Iniciando despliegue de ecommerce-api..."

# Ejecuta migraciones
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# Ejecuta los seeders
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

# Optimiza la aplicación para producción
echo "⚡ Optimizando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Servidor iniciado"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
