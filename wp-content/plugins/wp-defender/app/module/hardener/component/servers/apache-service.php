<?php
/**
 * Author: Paul Kevin
 */

namespace WP_Defender\Module\Hardener\Component\Servers;

use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Hardener\IRule_Service;
use WP_Defender\Module\Hardener\Rule_Service;

class Apache_Service extends Rule_Service implements IRule_Service {

	/**
	 * Exclude file paths
	 *
	 * @var array|bool|mixed
	 */
	private $exclude_file_paths = array();

	/**
	 * New htaccess file
	 *
	 * @var array|bool|mixed
	 */
	private $new_htconfig = array();

	/**
	 * The htaccess inside wp-content
	 * @var string
	 */
	public $contentdir_path = null;

	/**
	 * The htaccess path inside wp-includes
	 * @var null
	 */
	public $includedir_path = null;

	/**
	 * @return bool
	 */
	public function check() {
		return true;
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function process() {
		$ret = $this->protectContentDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		$ret = $this->protectIncludesDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		$ret = $this->protectUploadsDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		return true;
	}

	public function protectContentDir() {
		$ht_path = $this->contentdir_path;
		if ( $ht_path == null ) {
			$ht_path = WP_CONTENT_DIR . '/' . '.htaccess';
		}
		if ( ! file_exists( $ht_path ) ) {
			if ( file_put_contents( $ht_path, '', LOCK_EX ) === false ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE,
					sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
			}
		} elseif ( ! is_writeable( $ht_path ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
		}

		$exists_rules = $this->cleanupOldRules( file_get_contents( $ht_path ) );
		$rule         = [
			'## WP Defender - Protect PHP Executed ##',
			'<Files *.php>',
			$this->generateHtAccessRule( false ),
			'</Files>',
		];
		if ( ! empty( $this->exclude_file_paths ) ) {
			foreach ( $this->exclude_file_paths as $file_path ) {
				$rule[] = sprintf( "<Files %s>", sanitize_file_name( $file_path ) );
				$rule[] = $this->generateHtAccessRule( true );
				$rule[] = "</Files>";
			}
		}
		$rule[] = '## WP Defender - End ##';
		file_put_contents( $ht_path, $exists_rules . implode( PHP_EOL, $rule ), LOCK_EX );
	}

	public function protectIncludesDir() {
		$ht_path = $this->includedir_path;
		if ( $ht_path == null ) {
			$ht_path = ABSPATH . WPINC . '/' . '.htaccess';
		}
		if ( ! is_file( $ht_path ) ) {
			if ( file_put_contents( $ht_path, '', LOCK_EX ) === false ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE,
					sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
			}
		} elseif ( ! is_writeable( $ht_path ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
		}
		$exists_rules = $this->cleanupOldRules( file_get_contents( $ht_path ) );

		$rule = [
			'## WP Defender - Protect PHP Executed ##',
			'<Files *.php>',
			$this->generateHtAccessRule( false ),
			'</Files>',
			'<Files wp-tinymce.php>',
			$this->generateHtAccessRule( true ),
			'</Files>',
			'<Files ms-files.php>',
			$this->generateHtAccessRule( true ),
			'</Files>',
			'## WP Defender - End ##',
		];
		//no exclude here
		file_put_contents( $ht_path, $exists_rules . implode( PHP_EOL, $rule ), LOCK_EX );
	}

	/**
	 * Protect uploads directory
	 * This only when user provide a custom uploads
	 */
	public function protectUploadsDir() {
		if ( defined( 'UPLOADS' ) ) {
			$this->contentdir_path = ABSPATH . UPLOADS . '/' . '.htaccess';
			//should be same with protect content dirs
			$this->protectContentDir();
		}
	}

	public function unProtectContentDir() {
		$ht_path = $this->contentdir_path;
		if ( $ht_path == null ) {
			$ht_path = WP_CONTENT_DIR . '/' . '.htaccess';
		}
		if ( ! file_exists( $ht_path ) ) {
			//do nothing
			return;
		}
		if ( ! is_writeable( $ht_path ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
		}
		$ht_config = $this->cleanupOldRules( file_get_contents( $ht_path ) );
		$ht_config = trim( $ht_config );
		file_put_contents( $ht_path, trim( $ht_config ), LOCK_EX );
	}

	public function unProtectIncludeDir() {
		$ht_path = $this->includedir_path;
		if ( $ht_path == null ) {
			$ht_path = ABSPATH . WPINC . '/' . '.htaccess';
		}
		if ( ! is_writeable( $ht_path ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE,
				sprintf( __( "The file %s is not writable", wp_defender()->domain ), $ht_path ) );
		}
		$ht_config = $this->cleanupOldRules( file_get_contents( $ht_path ) );
		file_put_contents( $ht_path, trim( $ht_config ), LOCK_EX );
	}

	public function unProtectUploadDir() {
		if ( defined( 'UPLOADS' ) ) {
			$this->contentdir_path = ABSPATH . UPLOADS . '/' . '.htaccess';
			$this->unProtectContentDir();
		}
	}

	/**
	 * @return bool|\WP_Error
	 */
	public function revert() {
		$ret = $this->unProtectContentDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		$ret = $this->unProtectIncludeDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}
		$ret = $this->unProtectUploadDir();
		if ( is_wp_error( $ret ) ) {
			return $ret;
		}

		return true;
	}

	/**
	 * Set the exclude file paths
	 *
	 * @param String $paths
	 */
	public function setExcludeFilePaths( $paths ) {
		if ( ! empty( $paths ) ) {
			$this->exclude_file_paths = explode( "\n", $paths );
		}
	}


	/**
	 * Get the exclude file paths
	 *
	 * @return Array - $exclude_file_paths
	 */
	public function getExcludedFilePaths() {
		return $this->exclude_file_paths;
	}

	/**
	 * Set the exclude file paths
	 *
	 * @param String $paths
	 */
	public function setHtConfig( $config = array() ) {
		if ( ! empty( $config ) ) {
			$this->new_htconfig = $config;
		}
	}


	/**
	 * Get the new HT config
	 *
	 * @return Array - $new_htconfig
	 */
	public function getNewHtConfig() {
		return $this->new_htconfig;
	}

	/**
	 * @param $exists_rules
	 *
	 * @return string|string[]|null
	 */
	private function cleanupOldRules( $exists_rules ) {
		$pattern = '/(## WP Defender - Protect PHP Executed ##((.|\n)*)## WP Defender - End ##)/';
		if ( preg_match( $pattern, $exists_rules ) ) {
			//replace it
			$exists_rules = preg_replace( $pattern, '', $exists_rules );
		}
		$exists_rules = trim( $exists_rules );
		if ( strlen( $exists_rules ) ) {
			$exists_rules .= PHP_EOL;
		}

		return $exists_rules;
	}

	/**
	 * Return the correct apache rules for allow/deny
	 *
	 * @return String
	 */
	protected function generateHtAccessRule( $allow = true ) {
		$version = Utils::instance()->determineApacheVersion();
		if ( floatval( $version ) >= 2.4 ) {
			if ( $allow ) {
				return 'Require all granted';
			} else {
				return 'Require all denied';
			}
		} else {
			if ( $allow ) {
				return 'Allow from all';
			} else {
				return 'Order allow,deny' . PHP_EOL .
				       'Deny from all';
			}
		}
	}
}