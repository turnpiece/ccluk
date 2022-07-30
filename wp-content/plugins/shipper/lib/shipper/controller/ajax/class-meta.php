<?php
/**
 * Author: Hoang Ngo
 *
 * @package shipper
 */

/**
 * Class Shipper_Controller_Ajax_Meta
 */
class Shipper_Controller_Ajax_Meta extends Shipper_Controller_Ajax {
	/**
	 * Boots the controller and sets up event listeners.
	 */
	public function boot() {
		if ( ! is_admin() ) {
			return false;
		}

		add_action(
			'wp_ajax_shipper_dbprefix_update',
			array( &$this, 'json_dbprefix_update' )
		);

		add_action(
			'wp_ajax_shipper_migration_exclusion',
			array( &$this, 'json_migration_exclusion' )
		);

		add_action( 'wp_ajax_shipper_networktype_update', array( &$this, 'json_network_type' ) );
		add_action( 'wp_ajax_shipper_clear_db_prefix', array( $this, 'json_clear_db_prefix' ) );

		// Sub-site search handler.
		$this->add_handler( 'search_sub_site', array( $this, 'search_sub_site' ) );
	}

	/**
	 * Json network type
	 *
	 * @return void
	 */
	public function json_network_type() {
		$this->do_request_sanity_check();
		$data  = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
		$model = new Shipper_Model_Stored_MigrationMeta();
		$model->set( Shipper_Model_Stored_MigrationMeta::NETWORK_MODE, $data['mode'] );
		$model->set( Shipper_Model_Stored_MigrationMeta::NETWORK_SUBSITE_ID, $data['site_id'] );
		$model->save();
		wp_send_json_success();
	}

	/**
	 * JSON migration exclusion
	 *
	 * @return void
	 */
	public function json_migration_exclusion() {
		$this->do_request_sanity_check();
		$data           = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
		$model          = new Shipper_Model_Database();
		$table_included = ! empty( $data['exclude_tables'] ) ? $data['exclude_tables'] : array();
		$table_excluded = array_diff( $model->get_tables_list(), $table_included );

		$model = new Shipper_Model_Stored_MigrationMeta();

		// categorize the custom table so we can specific treat it later.
		// custom table is the case theu don't have db prefix.
		$all_tables   = Shipper_Helper_Template_Sorter::get_grouped_tables();
		$other_tables = array();
		foreach ( $table_included as $tbl ) {
			if ( in_array( $tbl, $all_tables[ Shipper_Helper_Template_Sorter::OTHER_TABLES ], true ) ) {
				$other_tables[] = $tbl;
			}
		}
		$model->set( Shipper_Model_Stored_MigrationMeta::KEY_OTHER_TABLES, $other_tables );

		$data['exclude_tables'] = $table_excluded;
		if ( ! empty( $data['exclude_files'] ) && is_array( $data['exclude_files'] ) ) {
			$model->set(
				Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_FS,
				array_map( 'sanitize_text_field', $data['exclude_files'] )
			);
		} else {
			$model->set( Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_FS, array() );
		}

		if ( ! empty( $data['exclude_tables'] ) && is_array( $data['exclude_tables'] ) ) {
			$excluded_tables = array_map( 'sanitize_text_field', $data['exclude_tables'] );
			$model->set(
				Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_DB,
				$excluded_tables
			);
		} else {
			$model->set( Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_DB, array() );
		}

		if ( ! empty( $data['exclude_extra'] ) && is_array( $data['exclude_extra'] ) ) {
			$model->set(
				Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_XX,
				array_map( 'sanitize_text_field', $data['exclude_extra'] )
			);
		} else {
			$model->set( Shipper_Model_Stored_MigrationMeta::KEY_EXCLUSIONS_XX, array() );
		}

		$model->save();
		wp_send_json_success();
	}

	/**
	 * We store the info about prefix
	 */
	public function json_dbprefix_update() {
		$this->do_request_sanity_check();

		$data   = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
		$option = $data['option'];
		$value  = $data['value'];

		if ( ! in_array( $option, array( 'source', 'destination', 'custom' ), true ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid request', 'shipper' ),
				)
			);
		}

		if ( 'custom' === $option ) {
			if ( 0 === strlen( $value ) ) {
				wp_send_json_error(
					array(
						'message' => __( "Your prefix can't be empty!", 'shipper' ),
					)
				);
			}
		}

		if ( preg_match( '|[^a-z0-9_]|i', $value ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Table prefix can only contain numbers, letters, and underscores.', 'shipper' ),
				)
			);
		}

		$model = new Shipper_Model_Stored_MigrationMeta();

		$model->set( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_OPTION, $option );
		$model->set( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_VALUE, $value );
		$model->save();

		wp_send_json_success();
	}

	/**
	 * Clear DB Prefix on ready to ship page (API migration)
	 * Clicking on back button, we're clearing saved db prefix so that
	 * The previous page is get rendered.
	 *
	 * @since 1.2.1
	 */
	public function json_clear_db_prefix() {
		$this->do_request_sanity_check();

		$model = new Shipper_Model_Stored_MigrationMeta();

		$model->remove( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_OPTION );
		$model->remove( Shipper_Model_Stored_MigrationMeta::KEY_DBPREFIX_VALUE );
		$model->save();
	}


	/**
	 * Search site
	 *
	 * @since 1.2.8
	 *
	 * @return void
	 */
	public function search_sub_site() {
		$this->do_request_sanity_check( 'shipper_search_sub_site', self::TYPE_GET );

		$args           = array();
		$get            = wp_unslash( $_GET ); // phpcs:ignore
		$search         = ! empty( $get['search'] ) ? sanitize_text_field( $get['search'] ) : '';
		$args['search'] = $search;

		$sites = Shipper_Helper_MS::get_all_sites( $args );
		$sites = array_map(
			function( $site ) {
				return array(
					'site_id'  => $site->blog_id,
					'site_url' => esc_url(
						$site->domain . rtrim( $site->path, '/' )
					),
				);
			},
			$sites
		);

		wp_send_json_success( $sites );
	}
}