<?php

namespace WPBlockRefererSpam;

/*
Plugin Name: Block Referer Spam
Plugin URI: https://wordpress.org/plugins/block-referer-spam/
Description: Prevents referer spam from accessing your site and cleans up your Google Analytics in the process.
Author: supersoju, codestic
Version: 1.1.9.2
Author URI: http://supersoju.com
Text Domain: ref-spam-blocker
Domain Path: /languages
*/

if (defined('ABSPATH') && !class_exists('RefSpamBlocker')) {
    if (!defined('REFSPAMBLOCKER_VERSION')) {
        define('REFSPAMBLOCKER_VERSION', '1.1.9.2');
    }

    if (!defined('REFSPAMBLOCKER_TEXTDOMAIN')) {
        define('REFSPAMBLOCKER_TEXTDOMAIN', 'ref-spam-blocker');
    }

    if (!defined('REFSPAMBLOCKER_DIRNAME')) {
        define('REFSPAMBLOCKER_DIRNAME', dirname(plugin_basename(__FILE__)));
    }

    if (!defined('REFSPAMBLOCKER_PATH')) {
        define('REFSPAMBLOCKER_PATH', trailingslashit(dirname(__FILE__)));
    }

    if (!defined('REFSPAMBLOCKER_URL')) {
        define('REFSPAMBLOCKER_URL', trailingslashit(plugins_url('', __FILE__)));
    }

    define('REFSPAMBLOCKER_PLUGIN_URL', 'https://www.blockreferspam.com');
    define('REFSPAMBLOCKER_LIST_URL', 'https://blockreferspam.com');
    define('REFSPAMBLOCKER_KEY', '586d3df5da9b55.70906614');

}

require_once REFSPAMBLOCKER_PATH . 'lib/RefSpamBlocker.php';

new RefSpamBlocker(__FILE__);
