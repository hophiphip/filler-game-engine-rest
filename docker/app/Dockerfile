# Reference:
#       https://hub.docker.com/_/php
FROM php:8.0-cli-alpine

# Reference:
#       https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY . /var/www/html
WORKDIR /var/www/html

RUN chmod +x /usr/local/bin/install-php-extensions && sync && \
    install-php-extensions mongodb-stable && \
	install-php-extensions @composer && \
	composer update --no-scripts && \
	composer install && \
	composer update

EXPOSE 8000

CMD [ "php", "artisan", "serve", "--port=8000", "--host=0.0.0.0" ]
