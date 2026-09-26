#!/bin/sh
set -e

echo "🚀 Iniciando despliegue de ecommerce-api..."

# Ejecuta migraciones (el flag --force evita la confirmación interactiva)
echo "🗄️  Ejecutando migraciones..."
php artisan migrate --force

# Ejecuta los seeders para crear los usuarios y datos iniciales
echo "🌱 Ejecutando seeders..."
php artisan db:seed --force

# Optimiza la aplicación para producción
echo "⚡ Optimizando cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Inicia el servidor principal de la imagen
echo "✅ Servidor iniciado en el puerto 80"
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
