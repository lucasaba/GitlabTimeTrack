FROM php:7.2-cli

RUN apt-get update \
    && apt-get install -y $PHPIZE_DEPS wget git libzip-dev unzip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install xdebug-2.9.2 \
    && docker-php-ext-enable xdebug.so \
    && docker-php-ext-install mysqli zip pdo pdo_mysql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv ~/.symfony/bin/symfony /usr/local/bin/symfony

RUN groupadd -g 1000 user \
    && mkdir /home/user \
    && useradd -u 1000 -g 1000 -d /home/user -s /bin/bash user \
    && chown user: /home/user

USER user