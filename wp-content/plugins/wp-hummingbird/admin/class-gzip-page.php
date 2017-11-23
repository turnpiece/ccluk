<?php
/**
 * Class WP_Hummingbird_GZIP_Page.
 */
class WP_Hummingbird_GZIP_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Overwrites parent class render_header method.
	 *
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	public function render_header() {

		if ( isset( $_GET['htaccess-error'] ) ) {
			$this->admin_notices->show( 'error', __( 'Hummingbird could not update or write your .htaccess file. Please, make .htaccess writable or paste the code yourself.', 'wphb' ), 'error', true );
		}

		if ( isset( $_GET['gzip-enabled'] ) ) {
			$this->admin_notices->show( 'updated', __( 'Gzip enabled. Your .htaccess file has been updated', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['gzip-disabled'] ) ) {
			$this->admin_notices->show( 'updated', __( 'Gzip disabled. Your .htaccess file has been updated', 'wphb' ), 'error', true );
		}

		parent::render_header();
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

		$redirect = false;
		$enabled = false;
		$disabled = false;

		if ( isset( $_GET['enable'] ) ) {
			// Enable caching in htaccess (only for apache servers).
			$result = wphb_save_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$enabled = true;
				// Clear saved status.
				wphb_clear_gzip_cache();
				// Update cache status.
				wphb_get_gzip_status();
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['disable'] ) ) {
			// Disable caching in htaccess (only for apache servers).
			$result = wphb_unsave_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$disabled = true;
				// Clear saved status.
				wphb_clear_gzip_cache();
				// Update cache status.
				wphb_get_gzip_status();
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['run'] ) ) {
			// Force a refresh of the data.
			wphb_get_status_from_api( 'gzip' );
			$redirect = true;
		}

		if ( $redirect ) {
			$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable', 'htaccess-error', 'gzip-enabled', 'gzip-disabled' ) );
			if ( $enabled ) {
				$redirect_to = add_query_arg( 'gzip-enabled', true, $redirect_to );
			} elseif ( $disabled ) {
				$redirect_to = add_query_arg( 'gzip-disabled', true, $redirect_to );
			}
			wp_safe_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Register meta boxes for the page.
	 */
	public function register_meta_boxes() {
		$this->add_meta_box(
			'gzip-summary',
			__( 'Summary', 'wphb' ),
			array( $this, 'gzip_summary_metabox' ),
			array( $this, 'gzip_summary_metabox_header' ),
			null,
			'box-gzip-top'
		);
		$this->add_meta_box( 'gzip-settings',
			__( 'Configure', 'wphb' ),
			array( $this, 'gzip_configure_metabox' ),
			null,
			null ,
			'box-gzip-bottom'
		);
	}

	/**
	 * Overwrite parent render_inner_content method.
	 *
	 * Render content for display.
	 */
	protected function render_inner_content() {
		$server_name = wphb_get_server_type();
		$server_type = array_search( $server_name, wphb_get_servers(), true );
		$this->view( $this->slug . '-page', array(
			'server_type' => $server_type,
			'server_name' => $server_name,
		));
	}

	/**
	 * Render gzip summary metabox.
	 */
	public function gzip_summary_metabox() {
		$status = wphb_get_gzip_status();

		$htaccess_written = wphb_is_htaccess_written( 'gzip' );
		$external_problem = false;
		if ( $htaccess_written ) {
			if ( ! is_array( $status ) || ( is_array( $status ) && 3 !== count( $status ) ) || in_array( false, $status, true ) ) {
				// There must be another plugin/server config that is setting its own gzip stuff.
				$external_problem = true;
			}
		}
		$inactive_types = wphb_get_number_of_issues( 'gzip' );

		$this->view( 'gzip/summary-meta-box', array(
			'status'           => $status,
			'external_problem' => $external_problem,
			'inactive_types'   => $inactive_types,
		));
	}

	/**
	 * Render gzip summary metabox header.
	 */
	public function gzip_summary_metabox_header() {
		$recheck_url = add_query_arg( 'run', 'true' );
		$recheck_url = remove_query_arg( 'htaccess-error', $recheck_url );
		$status = wphb_get_gzip_status();
		$full_enabled = array_sum( $status ) === 3;
		$this->view( 'gzip/summary-meta-box-header', array(
			'recheck_url'  => $recheck_url,
			'title'        => __( 'Summary', 'wphb' ),
			'full_enabled' => $full_enabled,
		));
	}

	/**
	 * Check if Gzip has been already activated in server by user, not by Hummingbird
	 *
	 * @return bool
	 */
	private function _gzip_already_activated_in_server() {
		$status = wphb_get_gzip_status();
		if ( ! is_array( $status ) ) {
			$status = array();
		}

		$all_types_compressed = array_sum( $status ) === 3;
		$htaccess_written_by_hummingbird = wphb_is_htaccess_written( 'gzip' );

		$result = false;
		if ( $all_types_compressed && ! $htaccess_written_by_hummingbird ) {
			// Server had already gzip activated, Hummingbird did nothing.
			$result = true;
		}

		return $result;
	}

	/**
	 * Render gzip configure metabox.
	 */
	public function gzip_configure_metabox() {
		$show_enable_button = ! $this->_gzip_already_activated_in_server();
		$enable_link = add_query_arg( array(
			'run'    => 'true',
			'enable' => 'true',
		));
		$disable_link = add_query_arg( array(
			'run'     => 'true',
			'disable' => 'true',
		));
		$status = wphb_get_gzip_status();
		$full_enabled = array_sum( $status ) === 3;
		$snippets = array(
			'apache'    => wphb_get_code_snippet( 'gzip', 'apache' ),
			'litespeed' => wphb_get_code_snippet( 'gzip', 'LiteSpeed' ),
			'nginx'     => wphb_get_code_snippet( 'gzip', 'nginx' ),
			'iis'       => wphb_get_code_snippet( 'gzip', 'iis' ),
			'iis-7'     => wphb_get_code_snippet( 'gzip', 'iis-7' ),
		);
		$htaccess_error = false;
		if ( isset( $_GET['htaccess-error'] ) ) {
			$htaccess_error = true;
		}

		$recheck_url = add_query_arg( 'run', 'true' );
		$recheck_url = remove_query_arg( 'htaccess-error', $recheck_url );
		$this->view( 'gzip/configure-meta-box', array(
			'snippets'            => $snippets,
			'enable_link'         => $enable_link,
			'disable_link'        => $disable_link,
			'show_enable_button'  => $show_enable_button,
			'gzip_server_type'    => wphb_get_server_type(),
			'full_enabled'        => $full_enabled,
			'recheck_url'         => $recheck_url,
			'htaccess_error'      => $htaccess_error,
		));
	}

}