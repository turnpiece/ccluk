<?php // phpcs:ignore

/**
 * Handles GDPR transition stuff
 *
 * This includes policy copy suggestion, data export and data erase.
 *
 */
class Snapshot_Gdpr {

	private function __construct() {}

	public static function serve() {
		$me = new self();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action( 'admin_init', array( $this, 'add_privacy_policy' ) );

		add_filter(
			'wp_privacy_personal_data_exporters',
			array( $this, 'register_data_exporter' )
		);
		add_filter(
			'wp_privacy_personal_data_erasers',
			array( $this, 'register_data_eraser' ),
			5
		);
	}

	/**
	 * Augments exporters with plugins data exporter callback
	 *
	 * @param array $exporters Exporters this far.
	 *
	 * @return array
	 */
	public function register_data_exporter( $exporters ) {
		$exporters['snapshot'] = array(
			'exporter_friendly_name' => __( 'Snapshot metadata', 'snapshot' ),
			'callback' => array( $this, 'export_user_metadata' ),
		);
		return $exporters;
	}

	/**
	 * Augments erasers with plugins data eraser callback
	 *
	 * @param array $erasers Exporters this far.
	 *
	 * @return array
	 */
	public function register_data_eraser( $erasers ) {
		$erasers['snapshot'] = array(
			'eraser_friendly_name' => __( 'Snapshot metadata', 'snapshot' ),
			'callback' => array( $this, 'erase_user_metadata' ),
		);
		return $erasers;
	}

	/**
	 * Exports plugins metadata
	 *
	 * @param string $email User email.
	 *
	 * @return array
	 */
	public function export_user_metadata( $email ) {
		$result	= array(
			'data' => array(),
			'done' => true,
		);
		$destinations = $this->get_destinations_list( $email );
		if ( empty( $destinations ) ) {
			return $result;
		}

		$label = __( 'Snapshot metadata', 'snapshot' );
		$exports = array();
		foreach ( $destinations as $destination ) {
			$destination_id = $destination['destination_id'];

			$data = array();
			foreach ( $destination as $key => $value ) {
				if ( 'display_name' === $key ) {
					$key = __( 'Destination Account Owner', 'snapshot' );
				} elseif ( 'email' === $key ) {
					$key = __( 'Destination Account Email', 'snapshot' );
				} elseif ( 'uid' === $key ) {
					$key = __( 'Destination Account UID', 'snapshot' );
				} elseif ( 'country' === $key ) {
					$key = __( 'Destination Account Country', 'snapshot' );
				} elseif ( ( 'quota_info' === $key ) || ( 'destination_id' === $key ) ) {
					continue;
				}

				$data[] = array(
					'name' => $key,
					'value' => $value,
				);
			}

			$exports[] = array(
				'group_id' => 'destinations-snapshot_meta',
				'group_label' => $label,
				'item_id' => "destinations-destination-snapshot_meta-{$destination_id}",
				'data' => $data,
			);
		}
		$result['data'] = $exports;

		return $result;
	}

	/**
	 * Erases plugins metadata
	 *
	 * @param string $email User email.
	 *
	 * @return array
	 */
	public function erase_user_metadata( $email ) {
		$result = array(
			'items_removed' => 0,
			'items_retained' => false,
			'messages' => array(),
			'done' => true,
		);
		$destinations = $this->get_destinations_list( $email );
		if ( empty( $destinations ) ) {
			return $result;
		}

		foreach ( $destinations as $destination ) {
			$destination_id = $destination['destination_id'];

			$CONFIG_CHANGED = false;
			if ( array_key_exists( $destination_id, WPMUDEVSnapshot::instance()->config_data['destinations'] ) ) {

				unset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_id ] );
				$CONFIG_CHANGED = true;
			}

			if ( $CONFIG_CHANGED ) {
				WPMUDEVSnapshot::instance()->save_config();
				$result['items_removed'] = true;

			}

		}

		return $result;
	}

	/**
	 * Affected destinations getter method
	 *
	 * @param string $email User email to check.
	 *
	 * @return array List of destinations
	 */
	public function get_destinations_list( $email ) {

		$byemail = array();

		$snapshot_options = get_option( 'wpmudev_snapshot' );

		foreach ($snapshot_options as $key => $value) {
			if ( 'destinations' === $key ) {
				foreach ($value as $key2 => $value2) {
					if ( 'dropbox' === $value2['type'] ) {
						foreach ( $value2 as $key3 => $value3 ) {
							if ( 'account_info' === $key3 ) {
								foreach ( $value3 as $key4 => $value4 ) {
									if ( ( 'email' === $key4 ) && ( $value4 === $email ) ) {
										$value3['destination_id'] = $key2;
										array_push( $byemail, $value3);
									}

								}
							}
						}
					}
				}
			}
		}

		return $byemail;
	}

	/**
	 * Hooks into privacy policy content, if possible
	 */
	public function add_privacy_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return false;
		}
		wp_add_privacy_policy_content(
			__( 'Snapshot', 'snapshot' ),
			$this->get_policy_content()
		);
	}

	/**
	 * Gets policy content as string
	 *
	 * @return string Policy content HTML
	 */
	public function get_policy_content() {
		return '' .
			'<h3>' . __( 'Third parties', 'snapshot' ) . '</h3>' .
			'<p>' . __( 'This site may be using a third-party to store backups of its files and database where personal information is collected. These services include Google Drive, Dropbox, Amazon S3, FTP/SFTP for other servers and WPMU DEV cloud storage.', 'snapshot' ) . '</p>' .
			'<h3>' . __( 'Additional data', 'snapshot' ) . '</h3>' .
			'<p>' . __( "This site gives the option to its administrators to set up third-party destinations for sending and receiving backups. To create these destinations, personal data is stored. This data includes the administrator's name, email, UID and country for Dropbox accounts and credentials for FTP accounts.", 'snapshot' ) . '</p>' .
			'<h3>' . __( 'Cookies', 'snapshot' ) . '</h3>' .
			'<p>' . __( 'This site might be using cookies on the admin side for establishing connections with third-party vendors for sending and receiving backups. These vendors include Google Drive, Dropbox, Amazon S3 and phpseclib for FTP accounts. Additionally, cookies may be set for potentially fixing cron requests at erratic servers. These cookies will last for 14 days.', 'snapshot' ) . '</p>' .
		'';
	}
}