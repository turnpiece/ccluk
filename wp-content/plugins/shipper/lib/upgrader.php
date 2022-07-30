<?php
/**
 * Shipper up-grader file
 *
 * @package shipper
 */

/**
 * Class Shipper_Version_Upgrader
 *
 * @since 1.2.6
 */
class Shipper_Version_Upgrader {
	const SHIPPER_VERSION_KEY = 'shipper_version';

	/**
	 * Shipper last version holder
	 *
	 * @var string|null
	 */
	public $last_version = null;

	/**
	 * Current shipper version holder
	 *
	 * @var string|null
	 */
	public $current_version = null;

	/**
	 * Shipper_Task_Api_Info_Set Instance
	 *
	 * @var Shipper_Task_Api_Info_Set|null
	 */
	public $info_set = null;

	/**
	 * Shipper_Model_System instance
	 *
	 * @var Shipper_Model_System|null
	 */
	public $system = null;

	/**
	 * Shipper_Version_Upgrader constructor.
	 */
	public function __construct() {
		$this->current_version = SHIPPER_VERSION;
		$this->last_version    = get_site_option( self::SHIPPER_VERSION_KEY );
	}

	/**
	 * Set API Info Task
	 *
	 * @param Shipper_Task_Api_Info_Set $info_set info_set instance.
	 */
	public function set_info( Shipper_Task_Api_Info_Set $info_set ) {
		$this->info_set = $info_set;
	}

	/**
	 * Set System Model
	 *
	 * @param Shipper_Model_System $system system instance.
	 */
	public function set_system( Shipper_Model_System $system ) {
		$this->system = $system;
	}

	/**
	 * Update the version
	 *
	 * @return bool
	 */
	public function run() {
		update_site_option( self::SHIPPER_VERSION_KEY, $this->current_version );

		return $this->info_set->apply( $this->system->get_data() );
	}

	/**
	 * Check whether the upgrader should run or not
	 *
	 * @return bool
	 */
	public function should_run() {
		return $this->current_version !== $this->last_version;
	}

	/**
	 * Get the upgrader instance.
	 *
	 * @return self
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

add_action(
	'admin_init',
	function() {
		$upgrader = Shipper_Version_Upgrader::init();

		if ( $upgrader->should_run() ) {
			$upgrader->set_info( new Shipper_Task_Api_Info_Set() );
			$upgrader->set_system( new Shipper_Model_System() );
			$upgrader->run();
		}
	}
);