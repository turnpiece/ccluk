<?php

/**
 * Class WP_Hummingbird_Caching_Page
 *
 * @property array tabs
 */
class WP_Hummingbird_Caching_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Current report.
	 *
	 * @since  1.5.3
	 * @var    array $report
	 * @access private
	 */
	private $report;

	/**
	 * Number of issues.
	 *
	 * If Cloudflare is enabled will calculate number of issues for it, if not - number of local issues.
	 *
	 * @since 1.5.3
	 * @var   int $issues  Default 0.
	 */
	private $issues = 0;

	/**
	 * Settings expiration values.
	 *
	 * @since 1.5.3
	 * @var   array $expires
	 */
	private $expires;

	/**
	 * Cloudflare module status.
	 *
	 * @since  1.5.3
	 * @var    bool $cloudflare  Default false.
	 * @access private
	 */
	private $cloudflare = false;

	/**
	 * If site is using Cloudflare.
	 *
	 * @since 1.7.1
	 */
	private $cf_server = false;

	/**
	 * Cloudflare expiration value.
	 *
	 * @since  1.5.3
	 * @var    int $expiration Default 0.
	 * @access private
	 */
	private $expiration = 0;

	/**
	 * If .htaccess is written by the module.
	 *
	 * @var bool
	 */
	private $htaccess_written = false;

	/**
	 * Init the page module.
	 *
	 * @since 1.7.1 Moved from __construct to init
	 */
	private function init() {
		$this->tabs = array(
			'main'     => __( 'Page Caching', 'wphb' ),
			'browser'  => __( 'Browser Caching', 'wphb' ),
			'gravatar' => __( 'Gravatar Caching', 'wphb' ),
			'rss'      => __( 'RSS Caching', 'wphb' ),
		);

		// We need to actually tweak these tasks.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );

		// Remove modules that are not used on subsites in a network.
		if ( is_multisite() && ! is_network_admin() ) {
			unset( $this->tabs['browser'] );
			unset( $this->tabs['gravatar'] );
			unset( $this->tabs['rss'] );

			// Don't run anything else.
			return;
		}

		/* @var WP_Hummingbird_Module_Caching $caching */
		$caching = WP_Hummingbird_Utils::get_module( 'caching' );
		$options = $caching->get_options();

		$this->expires = array(
			'css'        => $options['expiry_css'],
			'javascript' => $options['expiry_javascript'],
			'media'      => $options['expiry_media'],
			'images'     => $options['expiry_images'],
		);

		/**
		 * Check Cloudflare status.
		 *
		 * If Cloudflare is active, we store the values of CLoudFlare caching settings to the report variable.
		 * Else - we store the local setting in the report variable. That way we don't have to query and check
		 * later on what report to show to the user.
		 */

		/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$this->cloudflare = $cf_module->is_connected() && $cf_module->is_zone_selected();

		if ( $this->cloudflare ) {
			$this->expiration = $cf_module->get_caching_expiration();
			// Fill the report with values from Cloudflare.
			$this->report = array_fill_keys( array_keys( $this->expires ), $this->expiration );
			// Save status.
			$this->cf_server = $cf_module->has_cloudflare( true );
		} else {
			// Get latest local report.
			$this->report = WP_Hummingbird_Utils::get_status( 'caching' );
		}

		// Get number of issues.
		if ( ! $this->cloudflare ) {
			$this->htaccess_written = WP_Hummingbird_Module_Server::is_htaccess_written( 'caching' );
			$this->issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching' );
		} elseif ( 691200 > $this->expiration ) {
			$this->issues = count( $this->report );
		}

		// Re-check browser expiry whenever the browser caching page is loaded.
		if ( isset( $_GET['view'] ) && 'browser' === $_GET['view'] ) {
			$caching->get_analysis_data( true );
		}
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 *
	 * @since 1.7.0
	 */
	public function on_load() {
		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return;
		}

		$this->init();

		$redirect_to = remove_query_arg( array(
			'run',
			'enable',
			'disable',
			'caching-updated',
			'cache-disabled',
			'cache-enabled',
			'htaccess-error',
		) );

		// Parse submitted form from page caching or expiry settings pages.
		if ( isset( $_POST['submit'] ) ) { // Input var ok.
			check_admin_referer( 'wphb-caching' );

			if ( isset( $_POST['pc-settings'] ) && 1 === absint( $_POST['pc-settings'] ) ) {
				$form = 'page-caching';
			} elseif ( isset( $_POST['expiry-settings'] ) && 1 === absint( $_POST['expiry-settings'] ) ) {
				$form = 'expiry-settings';
			}
		}

		// Process form submit from page caching settings.
		if ( isset( $form ) && 'page-caching' === $form ) {
			$admins_can_disable_pc = false;
			$page_types = array();
			if ( isset( $_POST['page_types'] ) && is_array( $_POST['page_types'] ) ) {
				$page_types = array_keys( $_POST['page_types'] );
			}

			$cache_settings = array(
				'logged_in'    => 0,
				'url_queries'  => 0,
				'cache_404'    => 0,
				'clear_update' => 0,
				'debug_log'    => 0,
			);

			if ( isset( $_POST['settings'] ) ) {
				$form_data = $_POST['settings'];
				$cache_settings['logged_in']    = isset( $form_data['logged-in'] ) ? absint( $form_data['logged-in'] ) : 0;
				$cache_settings['url_queries']  = isset( $form_data['url-queries'] ) ? absint( $form_data['url-queries'] ) : 0;
				$cache_settings['cache_404']    = isset( $form_data['cache-404'] ) ? absint( $form_data['cache-404'] ) : 0;
				$cache_settings['clear_update'] = isset( $form_data['clear-update'] ) ? absint( $form_data['clear-update'] ) : 0;
				$cache_settings['debug_log']    = isset( $form_data['debug-log'] ) ? absint( $form_data['debug-log'] ) : 0;

				if ( isset( $form_data['admins_disable_caching'] ) && 1 === absint( $form_data['admins_disable_caching'] ) ) {
					$admins_can_disable_pc = true;
				}
			}

			$url_strings = '';
			if ( isset( $_POST['url_strings'] ) ) {
				$url_strings = sanitize_textarea_field( wp_unslash( $_POST['url_strings'] ) ); // Input var okay.
				$url_strings = preg_split( '/[\r\n\t ]+/', $url_strings );
				$url_strings = str_replace( '\\', '', $url_strings );
				$url_strings = str_replace( '/', '\/', $url_strings );
				$url_strings = str_replace( '.', '\.', $url_strings );
			}

			$user_agents = '';
			if ( isset( $_POST['user_agents'] ) ) {
				$user_agents = sanitize_textarea_field( wp_unslash( $_POST['user_agents'] ) ); // Input var okay.
				$user_agents = preg_split( '/[\r\n\t ]+/', $user_agents );
			}

			$settings['page_types'] = $page_types;
			$settings['settings']   = $cache_settings;
			$settings['exclude']['url_strings'] = $url_strings;
			$settings['exclude']['user_agents'] = $user_agents;

			/* @var WP_Hummingbird_Module_Page_Cache $module */
			$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
			$options = $module->get_options();

			if ( $admins_can_disable_pc ) {
				$options['enabled'] = 'blog-admins';
			} elseif ( $module->is_active() ) {
				$options['enabled'] = true;
			}

			$module->update_options( $options );

			$module->save_settings( $settings );
		} // End if().

		// Process form submit from expiry settings.
		if ( isset( $form ) && 'expiry-settings' === $form ) {
			if ( isset( $_POST['expiry-set-type'] ) && 'all' === sanitize_text_field( wp_unslash( $_POST['expiry-set-type'] ) ) ) { // Input var ok.
				$this->caching_set_expiration( 'all', $_POST['set-expiry-all'] );
			} else {
				$this->caching_set_expiration( 'javascript', $_POST['set-expiry-javascript'] );
				$this->caching_set_expiration( 'css', $_POST['set-expiry-css'] );
				$this->caching_set_expiration( 'media', $_POST['set-expiry-media'] );
				$this->caching_set_expiration( 'images', $_POST['set-expiry-images'] );
			}

			$response = $this->caching_reload_snippet();

			/* @var WP_Hummingbird_Module_Caching $caching_module */
			$caching_module = WP_Hummingbird_Utils::get_module( 'caching' );
			$caching_module->clear_cache();

			if ( 'apache' === $response['type'] && $response['updatedFile'] ) {
				$redirect_to = add_query_arg( array(
					'run'               => true,
					'caching-updated'   => true,
				), $redirect_to );
			} elseif ( 'apache' === $response['type'] && ! $response['updatedFile'] ) {
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
			} else {
				$redirect_to = add_query_arg( array(
					'run'               => true,
					'caching-updated'   => true,
				), $redirect_to );
			}
		} // End if().

		// Enable browser caching.
		if ( isset( $_GET['enable'] ) ) { // Input var ok.
			// Enable caching in .htaccess (only for apache servers).
			$result = WP_Hummingbird_Module_Server::save_htaccess( 'caching' );
			if ( $result ) {
				// Clear saved status.
				/* @var WP_Hummingbird_Module_Caching $caching_module */
				$caching_module = WP_Hummingbird_Utils::get_module( 'caching' );
				$caching_module->clear_cache();

				$redirect_to = add_query_arg( 'cache-enabled', true, $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
			}
		} // End if().

		// Disable browser caching.
		if ( isset( $_GET['disable'] ) ) { // Input var ok.
			// Disable caching in htaccess (only for apache servers).
			$result = WP_Hummingbird_Module_Server::unsave_htaccess( 'caching' );
			if ( $result ) {
				// Clear saved status.
				/* @var WP_Hummingbird_Module_Caching $caching_module */
				$caching_module = WP_Hummingbird_Utils::get_module( 'caching' );
				$caching_module->clear_cache();

				$redirect_to = add_query_arg( 'cache-disabled', true, $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
			}
		} // End if().

		if ( isset( $_GET['run'] ) && isset( $_GET['type'] ) ) { // Input var ok.
			$this->run_actions( $_GET['type'] );
		}

		if ( isset( $_POST['submit'] ) || isset( $_GET['enable'] ) || isset( $_GET['disable'] ) || isset( $_GET['run'] ) ) {
			wp_safe_redirect( $redirect_to );
			exit;
		}

	}

	/**
	 * Run Page caching, Browser caching, Gravatar caching...
	 *
	 * @param string $type Type of action to run.
	 * @since 1.4.5
	 */
	private function run_actions( $type ) {
		check_admin_referer( 'wphb-run-caching' );

		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return;
		}

		switch ( $type ) {
			// Activate Page Cache.
			case 'pc-activate':
				/* @var WP_Hummingbird_Module_Page_Cache $module */
				$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
				$module->toggle_service( true );
				break;
			// Deactivate Page Cache.
			case 'pc-deactivate':
				/* @var WP_Hummingbird_Module_Page_Cache $module */
				$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
				$module->toggle_service( false );
				break;
			// Download page caching logs.
			case 'download-logs':
				$content = file_get_contents( WP_CONTENT_DIR . '/wphb-logs/page-caching-log.php' );
				/* Remove <?php die(); ?> from file */
				$content = substr( $content, 15 );

				header( 'Content-Description: Page caching log download' );
				header( 'Content-Type: text/plain' );
				header( 'Content-Disposition: attachment; filename=page-caching.log' );
				header( 'Content-Transfer-Encoding: binary' );
				header( 'Content-Length: ' . strlen( $content ) );
				header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
				header( 'Expires: 0' );
				header( 'Pragma: public' );

				echo $content;
				exit;
			// Deactivate Cloudflare.
			case 'cf-deactivate':
				/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
				$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
				$cf_module->disconnect();
				break;
			// Activate Gravatar Cache.
			case 'gc-activate':
				/* @var WP_Hummingbird_Module_Gravatar $module */
				$module = WP_Hummingbird_Utils::get_module( 'gravatar' );
				$options = $module->get_options();
				$options['enabled'] = true;
				$module->update_options( $options );
				break;
			// Deactivate Gravatar Cache.
			case 'gc-deactivate':
				/* @var WP_Hummingbird_Module_Gravatar $module */
				$module = WP_Hummingbird_Utils::get_module( 'gravatar' );
				$options = $module->get_options();
				$options['enabled'] = false;
				$module->update_options( $options );
				break;
			// Purge gravatar files.
			case 'gc-purge':
				/* @var WP_Hummingbird_Module_Gravatar $module */
				$module = WP_Hummingbird_Utils::get_module( 'gravatar' );
				$redirect_to = remove_query_arg( array( 'run', '_wpnonce', 'type', 'gravatars-purged', 'purge-error' ) );

				if ( $module->clear_cache() ) {
					$redirect_to = add_query_arg( 'gravatars-purged', true, $redirect_to );
				} else {
					$redirect_to = add_query_arg( 'purge-error', true, $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit;
			// Purge page cache files.
			case 'pc-purge':
				// Remove notice.
				delete_site_option( 'wphb-notice-cache-cleaned-show' );

				/* @var WP_Hummingbird_Module_Page_Cache $module */
				$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
				$redirect_to = remove_query_arg( array( 'run', '_wpnonce', 'type', 'page-cache-purged', 'purge-error' ) );

				if ( $module->clear_cache() ) {
					$redirect_to = add_query_arg( 'page-cache-purged', true, $redirect_to );
				} else {
					$redirect_to = add_query_arg( 'purge-error', true, $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit;
			// Purge subsite page cache files.
			case 'pc-purge-subsite':
				// Remove notice.
				delete_site_option( 'wphb-notice-cache-cleaned-show' );

				/* @var WP_Hummingbird_Module_Page_Cache $module */
				$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
				$redirect_to = remove_query_arg( array( 'run', '_wpnonce', 'type', 'page-cache-purged', 'purge-error' ) );

				if ( $module->clear_cache( null ) ) {
					$redirect_to = add_query_arg( 'page-cache-purged', true, $redirect_to );
				} else {
					$redirect_to = add_query_arg( 'purge-error', true, $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit;
			case 'check-expiry':
				// On check expiry click force a refresh of the data.
				WP_Hummingbird_Utils::get_status( 'caching', true );
				break;
			case 'rss-activate':
				WP_Hummingbird_Settings::update_setting( 'enabled', true, 'rss' );
				break;
			case 'rss-deactivate':
				WP_Hummingbird_Settings::update_setting( 'enabled', false, 'rss' );
				break;
		} // End switch().

		wp_safe_redirect( remove_query_arg( array( 'run', '_wpnonce', 'type' ) ) );
		exit;
	}

	/**
	 * Overwrites parent class render_header method.
	 *
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	public function render_header() {
		?>
		<div class="wphb-notice wphb-notice-success hidden" id="wphb-notice-cloudflare-purge-cache">
			<p><?php esc_html_e( 'Cloudflare cache successfully purged. Please wait 30 seconds for the purge to complete.', 'wphb' ); ?></p>
		</div>

		<div class="wphb-notice hidden" id="wphb-notice-rss-cache">
			<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
		</div>

		<?php
		if ( isset( $_GET['caching-updated'] ) && ! isset( $_GET['htaccess-error'] ) ) {
			if ( $this->htaccess_written ) {
				$this->admin_notices->show( 'updated', __( 'Your .htaccess file has been updated', 'wphb' ), 'success', true );
			} else {
				$this->admin_notices->show( 'updated', __( 'Code snippet updated', 'wphb' ), 'success', true );
			}
		}

		if ( isset( $_GET['cache-enabled'] ) ) {
			$this->admin_notices->show( 'updated', __( 'Browser cache enabled. Your .htaccess file has been updated', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['cache-disabled'] ) ) {
			$this->admin_notices->show( 'updated', __( 'Browser cache disabled. Your .htaccess file has been updated', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['gravatars-purged'] ) ) {
			$this->admin_notices->show( 'purged', __( 'Gravatar cache purged.', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['page-cache-purged'] ) ) {
			$this->admin_notices->show( 'purged', __( 'Page cache purged.', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['purge-error'] ) ) {
			$this->admin_notices->show( 'purged', __( 'There was an error during the cache purge. Check file permissions are 755 for /wp-content/wphb-cache or delete directory manually.', 'wphb' ), 'error', true );
		}

		parent::render_header();
	}

	/**
	 * Register meta boxes for the page.
	 */
	public function register_meta_boxes() {
		/**
		 * PAGE CACHING META BOXES.
		 * @var WP_Hummingbird_Module_Page_Cache $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'page_cache' );
		$options = $module->get_options();

		if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
			/**
			 * Main site
			 */
			if ( $module->is_active() ) {
				$this->add_meta_box(
					'page-caching',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_metabox' ),
					array( $this, 'page_caching_metabox_header' ),
					array( $this, 'page_caching_metabox_footer' ),
					'main'
				);
			} else {
				$this->add_meta_box(
					'page-caching-disabled',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_disabled_metabox' ),
					null,
					null,
					'main'
				);
			}
		} elseif ( is_super_admin() || 'blog-admins' === $options['enabled'] ) {
			/**
			 * Subsites
			 */
			if ( $module->is_active() ) {
				$this->add_meta_box(
					'page-caching',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_subsite_metabox' ),
					array( $this, 'page_caching_metabox_header' ),
					null,
					'main'
				);
			} else {
				$this->add_meta_box(
					'page-caching-disabled',
					__( 'Page Caching', 'wphb' ),
					array( $this, 'page_caching_disabled_metabox' ),
					null,
					null,
					'main'
				);
			}
		}

		// Do not continue on subsites.
		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		/**
		 * BROWSER CACHING META BOXES.
		 */

		$this->add_meta_box(
			'caching-summary',
			__( 'Browser Caching', 'wphb' ),
			array( $this, 'caching_summary_metabox' ),
			array( $this, 'caching_summary_metabox_header' ),
			null,
			'browser',
			array(
				'box_content_class' => 'box-content no-padding',
			)
		);

		$this->add_meta_box(
			'caching-settings',
			__( 'Configure', 'wphb' ),
			array( $this, 'caching_settings_metabox' ),
			array( $this, 'caching_settings_metabox_header' ),
			null,
			'browser'
		);

		/**
		 * GRAVATAR CACHING META BOXES.
		 * @var WP_Hummingbird_Module_Gravatar $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'gravatar' );

		if ( $module->is_active() ) {
			$this->add_meta_box(
				'caching-gravatar',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_metabox' ),
				array( $this, 'caching_gravatar_header' ),
				null,
				'gravatar'
			);
		} else {
			$this->add_meta_box(
				'gravatar-disabled',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_disabled_metabox' ),
				null,
				null,
				'gravatar'
			);
		}

		/**
		 * RSS CACHING META BOXES.
		 * @var WP_Hummingbird_Module_Rss $module
		 */
		$module = WP_Hummingbird_Utils::get_module( 'rss' );

		if ( $module->is_active() ) {
			$this->add_meta_box(
				'caching/rss',
				__( 'RSS Caching', 'wphb' ),
				array( $this, 'caching_rss_metabox' ),
				null,
				array( $this, 'caching_rss_footer' ),
				'rss'
			);
		} else {
			$this->add_meta_box(
				'caching/rss-disabled',
				__( 'RSS Caching', 'wphb' ),
				array( $this, 'caching_rss_metabox' ),
				null,
				null,
				'rss'
			);
		}

	}

	/**
	 * Overwrite parent render_inner_content method.
	 *
	 * Render content for display.
	 */
	protected function render_inner_content() {
		$server_name = WP_Hummingbird_Module_Server::get_server_type();
		$server_type = array_search( $server_name, WP_Hummingbird_Module_Server::get_servers(), true );
		$this->view( $this->slug . '-page', array(
			'server_type' => $server_type,
			'server_name' => $server_name,
		));
	}

	/**
	 * We need to insert an extra label to the tabs sometimes
	 *
	 * @param string $tab Current tab.
	 */
	public function after_tab( $tab ) {
		// For easier module access later on.
		if ( 'main' === $tab ) {
			$tab = 'page_cache';
		}

		if ( 'browser' === $tab ) {
			if ( ! $this->cloudflare ) {
				$issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching' );
			} elseif ( 691200 > $this->expiration ) {
				count( $this->report );
				$issues = count( $this->report );
				// Add an issue for the CloudFlare type.
				$issues++;
			} else {
				$issues = 0;
			}
			if ( 0 !== $issues ) {
				echo '<span class="wphb-button-label wphb-button-label-yellow">' . absint( $issues ) . '</span>';
				return;
			}
			echo '<i class="hb-wpmudev-icon-tick"></i>';
			return;
		}

		// Available modules.
		$modules = array( 'gravatar', 'page_cache', 'rss' );
		if ( ! in_array( $tab, $modules ) ) {
			return;
		}

		/* @var WP_Hummingbird_Module_Gravatar|WP_Hummingbird_Module_Page_Cache $module */
		$module = WP_Hummingbird_Utils::get_module( $tab );

		if ( $module->is_active() && ( ! isset( $module->error ) || ! is_wp_error( $module->error ) ) ) {
			echo '<i class="hb-wpmudev-icon-tick"></i>';
		} elseif ( isset( $module->error ) && is_wp_error( $module->error ) ) {
			echo '<i class="hb-wpmudev-icon-warning"></i>';
		}
	}

	/**
	 * Check to see if caching is fully enabled
	 *
	 * @access private
	 * @return bool
	 */
	private function is_caching_fully_enabled() {
		$recommended = WP_Hummingbird_Utils::get_recommended_caching_values();

		$results = WP_Hummingbird_Utils::get_status( 'caching' );

		$result_sum = 0;

		foreach ( $results as $key => $result ) {
			if ( $result >= $recommended[ $key ]['value'] ) {
				$result_sum++;
			}
		}

		return count( $results ) === $result_sum;
	}

	/**
	 * ******************
	 * PAGE CACHING     *
	 ********************/

	/**
	 * Disabled page caching meta box.
	 *
	 * @since 1.5.4
	 */
	public function page_caching_disabled_metabox() {
		$activate_url = add_query_arg( array(
			'type' => 'pc-activate',
			'run'  => 'true',
		) );
		$activate_url = wp_nonce_url( $activate_url, 'wphb-run-caching' );

		$this->view( 'caching/disabled-page-caching-meta-box', array(
			'activate_url' => $activate_url,
		) );
	}

	/**
	 * Page caching meta box.
	 *
	 * @since 1.7.0
	 */
	public function page_caching_metabox() {
		/* @var WP_Hummingbird_Module_Page_Cache $module */
		$module = WP_Hummingbird_Utils::get_module( 'page_cache' );

		$deactivate_url = add_query_arg( array(
			'type' => 'pc-deactivate',
			'run'  => 'true',
		));
		$deactivate_url = wp_nonce_url( $deactivate_url, 'wphb-run-caching' );

		$download_url = add_query_arg( array(
			'type' => 'download-logs',
			'run'  => 'true',
		));
		$download_url = wp_nonce_url( $download_url, 'wphb-run-caching' );

		$options = $module->get_options();
		$admins_can_disable = false;
		if ( 'blog-admins' === $options['enabled'] ) {
			$admins_can_disable = true;
		}

		$this->view( 'caching/page-caching-meta-box', array(
			'error'              => $module->error,
			'deactivate_url'     => $deactivate_url,
			'settings'           => $module->get_settings(),
			'admins_can_disable' => $admins_can_disable,
			'pages'              => WP_Hummingbird_Module_Page_Cache::get_page_types(),
			'download_url'       => $download_url,
		) );
	}

	/**
	 * Page caching subsite meta box.
	 *
	 * @since 1.8.0
	 */
	public function page_caching_subsite_metabox() {
		/* @var WP_Hummingbird_Module_Page_Cache $module */
		$module = WP_Hummingbird_Utils::get_module( 'page_cache' );

		$deactivate_url = add_query_arg( array(
			'type' => 'pc-deactivate',
			'run'  => 'true',
		));
		$deactivate_url = wp_nonce_url( $deactivate_url, 'wphb-run-caching' );

		$this->view( 'caching/page-caching-subsite-meta-box', array(
			'error'          => $module->error,
			'deactivate_url' => $deactivate_url,
		) );
	}

	/**
	 * Page caching meta box header.
	 *
	 * @since 1.7.0
	 */
	public function page_caching_metabox_header() {
		if ( ! is_main_network() || ! is_main_site() ) {
			$purge_url = add_query_arg( array(
				'type' => 'pc-purge-subsite',
				'run'  => 'true',
			) );
		} else {
			$purge_url = add_query_arg( array(
				'type' => 'pc-purge',
				'run'  => 'true',
			) );
		}
		$purge_url = wp_nonce_url( $purge_url, 'wphb-run-caching' );

		$this->view( 'caching/page-caching-meta-box-header', array(
			'title'     => __( 'Page Caching', 'wphb' ),
			'purge_url' => $purge_url,
		));
	}

	/**
	 * Page caching meta box footer.
	 *
	 * @since 1.7.0
	 */
	public function page_caching_metabox_footer() {
		$this->view( 'caching/page-caching-meta-box-footer', array() );
	}

	/**
	 * ******************
	 * BROWSER CACHING  *
	 ********************/

	/**
	 * Display header for caching summary meta box.
	 */
	public function caching_summary_metabox_header() {

		if ( ! $this->cloudflare ) {
			$issues = WP_Hummingbird_Utils::get_number_of_issues( 'caching' );
		} elseif ( 691200 > $this->expiration ) {
			count( $this->report );
			$issues = count( $this->report );
			// Add an issue for the CloudFlare type.
			$issues++;
		} else {
			$issues = 0;
		}

		$check_expiry_url = add_query_arg( array(
			'type' => 'check-expiry',
			'run'  => 'true',
		));
		$check_expiry_url = wp_nonce_url( $check_expiry_url, 'wphb-run-caching' );

		$this->view( 'caching/browser-caching-meta-box-header', array(
			'title'      => __( 'Browser Caching', 'wphb' ),
			'issues'     => $issues,
			'url'        => $check_expiry_url,
		));
	}

	/**
	 * Render enable caching metabx.
	 */
	public function caching_summary_metabox() {
		// Check if .htaccess file has rules included.
		$htaccess_issue = false;
		if ( $this->htaccess_written && in_array( false, $this->report, true ) ) {
			$htaccess_issue = true;
		}

		/** @var WP_Hummingbird_Module_Cloudflare $cf_module */
		$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$show_cf_notice = false;
		if ( ! $cf_module->is_connected() && ( ! get_site_option( 'wphb-cloudflare-dash-notice' ) && 'dismissed' !== get_site_option( 'wphb-cloudflare-dash-notice' ) ) ) {
			$show_cf_notice = true;
		}
		$cf_notice = $cf_module->has_cloudflare( true ) ? __( 'Ahoi, we’ve detected you’re using CloudFlare!', 'wphb' ) : __( 'Using CloudFlare?', 'wphb' );

		$this->view( 'caching/browser-caching-meta-box', array(
			'htaccess_issue'         => $htaccess_issue,
			'results'                => $this->report,
			'issues'                 => $this->issues,
			'human_results'          => array_map( array( 'WP_Hummingbird_Utils', 'human_read_time_diff' ), $this->report ),
			'recommended'            => WP_Hummingbird_Utils::get_recommended_caching_values(),
			'show_cf_notice'         => $show_cf_notice,
			'cf_notice'              => $cf_notice,
			'cf_server'              => $this->cf_server,
			'cf_active'              => $this->cloudflare,
			'caching_type_tooltips'  => WP_Hummingbird_Utils::get_browser_caching_types(),
		));
	}

	/**
	 * Display browser caching settings header meta box.
	 */
	public function caching_settings_metabox_header() {
		$this->view( 'caching/browser-caching-configure-meta-box-header', array(
			'title'     => __( 'Configure', 'wphb' ),
			'cf_active' => $this->cloudflare,
		));
	}

	/**
	 * Display browser caching settings meta box.
	 */
	public function caching_settings_metabox() {
		// Server code snippets.
		$snippets = array(
			'apache'    => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'apache' ),
			'litespeed' => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'LiteSpeed' ),
			'nginx'     => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'nginx' ),
			'iis'       => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'iis' ),
			'iis-7'     => WP_Hummingbird_Module_Server::get_code_snippet( 'caching', 'iis-7' ),
		);

		$htaccess_writable = WP_Hummingbird_Module_Server::is_htaccess_writable();

		$already_enabled = $this->is_caching_fully_enabled() && ! $this->htaccess_written;

		// Cloudflare deactivate URL.
		$deactivate_url = add_query_arg( array(
			'type' => 'cf-deactivate',
			'run'  => 'true',
		));
		$deactivate_url = wp_nonce_url( $deactivate_url, 'wphb-run-caching' );

		// Footer links to enable/disable automatic caching.
		$enable_link = add_query_arg( array(
			'run' => 'true',
			'enable' => 'true',
		));
		$disable_link = add_query_arg( array(
			'run' => 'true',
			'disable' => 'true',
		));

		$show_cf_notice = false;
		// Default to show Cloudflare or Apache if set up.
		$server_type = WP_Hummingbird_Module_Server::get_server_type();
		if ( $this->cloudflare ) {
			$server_type = 'cloudflare';
			// Clear cached status.
			/* @var WP_Hummingbird_Module_Caching $caching_module */
			$caching_module = WP_Hummingbird_Utils::get_module( 'caching' );
			$caching_module->clear_cache();
		} elseif ( $this->cf_server ) {
			$server_type = 'cloudflare';
			/** @var WP_Hummingbird_Module_Cloudflare $cf_module */
			$cf_module = WP_Hummingbird_Utils::get_module( 'cloudflare' );
			if ( ! ($cf_module->is_active() && $cf_module->is_connected() && $cf_module->is_zone_selected() ) ) {
				if ( get_site_option( 'wphb-cloudflare-dash-notice' ) && 'dismissed' === get_site_option( 'wphb-cloudflare-dash-notice' ) ) {
					$show_cf_notice = true;
				}
			}
		} elseif ( $htaccess_writable && $this->htaccess_written ) {
			if ( 'LiteSpeed' !== $server_type ) {
				$server_type = 'apache';
			}
		}

		$all_expiry = ( count( array_unique( $this->expires ) ) === 1 );

		$this->view( 'caching/browser-caching-configure-meta-box', array(
			'results'             => $this->report,
			'human_results'       => array_map( array( 'WP_Hummingbird_Utils', 'human_read_time_diff' ), $this->report ),
			'expires'             => $this->expires,
			'server_type'         => $server_type,
			'snippets'            => $snippets,
			'htaccess_written'    => $this->htaccess_written,
			'htaccess_writable'   => $htaccess_writable,
			'already_enabled'     => $already_enabled,
			'cf_active'           => $this->cloudflare,
			'cf_server'           => $this->cf_server,
			'cf_current'          => $this->expiration,
			'cf_disable_url'      => $deactivate_url,
			'enable_link'         => $enable_link,
			'disable_link'        => $disable_link,
			'all_expiry'          => $all_expiry,
			'show_cf_notice'      => $show_cf_notice,
			'recheck_expiry_url'  => add_query_arg( 'run', 'true' ),
		));
	}

	/**
	 * ******************
	 * GRAVATAR CACHING *
	 ********************/

	/**
	 * Disabled Gravatar caching metabox.
	 *
	 * @since 1.5.3
	 */
	public function caching_gravatar_disabled_metabox() {
		$activate_url = add_query_arg( array(
			'type' => 'gc-activate',
			'run'  => 'true',
		));
		$activate_url = wp_nonce_url( $activate_url, 'wphb-run-caching' );

		$this->view( 'caching/disabled-gravatar-meta-box', array(
			'activate_url' => $activate_url,
		));
	}

	/**
	 * Display Gravatar caching header
	 *
	 * @since 1.5.0
	 */
	public function caching_gravatar_header() {
		$purge_url = add_query_arg( array(
			'type' => 'gc-purge',
			'run'  => 'true',
		));
		$purge_url = wp_nonce_url( $purge_url, 'wphb-run-caching' );

		$this->view( 'caching/gravatar-meta-box-header', array(
			'title'     => __( 'Gravatar Caching', 'wphb' ),
			'purge_url' => $purge_url,
		));
	}

	/**
	 * Display Gravatar metabox.
	 *
	 * @since 1.5.0
	 */
	public function caching_gravatar_metabox() {
		/* @var WP_Hummingbird_Module_Gravatar $module */
		$module = WP_Hummingbird_Utils::get_module( 'gravatar' );

		$deactivate_url = add_query_arg( array(
			'type' => 'gc-deactivate',
			'run'  => 'true',
		));
		$deactivate_url = wp_nonce_url( $deactivate_url, 'wphb-run-caching' );

		$this->view( 'caching/gravatar-meta-box', array(
			'module_active'    => $module->is_active(),
			'error'            => $module->error,
			'deactivate_url'   => $deactivate_url,
		));
	}

	/**
	 * Set expiration for browser caching.
	 *
	 * @since 1.6.1
	 * @param string $type   Expiry type.
	 * @param string $value  Expiry value.
	 */
	public function caching_set_expiration( $type, $value ) {
		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return;
		}

		$type  = sanitize_text_field( wp_unslash( $type ) ); // Input var okay.
		$value = sanitize_text_field( wp_unslash( $value ) ); // Input var okay.

		/* @var WP_Hummingbird_Module_Cloudflare $caching */
		$caching = WP_Hummingbird_Utils::get_module( 'cloudflare' );
		$cf_active = $caching->is_active();

		if ( $cf_active ) {
			$frequencies = WP_Hummingbird_Utils::get_cloudflare_frequencies();
		} else {
			$frequencies = WP_Hummingbird_Utils::get_caching_frequencies();
		}

		if ( ! isset( $frequencies[ $value ] ) ) {
			die();
		}

		if ( 'all' === $type && ! $cf_active ) {
			/* @var WP_Hummingbird_Module_Caching $caching */
			$caching = WP_Hummingbird_Utils::get_module( 'caching' );
			$options = $caching->get_options();
			$options['expiry_css']        = $value;
			$options['expiry_javascript'] = $value;
			$options['expiry_media']      = $value;
			$options['expiry_images']     = $value;
		} elseif ( 'all' === $type && $cf_active ) {
			$options = $caching->get_options();
			$options['cache_expiry'] = $value;
		} else {
			/* @var WP_Hummingbird_Module_Caching $caching */
			$caching = WP_Hummingbird_Utils::get_module( 'caching' );
			$options = $caching->get_options();
			$options[ 'expiry_' . $type ] = $value;
		}

		$caching->update_options( $options );
	}

	/**
	 * Reload snippet after new expiration interval has been selected.
	 *
	 * @since 1.6.1
	 * @return array|bool
	 */
	public function caching_reload_snippet() {
		if ( ! current_user_can( WP_Hummingbird_Utils::get_admin_capability() ) ) {
			return false;
		}

		if ( ! isset( $_POST['hb_server_type'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['hb_server_type'] ) ); // Input var okay.

		$code = WP_Hummingbird_Module_Server::get_code_snippet( 'caching', $type );

		$updated_file = false;
		if ( true === $this->htaccess_written && 'apache' === $type ) {
			WP_Hummingbird_Module_Server::unsave_htaccess( 'caching' );
			$updated_file = WP_Hummingbird_Module_Server::save_htaccess( 'caching' );
		}
		$response = array(
			'type' => $type,
			'code' => $code,
			'updatedFile' => $updated_file,
		);

		return $response;
	}

	/**
	 * ******************
	 * RSS CACHING      *
	 ********************/

	/**
	 * Display Rss caching meta box.
	 *
	 * @since 1.8
	 */
	public function caching_rss_metabox() {
		/* @var WP_Hummingbird_Module_Rss $rss_module */
		$rss_module = WP_Hummingbird_Utils::get_module( 'rss' );
		$options = $rss_module->get_options();

		$url = add_query_arg( array(
			'type' => $rss_module->is_active() ? 'rss-deactivate' : 'rss-activate',
			'run'  => 'true',
		));
		$url = wp_nonce_url( $url, 'wphb-run-caching' );

		if ( $rss_module->is_active() ) {
			$this->view( 'caching/rss-meta-box', array(
				'duration' => $options['duration'],
				'url'      => $url,
			));

			return;
		}

		$this->view( 'caching/rss-disabled-meta-box', array(
			'url'      => $url,
		));
	}

}