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

	const WP_TABLES = 'wp_tables';
	const NONWP_TABLES = 'nonwp_tables';
	const OTHER_TABLES = 'other_tables';

	/**
	 * Gets all tables, grouped by their origin
	 *
	 * @return array
	 */
	static public function get_grouped_tables() {
		$model = new Shipper_Model_Database;
		$prefix = preg_quote( $model->get_dbh()->base_prefix, '/' );
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
			'blogs',
			'blog_versions',
			'registration_log',
			'signups',
			'site',
			'sitemeta',
			'users',
			'usermeta',
		);
		$result = array(
			self::WP_TABLES => array(),
			self::NONWP_TABLES => array(),
			self::OTHER_TABLES => array(),
		);

		foreach ( $model->get_tables_list() as $table ) {
			if ( ! preg_match( "/^{$prefix}/", $table ) ) {
				$result[ self::OTHER_TABLES ][] = $table;
				continue;
			}

			$key = self::NONWP_TABLES;
			foreach ( $default_wp_tables as $dtbl ) {
				if ( ! preg_match( "/^{$prefix}(\d+)?{$dtbl}$/", $table ) ) {
					continue;
				}
				$key = self::WP_TABLES;
				break;
			}

			$result[ $key ][] = $table;
		}


		return $result;
	}

	/**
	 * Gets sorted task checks
	 *
	 * Wraps the `checks_by_error_status`.
	 *
	 * @since v1.1
	 *
	 * @param object Shipper_Task_Check instance.
	 *
	 * @return array Sorted checks
	 */
	static public function get_sorted_checks( Shipper_Task_Check $task ) {
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
	static public function checks_by_error_status( $checks ) {
		if ( ! is_array( $checks ) || empty( $checks ) ) {
			return array();
		}

		$errors = array();
		$warnings = array();
		$success = array();

		foreach( $checks as $check ) {
			if ( Shipper_Model_Check::STATUS_ERROR === $check['status'] ) {
				$errors[] = $check;
			} else if ( Shipper_Model_Check::STATUS_WARNING === $check['status'] ) {
				$warnings[] = $check;
			} else if ( Shipper_Model_Check::STATUS_OK === $check['status'] ) {
				$success[] = $check;
			}
		}

		return array_merge(
			$errors,
			$warnings,
			$success
		);
	}
}