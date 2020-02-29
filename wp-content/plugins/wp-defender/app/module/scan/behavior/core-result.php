<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\File_Helper;
use Hammer\Helper\Log_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;

class Core_Result extends Behavior {

	/**
	 * Query all the info to show up on frontend
	 * @return array
	 */
	public function getInfo() {
		$full_path = $this->getRaw()['file'];

		return [
			'id'         => $this->getOwner()->id,
			'type'       => 'core',
			'file_name'  => pathinfo( $full_path, PATHINFO_FILENAME ),
			'full_path'  => $full_path,
			'date_added' => Utils::instance()->formatDateTime( filemtime( $full_path ) ),
			'size'       => Utils::instance()->makeReadable( filesize( $full_path ) ),
			'scenario'   => $this->getRaw()['type'],
			'short_desc' => $this->getIssueDetail()
		];
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		$raw = $this->getRaw();

		return pathinfo( $raw['file'], PATHINFO_BASENAME );
	}

	/**
	 * @return mixed
	 */
	public function getSubtitle() {
		$raw = $this->getRaw();

		return $raw['file'];
	}

	/**
	 * Get this slug, will require for checking ignore status while scan
	 * @return string
	 */
	public function getSlug() {
		$raw = $this->getRaw();

		return $raw['file'];
	}

	/**
	 * @return string
	 */
	public function getIssueDetail() {
		return $this->getIssueSummary();
	}

	/**
	 * @return string
	 */
	public function getIssueSummary() {
		$raw = $this->getRaw();
		if ( $raw['type'] == 'unknown' ) {
			return esc_html__( "Unknown file in WordPress core", wp_defender()->domain );
		} elseif ( $raw['type'] == 'dir' ) {
			return esc_html__( "This directory does not belong to WordPress core", wp_defender()->domain );
		} elseif ( $raw['type'] == 'modified' ) {
			return esc_html__( "This WordPress core file appears modified", wp_defender()->domain );
		}
	}

	/**
	 * Delete file referenced by this item and delete item itself
	 * @return \WP_Error|bool
	 */
	public function purge() {
		//remove the file first
		$raw = $this->getRaw();
		if ( $raw['type'] == 'unknown' ) {
			$res = unlink( $raw['file'] );
			if ( $res == false ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
			}
			$this->getOwner()->delete();

			return true;
		} elseif ( $raw['type'] == 'modified' ) {
			return new \WP_Error( Error_Code::INVALID, __( "This file can't be removed", wp_defender()->domain ) );
		} elseif ( $raw['type'] == 'dir' ) {
			$res = $this->deleteFolder( $raw['file'] );
			if ( is_wp_error( $res ) ) {
				return $res;
			}
			$this->getOwner()->delete();

			return true;
		}
	}

	/**
	 * Only if the file is modified, we will download the original source and replace it
	 * @return bool|\WP_Error
	 */
	public function resolve() {
		$originSrc = $this->getOriginalSource();
		$raw       = $this->getRaw();
		if ( $raw['type'] != 'modified' ) {
			return new \WP_Error( Error_Code::INVALID, __( "This file is not resolvable", wp_defender()->domain ) );
		}

		if ( ! is_writeable( $raw['file'] ) ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE, sprintf( esc_html__( "It seems the %s file is currently using by another process or isn't writeable.", wp_defender()->domain ), $raw['file'] ) );
		}

		file_put_contents( $raw['file'], $originSrc, LOCK_EX );
		$this->getOwner()->markAsResolved();

		return true;
	}

	/**
	 * @return string
	 */
	public function getSrcCode() {
		if ( is_file( $this->getSubtitle() ) || is_dir( $this->getSubtitle() ) ) {
//			$mime = mime_content_type( $this->getSubtitle() );
//			if ( strpos( $mime, 'text/' ) !== 0 ) {
//				Utils::instance()->log( sprintf( 'file %s with mime %s',$this->getSubtitle(),$mime ), 'scan' );
//
//				return __( "This file type is not supported", wp_defender()->domain );
//			}
			$file_size = filesize( $this->getSubtitle() );
			if ( $file_size > 3145728 ) {
				return __( "This file size is too big", wp_defender()->domain );
			}
			$raw = $this->getRaw();
			if ( $raw['type'] == 'unknown' ) {
				$content = file_get_contents( $this->getSubtitle() );
				if ( function_exists( 'mb_convert_encoding' ) ) {
					$content = mb_convert_encoding( $content, 'UTF-8', 'ASCII' );
				}
				$entities = htmlentities( $content, null, 'UTF-8', false );

				return $entities;
			} elseif ( $raw['type'] == 'modified' ) {
				$original = $this->getOriginalSource();
				$current  = file_get_contents( $this->getSubtitle() );
				$diff     = $this->textDiff( $original, $current );

				return $diff;
			} elseif ( $raw['type'] == 'dir' ) {
				$files = File_Helper::findFiles( $raw['file'], true, false );

				return implode( PHP_EOL, $files );
			}
		}
	}

	/**
	 * @param $left_string
	 * @param $right_string
	 *
	 * @return string
	 */
	protected function textDiff( $left_string, $right_string ) {
		if ( ! class_exists( 'Text_Diff', false ) || ! class_exists( 'Text_Diff_Renderer_inline', false ) ) {
			require( ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'wp-diff.php' );
		}
		$left_lines  = explode( "\n", $left_string );
		$right_lines = explode( "\n", $right_string );
		$text_diff   = new \Text_Diff( 'auto', array(
			$right_lines,
			$left_lines
		) );
		$renderer    = new \Text_Diff_Renderer_inline();

		return $renderer->render( $text_diff );
	}

	/**
	 * @return Result_Item;
	 */
	protected function getOwner() {
		return $this->owner;
	}

	/**
	 * @return array
	 */
	protected function getRaw() {
		return $this->getOwner()->raw;
	}


	/**
	 * Getting the latest original source from svn.wordpress.org
	 * @return mixed|string
	 */
	protected function getOriginalSource() {
		$raw  = $this->getRaw();
		$file = $raw['file'];
		global $wp_version;
		$relPath         = Scan_Api::convertToUnixPath( $file );
		$source_file_url = "http://core.svn.wordpress.org/tags/$wp_version/" . $relPath;
		$ds              = DIRECTORY_SEPARATOR;
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin' . $ds . 'includes' . $ds . 'file.php';
		}
		$tmp = download_url( $source_file_url );
		if ( is_wp_error( $tmp ) ) {
			return $tmp;
		}
		$content = file_get_contents( $tmp );
		@unlink( $tmp );

		return $content;
	}

	private function deleteFolder( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$it    = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new \RecursiveIteratorIterator( $it,
			\RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				$res = @rmdir( $file->getRealPath() );
			} else {
				$res = @unlink( $file->getRealPath() );
			}
			if ( $res == false ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
			}
		}
		$res = @rmdir( $dir );
		if ( $res == false ) {
			return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
		}

		return true;
	}
}