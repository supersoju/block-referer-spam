<?php

namespace WPBlockRefererSpam;

require_once ABSPATH . 'wp-admin/includes/misc.php';

/**
 * RefSpamBlocker
 *
 * @author codestic <hello@codestic.com>
 *
 * ==========================================
 * | ,dP""8a "888888b,  d8b    "888b  ,888" |
 * | 88b   "  888  d88 dPY8b    88Y8b,8888  |
 * | `"Y8888a 888ad8P'dPaaY8b   88 Y88P888  |
 * | a,   Y88 888    dP    Y8b  88  YP 888  |
 * | `"8ad8P'a888a  a88a;*a888aa88a   a888a |
 * |                ;*;;;;*;;;*;;;*,,       |
 * |        _,---'':::';*;;;*;;;*;;*d;,     |
 * |     .-'      ::::::::::';*;;*;dII;     |
 * |   .' ,<<<,.  :::::::::::::::ffffff`.   |
 * |  / ,<<<<<<<<,::::::::::::::::fffffI,\  |
 * | .,<<<<<<<<<<I;:::::::::::::::ffffKIP", |
 * | |<<<<<<<<<<dP;,?>;,::::::::::fffKKIP | |
 * | ``<<<<<<<dP;;;;;\>>>>>;,::::fffKKIPf ' |
 * |  \ `mYMMV?;;;;;;;\>>>>>>>>>,YIIPP"` /  |
 * |   `. "":;;;;;;;;;i>>>>>>>>>>>>>,  ,'   |
 * |     `-._``":;;;sP'`"?>>>>>=========.   |
 * |         `---..._______...|<[Hormel |   |
 * |                          `========='   |
 * =====================================(FL)=
 *
 * More: https://en.wikipedia.org/wiki/Spam_(food)
 */

class RefSpamBlocker {

    public function __construct($pluginFile) {
        // load text domain
        load_textdomain('ref-spam-blocker', REFSPAMBLOCKER_PATH . 'lang/ref-spam-blocker-' . get_locale() . '.mo');

        // register activation
        register_activation_hook($pluginFile, array(&$this, 'activate'));
        // register deactivation
        register_deactivation_hook($pluginFile, array(&$this, 'deactivate'));

        // add actions
        add_action('plugins_loaded', array(&$this, 'init'));
        add_action('admin_init', array($this, 'registerSettings'));
        add_action('dailyCronjob', array($this, 'dailyCronjob'));
        add_action('wp', array(&$this, 'pageLoad'));

        add_action('wp_logout', array($this, 'logout'));
        add_action('wp_login', array($this, 'logout'));
    }

    /**
     * activate()
     */
    public function activate() {
        // if not apache, set block mode default to WordPress block
        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
        if (!preg_match('/apache/i', $serverSoftware)) {
            update_option('ref-spam-block-mode', 'wordpress');
        }

        // schedule daily update
        wp_schedule_event(time(), 'daily', 'dailyCronjob');
    }

    /**
     * deactivate()
     */
    public function deactivate() {
        $this->resetHtaccess();
        wp_clear_scheduled_hook('dailyCronjob');
    }

    /**
     * init()
     */
    public function init() {
        // init
        add_action('admin_init', array(&$this, 'adminInit'));
        add_action('admin_menu', array(&$this, 'createMenu'));
        add_action('admin_notices', array(&$this, 'cachingPluginNotice'));

        /*
        if (!session_id()) {
            session_start();
        }
         */
    }

    /**
     * adminInit()
     * Currently only used to register the CSS styles
     */
    public function adminInit() {
        wp_register_style('ref-block-styles', plugins_url('../assets/styles/ref-block.css', __FILE__));
    }

    /**
     * cachingPluginNotice()
     * Shows a warning on the plugin's own admin pages when WordPress block mode
     * is active and a full-page caching plugin is detected, since cached pages
     * are served before the wp hook fires.
     */
    public function cachingPluginNotice() {
        // Only show on this plugin's pages
        $page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
        if (strpos($page, 'ref-spam') === false) {
            return;
        }

        if (get_option('ref-spam-block-mode', 'rewrite') !== 'wordpress') {
            return;
        }

        $detected = $this->detectActiveCachingPlugin();
        if (!$detected) {
            return;
        }

        echo '<div class="notice notice-warning">'
            . '<p><strong>' . esc_html__('Block Referer Spam — Caching Plugin Conflict', 'ref-spam-blocker') . '</strong></p>'
            . '<p>' . sprintf(
                esc_html__('%s is active. Full-page caching serves pages before WordPress runs, so WordPress Block mode cannot intercept those requests. Switch to Rewrite Block mode (Apache only) to block referer spam reliably when caching is enabled.', 'ref-spam-blocker'),
                '<strong>' . esc_html($detected) . '</strong>'
            ) . '</p>'
            . '</div>';
    }

