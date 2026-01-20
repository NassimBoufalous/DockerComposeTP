FROM php:8.1-cli

WORKDIR /app
COPY index.php .

RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_sqlite

EXPOSE 5000
CMD ["php", "-S", "0.0.0.0:5000", "index.php"]
