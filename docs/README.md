# App design docs

This provides an overview of how the calendar works.


## Services

![Service diagram](./services.png)

DMS calendar communicates with:

* MariaDB: holds persistent data (e.g. calendar events, attendees, etc.); MySQL-compatible database server
* DMS Active Directory (LDAP): connects the calendar to DMS accounts used across DMS's other services; account / authentication server
* Braintree: credit card processesing; third-party service
* Sparkpost: sends email notifications? third-party service
* Twilio: sends SMS notifications; third-party service


## Webserver

The webserver is one of:

* Production: [Apache](https://httpd.apache.org/) and [PHP-FPM](https://www.php.net/manual/en/install.fpm.php) for calendar.dallasmakerspace.org. If you run locally without Docker, you might also be using these.


You can think of these as wrappers around CakePHP that handle low-level stuff like managing parallel requests, network connections, etc. They just invoke [index.php](../index.php) with each request. This activates the CakePHP framework, and eventually the calendar code under [src/](../src/) is invoked (see next section).


## Application

The app is built with CakePHP, which is a pretty typical [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) framework with [CRUD](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete)-centric route handling.

Key files:

* [index.php](../index.php) - entrypoint for everything
* [config/bootstrap.php](../config/bootstrap.php) - global setup type stuff
* [config/routes.php](../config/routes.php) - URL-to-Controller mappings
* [src/](../src/) - all the core business logic for the calendar and associated UI markup

Here's how a response is computed:

![Request / response sequence](./request-response-sequence.png)

More details on the rest can be found at [CakePHP](https://book.cakephp.org/) - [request cycle](https://book.cakephp.org/3/en/intro.html#cakephp-request-cycle) and [controllers request flow](https://book.cakephp.org/3/en/controllers.html#request-flow) are especially helpful.

**Warning:** check [composer.json](../composer.json) to ensure you're looking at docs for the right version of CakePHP.

