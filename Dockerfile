FROM php:7.4-apache as base

# Install Dependancies
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt update -y && \
    apt upgrade -y && \
    apt install -y curl zip unzip git mariadb-client && \
    chmod +x /usr/local/bin/install-php-extensions && \
    sync && \
    install-php-extensions ldap intl zip pdo_mysql openssl

RUN a2enmod rewrite && \
    a2enmod expires && \
    a2enmod headers && \
    a2enmod http2 && \
    sed -e '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' -i /etc/apache2/apache2.conf

# Install composer
RUN mkdir /opt/composer && \
    curl -sS https://getcomposer.org/installer > composer.php && \
    php composer.php --install-dir=/opt/composer

FROM base as develop

COPY .docker/environment.conf /etc/apache2/conf-enabled/

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "TLS_REQCERT never" >> /etc/ldap.conf
FROM base as production

WORKDIR /var/www

COPY . .
COPY ./webroot ./html
RUN mkdir logs && \
    chown www-data.www-data logs && \
    cp ./.docker/environment.conf /etc/apache2/conf-enabled/ && \
    cp ./config/app.default.php ./config/app.php && \
    mkdir ./tmp && chown www-data.www-data ./tmp && chmod 767 ./tmp && \
    rm -rf ./html && ln -s /var/www/webroot /var/www/html && \
    cp ./.docker/prod/php-production.ini /usr/local/etc/php/php.ini && \
    php /opt/composer/composer.phar -n install
EXPOSE 80
