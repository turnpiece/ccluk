<?php
/**
 * The scripts view class for the Tag Manager module.
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

use Beehive\Core\Helpers\General;
use Beehive\Core\Utils\Abstracts\Base;
use Beehive\Core\Modules\Google_Tag_Manager\Helper;

/**
 * Class Scripts
 *
 * @package Beehive\Core\Modules\Google_Tag_Manager\Views
 */
class Scripts extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function init() {
		// GTM script.
		add_action( 'beehive_gtm_frontend_inline_scripts_header', array( $this, 'tag_manager' ), 10, 2 );

		// Add noscript script.
		add_action( 'wp_footer', array( $this, 'no_script' ) );
	}

	/**
	 * Render GTM script to the front end.
	 *
	 * We are adding the script as inline.
	 *
	 * @param array $scripts Scripts array.
	 * @param bool  $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return array
	 */
	public function tag_manager( $scripts, $network ) {
		// No need to continue if container ID is empty.
		if ( ! Helper::is_ready( $network ) || ! Helper::can_output_script( $network ) ) {
			return $scripts;
		}

		// Get container ID.
		$container = beehive_analytics()->settings->get(
			'container',
			'gtm',
			$network
		);

		/**
		 * Filter to add/remove variables to dataLayer variable.
		 *
		 * @param array $vars    Variables.
		 * @param bool  $network Network flag.
		 *
		 * @since 3.3.0
		 */
		$vars = (array) apply_filters( 'beehive_google_gtm_datalayer_vars', array(), $network );

		// Format the vars.
		$data_layer = wp_json_encode( $vars, JSON_UNESCAPED_UNICODE );

		// Get data layer name.
		$data_layer_name = Helper::get_datalyer_name( $network );

		// Add data layer.
		$scripts['gtm-datalayer'] = empty( $vars ) ? 'var ' . $data_layer_name . ' = [];' : 'var ' . $data_layer_name . ' = [' . $data_layer . '];';

		// Add GTM script.
		$scripts['gtm-script'] = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
			new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
			j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
			'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
			})(window,document,'script','" . $data_layer_name . "','" . esc_html( $container ) . "');";

		return $scripts;
	}

	/**
	 * Add GTM no script iframe to site footer.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	public function no_script() {
		// For network sites.
		if ( General::is_networkwide() && Helper::can_output_network_script() ) {
			$this->no_script_output( true );
		}

		// For single/subsites.
		$this->no_script_output();
	}

	/**
	 * Render GTM noscript to the front end.
	 *
	 * We are adding the script as backup for no javascript browser.
	 *
	 * @param bool $network Network flag.
	 *
	 * @since 3.3.0
	 *
	 * @return void
	 */
	private function no_script_output( $network = false ) {
		// No need to continue if container ID is empty.
		if ( ! Helper::is_ready( $network ) || ! Helper::can_output_script( $network ) ) {
			return;
		}

		// Get container ID.
		$container = beehive_analytics()->settings->get( 'container', 'gtm', $network );
		?>
		<noscript>
			<iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_html( $container ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
		</noscript>
		<?php
	}
}