<?php
/**
 * The admin view class for the Tag Manager module.
 *
 * @link    http://wpmudev.com
 * @since   3.3.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */

namespace Beehive\Core\Modules\Google_Tag_Manager\Views;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use Beehive\Core\Controllers\Assets;
use Beehive\Core\Utils\Abstracts\Base;

/**
 * Class Scripts
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */
class Admin extends Base {

	/**
	 * Render GTM admin settings page.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function settings() {
		echo '<div id="beehive-tag-manager-app"></div>';

		// Enqueue assets.
		Assets::instance()->enqueue_style( 'beehive-tag-manager' );
		Assets::instance()->enqueue_script( 'beehive-tag-manager' );
	}
}