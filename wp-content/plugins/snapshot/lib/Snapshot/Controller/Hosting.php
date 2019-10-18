<?php // phpcs:ignore

/**
 * Hosting backups controller abstraction
 */
class Snapshot_Controller_Hosting {

	const CODE_ERROR_BULK_DELETE = 'bdel';
	const REQUEST_GET = 'GET';
	const REQUEST_POST = 'POST';
	const OPTIONS_BACKUP_FLAG = 'snapshot_current_hosting_backup_run';
	const HUB_BACKUP_URL = 'https://premium.wpmudev.org/hub/hosting/?view=site&site_id=%s&tab=backups';

	/**
	 * Singleton instance
	 *
	 * @var object Snapshot_Controller_Hosting
	 */
	private static $_instance;

	/**
	 * Hosting Model reference
	 *
	 * @var object Snapshot_Model_Hosting
	 */
	protected $_hosting_model;

	/**
	 * Full Backup Model
	 *
	 * @var object Snapshot_Model_Full_Backup;
	 */
	protected $_managed_model;

	/**
	 * Constructs an instance, never to the outside world.
	 *
	 * Also sets up a model reference to be used by it and
	 * implementing classes.
	 */
	protected function __construct() {
		$this->_hosting_model = new Snapshot_Model_Hosting();
		$this->_managed_model = new Snapshot_Model_Full_Backup();
	}

	/**
	 * No public cloning kthxbai
	 */
	protected function __clone() {}

	/**
	 * Singleton instance getter
	 *
	 * @return object Snapshot_Controller_Hosting
	 */
	public static function get() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Gets the backup type of this controller
	 *
	 * For future use
	 *
	 * @return string Backup type
	 */
	protected function _get_backup_type() {
		return 'hosting';
	}

	/**
	 * Gets prefixed filter/action name
	 *
	 * @param string $action Action/filter name to prefix
	 *
	 * @return string
	 */
	public function get_filter( $action ) {
		return 'snapshot-controller-' . $this->_get_backup_type() . '-' . $action;
	}

	/**
	 * Dispatch admin view and AJAX controllers.
	 */
	public function run() {

		if ( is_admin() && current_user_can( 'manage_snapshots_items' ) ) {
			Snapshot_Controller_Hosting_Ajax::get()->run();
		}

		add_action( 'current_screen', array( $this, 'process_actions' ) );
	}

	/**
	 * Dispatch actions processing.
	 */
	public function process_actions() {
		// phpcs:ignore
		if ( isset( $_GET['action'] ) && isset( $_GET['page'] ) && 'snapshot_pro_hosting_backups' === $_GET['page'] && 'delete' === $_GET['action'] ) {
			$_POST = $_GET; // phpcs:ignore
		}
		$data = new Snapshot_Model_Post();

		if ( $data->has( 'snapshot-full_backups-list-nonce' ) && $data->has( 'delete-bulk' ) && $data->has( 'action' ) && 'delete' === $data->value( 'action' ) ) {
			$this->_bulk_delete( $data );
		}
	}

	/**
	 * Deletes the snapshots in bulk
	 *
	 * @param Snapshot_Model_Post $data Request data
	 *
	 * @return bool False on failure
	 */
	private function _bulk_delete( Snapshot_Model_Post $data ) {
		if (
			! wp_verify_nonce( $data->value( 'snapshot-full_backups-list-nonce' ), 'snapshot-full_backups-list' )
		) {
			return false;
		}

		$to_remove = $data->value( 'delete-bulk' );
		if ( empty( $to_remove ) || ! is_array( $to_remove ) ) {
			return false;
		} // Not valid data

		$status = true; // Assume all is good
		foreach ( $to_remove as $timestamp ) {
			$timestamp = (int) $timestamp;
			if ( ! $timestamp ) {
				continue;
			} // Not a valid timestamp

			$status = $this->_managed_model->delete_backup( $timestamp );
			if ( ! $status ) {
				break;
			}
		}

		$url = WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-hosting-backups' );
		$url = ! empty( $status )
			? remove_query_arg( 'error', $url )
			: add_query_arg( 'error', self::CODE_ERROR_BULK_DELETE, $url );
		wp_safe_redirect( $url );
		die;
	}

	/**
	 * Checks if user is logged in the WPMUDEV Dashboard
	 */
	public function logged_in_wpmudev_user() {
		return $this->_hosting_model->active_dashboard_key();
	}

	/**
	 * Retrieve site's id.
	 */
	public function get_site_id() {
		return $this->_hosting_model->get_site_id();
	}

