FROM serversideup/php:8.3-fpm-nginx

USER root

RUN install-php-extensions \
    pdo_pgsql \
    pdo_mysql \
    intl \
    zip \
    bcmath \
    gd \
    exif

WORKDIR /var/www/html

COPY --chown=www-data:www-data . /var/www/html

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

RUN mkdir -p \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
    && chmod +x \
        /var/www/html/docker/start.sh \
    && chown -R www-data:www-data \
        /var/www/html/storage \
        /var/www/html/bootstrap/cache

ENV PHP_OPCACHE_ENABLE=1
ENV NGINX_WEBROOT=/var/www/html/public

EXPOSE 8080

USER www-data

CMD ["/var/www/html/docker/start.sh"]