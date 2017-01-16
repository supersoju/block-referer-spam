<div class="wrap">
    <h2><?php _e('Block Referer Spam', 'ref-spam-blocker'); ?> Pro Options</h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">

            <!-- LEFT COLUMN -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="inside">

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
