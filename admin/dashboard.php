<div class="wrap">
    <h2><?php _e('Block Referer Spam', 'ref-spam-blocker'); ?></h2>

    <?php if (isset($_SESSION['ref-spam-block-flash'])) : ?>
        <?php if ($_SESSION['ref-spam-block-flash'] == 'list-updated') : ?>
            <div id="message" class="updated">
                <p><strong><?php _e('List updated.', 'ref-spam-blocker') ?></strong></p>
            </div>

        <?php elseif ($_SESSION['ref-spam-block-flash'] == 'list-not-updated') : ?>
            <div id="message" class="error">
                <p><strong><?php _e('List failed to update.', 'ref-spam-blocker') ?></strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['ref-spam-block-proflash'])) { ?>
        <?php
        $message_class = "error";
        if($_SESSION['ref-spam-block-proflash-status'] == 'success'){
            $message_class = "updated";
        };
        ?>

        <div id="message" class="<?php echo $message_class; ?>">
            <p><strong><?php echo $_SESSION['ref-spam-block-proflash']; ?></strong></p>
        </div>
    <?php }; ?>

    <?php if (get_option('ref-spam-block-mode', 'rewrite') == 'rewrite' && (!is_writable(get_home_path() . '.htaccess'))) : ?>
        <div id="message" class="error">
            <p>
                <strong><?php _e('Your .htaccess is not writable. The "Rewrite Block" option will most likely not work!', 'ref-spam-blocker') ?></strong>
            </p>
        </div>
    <?php endif; ?>

    <?php settings_errors(); ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- LEFT COLUMN -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">
                            <form method="post" action="options.php">
                                <table class="form-table">
                                    <?php settings_fields('ref-spam-block-settings'); ?>
                                    <?php do_settings_sections('ref-spam-block-settings'); ?>

                                    <tbody>

                                    <!-- SETTINGS -->
                                    <tr>
                                        <th>
                                            <label
                                                for="ref-spam-pro-key"><?php _e('Pro License Key', 'ref-spam-blocker'); ?></label>
                                        </th>
                                        <td>
                                            <input type="text" name="ref-spam-pro-key" id="ref-spam-pro-key" value="<?php echo esc_attr(get_option('ref-spam-pro-key')); ?>" size="64" />
                                            <input type="hidden" name="ref-spam-pro-active" value="<?php echo esc_attr(get_option('ref-spam-pro-active')); ?>" />

                                            <p class="description">
                                            Pro version will allow you to sync your custom blocks across all of your registered sites. Get your key at <a href="https://blockreferspam.com/pro" target="_blank">BlockReferSpam.com</a> <?php echo get_option('ref-spam-pro-active'); ?>.
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label><?php _e('Auto Update', 'ref-spam-blocker'); ?></label>
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="radio" name="ref-spam-auto-update"
                                                           value="yes"<?php echo(get_option('ref-spam-auto-update', 'yes') == 'yes' ? ' checked="checked"' : '') ?>>
                                                    <span><?php _e('Yes, once daily', 'ref-spam-blocker'); ?></span>
                                                </label>

                                                <br>

                                                <label>
                                                    <input type="radio" name="ref-spam-auto-update"
                                                           value="no"<?php echo(get_option('ref-spam-auto-update') == 'no' ? ' checked="checked"' : '') ?>>
                                                    <span><?php _e('No, only manual', 'ref-spam-blocker'); ?></span>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>
                                            <label><?php _e('Block Mode', 'ref-spam-blocker'); ?></label>
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <?php if (preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE'])) : ?>
                                                        <input type="radio" name="ref-spam-block-mode"
                                                               value="rewrite"<?php echo(get_option('ref-spam-block-mode', 'rewrite') == 'rewrite' ? ' checked="checked"' : '') ?>>
                                                        <span><?php _e('Rewrite Block', 'ref-spam-blocker'); ?></span>
                                                    <?php else : ?>
                                                        <input type="radio" name="ref-spam-block-mode" disabled>
                                                        <span style="color: gray">
                                                            <?php _e('Rewrite Block', 'ref-spam-blocker'); ?>
                                                            <em style="color: lightgray"><?php _e('(Server software not supported)', 'ref-spam-blocker'); ?></em>
                                                        </span>
                                                    <?php endif; ?>
                                                </label>

                                                <br>

                                                <label>
                                                    <input type="radio" name="ref-spam-block-mode"
                                                           value="wordpress"<?php echo(get_option('ref-spam-block-mode') == 'wordpress' ? ' checked="checked"' : '') ?>>
                                                    <span><?php _e('WordPress Block', 'ref-spam-blocker'); ?></span>
                                                </label>
                                            </fieldset>

                                            <p class="description">
                                                <?php _e('Rewrite Block is faster and occurs on the web-server level. If you run into problems (e.g. you cannot write your .htaccess file), use the WordPress Block instead.', 'ref-spam-blocker'); ?></p>
                                        </td>
                                    </tr>

                                    <!-- MANUAL UPDATE -->
                                    <tr>
                                        <th><label><?php _e('Manual Update', 'ref-spam-blocker'); ?></label></th>
                                        <td>
                                            <a href="admin.php?page=ref-spam-block&download=true"
                                               class="button button-secondary"><?php _e('Download Updates', 'ref-spam-blocker'); ?></a>

                                            <p class="description">
                                                <?php _e('Clicking this button will force an update of the referer spam list.', 'ref-spam-blocker'); ?>
                                            </p>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th><label><?php _e('Last Update', 'ref-spam-blocker'); ?></label></th>
                                        <td>
                                            <p>
                                                <?php if (get_option('ref-blocker-updated') !== false) : ?>
                                                    <?php echo date_i18n(get_option('date_format'), get_option('ref-blocker-updated')) . ' ' . date_i18n(get_option('time_format'), get_option('ref-blocker-updated')); ?>

                                                    <br>

                                                    <?php
                                                        $count = count(array_unique(array_filter(array_merge(
                                                            preg_split('/[\n\r]+/', get_option('ref-blocker-list')),
                                                            preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'))
                                                        ))));
                                                    ?>
                                                    <?php $list = array_filter(preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'))); ?>

                                                    <span class="ref-block-hint">
                                                        <?php echo sprintf(__('%s Sites Blocked', 'ref-spam-blocker'), $count); ?>
                                                        (<a href="<?php echo admin_url('admin.php?page=ref-spam-list'); ?>"><?php _e('See List', 'ref-spam-blocker'); ?></a>)
                                                    </span>
                                                <?php else : ?>
                                                    <?php _e('Never', 'ref-spam-blocker'); ?>
                                                <?php endif; ?>
                                            </p>
                                        </td>
                                    </tr>

                                    <!-- CUSTOM BLOCKS -->
                                    <tr>
                                        <th>
                                            <label
                                                for="custom-blocks"><?php _e('Custom Blocks', 'ref-spam-blocker'); ?></label>
                                            <span class="ref-block-hint"><?php _e('The plugin will automatically convert URLs into the right format.<br /><br />This list will wildcard all subdomains to the left of the entry.<br />If you want to block an entire TLD (.com, .org, .xyz), just include it in the list<br /><br />Example:<br />com<br />net', 'ref-spam-blocker'); ?></span>
                                        </th>
                                        <td>
                                                <textarea name="ref-spam-custom-blocks" id="custom-blocks" rows="15" cols="50" placeholder="some-dodgy-site.com"><?php echo esc_attr(get_option('ref-spam-custom-blocks')); ?></textarea>

                                            <p class="description">
                                                <?php esc_attr_e('If you find that the spammer list does not catch all sites you want to block, feel free to add more. Custom blocks may be reported back anonymously to our servers to improve the list.', 'ref-spam-blocker'); ?>
                                            </p>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>

                                <?php submit_button(); ?>
                            </form>

                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php include(dirname(__FILE__) . '/_sidebar.php'); ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>

<?php unset($_SESSION['ref-spam-block-flash']); ?>
