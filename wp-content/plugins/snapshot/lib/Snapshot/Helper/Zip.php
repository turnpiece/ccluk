<?php // phpcs:ignore

/**
 * Zip archive factory handler
 */
class Snapshot_Helper_Zip {

	const TYPE_ARCHIVE = 'archive';
	const TYPE_PCLZIP = 'pclzip';

	/**
	 * Spawn a ZIP archive object
	 *
	 * With empty or uknown variation, defaults to whatever
	 * type was set in plugin configuration.
	 *
	 * @param string $variation Optional ZIP variation to use
	 *
	 * @return object Snapshot_Helper_Zip_Abstract instance
	 */
	public static function get_object ($variation = false) {
		$variation = strtolower($variation);

		// Use explicit user-submitted ZIP library variation, if any
		if (empty($variation) || !in_array($variation, array(self::TYPE_PCLZIP, self::TYPE_ARCHIVE), true)) {
			$variation = strtolower(WPMUDEVSnapshot::instance()->config_data['config']['zipLibrary']);
		}

		// Allow for forced ZIP library variation
		if (defined('SNAPSHOT_FORCE_ZIP_LIBRARY') && SNAPSHOT_FORCE_ZIP_LIBRARY) {
			if (self::TYPE_ARCHIVE === SNAPSHOT_FORCE_ZIP_LIBRARY)
				$variation = self::TYPE_ARCHIVE;
			else if (self::TYPE_PCLZIP === SNAPSHOT_FORCE_ZIP_LIBRARY)
				$variation = self::TYPE_PCLZIP;
		}

		$library = self::TYPE_PCLZIP === strtolower($variation) && function_exists('gzopen')
			? 'Snapshot_Helper_Zip_Pclzip'
			: 'Snapshot_Helper_Zip_Archive'
		;
		return new $library();
	}

	/**
	 * Spawns and prepares a ZIP object instance
	 *
	 * This is how we get a ZIP archive handler ready to use
	 *
	 * @param string $path Full ZIP archive destination path
	 * @param string $variation Optional ZIP variation to use
	 *
	 * @return object Prepared ZIP object ready to use
	 */
	public static function get ($path, $variation = false) {
		$instance = self::get_object($variation);
		$instance->prepare($path);
		return $instance;
	}

}