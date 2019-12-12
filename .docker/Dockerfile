FROM php:7.1.8-apache

MAINTAINER Mazecode

ENV HOME_DIR /www/siga-helpers
ENV NVM_DIR ~/.nvm
ENV NODE_VERSION 0.10.48

RUN a2enmod rewrite

WORKDIR $HOME_DIR

# Install basics
RUN apt-get update -yqq  \
    && apt-get install -yqq --no-install-recommends \
    && apt-get install -y \
		wget \
		openssl \
		libssl-dev \
		vim \
		libpng-dev \
		git \
		git-core \
		libzip-dev \
		zip unzip php-pclzip \
	   aufs-tools \
	   automake \
	   build-essential \
	   curl \
	   dpkg-sig \
	   libcap-dev \
	   libsqlite3-dev \
	   mercurial \
	   reprepro \
	   node \
   && apt-get clean

# Copy all project to docker folder
COPY . $HOME_DIR

# Install PHP extensions
RUN docker-php-ext-install mbstring pdo pdo_mysql zip gd \
	&& docker-php-ext-configure zip --with-libzip \
	&& docker-php-ext-configure gd \
    && chown -R www-data:www-data $HOME_DIR

# Install XDebug
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# Add Apache VirtualHost to machine
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

#RUN composer install
