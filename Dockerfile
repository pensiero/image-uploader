FROM ubuntu:16.04

MAINTAINER Luca Mattivi <luca@smartdomotik.com>

ENV PROJECT_PATH=/var/www \
    PROJECT_URL=uala.it \
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
    npm \
    git \
    htop \
    nano \
    wget \
    zip \
    unzip \
    apache2 \
    libapache2-mod-php7.0 \
    curl \
    php7.0 \
    php7.0-dom \
    php7.0-mbstring \
    php7.0-intl \
    php7.0-mcrypt \
    php7.0-cgi \
    php7.0-curl \
    php-imagick \
    gettext

RUN ln -s "$(which nodejs)" /usr/bin/node

# Apache mods
RUN a2enmod rewrite expires headers

# Custom PHP.ini
COPY custom-php.ini $PHP_INI_CONFD/custom-php.ini

# Apache2 conf
RUN echo "ServerName localhost" | tee /etc/apache2/conf-available/fqdn.conf && \
    a2enconf fqdn

# Set the timezone.
RUN echo "Europe/Paris" > /etc/timezone && \
    dpkg-reconfigure -f noninteractive tzdata

# Port to expose
EXPOSE 80

# VirtualHost
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf


# move composer before copy project, This should improve docker cache.
WORKDIR $PROJECT_PATH

COPY composer.json $PROJECT_PATH/composer.json
COPY composer.lock $PROJECT_PATH/composer.lock
COPY vendor $PROJECT_PATH/vendor


# must copy project before composer for artisan
#COPY . $PROJECT_PATH
#WORKDIR $PROJECT_PATH


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-dev --ignore-platform-reqs
    #composer install --no-interaction --optimize-autoloader

# Copy site into place
COPY . $PROJECT_PATH

# Folder permissions & Add permissions for temp data of mpdf library
RUN chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP vendor/ && \
    chown -R $APACHE_RUN_USER:root logs/ && \
    chown -R $APACHE_RUN_USER:$APACHE_RUN_GROUP src/

# Remove pre-existent apache pid and start apache
CMD rm -f $APACHE_PID_FILE && /usr/sbin/apache2ctl -D FOREGROUND