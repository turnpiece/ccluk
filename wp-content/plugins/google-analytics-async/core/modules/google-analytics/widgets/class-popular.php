<?php
/**
 * The Google analytics popular contents widget class.
 *
 * @link    http://wpmudev.com
 * @since   3.2.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Analytics\Widgets
 */

namespace Beehive\Core\Modules\Google_Analytics\Widgets;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Helpers\Cache;
use Beehive\Core\Controllers\Assets;
use Beehive\Core\Utils\Abstracts\Widget;
use Beehive\Core\Modules\Google_Analytics;

/**
 * Class Popular
 *
 * @package Beehive\Core\Modules\Google_Analytics\Widgets
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
			array( 'description' => __( 'Your site\'s most popular posts.', 'ga_trans' ) )
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
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-popular-widget', array( $this, 'widget_vars' ) );

		// Register assets.
		add_filter( 'beehive_assets_get_scripts', array( $this, 'get_scripts' ), 10, 2 );

		// Add i18n strings for the locale.
		add_filter( 'beehive_i18n_get_locale_scripts', array( $this, 'setup_i18n' ), 10, 2 );
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
		$args['content'] = empty( $cache ) ? $this->cache_content() : $cache;

		// Render template.
		Google_Analytics\Views\Stats::instance()->popular_widget_content( $args );

		// Enqueue scripts.
		Assets::instance()->enqueue_script( 'beehive-popular-widget' );
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
		$args = array(
			'widget' => $this,
			'number' => isset( $instance['number'] ) ? intval( $instance['number'] ) : 10,
			'title'  => isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Most popular posts', 'ga_trans' ),
		);

		// Render template.
		Google_Analytics\Views\Stats::instance()->popular_widget_form( $args );
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
	public function widget_vars( $vars ) {
		// Can we get the stats.
		$vars['can_get_stats'] = Google_Analytics\Helper::instance()->can_get_stats();
		/**
		 * Modify no. of retries if the most popular widget request is empty.
		 *
		 * @param int Number of retries.
		 *
		 * @since 3.2.4
		 */
		$vars['retries'] = apply_filters( 'beehive_google_popular_widget_retries', 1 );

		// Statistics type.
		$vars['stats_type'] = beehive_analytics()->settings->get( 'statistics_type', 'google', false, 'ua' );

		return $vars;
	}

	/**
	 * Add localized strings that can be used in JavaScript.
	 *
	 * @param array  $strings Existing strings.
	 * @param string $script  Script name.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function setup_i18n( $strings, $script ) {
		if ( 'beehive-popular-widget' === $script ) {
			$strings['widget'] = array(
				'no_data' => __( 'No data yet.', 'ga_trans' ),
			);
		}

		return $strings;
	}

	/**
	 * Get the scripts list to register.
	 *
	 * @param array $scripts Scripts list.
	 * @param bool  $admin   Is admin assets?.
	 *
	 * @since 3.2.4
	 *
	 * @return array
	 */
	public function get_scripts( $scripts, $admin ) {
		if ( ! $admin ) {
			// Most popular widget.
			$scripts['beehive-popular-widget'] = array(
				'src'  => 'ga-popular-posts.min.js',
				'deps' => array( 'jquery' ),
			);
		}

		return $scripts;
	}

	/**
	 * Get the list of popular posts.
	 *
	 * Get from cache if possible. Or setup the list
	 * using the URLs from Google API.
	 * Please note: Even if you set count param, you may get less
	 * no. of items. This is because we emit links from other sites
	 * and if there are many links from other sites in most popular
	 * pages API response, you will get less no. of items after the
	 * emission of other sites.
	 *
	 * @since 3.2.4
	 * @since 3.4.0 Added new param v2.
	 *
	 * @param int  $count      No. of items required.
	 * @param bool $cache_only Should check only cache.
	 * @param bool $v2         Is GA4 (v2) stats.
	 *
	 * @return array
	 */
	public function get_list( $count = 0, $cache_only = false, $v2 = false ) {
		// Get the count from widget settings.
		if ( empty( $count ) ) {
			// Get settings.
			$settings = $this->get_settings();

			if ( is_array( $settings ) ) {
				// Get first item.
				$settings = reset( $settings );
				// Get settings.
				$count = isset( $settings['number'] ) ? $settings['number'] : 5;
			} else {
				$count = 5;
			}
		}

		/**
		 * Filter to change no. of items in widget.
		 *
		 * @param int $number No. of items.
		 *
		 * @since 3.2.0
		 */
		$count = apply_filters( 'beehive_google_popular_widget_items_count', $count );

		// Get cache key.
		if ( $v2 ) {
			$cache_key = Cache::cache_key( 'popular_posts_v2_' . $count );
		} else {
			$cache_key = Cache::cache_key( 'popular_posts_' . $count );
		}

		// Get list from cache.
		$list = Cache::get_transient( $cache_key );

		// If requested exclusively from cache.
		if ( $cache_only ) {
			return empty( $list ) ? array() : $list;
		}

		// Setup the list.
		if ( empty( $list ) ) {
			$list = array();

			// Get the stats instance.
			if ( $v2 ) {
				$stats = Google_Analytics\Stats\GA4::instance();
			} else {
				$stats = Google_Analytics\Stats\UA::instance();
			}

			// Get the stats.
			$urls = $stats->stats(
				gmdate( 'Y-m-d', strtotime( '-30 days' ) ),
				gmdate( 'Y-m-d', strtotime( '-1 days' ) ),
				'popular_widget'
			);

			// If list found, setup the real post data.
			if ( ! empty( $urls ) ) {
				// Setup the list.
				$list = $this->setup_list( $urls, $count );

				// Set to cache.
				if ( ! empty( $list ) ) {
					Cache::set_transient( $cache_key, $list );
				}
			}
		}

		return $list;
	}

	/**
	 * Get widget content from cache if exist.
	 *
	 * When widget is rendered, we need to render the content
	 * only if it exist in cache if the argument is set.
	 *
	 * @since 3.2.0
	 *
	 * @return string
	 */
	private function cache_content() {
		// Get the stats instance.
		$list = $this->get_list( 0, true );

		// If empty, show loading text.
		if ( empty( $list ) ) {
			return '<div id="beehive-popular-widget-loading">' . esc_html__( 'Loading...', 'ga_trans' ) . '</div>';
		}

		// Setup page list.
		$content = '<ul>';
		foreach ( $list as $item ) {
			$content .= '<li><a href="' . $item['link'] . '" title="' . $item['title'] . '">' . $item['title'] . '</a></li>';
		}
		$content .= '</ul>';

		return $content;
	}

	/**
	 * Setup the list of post data.
	 *
	 * We need to get only the posts data. Exclude all
	 * other post types.
	 *
	 * @param array $urls  Popular post urls.
	 * @param int   $count No. of items required.
	 *
	 * @since 3.2.0
	 *
	 * @return array
	 */
	private function setup_list( $urls = array(), $count = 5 ) {
		$list = array();

		if ( ! empty( $urls ) ) {
			// Post IDs.
			$post_ids = array();

			foreach ( $urls as $url ) {
				// We need only few items.
				if ( count( $post_ids ) >= $count ) {
					break;
				}

				$url = $this->process_url( $url );

				// Now try to get the post id.
				$post_id = url_to_postid( ( is_ssl() ? 'https://' : 'http://' ) . $url );

				// This page is not from this site.
				if ( ! $post_id ) {
					continue;
				}

				// Do not duplicate.
				if ( in_array( $post_id, $post_ids, true ) ) {
					continue;
				}

				// Only if a valid post.
				if ( 'post' !== get_post_type( $post_id ) ) {
					continue;
				}

				// Add to post ids.
				$post_ids[] = $post_id;

				$list[] = array(
					'link'  => get_the_permalink( $post_id ),
					'title' => get_the_title( $post_id ),
				);
			}
		}

		return $list;
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
		/**
		 * Filter hook to alter the home url before filtering.
		 *
		 * Domain mapping plugins can use this filter to add the support.
		 *
		 * @param string $url Home URL.
		 *
		 * @since 3.2.4
		 */
		$url = apply_filters( 'beehive_google_analytics_popular_widget_process_url_replace', $url );

		// We don't need protocol.
		return str_replace( array( 'http://', 'https://' ), '', $url );
	}
}