FROM php:8.2-cli

# MySQL driver for PDO
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . .

# Railway provides the PORT variable
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app"]