	/**
	 * Actually start the backup.
	 *
	 * @return bool
	 */
	protected function _start_backup() {
		$status = $this->get_api_result( 'backups', self::REQUEST_POST );

		return $status;
	}

	/**
	 * Actually process the backup
	 *
	 * @throws Snapshot_Exception On error limit reached.
	 *
	 * @param string $idx Backup index to process
	 *
	 * @return bool Is backup done?
	 */
	protected function _process_backup( $idx ) {
		$status = false;

		return $status;
	}

	/**
	 * Wrapping up and clearing backup.
	 *
	 * @throws Snapshot_Exception On error limit reached.
	 *
	 * @param string $idx Backup index to process
	 *
	 * @return bool
	 */
	protected function _finish_backup( $idx ) {
		return false;
	}

	/**
	 * Backup restoration dispatch method
	 *
	 * @param string $backup_id Backup ID.
	 *
	 * @return bool True for done, false for in progress
	 */
	protected function _restore_backup( $backup_id ) {
		$status = $this->get_api_result(
			'backups/test/restore',
			self::REQUEST_POST,
			array( 'backup_id' => $backup_id )
		);

		return $status;
	}

	/**
	 * Backup export dispatch method
	 *
	 * @param string $backup_id Backup ID.
	 *
	 */
	protected function _export_backup( $backup_id ) {
		$status = $this->get_api_result(
			'backups/test/export',
			self::REQUEST_POST,
			array( 'backup_id' => $backup_id )
		);
		return $status;
	}

	/**
	 * Gets data about currently running backup/restore
	 *
	 * @param string $action_id Action ID.
	 *
	 * @return array
	 */
	protected function get_currently_running_action( $action_id ) {
		if ( ! $action_id || empty( $action_id ) ) return array();

		$status = $this->get_action_status( $action_id );

		$is_errored = is_wp_error( $status ) || ( isset( $status['status'] ) && 'errored' === $status['status']);
		$status['is_done'] = is_array( $status ) && ! empty( $status['completed_at'] );
		$status['error'] = $is_errored;

		return $status;
	}

	/**
	 * Gets a list of backups
	 *
	 * @return array A list of full backup items
	 */
	protected function get_backups_list() {
		$result = $this->get_api_result( 'backups', self::REQUEST_GET );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$this->_backups_cache = is_array( $result )
			? $result
			: array();

		return $this->_backups_cache;
	}

	/**
	 * Fetches result from API
	 *
	 * @uses WPMUDEV_HOSTING_SITE_ID define
	 *
	 * @param string $action API endpoint.
	 * @param string $type Optional request type - defaults to GET.
	 * @param array $params Optional additional params.
	 *
	 * @return WP_Error|array Error if something went wrong, parsed JSON array otherwise.
	 */
	protected function get_api_result( $action, $type = false, $params = array() ) {
		$api_key = $this->logged_in_wpmudev_user();
		if ( is_wp_error( $api_key ) ) {
			return new WP_Error(
				get_class(),
				'No valid API KEY'
			);
		}

		$url = sprintf(
			'https://premium.wpmudev.org/api/hosting/v1/%s/%s',
			$this->get_site_id(), // <-- This is pretty important
			sanitize_text_field( $action )
		);
		$type = self::REQUEST_POST === $type
			? self::REQUEST_POST
			: self::REQUEST_GET;
		$args = array(
			'method' => $type,
			'timeout' => 60,
			'headers' => array(
				'Authorization' => $api_key,
			),
		);
		if ( self::REQUEST_POST === $type && ! empty( $params ) ) {
			$args['body'] = $params;
		}

		$result = wp_remote_request( $url, $args );

		if ( is_wp_error( $result ) ) return $result;
		$status = wp_remote_retrieve_response_code( $result );
		if ( 200 !== (int) $status ) {
			return new WP_Error(
				get_class(),
				'Service responded with non-200'
			);
		}

		return json_decode( wp_remote_retrieve_body( $result ), true );
	}

	/**
	 * Queries the api for action status
	 *
	 * @param int $action_id Action ID
	 *
	 * @return WP_Error|array
	 */
	protected function get_action_status( $action_id ) {
		return $this->get_api_result( sprintf(
			'actions/%d',
			$action_id
		));
	}

