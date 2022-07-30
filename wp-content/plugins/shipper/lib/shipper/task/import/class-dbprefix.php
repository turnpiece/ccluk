<?php
/**
 * Author: Hoang Ngo
 *
 * @package shipper
 */

/**
 * Class Shipper_Task_Import_DBPrefix
 */
class Shipper_Task_Import_DBPrefix extends Shipper_Task_Import {

	/**
	 * Apply method
	 *
	 * @param array $args array of arguments.
	 *
	 * @return bool|mixed
	 */
	public function apply( $args = array() ) {
		$model  = new Shipper_Model_Stored_Migration();
		$prefix = $model->get( 'destination_prefix' );
		if ( false === $prefix ) {
			// do nothing.
			return true;
		}

		global $table_prefix;

		// Since all the db and stuff get done in table task, vie override, we wil need to ajust wp-config if hasn't.
		$wpconfig_path = Shipper_Helper_Fs_Path::get_abspath( 'wp-config.php' );
		Shipper_Helper_Log::debug( 'Config path ' . $wpconfig_path );
		$config    = file( $wpconfig_path );
		$hook_line = $this->find_default_hook_line( $config );

		if ( ! $hook_line ) {
			Shipper_Helper_Log::debug( __( 'Hook line not found', 'shipper' ) );
			return true;
		}

		Shipper_Helper_Log::debug( 'found hook line at ' . $hook_line );
		$new_prefix = "\$table_prefix = '" . $prefix . "';" . PHP_EOL;
		Shipper_Helper_Log::debug( 'new prefix ' . $new_prefix );
		$config[ $hook_line ] = $new_prefix;
		$config               = array_filter( $config );

		$fs = Shipper_Helper_Fs_File::open( $wpconfig_path, 'w' );

		if ( ! $fs ) {
			return false;
		}

		if ( ! $fs->isWritable() ) {
			$this->add_error(
				self::ERR_ACCESS,
				__( 'The file wp-config.php is not writable', 'shipper' )
			);

			return true;
		}

		$fs->flock( LOCK_EX );
		$fs->fwrite( implode( null, $config ) );
		$fs->flock( LOCK_UN );

		return true;
	}

	/**
	 * Find default hook line.
	 *
	 * @param array $config array of config.
	 *
	 * @return bool|int|string
	 */
	protected function find_default_hook_line( $config ) {
		$pattern = '/^\$table_prefix\s*=\s*[\'|\"][\w-]+[\'|\"]/';

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
		return __( 'Update new database prefix', 'shipper' );
	}
}