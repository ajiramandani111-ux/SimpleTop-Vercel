FROM php:8.3-cli

# Ekstensi yang dibutuhkan untuk koneksi PostgreSQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t ."]
