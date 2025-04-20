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

ENV DOCKERIZE_VERSION v0.9.3

RUN apt-get update \
    && apt-get install -y wget \
    && wget -O - https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz | tar xzf - -C /usr/local/bin \
    && apt-get autoremove -yqq --purge wget && rm -rf /var/lib/apt/lists/*

COPY ./entrypoint.sh /entrypoint.sh

COPY ./custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
