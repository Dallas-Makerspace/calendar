# Configuration Notes

## Using existing configuration data

Use migrations to create and update database.  There are also several database seeders which can add some initial data to your database for event categories, committees, configurations, prerequisites, rooms and tools. More information on how to run those seeders is available in the [CakePHP Cookbook](https://book.cakephp.org/3.0/en/migrations.html#seed-seeding-your-database)

Copy the `config/app.default.php` file to `config/app.php`. This file is a mirror of the config file on the production server with accounts and other sensitive information removed.

## Adding to or updating the configuration

* Application configuration values can be added to `config/app.php`. When adding new values also mirror those additions in `config/app.default.php` using placeholder values for sensitive information.
* More information on [migrations in CakePHP](https://book.cakephp.org/3.0/en/migrations.html).
* The system uses [Phinx migrations](http://docs.phinx.org/en/latest/migrations.html) and [Seeds](http://docs.phinx.org/en/latest/seeding.html) 

## Customizing/Overriding Routes

Custom routes can be added in `config/routes.php`. These are most useful when making complex routes easier to read or more descriptive of their purpose. More information on [routing in CakePHP](https://book.cakephp.org/3.0/en/development/routing.html).