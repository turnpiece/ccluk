<?php
/**
 * The Forminator integration for GTM.
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
 * Class Forminator
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Integrations
 */
class Forminator extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.0
	 *
	 * @return void
	 */
	public function init() {
		// Add integration scripts.
		if ( class_exists( 'Forminator' ) ) {
			add_action( 'beehive_gtm_frontend_inline_scripts_footer', array( $this, 'form_script' ), 10, 2 );
			add_action( 'beehive_gtm_frontend_inline_scripts_footer', array( $this, 'poll_script' ), 10, 2 );
			add_action( 'beehive_gtm_frontend_inline_scripts_footer', array( $this, 'quiz_script' ), 10, 2 );
		}
	}

	/**
	 * Add Forminator form script for inline rendering.
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
		$enabled = Helper::is_enabled( 'forminator_forms', $network ) && Helper::can_output_script( $network );

		// Data layer name.
		$datalayer = wp_strip_all_tags( Helper::get_datalyer_name( $network ) );

		/**
		 * Filter hook to disable the forminator form integration.
		 *
		 * @param bool   $enabled   Is enabled?.
		 * @param bool   $network   Network flag.
		 * @param string $datalayer Data layer name.
		 *
		 * @since 3.3.0
		 */
		if ( apply_filters( 'beehive_gtm_enable_forminator_form', $enabled, $network, $datalayer ) ) {
			// Format the vars.
			$fields = wp_json_encode( $this->excluded_fields( 'form', $network ), JSON_UNESCAPED_UNICODE );

			// Add form success event.
			$scripts['forminator_form'] = 'jQuery("body").on("forminator:form:submit:success", ".forminator-custom-form", function(e, data) {
				let formData = {};
				let excluded = ' . $fields . ';
				for ( const [key, value]  of data.entries() ) {
					if ( !excluded.includes( key ) ) {
						formData[key] = value;
					}
				}
				window.' . $datalayer . '.push({
					event: "beehive.forminatorFormSubmit",
					formData: formData
				})
			})';
		}

		return $scripts;
	}

	/**
	 * Add Forminator poll script for inline rendering.
	 *
	 * @param array $scripts Scripts array.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function poll_script( $scripts, $network ) {
		// Check if integration is enabled.
		$enabled = Helper::is_enabled( 'forminator_polls', $network ) && Helper::can_output_script( $network );

		// Make sure it's supported.
		$enabled = $enabled && self::is_supported( $network );

		// Data layer name.
		$datalayer = wp_strip_all_tags( Helper::get_datalyer_name( $network ) );

		/**
		 * Filter hook to disable the forminator form integration.
		 *
		 * @param bool   $enabled   Is enabled?.
		 * @param bool   $network   Network flag.
		 * @param string $datalayer Data layer name.
		 *
		 * @since 3.3.0
		 */
		if ( apply_filters( 'beehive_gtm_enable_forminator_poll', $enabled, $network, $datalayer ) ) {
			// Format the vars.
			$fields = wp_json_encode( $this->excluded_fields( 'poll', $network ), JSON_UNESCAPED_UNICODE );

			$scripts['forminator_poll'] = 'jQuery("body").on("forminator:poll:submit:success", ".forminator-poll", function(e, data, formData) {
				let pollData = {};
				let excluded = ' . $fields . ';
				for ( const [key, value]  of formData.entries() ) {
					if ( !excluded.includes( key ) ) {
						pollData[key] = value;
					}
				}
				window.' . $datalayer . '.push({
					event: "beehive.forminatorPollSubmit",
					pollData: pollData
				})
			})';
		}

		return $scripts;
	}

	/**
	 * Add Forminator quiz script for inline rendering.
	 *
	 * @param array $scripts Scripts array.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function quiz_script( $scripts, $network ) {
		// Check if integration is enabled.
		$enabled = Helper::is_enabled( 'forminator_quizzes', $network ) && Helper::can_output_script( $network );

		// Make sure it's supported.
		$enabled = $enabled && self::is_supported( $network );

		// Data layer name.
		$datalayer = wp_strip_all_tags( Helper::get_datalyer_name( $network ) );

		/**
		 * Filter hook to disable the forminator form integration.
		 *
		 * @param bool   $enabled   Is enabled?.
		 * @param bool   $network   Network flag.
		 * @param string $datalayer Data layer name.
		 *
		 * @since 3.3.0
		 */
		if ( apply_filters( 'beehive_gtm_enable_forminator_quiz', $enabled, $network, $datalayer ) ) {
			// Format the vars.
			$fields = wp_json_encode( $this->excluded_fields( 'quiz', $network ), JSON_UNESCAPED_UNICODE );

			$scripts['forminator_quiz'] = 'jQuery("body").on("forminator:quiz:submit:success", ".forminator-quiz", function(e, data, formData) {
				let quizData = {};
				let excluded = ' . $fields . ';
				for ( const [key, value]  of formData.entries() ) {
					if ( !excluded.includes( key ) ) {
						quizData[key] = value;
					}
				}
				window.' . $datalayer . '.push({
					event: "beehive.forminatorQuizSubmit",
					quizData: quizData
				})
			})';
		}

		return $scripts;
	}

	/**
	 * Get the list of fields to exclude from the custom events.
	 *
	 * @param string $type    Type of integration.
	 * @param bool   $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function excluded_fields( $type = 'form', $network = false ) {
		$fields = array(
			'forminator_nonce',
		);

		/**
		 * Filter hook to exclude fields from GTM event.
		 *
		 * @param array  $fields  Field names.
		 * @param string $type    Type of integration.
		 * @param bool   $network Network flag.
		 *
		 * @since 3.3.0
		 */
		$fields = apply_filters( 'beehive_gtm_forminator_excluded_fields', $fields, $type, $network );

		return empty( $fields ) ? array() : (array) $fields;
	}

	/**
	 * Check if current version of Forminator is supported.
	 *
	 * Forminator Quiz and Poll integrations require 1.14.0 or higher.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public static function is_supported( $network = false ) {
		// Get the version number.
		$version = class_exists( 'Forminator' ) && defined( 'FORMINATOR_VERSION' ) ? FORMINATOR_VERSION : 0;

		// Should be at least 1.14.0.
		$supported = version_compare( $version, '1.14.0', '>=' );

		/**
		 * Filter hook to change Forminator supported status.
		 *
		 * @param bool $supported Is supported.
		 * @param bool $network   Network flag.
		 *
		 * @since 3.3.0
		 */
		return apply_filters( 'beehive_gtm_forminator_is_supported', $supported, $network );
	}
}