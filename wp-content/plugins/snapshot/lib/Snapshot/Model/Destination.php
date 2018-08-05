<?php // phpcs:ignore
/**
 * Class for dealing with destinations.
 *
 * @since 2.5
 *
 * @package Snapshot
 * @subpackage Model
 */

if ( class_exists( 'Snapshot_Model_Destination' ) ) {
	return;
}

abstract class Snapshot_Model_Destination {

	public $name_slug;
	public $name_display;

	private static $destinations;

	/**
	 * @var array
	 */
	public $form_errors = array();

	public function __construct() {

		if ( method_exists( $this, 'on_creation' ) ) {
			$this->on_creation();
		}

		if ( empty( $this->name_display ) || empty ( $this->name_slug ) ) {
			wp_die( esc_html__( 'You must override all required vars in your Snapshot Destination class!', SNAPSHOT_I18N_DOMAIN ) );
		}

	}

	public function display_listing_table( $destinations, $edit_url, $delete_url ) {
		wp_die( esc_html__( "You must override the function 'display_listing_table' in your Snapshot Destination class!", SNAPSHOT_I18N_DOMAIN ) );
	}

	public function sendfile_to_remote( $destination_info, $filename ) {
		wp_die( esc_html__( "You must override the function 'sendfile_to_remote' in your Snapshot Destination class!", SNAPSHOT_I18N_DOMAIN ) );
	}

	public function display_details_form( $item = 0 ) {
		wp_die( esc_html__( "You must override the function 'display_details_form' in your Snapshot Destination class!", SNAPSHOT_I18N_DOMAIN ) );
	}

	public static function load_destinations() {

		$dir = WPMUDEVSnapshot::instance()->get_plugin_path() . 'lib/Snapshot/Model/Destination';

		if ( ! defined( 'WPMUDEV_SNAPSHOT_DESTINATIONS_EXCLUDE' ) ) {
			define( 'WPMUDEV_SNAPSHOT_DESTINATIONS_EXCLUDE', '' );
		}

		//search the dir for files
		$snapshot_destination_files = array();
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$dh = opendir( $dir );
		if ( ! $dh ) {
			return;
		}

		$plugin = readdir( $dh );
		while ( false !== $plugin ) {
			if ( '.' === $plugin[0] ) {
				$plugin = readdir( $dh );
				continue;
			}
			if ( '_' === $plugin[0] ) {
				$plugin = readdir( $dh );
				continue;
			}    // Ignore this starting with underscore

			$_destination_dir = $dir . '/' . $plugin;
			if ( is_dir( $_destination_dir ) ) {
				$_destination_dir_file = $_destination_dir . "/index.php";
				if ( is_file( $_destination_dir_file ) ) {
					$snapshot_destination_files[] = $_destination_dir_file;
				}
			}
			$plugin = readdir( $dh );
		}
		closedir( $dh );

		//echo "snapshot_destination_files<pre>"; print_r($snapshot_destination_files); echo "</pre>";
		if ( ( $snapshot_destination_files ) && ( count( $snapshot_destination_files ) ) ) {
			sort( $snapshot_destination_files );

			foreach ( $snapshot_destination_files as $file ) {
				//echo "file=[". $file ."]<br />";
				if ( strpos( $file, 'dropbox' ) !== false ) {
					if ( version_compare( phpversion(), '5.5.0', '>=' ) ) {
						include  $file ;
					}
				} else {
					include  $file ;
				}
			}
		}
		do_action( 'snapshot_destinations_loaded' );
	}

	public static function get_object_from_type( $type ) {
		$destinationClasses = WPMUDEVSnapshot::instance()->get_setting( 'destinationClasses' );
		if ( isset( $destinationClasses[ $type ] ) ) {
			return $destinationClasses[ $type ];
		}
	}


	public static function get_destinations() {
		if ( empty ( self::$destinations ) ) {
			self::load_destinations();
		}

		return self::$destinations;
	}

	public static function show_destination_item_count( $destination_key ) {
		if ( isset( WPMUDEVSnapshot::instance()->config_data['items'] ) ) {
			$destination_count = 0;
			foreach ( WPMUDEVSnapshot::instance()->config_data['items'] as $snapshot_item ) {
				if ( ( isset( $snapshot_item['destination'] ) ) && ( $snapshot_item['destination'] === $destination_key ) ) {
					$destination_count++;
				}
			}
			if ( $destination_count ) {
				?><a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' ) ); ?>snapshot_pro_snapshots&amp;destination=<?php echo esc_attr( $destination_key ); ?>"><?php echo esc_html( $destination_count ); ?></a>
                <?php
			} else {
				echo '<span>0</span>';
			}
		} else {
			echo "0";
		}
	}

