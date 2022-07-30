<?php
/**
 * Shipper template helpers: sorting helper
 *
 * @since v1.0.3
 * @package shipper
 */

/**
 * Handles sorting in templates
 */
class Shipper_Helper_Template_Sorter {

	const WP_TABLES    = 'wp_tables';
	const NONWP_TABLES = 'nonwp_tables';
	const OTHER_TABLES = 'other_tables';

	/**
	 * Gets all tables, grouped by their origin
	 *
	 * @param \Shipper_Model_Stored|null $meta Shipper_Model_Stored instance holder.
	 *
	 * @return array
	 */
	public static function get_grouped_tables( Shipper_Model_Stored $meta = null ) {
		$model = new Shipper_Model_Database();

		if ( ! is_object( $meta ) ) {
			$meta = new Shipper_Model_Stored_MigrationMeta();
		}

		$base_prefix = preg_quote( $model->get_dbh()->base_prefix, '/' );
		$prefix      = $base_prefix;
		if ( is_multisite() && $meta->get_mode() === 'subsite' && $meta->get_site_id() !== 1 ) {
			$site_id = $meta->get_site_id();
			$prefix  = $base_prefix . $site_id . '_';
		}

		$result = array(
			self::WP_TABLES    => array(),
			self::NONWP_TABLES => array(),
			self::OTHER_TABLES => array(),
		);

		if ( ! is_multisite() ) {
			self::categorize_tables( $prefix, $result, $model );
		} else {
			self::categorize_tables_ms( $prefix, $base_prefix, $result, $model, $meta );
		}

		return $result;
	}

	/**
	 * Categorize tables as core, no core, and other tables for non-multi sites
	 *
	 * @param string                 $prefix database prefix.
	 * @param array                  $result an array of result.
	 * @param Shipper_Model_Database $model  database instance.
	 */
	private static function categorize_tables( $prefix, &$result, Shipper_Model_Database $model ) {
		$default_wp_tables = self::default_wp_tables();
		foreach ( $model->get_tables_list() as $table ) {
			if ( ! preg_match( "/^{$prefix}/", $table ) ) {
				$result[ self::OTHER_TABLES ][] = $table;
				continue;
			}

			$key = self::NONWP_TABLES;
			foreach ( $default_wp_tables as $dtbl ) {
				if ( ! preg_match( "/^{$prefix}(\d+)?_?{$dtbl}$/", $table ) ) {
					continue;
				}
				$key = self::WP_TABLES;
				break;
			}

			$result[ $key ][] = $table;
		}
	}

