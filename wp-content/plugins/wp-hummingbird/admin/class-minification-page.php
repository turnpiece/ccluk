<?php

/**
 * Class WP_Hummingbird_Minification_Page
 */
class WP_Hummingbird_Minification_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Display mode.
	 *
	 * @since 1.7.1
	 * @var string $mode  Default: 'basic'. Possible: 'advanced', 'basic.
	 */
	public $mode = 'basic';

	/**
	 * Function triggered when the page is loaded before render any content.
	 */
	public function on_load() {
		$this->setup_navigation();

		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );

		if ( ! $minify_module->is_active() ) {
			return;
		}

		if ( ! $minify_module->scanner->is_scanning() ) {
			$minify_module->scanner->finish_scan();
		}

		$options = $minify_module->get_options();
		// If backed up settings exist apply to the files that are still present.
		if ( isset( $options['backed_up_settings'] ) && ! $minify_module->scanner->is_scanning() ) {
			$minify_module->merge_backed_up_settings();

		}

		$redirect = false;
		$redirect_url = WP_Hummingbird_Utils::get_admin_menu_url( 'minification' );

		// Re-check files button clicked.
		if ( isset( $_POST['recheck-files'] ) || isset( $_GET['recheck-files'] ) ) { // Input var ok.
			// Remove notice.
			if ( isset( $_GET['recheck-files'] ) ) { // Input var ok.
				delete_option( 'wphb-notice-cache-cleaned-show' );
			}

			// We want to backup the current settings.
			$minify_module->backup_settings();

			$minify_module->clear_cache();
			// Activate minification if is not.
			$minify_module->toggle_service( true );
			$minify_module->scanner->init_scan();
			$redirect = true;
		}

		// Clear cache button clicked.
		if ( isset( $_POST['clear-cache'] ) ) { // Input var okay.
			WP_Hummingbird_Utils::get_module( 'minify' )->clear_cache( false );
		}

		// Clear cache button click from notice.
		if ( isset( $_GET['clear-cache'] ) ) { // Input var okay.
			// Remove notice.
			delete_option( 'wphb-notice-cache-cleaned-show' );

			// Clear page caching if set.
			if ( isset( $_GET['clear-pc'] ) ) { // Input var okay.
				WP_Hummingbird_Utils::get_module( 'page_cache' )->clear_cache();
			}

			$minify_module->clear_cache( false );

			// Add clear cache notice.
			if ( isset( $_GET['clear-cache'] ) ) { // Input var ok.
				$redirect_url = add_query_arg( 'wphb-cache-cleared', 'true', $redirect_url );
			}
			$redirect = true;
		}

		// Reset to defaults button clicked on settings page.
		if ( isset( $_GET['reset'] ) ) { // Input var okay.
			check_admin_referer( 'wphb-reset-minification' );
			$minify_module->reset();
			$redirect = true;
		}

		// Disable clicked on settings page.
		if ( isset( $_GET['disable'] ) ) { // Input var okay.
			check_admin_referer( 'wphb-disable-minification' );
			$minify_module->disable();
			$redirect = true;
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Set up naviagation for module.
	 *
	 * @since 1.8.2
	 */
	private function setup_navigation() {
		$this->tabs = array(
			'files'    => __( 'Assets', 'wphb' ),
			'tools'    => __( 'Tools', 'wphb' ),
			'settings' => __( 'Settings', 'wphb' ),
		);

		// Remove modules that are not used on subsites in a network.
		if ( is_multisite() && ! is_network_admin() ) {
			unset( $this->tabs['tools'] );
		}

		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Render the template header.
	 */
	public function render_header() {
		// Asset Optimization publish changes.
		if ( isset( $_POST['submit'] ) ) { // Input var okay.
			check_admin_referer( 'wphb-enqueued-files' );

			/* @var WP_Hummingbird_Module_Minify $minify_module */
			$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
			$options = $minify_module->get_options();

			$options = $this->_sanitize_type( 'styles', $options );
			$options = $this->_sanitize_type( 'scripts', $options );

			$minify_module->update_options( $options );

			// Remove notice.
			delete_site_option( 'wphb-notice-minification-optimized-show' );

			$this->admin_notices->show(
				'updated',
				__( '<strong>Your changes have been published.</strong> Note: Files queued for compression will generate once someone visits your homepage.', 'wphb' ),
				'success'
			);
		}

		// Clear cache show notice (from clear cache button and clear cache notice).
		if ( isset( $_POST['clear-cache'] ) || isset( $_GET['wphb-cache-cleared'] ) ) { // Input var ok.
			$this->admin_notices->show(
				'updated',
				__( 'Your cache has been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ),
				'success'
			);
		}

		if ( isset( $_GET['wphb-cache-cleared-with-cloudflare'] ) ) { // Input var ok.
			$this->admin_notices->show(
				'updated',
				__( 'Your local and Cloudflare caches have been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ),
				'success'
			);
		}
		?>
		<div class="sui-notice-top sui-notice-success sui-hidden" id="wphb-notice-minification-advanced-settings-updated">
			<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
		</div>

		<?php
		parent::render_header();
	}

	/**
	 * Register meta boxes.
	 */
	public function register_meta_boxes() {
		/**
		 * Disabled state meta box.
		 *
		 * @var WP_Hummingbird_Module_Minify $minify_module
		 */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		if ( ! $minify_module->is_active() || $minify_module->scanner->is_scanning() ) {
			$this->add_meta_box(
				'minification/empty-files',
				__( 'Get Started', 'wphb' ),
				null,
				null,
				null,
				'box-enqueued-files-empty',
				array(
					'box_content_class' => 'sui-box-body sui-block-content-center',
				)
			);

			return;
		}

		// Move it here from __construct so we don't make an extra db call if minification is disabled.
		$this->mode = WP_Hummingbird_Settings::get_setting( 'view', 'minify' );

		/**
		 * Summary meta box.
		 */
		$this->add_meta_box(
			'minification/summary-meta-box',
			null,
			array( $this, 'summary_metabox' ),
			null,
			null,
			'summary',
			array(
				'box_class' => false,
				'box_content_class' => 'sui-box sui-summary',
			)
		);

		/**
		 * Files meta box.
		 */
		$this->add_meta_box(
			'minification/enqueued-files',
			__( 'Assets', 'wphb' ),
			array( $this, 'enqueued_files_metabox' ),
			array( $this, 'eunqeued_files_metabox_header' ),
			null,
			'main',
			array(
				'box_header_class'  => 'sui-box-header box-title-' . $this->mode,
				'box_content_class' => 'no-padding',
			)
		);

		/**
		 * Tools meta box.
		 */
		$this->add_meta_box(
			'minification/tools',
			__( 'Tools', 'wphb' ),
			array( $this, 'tools_metabox' ),
			null,
			null,
			'tools'
		);

		/**
		 * Settings meta box.
		 */
		$this->add_meta_box(
			'minification/settings',
			__( 'Settings', 'wphb' ),
			array( $this, 'settings_metabox' ),
			null,
			null,
			'settings',
			array(
				'box_content_class'  => WP_Hummingbird_Utils::is_member() ? 'sui-box-body' : 'sui-box-body sui-upsell-items',
			)
		);
	}

	/**
	 * *************************
	 * Summary and empty states
	 ***************************/

	/**
	 * Summary meta box.
	 */
	function summary_metabox() {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = WP_Hummingbird_Utils::get_module( 'minify' );
		$collection = $minify_module->get_resources_collection();

		// Remove those assets that we don't want to display.
		foreach ( $collection['styles'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
				unset( $collection['styles'][ $key ] );
			}
		}
		foreach ( $collection['scripts'] as $key => $item ) {
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
				unset( $collection['scripts'][ $key ] );
			}
		}

		$enqueued_files = count( $collection['scripts'] ) + count( $collection['styles'] );

		$original_size_styles = array_sum( @wp_list_pluck( $collection['styles'], 'original_size' ) );
		$original_size_scripts = array_sum( @wp_list_pluck( $collection['scripts'], 'original_size' ) );
		$original_size = $original_size_scripts + $original_size_styles;

		$compressed_size_styles = array_sum( @wp_list_pluck( $collection['styles'], 'compressed_size' ) );
		$compressed_size_scripts = array_sum( @wp_list_pluck( $collection['scripts'], 'compressed_size' ) );
		$compressed_size = $compressed_size_scripts + $compressed_size_styles;

		if ( (float) $original_size <= 0 ) {
			$percentage = 0;
		} else {
			$percentage = 100 - (int) $compressed_size * 100 / (int) $original_size;
		}
		$percentage = number_format_i18n( $percentage, 1 );
		$compressed_size = number_format( (float) $original_size - (float) $compressed_size, 0 );

		$use_cdn = $minify_module->get_cdn_status();
		$is_member = WP_Hummingbird_Utils::is_member();

		$args = compact( 'enqueued_files', 'compressed_size', 'percentage', 'use_cdn', 'is_member' );
		$this->view( 'minification/summary-meta-box', $args );
	}

	/**
	 * *************************
	 * Asset Optimization basic/advanced
	 ***************************/

	/**
	 * Enqueued files meta box.
	 *
	 * @since 1.7.1
	 */
	public function enqueued_files_metabox() {
		/* @var WP_Hummingbird_Module_Minify $module */
		$module = WP_Hummingbird_Utils::get_module( 'minify' );
		$collection = $module->get_resources_collection();

		// Prepare filters.
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			foreach ( get_site_option( 'active_sitewide_plugins', array() ) as $plugin => $item ) {
				$active_plugins[] = $plugin;
			}
		}
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		$selector_filter = array();
		$selector_filter[ $theme_name ] = $theme_name;
		foreach ( $active_plugins as $plugin ) {
			if ( ! is_file( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				continue;
			}
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			if ( $plugin_data['Name'] ) {
				// Found plugin, add it as a filter.
				$selector_filter[ $plugin_data['Name'] ] = $plugin_data['Name'];
			}
		}
		$styles_rows = $this->_collection_rows( $collection['styles'], 'styles', $this->mode );
		$scripts_rows = $this->_collection_rows( $collection['scripts'], 'scripts', $this->mode );
		$others_rows = $styles_rows['other'];
		$others_rows .= $scripts_rows['other'];

		if ( isset( $_GET['view-export-form'] ) ) { // Input var ok.
			$this->view( 'minification/export-form' );
		}

		$this->view( 'minification/enqueued-files-meta-box', array(
			'type'            => $this->mode,
			'styles_rows'     => $styles_rows['content'],
			'scripts_rows'    => $scripts_rows['content'],
			'others_rows'     => $others_rows,
			'selector_filter' => $selector_filter,
			'is_server_error' => $module->errors_controller->is_server_error(),
			'server_errors'   => $module->errors_controller->get_server_errors(),
			'error_time_left' => $module->errors_controller->server_error_time_left(),
			'is_http2'        => is_ssl() && WP_Hummingbird_Utils::get_http2_status(),
		) );
	}

	/**
	 * Tools meta box.
	 *
	 * @since 1.8
	 */
	public function tools_metabox() {
		$this->view( 'minification/tools-meta-box', array(
			'css' => WP_Hummingbird_Module_Minify::get_css(),
		));
	}

	/**
	 * Settings meta box.
	 *
	 * @since 1.9
	 */
	public function settings_metabox() {
		$this->view( 'minification/settings-meta-box', array(
			'cdn_status' => WP_Hummingbird_Utils::get_module( 'minify' )->get_cdn_status(),
			'is_member'  => WP_Hummingbird_Utils::is_member(),
			'logging'    => WP_Hummingbird_Settings::get_setting( 'log', 'minify' ),
			'file_path'  => WP_Hummingbird_Settings::get_setting( 'file_path', 'minify' ),
		));
	}

	/**
	 * Enqueued files header meta box.
	 *
	 * @since 1.7.1
	 */
	public function eunqeued_files_metabox_header() {
		$this->view( 'minification/enqueued-files-meta-box-header', array(
			'title' => __( 'Assets', 'wphb' ),
			'type'  => $this->mode,
		) );
	}

	/**
	 * Content after tabbed menu.
	 *
	 * @param string $tab  Tab name.
	 */
	public function after_tab( $tab ) {
		if ( 'files' === $tab ) {
			echo ' <span class="sui-tag sui-tag-disabled">' . esc_html( WP_Hummingbird_Utils::minified_files_count() ) . '</span>';
		}
	}

	/**
	 * Parse settings update.
	 *
	 * @param string $type     Asset type. Accepts: 'scripts' and 'styles'.
	 * @param array  $options  Current settings.
	 *
	 * @return mixed
	 */
	private function _sanitize_type( $type, $options ) {
		/* @var WP_Hummingbird_Module_Minify $minify */
		$minify = WP_Hummingbird_Utils::get_module( 'minify' );
		$current_options = $minify->get_options();

		// We'll save what groups have changed so we reset the cache for those groups.
		$changed_groups = array();

		if ( ! empty( $_POST[ $type ] ) ) { // Input var okay.
			foreach ( wp_unslash( $_POST[ $type ] ) as $handle => $item ) { // Input var okay.
				$key = array_search( $handle, $options['block'][ $type ], true );
				if ( ! isset( $item['include'] ) ) {
					$options['block'][ $type ][] = $handle;
				} elseif ( false !== $key ) {
					unset( $options['block'][ $type ][ $key ] );
				}
				$options['block'][ $type ] = array_unique( $options['block'][ $type ] );
				$diff = array_merge(
					array_diff( $current_options['block'][ $type ], $options['block'][ $type ] ),
					array_diff( $options['block'][ $type ], $current_options['block'][ $type ] )
				);
				if ( $diff ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}

				$key = array_search( $handle, $options['dont_minify'][ $type ], true );
				if ( ! isset( $item['minify'] ) ) {
					$options['dont_minify'][ $type ][] = $handle;
				} elseif ( false !== $key ) {
					unset( $options['dont_minify'][ $type ][ $key ] );
				}
				$options['dont_minify'][ $type ] = array_unique( $options['dont_minify'][ $type ] );
				$diff = array_merge(
					array_diff( $current_options['dont_minify'][ $type ], $options['dont_minify'][ $type ] ),
					array_diff( $options['dont_minify'][ $type ], $current_options['dont_minify'][ $type ] )
				);
				if ( $diff ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}

				$key = array_search( $handle, $options['combine'][ $type ], true );
				if ( ! isset( $item['combine'] ) && false !== $key ) {
					unset( $options['combine'][ $type ][ $key ] );
				} elseif ( isset( $item['combine'] ) ) {
					$options['combine'][ $type ][] = $handle;
				}
				$options['combine'][ $type ] = array_unique( $options['combine'][ $type ] );
				$diff = array_merge(
					array_diff( $current_options['combine'][ $type ], $options['combine'][ $type ] ),
					array_diff( $options['combine'][ $type ], $current_options['combine'][ $type ] )
				);

				if ( $diff ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}

				$key = array_search( $handle, $options['defer'][ $type ], true );
				if ( ! isset( $item['defer'] ) && false !== $key ) {
					unset( $options['defer'][ $type ][ $key ] );
				} elseif ( isset( $item['defer'] ) ) {
					$options['defer'][ $type ][] = $handle;
				}
				$options['defer'][ $type ] = array_unique( $options['defer'][ $type ] );
				$diff = array_merge(
					array_diff( $current_options['defer'][ $type ], $options['defer'][ $type ] ),
					array_diff( $options['defer'][ $type ], $current_options['defer'][ $type ] )
				);

				if ( $diff ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}

				$key = array_search( $handle, $options['inline'][ $type ], true );
				if ( ! isset( $item['inline'] ) && false !== $key ) {
					unset( $options['inline'][ $type ][ $key ] );
				} elseif ( isset( $item['inline'] ) ) {
					$options['inline'][ $type ][] = $handle;
				}
				$options['inline'][ $type ] = array_unique( $options['inline'][ $type ] );
				$diff = array_merge(
					array_diff( $current_options['inline'][ $type ], $options['inline'][ $type ] ),
					array_diff( $options['inline'][ $type ], $current_options['inline'][ $type ] )
				);

				if ( $diff ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}

				if ( empty( $item['position'] ) ) {
					$item['position'] = 'header';
				}
				$key_exists = array_key_exists( $handle, $options['position'][ $type ] );
				if ( 'footer' === $item['position'] ) {
					$options['position'][ $type ][ $handle ] = $item['position'];
				} elseif ( $key_exists ) {
					unset( $options['position'][ $type ][ $handle ] );
				}
				if ( $diff = array_diff_key( $current_options['position'][ $type ], $options['position'][ $type ] ) ) {
					foreach ( $diff as $diff_handle ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}
				$diff = array_merge(
					array_diff_key( $current_options['position'][ $type ], $options['position'][ $type ] ),
					array_diff_key( $options['position'][ $type ], $current_options['position'][ $type ] )
				);
				if ( $diff ) {
					foreach ( $diff as $diff_handle => $position ) {
						$_groups = WP_Hummingbird_Module_Minify_Group::get_groups_from_handle( $diff_handle, $type );
						if ( $_groups ) {
							$changed_groups = array_merge( $changed_groups, $_groups );
						}
					}
				}
			} // End foreach().
		} // End if().

		// Delete those groups.
		foreach ( $changed_groups as $group ) {
			/* @var WP_Hummingbird_Module_Minify_Group $group */
			$group->delete_file();
		}

		return $options;
	}

	/**
	 * Populate minification table with enqueued files.
	 *
	 * @param array  $collection  Array of files.
	 * @param string $type        Collection type. Accepts: scripts, styles.
	 * @param string $view        View type. Accepts: basic, advanced.
	 *
	 * @return array
	 */
	private function _collection_rows( $collection, $type, $view ) {
		/* @var WP_Hummingbird_Module_Minify $minification_module */
		$minification_module = WP_Hummingbird_Utils::get_module( 'minify' );

		$options = $minification_module->get_options();

		// This will be used for filtering.
		$theme = wp_get_theme();
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			foreach ( get_site_option( 'active_sitewide_plugins', array() ) as $plugin => $item ) {
				$active_plugins[] = $plugin;
			}
		}

		// This will be used to disable the combine button if site is ssl.
		$is_ssl = is_ssl() && WP_Hummingbird_Utils::get_http2_status();

		$content = array(
			'content' => '',
			'other'   => '',
		);

		foreach ( $collection as $item ) {
			/**
			 * Filter minification enqueued files items displaying
			 *
			 * @param bool $display If set to true, display the item. Default false
			 * @param array $item Item data
			 * @param string $type Type of the current item (scripts|styles)
			 */
			if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, $type ) ) {
				continue;
			}

			if ( ! empty( $options['position'][ $type ][ $item['handle'] ] ) && in_array( $options['position'][ $type ][ $item['handle'] ], array(
				'header',
				'footer',
			), true ) ) {
				$position = $options['position'][ $type ][ $item['handle'] ];
			} else {
				$position = '';
			}

			$original_size   = false;
			$compressed_size = false;

			$base_name = $type . '[' . $item['handle'] . ']';

			if ( isset( $item['original_size'] ) ) {
				$original_size = $item['original_size'];
			} elseif ( file_exists( WP_Hummingbird_Utils::src_to_path( $item['src'] ) ) ) {
				// Get original file size for local files that don't have it set for some reason.
				$original_size = number_format_i18n( filesize( WP_Hummingbird_Utils::src_to_path( $item['src'] ) ) / 1000, 1 );
			}

			if ( isset( $item['compressed_size'] ) ) {
				$compressed_size = $item['compressed_size'];
			}
			$processed  = false;
			$compressed = true;
			if ( $original_size && $compressed_size ) {
				$processed  = true;
				if ( $compressed_size > $original_size ) {
					$compressed = false;
				}
			}

			$site_url = str_replace( array( 'http://', 'https://' ), '', get_option( 'siteurl' ) );
			$rel_src = str_replace( array( 'http://', 'https://', $site_url ), '', $item['src'] );
			$rel_src = ltrim( $rel_src, '/' );
			$full_src = $item['src'];

			$info = pathinfo( $full_src );

			$ext = 'OTHER';
			if ( isset( $info['extension'] ) && preg_match( '/(css)\??[a-zA-Z=0-9]*/', $info['extension'] ) ) {
				$ext = 'CSS';
			} elseif ( isset( $info['extension'] ) && preg_match( '/(js)\??[a-zA-Z=0-9]*/', $info['extension'] ) ) {
				$ext = 'JS';
			}

			$row_error = $minification_module->errors_controller->get_handle_error( $item['handle'], $type );
			$disable_switchers = array();
			if ( $row_error ) {
				$disable_switchers = $row_error['disable'];
			}

			$filter = '';
			if ( preg_match( '/wp-content\/themes\/(.*)\//', $full_src, $matches ) ) {
				$filter = $theme->get( 'Name' );
			} elseif ( preg_match( '/wp-content\/plugins\/([\w-_]*)\//', $full_src, $matches ) ) {
				// The source comes from a plugin.
				foreach ( $active_plugins as $active_plugin ) {
					if ( stristr( $active_plugin, $matches[1] ) ) {
						// It seems that we found the plguin but let's double check.
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $active_plugin );
						if ( $plugin_data['Name'] ) {
							// Found plugin, add it as a filter.
							$filter = $plugin_data['Name'];
						}
						break;
					}
				}
			}

			$minified_file = preg_match( '/\.min\.(css|js)/', wp_basename( $rel_src ) );

			/**
			 * Allows to enable/disable switchers in minification page
			 *
			 * @param array $disable_switchers List of switchers disabled for an item ( include, minify, combine)
			 * @param array $item Info about the current item
			 * @param string $type Type of the current item (scripts|styles)
			 */
			$disable_switchers = apply_filters( 'wphb_minification_disable_switchers', $disable_switchers, $item, $type );

			// Disabled state filter.
			$disabled = in_array( $item['handle'], $options['block'][ $type ], true );

			// Check if file has had changes made to it (don't need to check minify).
			$file_changed = false;
			if ( in_array( $item['handle'], $options['combine'][ $type ], true )
			|| 'footer' === $position
			|| in_array( $item['handle'], $options['defer'][ $type ], true )
			|| in_array( $item['handle'], $options['inline'][ $type ], true ) ) {
				$file_changed = true;
			}

			$args = compact(
				'item',
				'options',
				'type',
				'position',
				'base_name',
				'original_size',
				'compressed_size',
				'rel_src',
				'full_src',
				'ext',
				'row_error',
				'disable_switchers',
				'filter',
				'is_ssl',
				'minified_file',
				'disabled',
				'processed',
				'compressed',
				'file_changed'
			);
			if ( 'OTHER' !== $ext ) {
				$content['content'] .= $this->view( "minification/{$view}-files-rows", $args, false );
			} else {
				$content['other'] .= $this->view( "minification/{$view}-files-rows", $args, false );
			}
		} // End foreach().

		return $content;
	}
}