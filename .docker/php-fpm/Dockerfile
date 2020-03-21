FROM php:7.2-fpm-alpine

COPY conf /usr/local/etc/php/conf.d/

RUN apk add --no-cache $PHPIZE_DEPS wkhtmltopdf \
    && pecl install xdebug-2.9.2 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install mysqli pdo pdo_mysql