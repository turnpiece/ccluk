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
			$this->show_notice( 'error', __( 'Hummingbird could not update or write your .htaccess file. Please, make .htaccess writable or paste the code yourself.', 'wphb' ), 'error', true );
		}

		if ( isset( $_GET['gzip-enabled'] ) ) {
			$this->show_notice( 'updated', __( 'Gzip enabled. Your .htaccess file has been updated', 'wphb' ), 'success', true );
		}

		if ( isset( $_GET['gzip-disabled'] ) ) {
			$this->show_notice( 'updated', __( 'Gzip disabled. Your .htaccess file has been updated', 'wphb' ), 'error', true );
		}

		parent::render_header();
	}

	/**
	 * Register meta boxes for the page.
	 */
	public function register_meta_boxes() {
		$redirect = false;
		$enabled = false;
		$disabled = false;

		if ( isset( $_GET['enable'] ) && current_user_can( wphb_get_admin_capability() ) ) {
			// Enable caching in htaccess (only for apache servers).
			$result = wphb_save_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$enabled = true;
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['disable'] ) && current_user_can( wphb_get_admin_capability() ) ) {
			// Disable caching in htaccess (only for apache servers).
			$result = wphb_unsave_htaccess( 'gzip' );
			if ( $result ) {
				$redirect = true;
				$disabled = true;
			} else {
				$redirect_to = remove_query_arg( array( 'run', 'enable', 'disable' ) );
				$redirect_to = add_query_arg( 'htaccess-error', true, $redirect_to );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		if ( isset( $_GET['run'] ) && current_user_can( wphb_get_admin_capability() ) ) {
			// Force a refresh of the data.
			wphb_get_gzip_status( true );
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

		$show_enable_button = ! $this->_gzip_already_activated_in_server();
		$footer_class = ! $show_enable_button ? '' : 'box-footer buttons buttons-on-left';
		$this->add_meta_box( 'gzip-summary', __( 'Summary', 'wphb' ), array( $this, 'gzip_summary_metabox' ), array( $this, 'gzip_summary_metabox_header' ), null, 'box-gzip-left' );
		$this->add_meta_box( 'gzip-enable', __( 'Enable GZIP', 'wphb' ), array( $this, 'gzip_enable_metabox' ), array( $this, 'gzip_enable_metabox_header' ), array( $this, 'gzip_enable_metabox_footer' ) , 'box-gzip-right', array(
			'box_footer_class' => $footer_class,
		));
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
		if ( false === $status ) {
			// Force only when we don't have any data yet.
			$status = wphb_get_gzip_status( true );
		}

		$htaccess_written = wphb_is_htaccess_written( 'gzip' );
		$external_problem = false;
		if ( $htaccess_written ) {
			if ( ! is_array( $status ) || ( is_array( $status ) && 3 !== count( $status ) ) || in_array( false, $status, true ) ) {
				// There must be another plugin/server config that is setting its own gzip stuff.
				$external_problem = true;
			}
		}

		$this->view( 'gzip/summary-meta-box', array(
			'status'           => $status,
			'external_problem' => $external_problem,
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
	 * Render enable gzip metabox.
	 */
	public function gzip_enable_metabox() {
		$snippets = array(
			'apache'    => wphb_get_code_snippet( 'gzip', 'apache' ),
			'litespeed' => wphb_get_code_snippet( 'gzip', 'LiteSpeed' ),
			'nginx'     => wphb_get_code_snippet( 'gzip', 'nginx' ),
			'iis'       => wphb_get_code_snippet( 'gzip', 'iis' ),
			'iis-7'     => wphb_get_code_snippet( 'gzip', 'iis-7' ),
		);

		$htaccess_written = wphb_is_htaccess_written( 'gzip' );
		$htaccess_writable = wphb_is_htaccess_writable();

		$gzip_already_active = $this->_gzip_already_activated_in_server();

		$status = wphb_get_gzip_status();
		$full_enabled = array_sum( $status ) === 3;

		if ( $full_enabled ) {
			$this->view( 'gzip/enabled-meta-box' );
		} else {
			$this->view( 'gzip/enable-meta-box',
				array(
					'snippets'            => $snippets,
					'htaccess_written'    => $htaccess_written,
					'htaccess_writable'   => $htaccess_writable,
					'gzip_already_active' => $gzip_already_active,
				)
			);
		}
	}

	/**
	 * Render enable gzip header metabox.
	 */
	public function gzip_enable_metabox_header() {
		$status = wphb_get_gzip_status();
		$full_enabled = array_sum( $status ) === 3;
		$this->view( 'gzip/code-snippet-meta-box-header', array(
			'title'            => __( 'Enable GZIP', 'wphb' ),
			'gzip_server_type' => wphb_get_server_type(),
			'full_enabled'     => $full_enabled,
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
	 * Render enable gzip footer metabox.
	 */
	public function gzip_enable_metabox_footer() {
		$show_enable_button = ! $this->_gzip_already_activated_in_server();
		$enable_link = add_query_arg( array(
			'run'    => 'true',
			'enable' => 'true',
		));
		$disable_link = add_query_arg( array(
			'run'     => 'true',
			'disable' => 'true',
		));
		$this->view( 'gzip/enable-meta-box-footer', array(
			'server_type'        => wphb_get_server_type(),
			'enable_link'        => $enable_link,
			'disable_link'       => $disable_link,
			'show_enable_button' => $show_enable_button,
		));
	}

}