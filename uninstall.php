<?php

if (!defined('ABSPATH')) {
    exit();
}

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Remove all plugin options
$options = array(
    'ref-spam-auto-update',
    'ref-spam-custom-blocks',
    'ref-spam-block-mode',
    'ref-spam-pro-key',
    'ref-spam-pro-active',
    'ref-blocker-list',
    'ref-blocker-updated',
    'ref-spam-htaccess-backup',
);

foreach ($options as $option) {
    delete_option($option);
}

// Remove .htaccess rules — insert_with_markers with an empty array clears the section cleanly
$htaccess = ABSPATH . '.htaccess';
if (file_exists($htaccess) && is_writable($htaccess)) {
    require_once ABSPATH . 'wp-admin/includes/misc.php';
    insert_with_markers($htaccess, 'Referer Spam Blocker', array());
}

// Remove scheduled cron
wp_clear_scheduled_hook('dailyCronjob');