    /**
     * detectActiveCachingPlugin()
     * Returns the display name of the first detected active caching plugin, or false.
     */
    private function detectActiveCachingPlugin() {
        $plugins = array(
            'wp-rocket/wp-rocket.php'                       => 'WP Rocket',
            'w3-total-cache/w3-total-cache.php'             => 'W3 Total Cache',
            'wp-super-cache/wp-cache.php'                   => 'WP Super Cache',
            'litespeed-cache/litespeed-cache.php'           => 'LiteSpeed Cache',
            'autoptimize/autoptimize.php'                   => 'Autoptimize',
            'cache-enabler/cache-enabler.php'               => 'Cache Enabler',
            'comet-cache/comet-cache.php'                   => 'Comet Cache',
            'hummingbird-performance/wp-hummingbird.php'    => 'Hummingbird',
            'sg-cachepress/sg-cachepress.php'               => 'SG Optimizer',
            'swift-performance-lite/swift-performance-lite.php' => 'Swift Performance',
        );

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        foreach ($plugins as $file => $name) {
            if (is_plugin_active($file)) {
                return $name;
            }
        }

        return false;
    }

    /**
     * pluginLoad()
     * Currently only used to load the registered CSS styles
     */
    public function pluginLoad() {
        wp_enqueue_style('ref-block-styles');
    }

    /**
     * createMenu()
     * Create Admin Menu
     */
    public function createMenu() {
        $hook = add_menu_page(
            __('Block Referer Spam', 'ref-spam-blocker'),
            __('Referer Spam', 'ref-spam-blocker'),
            'manage_options',
            'ref-spam-block/',
            array(&$this, 'adminDashboard'),
            'dashicons-shield-alt'
        );

        $subhook = add_submenu_page(
            'ref-spam-block',
            'Blocked Sites',
            __('All Blocked Sites', 'ref-spam-blocker'),
            'manage_options',
            'ref-spam-list/',
            array(&$this, 'adminBlockedList')
        );

        /*
        add_submenu_page(
            'ref-spam-block',
            'Pro',
            'Pro',
            'manage_options',
            'pro-options/',
            array(&$this, 'adminProOptions')
        );
         */

        add_action("load-{$hook}", array(&$this, 'updateSettings'));
        add_action('admin_print_styles-' . $hook, array(&$this, 'pluginLoad'));
        add_action('admin_print_styles-' . $subhook, array(&$this, 'pluginLoad'));
    }

    /**
     * registerSettings()
     */
    public function registerSettings() {
        register_setting('ref-spam-block-settings', 'ref-spam-auto-update');
        register_setting('ref-spam-block-settings', 'ref-spam-custom-blocks');
        register_setting('ref-spam-block-settings', 'ref-spam-block-mode');
        register_setting('ref-spam-block-settings', 'ref-spam-pro-key');
        register_setting('ref-spam-block-settings', 'ref-spam-pro-active');
    }

    /**
     * updateSettings()
     */
    public function updateSettings() {

        // download
        if (isset($_GET['download']) && $_GET['download'] == 'true') {
            check_admin_referer('ref-spam-download');
            if ($this->downloadList()) {
                set_transient('ref_spam_flash_' . get_current_user_id(), 'list-updated', 60);
                header('Location: admin.php?page=ref-spam-block');

            } else {
                set_transient('ref_spam_flash_' . get_current_user_id(), 'list-not-updated', 60);
                header('Location: admin.php?page=ref-spam-block&downloaded=false');
            }
            exit;

        } elseif (isset($_GET['settings-updated']) && sanitize_text_field(wp_unslash($_GET['settings-updated']))) {
            // verify custom blocks
            $this->verifyCustomBlocks();

            // get block mode
            $blockMode = get_option('ref-spam-block-mode', 'rewrite');

            if ($blockMode == 'rewrite') {
                // update htaccess
                $this->updateHtaccess();

            } else {
                // reset htaccess
                $this->resetHtaccess();
            }

            $pro_key = get_option('ref-spam-pro-key');
            if($pro_key){
                $this->verifyProKey();
            }
        }
    }

