# Reference:
#       https://hub.docker.com/_/php
FROM php:8.0-cli-alpine

RUN addgroup -S dokgroup && \
    adduser -S dokuser  \
            -G dokgroup \
            -D          \
            -H

USER root

# Reference:
#       https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY . /var/www/html
WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mongodb-stable

RUN chown -R dokuser:dokgroup /var/www/html
RUN chmod -R 755 /var/www/html
#USER dokuser

EXPOSE 8000
