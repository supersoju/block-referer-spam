<?php

namespace WPBlockRefererSpam;

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
        if (!preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE'])) {
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
    }

    /**
     * init()
     */
    public function init() {
        // init
        add_action('admin_init', array(&$this, 'adminInit'));
        add_action('admin_menu', array(&$this, 'createMenu'));

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
            __('Block Referer Spam'),
            __('Referer Spam'),
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
            if ($this->downloadList()) {
                //add_settings_error('list-updated', 'list-updated', 'UPDATED', 'updated');
                $_SESSION['ref-spam-block-flash'] = 'list-updated';
                header('Location: admin.php?page=ref-spam-block');

            } else {
                //add_settings_error('list-updated', 'list-updated', 'UPDATED', 'error');
                $_SESSION['ref-spam-block-flash'] = 'list-not-updated';
                header('Location: admin.php?page=ref-spam-block&downloaded=false');
            }
            exit;

        } elseif (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
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

        // create copy of current .htaccess
        copy($htaccess, $htaccess . '.bak');

        // update htaccess
        insert_with_markers($htaccess, 'Referer Spam Blocker', $lines);
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

        if($pro_key_active == 'active'){

        } else {

            $api_params = array(
                'slm_action' => 'slm_activate',
                'secret_key' => REFSPAMBLOCKER_KEY,
                'license_key' => $pro_key,
                'registered_domain' => $_SERVER['SERVER_NAME']
            );

            $query = esc_url_raw(add_query_arg($api_params, 'https://www.blockreferspam.com/pro'));
            $response = wp_remote_get($query, array('timeout' => 20, 'sslverify' => false));
            if (is_wp_error($response)){
                $message = "Unexpected Error! The query returned with an error.";
                $_SESSION['ref-spam-block-proflash-status'] = 'error';
            };

            // License data.
            $license_data = json_decode(wp_remote_retrieve_body($response));

            if($license_data->result == 'success'){
                //Uncomment the followng line to see the message that returned from the license server
                //echo '<br />The following message was returned from the server: '.$license_data->message;
                $message = "Pro Version Activated";
                $_SESSION['ref-spam-block-proflash-status'] = 'success';
                update_option('ref-spam-pro-active', 'active');
            } else {
                //Show error to the user. Probably entered incorrect license key.
                $message .= 'The following message was returned from the server: ' . $license_data->message;
                $_SESSION['ref-spam-block-proflash-status'] = 'error';
                //update_option('ref-spam-pro-active', false);
            };

            $_SESSION['ref-spam-block-proflash'] = $message;
        };
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

        if(!$_SERVER['HTTP_REFERER']){
            return true;
        };

        // get domain
        $domain = str_ireplace('www.', '', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST));

        // get list
        $list = $this->getList();

        foreach ($list as $host) {
            if (strpos($domain, $host) !== false) {
                header('HTTP/1.0 403 Forbidden');
                echo 'You are forbidden from accessing this website.<br>' .
                    'Powered by <a href="' . REFSPAMBLOCKER_PLUGIN_URL . '">Referer Spam Blocker</a>.';
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

        // create context to send custom blocks back home
        $pro_key_active = get_option('ref-spam-pro-active');
        if($pro_key_active == 'active'){
            $header_array = array(
                'Content-type: application/json',
                'X-Client-Version: ' . REFSPAMBLOCKER_VERSION,
                'X-URL-Hash: ' . md5(get_site_url()),
                'X-Check-Domain: ' . $_SERVER['SERVER_NAME'],
                'X-Lic-Key: ' . get_option('ref-spam-pro-key'),
                'X-User-Agent: Block Referer Spam v' . REFSPAMBLOCKER_VERSION
            );
        } else {
            $header_array = array(
                'Content-type: application/json',
                'X-Client-Version: ' . REFSPAMBLOCKER_VERSION,
                'X-URL-Hash: ' . md5(get_site_url()),
                'X-User-Agent: Block Referer Spam v' . REFSPAMBLOCKER_VERSION
            );
        };
        $opts = array(
            'http' =>
                array(
                    'method'  => 'PUT',
                    'header'  => $header_array,
                    'content' => $customBlocks,
                    'timeout' => 30
                )
        );
        $context  = stream_context_create($opts);

        // download list and send custom blocks home
        $list = @file_get_contents(REFSPAMBLOCKER_LIST_URL, false, $context);

        if (!$list) {
            return false;
        };

        $list_obj = json_decode($list);

        if(!$list_obj->list){
            return false;
        };

        $formatted_list = "";
        foreach($list_obj->list as $item){
            $formatted_list .= $item . "\n";
        };

        if($list_obj->custom_blocks){
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

        return true;
    }

    /**
     * verifyCustomBlocks()
     * Verifies that only valid domains are being saved.
     */
    private function verifyCustomBlocks() {
        // use IDN class
        require_once(__DIR__ . '/phlylabs/idna_convert.class.php');
        $IDN = new idna_convert();

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
            $hostIdn = $IDN->encode($host);

            // add scheme if missing
            if (!parse_url($hostIdn, PHP_URL_SCHEME)) {
                $hostIdn = "http://{$hostIdn}";
            }

            // parse URL/host
            $hostIdn = parse_url($hostIdn, PHP_URL_HOST);
            $host = $IDN->decode($hostIdn);

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
    }

    /**
     * getList()
     * Function responsible to return a clear, merged list of referers
     *
     * @return array
     */
    private function getList() {
        // get original list
        $list = preg_split('/[\n\r]+/', get_option('ref-blocker-list'));

        // get custom blocks
        $customBlocks = preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'));

        // combine arrays
        $list = array_merge($list, $customBlocks);

        // clean up
        $list = array_unique(array_filter($list));

        return $list;
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
        // download list
        $this->downloadList();

        // update htaccess if necessary
        if (get_option('ref-spam-block-mode', 'rewrite') == 'rewrite') {
            $this->updateHtaccess();
        }
    }

    /**
     * Destroy session data when logging out
     */
    public function logout() {
        //session_destroy();
    }
}
