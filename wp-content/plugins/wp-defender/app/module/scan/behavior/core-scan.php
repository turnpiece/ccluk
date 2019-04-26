<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\File_Helper;
use Hammer\Helper\Log_Helper;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;

class Core_Scan extends Behavior {
	public function processItemInternal( $args, $current ) {
		$model = $args['model'];

		$status = Result_Item::STATUS_ISSUE;
		if ( ( $oid = Scan_Api::isIgnored( $current ) ) !== false ) {
			//if this is ignored, we just need to update the parent ID
			$item           = Result_Item::findByID( $oid );
			$item->parentId = $model->id;
			$item->save();

			return true;
		}

		$checksums = Scan_Api::getCoreChecksums();

		if ( ! is_array( $checksums ) ) {
			$checksums = array();
		} else {
			$item           = new Result_Item();
			$item->parentId = $model->id;
			$item->type     = 'core';
			$item->status   = $status;
			$relPath        = Scan_Api::convertToUnixPath( $current ); //Windows File path fix set outside to be used in both file and dir checks
			$current_path   = Scan_Api::convertToWindowsAbsPath( $current ); //Windows needs fixing for the paths
			if ( is_file( $current ) ) {
				//check if this is core or not
				if ( isset( $checksums[ $relPath ] ) && strcmp( md5_file( $current ), $checksums[ $relPath ] ) !== 0 ) {
					$item->raw = array(
						'type' => 'modified',
						'file' => $current_path
					);
					$id        = $item->save();
				} elseif ( ! isset( $checksums[ $relPath ] ) ) {
					//we need to check if this is wp-config, a hot fix for windows
					if ( DIRECTORY_SEPARATOR == '\\' && $relPath == 'wp-config.php' ) {
						return null;
					}
					//some common files like robots.txt, we will move this to content scan
					if ( in_array( $relPath, array( 'robots.txt' ) ) ) {
						return null;
					}
					$item->raw = array(
						'type' => 'unknown',
						'file' => $current_path
					);
					$id        = $item->save();
				}
			} elseif ( is_dir( $current ) ) {
				if ( in_array( $relPath, array(
					'wp-content',
					'wp-admin',
					'wp-includes'
				) ) ) {
					return null;
				}
				//check if this empty then do nothing
				$files = File_Helper::findFiles( $current, true, false );
				if ( count( $files ) ) {
					$item->raw = array(
						'type' => 'dir',
						'file' => $current_path
					);
					$id        = $item->save();
				}
			} else {
				//this is not exist anymore, just move on
				return null;
			}
		}

		return true;
	}
}