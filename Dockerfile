FROM hhvm/hhvm-proxygen:latest

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

ENV DEBIAN_FRONTEND noninteractive

# Install Dependancies
RUN apt-get update -y && apt-get install -y curl

# Install composer
RUN mkdir /opt/composer
RUN curl -sS https://getcomposer.org/installer | hhvm --php -- --install-dir=/opt/composer

# Install the app
RUN rm -rf /var/www
COPY . /var/www
RUN cd /var/www && hhvm /opt/composer/composer.phar install

# Reconfigure HHVM
#ADD hhvm.prod.ini /etc/hhvm/site.ini

EXPOSE 80
HEALTHCHECK --interval=5m CMD 'curl -sSlk http://localhost/ || exit -1'
