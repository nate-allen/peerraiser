<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>
<div class="wrap">
    <div id="peerraiser-js-message" class="pr_flash-message" style="display:none;">
        <p></p>
    </div>

    <h1 class="wp-heading-inline"><?php _e( 'Donations', 'peerraiser' ); ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=peerraiser-donations&view=add' ); ?>" class="page-title-action"><?php _e( 'Add Offline Donation', 'peerraiser' ); ?></a>
    <hr class="wp-header-end">

    <?php $peerraiser['list_table']->views(); ?>
    <form method="post">
        <?php
        $peerraiser['list_table']->prepare_items();
        $peerraiser['list_table']->display();
        ?>
    </form>
</div>