	/**
	 * Categorize tables as core, no core, and other tables for multi sites
	 *
	 * @param string                 $prefix      db prefix.
	 * @param string                 $base_prefix db base prefix.
	 * @param array                  $result      array of result.
	 * @param Shipper_Model_Database $model       Shipper_Model_Database instance.
	 * @param Shipper_Model_Stored   $meta        Shipper_Model_Stored instance.
	 */
	private static function categorize_tables_ms( $prefix, $base_prefix, &$result, Shipper_Model_Database $model, Shipper_Model_Stored $meta ) {
		$default_wp_tables = self::default_wp_tables();

		foreach ( $model->get_tables_list() as $table ) {
			if ( ! preg_match( "/^{$base_prefix}(\d+)?_?/", $table ) ) {
				// if the table doesn't come in WordPress table format, mark as other.
				$result[ self::OTHER_TABLES ][] = $table;
				continue;
			}
			$key = self::OTHER_TABLES;
			if ( $meta->get_mode() === 'whole_network' ) {
				$dtbl = preg_replace( "/(^{$base_prefix}(\d+_)?)/", '', $table );
				if ( in_array( $dtbl, $default_wp_tables, true ) ) {
					$key = self::WP_TABLES;
				} elseif ( preg_match( "/(^{$base_prefix}(\d+_)?)/", $table ) ) {
					// if this match prefix but not core, mark as NONWP.
					$key = self::NONWP_TABLES;
				}
			} else {
				if ( $meta->get_site_id() !== 1 ) {
					// no need to check users and usermeta, as they always in core.
					unset( $default_wp_tables['users'] );
					unset( $default_wp_tables['usermeta'] );
				}
				$default_single_tables = self::default_wp_tables( false );
				// we have to check if this is subsite scenario, we wont include the main wp tables.
				$key  = self::NONWP_TABLES;
				$rtbl = str_replace( $prefix, '', $table );
				if ( in_array( $rtbl, $default_single_tables, true ) ) {
					// this mean this is core.
					$key = self::WP_TABLES;
				} else {
					// we will remove tables that is core, which belong to other subsites.
					foreach ( $default_wp_tables as $dtbl ) {
						if ( preg_match( "/{$dtbl}$/", $table ) ) {
							// this one is on core of other subsite, exclude it.
							$key = null;
							break;
						}
					}

					if (
						self::NONWP_TABLES === $key
						&& preg_match( "/{$base_prefix}((\d+_))/", $table )
						&& ( ! preg_match( "/{$base_prefix}{$meta->get_site_id()}_/", $table ) && $meta->get_site_id() !== 1 )
					) {
						// if the current table match other prefix except this, then it is custom table belong to other core.
						// and we dont need it.
						// no need to check the base prefix as it already excluded by the upper filter.
						$key = null;
					}
				}
			}

			if ( null !== $key ) {
				$result[ $key ][] = $table;
			}
		}

		// move the users and usermeta into core if not set.
		if ( ! in_array( $base_prefix . 'users', $result[ self::WP_TABLES ], true ) ) {
			$result[ self::WP_TABLES ][] = $base_prefix . 'users';
		}

		if ( ! in_array( $base_prefix . 'usermeta', $result[ self::WP_TABLES ], true ) ) {
			$result[ self::WP_TABLES ][] = $base_prefix . 'usermeta';
		}

		/**
		 * Move sitemeta into core if not set.
		 *
		 * @see https://incsub.atlassian.net/browse/SHI-248
		 * @since 1.2.8
		 */
		if ( ! in_array( $base_prefix . 'sitemeta', $result[ self::WP_TABLES ], true ) ) {
			$result[ self::WP_TABLES ][] = $base_prefix . 'sitemeta';
		}
	}

	/**
	 * Gets sorted task checks
	 *
	 * Wraps the `checks_by_error_status`.
	 *
	 * @param Shipper_Task_Check $task Shipper_Task_Check instance.
	 *
	 * @return array Sorted checks
	 * @since v1.1
	 */
	public static function get_sorted_checks( Shipper_Task_Check $task ) {
		$raw = $task->get_checks();
		if ( is_callable( array( $task, 'get_package_size_check' ) ) ) {
			$raw[] = $task->get_package_size_check();
		}
		$checks = array();

		foreach ( $raw as $check ) {
			$checks[] = $check->get_data();
		}

		return self::checks_by_error_status( $checks );
	}

	/**
	 * Sorts checks by their error status
	 *
	 * Errors first, next warnings, success last
	 *
	 * @param array $checks A list of checks data hashes.
	 *
	 * @return array Sorted checks
	 */
	public static function checks_by_error_status( $checks ) {
		if ( ! is_array( $checks ) || empty( $checks ) ) {
			return array();
		}

		$errors   = array();
		$warnings = array();
		$success  = array();

		foreach ( $checks as $check ) {
			if ( Shipper_Model_Check::STATUS_ERROR === $check['status'] ) {
				$errors[] = $check;
			} elseif ( Shipper_Model_Check::STATUS_WARNING === $check['status'] ) {
				$warnings[] = $check;
			} elseif ( Shipper_Model_Check::STATUS_OK === $check['status'] ) {
				$success[] = $check;
			}
		}

		return array_merge(
			$errors,
			$warnings,
			$success
		);
	}

	/**
	 * Shorthand to get all the tables belong to single wp
	 *
	 * @param bool $all whether to return all the table or not.
	 *
	 * @return array
	 */
	private static function default_wp_tables( $all = true ) {
		$default_wp_tables = array(
			'commentmeta',
			'comments',
			'links',
			'options',
			'postmeta',
			'posts',
			'terms',
			'termmeta',
			'term_relationships',
			'term_taxonomy',
			'usermeta',
			'users',
			'registration_log',
			'signups',
			'users',
			'usermeta',
		);
		$default_ms_tables = array(
			'blogs',
			'blogmeta',
			'blog_versions',
			'site',
			'sitemeta',
		);
		if ( $all ) {
			$default_wp_tables = array_merge( $default_wp_tables, $default_ms_tables );
		}

		return $default_wp_tables;
	}
}