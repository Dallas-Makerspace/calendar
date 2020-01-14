# DMS Calendar [![Build Status](https://travis-ci.org/Dallas-Makerspace/calendar.svg?branch=master)](https://travis-ci.org/Dallas-Makerspace/calendar) [![Docker Build Status](https://img.shields.io/docker/build/dallasmakerspace/calendar.svg)](https://hub.docker.com/r/dallasmakerspace/calendar) [![License](https://img.shields.io/github/license/Dallas-Makerspace/calendar.svg?style=flat-square)](https://github.com/Dallas-Makerspace/calendar/blob/master/LICENCE) [![Coverage Status](https://coveralls.io/repos/github/Dallas-Makerspace/calendar/badge.svg?branch=master)](https://coveralls.io/github/Dallas-Makerspace/calendar?branch=master)
[![Release](https://img.shields.io/github/tag/Dallas-Makerspace/calendar.svg?style=flat-square)](https://github.com/Dallas-Makerspace/calendar/tags)


Find a copy of the latest build at [Docker Hub](https://hub.docker.com/r/dallasmakerspace/calendar/). Join us <a href="https://discord.gg/rDVJgbe"><img src="https://img.shields.io/discord/300062029559889931.svg?logo=discord" alt="on Discord"></a>.

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

## Development Quickstart Guide

For an overview of how the calendar works, see [./docs/README.md](./docs/README.md).

These instructions have only been tested on Ubuntu Linux, the commands may vary slightly for other OS/distro.
1. Fork this repo in Github.
2. Clone your forked version to your development computer using whatever tool works best for you (CLI/GUI/IDE/etc).
3. Create a local branch to hold your changes, you'll want to leave the master branch alone and keep it synced with the upstream repo.
4. If you don't have the latest version of [Composer](http://getcomposer.org/doc/00-intro.md), download it or update using `composer self-update`.
5. Run `php composer.phar install` to install all the required packages, this will also automatically create an `config/app.php` file for you.
6. Install MySQL server if you don't already have it (this varies based on OS/distro).
7. Create a MySQL database and user with access to that database.
8. Run `DATABASE_URL="mysql://mysql_user_you_made:password_for_that_user@localhost/name_of_the_database" bin/cake migrations migrate` this will create all the necessary tables/structure.
9. Run `DATABASE_URL="mysql://mysql_user_you_made:password_for_that_user@localhost/name_of_the_database" bin/cake migrations seed` to fill those tables with some useful data.
10. Copy the `MockActiveDirectory` configuration from `config/app.testing.php` to your `config/app.php` file. This will let you "login" using mock users/accounts.
11. Run `DATABASE_URL="mysql://mysql_user_you_made:password_for_that_user@localhost/name_of_the_database" bin/cake server` and then open http://localhost:8765/ in a browser.
12. When your changes are done, create a pull request via Github, all the changes in the branch you created will show up in the PR. If additional changes need to be made before the PR is merged, you can simply add more commits to your branch.
