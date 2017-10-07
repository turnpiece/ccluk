<?php

/**
 * Class WP_Hummingbird_Minification_Page
 */
class WP_Hummingbird_Minification_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * WP_Hummingbird_Minification_Page constructor.
	 * @param $slug
	 * @param $page_title
	 * @param $menu_title
	 * @param bool $parent
	 * @param bool $render
	 */
	public function __construct( $slug, $page_title, $menu_title, $parent = false, $render = true ) {
		parent::__construct( $slug, $page_title, $menu_title, $parent, $render );

		$this->tabs = array(
			'files' => __( 'Files', 'wphb' ),
		);

		if ( ! is_multisite() ) {
			$this->tabs['settings'] = __( 'Settings', 'wphb' );
		}

		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	public function on_load() {

		wphb_minification_maybe_stop_scanning_files();
		if ( isset( $_POST['submit'] ) ) {
			check_admin_referer( 'wphb-enqueued-files' );

			$options = wphb_get_settings();

			$options = $this->_sanitize_type( 'styles', $options );
			$options = $this->_sanitize_type( 'scripts', $options );

			wphb_update_settings( $options );

			wp_safe_redirect( add_query_arg( 'updated', 'true' ) );
			exit;
		}

		if ( isset( $_POST['clear-cache'] ) ) {
			wphb_clear_minification_cache( false );
			$url = remove_query_arg( 'updated' );
			wp_safe_redirect( add_query_arg( 'wphb-cache-cleared', 'true', $url ) );
			exit;
		}

		// If selected to enable CDN from minification scan.
		if ( isset( $_POST['enable_cdn'] ) ) {
			$value = wp_validate_boolean( $_POST['enable_cdn'] );
			wphb_toggle_cdn( $value );
			// Redirect back.
			wp_safe_redirect( remove_query_arg( array(
				'enable_cdn',
				'wphb-cache-cleared',
			)));
			exit;
		}

	}

	private function _sanitize_type( $type, $options ) {
		$current_options = wphb_get_settings();

		// We'll save what groups have changed so we reset the cache for those groups.
		$changed_groups = array();

		if ( ! empty( $_POST[ $type ] ) ) {
			foreach ( $_POST[ $type ] as $handle => $item ) {
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
	 * Render the page
	 */
	public function render() {
		?>
		<div id="container" class="wrap wrap-wp-hummingbird wrap-wp-hummingbird-page <?php echo 'wrap-' . esc_attr( $this->slug ); ?>">
			<?php
			if ( isset( $_GET['updated'] ) ) {
				$this->show_notice( 'updated', __( 'Your new minify settings have been saved. Simply refresh your homepage and Hummingbird will minify and serve your newly compressed files.', 'wphb' ), 'success', false );
			}

			if ( isset( $_GET['wphb-cache-cleared'] ) ) {
				$this->show_notice( 'updated', __( 'Your cache has been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ), 'success', true );
			}

			if ( isset( $_GET['wphb-cache-cleared-with-cloudflare'] ) ) {
				$this->show_notice( 'updated', __( 'Your local and Cloudflare caches have been successfully cleared. Your assets will regenerate the next time someone visits your website.', 'wphb' ), 'success', true );
			}
			?>
			<div class="wphb-notice wphb-notice-success hidden" id="wphb-notice-minification-advanced-settings-updated">
				<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
			</div>

			<?php
			$this->render_header();

			$this->render_inner_content();
			?>
		</div><!-- end container -->

		<script>
			jQuery(document).ready( function() {
				window.WPHB_Admin.getModule( 'notices' );
			});

			// Avoid moving dashboard notice under h2
			var wpmuDash = document.getElementById( 'wpmu-install-dashboard' );
			if ( wpmuDash )
				wpmuDash.className = wpmuDash.className + " inline";

			jQuery( 'div.updated, div.error' ).addClass( 'inline' );
		</script>
		<?php
	}

	public function render_header() {
		?>
		<section id="header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php
			$collection = wphb_minification_get_resources_collection();
			$module = wphb_get_module( 'minify' );
			if ( ( ! empty( $collection['styles'] ) && ! empty( $collection['scripts'] ) ) && $module->is_active() ) : ?>
				<div class="actions status">
					<div class="toggle-group toggle-group-with-buttons">
						<div class="tooltip-box">
							<span class="toggle" tooltip="<?php esc_attr_e( 'Turn off Minification', 'wphb' ); ?>">
								<input type="checkbox" id="wphb-disable-minification" class="toggle-checkbox" name="wphb-disable-minification" checked>
								<label for="wphb-disable-minification" class="toggle-label"></label>
							</span>
						</div>
					</div>
					<span class="spinner right"></span>
				</div>
			<?php endif; ?>
		</section>
		<?php
	}

	/**
	 * Overriden from parent class
	 */
	protected function render_inner_content() {
		$collection = wphb_minification_get_resources_collection();
		$args = array(
			'instructions' => empty( $collection['styles'] ) && empty( $collection['scripts'] ),
		);

		$this->view( $this->slug . '-page', $args );
	}

	public function register_meta_boxes() {
		$collection = wphb_minification_get_resources_collection();
		$module = wphb_get_module( 'minify' );

		if ( ( empty( $collection['styles'] ) && empty( $collection['scripts'] ) ) || wphb_minification_is_scanning_files() || ! $module->is_active() ) {
			$this->add_meta_box( 'minification/enqueued-files-empty', __( 'Get Started', 'wphb' ), array( $this, 'enqueued_files_empty_metabox' ), null, null, 'box-enqueued-files-empty', array( 'box_class' => 'dev-box content-box content-box-one-col-center') );
		} else {
		    $this->add_meta_box( 'minification/summary-meta-box', null, array( $this, 'summary_metabox' ), null, null, 'summary', array( 'box_class' => 'dev-box content-box content-box-two-cols-image-left' ) );

			$this->add_meta_box( 'minification/enqueued-files', __( 'Files', 'wphb' ), array( $this, 'enqueued_files_metabox' ), null, null, 'main', array( 'box_content_class' => 'box-content', 'box_footer_class' => 'box-footer') );

			if ( ! is_multisite() ) {
				$this->add_meta_box( 'minification/advanced-settings', __( 'Advanced Settings', 'wphb' ), array( $this, 'advanced_settings_metabox' ), array( $this, 'advanced_settings_metabox_header' ), null, 'settings', array( 'box_content_class' => 'box-content', 'box_footer_class' => 'box-footer') );
			}
		}
	}

	public function enqueued_files_empty_metabox() {
		// Get current user name.
		$user = wphb_get_current_user_info();
		$checking_files = wphb_minification_is_scanning_files();
		$this->view( 'minification/enqueued-files-empty-meta-box', array( 'user' => $user, 'checking_files' => $checking_files ) );
	}


	public function enqueued_files_metabox() {
		$collection = wphb_minification_get_resources_collection();
		$styles_rows = $this->_collection_rows( $collection['styles'], 'styles' );
		$scripts_rows = $this->_collection_rows( $collection['scripts'], 'scripts' );

		// This will be used to disable the combine button on the bulk update modal if site is ssl.
		$is_ssl = wphb_is_ssl();

		$active_plugins = get_option('active_plugins', array() );
		if ( is_multisite() ) {
			foreach ( get_site_option('active_sitewide_plugins', array() ) as $plugin => $item ) {
				$active_plugins[] = $plugin;
			}
		}
		$theme = wp_get_theme();

		$selector_filter = array();
		$selector_filter[ $theme->Name ] = $theme->Name;
		foreach ( $active_plugins as $plugin ) {
			if ( ! is_file( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				continue;
			}
			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			if ( $plugin_data['Name'] ) {
				// Found plugin, add it as a filter
				$selector_filter[ $plugin_data['Name'] ] = $plugin_data['Name'];
			}
		}

		/** @var WP_Hummingbird_Module_Minify $module */
		$module = wphb_get_module( 'minify' );
		$is_server_error = $module->errors_controller->is_server_error();
		$server_errors = $module->errors_controller->get_server_errors();
		$error_time_left = $module->errors_controller->server_error_time_left();

		if ( isset( $_GET['view-export-form'] ) ) {
			$this->view( 'minification/export-form' );
		}

		$args = compact( 'collection', 'styles_rows', 'scripts_rows', 'selector_filter', 'is_server_error', 'server_errors', 'error_time_left', 'is_ssl' );
		$this->view( 'minification/enqueued-files-meta-box', $args );
	}


	private function _collection_rows( $collection, $type ) {
		$options = wphb_get_settings();

		// This will be used for filtering
		$theme = wp_get_theme();
		$active_plugins = get_option('active_plugins', array() );
		if ( is_multisite() ) {
			foreach ( get_site_option('active_sitewide_plugins', array() ) as $plugin => $item ) {
				$active_plugins[] = $plugin;
			}
		}

		// This will be used to disable the combine button if site is ssl.
		$is_ssl = wphb_is_ssl();

		/**
		 * @var WP_Hummingbird_Module_Minify $minification_module
		 */
		$minification_module = wphb_get_module( 'minify' );

		$content = '';

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

			if ( ! empty( $options['position'][ $type ][ $item['handle'] ] ) && in_array( $options['position'][ $type ][ $item['handle'] ], array( 'header', 'footer' ) ) ) {
				$position = $options['position'][ $type ][ $item['handle'] ];
			}
			else {
				$position = '';
			}

			$original_size = false;
			$compressed_size = false;

			$base_name = $type . '[' . $item['handle'] . ']';

			if ( isset ( $item['original_size'] ) )
				$original_size = $item['original_size'];

			if ( isset( $item['compressed_size'] ) )
				$compressed_size = $item['compressed_size'];

			$site_url = str_replace( array( 'http://', 'https://' ), '', get_option('siteurl') );
			$rel_src = str_replace( array( 'http://', 'https://', $site_url ), '', $item['src'] );
			$rel_src = ltrim( $rel_src, '/' );
			$full_src = $item['src'];

			$info = pathinfo( $full_src );
			$ext = isset( $info['extension'] ) ? strtoupper( $info['extension'] ) : __( 'OTHER', 'wphb' );
			if ( ! in_array( $ext, array( __( 'OTHER', 'wphb' ), 'CSS', 'JS' ) ) ) {
				$ext = __( 'OTHER', 'wphb' );
			}
			$row_error = $minification_module->errors_controller->get_handle_error( $item['handle'], $type );
			$disable_switchers = array();
			if ( $row_error ) {
				$disable_switchers = $row_error['disable'];
			}

			$filter = '';
			if ( preg_match( '/wp-content\/themes\/(.*)\//', $full_src, $matches ) ) {
				$filter = $theme->Name;
			}
			elseif ( preg_match( '/wp-content\/plugins\/([\w-_]*)\//', $full_src, $matches ) ) {
				// The source comes from a plugin
				foreach ( $active_plugins as $active_plugin ) {
					if ( stristr( $active_plugin, $matches[1] ) ) {
						// It seems that we found the plguin but let's double check
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $active_plugin );
						if ( $plugin_data['Name'] ) {
							// Found plugin, add it as a filter
							$filter = $plugin_data['Name'];
						}
						break;
					}

				}
			}

			$minified_file = $this->is_minified( wp_basename( $rel_src ) );

			/**
			 * Allows to enable/disable switchers in minification page
			 *
			 * @param array $disable_switchers List of switchers disabled for an item ( include, minify, combine)
			 * @param array $item Info about the current item
			 * @param string $type Type of the current item (scripts|styles)
			 */
			$disable_switchers = apply_filters( 'wphb_minification_disable_switchers', $disable_switchers, $item, $type );

			$args = compact( 'item', 'options', 'type', 'position', 'base_name', 'original_size', 'compressed_size', 'rel_src', 'full_src', 'ext', 'row_error', 'disable_switchers', 'filter', 'is_ssl', 'minified_file' );
			$content .= $this->view( 'minification/enqueued-files-rows', $args, false );
		}

		return $content;
	}

	function advanced_settings_metabox() {
		$args = array(
			'use_cdn' => wphb_get_cdn_status(),
			'disabled' => ! wphb_is_member(),
			'super_minify' => wphb_is_member(),
		);
		$this->view( 'minification/advanced-settings', $args );
	}

	function advanced_settings_metabox_header() {
		$args = array(
			'is_member' => wphb_is_member(),
			'title' => __( 'Advanced Settings', 'wphb' )
		);
		$this->view( 'minification/advanced-settings-header', $args );
	}

	function summary_metabox() {
		$collection = wphb_minification_get_resources_collection();
		// Remove those assets that we don't want to display
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

		if ( $original_size <= 0 ) {
			$percentage = 0;
		}
		else {
			//$percentage = 100 - ( (int) $compressed_size * 100 ) / (int) $original_size;
            $percentage = 100 - (int) $compressed_size * 100 / (int) $original_size;
		}
		$percentage = number_format_i18n( $percentage, 2 );
		$compressed_size = number_format( (int) $original_size - (int) $compressed_size, 1 );

		$use_cdn = wphb_get_cdn_status();
		$is_member = wphb_is_member();

		$args = compact( 'enqueued_files', 'compressed_size', 'percentage', 'use_cdn', 'is_member' );
	    $this->view( 'minification/summary-meta-box', $args );
    }

    public function after_tab( $tab ) {
		if ( 'files' === $tab ) {
			echo ' <span class="wphb-button-label wphb-button-label-light">' . wphb_minification_files_count() . '</span>';
		}
	}

	/**
	 * Checks whether the file has 'min' before the file extension
	 *
	 * @return bool   Default: False. True if file is min.
	 */
	function is_minified( $file_name ) {
		if ( preg_match( '/\.min\.(css|js)/', $file_name ) ) {
			return true;
		}
		return false;
	}
}