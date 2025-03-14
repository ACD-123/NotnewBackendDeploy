FROM php:7.4-fpm-alpine
RUN set -ex \
  && apk --no-cache add \
    postgresql-dev
RUN docker-php-ext-install pdo pdo_pgsql
RUN curl -sS https://getcomposer.org/installer​ | php -- \
     --install-dir=/usr/local/bin --filename=composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apk update && apk add --no-cache supervisor
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /app
COPY . .
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux
RUN composer install --no-scripts
RUN composer dump-autoload
RUN php artisan optimize:clear
RUN php artisan migrate
CMD ["/usr/bin/supervisord"]
