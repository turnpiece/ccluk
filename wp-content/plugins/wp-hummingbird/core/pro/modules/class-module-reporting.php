<?php

/**
 * Class WP_Hummingbird_Module_Reporting is used for sending out email reports after performance scans.
 */
class WP_Hummingbird_Module_Reporting extends WP_Hummingbird_Module {

	public function init() {}

	public function run() {}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * @since 1.7.1
	 */
	public function clear_cache() {}

	/**
	 * Send out an email report.
	 *
	 * @param mixed $last_report  Last report data.
	 * @param array $recipients   List of recipients.
	 *
	 * @since 1.4.5
	 */
	public static function send_email_report( $last_report, $recipients = array() ) {
		if ( WP_Hummingbird_Module_Performance::is_doing_report() ) {
			return;
		}

		$issues = WP_Hummingbird_Utils::get_number_of_issues( 'performance' );
		if ( 0 === $issues || empty( $recipients ) ) {
			return;
		}

		foreach ( $recipients as $recipient ) {
			// Prepare the parameters.
			$email   = $recipient['email'];
			/* translators: %s: Url for site */
			$subject = sprintf( __( "Here's your latest performance test results for %s", 'wphb' ), network_site_url() );
			$params  = array(
				'USER_NAME'       => $recipient['name'],
				'SCAN_PAGE_LINK'  => network_admin_url( 'admin.php?page=wphb-performance' ),
				'SITE_MANAGE_URL' => network_site_url( 'wp-admin/admin.php?page=wphb' ),
				'SITE_URL'        => wp_parse_url( network_site_url(), PHP_URL_HOST ),
				'SITE_NAME'       => get_bloginfo( 'name' ),
			);
			$email_content = self::issues_list_html( $last_report, $params );
			// Change nl to br.
			$email_content = stripslashes( $email_content );
			$no_reply_email = 'noreply@' . wp_parse_url( get_site_url(), PHP_URL_HOST );
			$headers        = array(
				'From: Hummingbird <' . $no_reply_email . '>',
				'Content-Type: text/html; charset=UTF-8',
			);

			wp_mail( $email, $subject, $email_content, $headers );
		}

	}

	/**
	 * Build issues html table.
	 *
	 * @access private
	 * @param  mixed $last_test  Latest test data.
	 * @param  array $params     Additional data for report.
	 * @return string            HTML for email.
	 * @since  1.4.5
	 */
	private static function issues_list_html( $last_test, $params ) {
		ob_start();
		self::load_template( 'index', compact( 'last_test', 'params' ) );
		return ob_get_clean();
	}

	/**
	 * Try to load a single reporting template.
	 *
	 * @param string $template  Template name. It should match the filename without extension.
	 * @param array  $args      Variables to pass to the templates.
	 */
	public static function load_template( $template, $args = array() ) {
		$dirs = apply_filters( 'wphb_reporting_templates_folders', array(
			'stylesheet' => STYLESHEETPATH . '/wphb/',
			'template'   => TEMPLATEPATH . '/wphb/',
			'plugin'     => WPHB_DIR_PATH . 'core/pro/modules/reporting/templates/',
		) );

		foreach ( (array) $dirs as $dir ) {
			$file = trailingslashit( $dir ) . "$template.php";
			if ( is_readable( $file ) ) {
				extract( $args );
				/* @noinspection PhpIncludeInspection */
				include( $file );
				break;
			}
		}
	}

}