<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'BP_Better_Messages_Emojies' ) ):

    class BP_Better_Messages_Emojies
    {

        public $client;

        public static function instance()
        {

            static $instance = null;

            if ( null === $instance ) {
                $instance = new BP_Better_Messages_Emojies();
            }

            return $instance;
        }


        public function __construct()
        {
            add_filter( 'bp_better_messages_after_format_message', array( $this, 'convert_emojies' ), 100, 3 );

            require_once( BP_Better_Messages()->path . 'vendor/emojione/autoload.php' );
            $this->client = new \Emojione\Client(new \Emojione\Ruleset());
            $this->client->ascii = true;
			$this->client->greedyMatch = true;
            $this->client->imageType = 'png';
            $this->client->imagePathPNG = 'https://cdnjs.cloudflare.com/ajax/libs/emojione/2.2.7/assets/png/';
            //
        }

        public function convert_emojies( $message, $message_id, $context )
        {
            $message = str_replace(array('&lt;', '&gt;'), array('%%%opentag%%%', '%%%closetag%%%'), $message);
            $message =  $this->client->toImage( html_entity_decode($message) );
            $message = str_replace( array('%%%opentag%%%', '%%%closetag%%%'), array('&lt;', '&gt;'), $message);
            return $message;
        }

        public function convert_emojies_to_unicode( $message )
        {
            $message = str_replace(array('&lt;', '&gt;'), array('%%%opentag%%%', '%%%closetag%%%'), $message);
            $message =  $this->client->shortnameToUnicode( html_entity_decode($message) );
            $message = str_replace( array('%%%opentag%%%', '%%%closetag%%%'), array('&lt;', '&gt;'), $message);
            return $message;
        }
    }

endif;


function BP_Better_Messages_Emojies()
{
    return BP_Better_Messages_Emojies::instance();
}
