FROM php:8.3-apache

RUN apt-get update

RUN apt-get install -y \
  curl \
  vim \
  wget \
  libxml2-dev \
  libzip-dev \
  libpng++-dev \
  git \
  webp \
  libmagickwand-dev \
  libmagickcore-dev

RUN docker-php-ext-install mysqli pdo_mysql zip xml soap

RUN apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-source extract \
        && pecl install xdebug redis \
        && docker-php-ext-enable xdebug redis

RUN apt-get install -y git && \
    git clone https://github.com/Imagick/imagick.git --depth 1 /tmp/imagick && \
    cd /tmp/imagick && \
    git fetch origin master && \
    git switch master && \
    cd /tmp/imagick && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    apt-get remove -y git && \
    docker-php-ext-enable imagick

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite \
    && a2enmod expires \
    && a2enmod headers \
    && a2enmod ssl \
    && mkdir /etc/apache2/ssl

RUN curl -s https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 --output mhsendmail_linux_amd64  \
    && chmod +x mhsendmail_linux_amd64 \
    && mv mhsendmail_linux_amd64 /usr/local/bin/mhsendmail

COPY docker/php/conf/* /usr/local/etc/php/conf.d/

COPY docker/php/sites/* /etc/apache2/sites-enabled/

COPY docker/php/certs/* /etc/apache2/ssl/

RUN rm -rf /var/www/html/* \
  && rm -rf /var/lib/apt/lists/*

RUN echo "alias debug-enable='export XDEBUG_CONFIG=\"idekey=PHPSTORM\"'" >> ~/.bashrc \
    && echo "alias debug-disable='unset XDEBUG_CONFIG'" >> ~/.bashrc
