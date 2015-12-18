<?php
/**
 * Welcome Screen Class
 * Sets up the welcome screen page, hides the menu item
 * and contains the screen content.
 */
class Politics_Welcome {

	/**
	 * Constructor
	 * Sets up the welcome screen
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'politics_welcome_register_menu' ) );
		add_action( 'load-themes.php', array( $this, 'politics_activation_admin_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'politics_welcome_scripts' ) );

		add_action( 'politics_welcome', array( $this, 'politics_welcome_intro' ), 				10 );
		add_action( 'politics_welcome', array( $this, 'politics_welcome_tabs' ), 				20 );
		add_action( 'politics_welcome', array( $this, 'politics_welcome_getting_started' ), 	30 );
		add_action( 'politics_welcome', array( $this, 'politics_welcome_support' ), 				40 );
		add_action( 'politics_welcome', array( $this, 'politics_welcome_changelog' ), 		50 );

	} // end constructor

	/**
	 * Adds an admin notice upon successful activation.
	 */
	public function politics_activation_admin_notice() {
		global $pagenow;

		if ( is_admin() && 'themes.php' == $pagenow && isset( $_GET['activated'] ) ) { // input var okay
			add_action( 'admin_notices', array( $this, 'politics_welcome_admin_notice' ), 99 );
		}
	}

	/**
	 * Display an admin notice linking to the welcome screen
	 */
	public function politics_welcome_admin_notice() {
		?>
			<div class="updated notice is-dismissible">
				<p><?php echo sprintf( esc_html__( 'Thanks for choosing Politics! You can read instructions on how get the most out of your new theme on the %stheme info screen%s.', 'politics' ), '<a href="' . esc_url( admin_url( 'themes.php?page=politics-welcome' ) ) . '">', '</a>' ); ?></p>
				<p><a href="<?php echo esc_url( admin_url( 'themes.php?page=politics-welcome' ) ); ?>" class="button" style="text-decoration: none;"><?php _e( 'Get started with Politics', 'politics' ); ?></a></p>
			</div>
		<?php
	}

	/**
	 * Load welcome screen css
	 * @return void
	 */
	public function politics_welcome_scripts() {
		global $politics_version;

		wp_enqueue_style( 'politics-theme-info', get_template_directory_uri() . '/inc/theme-info/css/welcome.css', $politics_version );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
	}

	/**
	 * Creates the dashboard page
	 * @see  add_theme_page()
	 */
	public function politics_welcome_register_menu() {
		add_theme_page( 'Theme Info', 'Theme Info', 'read', 'politics-welcome', array( $this, 'politics_welcome_screen' ) );
	}

	/**
	 * The welcome screen
	 */
	public function politics_welcome_screen() {
		require_once( ABSPATH . 'wp-load.php' );
		require_once( ABSPATH . 'wp-admin/admin.php' );
		require_once( ABSPATH . 'wp-admin/admin-header.php' );
		?>
		<div class="wrap about-wrap">

			<?php
			/**
			 * @hooked politics_welcome_intro - 10
			 * @hooked politics_welcome_getting_started - 20
			 * @hooked politics_welcome_addons - 30
			 */
			do_action( 'politics_welcome' ); ?>

		</div>
		<?php
	}

	/**
	 * Welcome screen intro
	 */
	public function politics_welcome_intro() {
		require_once( get_template_directory() . '/inc/theme-info/sections/intro.php' );
	}

	/**
	 * Welcome screen intro
	 */
	public function politics_welcome_tabs() {
		require_once( get_template_directory() . '/inc/theme-info/sections/tabs.php' );
	}

	/**
	 * Welcome screen getting started section
	 */
	public function politics_welcome_getting_started() {
		require_once( get_template_directory() . '/inc/theme-info/sections/start.php' );
	}

	/**
	 * Welcome screen support theme
	 */
	public function politics_welcome_support() {
		require_once( get_template_directory() . '/inc/theme-info/sections/support.php' );
	}

	/**
	 * Welcome screen changelog
	 */
	public function politics_welcome_changelog() {
		require_once( get_template_directory() . '/inc/theme-info/sections/changelog.php' );
	}

	/**
	 * Display the changelog file from the theme
	 */
	public function politics_changlog() {

		WP_Filesystem();
		global $wp_filesystem;

		$file = $wp_filesystem->get_contents( get_template_directory_uri() . '/changelog.txt' );
		$readme = nl2br( $file );

		return $readme;

	}

}

$GLOBALS['Politics_Welcome'] = new Politics_Welcome();