    /*
     * updateHtaccess()
     */
    private function updateHtaccess() {
        // htaccess path
        $htaccess = ABSPATH . '.htaccess';

        // build lines
        $lines = array();
        $lines[] = '<IfModule mod_rewrite.c>';
        $lines[] = '  RewriteEngine on';

        // load list into array
        $list = $this->getList();

        foreach ($list as $host) {
            /* RewriteCond for wildcard and plain entry */
            $lines[] = "    RewriteCond %{HTTP_REFERER} {$host} [NC,OR]";
            $lines[] = "    RewriteCond %{HTTP_REFERER} ^https?://[^/]+\\." . $host . " [NC" . ($host == end($list) ? '' : ',OR') . "]";
        }

        $lines[] = '  RewriteRule .* - [F]';
        $lines[] = '</IfModule>';

        // back up current .htaccess contents in an option rather than a
        // world-readable .bak file left sitting in the webroot
        update_option('ref-spam-htaccess-backup', file_get_contents($htaccess), false);

        // update htaccess
        insert_with_markers($htaccess, 'Referer Spam Blocker', $lines);

        // ensure our block runs before any caching plugin rules
        $this->ensureHtaccessOrdering($htaccess);
    }

    /**
     * ensureHtaccessOrdering()
     * Moves the Referer Spam Blocker block above any caching plugin blocks so
     * the referer check runs before cached pages are served.
     */
    private function ensureHtaccessOrdering($htaccess) {
        $content = file_get_contents($htaccess);

        $our_begin = '# BEGIN Referer Spam Blocker';
        $our_end   = '# END Referer Spam Blocker';

        $our_begin_pos = strpos($content, $our_begin);
        $our_end_pos   = strpos($content, $our_end);

        if ($our_begin_pos === false || $our_end_pos === false) {
            return;
        }

        // Known caching plugin begin markers
        $cache_markers = array(
            '# BEGIN WPRocket',
            '# BEGIN W3TC',
            '# BEGIN WP Super Cache',
            '# BEGIN LiteSpeed',
            '# BEGIN Autoptimize',
            '# BEGIN Cache Enabler',
        );

        // Find the earliest caching plugin block
        $earliest = false;
        foreach ($cache_markers as $marker) {
            $pos = strpos($content, $marker);
            if ($pos !== false && ($earliest === false || $pos < $earliest)) {
                $earliest = $pos;
            }
        }

        // No caching plugin found, or we already appear before it
        if ($earliest === false || $our_begin_pos < $earliest) {
            return;
        }

        // Extract our full block (inclusive of both marker lines)
        $block_end  = $our_end_pos + strlen($our_end);
        $our_block  = substr($content, $our_begin_pos, $block_end - $our_begin_pos);

        // Remove our block from its current position, collapsing the blank line it leaves
        $before = substr($content, 0, $our_begin_pos);
        $after  = ltrim(substr($content, $block_end), "\n");
        $content_without = rtrim($before) . "\n\n" . $after;

        // Recalculate position of the caching block after removal
        $earliest_new = false;
        foreach ($cache_markers as $marker) {
            $pos = strpos($content_without, $marker);
            if ($pos !== false && ($earliest_new === false || $pos < $earliest_new)) {
                $earliest_new = $pos;
            }
        }

        if ($earliest_new === false) {
            return;
        }

        // Insert our block immediately before the earliest caching block
        $new_content = substr($content_without, 0, $earliest_new)
                     . $our_block . "\n\n"
                     . substr($content_without, $earliest_new);

        file_put_contents($htaccess, $new_content);
    }

    /**
     * resetHtaccess()
     * @return bool
     */
    private function resetHtaccess() {
        // htaccess path
        $htaccess = ABSPATH . '.htaccess';

        // load htaccess
        $content = file_get_contents($htaccess);

        // define tags
        $startTag = '# BEGIN Referer Spam Blocker';
        $endTag = '# END Referer Spam Blocker';

        if (strpos($content, $startTag) === false) {
            return false;
        }

        $startPos = strpos($content, $startTag);
        $endPos = strpos($content, $endTag);
        $textToDelete = substr($content, $startPos, ($endPos + strlen($endTag)) - $startPos);

        $content = str_replace($textToDelete, '', $content);

        $content = trim($content);

        file_put_contents($htaccess, $content);

        return true;
    }

