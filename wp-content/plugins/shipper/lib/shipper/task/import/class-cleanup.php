<?php
/**
 * Shipper tasks: import, staging area cleanup task
 *
 * @package shipper
 */

/**
 * Shipper import cleanup class
 */
class Shipper_Task_Import_Cleanup extends Shipper_Task_Import {

	/**
	 * Gets import task label
	 *
	 * @return string
	 */
	public function get_work_description() {
		return __( 'Clean up the staging area', 'shipper' );
	}

	/**
	 * Task runner method
	 *
	 * Returns (bool)true on completion.
	 *
	 * @param array $args Not used.
	 *
	 * @return bool
	 */
	public function apply( $args = array() ) {
		$path   = Shipper_Helper_Fs_Path::get_temp_dir();
		$status = $this->clean_dir( $path, '' );

		return $status;
	}

	/**
	 * Recursively clean directory path
	 *
	 * @param string $path Path to clean up.
	 * @param string $previous Previous path.
	 *
	 * @return bool
	 */
	public function clean_dir( $path, $previous ) {
		$next    = ( ! empty( $previous ) ? trailingslashit( $previous ) : '' ) . basename( $path );
		$cleanup = shipper_glob_all( $path );
		$status  = true;
		foreach ( $cleanup as $file ) {
			if ( is_dir( $file ) ) {
				if ( ! $this->clean_dir( $file, $next ) ) {
					$this->add_error(
						self::ERR_ACCESS,
						/* translators: %s: sub directory name. */
						sprintf( __( 'Unable to clean up subdirectory: %s', 'shipper' ), $next )
					);
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
				if ( ! rmdir( $file ) ) {
					$this->add_error(
						self::ERR_ACCESS,
						/* translators: %s: sub directory name. */
						sprintf( __( 'Unable to remove subdirectory: %s', 'shipper' ), $next )
					);
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			} else {
				if ( ! is_writable( $file ) ) {
					$this->add_error(
						self::ERR_ACCESS,
						/* translators: %s: temp directory name. */
						sprintf( __( 'Unable to clean up local temp directory file: %s', 'shipper' ), $file )
					);
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}

				// Alright, drop it like it's hot.
				if ( ! shipper_delete_file( $file ) ) {
					$this->add_error(
						self::ERR_ACCESS,
						/* translators: %s: sub directory name. */
						sprintf( __( 'Error cleaning up local temp directory: %s', 'shipper' ), $file )
					);
					$status = false;
					continue; // Let's not break on errors here, keep on cleaning as much as we can.
				}
			}
		}

		return $status;
	}
}