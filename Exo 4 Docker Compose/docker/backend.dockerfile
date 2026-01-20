FROM php:8.1-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    libpq-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_pgsql

COPY backend/src/ /app/

EXPOSE 5000
CMD ["php", "-S", "0.0.0.0:5000", "index.php"]
