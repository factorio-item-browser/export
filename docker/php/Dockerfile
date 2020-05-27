FROM bluepsyduck/php-fpm
LABEL maintainer="BluePsyduck <buepsyduck@gmx.com>"

RUN apt-get update && apt-get install -y --quiet --no-install-recommends \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
    && docker-php-ext-configure \
        gd \
    && docker-php-ext-install \
        gd

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /project
