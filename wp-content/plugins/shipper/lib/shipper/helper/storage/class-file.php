<?php
/**
 * Shipper storage: File implementation (default)
 *
 * @package shipper
 */

/**
 * Database storage implementation
 */
class Shipper_Helper_Storage_File extends Shipper_Helper_Storage {

	/**
	 * Holds hasher instance, for storage obfuscation
	 *
	 * @var object Shipper_Helper_Hash instance
	 */
	private $_hasher;

	/**
	 * Gets a hasher object, instantiating if needed
	 *
	 * @return object Shipper_Helper_Hash instance
	 */
	public function get_storage_hasher() {
		if ( empty( $this->_hasher ) ) {
			$this->_hasher = new Shipper_Helper_Hash;
		}
		return $this->_hasher;
	}

	/**
	 * Loads current state from storage medium
	 *
	 * @return bool
	 */
	public function load() {
		$file = $this->get_storage_path();
		$str = is_readable( $file )
			? file_get_contents( $file )
			: '[]'
		;

		$str = $this->deobfuscate( $str );

		$this->data = (array) $this->decode( $str );

		return ! empty( $this->data );
	}

	/**
	 * Saves current state to implementation-specific storage medium
	 *
	 * @return bool
	 */
	public function save() {
		$str = $this->encode( $this->data );

		$str = $this->obfuscate( $str );

		return ! ! file_put_contents( $this->get_storage_path(), $str );
	}

	/**
	 * Gets storage bucket file path
	 *
	 * @return string
	 */
	public function get_storage_path() {
		$fname = $this->get_namespace();
		// Are we debugging? Keep it plain, please.
		if ( ! Shipper_Controller_Override_Debug::get()->is_in_debug_mode() ) {
			$hasher = $this->get_storage_hasher();
			$fname = $hasher->get_concealed( $fname );
		}
		return trailingslashit( Shipper_Helper_Fs_Path::get_storage_dir() ) .
			$fname . '.json'
		;
	}

	/**
	 * Obfuscates a string
	 *
	 * Used simple XOR-based procedure.
	 *
	 * @see `deobfuscate()` for reverse procedure.
	 *
	 * @param string $what String to obfuscate.
	 *
	 * @return string
	 */
	public function obfuscate( $what ) {
		// Are we debugging? Keep it plain, please.
		if ( Shipper_Controller_Override_Debug::get()->is_in_debug_mode() ) {
			return $what;
		}

		$hasher = $this->get_storage_hasher();
		$key = $hasher->get_obfuscation_key();

		$msg = base64_encode( $what );
		$key = $hasher->get_concealed( $key . $this->get_padded_time() );
		$key = md5( $key ); // So we're sure it's hex.
		$key = base64_encode( shipper_hex2bin( $key ) );

		$encoded = '';
		$msglen = strlen( $msg );
		$keylen = strlen( $key );
		for ( $i = 0; $i < $msglen; ) {
			for ( $j = 0; $j < $keylen; $j++, $i++ ) {
				if ( $i >= $msglen ) { break; }
				$encoded .= $msg[ $i ] ^ $key[ $j ];
			}
		}

		return base64_encode( $key . $encoded );
	}

	/**
	 * Reveals obfuscated string
	 *
	 * Uses simple XOR-based procedure.
	 *
	 * @param string $what String to deobfuscate.
	 *
	 * @return string
	 */
	public function deobfuscate( $what ) {
		// Are we debugging? Keep it plain, please.
		if ( Shipper_Controller_Override_Debug::get()->is_in_debug_mode() ) {
			return $what;
		}
		$hasher = $this->get_storage_hasher();
		$known_key = $hasher->get_obfuscation_key();

		$all = base64_decode( $what );
		$tmpkey = $hasher->get_concealed( $known_key . $this->get_padded_time() );
		$tmpkey = base64_encode( shipper_hex2bin( md5( $tmpkey ) ) );
		$key = substr( $all, 0, strlen( $tmpkey ) );
		$msg = substr( $all, strlen( $tmpkey ) );

		$decoded = '';
		$msglen = strlen( $msg );
		$keylen = strlen( $key );
		for ( $i = 0; $i < $msglen; ) {
			for ( $j = 0; $j < $keylen; $j++, $i++ ) {
				if ( $i >= $msglen ) { break; }
				$decoded .= $msg[ $i ] ^ $key[ $j ];
			}
		}

		return base64_decode( $decoded );
	}

	/**
	 * Gets timestamp padded to length
	 *
	 * @return string
	 */
	public function get_padded_time() {
		return '' . sprintf( '%\'.32.2f', microtime( true ) );
	}
}