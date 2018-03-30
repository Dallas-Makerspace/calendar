FROM php:7-apache

LABEL maintainer="infrastructure@dallasmakerspace.org"

LABEL affinity:org.dallasmakerspace.application=infrastructure
LABEL affinity:org.dallasmakerspace.architecture!=armhf

LABEL org.dallasmakerspace.orgunit "Infrastructure Committee"
LABEL org.dallasmakerspace.organization "Dallas Makerspace"
LABEL org.dallasmakerspace.commonname "calendar.dallasmakerspace.org"
LABEL org.dallasmakerspace.locality "Dallas"
LABEL org.dallasmakerspace.state "Texas"
LABEL org.dallasmakerspace.country "USA"
LABEL org.dallasmakerspace.environment "production"
LABEL org.dallasmakerspace.application "infrastructure"
LABEL org.dallasmakerspace.role "web application"
LABEL org.dallasmakerspace.owner "infrastructure@dallasmakerspace.org"
LABEL org.dallasmakerspace.customer "COMMITTEES:Infrastructure"
LABEL org.dallasmakerspace.costcenter "FUNDS_MONTHLY:Web Hosting"
LABEL org.dallasmakerspace.oid "iso.org.dod.internet.50391"
LABEL org.dallasmakerspace.duns "iso.org.duns.053332191"

ARG FWATCHDOG_VERSION="0.7.1"

EXPOSE 80

ENV VIRTUAL_PORT 80
ENV VIRTUAL_PROTO http

HEALTHCHECK --interval=5s CMD 'curl -sSlk http://localhost/'

COPY . /var/www/html/

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
    && docker-php-ext-install iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install gd

RUN a2enmod rewrite && \
    apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        zlib1g-dev \
        libicu-dev \
        mcrypt \
        g++ \
    && curl -sL https://github.com/openfaas/faas/releases/download/${FWATCHDOG_VERSION}/fwatchdog > /usr/bin/fwatchdog \
    && chmod +x /usr/bin/fwatchdog \
    && docker-php-ext-configure intl \
    && pecl install redis  && docker-php-ext-enable redis \
    && pecl install mcrypt-1.0.1 && docker-php-ext-enable mcrypt \
    && pecl install pdo &&  && docker-php-ext-enable pdo \
    && pecl install pdo_mysq &&  && docker-php-ext-enable pdo_mysq \
    && pecl install mbstring &&  && docker-php-ext-enable mbstring \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && chmod -R 777 /var/www/html/{tmp,logs}
