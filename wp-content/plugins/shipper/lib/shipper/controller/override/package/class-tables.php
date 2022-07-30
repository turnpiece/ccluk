<?php
/**
 * Shipper package controllers: package tables overrides.
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package table overrides implementation class
 */
class Shipper_Controller_Override_Package_Tables extends Shipper_Controller_Override_Package {

	/**
	 * Get scope.
	 *
	 * @return string
	 */
	public function get_scope() {
		return Shipper_Model_Stored_PackageMeta::KEY_EXCLUSIONS_DB;
	}

	/**
	 * Apply overrides.
	 *
	 * @return void
	 */
	public function apply_overrides() {
		if ( ! empty( $this->get_exclusions() ) ) {
			add_filter( 'shipper_exclude_tables', array( $this, 'exclude_table_list' ) );
		}

		if ( $this->get_model()->is_extract_mode() ) {
			add_filter( 'shipper_export_table_exclude_row', array( $this, 'maybe_exclude_user' ), 11, 3 );

			/**
			 * On sub-site migration, network wide plugins won't be in `active_plugins` list.
			 * So add those plugins to `active_plugins` list.
			 * Same goes for themes.
			 *
			 * @since 1.2.4
			 */
			add_action( 'shipper_before_dump_table_for_package_migration', array( $this, 'transform_table' ) );
		}

		add_filter( 'shipper_export_table_exclude_transient', '__return_false' );
	}

	/**
	 * We'll check and exclude any users that doesn't belong to a subsite
	 *
	 * @param bool   $exclude whether to exclude or not.
	 * @param string $row table row.
	 * @param string $table table name.
	 *
	 * @return bool
	 */
	public function maybe_exclude_user( $exclude, $row, $table ) {
		global $wpdb;
		$meta = new Shipper_Model_Stored_PackageMeta();
		if ( $table === $wpdb->users ) {
			if ( ! is_user_member_of_blog( $row['ID'], $meta->get_site_id() ) ) {
				return true;
			}
		}

		if ( $table === $wpdb->usermeta ) {
			if ( ! is_user_member_of_blog( $row['user_id'], $meta->get_site_id() ) ) {
				return true;
			}
		}

		return $exclude;
	}

	/**
	 * Excludes tables according to package settings
	 *
	 * @return array
	 */
	public function exclude_table_list() {
		return $this->get_exclusions();
	}

	/**
	 * Transform tables
	 *
	 * @since 1.2.4
	 *
	 * @param Shipper_Helper_Dumper_Php $dumper Dumber Object.
	 */
	public function transform_table( $dumper ) {
		global $wpdb;
		$current_site_id = $this->get_model()->get_site_id();
		$prefix          = $wpdb->prefix . $current_site_id . '_';
		$site_info       = Shipper_Helper_MS::get_site_info( $current_site_id );

		$dumper->set_transform_table_row_hook(
			function( $table, $row ) use ( $prefix, $site_info ) {
				if ( $table === $prefix . 'options' ) {
					if ( 'active_plugins' === $row['option_name'] ) {
						$row['option_value'] = serialize( $site_info['plugins'] ); // phpcs:ignore
					}

					if ( 'template' === $row['option_name'] ) {
						$row['option_value'] = $site_info['template'];
					}

					if ( 'stylesheet' === $row['option_name'] ) {
						$row['option_value'] = $site_info['stylesheet'];
					}
				}

				return $row;
			}
		);
	}
}