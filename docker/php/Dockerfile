FROM php:8.1-cli

RUN apt-get update

RUN apt-get install -y git curl zip unzip libicu-dev

RUN docker-php-ext-configure intl
RUN docker-php-ext-install pdo pdo_mysql intl

RUN apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/symfony
EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
