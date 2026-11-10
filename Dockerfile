FROM php:8.2-apache

# Instalar extensões necessárias do PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql mysqli zip \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Configurar diretório de trabalho
WORKDIR /var/www/html

# Copiar código do projeto
COPY . /var/www/html/

# Ajustar permissões para pastas de upload e sessão
RUN mkdir -p /var/www/html/uploads /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/uploads /var/www/html/public/uploads

# Configurar timezone e limites de upload do PHP
RUN echo "date.timezone = America/Sao_Paulo" > /usr/local/etc/php/conf.d/timezone.ini \
    && echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "display_errors = Off" > /usr/local/etc/php/conf.d/errors.ini \
    && echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini

EXPOSE 80

CMD ["apache2-foreground"]