    /**
     * verifyProKey()
     * @return bool
     */
    private function verifyProKey() {

        $pro_key = get_option('ref-spam-pro-key');
        $pro_key_active = get_option('ref-spam-pro-active');

        if ($pro_key_active == 'active') {
            return true;
        }

        $api_params = array(
            'slm_action' => 'slm_activate',
            'secret_key' => REFSPAMBLOCKER_KEY,
            'license_key' => $pro_key,
            'registered_domain' => isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : ''
        );

        $query = esc_url_raw(add_query_arg($api_params, 'https://www.blockreferspam.com/pro'));
        $response = wp_remote_get($query, array('timeout' => 20));

        if (is_wp_error($response)) {
            set_transient('ref_spam_proflash_' . get_current_user_id(), array(
                'status'  => 'error',
                'message' => 'Unexpected Error! The query returned with an error.',
            ), 60);
            return false;
        }

        // License data.
        $license_data = json_decode(wp_remote_retrieve_body($response));

        if ($license_data->result == 'success') {
            update_option('ref-spam-pro-active', 'active');
            set_transient('ref_spam_proflash_' . get_current_user_id(), array(
                'status'  => 'success',
                'message' => 'Pro Version Activated',
            ), 60);
        } else {
            set_transient('ref_spam_proflash_' . get_current_user_id(), array(
                'status'  => 'error',
                'message' => 'The following message was returned from the server: ' . $license_data->message,
            ), 60);
        }

        return true;
    }

    /**
     * pageLoad()
     * Function responsible for blocking in "WordPress" mode.
     *
     * @return bool
     */
    public function pageLoad() {
        // check block mode
        if (get_option('ref-spam-block-mode') != 'wordpress') {
            return false;
        };

        if(empty($_SERVER['HTTP_REFERER'])){
            return true;
        };

        // get domain
        $referer = sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER']));
        $domain = str_ireplace('www.', '', wp_parse_url($referer, PHP_URL_HOST));

        // get list
        $list = $this->getList();

        foreach ($list as $host) {
            if (strpos($domain, $host) !== false) {
                header('HTTP/1.0 403 Forbidden');
                echo 'You are forbidden from accessing this website.<br>' .
                    'Powered by <a href="' . esc_url(REFSPAMBLOCKER_PLUGIN_URL) . '">Referer Spam Blocker</a>.';
                exit;
            };
        };

        return true;
    }

