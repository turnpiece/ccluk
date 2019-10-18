<?php

namespace Beehive\Core\Modules\Google_Analytics\Widgets;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Widget;
use Beehive\Core\Modules\Google_Analytics\Stats;
use Beehive\Core\Modules\Google_Analytics\Views\Stats as Stats_View;

/**
 * The Google analytics popular contents widget class.
 *
 * @link   http://premium.wpmudev.org
 * @since  3.2.0
 *
 * @author Joel James <joel@incsub.com>
 */
class Popular extends Widget {

	/**
	 * Widget unique ID.
	 *
	 * @var string
	 *
	 * @since 3.2.0
	 */
	public $id = 'beehive_google_most_popular';

	/**
	 * Widget constructor.
	 *
	 * Construct the custom widget with required options.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		parent::__construct(
			$this->id,
			__( 'Most popular posts', 'ga_trans' ),
			[ 'description' => __( 'Your site\'s most popular posts.', 'ga_trans' ) ]
		);

		$this->init();
	}

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Setup vars for the front end widget.
		add_filter( 'beehive_google_popular_widget_localize_vars', [ $this, 'script_vars' ] );
	}

	/**
	 * Public view of the widget.
	 *
	 * Displays the widget based on the contents of the included template.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Values of the widget.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// Add instance to arguments.
		$args['widget'] = $instance;

		// Get cache first.
		$cache = wp_cache_get( $this->id, 'widget' );

		// Widget content.
		$args['content'] = empty( $cache ) ? $this->content( true ) : $cache;

		// Render template.
		Stats_View::instance()->popular_widget_content( $args );

		// Enqueue scripts.
		wp_enqueue_script( 'beehive_popular_widget' );
	}

	/**
	 * Show admin form for the widget in WP backend.
	 *
	 * Displays the administrative view of the form and includes the options
	 * for the instance of the widget as arguments passed into the function.
	 *
	 * @param array $instance Thhe options for the widget.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$args = [
			'widget' => $this,
			'number' => isset( $instance['number'] ) ? intval( $instance['number'] ) : 10,
			'title'  => isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Most popular posts', 'ga_trans' ),
		];

		// Render template.
		Stats_View::instance()->popular_widget_form( $args );
	}

	/**
	 * Setup script vars for the widget.
	 *
	 * @param array $vars Script vars.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	public function script_vars( $vars ) {
		// Stats are required only when widget is active.
		if ( is_active_widget( false, false, $this->id_base, true ) ) {
			// Check if stats available from cache.
			$stats = $this->stats( true );

			// If not available, request to load via ajax.
			if ( empty( $stats ) ) {
				$vars['async_load_popular_stats'] = true;
			} else {
				$vars['stats'] = $this->stats( true );
			}
		}

		return $vars;
	}

	/**
	 * Get widget content from cache if exist.
	 *
	 * When widget is rendered, we need to render the content
	 * only if it exist in cache if the argument is set.
	 *
	 * @param bool            $cache_only Only from cache.
	 * @param \Exception|bool $exception  Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	public function content( $cache_only = false, &$exception = false ) {
		$content = '';

		// Get the stats.
		$stats = $this->stats( $cache_only, $exception );

		// Not found in cache, so set a loading message as content.
		if ( $cache_only && empty( $stats ) ) {
			$content = '<li>' . __( 'Loading...', 'ga_trans' ) . '</li>';
		} elseif ( ! empty( $stats ) ) {
			// Post IDs.
			$post_ids = [];

			// Get settings.
			$settings = $this->get_settings();

			if ( is_array( $settings ) ) {
				// Get first item.
				$settings = reset( $settings );
				// Get settings.
				$number = isset( $settings['number'] ) ? $settings['number'] : 5;
			} else {
				$number = 5;
			}

			/**
			 * Filter to change no. of items in widget.
			 *
			 * @param int $number No. of items.
			 *
			 * @since 3.2.0
			 */
			$number = apply_filters( 'beehive_google_popular_widget_items_count', $number );

			foreach ( $stats['pages'] as $url ) {
				// We need only few items.
				if ( count( $post_ids ) >= $number ) {
					break;
				}

				$url = $this->process_url( $url );

				// Now try to get the post id.
				$post_id = url_to_postid( ( is_ssl() ? 'https://' : 'http://' ) . $url );

				// This page is not from this site.
				if ( ! $post_id ) {
					continue;
				}

				// Only if a valid post.
				if ( 'post' !== get_post_type( $post_id ) ) {
					continue;
				}

				// Do not duplicate.
				if ( in_array( $post_id, $post_ids ) ) {
					continue;
				}

				// Add to post ids.
				$post_ids[] = $post_id;

				// Get the content.
				$content .= '<li><a href="' . get_the_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a></li>';
			}

			// Set cache.
			wp_cache_set( $this->id, $content, 'widget' );
		} else {
			// Stats not found, let the user know.
			$content = '<li>' . __( 'No data yet.', 'ga_trans' ) . '</li>';
		}

		return $content;
	}

	/**
	 * Process the url for the widget post links.
	 *
	 * @param string $url Post url.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function process_url( $url ) {
		global $dm_map;

		// Add Domain Mapping support.
		if ( method_exists( $dm_map, 'domain_mapping_siteurl' ) ) {
			// Get mapped url without protocol.
			$mapped_url = str_replace( [
				'http://',
				'https://',
			], '', $dm_map->domain_mapping_siteurl( home_url() ) );

			// Get home url without protocol.
			$home_url = str_replace( array( 'http://', 'https://' ), '', home_url() );

			// Replace it with mapped url.
			$url = str_replace( $mapped_url, $home_url, $url );
		} else {
			// We don't need protocol.
			$url = str_replace( [ 'http://', 'https://' ], '', $url );
		}

		return $url;
	}

	/**
	 * Get stats data from Google.
	 *
	 * This gets top pages list stats for the site.
	 * This will work only if the current site is selected
	 * in Google account.
	 *
	 * @param bool            $cache_only Should get from cache only.
	 * @param \Exception|bool $exception  Exception if any.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function stats( $cache_only = false, &$exception = false ) {
		// Stats instance.
		$stats = Stats::instance();

		// Get top pages.
		return $stats->stats(
			date( 'Y-m-d', strtotime( '-30 days' ) ),
			date( 'Y-m-d' ),
			'popular_widget',
			false,
			false,
			$cache_only,
			$exception
		);
	}
}