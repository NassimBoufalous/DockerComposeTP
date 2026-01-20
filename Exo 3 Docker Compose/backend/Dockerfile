FROM php:8.1-cli

WORKDIR /app
COPY index.php .

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install curl

EXPOSE 5000
CMD ["php", "-S", "0.0.0.0:5000", "index.php"]
