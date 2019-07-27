<?php
defined( 'ABSPATH' ) || exit;
$search = sanitize_text_field( $_GET['search'] );
$stacks = BP_Better_Messages()->functions->get_search_stacks($search);
?>
<div class="bp-messages-wrap bp-messages-wrap-main">

    <div class="chat-header">
        <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="back ajax"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>

        <div class="bpbm-search">
            <form>
                <input title="<?php _e( 'Search', 'bp-better-messages' ); ?>" type="text" name="search" value="<?php esc_attr_e($search); ?>">
                <span class="close"><i class="fas fa-times" aria-hidden="true"></i></span>
            </form>
            <a style="display: none" href="#" class="search" title="<?php _e( 'Search', 'bp-better-messages' ); ?>"><i class="fas fa-search" aria-hidden="true"></i></a>
        </div>

        <a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>
    </div>

    <?php if ( !empty( $stacks ) ) { ?>
        <div class="scroller scrollbar-inner search">
            <div class="list">
                <?php foreach ( $stacks as $stack ) {
	                echo BP_Better_Messages()->functions->render_stack( $stack );
                } ?>
            </div>
        </div>
    <?php } else { ?>
        <p class="empty">
            <?php _e( 'Nothing found', 'bp-better-messages' ); ?>
        </p>
    <?php } ?>

    <script type="text/javascript">
        jQuery('.bp-better-messages-unread').text(<?php echo BP_Messages_Thread::get_total_threads_for_user( get_current_user_id(), 'inbox', 'unread' ); ?>);
    </script>

    <div class="preloader"></div>
</div>