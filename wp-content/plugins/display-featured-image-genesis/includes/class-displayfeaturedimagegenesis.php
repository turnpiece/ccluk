<?php
/**
 * Display Featured Image for Genesis
 *
 * @package   DisplayFeaturedImageGenesis
 * @author    Robin Cornett <hello@robincornett.com>
 * @link      https://github.com/robincornett/display-featured-image-genesis/
 * @copyright 2014-2016 Robin Cornett
 * @license   GPL-2.0+
 */

/**
 * Main plugin class.
 *
 * @package DisplayFeaturedImageGenesis
 */
class Display_Featured_Image_Genesis {

	/**
	 * Admin area class: handles columns.
	 * @var Display_Featured_Image_Genesis_Admin $admin
	 */
	protected $admin;

	/**
	 * Adds new author meta.
	 * @var Display_Featured_Image_Genesis_Author $author
	 */
	protected $author;

	/**
	 * Common class: sets image ID, post title, handles database query
	 * @var Display_Featured_Image_Genesis_Common $common
	 */
	protected $common;

	/**
	 * @var $customizer Display_Featured_Image_Genesis_Customizer
	 */
	protected $customizer;

	/**
	 * All archive description functions.
	 * @var Display_Featured_Image_Genesis_Description $description
	 */
	protected $description;

	/**
	 * Manages help tabs for settings page.
	 * @var $helptabs Display_Featured_Image_Genesis_HelpTabs
	 */
	protected $helptabs;

	/**
	 * Handles all image output functionality
	 * @var Display_Featured_Image_Genesis_Output $output
	 */
	protected $output;

	/**
	 * Updates metabox on post edit page
	 * @var Display_Featured_Image_Genesis_Post_Meta $post_meta
	 */
	protected $post_meta;

	/**
	 * Handles RSS feed output
	 * @var Display_Featured_Image_Genesis_RSS $rss
	 */
	protected $rss;

	/**
	 * Sets up settings page for the plugin.
	 * @var Display_Featured_Image_Genesis_Settings $settings
	 */
	protected $settings;

	/**
	 * Handles term meta.
	 * @var Display_Featured_Image_Genesis_Taxonomies $taxonomies
	 */
	protected $taxonomies;

	/**
	 * Display_Featured_Image_Genesis constructor.
	 *
	 * @param $admin
	 * @param $author
	 * @param $common
	 * @param $customizer
	 * @param $description
	 * @param $helptabs
	 * @param $output
	 * @param $rss
	 * @param $settings
	 * @param $taxonomies
	 */
	function __construct( $admin, $author, $common, $customizer, $description, $helptabs, $output, $post_meta, $rss, $settings, $taxonomies ) {
		$this->admin       = $admin;
		$this->author      = $author;
		$this->common      = $common;
		$this->customizer  = $customizer;
		$this->description = $description;
		$this->helptabs    = $helptabs;
		$this->output      = $output;
		$this->post_meta   = $post_meta;
		$this->rss         = $rss;
		$this->settings    = $settings;
		$this->taxonomies  = $taxonomies;
	}

	/**
	 * Main plugin function. Starts up all the things.
	 */
	public function run() {
		if ( 'genesis' !== basename( get_template_directory() ) ) {
			add_action( 'admin_init', array( $this, 'deactivate' ) );
			return;
		}

		require plugin_dir_path( __FILE__ ) . 'helper-functions.php';

		// Plugin setup
		add_action( 'after_setup_theme', array( $this, 'add_plugin_supports' ) );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . DISPLAYFEATUREDIMAGEGENESIS_BASENAME, array( $this, 'add_settings_link' ) );

