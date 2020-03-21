FROM php:7.2-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug-2.9.2 \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install mysqli pdo pdo_mysql