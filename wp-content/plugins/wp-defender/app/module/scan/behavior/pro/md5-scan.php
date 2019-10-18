<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Module\Scan\Behavior\Pro;

use Hammer\Base\Behavior;
use WP_Defender\Behavior\Utils;

class MD5_Scan extends Behavior {
	/**
	 * This do nothing for prepare the files for content scan
	 *
	 * @param $args
	 * @param $current
	 *
	 * @return bool
	 */
	public function processItemInternal( $args, $current ) {
		$downloadDirs = Utils::instance()->getDefUploadDir() . DIRECTORY_SEPARATOR . 'md5-scan';
		if ( ! is_dir( $downloadDirs ) ) {
			wp_mkdir_p( $downloadDirs );
		}
		$ds = DIRECTORY_SEPARATOR;
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin' . $ds . 'includes' . $ds . 'file.php';
		}
		if ( is_object( $current ) ) {
			$slug        = $current->get_stylesheet();
			$archiveName = $slug . '.' . $current->Version . '.zip';
			$archiveURL  = "https://downloads.wordpress.org/theme/" . $archiveName;
		} else {
			$slug        = explode( '/', $current['slug'] );
			$slug        = array_shift( $slug );
			$archiveName = $slug . '.' . $current['Version'] . '.zip';
			$archiveURL  = "https://downloads.wordpress.org/plugin/" . $archiveName;
		}
		$tmp = download_url( $archiveURL );
		if ( is_wp_error( $tmp ) ) {
			//do nothing
			return true;
		}
		if ( ! function_exists( 'unzip_file' ) ) {
			include_once ABSPATH . '/wp-admin/includes/file.php';
		}
		global $wp_filesystem;
		if ( ! $wp_filesystem || ! is_object( $wp_filesystem ) ) {
			//init it
			WP_Filesystem();
		}
		$ret = unzip_file( $tmp, $downloadDirs  );
		unlink( $tmp );

		return true;
	}
}