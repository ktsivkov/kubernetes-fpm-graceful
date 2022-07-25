FROM php:8.1-fpm

RUN touch /usr/local/etc/php/graceful.ini && \
    echo "process_control_timeout=5" >> /usr/local/etc/php/graceful.ini

COPY code /project/code
