<?php
/**
 * Plugin admin settings page.
 *
 * @package    Genesis Layout Extras
 * @subpackage Admin
 * @author     David Decker - DECKERWEB
 * @copyright  Copyright (c) 2011-2013, David Decker - DECKERWEB
 * @license    http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link       http://genesisthemes.de/en/wp-plugins/genesis-layout-extras/
 * @link       http://deckerweb.de/twitter
 *
 * @since      1.0.0
 */

/**
 * Prevent direct access to this file.
 *
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Sorry, you are not allowed to access this file directly.' );
}


/**
 * Registers a new admin page, providing content and corresponding menu item
 * for the plugin's admin settings page.
 *
 * @since 2.0.0
 */
class DDW_GLE_Plugin_Settings extends Genesis_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 2.0.0
	 */
	function __construct() {

		/** Set a unique settings page ID */
		$page_id = 'gle-layout-extras';

		/** Set it as a submenu to 'genesis', and define the menu and page titles */
		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => _x( 'Genesis Layout Extras', 'Translators: Genesis settings page title', 'genesis-layout-extras' ),
				'menu_title'  => _x( 'Layout Extras', 'Translators: Genesis menu title', 'genesis-layout-extras' ),
				'capability'  => 'edit_theme_options',
			)
		);

		$gle_error_notice = sprintf(
			__( 'Error saving settings for %s.', 'genesis-layout-extras' ),
			__( 'Genesis Layout Extras', 'genesis-layout-extras' )
		);

		$gle_error_notice = 
		/** Set up page options */
		$page_ops = array(
			'screen_icon'       => 'themes',
			'save_button_text'  => __( 'Save Settings', 'genesis-layout-extras' ),
			'reset_button_text' => __( 'Reset Settings', 'genesis-layout-extras' ),
			'saved_notice_text' => __( 'The extra layout settings have been saved successfully.', 'genesis-layout-extras' ),
			'reset_notice_text' => __( 'ALL extra layout settings were reset to their Genesis default option.', 'genesis-layout-extras' ) . ' ' . __( 'All other settings were reset to their appropriate plugin default setting.', 'genesis-layout-extras' ),
			'error_notice_text' => sprintf( __( 'Error saving settings for %s.', 'genesis-layout-extras' ),
			__( 'Genesis Layout Extras', 'genesis-layout-extras' ) ),
		);		

		/** Unique settings field */
		$settings_field = GLE_SETTINGS_FIELD;

		/** Set the default values */
		$default_settings = array(
			'gle_layout_sbc'                                  => 1,		// special
			'gle_layout_pbc'                                  => 1,		// special
			'gle_layout_pac'                                  => 0,		// special
			'gle_layout_hncs'                                 => 0,		// special
			'gle_layout_c_salt'                               => 0,		// 2-column
			'gle_layout_salt_c'                               => 0,		// 2-column
			'gle_layout_c_salt_s'                             => 0,		// 3-column
			'gle_layout_s_salt_c'                             => 0,		// 3-column
			'gle_layout_s_c_salt'                             => 0,		// 3-column
			'gle_layout_styles_sbc'                           => 0,
			'gle_layout_styles_prims'                         => 0,
			'gle_layout_styles_hncs'                          => 0,
			'gle_layout_styles_2col'                          => 0,
			'gle_layout_styles_3col'                          => 0,
			'gle_cpt_inpost_support_global'                   => 0,
			'gle_cpt_inpost_support_custom'                   => '',
			'gle_cpt_archives_support_global'                 => 0,
			'gle_cpt_archives_support_custom'                 => '',
			'ddw_genesis_layout_home'                         => '',
			'ddw_genesis_layout_search'                       => '',
			'ddw_genesis_layout_search_not_found'             => '',
			'ddw_genesis_layout_404'                          => '',
			'ddw_genesis_layout_post'                         => '',
			'ddw_genesis_layout_page'                         => '',
			'ddw_genesis_layout_attachment'                   => '',
			'ddw_genesis_layout_author'                       => '',
			'ddw_genesis_layout_date'                         => '',
			'ddw_genesis_layout_date_year'                    => '',
			'ddw_genesis_layout_date_month'                   => '',
			'ddw_genesis_layout_date_day'                     => '',
			'ddw_genesis_layout_category'                     => '',
			'ddw_genesis_layout_tag'                          => '',
			'ddw_genesis_layout_taxonomy'                     => '',
			'ddw_genesis_layout_cpt_apl_listing'              => '',
			'ddw_genesis_layout_cpt_apl_features'             => '',
			'ddw_genesis_layout_cpt_gmp_video'                => '',
			'ddw_genesis_layout_cpt_gmp_video_slideshow'      => '',
			'ddw_genesis_layout_cpt_gmp_video_category'       => '',
			'ddw_genesis_layout_cpt_gmp_video_tag'            => '',
			'ddw_genesis_layout_cpt_wcjs_product_cat'         => '',
			'ddw_genesis_layout_cpt_wcjs_product_tag'         => '',
			'ddw_genesis_layout_cpt_edd_download'             => '',
			'ddw_genesis_layout_cpt_edd_download_category'    => '',
			'ddw_genesis_layout_cpt_edd_download_tag'         => '',
			'ddw_genesis_layout_cpt_sc_event'                 => '',
			'ddw_genesis_layout_cpt_sc_event_category'        => '',
			'ddw_genesis_layout_bbpress'                      => '',
			'ddw_genesis_layout_bbpress_topics'               => '',
			'ddw_genesis_layout_cpt_child_portfolio'          => '',
			'ddw_genesis_layout_cpt_child_portfolio_category' => '',
			'ddw_genesis_layout_cpt_themedy_products'         => '',
			'ddw_genesis_layout_cpt_themedy_product_category' => '',
			'ddw_genesis_layout_cpt_themedy_photo_gallery'    => '',
		);

		/** Create the Admin Page */
		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		/** Initialize the Sanitization Filter */
		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitization_filters' ) );

	}  // end of method __construct


	/** 
	 * Set up Sanitization Filters.
	 *
	 * @since 2.0.0
	 *
	 * @uses  genesis_add_option_filter()
	 */	
	function sanitization_filters() {

		genesis_add_option_filter(
			'one_zero',
			$this->settings_field,
			array( 
				'gle_layout_sbc',
				'gle_layout_pbc',
				'gle_layout_pac',
				'gle_layout_hncs',
				'gle_layout_c_salt',
				'gle_layout_salt_c',
				'gle_layout_c_salt_s',
				'gle_layout_s_salt_c',
				'gle_layout_s_c_salt',
				'gle_layout_styles_sbc',
				'gle_layout_styles_prims',
				'gle_layout_styles_hncs',
				'gle_layout_styles_2col',
				'gle_layout_styles_3col',
				'gle_cpt_inpost_support_global',
				'gle_cpt_archives_support_global',
			)
		);

		genesis_add_option_filter(
			'no_html',
			$this->settings_field,
			array(
				'gle_cpt_inpost_support_custom',
				'gle_cpt_archives_support_custom',
				'ddw_genesis_layout_home',
				'ddw_genesis_layout_search',
				'ddw_genesis_layout_search_not_found',
				'ddw_genesis_layout_404',
				'ddw_genesis_layout_post',
				'ddw_genesis_layout_page',
				'ddw_genesis_layout_attachment',
				'ddw_genesis_layout_author',
				'ddw_genesis_layout_date',
				'ddw_genesis_layout_date_year',
				'ddw_genesis_layout_date_month',
				'ddw_genesis_layout_date_day',
				'ddw_genesis_layout_category',
				'ddw_genesis_layout_tag',
				'ddw_genesis_layout_taxonomy',
				'ddw_genesis_layout_cpt_apl_listing',
				'ddw_genesis_layout_cpt_apl_features',
				'ddw_genesis_layout_cpt_gmp_video',
				'ddw_genesis_layout_cpt_gmp_slideshow',
				'ddw_genesis_layout_cpt_gmp_video_category',
				'ddw_genesis_layout_cpt_gmp_video_tag',
				'ddw_genesis_layout_cpt_wcjs_product_cat',
				'ddw_genesis_layout_cpt_wcjs_product_tag',
				'ddw_genesis_layout_cpt_edd_download',
				'ddw_genesis_layout_cpt_edd_download_category',
				'ddw_genesis_layout_cpt_edd_download_tag',
				'ddw_genesis_layout_cpt_sc_event',
				'ddw_genesis_layout_cpt_sc_event_category',
				'ddw_genesis_layout_bbpress',
				'ddw_genesis_layout_bbpress_topics',
				'ddw_genesis_layout_cpt_child_portfolio',
				'ddw_genesis_layout_cpt_child_portfolio_category',
				'ddw_genesis_layout_cpt_themedy_products',
				'ddw_genesis_layout_cpt_themedy_product_category',
				'ddw_genesis_layout_cpt_themedy_photo_gallery',
			)
		);

	}  // end of method sanitization_filters


	/**
	 * Register metaboxes on the plugin's settings page.
	 *
	 * @since 2.0.0
	 *
	 * @uses  add_meta_box()
	 * @uses  is_multisite()
	 * @uses  is_super_admin()
	 * @uses  current_user_can()
	 * @uses  ddw_gle_core_sidebars_exists()
	 * @uses  ddw_gle_check_cpts()
	 * @uses  ddw_gle_supported_plugins()
	 * @uses  ddw_gle_supported_child_themes()
	 */
	function metaboxes() {

		/** Plugin Information Meta Box */
		add_meta_box(
			'gle-plugin-information',
			_x( 'Information', 'Translators: meta box title', 'genesis-layout-extras' ) . ' / ' . __( 'Table of Content', 'genesis-layout-extras' ),
			array( $this, 'gle_plugin_information_metabox' ),
			$this->pagehook,
			'main',
			'high'
		);

		/** Layouts Meta Box */
		if ( ddw_gle_core_sidebars_exists( 'sidebar' ) ) {

			add_meta_box(
				'gle-layouts-metabox',
				_x( 'Additional Layouts', 'Translators: meta box title', 'genesis-layout-extras' ),
				array( $this, 'gle_layouts_metabox' ),
				$this->pagehook,
				'main',
				'high'
			);

		}  // end if

		/** Additional Post Type Support Meta Box */
		if ( ddw_gle_check_cpts() ) {

			add_meta_box(
				'gle-post-type-support-metabox',
				_x( 'Additional Post Type Support', 'Translators: meta box title', 'genesis-layout-extras' ),
				array( $this, 'gle_post_type_support_metabox' ),
				$this->pagehook,
				'main',
				'high'
			);

		}  // end if

		/** Layouts - for WordPress Defaults */
		add_meta_box(
			'genesis-layout-extras-box',
			_x( 'Layouts for WordPress Default Cases', 'Translators: meta box title', 'genesis-layout-extras' ),
			array( $this, 'gle_layouts_wordpress_defaults_metabox' ),
			$this->pagehook,
			'main',
			'high'
		);

		/** Layouts - CPTs by Plugins */
		if ( ddw_gle_supported_plugins() ) {

			/** Add the meta box */
			add_meta_box(
				'genesis-layout-extras-box-cpts',
				_x( 'Custom Post Types by Plugins', 'Translators: meta box title', 'genesis-layout-extras' ),
				'ddw_genesis_layout_extras_box_cpts',
				$this->pagehook,
				'main',
				'high'
			);

			/** Include plugin code part */
			require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-options-plugins.php' );

		}  // end if CPT checks

		/** Layouts - CPTs by Child Themes */
		$gle_theme_check = 'default';

		if ( ddw_gle_supported_child_themes() ) {

			/** For StudioPress */
			if ( function_exists( 'minimum_portfolio_post_type' ) || function_exists( 'executive_portfolio_post_type' ) ) {

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-options-studiopress.php' );

				$gle_theme_check = 'ddw_genesis_layout_extras_box_studiopress';

			}
			
			/** For Themedy brand */
			elseif ( ddw_gle_supported_themedy() ) {

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-options-themedy.php' );

				$gle_theme_check = 'ddw_genesis_layout_extras_box_themedy';

			}
			
			/** For ZigZagPress brand zigzagpress_portfolio_layout */
			elseif ( function_exists( 'zp_footer_menu' )
					|| function_exists( 'project_showcase' )
					|| function_exists( 'zp_socialicon_load_widget' )
					|| function_exists( 'zp_home_slider' )
			) {

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-options-zigzagpress.php' );

				$gle_theme_check = 'ddw_genesis_layout_extras_box_zigzagpress';

			}  // end if theme/brand check

			/** Add the meta box */
			add_meta_box(
				'genesis-layout-extras-box-childthemes',
				_x( 'Custom Post Types by Child Themes', 'Translators: meta box title', 'genesis-layout-extras' ),
				$gle_theme_check,
				$this->pagehook,
				'main',
				'high'
			);

		}  // end if CPT checks

		/** Settings Export/ Import Meta Box - only display for Administrators! */
		if ( current_user_can( 'administrator' ) && ! GLE_NO_EXPORT_IMPORT_INFO ) {

			add_meta_box(
				'gle-export-import',
				__( 'Export/ Import Info', 'genesis-layout-extras' ),
				array( $this, 'gle_export_import_metabox' ),
				$this->pagehook,
				'main',
				'high'
			);

		}  // end if cap check

		/** Action hook 'gle_settings_metaboxes' */
		do_action( 'gle_settings_metaboxes', $this->pagehook );

	}  // end of method metaboxes


	/**
	 * Plugin Information Metabox.
	 *
	 * @since 2.0.0
	 */
	function gle_plugin_information_metabox() {

		/** Begin form code unix: strtotime( '1375645023' ) */
		?>

		<p>
			<strong><?php _e( 'Genesis Layout Extras', 'genesis-layout-extras' ); ?></strong> <?php _e( 'by', 'genesis-layout-extras' ); ?> <a href="' . esc_url( GLE_URL_PLUGIN ) . '" target="_new" title="David Decker - DECKERWEB &amp; wpAUTOBAHN.com">David Decker - DECKERWEB &amp; wpAUTOBAHN.com</a>
		</p>

		<p>
			<strong><?php _e( 'Version:', 'genesis-layout-extras' ); ?></strong> <?php echo esc_attr( ddw_gle_plugin_get_data( 'Version' ) ); ?> <?php echo '&middot;'; ?> <strong><?php _e( 'Released:', 'genesis-layout-extras' ); ?></strong> <?php echo date_i18n( _x( 'F j, Y', 'Translators: plugin release date format', 'genesis-layout-extras' ), '1375693200' ); ?>
		</p>

		<p>
			<span class="description"><?php echo sprintf( __( 'Support for this plugin is provided via %sour support forum%s.', 'genesis-layout-extras' ), '<a href="' . esc_url( GLE_URL_FORUM ) . '">', '</a>' ); echo ' &mdash; '; ?><a href="<?php echo esc_url( GLE_URL_TRANSLATE ); ?>" target="_new" title="<?php _e( 'Free Translations Platform', 'genesis-layout-extras' ); ?>"><?php _e( 'Free Translations Platform', 'genesis-layout-extras' ); ?></a>
			<br /><br /><?php echo sprintf( __( 'General support for the Genesis Framework you can get at %s.', 'genesis-layout-extras' ), '<a href="http://deckerweb.de/go/studiopress-support/">My.StudioPress.com</a>' ); ?></span>
		</p>

		<hr class="div" />

		<h4>&rarr; <?php _e( 'Table of Content', 'genesis-layout-extras' ); ?>:</h4>

		<ul>
			<?php if ( ddw_gle_core_sidebars_exists( 'sidebar' ) ) : ?>
				<li><a href="#gle-layouts-metabox"><?php _e( 'Additional Layouts', 'genesis-layout-extras' ); ?>&hellip;</a></li>
			<?php endif; ?>

			<?php if ( ddw_gle_check_cpts() ) : ?>
				<li><a href="#gle-post-type-support-metabox"><?php _e( 'Additional Post Type Support', 'genesis-layout-extras' ); ?>&hellip;</a></li>
			<?php endif; ?>

			<li><a href="#genesis-layout-extras-box"><?php _e( 'Layouts for WordPress Default Cases', 'genesis-layout-extras' ); ?>&hellip;</a></li>

			<?php if ( ddw_gle_supported_plugins() ) : ?>
				<li><a href="#genesis-layout-extras-box-cpts"><?php _e( 'Custom Post Types by Plugins', 'genesis-layout-extras' ); ?>&hellip;</a></li>
			<?php endif; ?>

			<?php if ( ddw_gle_supported_child_themes() ) : ?>
				<li><a href="#genesis-layout-extras-box-childthemes"><?php _e( 'Custom Post Types by Child Themes', 'genesis-layout-extras' ); ?>&hellip;</a></li>
			<?php endif; ?>

			<?php if ( current_user_can( 'administrator' ) && ! GLE_NO_EXPORT_IMPORT_INFO ) : ?>
				<li><a href="#gle-export-import"><?php _e( 'Export/ Import Info', 'genesis-layout-extras' ); ?>&hellip;</a></li>
			<?php endif; ?>
		</ul>

		<?php if ( ! ddw_gle_core_sidebars_exists( 'sidebar' ) ) : 	// additional user info ?>
			<p>
				<blockquote><span class="description"><small><?php _e( 'Note: Additional layout options only become available here if the Primary Sidebar may not have been unregistered.', 'genesis-layout-extras' ); ?><?php if ( current_user_can( 'install_plugins' ) ) : ?> <?php _e( 'In such a case, just check your child theme, other plugins or other code snippets.', 'genesis-layout-extras' ); ?><?php endif; ?></small></span></blockquote>
			</p>
		<?php endif; ?>

		<?php
		/** ^End form code */

	}  // end of method gle_plugin_information_metabox


	/**
	 * Layouts Metabox.
	 *
	 * @since 2.0.0
	 */
	function gle_layouts_metabox() {

		/** Begin form code */
		?>

		<h4><?php _e( 'Additional Layout Options.', 'genesis-layout-extras' ); ?></h4>

			<fieldset>
				<legend>
					<?php echo __( 'Enable any additional layout option:', 'genesis-layout-extras' ); ?>
				</legend>

				<div class="gle-add-layouts">
					<?php if ( ddw_gle_core_sidebars_exists( 'sidebar-alt' ) && ! defined( 'GPEX_PLUGIN_BASEDIR' ) ) : ?>
						<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_sbc' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_sbc' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_sbc' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_sbc' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_sbc.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Sidebars below Content', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Sidebars below Content', 'genesis-layout-extras' ); ?></div>
						</label>
					<?php endif; // if sidebar-alt & Prose Extras check ?>

					<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_pbc' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_pbc' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_pbc' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_pbc' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_pbc.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Primary below Content', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Primary below Content', 'genesis-layout-extras' ); ?></div>
					</label>

					<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_pac' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_pac' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_pac' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_pac' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_pac.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Primary above Content', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Primary above Content', 'genesis-layout-extras' ); ?></div>
					</label>

					<?php if ( ! GLE_NO_HNCS_LAYOUT_OPTION ) : ?>
						<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_hncs' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_hncs' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_hncs' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_hncs' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_hncs.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Header+Nav/Content/Sidebar', 'genesis-layout-extras' ); ?> (&#x2217;)" /><br /><div class="gle-checkbox"><?php _e( 'Header+Nav/Content/Sidebar', 'genesis-layout-extras' ); ?> (&#x2217;)</div>
						</label>
					<?php endif; // if constant check ?>

					<?php if ( ddw_gle_core_sidebars_exists( 'sidebar-alt' ) ) : ?>
						<?php if ( ! defined( 'GPEX_PLUGIN_BASEDIR' ) ) : ?>
							<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_c_salt' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_c_salt' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_c_salt' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_c_salt' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_c-salt.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Content/Sidebar-Alt', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Content/Sidebar-Alt', 'genesis-layout-extras' ); ?></div>
							</label>

							<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_salt_c' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_salt_c' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_salt_c' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_salt_c' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_salt-c.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Sidebar-Alt/Content', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Sidebar-Alt/Content', 'genesis-layout-extras' ); ?></div>
							</label>
						<?php endif; // if Prose Extras check ?>

						<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_c_salt_s' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_c_salt_s' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_c_salt_s' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_c_salt_s' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_c-salt-s.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Content/Sidebar-Alt/Sidebar', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Content/Sidebar-Alt/Sidebar', 'genesis-layout-extras' ); ?></div>
						</label>

						<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_s_salt_c' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_s_salt_c' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_s_salt_c' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_s_salt_c' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_s-salt-c.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Sidebar/Sidebar-Alt/Content', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Sidebar/Sidebar-Alt/Content', 'genesis-layout-extras' ); ?></div>
						</label>

						<label class="box" for="<?php echo $this->get_field_id( 'gle_layout_s_c_salt' ); ?>"><input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_s_c_salt' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_s_c_salt' ); ?>" value="1" <?php checked( $this->get_field_value( 'gle_layout_s_c_salt' ) ); ?> /><img src="<?php echo plugins_url( 'images/gle_s-c-salt.gif', dirname( __FILE__ ) ); ?>" title="<?php esc_html_e( 'Sidebar/Content/Sidebar-Alt', 'genesis-layout-extras' ); ?>" /><br /><div class="gle-checkbox"><?php _e( 'Sidebar/Content/Sidebar-Alt', 'genesis-layout-extras' ); ?></div>
						</label>
					<?php endif; // if sidebar check ?>
				</div>
				<br class="clear" />
			</fieldset>

			<p class="description">
				<?php _e( 'Note: Only the two Genesis default sidebars are used!', 'genesis-layout-extras' ); ?>
				<br />&#x2217;&#x232A; <?php _e( 'EXPERIMENTAL, use not yet, or just with care...', 'genesis-layout-extras' ); ?>
			</p>

			<?php if ( defined( 'GPEX_PLUGIN_BASEDIR' ) ) : ?>
				<p class="description">
					<?php echo sprintf(
						__( 'Note, the plugin %s is active: enable the following layout options there, if needed: %s.', 'genesis-layout-extras' ),
						'<a href="' . admin_url( 'admin.php?page=gpex-prose-extras' ) . '">' . __( 'Genesis Prose Extras', 'genesis-layout-extras' ) . '</a>',
						'&raquo;' . __( 'Sidebars below Content', 'genesis-layout-extras' ) . '&laquo;, &raquo;' . __( 'Content/Sidebar-Alt', 'genesis-layout-extras' ) . '&laquo;, &raquo;' . __( 'Sidebar-Alt/Content', 'genesis-layout-extras' ) . '&laquo;'
					) . ' &mdash; ' . __( 'These three are optimized for the use with Prose child theme then.', 'genesis-layout-extras' ); ?>
				</p>
			<?php endif; // if Prose Extras check ?>

			<?php ddw_gle_save_button(); ?>

		<hr class="div" />

		<h4><?php _e( 'Optionally load necessary CSS styles for above layouts.', 'genesis-layout-extras' ); ?></h4>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_styles_sbc' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_styles_sbc' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_layout_styles_sbc' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_layout_styles_sbc' ); ?>">
					<?php echo sprintf( __( 'Load CSS styles for layout %s?', 'genesis-layout-extras' ), '<em>' . __( 'Sidebars below Content', 'genesis-layout-extras' ) . '</em>' ); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_styles_prims' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_styles_prims' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_layout_styles_prims' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_layout_styles_prims' ); ?>">
					<?php echo sprintf( __( 'Load CSS styles for the %s layouts?', 'genesis-layout-extras' ), '<em>' . __( 'two Primary sidebars', 'genesis-layout-extras' ) . '</em>' ); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_styles_2col' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_styles_2col' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_layout_styles_2col' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_layout_styles_2col' ); ?>">
					<?php echo sprintf( __( 'Load CSS styles for the %s layouts?', 'genesis-layout-extras' ), '<em>' . __( 'two 2-column', 'genesis-layout-extras' ) . '</em>' ); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_styles_3col' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_styles_3col' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_layout_styles_3col' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_layout_styles_3col' ); ?>">
					<?php echo sprintf( __( 'Load CSS styles for the %s layouts?', 'genesis-layout-extras' ), '<em>' . __( 'three 3-column', 'genesis-layout-extras' ) . '</em>' ); ?>
				</label>
			</p>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_layout_styles_hncs' ); ?>" id="<?php echo $this->get_field_id( 'gle_layout_styles_hncs' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_layout_styles_hncs' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_layout_styles_hncs' ); ?>">
					<?php echo sprintf( __( 'Load CSS styles for layout %s?', 'genesis-layout-extras' ), '<em>' . __( 'Header+Nav/Content/Sidebar', 'genesis-layout-extras' ) . '</em>' ); ?> <small>(<?php _e( 'Experimental!', 'genesis-layout-extras' ); ?>)</small>
				</label>
			</p>

			<p>
				<span class="description"><strong>&rarr;</strong> <?php _e( 'Good to know', 'genesis-layout-extras' ); ?>:
					<br /><?php _e( 'If you enable any of the above style sheets they only get loaded if the appropriate layout is used for a frontend item.', 'genesis-layout-extras' ); ?><?php if ( current_theme_supports( 'genesis-html5' ) ) : echo ' ' . __( 'The loading logic includes full HTML5 and Genesis 2.0+ detection!', 'genesis-layout-extras' ); endif; ?> <?php _e( 'And, if loaded they come in minimized flavor for improved performance. These styles are optional, so you can also add those (or in customized form) to your used child theme.', 'genesis-layout-extras' ); ?></span>
			</p>

			<?php ddw_gle_save_button(); ?>

		<?php
		/** ^End form code */

		/** Action Hook 'gle_layouts_metabox' */
	    do_action( 'gle_layouts_metabox' );

	}  // end of method gle_layouts_metabox


	/**
	 * Additional Post Type Support Metabox.
	 *
	 * @since 2.0.0
	 */
	function gle_post_type_support_metabox() {

		/** Begin form code */
		?>

		<h4><?php _e( 'Genesis Inpost Options for Post Types (Layouts, SEO, Scripts)', 'genesis-layout-extras' ); ?></h4>

			<p>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_cpt_inpost_support_global' ); ?>" id="<?php echo $this->get_field_id( 'gle_cpt_inpost_support_global' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_cpt_inpost_support_global' ) ); ?> />
				<label for="<?php echo $this->get_field_id( 'gle_cpt_inpost_support_global' ); ?>">
					<?php _e( 'Include Genesis Inpost options for ALL public Custom Post Types? (global)', 'genesis-layout-extras' ); ?>
				</label>
			</p>

			<p>
				<blockquote><em><?php _e( 'OR', 'genesis-layout-extras' ); ?>:</em></blockquote>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'gle_cpt_inpost_support_custom' ); ?>">
					<?php _e( 'Include Genesis Inpost options for your specific Custom Post Types?', 'genesis-layout-extras' ); ?>
					<br /><input type="text" name="<?php echo $this->get_field_name( 'gle_cpt_inpost_support_custom' ); ?>" id="<?php echo $this->get_field_id( 'gle_cpt_inpost_support_custom' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'gle_cpt_inpost_support_custom' ) ); ?>" size="50" />
				</label>
			</p>

			<p>
				<span class="description"><?php echo sprintf( __( 'Enter comma separated list of your specific IDs (registered names) of Custom Post Types above (for example %s etc.).', 'genesis-layout-extras' ), '<code>favorite_books,movie_reviews,german_food</code>' ); ?></span>
			</p>

		<?php if ( class_exists( 'Genesis_Admin_CPT_Archive_Settings' ) ) : ?>
			<hr class="div" />

			<h4><?php _e( 'Genesis Post Type Archive Settings', 'genesis-layout-extras' ); ?></h4>

				<p>
					<input type="checkbox" name="<?php echo $this->get_field_name( 'gle_cpt_archives_support_global' ); ?>" id="<?php echo $this->get_field_id( 'gle_cpt_archives_support_global' ); ?>" value="1"<?php checked( $this->get_field_value( 'gle_cpt_archives_support_global' ) ); ?> />
					<label for="<?php echo $this->get_field_id( 'gle_cpt_archives_support_global' ); ?>">
						<?php _e( 'Enable Genesis Archive Settings for ALL public Custom Post Types? (global)', 'genesis-layout-extras' ); ?>
					</label>
				</p>

				<p>
					<blockquote><em><?php _e( 'OR', 'genesis-layout-extras' ); ?>:</em></blockquote>
				</p>

				<p>
					<label for="<?php echo $this->get_field_id( 'gle_cpt_archives_support_custom' ); ?>">
						<?php _e( 'Enable Genesis Archive Settings for your specific Custom Post Types?', 'genesis-layout-extras' ); ?>
						<br /><input type="text" name="<?php echo $this->get_field_name( 'gle_cpt_archives_support_custom' ); ?>" id="<?php echo $this->get_field_id( 'gle_cpt_archives_support_custom' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'gle_cpt_archives_support_custom' ) ); ?>" size="50" />
					</label>
				</p>

				<p>
					<span class="description"><?php echo sprintf( __( 'Enter comma separated list of your specific IDs (registered names) of Custom Post Types above (for example %s etc.).', 'genesis-layout-extras' ), '<code>file_downloads,job_requests,staff_members</code>' ); ?></span>
				</p>

				<?php ddw_gle_save_button(); ?>
		<?php endif; ?>

		<?php
		/** ^End form code */

		/** Action Hook 'gle_post_type_support_metabox' */
	    do_action( 'gle_post_type_support_metabox' );

	}  // end of method gle_post_type_support_metabox


	/**
	 * Layouts: WordPress Defaults Metabox.
	 *
	 * @since 2.0.0
	 */
	function gle_layouts_wordpress_defaults_metabox() {

		/** Begin form code */
		?>

			<p>
				<span class="description"><?php echo __( 'Here you can set up a <strong>default</strong> layout option for various extra archive pages and other special pages.', 'genesis-layout-extras' ) . ' ' . sprintf(
						__( '%1$sGenesis Default%2$s in the drop-down menus below always means the chosen default layout option in the regular <a href="%3$s">Genesis layout settings</a>.', 'genesis-layout-extras' ),
						'<code style="font-style: normal; color: #333;">',
						'</code>',
						admin_url( 'admin.php?page=genesis#genesis-theme-settings-layout' )
					); ?></span>
			</p>

		<hr class="div" />

		<h4><?php _e( 'Layouts for Special Sections', 'genesis-layout-extras' ); ?></h4>

			<?php ddw_genesis_layout_extras_option(
					__( 'Hompage Layout', 'genesis-layout-extras' ) . ': ',
					'ddw_genesis_layout_home'
			); ?>

				<p>
					<span class="description"><?php echo sprintf(
						__( 'This setting works for homepage templates (file %1$shome.php%2$s is there - %1$sis_home()%2$s) <u>and</u> also for static pages as front page (%1$sis_front_page()%2$s).', 'genesis-layout-extras' ),
						'<code style="font-style: normal; color: #333;">',
						'</code>'
					); ?></span>
				</p>

			<?php ddw_genesis_layout_extras_option(
				__( 'Search Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_search'
			); ?>

				<p>
					<span class="description"><?php _e( 'For regular search results display &ndash; if there are any results.', 'genesis-layout-extras' ); ?></span>
				</p>

			<?php ddw_genesis_layout_extras_option(
				__( 'Search Not Found Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_search_not_found'
			); ?>

				<p>
					<span class="description"><?php _e( 'If there are NO search results (empty).', 'genesis-layout-extras' ); ?></span>
				</p>

			<?php ddw_genesis_layout_extras_option(
				__( '404 Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_404'
			); ?>

				<p>
					<span class="description"><?php echo sprintf(
						__( 'If a page/URL is not found. Regarding the %1$s404.php%2$s error page template from Genesis core or from current child theme.', 'genesis-layout-extras' ),
						'<code style="font-style: normal; color: #333;">',
						'</code>'
					); ?></span>
				</p>

			<?php ddw_gle_save_button(); ?>

		<hr class="div" />

		<h4><?php _e( 'Global Layouts for Singular Pages', 'genesis-layout-extras' ); ?></h4>

			<?php ddw_genesis_layout_extras_option(
				__( 'Post Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_post'
			);

			ddw_genesis_layout_extras_option(
				__( 'Page Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_page'
			);

			ddw_genesis_layout_extras_option(
				__( 'Attachment Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_attachment'
			); ?>

			<?php ddw_gle_save_button(); ?>

		<hr class="div" />

		<h4><?php _e( 'Global Layouts for Archive Sections', 'genesis-layout-extras' ); ?></h4>

			<?php ddw_genesis_layout_extras_option(
				__( 'Author Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_author'
			);

			ddw_genesis_layout_extras_option(
				__( 'Date Archive Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_date'
			); ?>

				<p>
					<span class="description"><?php echo sprintf(
						__( 'This is the general setting for date archives and overwrites the following three settings (Year, Month, Day)! So, if you setup any of the following three settings then let this one here on %1$sGenesis Default%2$s.', 'genesis-layout-extras' ),
						'<code style="font-style: normal; color: #333;">',
						'</code>'
					); ?></span>
				</p>

			<?php ddw_genesis_layout_extras_option(
				'&middot; ' . __( 'Date Archive - Year Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_date_year'
			);

			ddw_genesis_layout_extras_option(
				'&middot; ' . __( 'Date Archive - Month Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_date_month'
			);

			ddw_genesis_layout_extras_option(
				'&middot; ' . __( 'Date Archive - Day Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_date_day'
			);

			ddw_genesis_layout_extras_option(
				__( 'Category Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_category'
			);

			ddw_genesis_layout_extras_option(
				__( 'Tag Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_tag'
			);

			ddw_genesis_layout_extras_option(
				__( 'Taxonomy Page Layout', 'genesis-layout-extras' ) . ': ',
				'ddw_genesis_layout_taxonomy'
			); 

			ddw_gle_save_button(); ?>

		<?php
		/** ^End form code */

		/** Action Hook 'gle_layouts_wordpress_defaults_metabox' */
	    do_action( 'gle_layouts_wordpress_defaults_metabox' );

	}  // end of method gle_layouts_wordpress_defaults_metabox


	/**
	 * Export/ Import Metabox.
	 *
	 * @since 2.0.0
	 *
	 * @uses  admin_url()
	 * @uses  wp_get_current_user()
	 * @uses  current_user_can()
	 * @uses  current_theme_supports()
	 * @uses  get_the_author_meta()
	 */
	function gle_export_import_metabox() {

		/** Get current user - needed for Genesis menu check */
		$gle_user = wp_get_current_user();

		/** Begin form code */
		?>

		<?php 	if ( current_user_can( 'edit_theme_options' )
					&& current_theme_supports( 'genesis-import-export-menu' )
					&& get_the_author_meta( 'genesis_import_export_menu', $gle_user->ID )
				) {
		?>

			<h4><?php _e( 'Export and import plugin settings.', 'genesis-layout-extras' ); ?></h4>

				<p>
					&rarr; <?php echo sprintf( __( 'Hooked in and done via regular %s.', 'genesis-layout-extras' ), '<a href="' . admin_url( 'admin.php?page=genesis-import-export' ) . '">' . __( 'Genesis Export/ Import', 'genesis-layout-extras' ) . '</a>' ); ?>
				</p>

				<p>
					<span class="description"><?php _e( 'Tip: You can do it for this plugin alone or combine plugin settings with Genesis settings to transfer settings from one Genesis site to another.', 'genesis-layout-extras' ); ?></span>
				</p>

		<?php }  // end-if cap & user check ?>

		<?php
		/** ^End form code */

		/** Action Hook 'gle_export_import_metabox' */
	    do_action( 'gle_export_import_metabox' );

	}  // end of method gle_export_import_metabox


	/**
	 * Set up the Help Tab system.
	 *
	 * @since 2.0.0
	 *
	 * @uses  callback functions located in theme file
	 *        '/includes/gle-admin-help.php' with the actual help tab content
	 * @uses  get_current_screen()
	 * @uses  WP_Screen::add_help_tab()
	 * @uses  WP_Screen::set_help_sidebar()
	 * @uses  ddw_gle_help_sidebar_content()
	 * @uses  ddw_gle_help_sidebar_content_extra()
	 */
	 function help() {

		/** Get current screen */
	 	$screen = get_current_screen();

		/** Display help tabs only for WordPress 3.3 or higher */
		if ( ! class_exists( 'WP_Screen' ) || ! $screen ) {
			return;
		}

		/** Add starting/general help tab */
		$screen->add_help_tab( array(
			'id'       => 'gle-plugin-start-help', 
			'title'    => __( 'Genesis Layout Extras', 'genesis-layout-extras' ),
			'callback' => apply_filters( 'gle_filter_help_content_plugin_start', 'ddw_gle_plugin_start_help' ),
		) );

		/** Add Layout Extras help tab */
		if ( ddw_gle_core_sidebars_exists( 'sidebar' ) ) {

			$screen->add_help_tab( array(
				'id'       => 'gle-layout-extras-help', 
				'title'    => __( 'Layouts', 'genesis-layout-extras' ),
				'callback' => apply_filters( 'gle_filter_help_content_layout_extras', 'ddw_gle_help_content_layout_extras' ),
			) );

		}  // end if

		/** Add Post Type Support help tab */
		if ( ddw_gle_check_cpts() ) {

			$screen->add_help_tab( array(
				'id'       => 'gle-post-type-support-help', 
				'title'    => _x( 'Additional Post Type Support', 'Translators: Help tab menu title', 'genesis-layout-extras' ),
				'callback' => apply_filters( 'gle_filter_help_content_post_type_support', 'ddw_gle_help_content_post_type_support' ),
			) );

		}  // end if

		/** Help tab only if supported plugins active */
		if ( ddw_gle_supported_plugins() ) {

			/** Add Plugin Support help tab */
			$screen->add_help_tab( array(
				'id'       => 'gle-plugin-support-help',
				'title'    => __( 'Plugin Support', 'genesis-layout-extras' ),
				'callback' => apply_filters( 'gle_filter_help_content_plugins', 'ddw_gle_admin_help_plugins' ),
			) );

			require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-help-plugins.php' );

		}  // end-if plugins check

		/**
		 * Help tab only if supported child themes are active
		 */

			/** Function slug part - variable init */
			$gle_child_theme_help = 'default';

			/** By StudioPress */
			if ( ( function_exists( 'minimum_portfolio_post_type' ) || function_exists( 'executive_portfolio_post_type' ) )
				&& post_type_exists( 'portfolio' )
			) {

				/** StudioPress function slug part */
				$gle_child_theme_help = 'studiopress';

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-help-' . $gle_child_theme_help . '.php' );

			}

			/** By Themedy brand */
			if ( ddw_gle_supported_themedy()
				&& ( post_type_exists( 'products' ) || post_type_exists( 'photo' ) )
			) {

				/** Themedy function slug part */
				$gle_child_theme_help = 'themedy';

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-help-' . $gle_child_theme_help . '.php' );

			}  // end-if child theme themedy check

			/** By ZigZagPress brand */
			if ( ddw_gle_supported_zigzagpress() ) {

				/** ZigZagPress function slug part */
				$gle_child_theme_help = 'zigzagpress';

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-help-' . $gle_child_theme_help . '.php' );

			}  // end-if child theme zigzagpress check

			/** Add the help tab */
			if ( 'studiopress' == $gle_child_theme_help
				|| 'themedy' == $gle_child_theme_help
				|| 'zigzagpress' == $gle_child_theme_help
			) {

				require_once( GLE_PLUGIN_DIR . '/includes/gle-admin-help-' . $gle_child_theme_help . '.php' );

				$screen->add_help_tab(array(
					'id'       => 'gle-child-theme-support',
					'title'    => __( 'Child Theme Support', 'genesis-layout-extras' ),
					'callback' => apply_filters( 'gle_filter_help_' . $gle_child_theme_help . '', 'ddw_gle_admin_help_' . $gle_child_theme_help . '' ),
				) );

			}  // end-if child support check


		/** Add FAQ help tab */
		$screen->add_help_tab( array(
			'id'       => 'gle-faq-help',
			'title'    => __( 'FAQ - Frequently Asked Questions', 'genesis-layout-extras' ),
			'callback' => apply_filters( 'gle_filter_help_content_faq', 'ddw_gle_help_content_faq' ),
		) );

		/** Add Translations help tab */
		$screen->add_help_tab( array(
			'id'       => 'gle-plugin-translations-help', 
			'title'    => __( 'Translations', 'genesis-layout-extras' ),
			'callback' => apply_filters( 'gle_filter_help_content_translations', 'ddw_gle_help_content_translations' ),
		) );

		/** Add Recommended Plugins help tab */
		$screen->add_help_tab( array(
			'id'       => 'gle-recommended-plugins-help', 
			'title'    => __( 'Recommended Plugins', 'genesis-layout-extras' ),
			'callback' => apply_filters( 'gle_filter_help_content_recommended_plugins', 'ddw_gle_help_content_recommended_plugins' ),
		) );

		/** Add help sidebar */
		$screen->set_help_sidebar( ddw_gle_help_sidebar_content_extra() . ddw_gle_help_sidebar_content() );

	}  // end of method help

}  // end of class


add_action( 'genesis_admin_menu', 'ddw_gle_settings_menu' );
/**
 * Instantiate the class to create the menu.
 *
 * @since  2.0.0
 *
 * @param  $gle_plugin_settings
 *
 * @global mixed $gle_plugin_settings
 */
function ddw_gle_settings_menu() {

	global $gle_plugin_settings;

	$gle_plugin_settings = new DDW_GLE_Plugin_Settings;

}  // end of function ddw_gle_settings_menu