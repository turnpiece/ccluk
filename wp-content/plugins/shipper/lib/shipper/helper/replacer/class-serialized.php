<?php
/**
 * Shipper helpers: serialized values replacer
 *
 * Handles low level serialized values replacement transformations.
 *
 * @package shipper
 */

/**
 * String replacer class
 */
class Shipper_Helper_Replacer_Serialized extends Shipper_Helper_Replacer {

	/**
	 * Holds table context
	 *
	 * @var string
	 */
	private $_table;

	/**
	 * Holds row key context
	 *
	 * @var string
	 */
	private $_key;

	/**
	 * Constructor
	 *
	 * Optionally sets table context.
	 *
	 * @param string $direction Direction (see parent).
	 * @param string $table Optional table context.
	 */
	public function __construct( $direction, $table = '' ) {
		parent::__construct( $direction );
		if ( ! empty( $table ) ) {
			$this->_table = $table;
		}
	}

	/**
	 * Sets row key context.
	 *
	 * @param string $key Row key.
	 *
	 * @return object
	 */
	public function set_key( $key = '' ) {
		$this->_key = $key;
		return $this;
	}

	/**
	 * Transformation wrapper
	 *
	 * @param string $source Source string to process.
	 *
	 * @return string
	 */
	public function transform( $source ) {
		return maybe_serialize( $this->transform_serialized( $source, 0 ) );
	}

	/**
	 * Applies migration transformations to a potentially serialized value
	 *
	 * @param string $source Source string to process.
	 * @param int    $iteration Optional recursion level.
	 *
	 * @return string
	 */
	public function transform_serialized( $source, $iteration = 0 ) {
		if ( ! is_scalar( $source ) ) {
			// Can't deal with this, pass through.
			return $source;
		}

		if ( Shipper_Helper_Codec::DECODE === $this->get_direction() ) {
			// This optimization is only applied on decoding.
			if ( ! Shipper_Helper_Codec::has_shipper_macro( $source ) ) {
				// We don't have macros to expand - don't even bother.
				return maybe_unserialize( $source ); // Because this will get re-serialized.
			}
		}

		// Is this an users table with keys that we shouldn't be touching?
		if ( ! empty( $this->_key ) && 'users' === $this->get_bare_tablename() ) {
			$skip_keys = array(
				'user_login',
				'user_email',
				'display_name',
			);
			if ( in_array( $this->_key, $skip_keys ) ) {
				// If so, return this verbatim.
				return maybe_unserialize( $source );
			}
		}

		if ( $iteration > $this->get_max_recursion_depth() ) {
			// We're over max recursion level, stop.
			return $source;
		}

		$raw = maybe_unserialize( $source );
		$rpl = new Shipper_Helper_Replacer_String( $this->get_direction() );
		$codecs = $this->get_codec_list();

		if ( ! empty( $this->_key ) ) {
			$cls = false;
			$cls_key = preg_replace( '/[^a-z]/i', '', $this->_key );
			if ( $this->is_ms_table() ) {
				// Multisite-specific table.
				$cls = 'Shipper_Helper_Codec_Ms' . strtolower( $cls_key );
			} elseif ( $this->is_processable_table() ) {
				// Other kind of post-processable table.
				$cls = 'Shipper_Helper_Codec_Pre' . strtolower( $cls_key );
			}
			if ( class_exists( $cls ) ) {
				$obj = new $cls;
				array_unshift( $codecs, $obj ); // Prepend the MS tables codec!
			}
		}
		$rpl->set_codec_list( $codecs );

		$processed = '';

		if ( is_array( $raw ) ) {
			$proc = array();
			foreach ( $raw as $key => $value ) {
				$iteration += 1;
				$key = $this->transform_serialized( $key, $iteration );
				$to_process = is_scalar( $value ) ? $value : serialize( $value );
				$value = $this->transform_serialized( $to_process, $iteration );
				$proc[ $key ] = $value;
			}
			$processed = $proc;
		} elseif ( is_object( $raw ) ) {
			$proc = new StdClass;
			foreach ( $raw as $key => $value ) {
				$iteration += 1;
				$key = $this->transform_serialized( $key, $iteration );
				$to_process = is_scalar( $value ) ? $value : serialize( $value );
				$value = $this->transform_serialized( $to_process, $iteration );
				$proc->$key = $value;
			}
			$processed = $proc;
		} else {
			$processed = $rpl->transform( $raw );
		}

		return $processed;
	}

	/**
	 * Gets maximum depth to which the unserialization is to happen
	 *
	 * @return int
	 */
	public function get_max_recursion_depth() {
		return (int) apply_filters(
			'shipper_codec_serialized_recursion_depth',
			200,
			$this
		);
	}

	/**
	 * Checks if a current table context is MS table
	 *
	 * @uses $wpdb global
	 *
	 * @return bool
	 */
	public function is_ms_table() {
		if ( empty( $this->_table ) ) { return false; }

		global $wpdb;
		foreach ( $wpdb->ms_global_tables as $tbl ) {
			if ( "{$wpdb->base_prefix}{$tbl}" === $this->_table ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if a current table is in need of postprocessing
	 *
	 * This means that we're dealing with a MS table, or with
	 * one of the usermeta/options tables.
	 *
	 * @return bool
	 */
	public function is_processable_table() {
		if ( empty( $this->_table ) ) { return false; }

		$tables = array(
			'options',
			'usermeta',
		);

		if ( in_array( $this->get_bare_tablename(), $tables, true ) ) {
			return true;
		}

		return $this->is_ms_table();
	}

	/**
	 * Returns tablename stripped of any prefix
	 *
	 * @uses $wpdb global
	 *
	 * @return string
	 */
	public function get_bare_tablename() {
		if ( empty( $this->_table ) ) { return ''; }

		global $wpdb;

		return preg_replace(
			'/^' . preg_quote( $wpdb->base_prefix, '/' ) . '(\d+_)?/',
			'',
			$this->_table
		);
	}
}