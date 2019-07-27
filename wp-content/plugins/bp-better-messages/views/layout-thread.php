<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$message_id = false;
if(isset($_GET['message_id'])) $message_id = intval($_GET['message_id']);
$participants = BP_Better_Messages()->functions->get_participants( $thread_id );
if($message_id){
	$stacks = BP_Better_Messages()->functions->get_stacks( $thread_id, $message_id, 'to_message');
} else {
	$stacks = BP_Better_Messages()->functions->get_stacks( $thread_id );
}

$is_mini = (isset($_GET['mini'])) ? true : false;
?>
<div class="bp-messages-wrap bp-messages-wrap-main">
    <div class="chat-header">
        <?php do_action('bp_better_messages_thread_pre_header'); ?>
        <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="back ajax"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
        <?php if(count($participants['links']) < 2) {
            if($is_mini){
                echo apply_filters('bp_better_messages_mini_chat_username', strip_tags($participants[ 'links' ][0]), $participants[ 'recipients' ][0], $thread_id);
            } else {
                echo $participants[ 'links' ][0];
            }
        } else {
            $subject = BP_Better_Messages()->functions->get_thread_subject($thread_id);
            echo '<strong title="' . $subject . '">' . mb_strimwidth($subject,0, 20, '...') . '</strong>';
            echo ' <a href="#" onclick="event.preventDefault();jQuery(\'.participants-panel\').toggleClass(\'open\')">('. count($participants['links']) . ' ' . __('participants', 'bp-better-messages') . ')</a>';
        }?>

        <a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>
    </div>
    <div class="chat-controls">
        <a href="#" class="bpbm-edit" data-nonce="<?php echo wp_create_nonce('edit_message_' . $thread_id); ?>"><i class="fas fa-pencil-alt"></i> <?php _e('Edit', 'bp-better-messages'); ?></a>
        <a href="#" class="bpbm-fave"><i class="fas fa-star"></i> Star</a>
        <a href="#" class="bpbm-delete"><i class="fas fa-trash"></i> Delete</a>
    </div>
    <?php if(count($participants['links']) > 1 && ! isset($_GET['mini'])) {
        $can_moderate = BP_Better_Messages()->functions->is_thread_moderator(get_current_user_id(), $thread_id);
        ?>
    <div class="participants-panel <?php echo (isset($_GET['participants'])) ? 'open' : ''; ?>">
        <div class="scroller scrollbar-inner">
        <div class="bp-messages-user-list">
            <?php foreach($participants['users'] as $user_id => $_user){
                $user = get_userdata($user_id);
                ?>
                <div class="user" data-id="<?php esc_attr_e($user_id); ?>" data-thread-id="<?php esc_attr_e($thread_id); ?>" data-username="<?php esc_attr_e($user->user_login); ?>">
                    <div class="pic">
                        <?php echo BP_Better_Messages_Functions()->get_avatar( $user_id, 30 ); ?>
                    </div>
                    <div class="name"><a target="_blank"  href="<?php echo bp_core_get_userlink( $user_id, false, true ); ?>"><?php echo BP_Better_Messages_Functions()->get_name( $user_id ); ?></a></div>
                    <div class="actions">
                        <?php if($user_id !== get_current_user_id() && $can_moderate){ ?>
                        <a href="#" class="remove-from-thread" title="<?php _e('Exclude user from thread', 'bp-better-messages'); ?>"><i class="fas fa-ban"></i></a>
                        <?php } ?>
                    </div>
                    <div class="loading"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php if($can_moderate){ ?>
        <div class="add-user" data-thread-id="<?php esc_attr_e($thread_id); ?>">
            <p><?php _e('Add new participants', 'bp-better-messages'); ?></p>
            <div id="send-to" class="input"></div>
            <button type="submit"><?php _e('Add participants', 'bp-better-messages'); ?></button>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="scroller scrollbar-inner thread"
         data-users="<?php echo implode( ',', array_keys( $participants[ 'users' ] ) ); ?>"
         data-users-json="<?php esc_attr_e(json_encode( $participants[ 'users' ] )); ?>"
         data-id="<?php echo $thread_id; ?>">
        <div class="loading-messages">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
        <div class="list">
            <?php
            if(count($stacks) == 1 && $stacks[0]['user_id'] == 0){ ?>
            <div class="empty-thread">
                <i class="fas fa-comments"></i>
                <span><?php esc_attr_e(' Write the message to start conversation', 'bp-better-messages'); ?></span>
            </div>
            <?php } else {
                foreach ( $stacks as $stack ) {
                    echo BP_Better_Messages()->functions->render_stack( $stack );
                }
            } ?>
        </div>
    </div>

    <span class="writing" style="display: none"></span>

    <?php if( apply_filters('bp_better_messages_can_send_message', true, get_current_user_id(), $thread_id ) ) { ?>
    <div class="reply">
        <form action="" method="POST">
            <div class="message">
                <textarea placeholder="<?php esc_attr_e( "Write your message", 'bp-better-messages' ); ?>" name="message" autocomplete="off"></textarea>
            </div>
            <div class="send">
                <button type="submit"><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
            </div>
            <input type="hidden" name="action" value="bp_messages_send_message">
            <input type="hidden" name="message_id" value="">
            <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
            <?php wp_nonce_field( 'sendMessage_' . $thread_id ); ?>
        </form>

        <span class="clearfix"></span>

        <?php do_action( 'bp_messages_after_reply_form', $thread_id ); ?>
    </div>
    <?php } ?>

    <div class="preloader"></div>
</div>