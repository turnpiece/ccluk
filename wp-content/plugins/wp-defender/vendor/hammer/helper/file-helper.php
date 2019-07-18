<?php
/**
 * Author: Hoang Ngo
 */

namespace Hammer\Helper;

use Hammer\Base\File;

class File_Helper {
	/**
	 * @param $path
	 * @param bool $include_file
	 * @param bool $include_dir
	 * @param array $exclude
	 * @param array $include
	 * @param bool $is_recursive
	 * @param bool $max_size
	 *
	 * @return array
	 */
	public static function findFiles( $path, $include_file = true, $include_dir = true, $exclude = array(), $include = array(), $is_recursive = true, $max_size = false, $is_hidden = false ) {
		$tv = new File( $path, $include_file, $include_dir, $include, $exclude, $is_recursive, $is_hidden );
		if ( $max_size != false ) {
			$tv->max_filesize = $max_size;
		}
		$result = $tv->get_dir_tree();
		unset( $v );

		return $result;
	}

	/**
	 * @param $dir
	 */
	public static function deleteFolder( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$it    = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );
		$files = new \RecursiveIteratorIterator( $it,
			\RecursiveIteratorIterator::CHILD_FIRST );
		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				rmdir( $file->getRealPath() );
			} else {
				unlink( $file->getRealPath() );
			}
		}
		rmdir( $dir );
	}
}