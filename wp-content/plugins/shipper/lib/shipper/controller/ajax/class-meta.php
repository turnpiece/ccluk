<?php
/**
 * Author: Hoang Ngo
 */

class Shipper_Controller_Ajax_Meta extends Shipper_Controller_Ajax{
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
	}

	public function json_migration_exclusion() {
		$this->do_request_sanity_check();
		$data                   = stripslashes_deep( $_POST );
		$model                  = new Shipper_Model_Database;
		$table_excluded         = array_diff( $model->get_tables_list(), $data['exclude_tables'] );
		$model                  = new Shipper_Model_Stored_MigrationExclusion();
		$data['exclude_tables'] = $table_excluded;
		if ( ! empty( $data['exclude_files'] ) && is_array( $data['exclude_files'] ) ) {
			$model->set(
				Shipper_Model_Stored_MigrationExclusion::KEY_EXCLUSIONS_FS,
				array_map( 'sanitize_text_field', $data['exclude_files'] )
			);
		}

		if ( ! empty( $data['exclude_tables'] ) && is_array( $data['exclude_tables'] ) ) {
			$model->set(
				Shipper_Model_Stored_MigrationExclusion::KEY_EXCLUSIONS_DB,
				array_map( 'sanitize_text_field', $data['exclude_tables'] )
			);
		}

		if ( ! empty( $data['exclude_extra'] ) && is_array( $data['exclude_extra'] ) ) {
			$model->set(
				Shipper_Model_Stored_MigrationExclusion::KEY_EXCLUSIONS_XX,
				array_map( 'sanitize_text_field', $data['exclude_extra'] )
			);
		}
		$ret = $model->save();

		wp_send_json_success();
	}

	/**
	 * We store the info about prefix
	 */
	public function json_dbprefix_update() {
		$this->do_request_sanity_check();

		$data   = stripslashes_deep( $_POST );
		$option = $data['option'];
		$value  = $data['value'];

		if ( ! in_array( $option, [
			'source',
			'destination',
			'custom'
		] ) ) {
			wp_send_json_error( [
				'message' => __( "Invalid request", 'shipper' )
			] );
		}
		if ( $option === 'custom' ) {
			if ( strlen( $value ) == 0 ) {
				wp_send_json_error( [
					'message' => __( "Your prefix can't be empty!", 'shipper' )
				] );
			}
		}

		if ( preg_match( '|[^a-z0-9_]|i', $value ) ) {
			wp_send_json_error( [
				'message' => __( "Table prefix can only contain numbers, letters, and underscores.", 'shipper' )
			] );
		}
		$model = new Shipper_Model_Stored_Dbprefix;
		$model->set_data( [
			'option' => $option,
			'value'  => $value
		] );
		$ret = $model->save();

		wp_send_json_success();
	}
}