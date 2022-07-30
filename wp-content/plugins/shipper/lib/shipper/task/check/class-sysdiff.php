<?php
/**
 * Shipper tasks: Systems difference check
 *
 * Checks the other system for possibly offending differences.
 *
 * @package shipper
 */

/**
 * Systems differences check class
 */
class Shipper_Task_Check_Sysdiff extends Shipper_Task_Check {

	const ERR_BLOCKING = 'issue_blocking';
	const ERR_WARNING  = 'issue_warning';

	/**
	 * Runs the diff checks suite.
	 *
	 * @param array $remote Other system info, as created by Shipper_Model_System::get_data on the other end.
	 *
	 * @return bool
	 */
	public function apply( $remote = array() ) {

		if ( empty( $remote ) ) {
			$this->add_error(
				self::ERR_BLOCKING,
				__( 'No remote data to process', 'shipper' )
			);

			return false;
		}

		if ( ! isset( $remote['wordpress'][ Shipper_Model_System_Wp::SHIPPER_VERSION ] ) ) {
			$remote['wordpress'][ Shipper_Model_System_Wp::SHIPPER_VERSION ] = '1.0.3';
		}

		$model = new Shipper_Model_System();
		$local = $model->get_data();

		if ( is_multisite() || Shipper_Helper_MS::can_ms_subsite_import() ) {
			/**
			 * If this is multiste, we will need to unset the check of multisite
			 */
			$meta = new Shipper_Model_Stored_MigrationMeta();
			if ( 'subsite' === $meta->get_mode() ) {
				unset( $remote['wordpress']['multisite'] );
				unset( $remote['wordpress']['MULTISITE'] );
				unset( $remote['wordpress']['subdomain_install'] );
				unset( $remote['wordpress']['SUBDOMAIN_INSTALL'] );
			}
		}
		foreach ( $remote as $section => $info ) {
			if ( ! is_array( $info ) ) {
				$this->add_error(
					self::ERR_BLOCKING,
					/* translators: %s: section name.*/
					sprintf( __( 'Invalid remote data for section %s', 'shipper' ), $section )
				);

				return false;
			}
			if ( ! isset( $local[ $section ] ) ) {
				$this->add_error(
					self::ERR_BLOCKING,
					/* translators: %s: section name.*/
					sprintf( __( 'Invalid local data for section %s', 'shipper' ), $section )
				);

				return false;
			}
			foreach ( $info as $key => $value ) {
				if ( ! isset( $local[ $section ][ $key ] ) ) {
					$this->add_error(
						self::ERR_BLOCKING,
						sprintf(
							/* translators: %1$s %2$s: section and key name.*/
							__( 'Invalid local data for section %1$s, key %2$s', 'shipper' ),
							$section,
							$key
						)
					);

					return false;
				}

				$check  = strtolower( "{$section}_{$key}" );
				$method = "is_{$check}_diff_acceptable";

				if ( is_callable( array( $this, $method ) ) ) {
					$check = call_user_func(
						array( $this, $method ),
						$value,
						$local[ $section ][ $key ]
					);
					$check->set( 'check_id', md5( get_class( $this ) . $method ) );
					$this->add_check( $check );
				}

				if ( shipper_has_error( self::ERR_BLOCKING, $this->get_errors() ) ) {
					// We encountered a blocking issue, no need to check further.
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks for significant PHP major version differences
	 *
	 * @param int $remote Remote PHP major version.
	 * @param int $local Local PHP major version.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_php_version_major_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'PHP version is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $remote !== $local ) {
			$status    = Shipper_Model_Check::STATUS_WARNING;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'PHP Version Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/php-difference',
					array(
						'local'       => $this->get_normalized_version( $local ),
						'remote'      => $this->get_normalized_version( $remote ),
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks for significant MySQL version differences
	 *
	 * @param string $remote Remote MySQL version string.
	 * @param string $local Local MySQL version string.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_mysql_version_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'MySQL version is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( version_compare( $this->get_normalized_version( $remote ), $this->get_normalized_version( $local ), 'ne' ) ) {
			$status    = Shipper_Model_Check::STATUS_WARNING;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'MySQL Version Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/mysql-difference',
					array(
						'local'       => $local,
						'remote'      => $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks for MySQL charset differences
	 *
	 * @param string $remote Remote MySQL charset string.
	 * @param string $local Local MySQL charset string.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_mysql_charset_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'Database Charset is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( strtolower( $remote ) !== strtolower( $local ) ) {
			$status    = Shipper_Model_Check::STATUS_WARNING;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Database Charset Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/charset-difference',
					array(
						'local'       => strtoupper( $local ),
						'remote'      => strtoupper( $remote ),
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks for significant server type differences.
	 *
	 * @param string $remote Remote server type.
	 * @param string $local Local server type.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_type_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'Server Type is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( $remote !== $local ) {
			$status    = Shipper_Model_Check::STATUS_WARNING;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Server Type Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/servertype-difference',
					array(
						'local'       => $local,
						'remote'      => $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks server operating systems for significant differences
	 *
	 * @param string $remote Remote operating system.
	 * @param string $local Local operating system.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_server_os_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'Server Operating System is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( strtolower( $remote ) !== strtolower( $local ) ) {
			$status    = Shipper_Model_Check::STATUS_WARNING;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Server Operating System Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/serveros-difference',
					array(
						'local'       => $local,
						'remote'      => $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if multisite setups are compatible
	 *
	 * @param bool $remote Whether remote WP is a network install.
	 * @param bool $local Whether local WP is a network install.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_wordpress_multisite_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'Installation type is compatible', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;

		if ( (int) $remote !== (int) $local ) {
			$status    = Shipper_Model_Check::STATUS_ERROR;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Installation Type Difference - Single Site / Multisite', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/multisite-difference',
					array(
						'local'       => (int) $local,
						'remote'      => (int) $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Checks if multisite subdomain setups are compatible
	 *
	 * @param bool $remote Whether remote WP is a subdomain multisite install.
	 * @param bool $local Whether local WP is a subdomain multisite install.
	 *
	 * @return object Shipper_Model_Check instance
	 */
	public function is_wordpress_subdomain_install_diff_acceptable( $remote, $local ) {
		$check    = new Shipper_Model_Check( __( 'Multisite Address is compatible', 'shipper' ) );
		$status   = Shipper_Model_Check::STATUS_OK;
		$do_check = is_multisite();

		if ( $do_check && (int) $remote !== (int) $local ) {
			$status    = Shipper_Model_Check::STATUS_ERROR;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Multisite Address Type Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/subdomain-difference',
					array(
						'local'       => (int) $local,
						'remote'      => (int) $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Is WordPress shipper version is diff acceptable
	 *
	 * @param bool $remote is remote.
	 * @param bool $local is local.
	 *
	 * @return object|\Shipper_Model_Check
	 */
	public function is_wordpress_shipper_version_diff_acceptable( $remote, $local ) {
		$check  = new Shipper_Model_Check( __( 'Shipper version is compatibility', 'shipper' ) );
		$status = Shipper_Model_Check::STATUS_OK;
		if ( false === (bool) $remote || $remote !== $local ) {
			$status    = Shipper_Model_Check::STATUS_ERROR;
			$migration = new Shipper_Model_Stored_Migration();
			$tpl       = new Shipper_Helper_Template();
			$check->set( 'title', __( 'Shipper Version Difference', 'shipper' ) );
			$check->set(
				'message',
				$tpl->get(
					'checks/shipper-difference',
					array(
						'local'       => $local,
						'remote'      => $remote,
						'source'      => $migration->get_source(),
						'destination' => $migration->get_destination(),
					)
				)
			);
		}

		return $check->complete( $status );
	}

	/**
	 * Normalizes version strings to point-separated integers
	 *
	 * @param string $version Raw version to normalize.
	 * @param int    $precision Optional number of point-separated values.
	 *
	 * @return string
	 */
	public function get_normalized_version( $version, $precision = 2 ) {
		$ver = array_map( 'intval', explode( '.', $version, $precision + 1 ) );
		if ( count( $ver ) > $precision ) {
			array_pop( $ver );
		}

		return join( '.', $ver );
	}
}