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

// Guardrail: refuse to run tests against any non-local DB host. Fixtures
// TRUNCATE tables, so a misconfigured DATABASE_URL pointing at prod could
// wipe a 'dms-calendar-test' schema living on the prod RDS instance.
$host = $defaultConfig['host'] ?? 'localhost';
$allowedHosts = ['localhost', '127.0.0.1', '::1', 'db'];
if (!in_array($host, $allowedHosts, true)) {
    fwrite(STDERR, "REFUSING to run tests: 'default' connection host '{$host}' "
        . "is not in the local allowlist (" . implode(', ', $allowedHosts) . ").\n"
        . "If this is intentional, edit tests/bootstrap.php.\n");
    exit(1);
}

$defaultConfig['database'] = ($defaultConfig['database'] ?? 'dms-calendar') . '-test';
\Cake\Datasource\ConnectionManager::setConfig('test', $defaultConfig);
