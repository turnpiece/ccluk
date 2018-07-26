<?php
/**
 * Controller for Membership add-ons.
 *
 * Manages the activating and deactivating of Membership addons.
 *
 * @since  1.0.0
 *
 * @package Membership2
 * @subpackage Controller
 */
class MS_Controller_Addon extends MS_Controller {

	/**
	 * AJAX action constants.
	 *
	 * @since  1.0.0
	 *
	 * @var string
	 */
	const AJAX_ACTION_TOGGLE_ADDON = 'toggle_addon';

	/**
	 * Prepare the Add-on manager.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->add_action(
			'ms_controller_membership_setup_completed',
			'auto_setup_addons'
		);

		$this->add_ajax_action(
			self::AJAX_ACTION_TOGGLE_ADDON,
			'ajax_action_toggle_addon'
		);
	}

	/**
	 * Initialize the admin-side functions.
	 *
	 * @since  1.0.0
	 */
	public function admin_init() {
		$hook = MS_Controller_Plugin::admin_page_hook( 'addon' );

		$this->run_action( 'load-' . $hook, 'admin_addon_process' );
		$this->run_action( 'admin_print_scripts-' . $hook, 'enqueue_scripts' );
	}

	/**
	 * Handle Ajax toggle action.
	 *
	 * Related Action Hooks:
	 * - wp_ajax_toggle_gateway
	 *
	 * @since  1.0.0
	 */
	public function ajax_action_toggle_addon() {
		$msg = 0;

		if ( $this->verify_nonce()
			&& ! empty( $_POST['addon'] )
			&& $this->is_admin_user()
		) {
			$addon = array( $_POST['addon'] );

			if ( isset( $_POST['value'] ) ) {
				if ( mslib3()->is_true( $_POST['value'] ) ) {
					$msg = $this->save_addon( 'enable', $addon );
				} else {
					$msg = $this->save_addon( 'disable', $addon );
				}
			} else {
				$msg = $this->save_addon( 'toggle_activation', $addon );
			}

			// Some Add-ons require to flush WP rewrite rules.
			flush_rewrite_rules();
		}

		echo $msg;
		exit;
	}

	/**
	 * Auto setup addons when membership setup is completed.
	 *
	 * Related Action Hooks:
	 * - ms_controller_membership_setup_completed
	 *
	 * @since  1.0.0
	 */
	public function auto_setup_addons( $membership ) {
		$addon = MS_Factory::load( 'MS_Model_Addon' );
		$addon->auto_config( $membership );
	}

	/**
	 * Handles Add-on admin actions.
	 *
	 * Handles activation/deactivation toggles and bulk update actions, then saves the model.
	 *
	 * @since  1.0.0
	 */
	public function admin_addon_process() {
		/**
		 * Hook into the Addon request handler before processing.
		 *
		 * **Note:**
		 * This action uses the "raw" request objects which could lead to SQL injections / XSS.
		 * By hooking this action you need to take **responsibility** for filtering user input.
		 *
		 * @since  1.0.0
		 * @param object $this The MS_Controller_Addon object.
		 */
		do_action( 'ms_controller_addon_admin_addon_process', $this );

		$msg 	= 0;
		$fields = array( 'addon', 'action', 'action2' );

		if ( $this->verify_nonce( 'bulk' )
			&& self::validate_required( $fields )
		) {
			$action = -1 != $_POST['action'] ? $_POST['action'] : $_POST['action2'];
			$msg 	= $this->save_addon( $action, $_POST['addon'] );
			wp_safe_redirect(
				esc_url_raw( add_query_arg( array( 'msg' => $msg ) ) )
			);
			exit;
		}
	}


	/**
	 * Load and render the Add-on manager view.
	 *
	 * @since  1.0.0
	 */
	public function admin_page() {
		// Reload the add-on list.
		do_action( 'ms_model_addon_flush' );

		/**
		 * Create / Filter the Addon admin view.
		 *
		 * @since  1.0.0
		 * @param object $this The MS_Controller_Addon object.
		 */
		$view = MS_Factory::create( 'MS_View_Addon' );
		$data = array(
			'addon' => MS_Factory::load( 'MS_Model_Addon' ),
		);

		$view->data = apply_filters( 'ms_view_addon_data', $data );
		$view->render();
	}

	/**
	 * Call the model to save the addon settings.
	 *
	 * Saves activation/deactivation settings.
	 *
	 * @since  1.0.0
	 *
	 * @param string $action The action to perform on the add-on
	 * @param object[] $addon_types The add-on or add-ons types to update.
	 */
	public function save_addon( $action, $addon_types ) {
		if ( ! $this->is_admin_user() ) {
			return false;
		}

		$addon = MS_Factory::load( 'MS_Model_Addon' );

		foreach ( $addon_types as $addon_type ) {
			switch ( $action ) {
				case 'enable':
					$addon->enable( $addon_type );
					break;

				case 'disable':
					$addon->disable( $addon_type );
					break;

				case 'toggle_activation':
					$addon->toggle_activation( $addon_type );
					break;
			}
		}

		return true;
	}

	/**
	 * Load Add-on specific scripts.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {
		$data = array(
			'ms_init' => array( 'view_addons' ),
		);

		mslib3()->ui->data( 'ms_data', $data );
		wp_enqueue_script( 'ms-admin' );
	}
}