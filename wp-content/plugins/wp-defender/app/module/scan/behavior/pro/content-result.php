<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Helper\Log_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Component\Error_Code;
use WP_Defender\Module\Scan\Model\Result_Item;

class Content_Result extends \Hammer\Base\Behavior {
	public function getInfo() {
		$full_path = $this->getRaw()['file'];
		$tracer    = [];
		foreach ( $this->getRaw()['meta'] as $item ) {
			if ( ! isset( $tracer[ $item['line'] ] ) ) {
				$tracer[ $item['line'] ] = [
					'text'   => $item['text'],
					'line'   => $item['line'],
					'offset' => $item['offset'],
					'column' => $item['length'],
					'code'   => $this->getCodeBlock( $item )
				];
			}
		}

		return [
			'id'         => $this->getOwner()->id,
			'type'       => 'content',
			'file_name'  => pathinfo( $full_path, PATHINFO_FILENAME ),
			'full_path'  => $full_path,
			'date_added' => file_exists( $full_path ) ? Utils::instance()->formatDateTime( filemtime( $full_path ) ) : 'N/A',
			'size'       => file_exists( $full_path ) ? Utils::instance()->makeReadable( filesize( $full_path ) ) : 'N/A',
			'short_desc' => $this->getIssueDetail(),
			'tracer'     => $tracer
		];
	}

	private function getCodeBlock( $item ) {
		if ( ! file_exists( $this->getRaw()['file'] ) ) {
			return __( "File not exists!", wp_defender()->domain );
		}
		$line   = $item['line'] - 1;
		$offset = $item['offset'];
		$column = $item['length'];
		$code   = file( $this->getRaw()['file'] );
		//we'll get lines +-1
		$margin = 3;
		for ( $j = $line - $margin; $j <= $line + $margin; $j ++ ) {
			$lines[] = isset( $code[ $j ] ) ? $code[ $j ] : null;
		}
//		$lines[]  = isset( $code[ $line - 1 ] ) ? $code[ $line - 1 ] : null;
//		$lines[]  = $code[ $line ];
//		$lines[]  = isset( $code[ $line + 1 ] ) ? $code[ $line + 1 ] : null;
		$lines    = array_filter( $lines );
		$entities = htmlentities( implode( '', $lines ), ENT_QUOTES . ENT_HTML5, 'UTF-8' );
		$entities = str_replace( '&lt;mark&gt;', '<mark>', $entities, $count );
		$entities = str_replace( '&lt;/mark&gt;', '</mark>', $entities, $count );

		return $entities;
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
	 * @return string|void
	 */
	public function getIssueDetail() {
		return $this->getIssueSummary();
	}

	/**
	 * @return string|void
	 */
	public function getIssueSummary() {
		return __( "Suspicious function found", wp_defender()->domain );
	}

	public function getSrcCode() {
		$raw = $this->getRaw();
		//do a dry check first
		$useOldFunc = true;
		foreach ( $raw['meta'] as $meta ) {
			if ( isset( $meta['offset'] ) ) {
				$useOldFunc = false;
				break;
			}
		}

		if ( $useOldFunc ) {
			return $this->_getSrcCode();
		}

		$content = file_get_contents( $raw['file'] );

		//debug
		$plus = 0;
		foreach ( $raw['meta'] as $meta ) {
			$offset = $meta['offset'];
			//move to new index, cause we have to add the length of <mark></mark>
			$offset  = $offset + $plus;
			$content = substr_replace( $content, '<mark>', $offset, 0 );
			$plus    += strlen( '<mark>' );
			$content = substr_replace( $content, '</mark>', $offset + $meta['length'] + strlen( '<mark>' ), 0 );
			$plus    += strlen( '</mark>' );
		}

		$entities = htmlentities( $content, ENT_QUOTES . ENT_HTML5, 'UTF-8' );
		$entities = str_replace( '&lt;mark&gt;', '<mark>', $entities, $count );
		$entities = str_replace( '&lt;/mark&gt;', '</mark>', $entities, $count );

		return $entities;
		//return '<pre class="line-numbers inner-sourcecode"><code class="language-php">' . $entities . '</code></pre>';
	}

	/**
	 * @return string
	 */
	public function _getSrcCode() {
		return null;
	}

	public function purge() {
		//remove the file first
		$raw  = $this->getRaw();
		$file = $raw['file'];
		if ( strpos( $file, WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' ) === 0 ) {
			//find the plugin
			$revPath = str_replace( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR, '', $file );
			$pools   = explode( '/', $revPath );
			//the path should be first item in pools
			$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pools[0];
			$res  = $this->deleteFolder( $path );
			if ( is_wp_error( $res ) ) {
				return $res;
			}
			$this->getOwner()->delete();
		} elseif ( strpos( $file, WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' ) === 0 ) {
			//find the theme
			$revPath = str_replace( WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR, '', $file );
			$pools   = explode( '/', $revPath );
			//the path should be first item in pools
			$path = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pools[0];
		} else {
			if ( $file == ABSPATH . 'wp-config.php' ) {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "wp-config.php can't be removed. Please remove the suspicious code manually.", wp_defender()->domain ) );
			}
			$res = unlink( $raw['file'] );
			if ( $res ) {
				$this->getOwner()->delete();
			} else {
				return new \WP_Error( Error_Code::NOT_WRITEABLE, __( "Defender doesn't have enough permission to remove this file", wp_defender()->domain ) );
			}
		}

		return true;
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
}