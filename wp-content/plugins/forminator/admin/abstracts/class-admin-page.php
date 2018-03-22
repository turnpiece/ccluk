<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_Admin_Page
 *
 * @since 1.0
 */
abstract class Forminator_Admin_Page {

	/**
	 * Current page ID
	 *
	 * @var number
	 */
	public $page_id = null;

	/**
	 * Current page slug
	 *
	 * @var string
	 */
	protected $page_slug = '';

	/**
	 * Path to view folder
	 *
	 * @var string
	 */
	protected $folder = '';

	/**
	 * All registered content boxes
	 *
	 * @var array
	 */
	protected $content_boxes = array();

	/**
	 * @since 1.0
	 * @param string $page_slug   Page slug.
	 * @param string $page_title  Page title.
	 * @param string $menu_title  Menu title.
	 * @param bool   $parent      Parent or not.
	 * @param bool   $render      Render the page.
	 */
	public function __construct(
			$page_slug,
			$folder = '',
			$page_title,
			$menu_title,
			$parent = false,
			$render = true
	) {
		$this->page_slug = $page_slug;
		$this->folder    = $folder;

		if ( ! $parent ) {
			$this->page_id = add_menu_page(
				$page_title,
				$menu_title,
				forminator_get_admin_cap(),
				$page_slug,
				$render ? array( $this, 'render' ) : null,
				'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzNDAiIGhlaWdodD0iMzAyIj48ZyBmaWxsPSIjODg4IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0yMjkuMjc4IDI0Ljg3OGwtMjAuMjEtMjAuMjE3di0uMDAzYy0zLjM1Ni0zLjMzOC04Ljc3OC0zLjMzNS0xMi4xMS4wMDNMNjQuNTIzIDEzNy4xMDFhOC41MDcgOC41MDcgMCAwIDAtMi41MDggNi4wNTJjMCAyLjI4OC44OTIgNC40NCAyLjUwOCA2LjA1MmwyMC4yMTEgMjAuMjE3YTguNTQgOC41NCAwIDAgMCA2LjA1MiAyLjUwNCA4LjU0IDguNTQgMCAwIDAgNi4wNTctMi41MDRsMTMyLjQzNS0xMzIuNDRjMy4zMzgtMy4zMzkgMy4zMzgtOC43NjYgMC0xMi4xMDRtMTA4LjIyMyAyMS43OTRsLTIwLjIxNy0yMC4yMTRjLTMuMzMyLTMuMzM4LTguNzcxLTMuMzM4LTEyLjEwMyAwTDEyOS41MjYgMjAyLjEwN2MtMy4zMzggMy4zMzgtMy4zMzggOC43NjggMCAxMi4xMDZsMjAuMjE3IDIwLjIxNGE4LjUyOCA4LjUyOCAwIDAgMCA2LjA1MiAyLjUwMmMyLjE5IDAgNC4zODUtLjgzMyA2LjA1LTIuNTAyTDMzNy41MDIgNTguNzc1YzMuMzMyLTMuMzM1IDMuMzMyLTguNzYyIDAtMTIuMTAzTTUxLjEyIDIxMC44NTZjLTEuNDQyLTEuNDM4LTMuNDY1LTIuMDM0LTUuNDc3LTEuNTQ0YTUuNzkgNS43OSAwIDAgMC00LjE4NSAzLjg1OWwtMTcuODkzIDU0Ljg2NGE1Ljc5NiA1Ljc5NiAwIDAgMCAxLjQxNiA1LjkyOSA1LjggNS44IDAgMCAwIDUuOTI5IDEuNDJsNTQuODcyLTE3Ljg5NXYtLjAwM2E1Ljc4NCA1Ljc4NCAwIDAgMCAzLjg1Ni00LjE4MiA1Ljc5MSA1Ljc5MSAwIDAgMC0xLjU1LTUuNDc1bC0zNi45NjctMzYuOTczem0yNDguNzIxLTc2Ljc4M2wtMzMuMTA2IDMzLjEwNmE4LjMxMSA4LjMxMSAwIDAgMC0yLjMzNSA0LjUxNmMtOS43OTYgNTUuNDA0LTU3LjczIDk1LjYxOC0xMTMuOTc5IDk1LjYxOC01LjYwNSAwLTExLjMwNi0uNDYzLTE3LjQxOS0xLjQxMy0yLjY1OC0uNDIzLTUuNDU1LjQ2Ni03LjM3MiAyLjM4M2wtMTQuODc5IDE0Ljg3OGMtMi4yMTggMi4yMjEtMy4wMzcgNS4zNTItMi4xOSA4LjM4MS44NDcgMy4wMzQgMy4xODIgNS4yOTEgNi4yNDEgNi4wMzhhMTUwLjY3NCAxNTAuNjc0IDAgMCAwIDM1LjYyIDQuMjcxYzgyLjk0NSAwIDE1MC40MjgtNjcuNDggMTUwLjQyOC0xNTAuNDI1IDAtMy40MzYtLjI0Ni02LjgxMy0uNDktMTAuMTg3bC0uNTE5LTcuMTY2ek0xNjIuNDU1IDEuNWwtMzUuNjQyIDM1LjYxNGMtMS4yNDQgMS4yMS0zLjAwNCAxLjY5OS00LjcxNyAyLjEwNC00Ni4zNjcgMTAuOTY4LTg2LjM4IDUzLjk3LTg3LjQ2NyAxMDcuOTUtLjExMiA1LjYwNC0uMDk0IDExLjE1My43NDkgMTcuMjgyLjM3NiAyLjY2NS0uNTYxIDUuNDQ2LTIuNTExIDcuMzNMMTcuNzMgMTg2LjM5NWMtMi4yNiAyLjE4LTQuOTE1IDMuMDQ4LTguNDE4IDIuMDQ0LTMuNTAzLTEuMDA0LTUuNC0zLjM3Mi02LjA5My02LjQ0My0yLjYzLTExLjY3OC0zLjU5My0yMy43MDItMy4xLTM1Ljg5M0MzLjQ3NiA2My4yMzUgNzAuMDY1IDIuNjcgMTQ3LjAyMyAxLjAwOGMzLjQzNi0uMDc0IDMuODU1LS4xMzQgNy4yMjUuMTdsOC4yMDguMzIyeiIvPjwvZz48L3N2Zz4='
				//'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIj48cGF0aCBmaWxsPSIjODg4IiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xNDkuNDQ2IDI2Ny42MmExMTcuNzYgMTE3Ljc2IDAgMCAxLTI3Ljg1LTMuMzQgNi42NDMgNi42NDMgMCAwIDEtNC44ODEtNC43MmMtLjY2MS0yLjM2OS0uMDIxLTQuODE4IDEuNzEzLTYuNTU0bDExLjYzNC0xMS42MzRjMS40OTgtMS40OTkgMy42ODYtMi4xOTMgNS43NjUtMS44NjMgNC43NzkuNzQzIDkuMjM1IDEuMTA1IDEzLjYxOSAxLjEwNSA0My45ODIgMCA4MS40NjItMzEuNDQ0IDg5LjEyMy03NC43NjVhNi40OTIgNi40OTIgMCAwIDEgMS44MjUtMy41M2wyNS44ODUtMjUuODg3LjQwNiA1LjYwM2MuMTkzIDIuNjQuMzg0IDUuMjguMzg0IDcuOTY1IDAgNjQuODU3LTUyLjc2NiAxMTcuNjItMTE3LjYyMyAxMTcuNjJtLTQ2LjYzLTEwMS41OWE2LjY4NCA2LjY4NCAwIDAgMS00LjczMi0xLjk1OGwtMTUuODAyLTE1LjgwOGE2LjY0IDYuNjQgMCAwIDEtMS45NjItNC43MzJjMC0xLjc4Ny42OTctMy40NjggMS45NjItNC43MzJsMTAzLjU1LTEwMy41NTdjMi42MDctMi42MSA2Ljg0Ny0yLjYxMiA5LjQ3LS4wMDJ2LjAwMmwxNS44MDMgMTUuODA3YTYuNyA2LjcgMCAwIDEgMCA5LjQ2NUwxMDcuNTUzIDE2NC4wNzJhNi42ODYgNi42ODYgMCAwIDEtNC43MzcgMS45NThtLS44OTcgNjMuNjNhNC41MjQgNC41MjQgMCAwIDEtMy4wMTUgMy4yN3YuMDAybC00Mi45MDUgMTMuOTkzYTQuNTI4IDQuNTI4IDAgMCAxLTQuNjM2LTEuMTExIDQuNTI4IDQuNTI4IDAgMCAxLTEuMTA3LTQuNjM2bDEzLjk5LTQyLjg5OWE0LjUyMiA0LjUyMiAwIDAgMSAzLjI3My0zLjAxNmMxLjU3Mi0uMzg0IDMuMTU0LjA4MiA0LjI4MiAxLjIwNmwyOC45MDcgMjguOTFhNC41MjggNC41MjggMCAwIDEgMS4yMSA0LjI4MW0tNjEtNTAuNDM0YTYuNzk1IDYuNzk1IDAgMCAxLTEuOTIxLS4yNzljLTIuMzg4LS43MS00LjEyMy0yLjU4Ni00LjYzNi01LjAxNS0xLjY3OC03LjkzOS0yLjUzMi0xNS45OS0yLjUzMi0yMy45MzIgMC02NC44NTcgNTIuNzYtMTE3LjYyIDExNy42MTctMTE3LjYyLjg5NCAwIDEuNzc0LjA2IDIuNjUuMTIuNTQ5LjAzNyAxLjA5OC4wNzYgMS42NS4wOTZsNS4xMjYuMTgxLTI3LjQyIDI3LjQyYTYuNTk3IDYuNTk3IDAgMCAxLTMuMTE2IDEuNzM2aC0uMDA1Qzg3LjQxNCA3MS43MSA1OC44MzQgMTA3LjkyNSA1OC44MzQgMTUwYzAgMy4wNTUuMjA2IDYuMjgzLjYyNCA5Ljg3MWE2LjY1OCA2LjY1OCAwIDAgMS0xLjkwNCA1LjUwM2wtMTEuODg3IDExLjg4N2E2LjcxNSA2LjcxNSAwIDAgMS00Ljc1IDEuOTY1bTI0NS4yNTgtOTIuMTJMMTU4LjM3OSAyMTQuOWE2LjY2OCA2LjY2OCAwIDAgMS00LjczMSAxLjk1NiA2LjY2OCA2LjY2OCAwIDAgMS00LjczMy0xLjk1NmwtMTUuODA4LTE1LjgwNWE2LjcgNi43IDAgMCAxIDAtOS40NjZMMjY2LjgwNiA1NS45MzNDMjM5LjMxIDIxLjgzNCAxOTcuMjE1IDAgMTUwIDAgNjcuMTU3IDAgMCA2Ny4xNTcgMCAxNTBjMCA4Mi44NDIgNjcuMTU3IDE1MCAxNTAgMTUwczE1MC02Ny4xNTggMTUwLTE1MGMwLTIyLjQ2Ny00Ljk3My00My43NjItMTMuODI1LTYyLjg5NCIvPjwvc3ZnPg=='
			);
		} else {
			$this->page_id = add_submenu_page(
				$parent,
				$page_title,
				$menu_title,
				forminator_get_admin_cap(),
				$page_slug,
				$render ? array( $this, 'render' ) : null
			);
		}

		if ( $render ) {
			$this->render_page_hooks();
		}

		$this->init();

	}

