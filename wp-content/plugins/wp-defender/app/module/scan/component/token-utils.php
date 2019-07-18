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
	static $code;

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
	 * Find the first token it met, can skip things dont need
	 *
	 * @param $from
	 * @param null $end
	 * @param array $skips
	 *
	 * @return bool|mixed
	 */
	public static function findFirstPrevious( $from, $end = 0, $skips = array() ) {
		for ( $i = $from; $i >= $end; $i -- ) {
			if ( isset( self::$tokens[ $i ] ) && ! in_array( self::$tokens[ $i ]['code'], $skips ) ) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * @param $from
	 * @param null $end
	 * @param array $skips
	 *
	 * @return bool
	 */
	public static function findFirstNext( $from, $end = null, $skips = array() ) {
		if ( $end == null ) {
			$end = count( self::$tokens ) - 1;
		}
		for ( $i = $from; $i <= $end; $i ++ ) {
			if ( isset( self::$tokens[ $i ] ) && ! in_array( self::$tokens[ $i ]['code'], $skips ) ) {
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

		for ( $i = $from; $i <= $end; $i ++ ) {
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
	 * @return string
	 */
	public static function getTokensAsStringByIndex( $start, $end ) {
		$str = '';
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
			$params[ $i ] = self::$tokens[ $i ];
		}

		return $params;
	}

	public static function prepareParams() {
		$results = array();
		foreach ( self::$tokens as $index => $token ) {
			if ( $token['code'] == T_VARIABLE ) {
				$next = self::findFirstNext( $index + 1, null, array( T_WHITESPACE ) );
				//this should be the =
				$params = array();
				if ( self::$tokens[ $next ]['code'] == T_EQUAL ) {
					//capture this
					$end    = self::findNext( T_SEMICOLON, $index + 2 );
					$params = self::findParams( $index + 2, $end );

				} elseif ( self::$tokens[ $next ]['code'] == T_OPEN_PARENTHESIS ) {
					//a variable function
				}

				$results[] = array(
					'variable' => $token['content'],
					'args'     => $params
				);
			}
		}

		return $results;
	}

	/**
	 * @param $token
	 *
	 * @return bool
	 */
	public static function isUserInput(
		$token
	) {
		if ( $token['code'] == T_VARIABLE
		     && preg_match( '/\$\{?_(GET|POST|REQUEST|COOKIE|SERVER|FILES|ENV)/', $token['content'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Borrow from https://github.com/FloeDesignTechnologies/phpcs-security-audit/blob/master/Security/Sniffs/Utils.php
	 * @return array
	 */
	public static function getFilesystemFunctions() {
		return array(
			// From http://www.php.net/manual/en/book.filesystem.php
			'chgrp',
			'chown',
			'clearstatcache',
			'copy',
			'delete',
			'disk_free_space',
			'disk_total_space',
			'diskfreespace',
			'fclose',
			'feof',
			'fflush',
			'fgetc',
			'fgetcsv',
			'fgets',
			'fgetss',
			'file_get_contents',
			'file_put_contents',
			'file',
			'fileatime',
			'filectime',
			'filegroup',
			'fileinode',
			'filemtime',
			'fileowner',
			'fileperms',
			'filesize',
			'filetype',
			'flock',
			'fopen',
			'fpassthru',
			'fputcsv',
			'fputs',
			'ftruncate',
			'fwrite',
			'glob',
			'is_executable',
			'is_readable',
			'is_uploaded_file',
			'is_writable',
			'is_writeable',
			'lchgrp',
			'lchown',
			'link',
			'linkinfo',
			'lstat',
			'mkdir',
			'move_uploaded_file',
			'parse_ini_file',
			'parse_ini_string',
			'readfile',
			'readlink',
			'realpath_cache_get',
			'realpath_cache_size',
			//'realpath',
			'rename',
			'rewind',
			'rmdir',
			'set_file_buffer',
			'stat',
			'symlink',
			'tempnam',
			'tmpfile',
			'touch',
			'umask',
			'unlink',
			// From http://www.php.net/manual/en/ref.dir.php except function that use directory handle as parameter
			'chdir',
			'chroot',
			'dir',
			'opendir',
			'scandir',
			// From http://ca2.php.net/manual/en/function.mime-content-type.php
			'finfo_open',
			// From http://ca2.php.net/manual/en/book.xattr.php
			'xattr_get',
			'xattr_list',
			'xattr_remove',
			'xattr_set',
			'xattr_supported',
			// From http://www.php.net/manual/en/function.readgzfile.php
			'readgzfile',
			'gzopen',
			'gzfile',
			// From http://www.php.net/manual/en/ref.image.php
			'getimagesize',
			'imagecreatefromgd2',
			'imagecreatefromgd2part',
			'imagecreatefromgd',
			'imagecreatefromgif',
			'imagecreatefromjpeg',
			'imagecreatefrompng',
			'imagecreatefromwbmp',
			'imagecreatefromwebp',
			'imagecreatefromxbm',
			'imagecreatefromxpm',
			'imagepsloadfont',
			'jpeg2wbmp',
			'png2wbmp',
			// 2nd params only, maybe make it standalone and check just the second param?
			'image2wbmp',
			'imagegd2',
			'imagegd',
			'imagegif',
			'imagejpeg',
			'imagepng',
			'imagewbmp',
			'imagewebp',
			'imagexbm',
			// From http://www.php.net/manual/en/ref.exif.php
			'exif_imagetype',
			'exif_read_data',
			'exif_thumbnail',
			'read_exif_data',
			// From http://www.php.net/manual/en/ref.hash.php
			'hash_file',
			'hash_hmac_file',
			'hash_update_file',
			// From http://www.php.net/manual/en/ref.misc.php
			'highlight_file',
			'php_check_syntax',
			'php_strip_whitespace',
			'show_source',
			// Various functions that open/read files
			'get_meta_tags',
			'hash_file',
			'hash_hmac_file',
			'hash_update_file',
			'md5_file',
			'sha1_file',
			'bzopen',
//Curl Functions
			'curl_exec',
			'curl_multi_exec',
		);
	}

//Borrow from https://github.com/FloeDesignTechnologies/phpcs-security-audit/blob/master/Security/Sniffs/Utils.php
	public static function getSystemexecFunctions() {
		return array(
			'exec',
			'passthru',
			'proc_open',
			'popen',
			'shell_exec',
			'system',
			'pcntl_exec'
		);
	}

	public static function getInclutions() {
		return array( 'include', 'include_once', 'require', 'require_once' );
	}

	//Borrow from https://github.com/FloeDesignTechnologies/phpcs-security-audit/blob/master/Security/Sniffs/Utils.php
	//Point to RIPs and SO https://stackoverflow.com/questions/3115559/exploitable-php-functions
	public static function getCallbackFunctions() {
		return array(
			'ob_start',
			'array_diff_uassoc',
			'array_diff_ukey',
			//'array_filter',
			'array_intersect_uassoc',
			'array_intersect_ukey',
			//'array_map',
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
		return array_merge( self::getDangeriousFunction(), self::getFilesystemFunctions() );
	}

	public static function getDangeriousFunction() {
		return array(
			'assert',
			'eval',
			'gzinflate'
		);
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

	/**
	 * @return array
	 */
	public static function getSanitizeFunctions() {
		return array(
			'sanitize_email',
			'sanitize_file_name',
			'sanitize_html_class',
			'sanitize_key',
			'sanitize_meta',
			'sanitize_mime_type',
			'sanitize_option',
			'sanitize_sql_orderby',
			'sanitize_text_field',
			'sanitize_textarea_field',
			'sanitize_title',
			'sanitize_title_for_query',
			'sanitize_title_with_dashes',
			'sanitize_user',
			'intval',
			'floatval'
		);
	}

	public static function getOutputFunctions() {
		return array(
			'echo',
			'print'
		);
	}

	public static function getEscapeFunction() {
		return array(
			'esc_html',
			'esc_html_',
			'esc_html_e',
			'esc_html_x',
			'esc_url',
			'esc_js',
			'esc_attr',
			'esc_attr_',
			'esc_attr_e',
			'esc_attr_x',
			'esc_textarea'
		);
	}
}