<?php
defined( 'ABSPATH' ) || exit;
$stacks = BP_Better_Messages()->functions->get_starred_stacks();
?>
<div class="bp-messages-wrap bp-messages-wrap-main">

    <div class="chat-header">
        <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="back ajax"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        <a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>
    </div>

    <?php if ( !empty( $stacks ) ) { ?>
        <div class="scroller scrollbar-inner starred">
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