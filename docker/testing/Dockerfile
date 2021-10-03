# Reference:
#       https://hub.docker.com/_/php
FROM php:8.0-cli-alpine

USER root

# Reference:
#       https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY . /var/www/html
COPY .env.testing /var/www/html/.env
WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mongodb-stable

EXPOSE 8000

CMD [ "php", "artisan", "serve", "--port=8000", "--host=0.0.0.0" ]
