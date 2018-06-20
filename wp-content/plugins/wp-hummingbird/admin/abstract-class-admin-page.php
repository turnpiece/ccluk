<?php

/**
 * Class WP_Hummingbird_Admin_Page
 */
abstract class WP_Hummingbird_Admin_Page {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	protected $slug = '';

	/**
	 * Meta boxes array.
	 *
	 * @var array
	 */
	protected $meta_boxes = array();

	/**
	 * Submenu tabs.
	 *
	 * @var array
	 */
	protected $tabs = array();

	/**
	 * Page ID.
	 *
	 * @var false|null|string
	 */
	public $page_id = null;

	/**
	 * Admin notices.
	 *
	 * @var WP_Hummingbird_Admin_Notices
	 */
	protected $admin_notices;

	/**
	 * WP_Hummingbird_Admin_Page constructor.
	 *
	 * @param string $slug        Module slug.
	 * @param string $page_title  Page title.
	 * @param string $menu_title  Menu title.
	 * @param bool   $parent      Parent or not.
	 * @param bool   $render      Render the page.
	 */
	public function __construct( $slug, $page_title, $menu_title, $parent = false, $render = true ) {
		$this->slug = $slug;

		$this->admin_notices = WP_Hummingbird_Admin_Notices::get_instance();

		if ( ! $parent ) {
			$this->page_id = add_menu_page(
				$page_title,
				$menu_title,
				WP_Hummingbird_Utils::get_admin_capability(),
				$slug,
				$render ? array( $this, 'render' ) : null,
				'data:image/svg+xml;base64,' . base64_encode( '<?xml version="1.0" encoding="utf-8"?><svg width="1024" height="1024" viewBox="0 -960 1024 1024" xmlns="http://www.w3.org/2000/svg"><path fill="black" transform="scale(-1,1) rotate(180)" d="M1009.323 570.197c-72.363-3.755-161.621-7.509-238.933-8.192l192.171 128.512c19.042-34.586 34.899-74.653 45.502-116.806zM512 960c189.862-0.034 355.572-103.406 443.951-256.93-61.487-12.553-225.839-36.617-400.943-48.051-34.133-2.219-55.979-36.181-68.267-62.464 0 0-31.061 195.925-244.907 145.408-41.984 18.944-81.237 34.133-116.224 46.251 94.16 107.956 231.957 175.787 385.597 175.787 0.279 0 0.557 0 0.836-0.001zM0 448c0 0.221-0.001 0.483-0.001 0.746 0 121.29 42.344 232.689 113.056 320.222 39.45-15.556 74.218-33.581 106.162-55.431s37.807-77.121 65.284-135.489 46.592-91.136 54.613-161.109 65.877-184.491 168.277-221.867c-34.879-47.972-65.982-102.598-90.759-160.574 26.898-39.4 57.774-69.843 91.053-97.495-280.204 0.74-507.686 229.298-507.686 510.988 0 0.003 0 0.007 0 0.010zM573.952-60.416c0 19.115 0 36.352 1.195 51.2 2.803 46.275 12.454 89.473 27.966 129.761 19.44 50.098 31.281 111.481 31.281 175.63 0 12.407-0.443 24.711-1.314 36.896-1.165 15.156-3.891 30.694-7.991 45.664l392.938 149.478c4.007-24.063 6.297-51.79 6.297-80.052 0-260.928-195.185-476.268-447.514-507.978z"/></svg>' )
			);
		} else {
			$this->page_id = add_submenu_page(
				$parent,
				$page_title,
				$menu_title,
				WP_Hummingbird_Utils::get_admin_capability(),
				$slug,
				$render ? array( $this, 'render' ) : null
			);
		}

		if ( $render ) {
			add_action( 'load-' . $this->page_id, array( $this, 'register_meta_boxes' ) );
			add_action( 'load-' . $this->page_id, array( $this, 'on_load' ) );
			add_action( 'load-' . $this->page_id, array( $this, 'trigger_load_action' ) );
			add_filter( 'load-' . $this->page_id, array( $this, 'add_screen_hooks' ) );
		}
	}

	/**
	 * Return the admin menu slug
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Trigger an action before this screen is loaded
	 */
	public function trigger_load_action() {
		do_action( 'wphb_load_admin_page_' . $this->get_slug() );
	}

	/**
	 * Load an admin view.
	 *
	 * @param string $name  View name = file name.
	 * @param array  $args  Arguments.
	 * @param bool   $echo  Echo or return.
	 *
	 * @return string
	 */
	public function view( $name, $args = array(), $echo = true ) {
		$file = WPHB_DIR_PATH . "admin/views/{$name}.php";
		$content = '';

		if ( is_file( $file ) ) {

			ob_start();

			if ( isset( $args['id'] ) ) {
				$args['orig_id'] = $args['id'];
				$args['id'] = str_replace( '/', '-', $args['id'] );
			}
			extract( $args );

			/* @noinspection PhpIncludeInspection */
			include( $file );

			$content = ob_get_clean();
		}

		if ( ! $echo ) {
			return $content;
		}

		echo $content;
	}