	public static function get_destination_item_count( $destination_key ) {
		if ( isset( WPMUDEVSnapshot::instance()->config_data['items'] ) ) {
			$destination_count = 0;
			foreach ( WPMUDEVSnapshot::instance()->config_data['items'] as $snapshot_item ) {
				if ( ( isset( $snapshot_item['destination'] ) ) && ( $snapshot_item['destination'] === $destination_key ) ) {
					$destination_count++;
				}
			}
			return $destination_count;
		} else {
			return 0;
		}
	}

	public static function get_destination_nice_name( $destination_type ) {

		$nice_names = array(
			'dropbox'      => __( 'Dropbox', SNAPSHOT_I18N_DOMAIN ),
			'aws'          => __( 'Amazon AWS', SNAPSHOT_I18N_DOMAIN ),
			'google-drive' => __( 'Google Drive', SNAPSHOT_I18N_DOMAIN ),
			'ftp'          => __( 'FTP/SFTP', SNAPSHOT_I18N_DOMAIN ),
		);

		if ( isset( $nice_names[ $destination_type ] ) ) {
			return $nice_names[ $destination_type ];
		}

		return $destination_type;
	}

	/**
	 * Ensure that an array of destination info has the required fields to be complete and usable
	 *
	 * @param array $destination_info
	 * @param array $required_fields
	 *
	 * @return bool
	 */
	public static function has_required_fields( $destination_info, $required_fields ) {

		foreach ( $required_fields as $key => $field ) {

			if ( is_array( $field ) ) {
				if ( ! self::has_required_fields( $destination_info[ $key ], $field ) ) {
					return false;
				}

			} elseif ( empty( $destination_info[ $field ] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate a list of basic text fields
	 *
	 * @param array $text_fields
	 * @param array $input
	 * @param array $output
	 * @param array $required_fields
	 *
	 * @return array
	 */
	protected function validate_text_fields( $text_fields, $input, $output = array(), $required_fields = array() ) {

		foreach ( $text_fields as $field ) {
			$output[ $field ] = empty( $input[ $field ] ) ? '' : sanitize_text_field( stripslashes( $input[ $field ] ) );

			if ( isset( $required_fields[ $field ] ) && ! $output[ $field ] ) {
				$this->form_errors[ $field ] = $required_fields[ $field ];
			}
		}

		return $output;
	}

	/**
	 * @param array $number_fields
	 * @param array $input
	 * @param array $output
	 * @param array $required_fields
	 *
	 * @return array
	 */
	protected function validate_number_fields( $number_fields, $input, $output = array(), $required_fields = array() ) {

		foreach ( $number_fields as $field ) {
			$output[ $field ] = empty( $input[ $field ] ) ? 0 : intval( $input[ $field ] );

			if ( isset( $required_fields[ $field ] ) && ! $output[ $field ] ) {
				$this->form_errors[ $field ] = $required_fields[ $field ];
			}
		}

		return $output;
	}

	abstract public function validate_form_data( $d_info );

	/**
	 * Uniform exception handling
	 *
	 * Logs error and sets up error status
	 *
	 * @since 3.1.6-beta.1
	 *
	 * @param Exception $e Exception to log
	 * @param string $action Action that we were trying to do
	 *
	 * @return false
	 */
	public function handle_exception ($e, $action) {
		$this->error_array['errorStatus'] = true;
		if ( isset( $this->snapshot_logger ) && isset($e) ) {
			$this->snapshot_logger->log_message(
				sprintf(
					__("Error: Could not perform %1\$s [%2\$s]: Error: %3\$s", SNAPSHOT_I18N_DOMAIN),
					$action, $this->name_display, $e
				)
			);
		}
		return false;
	}

	/**
	 * Sets up destination info and prepares connection
	 *
	 * @since v3.1.6-beta.1
	 *
	 * @param array $destination_info Destination info to set up
	 *
	 * @return bool
	 */
	public function set_up_destination ($destination_info) {
		$this->init();
		$this->load_class_destination( $destination_info );

		return $this->login();
	}

	/**
	 * Purges remote items to spec
	 *
	 * @since 3.1.6-beta.1
	 *
	 * @param string $root Filename prefix to match.
	 * @param int $keep_count How many remote files to preserve.
	 *
	 * @return int Number of removed files
	 */
	public function purge_remote_items ($root, $keep_count) {
		if (!is_callable(array($this, 'remove_file'))) {
			// We're not able to remove remote files.
			return 0;
		}
		if (!is_callable(array($this, 'get_prepared_items'))) {
			// We're not able to parse remote items into something we know about.
			return 0;
		}
		$items = $this->list_remote_items($root);

		$initial_count = count($items);
		$to_remove = $initial_count - $keep_count;

		if ($to_remove <= 0) return 0; // Nothing to do here

		$prepared = $this->get_prepared_items($items);
		ksort($prepared);

		$removed = 0;
		foreach ($prepared as $item) {
			try {
				if ($this->remove_file($item['id'])) {
					$removed++;
				}
			} catch (Exception $e) {
				// Something went wrong
				$this->handle_exception($e, "remove {$item['title']}");
			}
			if ($removed >= $to_remove) break;
		}

		return $removed;
	}

}