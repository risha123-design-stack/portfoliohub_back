FROM php:8.3-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpq-dev \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_pgsql \
        pdo_mysql \
        intl \
        zip \
        bcmath \
        gd \
        exif \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 \
    /usr/bin/composer \
    /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN sed -ri \
        -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && sed -ri \
        -e 's/Listen 80/Listen 8080/' \
        /etc/apache2/ports.conf \
    && sed -ri \
        -e 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' \
        /etc/apache2/sites-available/*.conf

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod +x docker/start.sh \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache

EXPOSE 8080

CMD ["/var/www/html/docker/start.sh"]