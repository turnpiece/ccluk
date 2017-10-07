<?php

/**
 * Handles plugin installation.
 */
class AgmPluginInstaller {
	/**
	 * Default settings of the plugin.
	 * @var array
	 */
	private $defaults = array(
		'height'        => 300,
		'width'         => 300,
		'map_type'      => 'ROADMAP',
		'image_size'    => 'small',
		'image_limit'   => 10,
		'map_alignment' => 'left',
		'zoom'          => 1,
		'units'         => 'METRIC',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->model = new AgmMapModel();
	}

	/**
	 * Entry method.
	 *
	 * Handles Plugin installation.
	 *
	 * @access public
	 * @static
	 */
	static public function install() {
		$me = new AgmPluginInstaller();
		if ( ! $me->has_database_table() ) {
			$me->create_database_table();
			$me->set_default_options();
		}
	}

	/**
	 * Performs a quick check for plugin install state.
	 * Also updates plugin options as needed. This handles minor updates
	 * (i.e. no database changes).
	 *
	 * @access public
	 * @static
	 */
	public static function check() {
		// Check if Map-Options already exist in the DB.
		$agm_settings = get_option( 'agm_google_maps', false );

		if ( is_array( $agm_settings ) ) {
			self::check_and_update_options( $agm_settings );
		} else {
			self::install();
		}
	}

	/**
	 * Checks to see if we already have a table.
	 *
	 * @access public
	 * @return bool True if we do, false if we need to create it.
	 */
	public function has_database_table() {
		global $wpdb;
		$Res = null;

		if ( null === $Res ) {
			$table = $this->model->get_table_name();
			$Res = $wpdb->get_var( "show tables like '{$table}'" ) == $table;
		}

		return $Res;
	}

	/**
	 * Actually creates the database table.
	 *
	 * @access private
	 */
	private function create_database_table() {
		global $wpdb;

		$table = $this->model->get_table_name();
		$sql = "CREATE TABLE {$table} (
			id INT(10) NOT NULL AUTO_INCREMENT,
			title VARCHAR(50) NOT NULL,
			post_ids TEXT NOT NULL,
			markers TEXT NOT NULL,
			options TEXT NOT NULL,
			UNIQUE KEY id (id)
		)";
		// Setup charset and collation
		if ( ! empty( $wpdb->charset ) ) {
			$sql .= " DEFAULT CHARACTER SET {$wpdb->charset}";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$sql .= " COLLATE {$wpdb->collate}";
		}

		// Do install
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( "{$sql};" );
	}

	/**
	 * (Re)sets Plugin options to defaults.
	 *
	 * @access public
	 */
	public function set_default_options() {
		update_option( 'agm_google_maps', $this->defaults );
	}

	/**
	 * Checks for new plugin options and adds them as needed.
	 *
	 * @access private
	 * @static
	 */
	static private function check_and_update_options( $opts ) {
		$me = new AgmPluginInstaller();
		$res = array_merge( $me->defaults, $opts );
		update_option( 'agm_google_maps', $res );
	}

};