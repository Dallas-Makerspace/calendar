#!/bin/bash

rm -rf /var/www/html
ln -s /var/www/webroot /var/www/html
cp /var/www/.docker/php-dev.ini $PHP_INI_DIR/php.ini
cp /var/www/.docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
mkdir -p /var/www/logs
touch /var/log/xdebug.log
touch /var/www/logs/queries.log
ln -s /var/www/config/app.default.php /var/www/config/app.php && \
chmod 666 /var/log/xdebug.log
chmod 666 /var/www/logs/queries.log

cd /var/www
php /opt/composer/composer.phar install
php /var/www/bin/cake.php migrations migrate
result=`mysql -N -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE -s -e "select count(*) from categories;"`
if (( $result == 0 ))
then
php /var/www/bin/cake.php migrations seed
fi

source /etc/apache2/envvars
apache2 -D FOREGROUND
