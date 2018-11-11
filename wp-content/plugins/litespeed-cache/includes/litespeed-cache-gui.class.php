<?php
/**
 * The frontend GUI class.
 *
 * @since      	1.3
 * @since  		1.5 Moved into /inc
 * @package    	LiteSpeed_Cache
 * @subpackage 	LiteSpeed_Cache/inc
 * @author     	LiteSpeed Technologies <info@litespeedtech.com>
 */

if ( ! defined( 'WPINC' ) ) {
	die ;
}

class LiteSpeed_Cache_GUI
{
	private static $_instance ;

	private static $_clean_counter = 0 ;

	const TYPE_DISMISS_WHM = 'whm' ;
	const TYPE_DISMISS_EXPIRESDEFAULT = 'ExpiresDefault' ;
	const TYPE_DISMISS_PROMO = 'promo' ;

	/**
	 * Init
	 *
	 * @since  1.3
	 * @access private
	 */
	private function __construct()
	{
		if ( ! is_admin() ) {
			LiteSpeed_Cache_Log::debug( 'GUI init' ) ;
			if ( is_admin_bar_showing() && current_user_can( 'manage_options' ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_style' ) ) ;
				add_action( 'admin_bar_menu', array( $this, 'frontend_shortcut' ), 95 ) ;
			}

			/**
			 * Turn on instant click
			 * @since  1.8.2
			 */
			if ( LiteSpeed_Cache::config( LiteSpeed_Cache_Config::OPID_ADV_INSTANT_CLICK ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_style_public' ) ) ;
			}
		}

		// if ( LiteSpeed_Cache::config( LiteSpeed_Cache_Config::OPID_ADV_FAVICON ) ) {
		// 	if ( is_admin() ) {
		// 		add_action( 'admin_head', array( $this, 'favicon' ) ) ;
		// 	}
		// 	else {
		// 		add_action( 'wp_head', array( $this, 'favicon' ) ) ;
		// 	}
		// }
	}

	/**
	 * Display the favicon
	 *
	 * @since 1.7.2
	 */
	// public function favicon()
	// {
	// 	$current_favicon = get_option( LiteSpeed_Cache_Config::ITEM_FAVICON, array() ) ;
	// 	if ( is_admin() ) {
	// 		if ( ! empty( $current_favicon[ 'backend' ] ) ) {
	// 			echo "<link rel='icon' href='$current_favicon[backend]' />" ;
	// 		}
	// 	}
	// 	else {
	// 		if ( ! empty( $current_favicon[ 'frontend' ] ) ) {
	// 			echo "<link rel='icon' href='$current_favicon[frontend]' />" ;
	// 		}
	// 	}
	// }

	/**
	 * Display a pie
	 *
	 * @since 1.6.6
	 */
	public static function pie( $percent, $width = 50, $finished_tick = false )
	{
		$percentage = '<text x="16.91549431" y="15.5">' . $percent . '%</text>' ;
		if ( $percent == 100 && $finished_tick ) {
			$percentage = '<text x="16.91549431" y="15.5" class="litespeed-pie-done">&#x2713</text>' ;
		}
		return "
		<svg class='litespeed-pie' viewbox='0 0 33.83098862 33.83098862' width='$width' height='$width' xmlns='http://www.w3.org/2000/svg'>
			<circle class='litespeed-pie_bg' />
			<circle class='litespeed-pie_circle' stroke-dasharray='$percent,100' />
			<g class='litespeed-pie_info'>$percentage</g>
		</svg>
		";

	}

	/**
	 * Dismiss banner
	 *
	 * @since 1.0
	 * @access public
	 */
	public static function dismiss()
	{
		switch ( LiteSpeed_Cache_Router::verify_type() ) {
			case self::TYPE_DISMISS_WHM :
				LiteSpeed_Cache_Activation::dismiss_whm() ;
				break ;

			case self::TYPE_DISMISS_EXPIRESDEFAULT :
				update_option( LiteSpeed_Cache_Admin_Display::DISMISS_MSG, LiteSpeed_Cache_Admin_Display::RULECONFLICT_DISMISSED ) ;
				break ;

			case self::TYPE_DISMISS_PROMO :

				if ( ! empty( $_GET[ 'slack' ] ) ) {
					// Update slack
					update_option( 'litespeed-banner-promo-slack', 'done' ) ;

					defined( 'LSCWP_LOG' ) && LiteSpeed_Cache_Log::debug( '[GUI] Dismiss promo slack' ) ;
				}
				else {
					// Update welcome banner
					update_option( 'litespeed-banner-promo', ! empty( $_GET[ 'done' ] ) ? 'done' : time() ) ;

					defined( 'LSCWP_LOG' ) && LiteSpeed_Cache_Log::debug( '[GUI] Dismiss promo welcome' ) ;
				}

				break ;

			default:
				break ;
		}

		// All dismiss actions are considered as ajax call, so just exit
		exit( json_encode( array( 'success' => 1 ) ) ) ;
	}

	/**
	 * Check if has rule conflict notice
	 *
	 * @since 1.1.5
	 * @access public
	 * @return boolean
	 */
	public static function has_msg_ruleconflict()
	{
		return get_option( LiteSpeed_Cache_Admin_Display::DISMISS_MSG ) == LiteSpeed_Cache_Admin_Display::RULECONFLICT_ON ;
	}

	/**
	 * Check if has whm notice
	 *
	 * @since 1.1.1
	 * @access public
	 * @return boolean
	 */
	public static function has_whm_msg()
	{
		return get_transient( LiteSpeed_Cache::WHM_TRANSIENT ) == LiteSpeed_Cache::WHM_TRANSIENT_VAL ;
	}

	/**
	 * Display promo banner
	 *
	 * @since 2.1
	 * @access public
	 */
	public static function show_promo()
	{
		include_once LSCWP_DIR . "admin/tpl/inc/banner_promo.php" ;
		include_once LSCWP_DIR . "admin/tpl/inc/banner_promo.slack.php" ;

		include_once LSCWP_DIR . "admin/tpl/inc/disabled_all.php" ;
	}

	/**
	 * Detect if need to display promo banner or not
	 *
	 * @since 2.1
	 * @access public
	 */
	public static function should_show_promo( $banner = false )
	{
		// Only show one promo at one time
		if ( defined( 'LITESPEED_PROMO_SHOWN' ) ) {
			return false ;
		}

		if ( ! self::has_promo_msg( $banner ) ) {
			return false ;
		}

		defined( 'LSCWP_LOG' ) && LiteSpeed_Cache_Log::debug( '[GUI] Show promo ' . $banner ) ;

		! defined( 'LITESPEED_PROMO_SHOWN' ) && define( 'LITESPEED_PROMO_SHOWN', true ) ;

		return true ;
	}

	/**
	 * Check if has promotion notice
	 *
	 * @since 1.3.2
	 * @access public
	 * @return boolean
	 */
	public static function has_promo_msg( $banner = false )
	{
		// How many days delayed to show the banner
		$delay_days = 2 ;
		if ( $banner == 'slack' ) {
			$delay_days = 3 ;
		}

		$option_name = 'litespeed-banner-promo' ;
		if ( $banner ) {
			$option_name .= '-' . $banner ;
		}

		$promo = get_option( $option_name ) ;
		if ( ! $promo ) {
			update_option( $option_name, time() - 86400 * ( 10 - $delay_days ) ) ;
			return false ;
		}
		if ( $promo == 'done' ) {
			return false ;
		}
		if ( $promo && time() - $promo < 864000 ) {
			return false ;
		}

		return true ;
	}

	/**
	 * Load frontend public script
	 *
	 * @since  1.8.2
	 * @access public
	 */
	public function frontend_enqueue_style_public()
	{
		wp_enqueue_script( LiteSpeed_Cache::PLUGIN_NAME, LSWCP_PLUGIN_URL . 'js/instant_click.min.js', array(), LiteSpeed_Cache::PLUGIN_VERSION, true ) ;
	}

	/**
	 * Load frontend menu shortcut
	 *
	 * @since  1.3
	 * @access public
	 */
	public function frontend_enqueue_style()
	{
		wp_enqueue_style( LiteSpeed_Cache::PLUGIN_NAME, LSWCP_PLUGIN_URL . 'css/litespeed.css', array(), LiteSpeed_Cache::PLUGIN_VERSION, 'all' ) ;
	}

	/**
	 * Load frontend menu shortcut
	 *
	 * @since  1.3
	 * @access public
	 */
	public function frontend_shortcut()
	{
		global $wp_admin_bar ;

		$wp_admin_bar->add_menu( array(
			'id'	=> 'litespeed-menu',
			'title'	=> '<span class="ab-icon"></span>',
			'href'	=> get_admin_url( null, 'admin.php?page=lscache-settings' ),
			'meta'	=> array( 'tabindex' => 0, 'class' => 'litespeed-top-toolbar' ),
		) ) ;

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-purge-single',
			'title'		=> __( 'Purge this page', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_FRONT, false, true ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-single-action',
			'title'		=> __( 'Mark this page as ', 'litespeed-cache' ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-single-action',
			'id'		=> 'litespeed-single-forced_cache',
			'title'		=> __( 'Forced cacheable', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_FRONT_EXCLUDE, 'forced_cache', false, true ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-single-action',
			'id'		=> 'litespeed-single-noncache',
			'title'		=> __( 'Non cacheable', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_FRONT_EXCLUDE, 'nocache', false, true ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-single-action',
			'id'		=> 'litespeed-single-private',
			'title'		=> __( 'Private cache', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_FRONT_EXCLUDE, 'private', false, true ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-single-action',
			'id'		=> 'litespeed-single-nonoptimize',
			'title'		=> __( 'No optimization', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_FRONT_EXCLUDE, 'nonoptimize', false, true ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-single-action',
			'id'		=> 'litespeed-single-more',
			'title'		=> __( 'More settings', 'litespeed-cache' ),
			'href'		=> get_admin_url( null, 'admin.php?page=lscache-settings#excludes' ),
		) );
	}

	/**
	 * Hooked to wp_before_admin_bar_render.
	 * Adds a link to the admin bar so users can quickly purge all.
	 *
	 * @access public
	 * @global WP_Admin_Bar $wp_admin_bar
	 * @since 1.7.2 Moved from admin_display.cls to gui.cls; Renamed from `add_quick_purge` to `backend_shortcut`
	 */
	public function backend_shortcut()
	{
		global $wp_admin_bar ;

		if ( defined( 'LITESPEED_ON' ) ) {
			$wp_admin_bar->add_menu( array(
				'id'    => 'litespeed-menu',
				'title' => '<span class="ab-icon" title="' . __( 'LiteSpeed Cache Purge All', 'litespeed-cache' ) . ' - ' . __( 'LSCache', 'litespeed-cache' ) . '"></span>',
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_LSCACHE ),
				'meta'  => array( 'tabindex' => 0, 'class' => 'litespeed-top-toolbar' ),
			) ) ;
		}
		else {
			$wp_admin_bar->add_menu( array(
				'id'    => 'litespeed-menu',
				'title' => '<span class="ab-icon" title="' . __( 'LiteSpeed Cache', 'litespeed-cache' ) . '"></span>',
				'meta'  => array( 'tabindex' => 0, 'class' => 'litespeed-top-toolbar' ),
			) ) ;
		}

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-bar-manage',
			'title'		=> __( 'Manage', 'litespeed-cache' ),
			'href'		=> 'admin.php?page=lscache-dash',
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-bar-setting',
			'title'		=> __( 'Settings', 'litespeed-cache' ),
			'href'		=> 'admin.php?page=lscache-settings',
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		if ( ! is_network_admin() ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-bar-imgoptm',
				'title'		=> __( 'Image Optimization', 'litespeed-cache' ),
				'href'		=> 'admin.php?page=lscache-optimization',
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-purge-all',
			'title'		=> __( 'Purge All', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-lscache-purge-all',
			'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'LSCache', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_LSCACHE ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		$wp_admin_bar->add_menu( array(
			'parent'	=> 'litespeed-menu',
			'id'		=> 'litespeed-purge-cssjs',
			'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'CSS/JS Cache', 'litespeed-cache' ),
			'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_CSSJS ),
			'meta'		=> array( 'tabindex' => '0' ),
		) );

		if ( LiteSpeed_Cache::config( LiteSpeed_Cache_Config::OPID_CDN_CLOUDFLARE ) ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-purge-cloudflare',
				'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'Cloudflare', 'litespeed-cache' ),
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_CDN_CLOUDFLARE, LiteSpeed_Cache_CDN_Cloudflare::TYPE_PURGE_ALL ),
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}

		if ( defined( 'LSCWP_OBJECT_CACHE' ) ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-purge-object',
				'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'Object Cache', 'litespeed-cache' ),
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_OBJECT ),
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}

		if ( LiteSpeed_Cache_Router::opcache_enabled() ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-purge-opcache',
				'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'Opcode Cache', 'litespeed-cache' ),
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_OPCACHE ),
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}

		if ( LiteSpeed_Cache_CSS::has_ccss_cache() ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-purge-ccss',
				'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'Critical CSS', 'litespeed-cache' ),
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_CCSS ),
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}

		if ( LiteSpeed_Cache_Media::has_placehoder_cache() ) {
			$wp_admin_bar->add_menu( array(
				'parent'	=> 'litespeed-menu',
				'id'		=> 'litespeed-purge-placeholder',
				'title'		=> __( 'Purge All', 'litespeed-cache' ) . ' - ' . __( 'Placeholder Cache', 'litespeed-cache' ),
				'href'		=> LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_PURGE, LiteSpeed_Cache_Purge::TYPE_PURGE_ALL_PLACEHOLDER ),
				'meta'		=> array( 'tabindex' => '0' ),
			) );
		}
	}

