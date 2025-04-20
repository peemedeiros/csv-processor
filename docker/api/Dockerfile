FROM php:8.4-cli

WORKDIR /var/www/html

COPY . .

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    brotli \
    libbrotli-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./entrypoint.sh /entrypoint.sh

COPY ./worker-entrypoint.sh /worker-entrypoint.sh

COPY ./custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

RUN chmod +x /entrypoint.sh
RUN chmod +x /worker-entrypoint.sh

EXPOSE 80/tcp

ENTRYPOINT ["/entrypoint.sh"]