	/**
	 * Check if view exists.
	 *
	 * @param string $name  View name = file name.
	 *
	 * @return bool
	 */
	protected function view_exists( $name ) {
		$file = WPHB_DIR_PATH . "admin/views/{$name}.php";
		return is_file( $file );
	}

	/**
	 * Common hooks for all screens
	 */
	public function add_screen_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
		add_action( 'network_admin_notices', array( $this, 'notices' ) );

		// Add the admin body classes.
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
	}

	/**
	 * Return the classes to be added to the body in the admin.
	 *
	 * @param String $classes Classes to be added.
	 * @return String
	 */
	public function admin_body_class( $classes ) {
		$classes .= ' sui-2-2-4 wpmud ';

		return $classes;
	}

	/**
	 * Notices.
	 */
	public function notices() {}

	/**
	 * Function triggered when the page is loaded before render any content
	 */
	public function on_load() {}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $hook  Hook from where the call is made.
	 */
	public function enqueue_scripts( $hook ) {
		// Styles.
		wp_enqueue_style( 'wphb-admin', WPHB_DIR_URL . 'admin/assets/css/app.min.css', array(), WPHB_VERSION );

		// Scripts.
		WP_Hummingbird_Utils::enqueue_admin_scripts( WPHB_VERSION );
		wp_enqueue_script(
			'wpmudev-sui',
			WPHB_DIR_URL . 'admin/assets/js/shared-ui.min.js',
			array( 'jquery' ),
			WPHB_VERSION,
			true
		);

		// Google visualization library for Uptime.
		if ( 'hummingbird-pro_page_wphb-uptime' === $hook ) {
			wp_enqueue_script(
				'wphb-google-chart',
				"https://www.google.com/jsapi?autoload={'modules':[{'name':'visualization','version':'1.1','packages':['corechart','timeline']}]}",
				array( 'jquery' )
			);
		}
	}

	/**
	 * Trigger before on_load, allows to register meta boxes for the page
	 */
	public function register_meta_boxes() {}

	/**
	 * Add meta box.
	 *
	 * @param string $id               Meta box ID.
	 * @param string $title            Meta box title.
	 * @param string $callback         Callback for meta box content.
	 * @param string $callback_header  Callback for meta box header.
	 * @param string $callback_footer  Callback for meta box footer.
	 * @param string $context          Meta box context.
	 * @param array  $args             Arguments.
	 */
	public function add_meta_box( $id, $title, $callback = '', $callback_header = '', $callback_footer = '', $context = 'main', $args = array() ) {
		$default_args = array(
			'box_class'         => 'sui-box',
			'box_header_class'  => 'sui-box-header',
			'box_content_class' => 'sui-box-body',
			'box_footer_class'  => 'sui-box-footer',
		);

		$args = wp_parse_args( $args, $default_args );

		if ( ! isset( $this->meta_boxes[ $this->slug ] ) ) {
			$this->meta_boxes[ $this->slug ] = array();
		}

		if ( ! isset( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			$this->meta_boxes[ $this->slug ][ $context ] = array();
		}

		if ( ! isset( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			$this->meta_boxes[ $this->slug ][ $context ] = array();
		}

		$meta_box = array(
			'id'              => $id,
			'title'           => $title,
			'callback'        => $callback,
			'callback_header' => $callback_header,
			'callback_footer' => $callback_footer,
			'args'            => $args,
		);

		/**
		 * Allow to filter a WP Hummingbird Metabox
		 *
		 * @param array $meta_box Meta box attributes
		 * @param string $slug Admin page slug
		 * @param string $page_id Admin page ID
		 */
		$meta_box = apply_filters( 'wphb_add_meta_box', $meta_box, $this->slug, $this->page_id );
		$meta_box = apply_filters( 'wphb_add_meta_box_' . $meta_box['id'], $meta_box, $this->slug, $this->page_id );

		if ( $meta_box ) {
			$this->meta_boxes[ $this->slug ][ $context ][ $id ] = $meta_box;
		}
	}

	/**
	 * Render meta box.
	 *
	 * @param string $context  Meta box context. Default: main.
	 */
	protected function do_meta_boxes( $context = 'main' ) {
		if ( empty( $this->meta_boxes[ $this->slug ][ $context ] ) ) {
			return;
		}

		do_action_ref_array( 'wphb_admin_do_meta_boxes_' . $this->slug, array( &$this ) );

		foreach ( $this->meta_boxes[ $this->slug ][ $context ] as $id => $box ) {
			$args = array(
				'title'           => $box['title'],
				'id'              => $id,
				'callback'        => $box['callback'],
				'callback_header' => $box['callback_header'],
				'callback_footer' => $box['callback_footer'],
				'args'            => $box['args'],
			);
			$this->view( 'meta-box', $args );
		}
	}

	/**
	 * Check if there is any meta box for a given context.
	 *
	 * @param string $context  Meta box context.
	 *
	 * @return bool
	 */
	protected function has_meta_boxes( $context ) {
		return ! empty( $this->meta_boxes[ $this->slug ][ $context ] );
	}

	/**
	 * Renders the template header that is repeated on every page.
	 * From WPMU DEV Dashboard
	 */
	protected function render_header() {
		?>
		<div class="sui-header">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<div class="sui-actions-right">
				<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_documentation_url( $this->slug, $this->get_current_tab() ) ); ?>" target="_blank" class="sui-button sui-button-ghost">
					<i class="sui-icon-academy" aria-hidden="true"></i>
					<?php esc_html_e( 'View Documentation', 'wphb' ); ?>
				</a>
			</div>
		</div><!-- end header -->
		<?php
	}

	/**
	 * Render the page
	 */
	public function render() {
		?>
		<div class="sui-wrap wrap wrap-wp-hummingbird wrap-wp-hummingbird-page <?php echo 'wrap-' . esc_attr( $this->slug ); ?>">
			<div class="sui-notice-top sui-notice-success sui-hidden" id="wphb-ajax-update-notice">
				<p><?php esc_html_e( 'Settings updated', 'wphb' ); ?></p>
			</div>
			<?php
			if ( isset( $_GET['updated'] ) ) { // Input var ok.
				$this->admin_notices->show( 'updated', __( 'Settings Updated', 'wphb' ), 'success' );
			}

			$this->render_header();

			$this->render_inner_content();
			?>
			<div class="sui-footer">
				<?php
					printf(
						/* translators: %s - icon */
						esc_html__( 'Made with %s by WPMU DEV', 'wphb' ),
						'<i aria-hidden="true" class="sui-icon-heart"></i>'
					);
				?>
			</div>
		</div><!-- end container -->

		<script>
			// Avoid moving dashboard notice under h2
			var wpmuDash = document.getElementById( 'wpmu-install-dashboard' );
			if ( wpmuDash )
				wpmuDash.className = wpmuDash.className + " inline";

			jQuery( 'div.updated, div.error' ).addClass( 'inline' );
		</script>
		<?php
	}

	/**
	 * Render inner content.
	 */
	protected function render_inner_content() {
		$this->view( $this->slug . '-page' );
	}

	/**
	 * Return this menu page URL
	 *
	 * @return string
	 */
	public function get_page_url() {
		if ( is_multisite() && is_network_admin() ) {
			global $_parent_pages;

			if ( isset( $_parent_pages[ $this->slug ] ) ) {
				$parent_slug = $_parent_pages[ $this->slug ];
				if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
					$url = network_admin_url( add_query_arg( 'page', $this->slug, $parent_slug ) );
				} else {
					$url = network_admin_url( 'admin.php?page=' . $this->slug );
				}
			} else {
				$url = '';
			}

			$url = esc_url( $url );

			return $url;
		} else {
			return menu_page_url( $this->slug, false );
		}
	}

	/**
	 * Get the current screen tab
	 *
	 * @return string
	 */
	public function get_current_tab() {
		$tabs = $this->get_tabs();
		if ( isset( $_GET['view'] ) && array_key_exists( wp_unslash( $_GET['view'] ), $tabs ) ) { // Input var ok.
			return wp_unslash( $_GET['view'] ); // Input var ok.
		}

		if ( empty( $tabs ) ) {
			return false;
		}

		reset( $tabs );
		return key( $tabs );
	}

	/**
	 * Get the list of tabs for this screen
	 *
	 * @return array
	 */
	protected function get_tabs() {
		return apply_filters( 'wphb_admin_page_tabs_' . $this->slug, $this->tabs );
	}

	/**
	 * Display tabs navigation
	 */
	public function show_tabs() {
		$this->view( 'tabs', array(
			'tabs' => $this->get_tabs(),
		) );
	}

	/**
	 * Get a tab URL
	 *
	 * @param string $tab  Tab ID.
	 *
	 * @return string
	 */
	public function get_tab_url( $tab ) {
		$tabs = $this->get_tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			return '';
		}

		if ( is_multisite() && is_network_admin() ) {
			return network_admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
		} else {
			return admin_url( 'admin.php?page=' . $this->slug . '&view=' . $tab );
		}
	}

	/**
	 * Return the name of a tab
	 *
	 * @param string $tab  Tab ID.
	 *
	 * @return mixed|string
	 */
	public function get_tab_name( $tab ) {
		$tabs = $this->get_tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			return '';
		}

		return $tabs[ $tab ];
	}
}