<?php
/**
 * Admin class.
 *
 * @package Smush\App
 */

namespace Smush\App;

use Smush\Core\Core;
use Smush\Core\Helper;
use Smush\Core\Settings;
use Smush\WP_Smush;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Admin
 */
class Admin {

	/**
	 * Plugin pages.
	 *
	 * @var array
	 */
	public $pages = array();

	/**
	 * AJAX module.
	 *
	 * @var Ajax
	 */
	public $ajax;

	/**
	 * List of smush settings pages.
	 *
	 * @var array $plugin_pages
	 */
	public static $plugin_pages = array(
		'gallery_page_wp-smush-nextgen-bulk',
		'toplevel_page_smush-network',
		'toplevel_page_smush',
		'smush_page_smush-upgrade-network',
		'smush_page_smush-upgrade',
	);

	/**
	 * Admin constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_action( 'network_admin_menu', array( $this, 'add_menu_pages' ) );

		add_action( 'admin_init', array( $this, 'smush_i18n' ) );
		// Add information to privacy policy page (only during creation).
		add_action( 'admin_init', array( $this, 'add_policy' ) );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->ajax = new Ajax();
		}

		// Register media library UI.
		new Media_Library();

		add_filter( 'plugin_action_links_' . WP_SMUSH_BASENAME, array( $this, 'settings_link' ) );
		add_filter( 'network_admin_plugin_action_links_' . WP_SMUSH_BASENAME, array( $this, 'settings_link' ) );

		// Prints a membership validation issue notice in Media Library.
		add_action( 'admin_notices', array( $this, 'media_library_membership_notice' ) );
	}

	/**
	 * Load translation files.
	 */
	public function smush_i18n() {
		load_plugin_textdomain(
			'wp-smushit',
			false,
			dirname( WP_SMUSH_BASENAME ) . '/languages'
		);
	}

	/**
	 * Register JS and CSS.
	 */
	private function register_scripts() {
		// Share UI JS.
		wp_register_script( 'smush-sui', WP_SMUSH_URL . 'app/assets/js/smush-sui.min.js', array( 'jquery' ), WP_SHARED_UI_VERSION, true );

		// Main JS.
		wp_register_script( 'smush-admin', WP_SMUSH_URL . 'app/assets/js/smush-admin.min.js', array( 'jquery', 'smush-sui', 'underscore', 'wp-color-picker' ), WP_SMUSH_VERSION, true );

		// Main CSS.
		wp_register_style( 'smush-admin', WP_SMUSH_URL . 'app/assets/css/smush-admin.min.css', array(), WP_SMUSH_VERSION );

		// Styles that can be used on all pages in the WP backend.
		wp_register_style( 'smush-admin-common', WP_SMUSH_URL . 'app/assets/css/smush-common.min.css', array(), WP_SMUSH_VERSION );

		// Dismiss update info.
		WP_Smush::get_instance()->core()->mod->smush->dismiss_update_info();
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$current_page   = '';
		$current_screen = '';

		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
			$current_page   = ! empty( $current_screen ) ? $current_screen->base : $current_page;
		}

		if ( ! in_array( $current_page, Core::$pages, true ) ) {
			return;
		}

		// Allows to disable enqueuing smush files on a particular page.
		if ( ! apply_filters( 'wp_smush_enqueue', true ) ) {
			return;
		}

		$this->register_scripts();

		// Load on all Smush page only.
		if ( isset( $current_screen->id ) && in_array( $current_screen->id, self::$plugin_pages, true ) ) {
			// Smush admin (smush-admin) includes the Shared UI.
			wp_enqueue_style( 'smush-admin' );
			wp_enqueue_script( 'smush-wpmudev-sui' );
		}

		// We need it on media pages and Smush pages.
		wp_enqueue_script( 'smush-admin' );
		wp_enqueue_style( 'smush-admin-common' );