	/**
	 * Clear unfinished data
	 *
	 * @since  2.4.2
	 * @access public
	 */
	public static function img_optm_clean_up_unfinished()
	{
		return sprintf(
			'<a href="%1$s" class="litespeed-btn-warning" title="%2$s"><span class="dashicons dashicons-editor-removeformatting"></span>&nbsp;%3$s</a>',
			LiteSpeed_Cache_Utility::build_url( LiteSpeed_Cache::ACTION_IMG_OPTM, LiteSpeed_Cache_Img_Optm::TYPE_IMG_OPTM_DESTROY_UNFINISHED ),
			__( 'Remove all previous unfinished image optimization requests.', 'litespeed-cache' ),
			__( 'Clean Up Unfinished Data', 'litespeed-cache' )
		) ;
	}

	/**
	 * Generate install link
	 *
	 * @since  2.4.2
	 * @access public
	 */
	public static function plugin_install_link( $title, $name, $v )
	{
		$url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $name ), 'install-plugin_' . $name ) ;

		$action = sprintf(
			'<a href="%1$s" class="install-now" data-slug="%2$s" data-name="%3$s" aria-label="%4$s">%5$s</a>',
			esc_url( $url ),
			esc_attr( $name ),
			esc_attr( $title ),
			esc_attr( sprintf( __( 'Install %s' ), $title ) ),
			__( 'Install Now' )
		);

		return $action ;

		// $msg .= " <a href='$upgrade_link' class='litespeed-btn-success' target='_blank'>" . __( 'Click here to upgrade', 'litespeed-cache' ) . '</a>' ;

	}

	/**
	 * Generate upgrade link
	 *
	 * @since  2.4.2
	 * @access public
	 */
	public static function plugin_upgrade_link( $title, $name, $v )
	{
		$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $name . '&section=changelog&TB_iframe=true&width=600&height=800' );
		$file = $name . '/' . $name . '.php' ;

		$msg = sprintf( __( '<a href="%1$s" %2$s>View version %3$s details</a> or <a href="%4$s" %5$s target="_blank">update now</a>.' ),
			esc_url( $details_url ),
			sprintf( 'class="thickbox open-plugin-details-modal" aria-label="%s"',
				esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $title, $v ) )
			),
			$v,
			wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ),
			sprintf( 'class="update-link" aria-label="%s"',
				esc_attr( sprintf( __( 'Update %s now' ), $title ) )
			)
		);

		return $msg ;
	}

	/**
	 * Finalize buffer by GUI class
	 *
	 * @since  1.6
	 * @access public
	 */
	public static function finalize( $buffer )
	{
		$instance = self::get_instance() ;
		return $instance->_clean_wrapper( $buffer ) ;
	}

	/**
	 * Clean wrapper from buffer
	 *
	 * @since  1.4
	 * @since  1.6 converted to private with adding prefix _
	 * @access private
	 */
	private function _clean_wrapper( $buffer )
	{
		if ( self::$_clean_counter < 1 ) {
			LiteSpeed_Cache_Log::debug2( "GUI bypassed by no counter" ) ;
			return $buffer ;
		}

		LiteSpeed_Cache_Log::debug2( "GUI start cleaning counter " . self::$_clean_counter ) ;

		for ( $i = 1 ; $i <= self::$_clean_counter ; $i ++ ) {
			// If miss beginning
			$start = strpos( $buffer, self::clean_wrapper_begin( $i ) ) ;
			if ( $start === false ) {
				$buffer = str_replace( self::clean_wrapper_end( $i ), '', $buffer ) ;
				LiteSpeed_Cache_Log::debug2( "GUI lost beginning wrapper $i" ) ;
				continue;
			}

			// If miss end
			$end_wrapper = self::clean_wrapper_end( $i ) ;
			$end = strpos( $buffer, $end_wrapper ) ;
			if ( $end === false ) {
				$buffer = str_replace( self::clean_wrapper_begin( $i ), '', $buffer ) ;
				LiteSpeed_Cache_Log::debug2( "GUI lost ending wrapper $i" ) ;
				continue;
			}

			// Now replace wrapped content
			$buffer = substr_replace( $buffer, '', $start, $end - $start + strlen( $end_wrapper ) ) ;
			LiteSpeed_Cache_Log::debug2( "GUI cleaned wrapper $i" ) ;
		}

		return $buffer ;
	}

	/**
	 * Display a to-be-removed html wrapper
	 *
	 * @since  1.4
	 * @access public
	 */
	public static function clean_wrapper_begin( $counter = false )
	{
		if ( $counter === false ) {
			self::$_clean_counter ++ ;
			$counter = self::$_clean_counter ;
			LiteSpeed_Cache_Log::debug( "GUI clean wrapper $counter begin" ) ;
		}
		return '<!-- LiteSpeed To Be Removed begin ' . $counter . ' -->' ;
	}

	/**
	 * Display a to-be-removed html wrapper
	 *
	 * @since  1.4
	 * @access public
	 */
	public static function clean_wrapper_end( $counter = false )
	{
		if ( $counter === false ) {
			$counter = self::$_clean_counter ;
			LiteSpeed_Cache_Log::debug( "GUI clean wrapper $counter end" ) ;
		}
		return '<!-- LiteSpeed To Be Removed end ' . $counter . ' -->' ;
	}

	/**
	 * Get the current instance object.
	 *
	 * @since 1.3
	 * @access public
	 * @return Current class instance.
	 */
	public static function get_instance()
	{
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self() ;
		}

		return self::$_instance ;
	}

}


