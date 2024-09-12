FROM php:8.3-fpm

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY php.ini /etc/php/8.3/cli/conf.d/99-etc.ini

ADD docker-entrypoint.sh ./
RUN chmod +x ./docker-entrypoint.sh
CMD ["./docker-entrypoint.sh"]

WORKDIR /var/www/laravel
