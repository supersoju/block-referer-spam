<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// lib/RefSpamBlocker.php does `require_once ABSPATH . 'wp-admin/includes/misc.php'` at
// file scope, so ABSPATH needs to resolve to a real (stub) file even though these tests
// never boot WordPress.
define('ABSPATH', __DIR__ . '/stubs/');

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

require_once dirname(__DIR__) . '/lib/RefSpamBlocker.php';
