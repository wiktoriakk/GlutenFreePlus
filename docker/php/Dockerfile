FROM php:8.2-fpm-alpine

# Instalacja rozszerzeń PHP potrzebnych do PostgreSQL
RUN apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Instalacja dodatkowych narzędzi
RUN apk add --no-cache bash

# Ustaw katalog roboczy
WORKDIR /app

# Kopiuj kod aplikacji
COPY . /app

# Ustaw uprawnienia
RUN chown -R www-data:www-data /app

# Eksponuj port 9000
EXPOSE 9000

CMD ["php-fpm"]