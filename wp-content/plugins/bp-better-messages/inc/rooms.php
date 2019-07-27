<?php
defined( 'ABSPATH' ) || exit;

class BP_Better_Messages_Rooms
{

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Rooms;
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_actions(){
        add_shortcode('bpbm-room', array( $this, 'layout') );
    }

    public function layout(){
        $path = BP_Better_Messages()->path . '/views/';
        ob_start();
        include($path . 'layout-room.php');
        return ob_get_clean();
    }

}

function BP_Better_Messages_Rooms()
{
    return BP_Better_Messages_Rooms::instance();
}