<?php
/**
 * Author: Hoang Ngo
 */

class Shipper_Task_Import_DBPrefix extends Shipper_Task_Import {

	public function apply( $args = array() ) {
		$model  = new Shipper_Model_Stored_Migration();
		$prefix = $model->get( 'destination_prefix' );
		if ( $prefix == false ) {
			//do nothing
			return true;
		}

		global $table_prefix;
		if ( $table_prefix == $prefix ) {
			//we use the same, so do nothing
		}
		//Since all the db and stuff get done in table task, vie override, we wil need to ajust wp-config if hasn't
		$wpconfig_path = Shipper_Helper_Fs_Path::get_abspath( 'wp-config.php' );
		Shipper_Helper_Log::debug( 'Config path ' . $wpconfig_path );
		$config    = file( $wpconfig_path );
		$hook_line = $this->findDefaultHookLine( $config );
		Shipper_Helper_Log::debug( 'found hook line at ' . $hook_line );
		$new_prefix = "\$table_prefix = '" . $prefix . "';" . PHP_EOL;
		Shipper_Helper_Log::debug( 'new prefix ' . $new_prefix );
		$config[ $hook_line ] = $new_prefix;
		$config               = array_filter( $config );
		if ( ! file_put_contents( $wpconfig_path, implode( null, $config ), LOCK_EX ) ) {
			$this->add_error(
				self::ERR_ACCESS,
				__( 'The file wp-config.php is not writable', 'shipper' )
			);

			return true;
		}

		return true;
	}

	/**
	 * @param $config
	 *
	 * @return bool|int|string
	 */
	protected function findDefaultHookLine( $config ) {
		global $wpdb;
		$pattern = '/^\$table_prefix\s*=\s*[\'|\"]' . $wpdb->prefix . '[\'|\"]/';
		foreach ( $config as $k => $line ) {
			if ( preg_match( $pattern, $line ) ) {
				return $k;
			}
		}

		return false;
	}

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( "Update new database prefix", 'shipper' );
	}
}