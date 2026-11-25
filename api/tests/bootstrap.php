<?php
/**
 * Copyright 2026 Anginger HR
 *
 * Licensed under the MIT License (https://opensource.org/licenses/MIT)
 *
 * PHPUnit bootstrap: boots a Yii web application against a throwaway
 * SQLite database (migrated + seeded once per run).
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

// throwaway database for the test run
$dbPath = sys_get_temp_dir() . '/anginger-test-' . getmypid() . '.sqlite';
foreach (glob($dbPath . '*') ?: [] as $f) {
    @unlink($f);
}
putenv('SQLITE_PATH=' . $dbPath);
putenv('COOKIE_VALIDATION_KEY=anginger-test-key');
putenv('LDAP_ENABLED=false');

// run migrations (console entry) with the same env; add -d flags when the
// local PHP CLI lacks pdo_sqlite (this dev machine loads it via extension dir)
$extFlags = '';
// this dev machine ships pdo_sqlite outside the default extension dir;
// pass it explicitly to the child process when present (no-op elsewhere)
foreach (glob('/tmp/phpext/ext/usr/lib/php/*/pdo_sqlite.so') ?: [] as $so) {
    $extFlags .= ' -d extension=' . $so;
}
$cmd = 'SQLITE_PATH=' . escapeshellarg($dbPath)
    . ' COOKIE_VALIDATION_KEY=anginger-test-key LDAP_ENABLED=false '
    . escapeshellarg(PHP_BINARY) . $extFlags . ' '
    . escapeshellarg(dirname(__DIR__) . '/console.php') . ' migrate --interactive=0 2>&1';
exec($cmd, $out, $code);
if ($code !== 0) {
    fwrite(STDERR, "migration failed:\n" . implode("\n", $out) . "\n");
    exit(1);
}

$config = require dirname(__DIR__) . '/config.php';
$config['components']['cache'] = ['class' => 'yii\\caching\\ArrayCache'];

new yii\web\Application($config);
