FROM bluepsyduck/php-fpm
LABEL maintainer="BluePsyduck <buepsyduck@gmx.com>"

RUN apt-get update && apt-get install -y --quiet --no-install-recommends \
        libfreetype6-dev \
        libjpeg-dev \
        libpng-dev \
    && docker-php-ext-configure \
        gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \
        gd

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /project
