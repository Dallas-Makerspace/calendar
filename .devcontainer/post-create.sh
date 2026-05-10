#!/usr/bin/env bash
set -e

# Symlink webroot as Apache document root
rm -rf /var/www/html
ln -sf /var/www/webroot /var/www/html

# Copy php.ini and xdebug config
cp /var/www/.docker/php-dev.ini $PHP_INI_DIR/php.ini
cp /var/www/.docker/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Set up log files
mkdir -p /var/www/logs
touch /var/log/xdebug.log /var/www/logs/queries.log
chmod 666 /var/log/xdebug.log /var/www/logs/queries.log
mkdir -p /var/log/apache2
chmod 777 /var/log/apache2 /var/log

# Link app config
[ -f /var/www/config/app.php ] || ln -sf /var/www/config/app.default.php /var/www/config/app.php

# Install PHP dependencies
cd /var/www
php /opt/composer/composer.phar install

# Wait for DB and run migrations
/var/www/.docker/wait-for-it.sh "${DB_HOST:-db}:3306" -s -t 60 -- \
  php /var/www/bin/cake.php migrations migrate

# Seed if empty
result=$(mysql -N -h "${DB_HOST:-db}" -u "${DB_USERNAME:-calendar}" -p"${DB_PASSWORD:-calendar}" "${DB_DATABASE:-dms-calendar}" -s -e "select count(*) from categories;" 2>/dev/null || echo 0)
if [ "$result" -eq 0 ]; then
  php /var/www/bin/cake.php migrations seed
fi

# Start Apache in foreground via supervisord / background
source /etc/apache2/envvars
apache2 -D FOREGROUND &

echo "Dev environment ready. App running on port 80."