	/**
	 * Builds the arguments for any backup restore AJAX calls
	 *
	 * @param string $backup_id Backup ID
	 * @param array $current_restore Current restore state
	 */
	protected function deal_with_backup_restore( $backup_id ) {
		$status = $this->_restore_backup( $backup_id );

		if ( ! $status || ! is_array( $status ) || is_wp_error( $status ) ) {
			// The API call wasnt successful.
			$args = array(
				'is_done' => false,
				'error' => true,
			);
		} else {
			if ( ! empty( $status['action_id'] )  ) {
				if ( ! empty( $status['completed_at'] ) ) {
					// The restore completed on the first go, so we need to set it complete.
					$args = array(
						'is_done' => true,
					);
				} else {
					// The restore didn't complete on the first go, so we need to somehow persist this action ID.
					$args = array(
						'is_done' => false,
						'action_id' => $status['action_id'],
					);
				}
			}
		}

		return $args;
	}

	/**
	 * Builds the arguments for any following AJAX calls, depending on any previous ran backups
	 *
	 * @param array $current Any previous ran backup
	 * @param bool $new_backup Whether called from the new backup creation
	 *
	 * @return array
	 */
	protected function deal_with_running_backup( $current, $new_backup ) {
		if ( isset( $current['is_done'] ) && $current['is_done'] ) {
			// The previously ran backup has been completed.
			update_site_option( self::OPTIONS_BACKUP_FLAG, false );

			//Now lets check if it completed as errored, or as success.
			$args = ( isset( $current['error'] ) &&  $current['error'] ) ? array(
				'is_done' => false,
				'error' => true
			) : array( 'is_done' => true );

		} else if ( isset( $current['type'] ) && 'backup' === $current['type'] && 'in-progress' === $current['status'] ) {
			// The previously ran backup is still running.
			$args = array(
				'is_done' => false,
				'action_id' => $current['id' ],
				'older_backup' => true,
			);
		} else {
			if ( $new_backup ) {
				// Let's create a new backup. But only if there are no older backups still running.
				// We have checked nonces coming into the function.
				// phpcs:ignore
				if ( ! get_site_option( self::OPTIONS_BACKUP_FLAG ) && ( ! isset( $_POST['older_backup'] ) || false === $_POST['older_backup'] ) ){
					$status = $this->_start_backup();

					if ( ! $status || ! is_array( $status ) || is_wp_error( $status ) ) {
						// The API call wasnt successful.
						$args = array(
							'is_done' => false,
							'error' => true,
						);
					} else {
						if ( ! empty( $status['action_id'] )  ) {
							if ( ! empty( $status['completed_at'] ) ) {
								// The backup completed on the first go, so we need to set it complete.
								update_site_option( self::OPTIONS_BACKUP_FLAG, false );
								$args = array(
									'is_done' => true,
								);
							} else {
								// The backup didn't complete on the first go, so we need to persist this action ID.
								update_site_option( self::OPTIONS_BACKUP_FLAG, $status['action_id' ] );
								$args = array(
									'is_done' => false,
									'action_id' => $status['action_id' ],
								);
							}
						}
					}
				} else {
					if ( ! get_site_option( self::OPTIONS_BACKUP_FLAG ) ) {
						$args = array(
							'is_done' => true,
							'older_backup' => true,
						);
					} else {
						$args = array(
							'is_done' => false,
							'action_id' => get_site_option( self::OPTIONS_BACKUP_FLAG ),
							'older_backup' => true,
						);
					}
				}
			} else {
				$args = array();
			}
		}
		return $args;
	}

