# Configuration Notes

## Using existing configuration data

Create a MySQL database and seed the database using the sql file at `config/schema/schema.sql`. There are also several database seeders which can add some initial data to your database for event categories, committees, configurations, prerequisites, rooms and tools. More information on how to run those seeders is available in the [CakePHP Cookbook](https://book.cakephp.org/3.0/en/migrations.html#seed-seeding-your-database)

Copy the `config/app.default.php` file to `config/app.php`. This file is a mirror of the config file on the production server with accounts and other sensitive information removed.

## Adding to or updating the configuration

* Application configuration values can be added to `config/app.php`. When adding new values also mirror those additions in `config/app.default.php` using placeholder values for sensitive information.
* When amending the database schema a database migration should be created to reflect that change. *NOTE THAT THE CURRENT MIGRATIONS ARE NOT UP TO DATE.* Up to date migrations will be generated from the current schema and this section will be updated to reflect when they are available. More information on [migrations in CakePHP](https://book.cakephp.org/3.0/en/migrations.html)