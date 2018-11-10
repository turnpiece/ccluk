<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Component;


use Hammer\Base\Component;

class Token_Utils extends Component {
	/**
	 * @var array
	 */
	static $tokens = array();

	/**
	 * @param $token
	 * @param $from
	 * @param null $end
	 *
	 * @return bool
	 */
	public static function findPrevious( $token, $from, $end = null ) {
		for ( $i = $from; $i >= $end; $i -- ) {
			if ( isset( self::$tokens[ $i ] ) && self::$tokens[ $i ]['code'] == $token ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * @param $token
	 * @param $from
	 * @param $end
	 *
	 * @return bool|int|string
	 */
	public static function findNext( $token, $from, $end = null ) {
		if ( $end == null ) {
			$end = count( self::$tokens ) - 1;
		}

		if ( ! is_array( $token ) ) {
			$token = array( $token );
		}

		for ( $i = $from; $i < $end; $i ++ ) {
			if ( ! isset( self::$tokens[ $i ] ) ) {
				return false;
			}

			if ( self::$tokens[ $i ]['code'] == T_SEMICOLON && ! in_array( T_SEMICOLON, $token ) ) {
				return false;
			}

			if ( in_array( self::$tokens[ $i ]['code'], $token ) ) {
				return $i;
			}
		}
	}

	/**
	 * @param $start
	 * @param $length
	 *
	 * @return string
	 * code borrow from @PHP_CodeSniffer_File
	 */
	public static function getTokensAsString( $start, $length ) {
		$str = '';
		$end = ( $start + $length );

		for ( $i = $start; $i < $end; $i ++ ) {
			$str .= self::$tokens[ $i ]['content'];
		}

		return $str;
	}

	/**
	 * @param $start
	 * @param $end
	 *
	 * @return array
	 */
	public static function findParams( $start, $end ) {
		$params = array();
		for ( $i = $start; $i < $end; $i ++ ) {
			$params[] = self::$tokens[ $i ];
		}

		return $params;
	}

	/**
	 * @param $token
	 *
	 * @return bool
	 */
	public static function isUserInput( $token ) {
		if ( $token['code'] == T_VARIABLE
		     && preg_match( '/\$\{?_(GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/', $token['content'] ) ) {
			return true;
		}

		return false;
	}

	//Borrow from https://github.com/FloeDesignTechnologies/phpcs-security-audit/blob/master/Security/Sniffs/Utils.php
	//Point to RIPs and SO https://stackoverflow.com/questions/3115559/exploitable-php-functions
	public static function getCallbackFunctions() {
		return array(
			'ob_start',
			'array_diff_uassoc',
			'array_diff_ukey',
			'array_filter',
			'array_intersect_uassoc',
			'array_intersect_ukey',
			'array_map',
			'array_reduce',
			'array_udiff_assoc',
			'array_udiff_uassoc',
			'array_udiff',
			'array_uintersect_assoc',
			'array_uintersect_uassoc',
			'array_uintersect',
			'array_walk_recursive',
			'array_walk',
			'assert_options',
			'uasort',
			'uksort',
			'usort',
			'preg_replace_callback',
			'spl_autoload_register',
			'iterator_apply',
			'call_user_func',
			'call_user_func_array',
			'register_shutdown_function',
			'register_tick_function',
			'set_error_handler',
			'set_exception_handler',
			'session_set_save_handler',
			'sqlite_create_aggregate',
			'sqlite_create_function'
		);
	}

	// From http://www.php.net/manual/en/ref.funchand.php
	public static function getCreateFuncs() {
		return array(
			'create_function',
			'call_user_func',
			'call_user_func_array',
			'forward_static_call',
			'forward_static_call_array',
			'function_exists',
			'register_shutdown_function',
			'register_tick_function'
		);
	}

	/**
	 * @return array
	 */
	public static function getsuspiciousFunctions() {
		return array_merge( self::getCryptoFunctions(), array(
			'assert',
			'eval',
			'gzinflate'
		) );
	}

	/**
	 * Borrow from https://github.com/FloeDesignTechnologies/phpcs-security-audit/blob/master/Security/Sniffs/Utils.php
	 * @return array
	 */
	public static function getCryptoFunctions() {
		return array(
			// Officials
			'crypt',
			'md5',
			'md5_file',
			'sha1',
			'sha1_file',
			'str_rot13',
			'base64_decode',
			'base64_encode',
			'convert_uudecode',
			'convert_uuencode',
			// http://php.net/manual/en/book.mcrypt.php
			'mcrypt_cbc',
			'mcrypt_cfb',
			'mcrypt_create_iv',
			'mcrypt_decrypt',
			'mcrypt_ecb',
			'mcrypt_enc_get_algorithms_name',
			'mcrypt_enc_get_block_size',
			'mcrypt_enc_get_iv_size',
			'mcrypt_enc_get_key_size',
			'mcrypt_enc_get_modes_name',
			'mcrypt_enc_get_supported_key_sizes',
			'mcrypt_enc_is_block_algorithm_mode',
			'mcrypt_enc_is_block_algorithm',
			'mcrypt_enc_is_block_mode',
			'mcrypt_enc_self_test',
			'mcrypt_encrypt',
			'mcrypt_generic_deinit',
			'mcrypt_generic_end',
			'mcrypt_generic_init',
			'mcrypt_generic',
			'mcrypt_get_block_size',
			'mcrypt_get_cipher_name',
			'mcrypt_get_iv_size',
			'mcrypt_get_key_size',
			'mcrypt_list_algorithms',
			'mcrypt_list_modes',
			'mcrypt_module_close',
			'mcrypt_module_get_algo_block_size',
			'mcrypt_module_get_algo_key_size',
			'mcrypt_module_get_supported_key_sizes',
			'mcrypt_module_is_block_algorithm_mode',
			'mcrypt_module_is_block_algorithm',
			'mcrypt_module_is_block_mode',
			'mcrypt_module_open',
			'mcrypt_module_self_test',
			'mcrypt_ofb',
			'mdecrypt_generic',
			// http://php.net/manual/en/book.mhash.php
			'mhash_count',
			'mhash_get_block_size',
			'mhash_get_hash_name',
			'mhash_keygen_s2k',
			'mhash',
			// http://php.net/manual/en/book.crack.php
			'crack_check',
			'crack_closedict',
			'crack_getlastmessage',
			'crack_opendict',
			// http://php.net/manual/en/book.hash.php
			'hash_algos',
			'hash_copy',
			'hash_file',
			'hash_final',
			'hash_hmac_file',
			'hash_hmac',
			'hash_init',
			'hash_pbkdf2',
			'hash_update_file',
			'hash_update_stream',
			'hash_update',
			//'hash',
			// http://php.net/manual/en/book.openssl.php
			'openssl_cipher_iv_length',
			'openssl_csr_export_to_file',
			'openssl_csr_export',
			'openssl_csr_get_public_key',
			'openssl_csr_get_subject',
			'openssl_csr_new',
			'openssl_csr_sign',
			'openssl_decrypt',
			'openssl_dh_compute_key',
			'openssl_digest',
			'openssl_encrypt',
			'openssl_error_string',
			'openssl_free_key',
			'openssl_get_cipher_methods',
			'openssl_get_md_methods',
			'openssl_get_privatekey',
			'openssl_get_publickey',
			'openssl_open',
			'openssl_pbkdf2',
			'openssl_pkcs12_export_to_file',
			'openssl_pkcs12_export',
			'openssl_pkcs12_read',
			'openssl_pkcs7_decrypt',
			'openssl_pkcs7_encrypt',
			'openssl_pkcs7_sign',
			'openssl_pkcs7_verify',
			'openssl_pkey_export_to_file',
			'openssl_pkey_export',
			'openssl_pkey_free',
			'openssl_pkey_get_details',
			'openssl_pkey_get_private',
			'openssl_pkey_get_public',
			'openssl_pkey_new',
			'openssl_private_decrypt',
			'openssl_private_encrypt',
			'openssl_public_decrypt',
			'openssl_public_encrypt',
			'openssl_random_pseudo_bytes',
			'openssl_seal',
			'openssl_sign',
			'openssl_spki_export_challenge',
			'openssl_spki_export',
			'openssl_spki_new',
			'openssl_spki_verify',
			'openssl_verify',
			'openssl_x509_check_private_key',
			'openssl_x509_checkpurpose',
			'openssl_x509_export_to_file',
			'openssl_x509_export',
			'openssl_x509_free',
			'openssl_x509_parse',
			'openssl_x509_read',
			// http://php.net/manual/en/book.password.php
			'password_get_info',
			'password_hash',
			'password_needs_rehash',
			'password_verify',
			// Guesses
			'encrypt',
			'decrypt',
			'mc_encrypt',
			'mc_decrypt',
			'crypto',
			'scrypt',
			'bcrypt',
			'password_crypt',
			'sha256',
			'sha128',
			'sha512',
			'hmac',
			'pbkdf2',
			'aes',
			'encipher',
			'decipher',
			'crc32',
		);
	}
}