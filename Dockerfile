FROM php:7.2-apache

RUN apt update -y && apt install -y libjpeg-dev libpng-dev
RUN docker-php-ext-install exif mbstring gd

COPY src/ /var/www/html/
