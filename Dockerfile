FROM richarvey/nginx-php-fpm:latest

COPY . .

# Configuración de la imagen para Laravel - CAMBIADO A 0 PARA INSTALAR DEPENDENCIAS
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Configuración de Laravel
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Permitir a Composer ejecutarse como root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Comando final con migraciones automáticas incluidas
CMD php artisan migrate --force && /start.sh
