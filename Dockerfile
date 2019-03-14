FROM php:7.2-apache

RUN apt update -y && apt install -y \
      libjpeg-dev libpng-dev libfreetype6-dev \
      ttf-anonymous-pro
RUN docker-php-ext-configure gd \
          --enable-gd-native-ttf \
          --with-freetype-dir=/usr/include/freetype2 \
          --with-png-dir=/usr/include \
          --with-jpeg-dir=/usr/include && \
      docker-php-ext-install exif mbstring gd && \
      docker-php-ext-enable gd

COPY src/ /var/www/html/
