FROM php:8.2-fpm

# Instalar extensiones necesarias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    zip \
    && docker-php-ext-install pdo_pgsql
# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Configuración del directorio de trabajo
WORKDIR /var/www

# Copiar todos los archivos del proyecto
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader

# Ajustar permisos
RUN chmod -R 775 storage bootstrap/cache
RUN chown -R www-data:www-data /var/www

# Exponer el puerto de PHP-FPM
EXPOSE 9000

# Iniciar PHP-FPM
CMD ["php-fpm"]