    /**
     * downloadList()
     * Function responsible to download the referer spam list
     *
     * @return bool
     */
    private function downloadList() {
        // get custom blocks
        $customBlocks = json_encode(array_filter(preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'))));

        // create headers to send custom blocks back home
        $pro_key_active = get_option('ref-spam-pro-active');
        $header_array = array(
            'Content-type'    => 'application/json',
            'X-Client-Version' => REFSPAMBLOCKER_VERSION,
            'X-URL-Hash'      => md5(get_site_url()),
            'X-User-Agent'    => 'Block Referer Spam v' . REFSPAMBLOCKER_VERSION,
        );
        if ($pro_key_active == 'active') {
            $header_array['X-Check-Domain'] = isset($_SERVER['SERVER_NAME']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])) : '';
            $header_array['X-Lic-Key']      = get_option('ref-spam-pro-key');
        };

        // download list and send custom blocks home
        $response = wp_remote_request(REFSPAMBLOCKER_LIST_URL, array(
            'method'  => 'PUT',
            'headers' => $header_array,
            'body'    => $customBlocks,
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            return false;
        };

        $list = wp_remote_retrieve_body($response);

        if (!$list) {
            return false;
        };

        $list_obj = json_decode($list);

        if (!is_object($list_obj) || empty($list_obj->list)) {
            return false;
        };

        $formatted_list = "";
        foreach($list_obj->list as $item){
            $formatted_list .= $item . "\n";
        };

        if(!empty($list_obj->custom_blocks)){
            $custom_list = "";
            foreach($list_obj->custom_blocks as $item){
                $custom_list .= $item . "\n";
            };
            // save custom list
            update_option('ref-spam-custom-blocks', $custom_list);
        };

        // save list
        update_option('ref-blocker-list', $formatted_list);

        // save last updated stamp
        update_option('ref-blocker-updated', time());

        $this->invalidateListCache();

        return true;
    }

    /**
     * verifyCustomBlocks()
     * Verifies that only valid domains are being saved.
     */
    private function verifyCustomBlocks() {
        // get list
        $list = array_unique(array_filter(preg_split('/[\n\r]+/', get_option('ref-blocker-list'))));

        // get custom blocks
        $customBlocks = array_unique(array_filter(preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'))));

        // validate custom blocks
        $hasErrors = false;
        $hasDuplicates = false;

        foreach ($customBlocks as $i => &$host) {

            // check for duplicates
            if (in_array($host, $list)) {
                $hasDuplicates = true;
                $host = NULL;
                continue;
            }

            // decode, may be an IDN domain
            $hostIdn = $this->idnEncode($host);

            // add scheme if missing
            if (!wp_parse_url($hostIdn, PHP_URL_SCHEME)) {
                $hostIdn = "http://{$hostIdn}";
            }

            // parse URL/host
            $hostIdn = wp_parse_url($hostIdn, PHP_URL_HOST);
            $host = $this->idnDecode($hostIdn);

            // quit right here!
            if (!isset($hostIdn)) {
                $host = NULL;
                $hasErrors = true;
                continue;
            }

            if (!filter_var("http://{$hostIdn}", FILTER_VALIDATE_URL)) {
                $host = NULL;
                $hasErrors = true;
                continue;
            }
        }

        // check if we had duplicates
        if ($hasDuplicates === true) {
            add_settings_error('ref-spam-custom-blocks', 'redundant_hosts',
                __('At least one of your custom blocks was ignored because it is already covered by the internal blocks.<br><span style="font-weight: normal">If you think that this was a mistake, please <em>get in touch!</em></span>', 'ref-spam-blocker'),
                'error');
        }

        // check if there were any errors
        if ($hasErrors === true) {
            add_settings_error('ref-spam-custom-blocks', 'invalid_hosts',
                __('At least one of your custom blocks was not valid and was removed.<br><span style="font-weight: normal">Please enter only valid URLs or hostnames (e.g. <span class="monospace">http://wwww.some-dodgy-site.com</span> or <span class="monospace">some-dodgy-site.com</span>). If you think that this was a mistake, please <em>get in touch!</em></span>', 'ref-spam-blocker'),
                'error');
        }

        // filter empty values out
        $customBlocks = array_filter($customBlocks);

        // update option
        update_option('ref-spam-custom-blocks', implode("\n", $customBlocks));

        $this->invalidateListCache();
    }

    /**
     * idnEncode()
     * Converts a domain to its ASCII/Punycode form. Uses the native intl extension
     * when available (present on most hosts today) and only falls back to the bundled
     * pure-PHP codec — kept for the hosts without ext-intl this plugin still supports —
     * when the native function is unavailable or fails to encode the input.
     */
    private function idnEncode($string) {
        if (function_exists('idn_to_ascii')) {
            $result = idn_to_ascii($string, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($result !== false) {
                return $result;
            }
        }

        require_once(__DIR__ . '/phlylabs/idna_convert.class.php');
        $IDN = new idna_convert();

        return $IDN->encode($string);
    }

    /**
     * idnDecode()
     * Converts a Punycode-encoded domain back to its Unicode form. See idnEncode().
     */
    private function idnDecode($string) {
        if (function_exists('idn_to_utf8')) {
            $result = idn_to_utf8($string, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if ($result !== false) {
                return $result;
            }
        }

        require_once(__DIR__ . '/phlylabs/idna_convert.class.php');
        $IDN = new idna_convert();

        return $IDN->decode($string);
    }

    /**
     * getList()
     * Function responsible to return a clear, merged list of referers
     *
     * @return array
     */
    private function getList() {
        $cached = wp_cache_get('merged-list', 'ref-spam-blocker');
        if ($cached !== false) {
            return $cached;
        }

        // get original list
        $list = preg_split('/[\n\r]+/', get_option('ref-blocker-list'));

        // get custom blocks
        $customBlocks = preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'));

        // combine arrays
        $list = array_merge($list, $customBlocks);

        // clean up
        $list = array_unique(array_filter($list));

        wp_cache_set('merged-list', $list, 'ref-spam-blocker', HOUR_IN_SECONDS);

        return $list;
    }

    /**
     * invalidateListCache()
     * Clears the cached merged block list after ref-blocker-list or
     * ref-spam-custom-blocks changes.
     */
    private function invalidateListCache() {
        wp_cache_delete('merged-list', 'ref-spam-blocker');
    }

    /**
     * adminDashboard()
     */
    public function adminDashboard() {
        include(dirname(__FILE__) . '/../admin/dashboard.php');
    }

    /**
     * adminBlockedList()
     */
    public function adminBlockedList() {
        include(dirname(__FILE__) . '/../admin/blocked-list.php');
    }

    /**
     * adminProOptions()
     */
    public function adminProOptions() {
        include(dirname(__FILE__) . '/../admin/pro-options.php');
    }

    /**
     * dailyCronjob()
     * Executed daily to update list and htaccess file
     */
    public function dailyCronjob() {
        // respect the auto-update setting
        if (get_option('ref-spam-auto-update', 'yes') !== 'yes') {
            return;
        }

        // download list
        $this->downloadList();

        // update htaccess if necessary
        if (get_option('ref-spam-block-mode', 'rewrite') == 'rewrite') {
            $this->updateHtaccess();
        }
    }

    /**
     * Clear flash transients on logout
     */
    public function logout() {
        delete_transient('ref_spam_flash_' . get_current_user_id());
        delete_transient('ref_spam_proflash_' . get_current_user_id());
    }
}