	/**
	 * Use that method instead of __construct
	 *
	 * @since 1.0
	 */
	public function init() {}

	/**
	 * Hooks before content render
	 *
	 * @since 1.0
	 */
	public function render_page_hooks() {
		add_action( 'load-' . $this->page_id, array( $this, 'register_content_boxes' ) );
		add_action( 'load-' . $this->page_id, array( $this, 'before_render' ) );
		add_action( 'load-' . $this->page_id, array( $this, 'trigger_before_render_action' ) );
		add_filter( 'load-' . $this->page_id, array( $this, 'add_page_hooks' ) );
	}

	/**
	 * Return page slug
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_the_slug() {
		return $this->page_slug;
	}

	/**
	 * Used to register content boxes for the page
	 *
	 * @since 1.0
	 */
	public function register_content_boxes() {}

	/**
	 * Called when page is loaded and content not rendered yet
	 *
	 * @since 1.0
	 */
	public function before_render() {}

	/**
	 * Trigger an action before this screen is rendered
	 *
	 * @since 1.0
	 */
	public function trigger_before_render_action() {
		do_action( 'forminator_loaded_admin_page_' . $this->get_the_slug() );
	}

	/**
	 * Add page screen hooks
	 *
	 * @since 1.0
	 */
	public function add_page_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'init', array( $this, 'init_scripts' ) );
	}

	/**
	 * Add page screen hooks
	 *
	 * @since 1.0
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		// Load jquery ui
		forminator_admin_jquery_ui();

		// Load admin fonts
		forminator_admin_enqueue_fonts( FORMINATOR_VERSION );

		// Load admin styles
		forminator_admin_enqueue_styles( FORMINATOR_VERSION );

		$forminator_data = new Forminator_Admin_Data();
		$forminator_l10n = new Forminator_Admin_l10n();

		// Load admin scripts
		forminator_admin_enqueue_scripts(
			FORMINATOR_VERSION,
			$forminator_data->get_options_data(),
			$forminator_l10n->get_l10n_strings()
		);

		// Load front scripts for preview_form
		forminator_print_front_scripts( FORMINATOR_VERSION );
	}

	/**
	 * Init Admin scripts
	 *
	 * @since 1.0
	 * @param $hook
	 */
	public function init_scripts( $hook ) {
		// Init jquery ui
		forminator_admin_jquery_ui_init();
	}

	/**
	 * Render page header
	 *
	 * @since 1.0
	 */
	protected function render_header() { ?>
		<header id="wpmudev-header">
			<?php
			if($this->template_exists( $this->folder . '/header' )) {
				$this->template( $this->folder . '/header' );
			} else { ?>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php } ?>

		</header>
	<?php }

	/**
	 * Render page footer
	 *
	 * @since 1.0
	 */
	protected function render_footer() { ?>
		<footer id="wpmudev-footer">
			<?php
			if($this->template_exists($this->folder . '/footer')) {
				$this->template( $this->folder . '/footer' );
			} else { ?>
			<p><?php printf( __( 'Made with %s by WPMU DEV', Forminator::DOMAIN ), '<span class="wpdui-icon wpdui-icon-heart"></span>' ); ?></p>
			<?php } ?>
		</footer>

	<?php }

	/**
	 * Render page container
	 *
	 * @since 1.0
	 */
	public function render() {
		?>
		<main id="wpmudev-main" class="wpmudev-ui <?php echo 'wpmudev-forminator-' . $this->page_slug; ?>">
			<?php
			$this->render_header();

			$this->render_page_content();

			$this->render_footer();
			?>
		</main>
		<?php
	}

	/**
	 * Render actual page template
	 *
	 * @since 1.0
	 */
	protected function render_page_content() {
		$this->template( $this->folder . '/content' );
	}

	/**
	 * Load an admin template
	 *
	 * @since 1.0
	 * @param $path
	 * @param array $args
	 * @param bool $echo
	 * @return string
	 */
	public function template( $path, $args = array(), $echo = true ) {
		$file = forminator_plugin_dir() . "admin/views/$path.php";
		$content = '';

		if ( is_file ( $file ) ) {
			ob_start();

			if ( isset( $args['id'] ) ) {
				$args['template_class'] = $args['class'];
				$args['template_id'] = $args['id'];
			}

			extract( $args );

			include( $file );

			$content = ob_get_clean();
		}

		if ( ! $echo )
			return $content;

		echo $content;
	}

	/**
	 * Check if template exist
	 *
	 * @since 1.0
	 * @param $path
	 * @return bool
	 */
	protected function template_exists( $path ) {
		$file = forminator_plugin_dir() . "admin/views/$path.php";
		return is_file ( $file );
	}

	/**
	 * Add a box to the page
	 *
	 * @since 1.0
	 * @param $box_id
	 * @param $title
	 * @param string $header_callback
	 * @param string $main_callback
	 * @param string $footer_callback
	 * @param array $args
	 */
	public function add_box(
		$box_id,
		$title,
		$class,
		$header_callback = '',
		$main_callback = '',
		$footer_callback = '',
		$args = array()
	) {
		$args = wp_parse_args( $args, array() );

		if ( ! isset( $this->content_boxes[ $this->page_slug ] ) ) {
			$this->content_boxes[ $this->page_slug ] = array();
		}

		$box = array('id' => $box_id,
			'title' => $title,
			'class' => $class,
			'header_callback' => $header_callback,
			'main_callback' => $main_callback,
			'footer_callback' => $footer_callback,
			'args' => $args
		);

		$box = apply_filters(
			'forminator_add_box',
			$box,
			$this->page_slug,
			$this->page_id
		);

		$box = apply_filters(
			'forminator_add_box_' . $box_id,
			$box,
			$this->page_slug,
			$this->page_id
		);

		if ( $box ) {
			$this->content_boxes[ $this->page_slug ][ $box_id ] = $box;
		}
	}

	/**
	 * Check if content box exist
	 *
	 * @since 1.0
	 * @param $box_id
	 * @return bool
	 */
	protected function box_exist( $box_id ) {
		return ! empty( $this->content_boxes[ $this->page_slug ][ $box_id ] );
	}

	/**
	 * Print content box
	 *
	 * @since 1.0
	 * @param $box_id
	 */
	protected function do_content_box( $box_id ) {
		if ( ! isset( $this->content_boxes[ $this->page_slug ][ $box_id ] ) )
			return;

		do_action_ref_array( 'forminator_admin_print_content_box' . $this->page_slug, array( &$this ) );

		$box_data = $this->content_boxes[ $this->page_slug ][ $box_id ];
		$args = array(
			'title' 		  => $box_data['title'],
			'id' 		 	  => $box_id,
			'class'	 		  => $box_data['class'],
			'header_callback' => $box_data['header_callback'],
			'main_callback'   => $box_data['main_callback'],
			'footer_callback' => $box_data['footer_callback'],
			'args' => $box_data['args']
		);

		$this->template( 'boxes/content-box', $args );
	}

	/**
	 * Print popup box
	 *
	 * @since 1.0
	 * @param $box_id
	 */
	protected function do_popup_box( $box_id ) {
		if ( ! isset( $this->content_boxes[ $this->page_slug ][ $box_id ] ) )
			return;

		do_action_ref_array( 'forminator_admin_print_popup_box' . $this->page_slug, array( &$this ) );

		$box_data = $this->content_boxes[ $this->page_slug ][ $box_id ];
		$args = array(
			'title' 		  => $box_data['title'],
			'id' 		 	  => $box_id,
			'class'	 		  => $box_data['class'],
			'header_callback' => $box_data['header_callback'],
			'main_callback'   => $box_data['main_callback'],
			'footer_callback' => $box_data['footer_callback'],
			'args' => $box_data['args']
		);

		$this->template( 'boxes/popup-box', $args );
	}
}