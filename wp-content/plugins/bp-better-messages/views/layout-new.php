<div class="bp-messages-wrap bp-messages-wrap-main">
    <div class="chat-header">
        <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="new-message ajax" title="<?php _e( 'New Thread', 'bp-better-messages' ); ?>"><i class="fas fa-times" aria-hidden="true"></i></a>

        <?php if(current_user_can('manage_options')){ ?>
        <a href="<?php echo add_query_arg( 'bulk-message', '', BP_Better_Messages()->functions->get_link() ); ?>" class="mass-message ajax" title="<?php _e( 'Mass Message', 'bp-better-messages' ); ?>"><i class="fas fa-envelope" aria-hidden="true"></i></a>
        <?php } ?>
        <a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>
    </div>
    <div class="new-message">
        <form>
            <div>
                <label><?php _e( "Send To (Username or Friend's Name)", 'bp-better-messages' ); ?></label>
                <div id="send-to" class="input"></div>
                <span class="clearfix"></span>
            </div>
            <?php if(BP_Better_Messages()->settings['disableSubject'] !== '1') { ?>
            <div>
                <label for="subject-input"><?php _e( 'Subject', 'bp-better-messages' ); ?></label>
                <input type="text" tabindex="3" name="subject" class="subject-input" id="subject-input" autocomplete="off">
                <span class="clearfix"></span>
            </div>
            <?php } ?>
            <div>
                <label for="message-input"><?php _e( 'Message', 'bp-better-messages' ); ?></label>

                <textarea name="message" placeholder="<?php esc_attr_e( "Write your message", 'bp-better-messages' ); ?>" id="message-input" autocomplete="off"></textarea>
                <span class="clearfix"></span>
            </div>

            <button type="submit"><?php _e( 'Send Message', 'bp-better-messages' ); ?></button>

            <?php if ( isset( $_GET[ 'to' ] ) && !empty( $_GET[ 'to' ] ) ) {
                $recepients = explode(',', $_GET['to']);
                foreach ($recepients as $recepient){
	                echo '<input type="hidden" name="to" value="' . $recepient . '">';
                }
            } ?>

            <input type="hidden" name="action" value="bp_messages_new_thread">
            <?php wp_nonce_field( 'newThread' ); ?>
        </form>

    </div>

    <script type="text/javascript">
        setTimeout(tabIndexFix, 100);
        function tabIndexFix(){
            var result =  jQuery('.emojionearea-editor').attr('tabindex', '4');
            if(result.length === 0) setTimeout(tabIndexFix, 100);
        }
    </script>

    <div class="preloader"></div>
</div>