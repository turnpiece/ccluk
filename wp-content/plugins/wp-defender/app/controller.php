<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender;

use Hammer\Helper\HTTP_Helper;
use Hammer\WP\Component;


class Controller extends Component {
	protected $slug;

	/**
	 * @return bool
	 */
	protected function isInPage() {
		return HTTP_Helper::retrieve_get( 'page' ) == $this->slug;
	}

	/**
	 * @param $view
	 *
	 * @return bool
	 */
	public function isView( $view ) {
		return HTTP_Helper::retrieve_get( 'view' ) == $view;
	}

	/**
	 * @return bool
	 */
	public function isDashboard() {
		return HTTP_Helper::retrieve_get( 'page' ) == 'wp-defender';
	}
}