<?php
/**
 * The Hustle integration for GTM.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager\Integrations
 */

namespace Beehive\Core\Modules\Google_Tag_Manager\Integrations;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Tag_Manager\Helper;

/**
 * Class Hustle
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Integrations
 */
class Hustle extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// Add integration script.
		if ( class_exists( 'Opt_In' ) ) {
			add_action( 'beehive_gtm_frontend_inline_scripts_footer', array( $this, 'form_script' ), 10, 2 );
		}
	}

	/**
	 * Add hustle script for inline rendering.
	 *
	 * @param array $scripts Scripts array.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function form_script( $scripts, $network ) {
		// Check if integration is enabled.
		$enabled = Helper::is_enabled( 'hustle_leads', $network ) && Helper::can_output_script( $network );

		// Data layer name.
		$datalayer = wp_strip_all_tags( Helper::get_datalyer_name( $network ) );

		/**
		 * Filter hook to disable the hustle form integration.
		 *
		 * @param bool   $enabled   Is enabled?
		 * @param bool   $network   Network flag.
		 * @param string $datalayer Data layer name.
		 *
		 * @since 3.3.0
		 */
		if ( apply_filters( 'beehive_google_enable_gtm_hustle_form', $enabled, $network, $datalayer ) ) {
			// Add Hustle script.
			$scripts['hustle_form'] = 'jQuery(".hustle-layout-form").on("hustle:module:submit:success", function () {
				data = jQuery(this).serializeArray().reduce(function(obj, item) {
					obj[item.name] = item.value;
					return obj;
				}, {});
				window.' . $datalayer . '.push({
					event: "beehive.hustleModuleSubmit",
					formData: data,
				})
			})';
		}

		return $scripts;
	}
}