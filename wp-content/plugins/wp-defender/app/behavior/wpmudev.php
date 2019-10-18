<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;

/**
 * This class contains everything relate to WPMUDEV
 * Class WPMUDEV
 * @package WP_Defender\Behavior
 * @since 2.2
 */
class WPMUDEV extends Behavior {
	/**
	 * @param $campaign
	 *
	 * @return string
	 */
	public function campaignURL( $campaign ) {
		$url = "https://premium.wpmudev.org/project/wp-defender/?utm_source=defender&utm_medium=plugin&utm_campaign=" . $campaign;

		return $url;
	}

	/**
	 * Get whitelabel status from Dev Dashboard
	 * Properties
	 *  - hide_branding
	 *  - hero_image
	 *  - footer_text
	 *  - change_footer
	 *  - hide_doc_link
	 *
	 * @return mixed
	 */
	public function whiteLabelStatus() {
		if ( \WP_Defender\Behavior\Utils::instance()->getAPIKey() ) {
			$site = \WPMUDEV_Dashboard::$site;
			if ( is_object( $site ) ) {
				$info = $site->get_wpmudev_branding( array() );
				return $info;
			}
		} else {
			return [
				'hide_branding' => false,
				'hero_image'    => '',
				'footer_text'   => '',
				'change_footer' => false,
				'hide_doc_link' => false
			];
		}
	}

	/**
	 * a quick helper for static class
	 * @return WPMUDEV
	 */
	public static function instance() {
		return new WPMUDEV();
	}

	/**
	 * Return the highcontrast css class if it is
	 * @return string
	 */
	public function maybeHighContrast() {
		return \WP_Defender\Module\Setting\Model\Settings::instance()->high_contrast_mode;
	}
}