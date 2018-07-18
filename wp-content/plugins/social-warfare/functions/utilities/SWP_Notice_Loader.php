<?php

/**
 * SWP_Notice_Loader
 *
 * This is where we define all the messages, CTAs, and scheudling for each notice.
 * It is fine to bloat the method with as many notices as necessary.
 *
 * @package   SocialWarfare\Functions\Social-Networks
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since  3.0.9  | 09 JUN 2018 | Created the class.
 * @since  3.1.0 | 27 JUN 2018 | Break each notice into it's own method.
 *
 */
class SWP_Notice_Loader {


	/**
	 * Instantiate the class.
	 *
	 * The constructor will call up the methods that create each of the various
	 * notices throughout the plugin.
	 *
	 * @since  3.0.9  | 09 JUN 2018 | Created.
	 * @since  3.1.0 | 27 JUN 2018 | Updated to use separate methods per notice.
	 * @see    SWP_Notice.php
	 * @param  void
	 * @return void
	 *
	 */
    public function __construct() {
		$this->activate_json_notices();
		$this->debug();
    }


	/**
	 * Activate notices created via our remote JSON file.
	 *
	 * @since  3.1.0 | 27 JUN 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function activate_json_notices() {
		$cache_data = get_option('swp_json_cache');

		if( false === $cache_data ):
			return;
		endif;

		if( !is_array( $cache_data ) || empty($cache_data['notices']) ):
			return;
		endif;

		foreach( $cache_data['notices'] as $notice ) :
            if ( empty( $notice['key'] ) || empty( $notice['message'] ) ) {
                continue;
            }

			$key     = $notice['key'];
			$message = $notice['message'];

            $n = new SWP_Notice( $key, $message );

            if ( !empty( $notice['ctas'] ) ) {

                foreach( $notice['ctas'] as $cta) {
                    $fields = [
                        'action' => '',
                        'link'   => '',
                        'class'  => '',
                        'timeframe' => 0
                    ];

                    $_cta = [];

                    foreach( $fields as $field => $default ) {
                        if ( isset( $cta[$field] ) ) {
                            $_cta[$field] = $cta[$field];
                        } else {
                            $_cta[$field] = $default;
                        }
                    }

                    $n->add_cta( $_cta['action'], $_cta['link'], $_cta['class'], $_cta['timeframe'] );
                }
            }

            if ( isset( $notice['start_date'] ) ) {
                $n->set_start_date( $notice['start_date'] );
            }

            if ( isset( $notice['end_date'] ) ) {
                $n->set_end_date( $notice['end_date'] );
            }

            if ( isset( $notice['no_cta'] ) ) {
                $n->remove_cta();
            }

			$this->notices[] = $n;

		endforeach;
	}


	/**
	 * A function for debugging this class.
	 *
	 * All notices are stored in the $this->notices as an array of notice
	 * objects. Since this is the last method called, all notices should be
	 * present in the $this object for review.
	 *
	 * @since  3.1.0 | 28 JUN 2018 | Created
	 * @param  void
	 * @return void
	 *
	 */
	private function debug() {
		if( true === _swp_is_debug( 'notices' ) ):
			var_dump($this);
		endif;
	}

}
