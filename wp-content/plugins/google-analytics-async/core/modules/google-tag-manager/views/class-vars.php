<?php
/**
 * The script variables view class for the Tag Manager module.
 *
 * @link    http://premium.wpmudev.org
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */

namespace Beehive\Core\Modules\Google_Tag_Manager\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Tag_Manager\Helper;
use Beehive\Core\Modules\Google_Tag_Manager\Integrations\Forminator;

/**
 * Class Locale
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */
class Vars extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// Setup vars for the scripts.
		add_filter( 'beehive_google_gtm_datalayer_vars', array( $this, 'post_items' ), 10, 2 );
		add_filter( 'beehive_google_gtm_datalayer_vars', array( $this, 'visitor_items' ), 10, 2 );
		add_filter( 'beehive_google_gtm_datalayer_vars', array( $this, 'custom_items' ), 10, 2 );

		// Common vars.
		add_filter( 'beehive_assets_scripts_common_localize_vars', array( $this, 'common_vars' ) );

		// Setup vars required for settings.
		add_filter( 'beehive_assets_scripts_localize_vars_beehive-tag-manager', array( $this, 'settings_vars' ) );
	}

	/**
	 * Set post related datalayer vars for GTM.
	 *
	 * @param array $vars    Existing vars.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function post_items( $vars, $network ) {
		global $post;

		// Names list.
		$names = beehive_analytics()->settings->get( 'variables', 'gtm', $network, array() );

		if ( is_singular() ) {
			// Current page/post id.
			if ( Helper::is_enabled( 'post_id', $network ) ) {
				$vars[ $this->get_name( 'post_id', $names ) ] = $post->ID;
			}

			// Current page/post title.
			if ( Helper::is_enabled( 'post_title', $network ) ) {
				$vars[ $this->get_name( 'post_title', $names ) ] = $post->post_title;
			}

			// Current post type.
			if ( Helper::is_enabled( 'post_type', $network ) ) {
				$vars[ $this->get_name( 'post_type', $names ) ] = $post->post_type;
			}

			// Current page/post created date.
			if ( Helper::is_enabled( 'post_date', $network ) ) {
				$vars[ $this->get_name( 'post_date', $names ) ] = get_the_date( 'Y-m-d' );
			}

			// Current page/post author id.
			if ( Helper::is_enabled( 'post_author', $network ) ) {
				$vars[ $this->get_name( 'post_author', $names ) ] = $post->post_author;
			}

			// Current page/post author name.
			if ( Helper::is_enabled( 'post_author_name', $network ) ) {
				$author = get_userdata( $post->post_author );
				// Set the name.
				$vars[ $this->get_name( 'post_author_name', $names ) ] = $author->display_name;
			}

			// Current page/post categories list.
			if ( Helper::is_enabled( 'post_categories', $network ) ) {
				// Get current post categories.
				$cats = get_the_category();
				if ( $cats ) {
					$cat_slugs = array();
					// We need only slugs.
					foreach ( $cats as $cat ) {
						$cat_slugs[] = $cat->slug;
					}

					$vars[ $this->get_name( 'post_categories', $names ) ] = $cat_slugs;
				}
			}

			// Current page/post tags.
			if ( Helper::is_enabled( 'post_tags', $network ) ) {
				// Get current post categories.
				$tags = get_the_tags();
				if ( $tags ) {
					$tag_slugs = array();
					// We need only slugs.
					foreach ( $tags as $tag ) {
						$tag_slugs[] = $tag->slug;
					}

					$vars[ $this->get_name( 'post_tags', $names ) ] = $tag_slugs;
				}
			}
		}

		return $vars;
	}

	/**
	 * Set visitor related datalayer vars for GTM.
	 *
	 * Vars related to the currently logged in user.
	 *
	 * @param array $vars    Existing vars.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function visitor_items( $vars, $network ) {
		// Names list.
		$names = beehive_analytics()->settings->get( 'visitors', 'gtm', $network, array() );

		// Current page/post id.
		if ( Helper::is_enabled( 'login_status', $network ) ) {
			$vars[ $this->get_name( 'login_status', $names ) ] = is_user_logged_in() ? 'logged-in' : 'logged-out';
		}

		// Only if user logged in.
		if ( is_user_logged_in() ) {
			// Get current user.
			$user = wp_get_current_user();

			// Set the user ID.
			if ( Helper::is_enabled( 'user_id', $network ) ) {
				$vars[ $this->get_name( 'user_id', $names ) ] = empty( $user->roles[0] ) ? array() : $user->roles[0];
			}

			// Set the user role.
			if ( Helper::is_enabled( 'user_role', $network ) ) {
				$vars[ $this->get_name( 'user_role', $names ) ] = get_current_user_id();
			}

			// Set the user name.
			if ( Helper::is_enabled( 'user_name', $network ) ) {
				$vars[ $this->get_name( 'user_name', $names ) ] = $user->user_login;
			}

			// Set the user email.
			if ( Helper::is_enabled( 'user_email', $network ) ) {
				$vars[ $this->get_name( 'user_email', $names ) ] = $user->user_email;
			}

			// Set the user created date.
			if ( Helper::is_enabled( 'user_creation_date', $network ) ) {
				$vars[ $this->get_name( 'user_creation_date', $names ) ] = $user->user_registered;
			}
		}

		return $vars;
	}

	/**
	 * Set custom datalayer vars for GTM.
	 *
	 * All custom vars should have proper name and value before
	 * appearing in front end.
	 *
	 * @param array $vars    Existing vars.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function custom_items( $vars, $network ) {
		// Custom items.
		$items = beehive_analytics()->settings->get( 'custom', 'gtm', $network, array() );

		if ( empty( $items ) ) {
			return $vars;
		}

		foreach ( $items as $item ) {
			// Both name and value should not be empty.
			if ( ! empty( $item['name'] ) && ! empty( $item['value'] ) ) {
				$vars[ $item['name'] ] = $item['value'];
			}
		}

		return $vars;
	}

	/**
	 * Get the assigned name for a variable.
	 *
	 * If custom variable name is given for a variable,
	 * get it instead of default one.
	 *
	 * @param string $name      Name of the variable.
	 * @param array  $variables Variables array.
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	private function get_name( $name, $variables ) {
		// Get the name.
		$name = empty( $variables[ $name ] ) ? $name : $variables[ $name ];

		/**
		 * Filter hook to alter the name of the variable for dataLayer.
		 *
		 * @param string $name Name.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_vars_get_name', $name );
	}

	/**
	 * Commons vars added from GTM modules.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function common_vars( $vars ) {
		// Setup URLs.
		$vars['urls']['gtm_account']  = Helper::settings_url( 'account', $this->is_network() );
		$vars['urls']['gtm_settings'] = Helper::settings_url( 'settings', $this->is_network() );

		return $vars;
	}

	/**
	 * Set settings page vars for GTM.
	 *
	 * @param array $vars Existing vars.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function settings_vars( $vars ) {
		// Integrations.
		$vars['integrations'] = array(
			'hustle_active'        => class_exists( 'Opt_In' ),
			'forminator_active'    => class_exists( 'Forminator' ),
			'forminator_supported' => Forminator::is_supported( $this->is_network() ),
		);

		return $vars;
	}
}