<?php
/**
 * Shipper packages task: DB tables export
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Tables package - extends from export for now, and facades the package task interface
 */
class Shipper_Task_Package_Tables extends Shipper_Task_Export_Tables {

	public function apply( $args = array() ) {
		add_filter( 'shipper_export_table_include_transients', '__return_false' );
		add_filter( 'shipper_export_table_include_row', array( &$this, 'maybe_exclude_row' ), 10, 3 );
		$is_done = parent::apply( $args );
		if ( $is_done ) {
			return $is_done;
		}
		remove_filter( 'shipper_export_table_include_transients', '__return_false' );
		remove_filter( 'shipper_export_table_include_row', array( &$this, 'maybe_exclude_row' ), 10 );
	}

	public function maybe_exclude_row( $include, $raw, $table ) {
		global $wpdb;
		$tbl        = $wpdb->options;
		$field_name = 'option_name';
		if ( is_multisite() ) {
			$tbl        = $wpdb->sitemeta;
			$field_name = 'meta_key';
		}
		if ( $tbl == $table ) {
			$fields = [
				//'wdp_un_analytics_enabled',
				'wdp_un_analytics_site_id',
				'wdp_un_analytics_tracker',
				'wdp_un_analytics_metrics',
				'wdp_un_remote_access'
			];
			if ( isset( $raw[ $field_name ] ) && in_array( $raw[ $field_name ], $fields ) ) {
				//Shipper_Helper_Log::write( var_export( $raw, true ) );

				//we dont copy support staff status
				return false;
			}
		}

		return $include;
	}

	public function table_to_final_destination( $table, $exported_file ) {
		$destination = $this->get_destination_path( $table . '.sql' );

		$zip = Shipper_Task_Package::get_zip();

		if ( ! $zip->add_file( $exported_file, $destination ) ) {
			throw new Shipper_Exception(
				sprintf( __( 'Shipper couldn\'t archive exported table %1$s as %2$s', 'shipper' ), $exported_file, $destination )
			);

			return false;
		}
		$zip->close();

		return true;
	}
}