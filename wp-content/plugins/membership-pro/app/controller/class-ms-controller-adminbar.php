<?php
/**
 * Controller to add functionality to the admin toolbar.
 *
 * Used extensively for simulating memberships and content access.
 * Adds ability for Membership users to test the behaviour for their end-users.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Controller_Adminbar extends MS_Controller {

	/**
	 * Details on current simulation mode
	 *
	 * @since  1.0.0
	 *
	 * @var MS_Model_Simulate
	 */
	protected $simulate = null;

	/**
	 * Prepare the Admin Bar simulator.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->run_action( 'init', 'init_adminbar', 1 );
	}

	/**
	 * Returns the URL to start/switch simulation of a specific membership.
	 *
	 * @since  1.0.0
	 * @param  int $id Membership-ID
	 * @return string URL
	 */
	static public function get_simulation_url( $id ) {
		$link_url = admin_url(
			'?action=ms_simulate&membership_id=' . $id,
			is_ssl() ? 'https' : 'http'
		);
		$link_url = wp_nonce_url(
			$link_url,
			'ms_simulate'
		);

		return $link_url;
	}

	/**
	 * Returns the URL to end simulation.
	 *
	 * @since  1.0.0
	 * @return string URL
	 */
	static public function get_simulation_exit_url() {
		return self::get_simulation_url( 0 );
	}

	/**
	 * Initialize the Admin-Bar after we have determined the current user.
	 *
	 * @since  1.0.0
	 */
	public function init_adminbar() {
		$this->simulate = MS_Factory::load( 'MS_Model_Simulate' );

		// Hide WP toolbar in front end to not admin users
		if ( ! $this->is_admin_user() && MS_Plugin::instance()->settings->hide_admin_bar ) {
			add_filter( 'show_admin_bar', '__return_false' );
			$this->add_action( 'wp_before_admin_bar_render', 'customize_toolbar_front', 999 );
		}

		// Customize WP toolbar for admin users
		if ( $this->is_admin_user() ) {
			$this->add_action( 'wp_before_admin_bar_render', 'customize_toolbar', 999 );
			$this->add_action( 'add_admin_bar_menus', 'admin_bar_manager' );
			$this->add_action( 'admin_enqueue_scripts', 'enqueue_scripts' );
			$this->add_action( 'wp_enqueue_scripts', 'enqueue_scripts' );
		}
		$this->add_action( 'admin_head', 'custom_adminbar_styles' );
		$this->add_action( 'wp_head', 'custom_adminbar_styles' );

	}

	/**
	 * Customize the Admin Toolbar.
	 *
	 * Related Action Hooks:
	 * - wp_before_admin_bar_render
	 *
	 * @since  1.0.0
	 */
	public function customize_toolbar() {
		if ( MS_Model_Member::is_admin_user()
			&& MS_Plugin::is_enabled()
			&& ! is_network_admin()
		) {
			if ( MS_Model_Simulate::can_simulate() ) {
				if ( $this->simulate->is_simulating() ) {
					$this->add_detail_nodes();
				} else {
					$this->add_test_membership_node();
				}
			}

			if ( MS_Helper_Cache::is_query_cache_enabled() ) {
				$this->add_cache_notice();
			}

		} else if ( ! MS_Plugin::is_enabled() ) {
			$this->add_unprotected_node();
		}
	}

	/**
	 * Process GET and POST requests
	 *
	 * Related Action Hooks:
	 * - add_admin_bar_menus
	 *
	 * @since  1.0.0
	 */
	public function admin_bar_manager() {
		$redirect = false;

		mslib3()->array->equip_get( 'membership_id' );

		if ( $this->verify_nonce( 'ms_simulate', 'any' ) ) {
			/*
			 * Check for memberhship id simulation GET request.
			 * - Any valid Membership_id will simulate that membership.
			 * - An ID of "0" will exit simulation mode.
			 */
			$new_id = absint( $_REQUEST['membership_id'] );

			if ( $new_id != $this->simulate->membership_id ) {
				// Change the simulated membership.
				$this->simulate->membership_id = $new_id;

				$target = wp_get_referer();
				if ( $this->simulate->is_simulating()
					&& false !== strpos( $target, 'wp-admin' )
				) {
					$redirect = admin_url();
				}
			}

			if ( ! empty( $_POST['simulate_date'] ) ) {
				// Change the simulation date.
				$this->simulate->date = $_POST['simulate_date'];
			}

			$this->simulate->save();

			if ( ! $redirect ) {
				if ( ! empty( $_GET['redirect_to'] ) ) {
					$redirect = $_GET['redirect_to'];
				} else {
					$redirect = wp_get_referer();
				}
			}

			if ( ! $redirect ) {
				$redirect = mslib3()->net->current_url();
			}
		}

		if ( $redirect ) {
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Remove all Admin Bar nodes.
	 *
	 * @since  1.0.0
	 *
	 * @param string[] $exclude The node IDs to exclude.
	 */
	private function remove_admin_bar_nodes( $exclude = array() ) {
		global $wp_admin_bar;

		$nodes 		= $wp_admin_bar->get_nodes();
		$exclude 	= apply_filters(
			'ms_controller_adminbar_remove_admin_bar_nodes_exclude',
			$exclude,
			$nodes
		);

		if ( is_array( $nodes ) ) {
			foreach ( $nodes as $node ) {
				if ( is_array( $exclude ) && ! in_array( $node->id, $exclude ) ) {
					$wp_admin_bar->remove_node( $node->id );
				}
			}
		}

		do_action(
			'ms_controller_adminbar_remove_admin_bar_nodes',
			$nodes,
			$exclude
		);
	}

	/**
	 * Add 'Test Memberships' node.
	 *
	 * @since  1.0.0
	 *
	 */
	private function add_test_membership_node() {
		global $wp_admin_bar;

		$base_id = MS_Model_Membership::get_base()->id;

		if ( $base_id ) {
			$link_url = self::get_simulation_url( $base_id );

			$wp_admin_bar->add_node(
				apply_filters(
					'ms_controller_adminbar_add_test_membership_node',
					array(
						'id'     => 'ms-test-memberships',
						'title'  => __( 'Test Memberships', 'membership2' ),
						'href'   => $link_url,
						'meta'   => array(
							'class'    => 'ms-test-memberships',
							'title'    => __( 'Membership Simulation Menu', 'membership2' ),
							'tabindex' => '1',
						),
					)
				)
			);
		}
	}

	/**
	 * Add 'Unprotected' node.
	 *
	 * @since  1.0.0
	 *
	 */
	private function add_unprotected_node() {
		global $wp_admin_bar;

		if ( MS_Plugin::is_enabled() ) { return; }
		if ( MS_Plugin::is_wizard() ) { return; }

		$link_url = MS_Controller_Plugin::get_admin_url( 'settings' );

		$wp_admin_bar->add_node(
			apply_filters(
				'ms_controller_adminbar_add_unprotected_node',
				array(
					'id'     => 'ms-unprotected',
					'title'  => __( 'Content Protection is disabled', 'membership2' ),
					'href'   => $link_url,
					'meta'   => array(
						'class'    => 'ms-unprotected',
						'title'    => __( 'Content of this site is unprotected', 'membership2' ),
						'tabindex' => '1',
					),
				)
			)
		);
	}

	/**
	 * Add cache notice on admin bar
	 *
	 * @since 1.1.3
	 */
	private function add_cache_notice() {
		global $wp_admin_bar;

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'membership-cache-notice',
				'title' => __( 'Membership Cache Enabled', 'membership2' ),
				'href'  => '#',
				'meta'   => array(
					'class'    => 'membership-cache-notice',
					'title'    => __( 'Membership is enabled to cache data', 'membership2' ),
					'tabindex' => '1',
				)
			)
		);
	}

	/**
	 * Add membership description nodes.
	 *
	 * @since  1.0.0
	 *
	 */
	private function add_detail_nodes() {
		global $wp_admin_bar;

		/**
		 * Info menu is currently only available on the front-end.
		 *
		 * @todo add information also for admin side (Admin-Protection/Capabilities)
		 */
		if ( is_admin() ) { return; }

		if ( ! $this->simulate->is_simulating() ) { return; }

		$membership = MS_Factory::load(
			'MS_Model_Membership',
			$this->simulate->membership_id
		);

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'membership-details',
				'title' => __( 'Protection Details', 'membership2' ),
				'href'  => '#',
			)
		);

		$details = mslib3()->session->get( 'ms-access' );
		$parent1 = '';
		$parent2 = '';

		foreach ( $details as $req_ind => $request ) {
			if ( ! is_array( $request ) ) { continue; }
			$parent1 = 'membership-details-' . $req_ind;

			$url = explode( '?', $request['url'] );
			$url = str_replace( site_url(), '', reset( $url ) );

			$wp_admin_bar->add_node(
				array(
					'id'     => $parent1,
					'parent' => 'membership-details',
					'title'  => (1 + $req_ind) . ': ' . $url,
					'href'   => $request['url'],
				)
			);

			if ( isset( $request['reason'] ) ) {
				foreach ( $request['reason'] as $key => $item ) {
					if ( is_array( $item ) ) {
						foreach ( $item as $child => $note ) {
							$wp_admin_bar->add_node(
								array(
									'id'     => $parent2 . '-' . $child,
									'parent' => $parent2,
									'title'  => $note,
								)
							);
						}
					} else {
						$parent2 = $parent1 . '-' . $key;
						$wp_admin_bar->add_node(
							array(
								'id'     => $parent2,
								'parent' => $parent1,
								'title'  => $item,
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Customize the Admin Toolbar for front end users.
	 *
	 * Related Action Hooks:
	 * - wp_before_admin_bar_render
	 *
	 * @since  1.0.0
	 *
	 */
	public function customize_toolbar_front() {
		if ( ! $this->is_admin_user() ) {
			$this->remove_admin_bar_nodes();
		}
	}

	/**
	 * Enqueues necessary scripts and styles.
	 *
	 * Related Action Hooks:
	 * - wp_enqueue_scripts
	 * - admin_enqueue_scripts
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {
		$data = array(
			'ms_init' => array( 'controller_adminbar' ),
			'switching_text' => __( 'Switching...', 'membership2' ),
		);

		mslib3()->ui->add( 'select' );
		mslib3()->ui->data( 'ms_data', $data );

		wp_enqueue_script( 'ms-admin' );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_style( 'ms-public' );
	}

	/**
	 * Add in-line css to admin head
	 *
	 * @since 1.1.3
	 *
	 * @return string
	 */
	public function custom_adminbar_styles() {
		?>
		<style type="text/css">
		#wpadminbar .ms-test-memberships{
			color:#f0f0f0;
			background-color: #0073aa;
			font-size: 10px !important;
		}
		</style>
		<?php
	}

}