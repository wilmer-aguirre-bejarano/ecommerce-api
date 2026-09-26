FROM richarvey/nginx-php-fpm:latest

COPY . .

# Configuración de la imagen para Laravel
#ENV SKIP_COMPOSER 1 #evita error no lo descomentes
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

# Copia tu script de inicio personalizado
COPY scripts/start.sh /start.sh
RUN chmod +x /start.sh

# Comando de inicio plano para evitar bloqueos por bases de datos
CMD ["/start.sh"]



# FROM richarvey/nginx-php-fpm:latest

# COPY . .

# # Configuración de la imagen para Laravel
# ENV SKIP_COMPOSER 0
# ENV WEBROOT /var/www/html/public
# ENV PHP_ERRORS_STDERR 1
# ENV RUN_SCRIPTS 1
# ENV REAL_IP_HEADER 1

# # Configuración de Laravel
# ENV APP_ENV production
# ENV APP_DEBUG false
# ENV LOG_CHANNEL stderr

# # Permitir a Composer ejecutarse como root
# ENV COMPOSER_ALLOW_SUPERUSER 1

# # Regresamos al inicio normal para que no se caiga
# CMD ["/start.sh"]
