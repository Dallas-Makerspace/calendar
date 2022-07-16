FROM php:7.4-apache

# Install Dependancies
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN apt update -y && \
    apt upgrade -y && \
    apt install -y curl zip unzip git mariadb-client && \
    chmod +x /usr/local/bin/install-php-extensions && \
    sync && \
    install-php-extensions ldap intl zip pdo_mysql

RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN a2enmod rewrite && \
    a2enmod expires && \
    a2enmod headers && \
    a2enmod http2 && \
    sed -e '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' -i /etc/apache2/apache2.conf


# Install composer
RUN mkdir /opt/composer && \
    curl -sS https://getcomposer.org/installer > composer.php && \
    php composer.php --install-dir=/opt/composer
