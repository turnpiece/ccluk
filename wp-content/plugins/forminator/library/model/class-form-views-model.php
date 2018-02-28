<?php
/**
 * Form Views
 * Handles conversions and views of the different forms
 */
class Forminator_Form_Views_Model {

	/**
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;


	/**
	 * Plugin instance
	 *
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Return the plugin instance
	 *
	 * @since 1.0
	 * @return Forminator_Form_Views_Model
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Forminator_Form_Views_Model constructor.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		$this->table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_VIEWS );
	}

	/**
	 * Save conversion
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param int $page_id - the page id
	 * @param string $ip - the ip
	 */
	public function save_view( $form_id, $page_id, $ip ) {
		global $wpdb;
		$sql 		= "SELECT `view_id` FROM {$this->table_name} WHERE `form_id` = %d AND `page_id` = %d AND `ip` = %s AND `date_created` BETWEEN DATE_SUB(utc_timestamp(), INTERVAL 1 DAY) AND utc_timestamp()";
		$view_id 	= $wpdb->get_var( $wpdb->prepare( $sql, $form_id, $page_id, $ip ) );
		if ( $view_id ) {
			$this->_update( $view_id, $wpdb );
		} else {
			$this->_save( $form_id, $page_id, $ip, $wpdb );
		}
	}

	/**
	 * Save Data to database
	 *
	 * @param int $form_id - the form id
	 * @param int $page_id - the page id
	 * @param string $ip - the user ip
	 * @param bool|object $db - the wp db object
	 */
	private function _save( $form_id, $page_id, $ip, $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}

