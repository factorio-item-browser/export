FROM php:7.2-fpm
LABEL maintainer="BluePsyduck <buepsyduck@gmx.com>"

RUN apt-get update && apt-get install -y --quiet --no-install-recommends \
        git \
        libbz2-dev \
        libpng-dev \
        libxml2-dev \
        ssh \
        zlib1g-dev \
    && docker-php-ext-install \
        bz2 \
        gd \
        mbstring \
        xml \
        zip

ENV COMPOSER_ALLOW_SUPERUSER=1
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /project