# DMS Calendar

## Development Quickstart Guide

For an overview of how the calendar works, see [./docs/README.md](./docs/README.md).

1. Fork this repo in Github.
2. Clone your forked version to your development computer using whatever tool works best for you (CLI/GUI/IDE/etc). Make sure to use the recursive clone flag.
3. If you forget to use the recursive clone, make sure any submodules are up to date with `git submodule update --init`
4. Create a local branch to hold your changes, you'll want to leave the master branch alone and keep it synced with the upstream repo.
5. Install a version of [Docker Desktop](https://www.docker.com/products/docker-desktop/), or something like [Rancher Desktop](https://rancherdesktop.io/)
6. After installation, you should be able to navigate to the root directory of this repository. Then type "docker compose up"
7. After some time, the containers  the application will be availbe at http://localhost:8080
8. Mail will be available at http://localhost:8025. PhpMyAdmin is at http://localhost:8081. Open LDAP is at http://localhost:8888
9. The docker environment automatically maps your local filesystem into the server. Any changes you make in the PHP files will be reflected. You may need to rebuild the app container if you change dependencies.
10. VS Code launch settings and xdebug files are included for convenience.

Ctrl+C will stop your containers
`docker compose down` will reset your containers along with your data
Log into OpenLDAP with `cn=admin,dc=dms,dc=local`, password `Adm1n!`
Default credentials for application will be `user1` and `password`
user2 may be used for admin rights
## Installation

1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar install`.

You should now have all of the components needed to run a local version of the calendar application. Check this by ensuring that the `vendor` directory in the root folder contains several folders with imported packages.

## Configuration

Use migrate command to create database.  There are also several database seeders which can add some initial data to your database for event categories, committees, configurations, prerequisites, rooms and tools. More information on how to run those seeders is available in the [CakePHP Cookbook](https://book.cakephp.org/3.0/en/migrations.html#seed-seeding-your-database)

Copy the `config/app.default.php` file to `config/app.php`. This file is a mirror of the config file on the production server with accounts and other sensitive information removed.

The `.htaccess-sample` file in the root directory can, in most cases, be renamed to `.htaccess` and provide everything needed for CakePHP to route necessary traffic to the `webroot` directory.

