# DMS Calendar [![Build Status](https://travis-ci.org/Dallas-Makerspace/calendar.svg?branch=master)](https://travis-ci.org/Dallas-Makerspace/calendar) [![License](https://img.shields.io/github/license/Dallas-Makerspace/calendar.svg?style=flat-square)](https://github.com/Dallas-Makerspace/calendar/blob/master/LICENCE) [![Coverage Status](https://coveralls.io/repos/github/Dallas-Makerspace/calendar/badge.svg?branch=master)](https://coveralls.io/github/Dallas-Makerspace/calendar?branch=master)
[![Release](https://img.shields.io/github/tag/Dallas-Makerspace/calendar.svg?style=flat-square)](https://github.com/Dallas-Makerspace/calendar/tags)

Find a copy of the latest build at [Docker Hub](https://hub.docker.com/r/dallasmakerspace/calendar/).

## Prerequisites

* PHP-ldap
* PHP >=5.5.9
* MySQL (version compatible with your environment)
* Composer

## Installation

1. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar install`.

You should now have all of the components needed to run a local version of the calendar application. Check this by ensuring that the `vendor` directory in the root folder contains several folders with imported packages.

## Configuration

Use migrate command to create database.  There are also several database seeders which can add some initial data to your database for event categories, committees, configurations, prerequisites, rooms and tools. More information on how to run those seeders is available in the [CakePHP Cookbook](https://book.cakephp.org/3.0/en/migrations.html#seed-seeding-your-database)

Copy the `config/app.default.php` file to `config/app.php`. This file is a mirror of the config file on the production server with accounts and other sensitive information removed.

The `.htaccess-sample` file in the root directory can, in most cases, be renamed to `.htaccess` and provide everything needed for CakePHP to route necessary traffic to the `webroot` directory.
