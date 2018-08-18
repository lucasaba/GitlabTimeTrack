FROM php:7.1.8-apache

LABEL MAINTAINER Naveen Kumar Sangi <naveenkumarsangi@protonmail.com>

RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev \
                                                  libmcrypt-dev \
                                                  libicu-dev \
                                                  libxml2-dev \
                                                  zlib1g-dev \
                                                  zip \
                                                  unzip \
                                                  git \
                                                  netcat \
    && apt-get clean

RUN docker-php-ext-install pdo_mysql \
                           mysqli \
                           mcrypt \
                           intl \
                           zip

RUN curl --show-error https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

ENV ROOT_DIR=/var/www \
    APP_USER=www-data \
    APP_USER_GROUP=www-data
WORKDIR ${ROOT_DIR}

COPY composer.json ${ROOT_DIR}
COPY composer.lock ${ROOT_DIR}
COPY app ${ROOT_DIR}/app
RUN composer install --no-plugins --no-scripts

COPY . ${ROOT_DIR}
COPY .docker/apache2.conf /etc/apache2/sites-available/000-default.conf
COPY .docker/parameters.yml ${ROOT_DIR}/app/config/parameters.yml
RUN a2enmod rewrite

ENTRYPOINT ["./.docker/run.sh"]
