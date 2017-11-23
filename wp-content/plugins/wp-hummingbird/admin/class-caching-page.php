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
	 * @var    bool $report
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
	 * Cloudflare status.
	 *
	 * @since  1.5.3
	 * @var    bool $cloudflare  Default false.
	 * @access private
	 */
	private $cloudflare = false;

	/**
	 * Cloudflare expiration value.
	 *
	 * TODO: maybe we can delete this, as it is used only once in the header of a meta box.
	 *
	 * @since  1.5.3
	 * @var    int $expiration Default 0.
	 * @access private
	 */
	private $expiration = 0;

	/**
	 * WP_Hummingbird_Admin_Page constructor.
	 *
	 * @param string $slug        The slug name to refer to this menu by (should be unique for this menu).
	 * @param string $page_title  The text to be displayed in the title tags of the page when the menu is selected.
	 * @param string $menu_title  The text to be used for the menu.
	 * @param bool   $parent      Parent or child.
	 * @param bool   $render      Use a callback function.
	 */
	public function __construct( $slug, $page_title, $menu_title, $parent = false, $render = true ) {
		parent::__construct( $slug, $page_title, $menu_title, $parent, $render );

		$this->tabs = array(
			'main'     => __( 'Page Caching', 'wphb' ),
			'browser'  => __( 'Browser Caching', 'wphb' ),
			'gravatar' => __( 'Gravatar Caching', 'wphb' ),
		);

		// Get expiration setting values.
		$options = wphb_get_settings();
		$this->expires = array(
			'css'        => $options['caching_expiry_css'],
			'javascript' => $options['caching_expiry_javascript'],
			'media'      => $options['caching_expiry_media'],
			'images'     => $options['caching_expiry_images'],
		);

		/**
		 * Check Cloudflare status.
		 *
		 * If Cloudflare is active, we store the values of CLoudFlare caching settings to the report variable.
		 * Else - we store the local setting in the report variable. That way we don't have to query and check
		 * later on what report to show to the user.
		 *
		 * @var WP_Hummingbird_Module_Cloudflare $cf_module
		 */
		$cf_module = wphb_get_module( 'cloudflare' );
		$this->cloudflare = wphb_cloudflare_is_active();
		if ( $this->cloudflare ) {
			$this->expiration = $cf_module->get_caching_expiration();
			// Fill the report with values from Cloudflare.
			$this->report = array_fill_keys( array_keys( $this->expires ), $this->expiration );
		} else {
			// Get latest local report.
			$this->report = wphb_get_caching_status();
		}

		// Get number of issues.
		if ( ! $this->cloudflare ) {
			$this->issues = wphb_get_number_of_issues( 'caching' );
		} elseif ( 691200 > $this->expiration ) {
			$this->issues = count( $this->report );
		}

		// We need to actually tweak these tasks.
		add_filter( 'wphb_admin_after_tab_' . $this->get_slug(), array( $this, 'after_tab' ) );
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 *
	 * @since 1.7.0
	 */
	public function on_load() {
		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

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
			$page_types = array();
			if ( isset( $_POST['page_types'] ) && is_array( $_POST['page_types'] ) ) {
				$page_types = array_keys( $_POST['page_types'] );
			}

			$cache_settings = array();
			//if ( isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ) {
				$form_data = $_POST['settings'];
				$cache_settings['logged_in']    = isset( $form_data['logged-in'] ) ? absint( $form_data['logged-in'] ) : 0;
				$cache_settings['url_queries']  = isset( $form_data['url-queries'] ) ? absint( $form_data['url-queries'] ) : 0;
				$cache_settings['clear_update'] = isset( $form_data['clear-update'] ) ? absint( $form_data['clear-update'] ) : 0;
				$cache_settings['debug_log']    = isset( $form_data['debug-log'] ) ? absint( $form_data['debug-log'] ) : 0;
			//}

			$url_strings = '';
			if ( isset( $_POST['url_strings'] ) ) {
				$url_strings = sanitize_textarea_field( wp_unslash( $_POST['url_strings'] ) ); // Input var okay.
				$url_strings = preg_split( '/[\r\n\t ]+/', $url_strings );
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

			/* @var WP_Hummingbird_Module_Page_Caching $module */
			$module = wphb_get_module( 'page-caching' );
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
			wphb_clear_caching_cache();
			wphb_get_caching_status();

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
			$result = wphb_save_htaccess( 'caching' );
			if ( $result ) {
				// Clear saved status.
				wphb_clear_caching_cache();
				// Update cache status.
				wphb_get_caching_status();
				$redirect_to = add_query_arg( 'cache-enabled', true, $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
			}
		} // End if().

		// Disable browser caching.
		if ( isset( $_GET['disable'] ) ) { // Input var ok.
			// Disable caching in htaccess (only for apache servers).
			$result = wphb_unsave_htaccess( 'caching' );
			if ( $result ) {
				// Clear saved status.
				wphb_clear_caching_cache();
				// Update cache status.
				wphb_get_caching_status();
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

		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		switch ( $type ) {
			// Activate Page Cache.
			case 'pc-activate':
				/* @var WP_Hummingbird_Module_Page_Caching $module */
				$module = wphb_get_module( 'page-caching' );
				$module->activate();
				break;
			// Deactivate Page Cache.
			case 'pc-deactivate':
				/* @var WP_Hummingbird_Module_Page_Caching $module */
				$module = wphb_get_module( 'page-caching' );
				$module->deactivate();
				break;
			// Deactivate Cloudflare.
			case 'cf-deactivate':
				wphb_cloudflare_disconnect();
				break;
			// Activate Gravatar Cache.
			case 'gc-activate':
				wphb_update_setting( 'gravatar_cache', true );
				break;
			// Deactivate Gravatar Cache.
			case 'gc-deactivate':
				wphb_update_setting( 'gravatar_cache', false );
				break;
			// Purge gravatar files.
			case 'gc-purge':
				/* @var WP_Hummingbird_Module_Gravatar $module */
				$module = wphb_get_module( 'gravatar' );
				$redirect_to = remove_query_arg( array( 'run', '_wpnonce', 'type', 'gravatars-purged', 'purge-error' ) );

				if ( $module->delete_files() ) {
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

				/* @var WP_Hummingbird_Module_Page_Caching $module */
				$module = wphb_get_module( 'page-caching' );
				$redirect_to = remove_query_arg( array( 'run', '_wpnonce', 'type', 'page-cache-purged', 'purge-error' ) );

				if ( $module->purge_cache_dir() ) {
					$redirect_to = add_query_arg( 'page-cache-purged', true, $redirect_to );
				} else {
					$redirect_to = add_query_arg( 'purge-error', true, $redirect_to );
				}
				wp_safe_redirect( $redirect_to );
				exit;
			case 'check-expiry':
				// On check expiry click force a refresh of the data.
				wphb_get_status_from_api( 'caching' );
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

		<?php
		if ( isset( $_GET['caching-updated'] ) && ! isset( $_GET['htaccess-error'] ) ) {
			if ( wphb_is_htaccess_written( 'caching' ) ) {
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
		 */

		/* @var WP_Hummingbird_Module_Page_Caching $module */
		$module = wphb_get_module( 'page-caching' );

		if ( ! $module->is_active() ) {
			$this->add_meta_box(
				'page-caching-disabled',
				__( 'Page Caching', 'wphb' ),
				array( $this, 'page_caching_disabled_metabox' ),
				null,
				null,
				'main',
				array(
					'box_class' => 'dev-box content-box content-box-one-col-center',
				)
			);
		} else {
			$this->add_meta_box(
				'page-caching',
				__( 'Page Caching', 'wphb' ),
				array( $this, 'page_caching_metabox' ),
				array( $this, 'page_caching_metabox_header' ),
				array( $this, 'page_caching_metabox_footer' ),
				'main',
				array(
					'box_content_class' => 'box-content',
				)
			);
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
			'browser',
			array(
				'box_content_class' => 'box-content',
			)
		);

		/**
		 * GRAVATAR CACHING META BOXES.
		 */

		/* @var WP_Hummingbird_Module_Gravatar $module */
		$module = wphb_get_module( 'gravatar' );

		if ( ! $module->is_active() ) {
			$this->add_meta_box(
				'gravatar-disabled',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_disabled_metabox' ),
				null,
				null,
				'gravatar',
				array(
					'box_class' => 'dev-box content-box content-box-one-col-center',
				)
			);
		} else {
			$this->add_meta_box(
				'caching-gravatar',
				__( 'Gravatar Caching', 'wphb' ),
				array( $this, 'caching_gravatar_metabox' ),
				array( $this, 'caching_gravatar_header' ),
				null,
				'gravatar',
				array(
					'box_class' => 'dev-box content-box content-box-one-col-center',
				)
			);
		}
	}

	/**
	 * Overwrite parent render_inner_content method.
	 *
	 * Render content for display.
	 */
	protected function render_inner_content() {
		$server_name = wphb_get_server_type();
		$server_type = array_search( $server_name, wphb_get_servers() , true );
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
		if ( 'browser' === $tab ) {
			if ( 0 !== $this->issues ) {
				echo '<span class="wphb-button-label wphb-button-label-yellow">' . absint( $this->issues ) . '</span>';
			} else {
				echo '<i class="hb-wpmudev-icon-tick"></i>';
			}
		} elseif ( 'gravatar' === $tab || 'main' === $tab ) {
			if ( 'gravatar' === $tab ) {
				/* @var WP_Hummingbird_Module_Gravatar $module */
				$module = wphb_get_module( 'gravatar' );
			} else {
				/* @var WP_Hummingbird_Module_Page_Caching $module */
				$module = wphb_get_module( 'page-caching' );
			}
			if ( $module->is_active() && ! is_wp_error( $module->error ) ) {
				echo '<i class="hb-wpmudev-icon-tick"></i>';
			} elseif ( is_wp_error( $module->error ) ) {
				echo '<i class="hb-wpmudev-icon-warning"></i>';
			}
		}
	}

	/**
	 * Check to see if caching is fully enabled
	 *
	 * @access private
	 * @return bool
	 */
	private function is_caching_fully_enabled() {
		$recommended = wphb_get_recommended_caching_values();

		$results = wphb_get_caching_status();

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
		));
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
		/* @var WP_Hummingbird_Module_Page_Caching $module */
		$module = wphb_get_module( 'page-caching' );

		$settings = $module->get_settings();

		$deactivate_url = add_query_arg( array(
			'type' => 'pc-deactivate',
			'run'  => 'true',
		));
		$deactivate_url = wp_nonce_url( $deactivate_url, 'wphb-run-caching' );

		$page_types = $module->get_page_types();

		$this->view( 'caching/page-caching-meta-box', array(
			'error'          => $module->error,
			'deactivate_url' => $deactivate_url,
			'settings'       => $settings,
			'pages'          => $page_types,
		) );
	}

	/**
	 * Page caching meta box header.
	 *
	 * @since 1.7.0
	 */
	public function page_caching_metabox_header() {
		$purge_url = add_query_arg( array(
			'type' => 'pc-purge',
			'run'  => 'true',
		));
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
			$issues = wphb_get_number_of_issues( 'caching' );
		} elseif ( 691200 > $this->expiration ) {
			count( $this->report );
			$issues = count( $this->report );
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
		$htaccess_written = wphb_is_htaccess_written( 'caching' );
		if ( $htaccess_written && in_array( false, $this->report, true ) ) {
			$htaccess_issue = true;
		}

		$this->view( 'caching/browser-caching-meta-box', array(
			'htaccess_issue' => $htaccess_issue,
			'results'        => $this->report,
			'issues'         => $this->issues,
			'human_results'  => array_map( 'wphb_human_read_time_diff', $this->report ),
			'recommended'    => wphb_get_recommended_caching_values(),
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
			'apache'    => wphb_get_code_snippet( 'caching', 'apache' ),
			'litespeed' => wphb_get_code_snippet( 'caching', 'LiteSpeed' ),
			'nginx'     => wphb_get_code_snippet( 'caching', 'nginx' ),
			'iis'       => wphb_get_code_snippet( 'caching', 'iis' ),
			'iis-7'     => wphb_get_code_snippet( 'caching', 'iis-7' ),
		);

		$htaccess_written = wphb_is_htaccess_written( 'caching' );
		$already_enabled = $this->is_caching_fully_enabled() && ! $htaccess_written;

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

		$expiry_selects = false;
		// Default to show Cloudflare or Apache if set up.
		$server_type = wphb_get_server_type();
		if ( $this->cloudflare ) {
			$server_type = 'cloudflare';
			// If htaccess has been written, remove it.
			if ( wphb_is_htaccess_writable() && $htaccess_written ) {
				$result = wphb_unsave_htaccess( 'caching' );
				if ( $result ) {
					// Clear cached status.
					wphb_clear_caching_cache();
					// Update with new settings.
					wphb_get_caching_status();
				}
			}
			$expiry_selects = true;
		} elseif ( wphb_is_htaccess_writable() && $htaccess_written ) {
			if ( 'LiteSpeed' !== $server_type ) {
				$server_type = 'apache';
			}
			$expiry_selects = true;
		}

		$all_expiry = ( count( array_unique( $this->expires ) ) === 1 );

		$this->view( 'caching/browser-caching-configure-meta-box', array(
			'results'           => $this->report,
			'human_results'     => array_map( 'wphb_human_read_time_diff', $this->report ),
			'expires'           => $this->expires,
			'server_type'       => $server_type,
			'snippets'          => $snippets,
			'htaccess_written'  => $htaccess_written,
			'htaccess_writable' => wphb_is_htaccess_writable(),
			'already_enabled'   => $already_enabled,
			'cf_active'         => $this->cloudflare,
			'cf_current'        => $this->expiration,
			'cf_disable_url'    => $deactivate_url,
			'enable_link'       => $enable_link,
			'disable_link'      => $disable_link,
			'all_expiry'        => $all_expiry,
			'expiry_selects'    => $expiry_selects,
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
		$module = wphb_get_module( 'gravatar' );

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
		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		$type  = sanitize_text_field( wp_unslash( $type ) ); // Input var okay.
		$value = sanitize_text_field( wp_unslash( $value ) ); // Input var okay.

		$cf_active = wphb_cloudflare_is_active();

		if ( $cf_active ) {
			$frequencies = wphb_get_caching_cloudflare_frequencies();
		} else {
			$frequencies = wphb_get_caching_frequencies();
		}

		if ( ! isset( $frequencies[ $value ] ) ) {
			die();
		}

		$options = wphb_get_settings();
		if ( 'all' === $type && ! $cf_active ) {
			$options['caching_expiry_css']        = $value;
			$options['caching_expiry_javascript'] = $value;
			$options['caching_expiry_media']      = $value;
			$options['caching_expiry_images']     = $value;
		} elseif ( 'all' === $type && $cf_active ) {
			$options['cloudflare-caching-expiry'] = $value;
		} else {
			$options[ 'caching_expiry_' . $type ] = $value;
		}

		wphb_update_settings( $options );
	}

	/**
	 * Reload snippet after new expiration interval has been selected.
	 *
	 * @since 1.6.1
	 * @return array|void
	 */
	public function caching_reload_snippet() {
		if ( ! current_user_can( wphb_get_admin_capability() ) ) {
			return;
		}

		if ( ! isset( $_POST['hb_server_type'] ) ) { // Input var okay.
			die();
		}

		$type = sanitize_text_field( wp_unslash( $_POST['hb_server_type'] ) ); // Input var okay.

		$code = wphb_get_code_snippet( 'caching', $type );

		$updated_file = false;
		if ( true === wphb_is_htaccess_written( 'caching' ) && 'apache' === $type ) {
			$updated_file = wphb_unsave_htaccess( 'caching' );
			$updated_file = wphb_save_htaccess( 'caching' );
		}
		$response = array(
			'type' => $type,
			'code' => $code,
			'updatedFile' => $updated_file,
		);

		return $response;
	}
}