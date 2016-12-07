<div class="wrap">
    <h2><?php _e('Block Referer Spam', 'ref-spam-blocker'); ?></h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- LEFT COLUMN -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">
                            <p><?php _e('Below you will find all internal and custom blocks, in two lists respectively. You can
                                open them safely, as your sites referer will be hidden through a free tunnel-website
                                (<a href="https://href.li/" target="_blank">href.li</a>).', 'ref-spam-blocker'); ?></p>

                            <h3><?php _e('Internal Blocks', 'ref-spam-blocker'); ?></h3>

                            <?php $list = array_filter(preg_split('/[\n\r]+/', get_option('ref-blocker-list'))); ?>

                            <?php if (count($list) > 0) : ?>
                                <ul class="ref-block-list">
                                    <?php foreach ($list as $host) : ?>
                                        <li>
                                            <?php echo $host; ?>
                                            (<a href="https://href.li/?http://<?php echo $host; ?>" target="_blank"><?php _e('Open', 'ref-spam-blocker'); ?></a>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                            <?php else : ?>
                                <div class="inside">
                                    <p><?php _e('None', 'ref-spam-blocker'); ?></p>
                                </div>
                            <?php endif; ?>

                            <h3><?php _e('Custom Blocks', 'ref-spam-blocker'); ?></h3>

                            <?php $list = array_filter(preg_split('/[\n\r]+/', get_option('ref-spam-custom-blocks'))); ?>

                            <?php if (count($list) > 0) : ?>
                                <ul class="ref-block-list">
                                    <?php foreach ($list as $host) : ?>
                                        <li>
                                            <?php echo $host; ?>
                                            (<a href="https://href.li/?http://<?php echo $host; ?>" target="_blank"><?php _e('Open', 'ref-spam-blocker'); ?></a>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                            <?php else : ?>
                                <div class="inside">
                                    <p><?php _e('None', 'ref-spam-blocker'); ?></p>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <?php include(dirname(__FILE__). '/_sidebar.php'); ?>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>

<?php unset($_SESSION['ref-spam-block-flash']); ?>