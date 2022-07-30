<?php
/**
 * Shipper package controllers: package advanced overrides.
 *
 * Responsible for excluding:
 *  - post revisions
 *  - spam comments
 *  - inactive plugins
 *  - inactive themes
 *
 * @since v1.1
 * @package shipper
 */

/**
 * Package advanced overrides implementation class
 */
class Shipper_Controller_Override_Package_Advanced
	extends Shipper_Controller_Override_Package {

	/**
	 * Get scope.
	 *
	 * @return string
	 */
	public function get_scope() {
		return Shipper_Model_Stored_PackageMeta::KEY_EXCLUSIONS_XX;
	}

	/**
	 * Apply overrides.
	 *
	 * @return false
	 */
	public function apply_overrides() {
		$exclusions = $this->get_exclusions();
		if ( empty( $exclusions ) ) {
			return false;
		}

		if ( in_array( 'post-revisions', $exclusions, true ) ) {
			add_filter(
				'shipper_export_table_include_row',
				array( $this, 'exclude_post_revisions' ),
				10,
				3
			);
		}

		if ( in_array( 'spam-comments', $exclusions, true ) ) {
			add_filter(
				'shipper_export_table_include_row',
				array( $this, 'exclude_spam_comments' ),
				10,
				3
			);
		}

		if ( in_array( 'inactive-themes', $exclusions, true ) ) {
			add_filter(
				'shipper_path_include_file',
				array( $this, 'exclude_inactive_theme_file' ),
				10,
				2
			);
		}

		if ( in_array( 'inactive-plugins', $exclusions, true ) ) {
			add_filter(
				'shipper_path_include_file',
				array( $this, 'exclude_inactive_plugin_file' ),
				10,
				2
			);
		}
	}

	/**
	 * Excludes the files beloging to inactive plugins
	 *
	 * @param bool   $include Whether to include a file.
	 * @param string $path Path to file.
	 *
	 * @return bool
	 */
	public function exclude_inactive_plugin_file( $include, $path ) {
		if ( empty( $include ) ) {
			return $include; }

		$plugins_path = Shipper_Helper_Fs_Path::get_relpath( WP_PLUGIN_DIR );
		if ( ! (bool) stristr( $path, $plugins_path ) ) {
			return $include; }

		if ( wp_normalize_path( WP_PLUGIN_DIR ) === wp_normalize_path( $path ) ) {
			return true;
		}

		$relpath = Shipper_Helper_Fs_Path::get_relpath( $path, WP_PLUGIN_DIR );
		$include = false;
		foreach ( $this->get_active_plugins() as $plugin ) {
			if ( empty( $plugin['directory'] ) ) {
				$pluginfile = trailingslashit( WP_PLUGIN_DIR ) . $plugin['filename'];
				if ( $path === $pluginfile ) {
					$include = true;
				}
			} else {
				$paths   = array_filter( explode( '/', $relpath ) );
				$dirname = $paths[0];
				if ( $dirname === $plugin['directory'] ) {
					$include = true;
				}
			}
			if ( $include ) {
				break;
			}
		}

		return $include;
	}

	/**
	 * Gets a list of parsed active plugin hashes
	 *
	 * Hashes contain directory and filename parts of the plugin.
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		if ( empty( $this->active_plugins ) ) {
			$result = array();
			foreach ( get_option( 'active_plugins' ) as $plugin ) {
				$dirname = dirname( $plugin );
				if ( '.' === $dirname ) {
					$dirname = ''; }
				$result[] = array(
					'directory' => $dirname,
					'filename'  => basename( $plugin ),
				);
			}
			$this->active_plugins = $result;
		}
		return $this->active_plugins;
	}

	/**
	 * Excludes the files beloging to inactive themes
	 *
	 * @param bool   $include Whether to include a file.
	 * @param string $path Path to file.
	 *
	 * @return bool
	 */
	public function exclude_inactive_theme_file( $include, $path ) {
		if ( empty( $include ) ) {
			return $include; }

		$themes_path = get_theme_root();
		if ( wp_normalize_path( $themes_path ) === wp_normalize_path( $path ) ) {
			return $include;
		}

		$themes_path = Shipper_Helper_Fs_Path::get_relpath( $themes_path );
		if ( ! (bool) stristr( $path, $themes_path ) ) {
			return $include; }

		// Now we know this is a theme file. Is it active?
		return (bool) stristr( $path, get_template_directory() ) ||
			(bool) stristr( $path, get_stylesheet_directory() );
	}

	/**
	 * Excludes post revisions from the migrated dataset
	 *
	 * @param bool   $include Whether to include this row.
	 * @param array  $row Raw row hash.
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public function exclude_post_revisions( $include, $row, $table ) {
		if ( empty( $include ) ) {
			return $include; }

		$is_post_row = shipper_array_keys_exist(
			array(
				'ID',
				'post_author',
				'post_status',
				'post_title',
				'post_type',
				'post_content',
			),
			$row
		) && strrpos( $table, 'posts' );
		if ( ! $is_post_row ) {
			return $include; }

		return 'revision' !== $row['post_type'];
	}

	/**
	 * Excludes spam comments from the migrated dataset
	 *
	 * @param bool   $include Whether to include this row.
	 * @param array  $row Raw row hash.
	 * @param string $table Table name.
	 *
	 * @return bool
	 */
	public function exclude_spam_comments( $include, $row, $table ) {
		if ( empty( $include ) ) {
			return $include; }

		$is_post_row = shipper_array_keys_exist(
			array(
				'comment_ID',
				'comment_post_ID',
				'comment_author',
				'comment_date',
				'comment_content',
				'comment_approved',
			),
			$row
		) && strrpos( $table, 'comments' );
		if ( ! $is_post_row ) {
			return $include; }

		return 'spam' !== $row['comment_approved'];
	}
}