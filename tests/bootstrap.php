<?php
/**
 * Test runner bootstrap.
 *
 * Add additional configuration/setup your application needs when running
 * unit tests in this file.
 */
require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

$_SERVER['PHP_SELF'] = '/';

// Register a dedicated 'test' datasource that mirrors the default
// connection but points at a separate database. Fixtures truncate
// tables, so we never want them touching the dev database.
$defaultConfig = \Cake\Datasource\ConnectionManager::getConfig('default');
$defaultConfig['database'] = ($defaultConfig['database'] ?? 'dms-calendar') . '-test';
\Cake\Datasource\ConnectionManager::setConfig('test', $defaultConfig);
