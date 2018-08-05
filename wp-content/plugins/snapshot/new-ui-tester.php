<?php

if ( class_exists( 'WPMUDEVSnapshot_New_Ui_Tester' ) ) {
	return;
}

class WPMUDEVSnapshot_New_Ui_Tester {

	public function dashboard() {
		$this->render( 'dashboard' );
	}

	public function snapshots() {

		if ( isset( $_REQUEST['snapshot-action'] ) ) {
			if ( ! isset( $_REQUEST['snapshot-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['snapshot-noonce-field'], 'snapshot-nonce' ) ) {
				return;
			}

			if ( 'new' === $_REQUEST['snapshot-action'] ) {
				$this->render(
						'snapshots/snapshot', false, array(
						'action' => 'add',
						'item' => array()
						)
				);
				return;
			}
		}

		if ( isset( $_REQUEST['item'], WPMUDEVSnapshot::instance()->config_data['items'][ sanitize_text_field( $_REQUEST['item'] ) ] ) ) {

			$item = WPMUDEVSnapshot::instance()->config_data['items'][ sanitize_text_field( $_REQUEST['item'] ) ];

			$snapshot_action = 'default';

			if ( isset( $_REQUEST['snapshot-action'] ) ) {
				$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );
			}

			$force_backup = false;

			switch ( $snapshot_action ) {
				case 'backup':
					$force_backup = true;
					// no break.
				case 'edit':
					$this->render(
                         'snapshots/snapshot', false, array(
							'action' => 'update',
							'item' => $item,
							'force_backup' => $force_backup
							)
                        );
					break;

				case 'restore':
					if ( ( isset( $_GET['snapshot-data-item'] ) ) && ( isset( $item['data'][ intval( $_GET['snapshot-data-item'] ) ] ) ) ) {
						$data_item_key = intval( $_GET['snapshot-data-item'] );
						$this->render(
                             'snapshots/restore', false, array(
								'item' => $item,
								'data_item_key' => $data_item_key
								)
                            );
					} else {
						$this->render( 'snapshots/item', false, array( 'item' => $item ) );
					}
					break;
				default:
					$this->render( 'snapshots/item', false, array( 'item' => $item ) );
			}


		} else {
			$snapshots = WPMUDEVSnapshot::instance()->config_data['items'];
			$count_all_snapshots = count( $snapshots );
			$all_destinations = WPMUDEVSnapshot::instance()->config_data['destinations'];
			$filter = ( isset( $_GET['destination'] ) ) ? sanitize_text_field( $_GET['destination'] ) : '';
			if ( '' !== $filter && isset( $all_destinations[ $filter ] ) ) {
				$filtred_snapshot = array();
				foreach ( $snapshots as $key => $snapshot ) {
					if ( isset( $snapshot['destination'] ) && $snapshot['destination'] === $filter ) {
						$filtred_snapshot[ $key ] = $snapshot;
					}
				}
				$snapshots = $filtred_snapshot;
			}
			$results_count = count( $snapshots );
			$per_page = 20;
			//Max number of pages
			$max_pages = ceil( $results_count / $per_page );
			$paged = ( ! isset( $_GET['paged'] ) ) ? 1 : intval( $_GET['paged'] );
			$offset = $per_page * ( $paged - 1 );

			$data = array(
				'snapshots' => $snapshots,
				'results_count' => $results_count,
				'count_all_snapshots' => $count_all_snapshots,
				'per_page' => $per_page,
				'max_pages' => $max_pages,
				'paged' => $paged,
				'offset' => $offset,
				'filter' => $filter
			);
			$this->render( "snapshots", false, $data );
		}
	}

	/*public function create_snapshot(){
		$this->render( "snapshots/partials/create-snapshot-progress", "Create Snapshot" );
	}*/

	public function destinations() {
		$snapshot_action = 'default';

		if ( isset( $_REQUEST['snapshot-action'] ) ) {
			if ( ! isset( $_REQUEST['destination-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['destination-noonce-field'], 'snapshot-destination' ) ) {
				return;
			}
			$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );
		}

		switch ( $snapshot_action ) {

			case 'add':
			case 'edit':
			case 'update':
				$this->render( 'destinations/add/index' );
				break;

			default:
				$this->render( 'destinations' );
		}
	}

	public function managed_backups() {
		$model = new Snapshot_Model_Full_Backup();

		$is_dashboard_active = $model->is_dashboard_active();
		$is_dashboard_installed = $is_dashboard_active
			? true
			: $model->is_dashboard_installed();
		$has_dashboard_key = $model->has_dashboard_key();

		$is_client = $is_dashboard_installed && $is_dashboard_active && $has_dashboard_key;

		$apiKey = $model->get_config( 'secret-key', '' );

		$has_snapshot_key = $is_client && Snapshot_Model_Full_Remote_Api::get()->get_token() !== false && ! empty( $apiKey );

		if ( ! $is_client ) {

			$this->render( "managed-backups/get-started", false, array( 'model' => $model ) );

		} else if ( ! $has_snapshot_key ) {

			$this->render( "managed-backups/activate", false, array( 'model' => $model ) );

		} else {
			$snapshot_action = 'default';
			if ( isset( $_REQUEST['snapshot-action'] ) ) {
				$snapshot_action = sanitize_text_field( $_REQUEST['snapshot-action'] );

				if ( ! isset( $_REQUEST['snapshot-full_backups-noonce-field']  ) ) {
					return;
				}
				if ( ! wp_verify_nonce( $_REQUEST['snapshot-full_backups-noonce-field'], 'snapshot-full_backups' ) ) {
					return;
				}
			}

			function _snapshot_sort_managed_backups_array ( $a, $b ){
				return $b['timestamp'] - $a['timestamp'];
			}

			switch ( $snapshot_action ) {
				case 'backup':
					$this->render( "managed-backups/new-backup", false, array( 'model' => $model ) );
					break;
				case 'restore':
					$item = false;
					if ( isset( $_GET['item'] ) ) {
						$item = $model->get_backup( sanitize_text_field( $_GET['item'] ) );
					}
					if ( $item ) {
						$this->render(
                             "managed-backups/restore", false, array(
								'model' => $model,
								'item' => $item
								)
                            );
						break;
					}
					// Potentially no break.
				default:
					$backups = $model->get_backups();
					usort( $backups, '_snapshot_sort_managed_backups_array' );
					$last_backup = reset( $backups );
					$filter = ( isset( $_GET['date'] ) ) ? sanitize_text_field( $_GET['date'] ) : '';
					$timestamps = wp_list_pluck( $backups, 'timestamp' );
					$months = array();
					foreach ( $timestamps as $key => $month ) {
						$months[ date( 'mY', $month ) ] = date( 'F Y', $month );
					}

					if ( '' !== $filter && is_int( $filter ) ) {
						$filtred_snapshot = array();
						foreach ( $backups as $key => $snapshot ) {
							if ( isset( $snapshot['timestamp'] ) && date( 'mY', $snapshot['timestamp'] ) === $filter ) {
								$filtred_snapshot[ $key ] = $snapshot;
							}
						}
						$backups = $filtred_snapshot;
					}
					$results_count = count( $backups );
					$per_page = 20;
					//Max number of pages
					$max_pages = ceil( $results_count / $per_page );
					$paged = ( ! isset( $_GET['paged'] ) ) ? 1 : intval( $_GET['paged'] );
					$offset = $per_page * ( $paged - 1 );

					$apiKey = $model->get_config( 'secret-key', '' );

					$data = array(
						"model" => $model,
						"last_backup" => $last_backup,
						"hasApikey" => ! empty( $apiKey ),
						"apiKey" => $apiKey,
						"apiKeyUrl" => $model->get_current_secret_key_link(),
						'backups' => $backups,
						'results_count' => $results_count,
						'per_page' => $per_page,
						'max_pages' => $max_pages,
						'paged' => $paged,
						'offset' => $offset,
						'filter' => $filter,
						'months' => $months

					);

					$this->render( 'managed_backups', false, $data );
			}
		}
	}

	public function import() {
		$this->render( 'import' );
	}

	public function settings() {
		$this->render( 'settings' );
	}

	/**
	 * @param string $file
	 * @param bool   $deprecated
	 * @param array  $params
	 * @param bool   $return
	 * @param bool   $footer
	 *
	 * @return string
	 */
	public function render( $file, $deprecated = false, $params = array(), $return = false, $footer = true ) {

		$template_filename = "views/$file.php";
		$template_file = plugin_dir_url( plugin_basename( __FILE__ ) ) . $template_filename;

		if ( ! file_exists( $template_file ) ) {
			$template_file = trailingslashit( dirname( __FILE__ ) ) . $template_filename;
		}

		if ( $return ) {
			ob_start();
		}

		// phpcs:ignore
		extract( $params, EXTR_SKIP );

		include $template_file;

		if ( $footer ) {
			$this->render( 'common/footer', false, array(), false, false );
		}

		if ( $return ) {
			return ob_get_clean();
		}

		foreach ( $params as $param ) {
			unset( $param );
		}

		return null;
	}

	/**
	 * Print the form errors for a particular field, if there are any
	 *
	 * @param $field
	 */
	public function input_error_message( $field ) {

		if ( ! isset( WPMUDEVSnapshot::instance()->form_errors[ $field ] ) ) {
			return;
		}

		$field_errors = (array) WPMUDEVSnapshot::instance()->form_errors[ $field ];

		echo '<div class="error-text">';

		foreach ( $field_errors as $error ) {
			echo '<p>', esc_html( $error ), '</p>';
		}

		echo '</div>';
	}

	public function input_error_class( $field, $echo = true ) {

		if ( ! isset( WPMUDEVSnapshot::instance()->form_errors[ $field ] ) ) {
			return '';
		}

		$class = ' validation-error';

		if ( $echo ) {
			echo esc_attr( $class );
		}

		return $class;
	}
}