		$db->insert( $this->table_name, array(
			'form_id'     	=> $form_id,
			'page_id'    	=> $page_id,
			'ip'    		=> $ip,
			'date_created'  => date_i18n( 'Y-m-d H:i:s' )
		) );
	}

	/**
	 * Update view
	 *
	 * @since 1.0
	 * @param int $id - entry id
	 * @param bool|object $db - the wp db object
	 *
	 */
	private function _update( $id, $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$db->query( $db->prepare( "UPDATE {$this->table_name} SET `count` = `count`+1, `date_updated` = now() WHERE `view_id` = %d", $id ) );
	}

	/**
	 * Count views
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @return int - totol views based on parameters
	 */
	public function count_views( $form_id, $starting_date = null, $ending_date = null ) {
		return $this->_count( $form_id, $starting_date, $ending_date );
	}

	/**
	 * Delete views by form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 */
	public function delete_by_form( $form_id ) {
		global $wpdb;
		$sql = "DELETE FROM {$this->table_name} WHERE `form_id` = %d";
		$wpdb->query( $wpdb->prepare( $sql, $form_id ) );
	}

	/**
	 * Get the top converting form
	 *
	 * @since 1.0
	 * @param string $form_type - the form type (Forminator_Base_Form_Model - post_type)
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @see _get_top_converting
	 *
	 * @return false|object { form_id => id, conversion => 0 }
	 */
	public function top_converting_form( $form_type, $starting_date = null, $ending_date = null ) {
		return $this->_get_top_converting( $form_type, $starting_date, $ending_date );
	}

	/**
	 * Get the most popular form
	 *
	 * @since 1.0
	 * @param string $form_type - the form type (Forminator_Base_Form_Model - post_type)
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @see _get_top_converting
	 *
	 * @return false|object { form_id => id, views => 0 }
	 */
	public function most_popular_form( $form_type, $starting_date = null, $ending_date = null ) {
		return $this->_get_most_popular( $form_type, $starting_date, $ending_date );
	}

	/**
	 * Count data
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @return int - totol counts based on parameters
	 */
	private function _count( $form_id, $starting_date = null, $ending_date = null ) {
		global $wpdb;
		$date_query = $this->_generate_date_query( $wpdb, $starting_date, $ending_date );
		$sql 		= "SELECT SUM(`count`) FROM {$this->table_name} WHERE `form_id` = %d $date_query";
		$counts 	= $wpdb->get_var( $wpdb->prepare( $sql, $form_id ) );

		if ( $counts ) {
			return $counts;
		}

		return 0;
	}

	/**
	 * Generate the date query
	 *
	 * @since 1.0
	 * @param object $wpdb - the WordPress database object
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @return string $date_query
	 */
	private function _generate_date_query( $wpdb, $starting_date = null, $ending_date = null, $prefix='', $clause = 'AND' ) {
		$date_query = "";
		if ( !is_null( $starting_date ) && !is_null( $ending_date ) && !empty( $starting_date ) && !empty( $ending_date ) ) {
			$date_query = $wpdb->prepare( "$clause DATE_FORMAT($prefix`date_created`, '%d-%m-%Y') >= ? AND DATE_FORMAT($prefix`date_created`, '%d-%m-%Y') <= ?", $starting_date, $ending_date );
		} else {
			if ( !is_null( $starting_date ) && !empty( $starting_date ) ) {
				$date_query = $wpdb->prepare( "$clause DATE_FORMAT($prefix`date_created`, '%d-%m-%Y') >= ?", $starting_date );
			} else if ( !is_null( $ending_date ) && !empty( $ending_date ) ) {
				$date_query = $wpdb->prepare( "$clause DATE_FORMAT($prefix`date_created`, '%d-%m-%Y') <= ?", $starting_date );
			}
		}

		return $date_query;
	}

	/**
	 * Get top converting form by type
	 *
	 * @since 1.0
	 * @param string $form_type - the form type
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @return false|object { form_id => id, conversion => 0 }
	 */
	private function _get_top_converting( $form_type = null, $starting_date = null, $ending_date = null ) {
		global $wpdb;
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );

		if ( ! is_null( $form_type ) && ! empty( $form_type ) ) {
			$date_query  = $this->_generate_date_query( $wpdb, $starting_date, $ending_date, 'd.' );
			$sql_views   = "SELECT d.form_id, SUM(d.`count`) AS views FROM {$this->table_name} d LEFT JOIN {$wpdb->posts}  p ON (p.`ID` = d.`form_id`) WHERE p.post_type = %s $date_query GROUP BY d.`form_id`";
			$sql_entries = "SELECT e.form_id, COUNT(1) AS entries FROM $entry_table_name e LEFT JOIN  {$wpdb->posts} p ON (p.`ID` = e.`form_id`) WHERE p.post_type = %s GROUP BY e.`form_id`";
			$sql         = "SELECT v.form_id, ROUND( (( s.entries *100 )/ v.views), 1 ) AS conversion FROM ($sql_views) v LEFT JOIN ($sql_entries) s ON (s.form_id = v.form_id) WHERE v.views > 0 ORDER BY conversion DESC LIMIT 0, 1";

			$sql = $wpdb->prepare( $sql, $form_type, $form_type );
		} else {
			$date_query  = $this->_generate_date_query( $wpdb, $starting_date, $ending_date, 'd.', 'WHERE' );
			$sql_views   = "SELECT d.form_id, SUM(d.`count`) AS views FROM {$this->table_name} d $date_query GROUP BY d.`form_id`";
			$sql_entries = "SELECT e.form_id, COUNT(1) AS entries FROM $entry_table_name e GROUP BY e.`form_id`";
			$sql         = "SELECT v.form_id, ROUND( (( s.entries *100 )/ v.views), 1 ) AS conversion FROM ($sql_views) v LEFT JOIN ($sql_entries) s ON (s.form_id = v.form_id) WHERE v.views > 0 ORDER BY conversion DESC LIMIT 0, 1";
		}

		$results = $wpdb->get_results( $sql );

		if ( $results && is_array( $results ) && count( $results ) > 0 ) {
			return $results[0];
		}

		return false;
	}


	/**
	 * Get most popular form by type
	 *
	 * @since 1.0
	 * @param string $form_type - the form type
	 * @param string $starting_date - the start date (dd-mm-yyy)
	 * @param string $ending_date - the end date (dd-mm-yyy)
	 *
	 * @return false|object { form_id => id, views => 0 }
	 */
	private function _get_most_popular( $form_type = null, $starting_date = null, $ending_date = null ) {
		global $wpdb;

		if ( !is_null( $form_type ) && !empty( $form_type ) ) {
			$date_query = $this->_generate_date_query( $wpdb, $starting_date, $ending_date, 'd.' );
			$sql 		= "SELECT d.`form_id`, SUM(d.`count`) as views FROM  {$this->table_name} d LEFT JOIN {$wpdb->posts} p ON (p.`ID` = d.`form_id`) WHERE p.post_type = %s $date_query GROUP BY d.`form_id` ORDER BY views DESC LIMIT 0,1";
			$sql 		= $wpdb->prepare( $sql, $form_type );
		} else {
			$date_query = $this->_generate_date_query( $wpdb, $starting_date, $ending_date, 'd.', 'WHERE' );
			$sql 		= "SELECT d.`form_id`, SUM(d.`count`) as views FROM  {$this->table_name} d $date_query GROUP BY d.`form_id` ORDER BY views DESC LIMIT 0,1";
		}

		$results = $wpdb->get_results( $sql );

		if ( $results && is_array( $results ) && count( $results ) > 0 ) {
			return $results[0];
		}

		return false;
	}
}