		// Localize translatable strings for js.
		WP_Smush::get_instance()->core()->localize();
	}

	/**
	 * Adds a Smush pro settings link on plugin page.
	 *
	 * @param array $links        Current links.
	 * @param bool  $url_only     Get only URL.
	 * @param bool  $networkwide  Do we need the network wide setting url.
	 *
	 * @return array|string
	 */
	public function settings_link( $links, $url_only = false, $networkwide = false ) {
		$settings_page = is_multisite() && is_network_admin() ? network_admin_url( 'admin.php?page=smush' ) : menu_page_url( 'smush', false );
		// If networkwide setting url is needed.
		$settings_page = $url_only && $networkwide && is_multisite() ? network_admin_url( 'admin.php?page=smush' ) : $settings_page;
		$settings      = '<a href="' . $settings_page . '">' . __( 'Settings', 'wp-smushit' ) . '</a>';

		// Return only settings page link.
		if ( $url_only ) {
			return $settings_page;
		}

		// Added a fix for weird warning in multisite, "array_unshift() expects parameter 1 to be array, null given".
		if ( ! empty( $links ) ) {
			array_unshift( $links, $settings );
		} else {
			$links = array( $settings );
		}

		// Upgrade link.
		if ( ! WP_Smush::is_pro() ) {
			$upgrade_url = add_query_arg(
				array(
					'utm_source'   => 'smush',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'smush_pluginlist_upgrade',
				),
				esc_url( 'https://premium.wpmudev.org/project/wp-smush-pro/' )
			);

			$links['upgrade'] = '<a href="' . esc_url( $upgrade_url ) . '" aria-label="' . esc_attr( __( 'Upgrade to Smush Pro', 'wp-smushit' ) ) . '" target="_blank" style="color: #1ABC9C;">' . esc_html__( 'Upgrade', 'wp-smushit' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Add menu pages.
	 */
	public function add_menu_pages() {
		$title = WP_Smush::is_pro() ? esc_html__( 'Smush Pro', 'wp-smushit' ) : esc_html__( 'Smush', 'wp-smushit' );

		if ( Settings::can_access( false, true ) ) {
			$this->pages['smush']           = new Pages\Dashboard( 'smush', $title );
			$this->pages['smush-dashboard'] = new Pages\Dashboard( 'smush', __( 'Dashboard', 'wp-smushit' ), 'smush' );

			if ( ! WP_Smush::is_pro() ) {
				$this->pages['smush-upgrade'] = new Pages\Upgrade( 'smush-upgrade', __( 'Smush Pro', 'wp-smushit' ), 'smush' );
			}
		}

		// Add a bulk smush option for NextGen gallery.
		if ( defined( 'NGGFOLDER' ) && WP_Smush::get_instance()->core()->nextgen->is_enabled() && WP_Smush::is_pro() && ! is_network_admin() ) {
			$this->pages['nextgen'] = new Pages\Nextgen( 'wp-smush-nextgen-bulk', $title, NGGFOLDER, true );
		}
	}

	/**
	 * Add Smush Policy to "Privacy Policy" page during creation.
	 *
	 * @since 2.3.0
	 */
	public function add_policy() {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}

		$content  = '<h3>' . __( 'Plugin: Smush', 'wp-smushit' ) . '</h3>';
		$content .=
			'<p>' . __( 'Note: Smush does not interact with end users on your website. The only input option Smush has is to a newsletter subscription for site admins only. If you would like to notify your users of this in your privacy policy, you can use the information below.', 'wp-smushit' ) . '</p>';
		$content .=
			'<p>' . __( 'Smush sends images to the WPMU DEV servers to optimize them for web use. This includes the transfer of EXIF data. The EXIF data will either be stripped or returned as it is. It is not stored on the WPMU DEV servers.', 'wp-smushit' ) . '</p>';
		$content .=
			'<p>' . sprintf(
			__( "Smush uses the Stackpath Content Delivery Network (CDN). Stackpath may store web log information of site visitors, including IPs, UA, referrer, Location and ISP info of site visitors for 7 days. Files and images served by the CDN may be stored and served from countries other than your own. Stackpath's privacy policy can be found %1\$shere%2\$s.", 'wp-smushit' ),
			'<a href="https://www.stackpath.com/legal/privacy-statement/" target="_blank">',
			'</a>'
		) . '</p>';

		if ( strpos( WP_SMUSH_DIR, 'wp-smushit' ) !== false ) {
			// Only for wordpress.org members.
			$content .=
				'<p>' . __( 'Smush uses a third-party email service (Drip) to send informational emails to the site administrator. The administrator\'s email address is sent to Drip and a cookie is set by the service. Only administrator information is collected by Drip.', 'wp-smushit' ) . '</p>';
		}

		wp_add_privacy_policy_content(
			__( 'WP Smush', 'wp-smushit' ),
			wp_kses_post( wpautop( $content, false ) )
		);
	}

	/**
	 * Prints the Membership Validation issue notice
	 */
	public function media_library_membership_notice() {
		// No need to print it for free version.
		if ( ! WP_Smush::is_pro() ) {
			return;
		}

		// Show it on Media Library page only.
		$screen = get_current_screen();
		if ( ! empty( $screen ) && 'upload' === $screen->id ) {
			$this->get_user_validation_message( false );
		}
	}

	/**
	 * Get membership validation message.
	 *
	 * @param bool $notice Is a notice.
	 */
	public function get_user_validation_message( $notice = true ) {
		$notice_class = $notice ? ' sui-notice sui-notice-warning' : ' notice notice-warning is-dismissible';
		$wpmu_contact = '<a href="' . esc_url( 'https://premium.wpmudev.org/contact' ) . '" target="_blank">';
		$recheck_link = '<a href="#" id="wp-smush-revalidate-member" data-message="%s">';
		?>

		<div id="wp-smush-invalid-member" data-message="<?php esc_attr_e( 'Validating..', 'wp-smushit' ); ?>" class="sui-hidden hidden <?php echo esc_attr( $notice_class ); ?>">
			<p>
				<?php
				printf(
					/* translators: $1$s: recheck link, $2$s: closing a tag, %3$s; contact link, %4$s: closing a tag */
					esc_html__(
						'It looks like Smush couldn’t verify your WPMU DEV membership so Pro features
					have been disabled for now. If you think this is an error, run a %1$sre-check%2$s or get in touch
					with our %3$ssupport team%4$s.',
						'wp-smushit'
					),
					$recheck_link,
					'</a>',
					$wpmu_contact,
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Shows a option to ignore the Image ids which can be resmushed while bulk smushing.
	 *
	 * @param bool|int $count  Resmush + unsmushed image count.
	 *
	 * @return mixed $notice
	 */
	public function bulk_resmush_content( $count = false ) {
		// If we already have count, don't fetch it.
		if ( false === $count && $resmush_ids = get_option( 'wp-smush-resmush-list' ) ) {
			// If we have the resmush ids list, Show Resmush notice and button.
			// Get the actual remainaing count.
			if ( ! isset( WP_Smush::get_instance()->core()->remaining_count ) ) {
				WP_Smush::get_instance()->core()->setup_global_stats();
			}

			$count = WP_Smush::get_instance()->core()->remaining_count;
		}

		$notice = '';

		// Show only if we have any images to ber resmushed.
		if ( $count > 0 ) {
			$notice  = '<div class="sui-notice sui-notice-warning wp-smush-resmush-notice wp-smush-remaining" tabindex="0">';
			$notice .= '<p>';
			$notice .= '<span class="wp-smush-notice-text">';
			$notice .= sprintf(
				/* translators: %1$s: user name, %2$s: strong tag, %3$s: span tag, %4$d: number of remaining umages, %5$s: closing span tag, %6$s: closing strong tag  */
				_n( '%1$s, you have %2$s%3$s%4$d%5$s attachment%6$s that needs re-compressing!', '%1$s, you have %2$s%3$s%4$d%5$s attachments%6$s that need re-compressing!', $count, 'wp-smushit' ),
				esc_html( Helper::get_user_name() ),
				'<strong>',
				'<span class="wp-smush-remaining-count">',
				absint( $count ),
				'</span>',
				'</strong>'
			);
			$notice .= '</span>';
			$notice .= '</p>';
			$notice .= '</div>';
		}

		return $notice;
	}

}