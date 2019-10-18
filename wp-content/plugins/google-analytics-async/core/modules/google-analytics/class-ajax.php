<?php

namespace Beehive\Core\Modules\Google_Analytics;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Modules\Google_Analytics;
use Beehive\Core\Utils\Abstracts\Admin_Ajax;

/**
 * The ajax functions class for the module.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Ajax extends Admin_Ajax {

	/**
	 * Initialize the class by registering all ajax calls.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Google stats data.
		add_action( 'wp_ajax_beehive_google_stats_dashboard_data', [ $this, 'dashboard_stats_data' ] );

		if ( Google_Analytics\Helper::instance()->can_get_stats( $this->is_network() ) ) {
			// All stats data.
			add_action( 'wp_ajax_beehive_google_stats_page_data', [ $this, 'stats_page_data' ] );

			// Update post widget content.
			add_action( 'wp_ajax_beehive_google_stats_post_data', [ $this, 'post_stats_data' ] );

			// Front end widget content.
			add_action( 'wp_ajax_beehive_popular_widget_content', [ $this, 'popular_widget_content' ] );

			// Front end widget content when user is not logged in.
			add_action( 'wp_ajax_nopriv_beehive_popular_widget_content', [ $this, 'popular_widget_content' ] );
		}
	}

	/**
	 * Get contents for popular posts widget.
	 *
	 * Get the most popular posts based on sessions.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function popular_widget_content() {
		// Popular pages widget instance.
		$popular = Widgets\Popular::instance();

		// Get the stats content.
		$content = $popular->content( false, $exception );

		// Send success response.
		if ( ! empty( $content ) ) {
			wp_send_json_success( $content );
		} else {
			// Error response.
			$this->send_json_error( $exception );
		}
	}

	/**
	 * Get Google Analytics reporting data.
	 *
	 * This method can be used to get analytics data using ajax.
	 * When period is changed, we will get the whole reporting data
	 * and store them in cache, and only return the required data.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function dashboard_stats_data() {
		// Security check.
		$this->security_check( true, 'analytics' );

		// Continue only if action is set.
		$this->required_check( [ 'from', 'to' ] );

		// Get the stats instance.
		$stats = Google_Analytics\Stats::instance();

		// Get stats.
		$stats = $stats->stats(
			sanitize_text_field( $_REQUEST['from'] ),
			sanitize_text_field( $_REQUEST['to'] ),
			'dashboard',
			$this->is_network(),
			false,
			false,
			$exception
		);

		// Send success response.
		if ( ! empty( $stats ) ) {
			wp_send_json_success( $stats );
		} else {
			$this->send_json_error( $exception );
		}
	}

	/**
	 * Get Google Analytics reporting data for the post.
	 *
	 * This method can be used to get analytics data using ajax
	 * for the async method.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function post_stats_data() {
		// Security check.
		$this->security_check( true, 'analytics' );

		// Continue only if action is set.
		$this->required_check( [ 'post' ] );

		// Get the stats instance.
		$stats = Google_Analytics\Stats::instance();

		// Get stats.
		$stats = $stats->post_stats(
			(int) $_REQUEST['post'],
			date( 'Y-m-d', strtotime( '-30 days' ) ),
			date( 'Y-m-d', strtotime( '-1 days' ) ),
			false,
			false,
			$exception
		);

		// Send success response.
		if ( ! empty( $stats ) ) {
			wp_send_json_success( $stats );
		} else {
			$this->send_json_error( $exception );
		}
	}

	/**
	 * Get Google Analytics reporting data for the stats page.
	 *
	 * This method can be used to get analytics data using ajax
	 * for the async method.
	 *
	 * @since 3.2.0
	 *
	 * @return void JSON response.
	 */
	public function stats_page_data() {
		// Security check.
		$this->security_check( true, 'analytics' );

		// Continue only if action is set.
		$this->required_check( [ 'from', 'to' ] );

		// Get the stats instance.
		$stats = Google_Analytics\Stats::instance();

		// Get stats.
		$stats = $stats->stats(
			sanitize_text_field( $_REQUEST['from'] ),
			sanitize_text_field( $_REQUEST['to'] ),
			'stats',
			$this->is_network(),
			false,
			false,
			$exception
		);

		// Send success response.
		if ( ! empty( $stats ) ) {
			wp_send_json_success( $stats );
		} else {
			$this->send_json_error( $exception );
		}
	}
}