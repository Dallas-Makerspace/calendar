<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

// Note: no connection aliasing here. The fixture manager aliases `default`
// to the `test` datasource while fixtures are in use, which requires the
// `test` connection to be configured (see config/app.default.php).

$_SERVER['PHP_SELF'] = '/';
