#!/bin/bash

cd /var/www
rm -rf /var/www/html
ln -s /var/www/webroot /var/www/html
php /opt/composer/composer.phar install
#cp -R webroot /var/www/html
#chown -R www-data.www-data /var/www
cd /var/www/html
php /var/www/bin/cake.php migrations migrate
result=`mysql -N -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE -s -e "select count(*) from categories;"`
if (( $result == 0 ))
then
php /var/www/bin/cake.php migrations seed
fi

source /etc/apache2/envvars
apache2 -D FOREGROUND
