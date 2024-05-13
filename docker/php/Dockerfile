FROM php:8.1-fpm

WORKDIR /var/www/bothelp

RUN apt-get update && apt-get install -y \
      curl \
      wget \
      git \
      libxml2-dev \
      zip \
      zlib1g-dev \
      libpng-dev \
      unzip \
      libldb-dev \
      libldap2-dev \
      libpq-dev \
      libssl-dev \
      libpspell-dev \
      libtidy-dev \
      libzip-dev \
      libfreetype6-dev \
      libjpeg62-turbo-dev \
    && docker-php-ext-install -j$(nproc) bcmath dba dom fileinfo gd intl ldap opcache pdo pdo_mysql pdo_pgsql pgsql phar posix pspell soap sockets tidy zip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN chown -R :www-data /var/www/bothelp/ && chmod -R 755 /var/www/bothelp/

CMD ["php-fpm"]