	/**
	 * Builds the UI for listing managed and hosting backups
	 *
	 * @param array $raw_backups Combined hosting and managed backups.
	 * @param bool $dashboard Whether on dashboard.
	 *
	 * @return array
	 */
	public function deal_with_listing_backups( $raw_backups, $dashboard ) {
		$backups = array();
		foreach ( $raw_backups as $key => $backup ) {
			$backups[ $key ] = $backup;

			$download_tooltip = ! $dashboard ? " wps-tooltip sui-tooltip sui-tooltip-small sui-tooltip-top-center" : "";
			$download_tooltip_content = ! $dashboard ? ' data-tooltip="Download" ' : "";

			if ( isset( $backup['Key'] ) ) {
				// Hosting backup
				$backups[ $key ]['id']      = esc_attr( substr( $backup['Key'], strpos( $backup['Key'], '@' ) + 1 ) );

				$backups[ $key ]['link']    = $dashboard ? esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'M d, Y g:i a' ) ) : '<a href="#" class="snapshot-hosting-backup-export' .  $download_tooltip . '" data-backup-id="' . esc_attr( $backups[ $key ]['id'] ) . '"' .  $download_tooltip_content . '>' . esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'M d, Y g:i a' ) ) . '</a>';

				$backup_menu = $this->get_backup_menu( $backups, $key, $backup, true );
			} else {
				// Managed Backup
				$backup_link = $this->_managed_model->remote()->get_backup_link( $backup['timestamp'] );

				/* If there is no remote URL, build a local download link */
				if ( ! $backup_link ) {
					$backup_link = network_admin_url('admin.php?page=snapshot_pro_hosting_backups');
					$backup_link = add_query_arg( 'snapshot-action', 'download-backup-archive', $backup_link );
					$backup_link = add_query_arg( 'backup-item', sanitize_text_field( $backup['timestamp'] ), $backup_link );
				}
				$backups[ $key ]['link']    = $dashboard ? esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'M d, Y g:i a' ) ) : '<a href="' . esc_url( $backup_link ) . '" class="' .  $download_tooltip . '" ' . $download_tooltip_content . '>' . esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'M d, Y g:i a' ) ) . '</a>';

				$backup_menu = $this->get_backup_menu( $backups, $key, $backup, false );
			}

			$backups[ $key ]['date'] = esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'F j, Y' ) );
			$backups[ $key ]['time'] = esc_html__( 'at', SNAPSHOT_I18N_DOMAIN ) . ' ' . esc_attr( Snapshot_Helper_Utility::show_date_time( strtotime( $backups[ $key ]['creation_time'] ), 'g:ia' ) );

			$backups[ $key ]['icon']    = $this->get_backup_icon( $backup );
			$backups[ $key ]['tooltip'] = $this->get_backup_tooltip( $backup, $dashboard );

			$backups[ $key ]['type']    = $this->get_backup_type( $backup );
			$backups[ $key ]['context'] = isset( $backups[ $key ]['context'] ) ? str_replace( array( 'Manual', 'Nightly' ), array( 'Once', 'Daily, @ ' . esc_attr( Snapshot_Helper_Utility::get_hosting_backup_local_time() ) ), $backups[ $key ]['context'] ) : '-';

			if ( ! $dashboard ) {
				$backups[ $key ]['menu'] = $backup_menu;

				if ( ! empty( $backup['local'] ) ) {
					if ( is_multisite() ) {
						$page_url = network_admin_url( 'admin.php?page=snapshot_pro_hosting_backups' );
					} else {
						$page_url = admin_url( 'admin.php?page=snapshot_pro_hosting_backups' );
					}

					$delete_link = add_query_arg(
						array(
							'action' => 'delete',
							'item' => $backup['timestamp'],
							'snapshot-full_backups-list-nonce' => wp_create_nonce( 'snapshot-full_backups-list' ),
							'delete-bulk' => array( $backup['timestamp'] )
						),
						$page_url
					);

					$backups[ $key ]['menu'] = '
						<a class="button button-small button-outline button-gray" href="' . esc_url( $delete_link ) . '">
							<span class="wps-destination-config-text">' . esc_html__( 'Delete', SNAPSHOT_I18N_DOMAIN ) . '</span>
						</a>
					';
				}
			}

		}

		return $backups;
	}

	/**
	 * Return icon tooltip
	 *
	 * @param $backup
	 * @param $dashboard
	 *
	 * @return string
	 */
	private function get_backup_tooltip( $backup, $dashboard ) {
		$tooltip = '';

		if ( ! empty( $backup['local'] ) ) {
			$tooltip = esc_html__( "This backup failed to upload to The Hub. Usually we'd recommend re-trying the upload, but since you're hosting with us you already have daily backups running. Therefore, you can probably delete this backup.", SNAPSHOT_I18N_DOMAIN );
			if ( $dashboard ) {
				$tooltip .= esc_html__( " Visit the Backups page to delete it.", SNAPSHOT_I18N_DOMAIN );
			}
		}

		return $tooltip;
	}

	/**
	 * Return hosting icon
	 *
	 * @param array $backup
	 *
	 * @return string
	 */
	private function get_backup_icon( $backup ) {
		if ( ! empty( $backup['local'] ) ) {
			$local = ' i-snapshot-error';
		} else {
			$local = '';
		}

		if ( isset( $backup['context'] ) ) {
			if( preg_match( "/Automate/i", $backup['context'] ) ) {
				return 'i-cloud-automate';
			} else {
				return 'i-cloud-hosting';
			}
		} else {
			if (Snapshot_Helper_Backup::is_automated_backup($backup['name'])) {
				return 'i-cloud-automate' . $local;
			} else {
				return 'i-snapshot' . $local;
			}
		}

		return 'i-snapshot';
	}

	/**
	 * Return backup type
	 *
	 * @param array $backup
	 *
	 * @return string
	 */
	private function get_backup_type( $backup ) {
		if ( isset( $backup['Key'] ) ) {
			return esc_html__( 'Hosting', SNAPSHOT_I18N_DOMAIN );
		} else {
			if ( Snapshot_Helper_Backup::is_automated_backup( $backup['name'] ) ) {
				return esc_html__( 'Automated', SNAPSHOT_I18N_DOMAIN );
			} else {
				return esc_html__( 'Managed', SNAPSHOT_I18N_DOMAIN );
			}
		}
	}

	/**
	 * Return backup menu
	 *
	 * @param array $backup
	 * @param bool $hosting_backup
	 *
	 * @return string
	 */
	private function get_backup_menu( $backups, $key, $backup, $hosting_backup ) {
		if ( $hosting_backup ) {
			$is_staging = strpos( $_SERVER['HTTP_HOST'], '.staging.wpmudev.host' ) !== false;
			if ( $is_staging ) {
				$restore_item =
				'<li class="snapshot-hosting-backup-restore sui-tooltip sui-constrained sui-tooltip-left" data-tooltip="You can\'t restore the backup, because you\'re on staging. Please log in to your regular account and try again.">
					<a style="opacity: .5; pointer-events: none;" href="#" data-backup-id="' . esc_attr( $backups[ $key ]['id'] ) . '">' . esc_html__( 'Restore', SNAPSHOT_I18N_DOMAIN ) . '</a>
				</li>
				';
			} else {
				$restore_item =
				'<li>
					<a href="#" class="snapshot-hosting-backup-restore" data-backup-id="' . esc_attr( $backups[ $key ]['id'] ) . '">' . esc_html__( 'Restore', SNAPSHOT_I18N_DOMAIN ) . '</a>
				</li>
				';
			}
			$backup_menu =
'<div class="wps-menu">

	<div class="wps-menu-dots">

		<div class="wps-menu-dot"></div>

		<div class="wps-menu-dot"></div>

		<div class="wps-menu-dot"></div>

	</div>

	<div class="wps-menu-holder">

		<ul class="wps-menu-list">

			<li class="wps-menu-list-title">' . esc_html__( 'Options', SNAPSHOT_I18N_DOMAIN ) . '</li>
			<li>
				<a href="' . esc_url( sprintf( self::HUB_BACKUP_URL, $this->get_site_id() ) ) . '" target="_blank" >' . esc_html__( 'Info', SNAPSHOT_I18N_DOMAIN ) . '</a>
			</li>' . $restore_item . '
		</ul>

	</div>

</div>';
		} else {
			$restore_link = add_query_arg(
				array(
					'snapshot-action' => 'restore',
					'item' => $backup['timestamp'],
					'snapshot-full_backups-noonce-field' => wp_create_nonce( 'snapshot-full_backups' ),
				),
				network_admin_url('admin.php?page=snapshot_pro_managed_backups')
			);

			$delete_link = add_query_arg(
				array(
					'action' => 'delete',
					'item' => $backup['timestamp'],
					'snapshot-full_backups-list-nonce' => wp_create_nonce( 'snapshot-full_backups-list' ),
					'delete-bulk' => array( $backup['timestamp'] )
				),
				network_admin_url('admin.php?page=snapshot_pro_hosting_backups')
			);

			$backup_menu =
'<div class="wps-menu">

	<div class="wps-menu-dots">

		<div class="wps-menu-dot"></div>

		<div class="wps-menu-dot"></div>

		<div class="wps-menu-dot"></div>

	</div>

	<div class="wps-menu-holder">

		<ul class="wps-menu-list">

			<li class="wps-menu-list-title">' . esc_html__( 'Options', SNAPSHOT_I18N_DOMAIN ) . '</li>
			<li>
				<a href="' . esc_url( $restore_link ) . '">' . esc_html__( 'Restore', SNAPSHOT_I18N_DOMAIN ) . '</a>
			</li>
			<li>
				<a href="' . esc_attr( $delete_link ) . '" class="snapshot-older-managed-backup-delete">' . esc_html__( 'Delete', SNAPSHOT_I18N_DOMAIN ) . '</a>
			</li>

		</ul>

	</div>

</div>';
		}

		return $backup_menu;
	}
}