<?php

namespace WP_Defender\Module\Scan\Component;

use Hammer\Queue\Queue;
use Hammer\WP\Component;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Scan\Behavior\Core_Files;
use WP_Defender\Module\Scan\Behavior\Core_Scan;
use WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan;
use WP_Defender\Module\Scan\Behavior\Pro\Vuln_Scan;

class Queue_Factory extends Component {
	/**
	 * @param $slug
	 * @param array $args
	 *
	 * @return array|null
	 */
	public static function queueFactory( $slug, $args = array() ) {
		switch ( $slug ) {
			case 'gather_core_files':
				$queue                = new Queue( [ 'dummy' ], 'gather_core_files', true );
				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'core_files', new Core_Files() );

				return [ $queue, __( "Analyzing WordPress Core...", wp_defender()->domain ) ];
			case 'core':
				$queue                = new Queue(
					Scan_Api::getCoreFiles(),
					'core',
					true
				);
				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'core', new Core_Scan() );

				return [ $queue, __( "Analyzing WordPress Core...", wp_defender()->domain ) ];
			case 'vuln':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Vuln_Scan' ) ) {
					return null;
				}

				$queue = new Queue( array(
					'dummy'
				), 'vuln', true );

				$queue->args          = $args;
				$queue->args['owner'] = $queue;
				$queue->attachBehavior( 'vuln', new Vuln_Scan() );

				return [
					$queue,
					__( "Checking for any published vulnerabilities in your plugins & themes...", wp_defender()->domain )
				];
				break;
			case 'gather_content_files':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan' ) ) {
					return null;
				}
				$queue = new Queue( [ 'dummy' ], 'gather_content_files', true );

				return [ $queue, __( "Analyzing WordPress Content...", wp_defender()->domain ) ];
			case 'content':
				if ( ! class_exists( '\WP_Defender\Module\Scan\Behavior\Pro\Content_Yara_Scan' ) ) {
					return null;
				}
				//dont use composer autoload preventing bloating
				$queue                   = new Queue( Scan_Api::getContentFiles(), 'content', true );
				$queue->args             = $args;
				$queue->args['owner']    = $queue;
				$patterns                = Scan_Api::getPatterns();
				$queue->args['patterns'] = $patterns;
				$queue->attachBehavior( 'content', new Content_Yara_Scan() );

				return [ $queue, __( "Analyzing WordPress Content...", wp_defender()->domain ) ];
				break;
			default:
				//param not from the button on frontend, log it
				error_log( sprintf( 'Unexpected value %s from IP %s', $slug, Utils::instance()->getUserIp() ) );
				break;
		}
	}
}