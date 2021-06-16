# Dockerfile
FROM php:7.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80
WORKDIR /app

RUN apt-get update -qq && \
    apt-get install -qy \
    nano \
    git \
    iputils-ping \
    mariadb-client \
    gnupg \
    unzip \
    libcurl4-openssl-dev \
    zlib1g-dev \
    libxml2-dev \
    zip && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* && \
    docker-php-ext-install opcache bcmath curl xml pdo pdo_mysql

# PHP Extensions
COPY php.ini /usr/local/etc/php/conf.d/app.ini

# Apache
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY apache.conf /etc/apache2/conf-available/z-app.conf

RUN a2enmod rewrite remoteip && \
    a2enconf z-app
