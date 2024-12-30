<?php
/**
 * Base class for all Addon classes
 */
class AgmAddonBase {

	/**
	 * Holds the current custom data options used by get_option/set_option
	 *
	 * @since  2.0.9.4
	 * @var array
	 */
	static protected $custom_data = null;

	/**
	 * Semi-Factory that returns a singleton instance of the AgmMapModel class.
	 *
	 * @since  2.9.0.4
	 *
	 * @return AgmMapModel
	 */
	protected function map_model() {
		static $Model = null;

		if ( null === $Model ) {
			$Model = new AgmMapModel();
		}

		return $Model;
	}

	/**
	 * Echos a message if the user is an administrator
	 *
	 * @since  2.9.0.4
	 * @param  string $text The text to display.
	 */
	protected function admin_note( $text ) {
		if ( is_super_admin() ) {
			printf(
				'<div class="agm-admin-note">%1$s</div>',
				$text
			);
		}
	}

	/**
	 * Returns the current post_id
	 *
	 * @since  2.9.0.4
	 * @return int
	 */
	protected function get_the_id() {
		static $Post_id = null;

		if ( null === $Post_id ) {
			$Post_id = get_the_ID();
			if ( empty( $Post_id ) ) { $Post_id = get_queried_object_id(); }
			// Could also use the function url_to_postid()...
		}

		return $Post_id;
	}

	/**
	 * Returns a custom AGM map option.
	 *
	 * @since  2.9.0.4
	 * @param  string $group The option group
	 * @param  string $key The option key. If null, then whole group is returned.
	 * @return mixed
	 */
	protected function get_option( $group, $key = null ) {
		$result = null;

		if ( ! is_array( self::$custom_data ) ) {
			self::$custom_data = get_option( 'agm_custom_data' );
		}
		if ( ! is_array( self::$custom_data[$group] ) ) {
			self::$custom_data[$group] = array();
		}

		if ( null === $key ) {
			$result = self::$custom_data[$group];
		} elseif ( isset( self::$custom_data[$group][$key] ) ) {
			$result = self::$custom_data[$group][$key];
		}
		return $result;
	}

	/**
	 * Saves a custom AGM map option.
	 *
	 * @since  2.9.0.4
	 * @param  string $group The option group
	 * @param  string $key The option key.
	 * @param  mixed $value The option value.
	 */
	protected function set_option( $group, $key, $value ) {
		if ( ! is_array( self::$custom_data ) ) {
			$this->get_option( $group, $key );
		} elseif ( ! is_array( self::$custom_data[$group] ) ) {
			self::$custom_data[$group] = array();
		}

		self::$custom_data[$group][$key] = $value;
		update_option( 'agm_custom_data', self::$custom_data );
	}

	/**
	 * Removes a custom AGM map option.
	 *
	 * @since  2.9.0.4
	 * @param  string $group The option group
	 * @param  string $key The option key.
	 */
	protected function del_option( $group, $key ) {
		if ( ! is_array( self::$custom_data ) ) {
			$this->get_option( $group, $key );
		} elseif ( ! is_array( self::$custom_data[$group] ) ) {
			self::$custom_data[$group] = array();
		}

		unset( self::$custom_data[$group][$key] );
		update_option( 'agm_custom_data', self::$custom_data );
	}
}