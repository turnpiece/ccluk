<?php
/**
 * Class WP_Hummingbird_GZIP_Page.
 */
class WP_Hummingbird_GZIP_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Check if .htaccess is written by the module.
	 *
	 * @var bool $htaccess_written
	 */
	private $htaccess_written = false;

	/**
	 * Gzip status array.
	 *
	 * @since 1.8
	 *
	 * @var array $status
	 */
	private $status = array();

	/**
	 * Overwrites parent class render_header method.
	 *
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	public function render_header() {
		if ( isset( $_GET['htaccess-error'] ) ) { // Input var ok.
			$this->admin_notices->show( 'error', __( 'Hummingbird could not update or write your .htaccess file. Please, make .htaccess writable or paste the code yourself.', 'wphb' ), 'error' );
		}

		if ( isset( $_GET['gzip-enabled'] ) ) { // Input var ok.
			$this->admin_notices->show( 'updated', __( 'Gzip enabled. Your .htaccess file has been updated', 'wphb' ), 'success' );
		}

		if ( isset( $_GET['gzip-disabled'] ) ) { // Input var ok.
			$this->admin_notices->show( 'updated', __( 'Gzip disabled. Your .htaccess file has been updated', 'wphb' ), 'success' );
		}

		parent::render_header();
	}

	/**
	 * Function triggered when the page is loaded before render any content.
	 *
	 * @since 1.7.0
	 */
	public function on_load() {
		$this->htaccess_written = WP_Hummingbird_Module_Server::is_htaccess_written( 'gzip' );
		$this->status = WP_Hummingbird_Utils::get_status( 'gzip' );

		$redirect = false;
		$enabled = false;
		$disabled = false;

		if ( isset( $_GET['enable'] ) ) { // Input var ok.
			// Enable caching in htaccess (only for apache servers).
			$result = WP_Hummingbird_Module_Server::save_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$enabled = true;

				// Clear saved status.
				/* @var WP_Hummingbird_Module_GZip $gzip_module */
				$gzip_module = WP_Hummingbird_Utils::get_module( 'gzip' );
				$gzip_module->clear_cache();
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['disable'] ) ) { // Input var ok.
			// Disable caching in htaccess (only for apache servers).
			$result = WP_Hummingbird_Module_Server::unsave_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$disabled = true;

				// Clear saved status.
				/* @var WP_Hummingbird_Module_GZip $gzip_module */
				$gzip_module = WP_Hummingbird_Utils::get_module( 'gzip' );
				$gzip_module->clear_cache();
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['run'] ) ) { // Input var ok.
			// Force a refresh of the data.
			WP_Hummingbird_Utils::get_status( 'gzip', true );
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
			null,
			'box-gzip-bottom'
		);
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
	 * Render gzip summary metabox.
	 */
	public function gzip_summary_metabox() {
		$inactive_types = WP_Hummingbird_Utils::get_number_of_issues( 'gzip', $this->status );

		$this->view( 'gzip/summary-meta-box', array(
			'status'           => $this->status,
			'external_problem' => WP_Hummingbird_Utils::get_module( 'gzip' )->check_gzip_issues(),
			'inactive_types'   => $inactive_types,
		));
	}

	/**
	 * Render gzip summary metabox header.
	 */
	public function gzip_summary_metabox_header() {
		$recheck_url = add_query_arg( 'run', 'true' );
		$recheck_url = remove_query_arg( 'htaccess-error', $recheck_url );
		$full_enabled = array_sum( $this->status ) === 3;
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
		$result = false;
		if ( 3 === array_sum( $this->status ) && ! $this->htaccess_written ) {
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

		$full_enabled = array_sum( $this->status ) === 3;
		$snippets = array(
			'apache'    => WP_Hummingbird_Module_Server::get_code_snippet( 'gzip', 'apache' ),
			'litespeed' => WP_Hummingbird_Module_Server::get_code_snippet( 'gzip', 'LiteSpeed' ),
			'nginx'     => WP_Hummingbird_Module_Server::get_code_snippet( 'gzip', 'nginx' ),
			'iis'       => WP_Hummingbird_Module_Server::get_code_snippet( 'gzip', 'iis' ),
		);
		$htaccess_error = false;
		if ( isset( $_GET['htaccess-error'] ) ) { // Input var ok.
			$htaccess_error = true;
		}

		$htaccess_writable = WP_Hummingbird_Module_Server::is_htaccess_writable();

		$recheck_url = add_query_arg( 'run', 'true' );
		$recheck_url = remove_query_arg( 'htaccess-error', $recheck_url );
		$this->view( 'gzip/configure-meta-box', array(
			'snippets'            => $snippets,
			'enable_link'         => $enable_link,
			'disable_link'        => $disable_link,
			'show_enable_button'  => $show_enable_button,
			'gzip_server_type'    => WP_Hummingbird_Module_Server::get_server_type(),
			'full_enabled'        => $full_enabled,
			'recheck_url'         => $recheck_url,
			'htaccess_error'      => $htaccess_error,
			'htaccess_writable'   => $htaccess_writable,
			'htaccess_written'    => $this->htaccess_written,
		));
	}

}