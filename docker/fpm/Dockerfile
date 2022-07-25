FROM php:8.1-fpm

RUN echo "process_control_timeout=5s" >> /usr/local/etc/php/conf.d/graceful.ini

COPY code /project/code
