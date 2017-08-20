<?php
/**
 * Custom filters/actions
 *
 * @package   PT_Content_Views
 * @author    PT Guy <http://www.contentviewspro.com/>
 * @license   GPL-2.0+
 * @link      http://www.contentviewspro.com/
 * @copyright 2014 PT Guy
 */
if ( !class_exists( 'PT_CV_Hooks' ) ) {

	/**
	 * @name PT_CV_Hooks
	 */
	class PT_CV_Hooks {

		/**
		 * Add custom filters/actions
		 */
		static function init() {
			add_filter( PT_CV_PREFIX_ . 'validate_settings', array( __CLASS__, 'filter_validate_settings' ), 10, 2 );
			add_filter( PT_CV_PREFIX_ . 'field_content_excerpt', array( __CLASS__, 'filter_field_content_excerpt' ), 9, 3 );
			add_filter( PT_CV_PREFIX_ . 'item_col_class', array( __CLASS__, 'filter_item_col_class' ), 20, 2 );
			add_filter( PT_CV_PREFIX_ . 'fields_html', array( __CLASS__, 'filter_fields_html' ), 20, 2 );

			if ( apply_filters( PT_CV_PREFIX_ . 'prevent_broken_excerpt', true ) ) {
				add_filter( PT_CV_PREFIX_ . 'before_trim_words', array( __CLASS__, 'filter_before_trim_words' ) );
				add_filter( PT_CV_PREFIX_ . 'after_trim_words', array( __CLASS__, 'filter_after_trim_words' ) );
			}

			/**
			 * @since 1.7.5
			 * able to disable responsive image of WordPress 4.4
			 */
			add_filter( 'wp_get_attachment_image_attributes', array( __CLASS__, 'filter_disable_wp_responsive_image' ), 1000, 3 );

			// Do action
			add_action( PT_CV_PREFIX_ . 'before_query', array( __CLASS__, 'action_before_query' ) );
			add_action( PT_CV_PREFIX_ . 'before_process_item', array( __CLASS__, 'action_before_process_item' ) );
			add_action( PT_CV_PREFIX_ . 'after_process_item', array( __CLASS__, 'action_after_process_item' ) );
			add_action( PT_CV_PREFIX_ . 'before_content', array( __CLASS__, 'action_before_content' ) );
		}

		/**
		 * Validate settings filter
		 *
		 * @param string $errors The error message
		 * @param array  $args  The Query parameters array
		 */
		public static function filter_validate_settings( $errors, $args ) {
			$dargs		 = PT_CV_Functions::get_global_variable( 'dargs' );
			$messages	 = array(
				'field'	 => array(
					'select' => __( 'Please select an option in', 'content-views-query-and-display-post-page' ) . ' : ',
					'text'	 => __( 'Please set value in', 'content-views-query-and-display-post-page' ) . ' : ',
				),
				'tab'	 => array(
					'filter'	 => __( 'Filter Settings', 'content-views-query-and-display-post-page' ),
					'display'	 => __( 'Display Settings', 'content-views-query-and-display-post-page' ),
				),
			);

			// Post type
			if ( empty( $args[ 'post_type' ] ) ) {
				$errors[] = $messages[ 'field' ][ 'select' ] . $messages[ 'tab' ][ 'filter' ] . ' > ' . __( 'Content type', 'content-views-query-and-display-post-page' );
			}

			// View type
			if ( empty( $dargs[ 'view-type' ] ) ) {
				$errors[] = $messages[ 'field' ][ 'select' ] . $messages[ 'tab' ][ 'display' ] . ' > ' . __( 'Layout', 'content-views-query-and-display-post-page' );
			}

			// Layout format
			if ( empty( $dargs[ 'layout-format' ] ) ) {
				$errors[] = $messages[ 'field' ][ 'select' ] . $messages[ 'tab' ][ 'display' ] . ' > ' . __( 'Format', 'content-views-query-and-display-post-page' );
			}

			// Field settings
			if ( !isset( $dargs[ 'fields' ] ) ) {
				$errors[] = $messages[ 'field' ][ 'select' ] . $messages[ 'tab' ][ 'display' ] . ' > ' . __( 'Fields settings', 'content-views-query-and-display-post-page' );
			}

			// Item per page
			if ( isset( $dargs[ 'pagination-settings' ] ) ) {
				if ( empty( $dargs[ 'pagination-settings' ][ 'items-per-page' ] ) ) {
					$errors[] = $messages[ 'field' ][ 'text' ] . $messages[ 'tab' ][ 'display' ] . ' > ' . __( 'Pagination', 'content-views-query-and-display-post-page' ) . ' > ' . __( 'Items per page', 'content-views-query-and-display-post-page' );
				}
			}

			if ( !empty( $dargs[ 'view-type' ] ) ) {
				switch ( $dargs[ 'view-type' ] ) {
					case 'grid':
						if ( empty( $dargs[ 'number-columns' ] ) ) {
							$errors[] = $messages[ 'field' ][ 'text' ] . $messages[ 'tab' ][ 'display' ] . ' > ' . __( 'Layout', 'content-views-query-and-display-post-page' ) . ' > ' . __( 'Items per row', 'content-views-query-and-display-post-page' );
						}
						break;
				}
			}

			return array_filter( $errors );
		}

		/**
		 * Filter content before generating excerpt
		 *
		 * @param type $args
		 * @param type $fargs
		 * @param type $post
		 */
		public static function filter_field_content_excerpt( $args, $fargs, $post ) {
			/**
			 * Get content of current language
			 * qTranslate-X (and qTranslate, mqTranslate)
			 * @since 1.7.8
			 */
			if ( function_exists( 'qtranxf_use' ) ) {
				global $q_config;
				$args = qtranxf_use( $q_config[ 'language' ], $args );
			}

			return $args;
		}

		/**
		 * Filter span with
		 * @since 1.8.5
		 *
		 * @param array $args
		 * @param int $span_width
		 *
		 * @return array
		 */
		public static function filter_item_col_class( $args, $span_width ) {
			if ( PT_CV_Functions::get_global_variable( 'view_type' ) === 'grid' ) {
				$tablet_col	 = (int) PT_CV_Functions::setting_value( PT_CV_PREFIX . 'resp-tablet-number-columns' );
				$mobile_col	 = (int) PT_CV_Functions::setting_value( PT_CV_PREFIX . 'resp-number-columns' );

				$sm_class	 = 'col-sm-' . (int) ( 12 / ($tablet_col ? $tablet_col : 2) );
				$xs_class	 = 'col-xs-' . (int) ( 12 / ($mobile_col ? $mobile_col : 1) );

				if ( !in_array( $sm_class, $args ) ) {
					$args[] = $sm_class;
				}

				if ( !in_array( $xs_class, $args ) ) {
					$args[] = $xs_class;
				}
			}

			return $args;
		}

		/**
		 * Do not wrap text around image when show thumbnail on left/right of text
		 *
		 * @since 1.9.9
		 * @param array $args
		 * @param object $post
		 */
		public static function filter_fields_html( $args, $post ) {
			$format	 = PT_CV_Functions::setting_value( PT_CV_PREFIX . 'layout-format' );
			$nowrap	 = PT_CV_Functions::setting_value( PT_CV_PREFIX . 'lf-nowrap' );

			if ( $format === '2-col' && $nowrap ) {
				$exclude_fields = apply_filters( PT_CV_PREFIX_ . '2col_nowrap_fields', array( 'thumbnail' ) );

				if ( PT_CV_Functions::get_global_variable( 'view_type' ) === 'collapsible' ) {
					$exclude_fields[] = 'title';
				}

				$others = array();
				foreach ( $args as $field => $value ) {
					if ( !in_array( $field, $exclude_fields ) ) {
						$others[ $field ] = $value;
						unset( $args[ $field ] );
					}
				}

				if ( $others ) {
					$args[ 'others-wrap' ] = '<div class="' . PT_CV_PREFIX . 'colwrap">' . implode( '', $others ) . '</div>';
				}
			}

			return $args;
		}

		public static function filter_before_trim_words( $content ) {
			if ( PT_CV_Functions::setting_value( PT_CV_PREFIX . 'field-excerpt-allow_html' ) ) {
				global $cv_replaced_tags, $cv_replaced_idx;
				# reset for each post
				$cv_replaced_tags	 = array();
				$cv_replaced_idx	 = 0;

				$content = preg_replace_callback( '/<(\/?)[^>]+>/', array( __CLASS__, '_callback_before_trim_words' ), $content );
			}

			return $content;
		}

		/**
		 * Temporary replace HTML tag content by a simple code which doesn't impact trimming word function
		 * @since 1.9.4
		 *
		 * @global type $cv_replaced_tags
		 * @global type $cv_replaced_idx
		 * @param type $matches
		 * @return string
		 */
		public static function _callback_before_trim_words( $matches ) {
			global $cv_replaced_tags, $cv_replaced_idx;

			$return = $matches;
			if ( !empty( $matches[ 0 ] ) ) {
				$cv_replaced_tags[ ++$cv_replaced_idx ]	 = $matches[ 0 ];
				$return									 = "@$cv_replaced_idx@";
			}

			return $return;
		}

		public static function filter_after_trim_words( $content ) {
			if ( PT_CV_Functions::setting_value( PT_CV_PREFIX . 'field-excerpt-allow_html' ) ) {
				$content = preg_replace_callback( '/@(\d+)@/', array( __CLASS__, '_callback_after_trim_words' ), $content );
			}

			return $content;
		}

		/**
		 * Revert HTML tag content
		 * @since 1.9.4
		 *
		 * @global type $cv_replaced_tags
		 * @param type $matches
		 * @return \type
		 */
		public static function _callback_after_trim_words( $matches ) {
			global $cv_replaced_tags;

			$return = $matches;
			if ( !empty( $matches[ 1 ] ) && isset( $cv_replaced_tags[ (int) $matches[ 1 ] ] ) ) {
				$return = $cv_replaced_tags[ (int) $matches[ 1 ] ];
			}

			return $return;
		}

		// Disable WP 4.4 responsive image
		public static function filter_disable_wp_responsive_image( $args, $attachment = null, $size = null ) {
			if ( PT_CV_Html::is_responsive_image_disabled() ) {
				if ( isset( $args[ 'sizes' ] ) )
					unset( $args[ 'sizes' ] );
				if ( isset( $args[ 'srcset' ] ) )
					unset( $args[ 'srcset' ] );
			}

			return $args;
		}

		public static function action_before_query() {
			/** Fix problem with Paid Membership Pro plugin
			 * It resets (instead of append) "post__not_in" parameter of WP query which makes:
			 * - exclude function doesn't work
			 * - output in Preview panel is different from output in front-end
			 */
			if ( function_exists( 'pmpro_search_filter' ) ) {
				remove_filter( 'pre_get_posts', 'pmpro_search_filter' );
			}
		}

		public static function action_before_process_item() {
			// Disable View Shortcode in child page
			PT_CV_Functions::disable_view_shortcode();
		}

		public static function action_after_process_item() {
			// Enable View Shortcode again
			PT_CV_Functions::disable_view_shortcode( 'recovery' );
		}

		/**
		 * Issue: shortcode is visible in pagination, preview
		 * Solution: Backup shortcode tag in live page, to use for preview, pagination request
		 *
		 * @since 1.9.3
		 */
		public static function action_before_content() {
			global $shortcode_tags, $cv_shortcode_tags_backup;

			if ( !$cv_shortcode_tags_backup ) {
				$trans_key = 'cv_shortcode_tags_193';
				if ( !defined( 'PT_CV_DOING_PAGINATION' ) && !defined( 'PT_CV_DOING_PREVIEW' ) ) {
					$tagnames					 = array_keys( $shortcode_tags );
					$cv_shortcode_tags_backup	 = join( '|', array_map( 'preg_quote', $tagnames ) );
					set_transient( $trans_key, $cv_shortcode_tags_backup, DAY_IN_SECONDS );
				} else {
					$cv_shortcode_tags_backup = get_transient( $trans_key );
				}
			}
		}

	}

}