		// Admin
		add_action( 'admin_init', array( $this->admin, 'set_up_columns' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Taxonomies, Author, Post Meta
		add_filter( 'displayfeaturedimagegenesis_get_taxonomies', array( $this->taxonomies, 'remove_post_status_terms' ) );
		add_action( 'admin_init', array( $this->taxonomies, 'set_taxonomy_meta' ) );
		add_action( 'admin_init', array( $this->author, 'set_author_meta' ) );
		add_filter( 'admin_post_thumbnail_html', array( $this->post_meta, 'meta_box' ), 10, 2 );
		add_action( 'save_post', array( $this->post_meta, 'save_meta' ) );

		// Settings
		add_action( 'admin_menu', array( $this->settings, 'do_submenu_page' ) );
		add_filter( 'displayfeaturedimagegenesis_get_setting', array( $this->settings, 'get_display_setting' ) );
		add_action( 'load-appearance_page_displayfeaturedimagegenesis', array( $this->helptabs, 'help' ) );

		// Customizer
		add_action( 'customize_register', array( $this->customizer, 'customizer' ) );

		// Front End Output
		add_action( 'get_header', array( $this->output, 'manage_output' ) );
		add_filter( 'genesis_get_image_default_args', array( $this->output, 'change_thumbnail_fallback' ) );

		// RSS
		add_action( 'template_redirect', array( $this->rss, 'maybe_do_feed' ) );

	}

	/**
	 * deactivates the plugin if Genesis isn't running
	 *
	 *  @since 1.1.2
	 *
	 */
	public function deactivate() {
		deactivate_plugins( DISPLAYFEATUREDIMAGEGENESIS_BASENAME );
		add_action( 'admin_notices', array( $this, 'error_message' ) );
	}

	/**
	 * Error message if we're not using the Genesis Framework.
	 *
	 * @since 1.1.0
	 */
	public function error_message() {

		$error = sprintf( __( 'Sorry, Display Featured Image for Genesis works only with the Genesis Framework. It has been deactivated.', 'display-featured-image-genesis' ) );

		if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
			$error = $error . sprintf(
				__( ' But since we\'re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'display-featured-image-genesis' ),
				PHP_VERSION
			);
		}

		echo '<div class="error"><p>' . esc_attr( $error ) . '</p></div>';

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

	}


	/**
	 * add plugin support for new image size and excerpts on pages, if move excerpts option is enabled
	 *
	 * @since 1.3.0
	 */
	public function add_plugin_supports() {

		$args = apply_filters( 'displayfeaturedimagegenesis_custom_image_size', array(
			'width'  => 2000,
			'height' => 2000,
			'crop'   => false,
		) );
		add_image_size( 'displayfeaturedimage_backstretch', (int) $args['width'], (int) $args['height'], (bool) $args['crop'] );

		$displaysetting = displayfeaturedimagegenesis_get_setting();
		if ( $displaysetting['move_excerpts'] ) {
			add_post_type_support( 'page', 'excerpt' );
		}
	}

	/**
	 * check existing settings array to see if a setting is in the array
	 * @return updated setting updates to default (0)
	 * @since  1.5.0
	 */
	public function check_settings() {

		$displaysetting = displayfeaturedimagegenesis_get_setting();

		// return early if the option doesn't exist yet
		if ( empty( $displaysetting ) ) {
			return;
		}

		if ( empty( $displaysetting['feed_image'] ) ) {
			$this->update_settings( array(
				'feed_image' => 0,
			) );
		}

		// new setting for titles added in 2.0.0
		if ( empty( $displaysetting['keep_titles'] ) ) {
			$this->update_settings( array(
				'keep_titles' => 0,
			) );
		}

		// new setting for subsequent pages added in 2.2.0
		if ( empty( $displaysetting['is_paged'] ) ) {
			$this->update_settings( array(
				'is_paged' => 0,
			) );
		}

	}

	/**
	 * Takes an array of new settings, merges them with the old settings, and pushes them into the database.
	 *
	 * @since 1.5.0
	 *
	 * @param string|array $new     New settings. Can be a string, or an array.
	 * @param string       $setting Optional. Settings field name. Default is displayfeaturedimagegenesis.
	 */
	protected function update_settings( $new = '', $setting = 'displayfeaturedimagegenesis' ) {
		return update_option( $setting, wp_parse_args( $new, get_option( $setting ) ) );
	}

	/**
	 * Set up text domain for translations
	 *
	 * @since 1.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'display-featured-image-genesis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * enqueue admin scripts
	 * @return scripts to use image uploader
	 *
	 * @since  1.2.1
	 */
	public function enqueue_scripts() {

		$version = $this->common->version;

		wp_register_script( 'displayfeaturedimage-upload', plugins_url( '/includes/js/settings-upload.js', dirname( __FILE__ ) ), array( 'jquery', 'media-upload', 'thickbox' ), $version );
		wp_register_script( 'widget_selector', plugins_url( '/includes/js/widget-selector.js', dirname( __FILE__ ) ), array( 'jquery' ), $version );

		$screen     = get_current_screen();
		$screen_ids = array(
			'appearance_page_displayfeaturedimagegenesis',
			'profile',
			'user-edit',
		);

		if ( in_array( $screen->id, $screen_ids, true ) || ! empty( $screen->taxonomy ) ) {
			wp_enqueue_media();
			wp_enqueue_script( 'displayfeaturedimage-upload' );
			wp_localize_script( 'displayfeaturedimage-upload', 'objectL10n', array(
				'text' => __( 'Select Image', 'display-featured-image-genesis' ),
			) );
		}

		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() && ! function_exists( 'genesis' ) ) {
			return;
		}

		if ( in_array( $screen->id, array( 'widgets', 'customize' ), true ) ) {
			wp_enqueue_script( 'widget_selector' );
			wp_localize_script( 'widget_selector', 'displayfeaturedimagegenesis_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
		}

	}

	/**
	 * Register widgets for plugin
	 * @return widgets Taxonomy/term, CPT, and Author widgets
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {

		$files = array(
			'author',
			'cpt-archive',
			'taxonomy',
		);

		foreach ( $files as $file ) {
			require_once plugin_dir_path( __FILE__ ) . 'widgets/displayfeaturedimagegenesis-' . $file . '-widget.php';
		}

		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() && ! function_exists( 'genesis' ) ) {
			return;
		}
		register_widget( 'Display_Featured_Image_Genesis_Author_Widget' );
		register_widget( 'Display_Featured_Image_Genesis_Widget_Taxonomy' );
		register_widget( 'Display_Featured_Image_Genesis_Widget_CPT' );

	}

	/**
	 * Add link to plugin settings page in plugin table
	 * @param $links link to settings page
	 *
	 * @since 2.3.0
	 */
	public function add_settings_link( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'themes.php?page=displayfeaturedimagegenesis' ) ), esc_attr__( 'Settings', 'display-featured-image-genesis' ) );
		return $links;
	}
}
