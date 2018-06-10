<?php

/**
 * Conditions utils
 *
 * Most of the methods are courtesy Philipp Stracker
 *
 * Class Opt_In_Utils
 */
class Opt_In_Utils
{

	/**
	 * Instance of Opt_In_Geo
	 *
	 * @var Opt_In_Geo
	 */
	private $_geo;

	function __construct( Opt_In_Geo $geo )
	{
		$this->_geo = $geo;
	}

	/**
	 * Checks if user has already commented
	 *
	 * @return bool|int
	 */
	public function has_user_commented() {
		global $wpdb;
		static $Comment = null;

		if ( null === $Comment ) {
			// Guests (and maybe logged in users) are tracked via a cookie.
			$Comment = isset( $_COOKIE['comment_author_' . COOKIEHASH] ) ? 1 : 0;

			if ( ! $Comment && is_user_logged_in() ) {
				// For logged-in users we can also check the database.
				$sql = "
					SELECT COUNT(1)
					FROM {$wpdb->comments}
					WHERE user_id = %s
				";
				$sql = $wpdb->prepare( $sql, get_current_user_id() );
				$count = absint( $wpdb->get_var( $sql ) );
				$Comment = $count > 0;
			}
		}
		return $Comment;
	}

	/**
	 * Returns the referrer.
	 *
	 * @return string
	 */
	public function get_referrer() {
		$referrer = '';

		$is_ajax = (defined( 'DOING_AJAX' ) && DOING_AJAX)
			|| ( ! empty( $_POST['_po_method_'] ) && 'raw' == $_POST['_po_method_'] );

		if ( isset( $_REQUEST['thereferrer'] ) ) {
			$referrer = $_REQUEST['thereferrer'];
		} else if ( ! $is_ajax && isset( $_SERVER['HTTP_REFERER'] ) ) {
			// When doing Ajax request we NEVER use the HTTP_REFERER!
			$referrer = $_SERVER['HTTP_REFERER'];
		}

		return $referrer;
	}

	/**
	 * Tests if the current referrer is one of the referers of the list.
	 * Current referrer has to be specified in the URL param "thereferer".
	 *
	 *
	 * @param  array $list List of referers to check.
	 * @return bool
	 */
	public function test_referrer( $list ) {
		$response = false;
		if ( is_string( $list ) ) { $list = array( $list ); }
		if ( ! is_array( $list ) ) { return true; }

		$referrer = $this->get_referrer();

		if ( empty( $referrer ) ) {
			$response = false;
		} else {
			foreach ( $list as $item ) {
				$item = trim( $item );
				$res = stripos( $referrer, $item );
				if ( false !== $res ) {
					$response = true;
					break;
				}
			}
		}

		return $response;
	}

	/**
	 * Tests if the $test_url matches any pattern defined in the $list.
	 *
	 * @since  4.6
	 * @param  string $test_url The URL to test.
	 * @param  array $list List of URL-patterns to test against.
	 * @return bool
	 */
	public function check_url( $test_url, $list ) {
		$response = false;
		$list = array_map( 'trim', (array) $list );
		$test_url = strtok( $test_url, '#' );

		if ( empty( $list ) ) {
			$response = true;
		} else {
			foreach ( $list as $match ) {
				$match = preg_quote( strtok( $match, '#' ) );

				if ( false === strpos( $match, '://' ) ) {
					$match = '\w+://' . $match;
				}
				if ( substr( $match, -1 ) != '/' ) {
					$match .= '/?';
				} else {
					$match .= '?';
				}
				$exp = '#^' . $match . '$#i';
				$res = preg_match( $exp, $test_url );

				if ( $res ) {
					$response = true;
					break;
				}
			}
		}

		return $response;
	}

	/**
	 * Returns current url
	 * should only be called after plugins_loaded hook is fired
	 *
	 * @return string
	 */
	function get_current_url(){
		if( !did_action("plugins_loaded") )
			new Exception("This method should only be called after plugins_loaded hook is fired");

		global $wp;
		return add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
	}

	/**
	 * Returns current actual url, the one seen on browser
	 *
	 * @return string
	 */
	function get_current_actual_url(){
		if( !did_action("plugins_loaded") )
			new Exception("This method should only be called after plugins_loaded hook is fired");

		return "http" . ( isset($_SERVER['HTTPS'] ) ? "s" : "" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}



	/**
	 * Checks if the current user IP belongs to one of the countries defined in
	 * country_codes list.
	 *
	 * @param  array $country_codes List of country codes.
	 * @return bool
	 */
	public function test_country( $country_codes ) {
		$response = true;
		$country = $this->_geo->get_user_country();

		if ( 'XX' == $country ) {
			return $response;
		}

		return in_array( $country, (array) $country_codes );
	}

	/**
	 * Checks if user is allowed to perform the ajax actions
	 *
	 * @since 1.0
	 * @return bool
	 */
	public static function is_user_allowed(){
		return current_user_can("manage_options");
	}

	/**
	 * Checks if the ajax
	 *
	 * @since 1.0
	 * @param $action string ajax call action name
	 */
	public static function validate_ajax_call( $action ){
		if( !self::is_user_allowed() || !check_ajax_referer( $action ) )
			wp_send_json_error( __("Invalid request, you are not allowed to make this request", Opt_In::TEXT_DOMAIN) );
	}

	/**
	 * Verify if current version is FREE
	 **/
	public static function _is_free() {
		$is_free = ! file_exists( Opt_In::$plugin_path . 'lib/wpmudev-dashboard/wpmudev-dash-notification.php' );

		return $is_free;
	}

	/**
	 * Verify if current version is free
	 **/
	public static function is_hustle_free( $type = 'opt-ins' ) {
		$is_free = self::_is_free();

		if ( $is_free ) {
			if ( 'opt-ins' == $type ) {
				$optins = Opt_In_Collection::instance()->get_all_optins( null );
				$is_free = count( $optins ) > 1;
			} else {
				// For CC
				$cc = Hustle_Custom_Content_Collection::instance()->get_all( null );
				$is_free = count( $cc ) > 1;
			}
		}

		return $is_free;
	}

	/**
	 * Remove "-pro" that came from the menu which causes template not to work
	 **/
	public static function clean_current_screen( $screen ) {
		return str_replace( 'hustle-pro', 'hustle', $screen );
	}

	/**
	 * Check if is IE
	 *
	 * @return bool
	 */
	public static function is_ie() {
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			preg_match( '/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches );
			if( count( $matches ) < 2 ) {
				preg_match( '/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches );
			}
			return ( count( $matches ) >1 );
		}
		return false;
	}
}