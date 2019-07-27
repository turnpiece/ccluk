<?php
defined( 'ABSPATH' ) || exit;

class BP_Better_Messages_Mini_List
{

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Mini_List;
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_actions()
    {
        add_action('wp_footer', array( $this, 'html' ), 199);
    }


    public function html(){
        if( ! is_user_logged_in() ) return false;
        $user_id = get_current_user_id();
        $threads = BP_Better_Messages()->functions->get_threads( $user_id );

        $tabs = array();
        if(BP_Better_Messages()->settings['miniThreadsEnable'] === '1') $tabs[] = 'messages';
        if(BP_Better_Messages()->settings['miniFriendsEnable'] === '1'  && function_exists('friends_get_friend_user_ids')) {
            $friends = friends_get_friend_user_ids(get_current_user_id());
            if( count($friends) > 0 ) $tabs[] = 'friends';
        }

        if( count($tabs) == 0 ) return false;
        ?>
        <div class="bp-messages-wrap bp-better-messages-list">
            <div class="tabs">
                <?php if(in_array('messages', $tabs)){ ?>
                    <div data-tab="messages"><span class="unread-count" style="display:none"></span><i class="fas fa-comments"></i> <?php _e('Messages', 'bp-better-messages'); ?></div>
                <?php } ?>
                <?php if(in_array('friends', $tabs)){ ?>
                    <div data-tab="friends"><i class="fas fa-users"></i> <?php _e('Friends', 'bp-better-messages'); ?></div>
                <?php } ?>
            </div>
            <div class="tabs-content">
            <?php if(in_array('messages', $tabs)){ ?>
                <div class="messages">
                    <?php if ( !empty( $threads ) ) { ?>
                        <div class="scroller scrollbar-inner">
                            <div class="threads-list">
                                <?php foreach ( $threads as $thread ) {
                                    echo BP_Better_Messages()->functions->render_thread( $thread );
                                } ?>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="threads-list">
                            <p class="empty">
                                <?php _e( 'Nothing found', 'bp-better-messages' ); ?>
                            </p>
                        </div>
                    <?php } ?>
                    <div class="chat-header">
                        <a href="<?php echo add_query_arg( 'new-message', '', BP_Better_Messages()->functions->get_link() ); ?>" class="new-message ajax" title="<?php _e( 'New Thread', 'bp-better-messages' ); ?>"><i class="fas fa-plus" aria-hidden="true"></i></a>
                    </div>
                </div>
            <?php } ?>
            <?php if(in_array('friends', $tabs)){ ?>
                <div class="friends">
                    <?php
                    $friends = friends_get_friend_user_ids(get_current_user_id());
                    ?>
                    <div class="scroller scrollbar-inner">
                        <div class="bp-messages-user-list">
                              <?php foreach($friends as $user_id){
                                  $user = get_userdata($user_id);
                                  if( ! $user ) continue;
                              ?>
                              <div class="user" data-id="<?php esc_attr_e($user_id); ?>" data-username="<?php esc_attr_e($user->user_login); ?>">
                                  <div class="pic">
                                      <?php echo BP_Better_Messages_Functions()->get_avatar( $user_id, 30 ); ?>
                                  </div>
                                  <div class="name"><?php echo BP_Better_Messages_Functions()->get_name( $user_id ); ?></div>
                                  <div class="actions">
                                      <a href="<?php echo bp_core_get_userlink( $user_id, false, true ); ?>" class="open-profile"><i class="fas fa-user"></i></a>
                                  </div>
                                  <div class="loading">
                                      <div class="bounce1"></div>
                                      <div class="bounce2"></div>
                                      <div class="bounce3"></div>
                                  </div>
                              </div>
                              <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            </div>
        </div>
        <?php
    }

}

function BP_Better_Messages_Mini_List()
{
    return BP_Better_Messages_Mini_List::instance();
}