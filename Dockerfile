FROM ubuntu:16.04

MAINTAINER Luca Mattivi <luca@smartdomotik.com>

ENV PROJECT_PATH=/var/www \
    PROJECT_URL=images.uala.it \
    DEBIAN_FRONTEND=noninteractive \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data \
    APACHE_LOG_DIR=/var/log/apache2 \
    APACHE_LOCK_DIR=/var/lock/apache2 \
    APACHE_PID_FILE=/var/run/apache2/apache2.pid \
    PHP_MODS_CONF=/etc/php/7.0/mods-available \
    PHP_INI=/etc/php/7.0/apache2/php.ini \
    PHP_INI_CONFD=/etc/php/7.0/apache2/conf.d \
    TERM=xterm

RUN apt-get update && apt-get upgrade -y --force-yes

# Utilities, Apache, PHP, and supplementary programs
RUN apt-get install -yqq --force-yes \
    curl \
    git \
    wget \
    zip \
    apache2 \
    libapache2-mod-php \
    php \
    php-curl \
    php-dom \
    php-mbstring \
    php-intl \
    php-mcrypt \
    php-imagick

# Apache mods
RUN a2enmod rewrite expires headers

# Custom PHP.ini
COPY config/docker/production/php.ini $PHP_INI_CONFD/custom-php.ini

# Apache2 conf
RUN echo "ServerName localhost" | tee /etc/apache2/conf-available/fqdn.conf && \
    a2enconf fqdn

# Set the timezone.
RUN echo "Europe/Paris" > /etc/timezone && \
    dpkg-reconfigure -f noninteractive tzdata

# Cleanup
RUN apt-get purge -yq \
      wget \
      patch \
      software-properties-common && \
    apt-get autoremove -yqq

# Port to expose
EXPOSE 80

# VirtualHost
COPY config/docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

# Change workdir
WORKDIR $PROJECT_PATH

# Copy composer json and lock file before the copy o the entire project
COPY composer.json $PROJECT_PATH/composer.json
COPY composer.lock $PROJECT_PATH/composer.lock

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --optimize-autoloader

# Copy site into place
COPY . $PROJECT_PATH

# Folder permissions & Add permissions for temp data of mpdf library
RUN chown -R $APACHE_RUN_USER:root logs/

# Remove pre-existent apache pid and start apache
CMD rm -f $APACHE_PID_FILE && /usr/sbin/apache2ctl -D FOREGROUND