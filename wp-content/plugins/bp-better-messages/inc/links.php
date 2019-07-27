<?php

class BP_Messages_Links{

    public function __construct()
    {
        add_filter( 'bp_get_send_private_message_link', array($this, 'pm_link'), 20, 1 );
        add_filter( 'bp_get_message_thread_view_link',  array($this, 'thread_link'), 20, 1);
    }

    public function pm_link($link){
        return BP_Messages::get_link() . '?new-message&to='. bp_core_get_username( bp_displayed_user_id() );
    }

    public function thread_link($thread_id){
        return BP_Messages()->get_link() . 'bp-messages/?thread_id=' . $thread_id;
    }

}

new BP_Messages_Links();
