<?php
/**
 * Form Entry model
 * Base model for all form entries
 *
 * @since 1.0
 */
class Forminator_Form_Entry_Model {

	/**
	 * Entry id
	 *
	 * @var int
	 */
	public $entry_id = 0;

	/**
	 * Entry type
	 *
	 * @var string
	 */
	public $entry_type;

	/**
	 * Form id
	 *
	 * @var int
	 */
	public $form_id;

	/**
	 * Spam flag
	 *
	 * @var bool
	 */
	public $is_spam = false;

	/**
	 * Date created in sql format 0000-00-00 00:00:00
	 *
	 * @var string
	 */
	public $date_created_sql;

	/**
	 * Date created in sql format D M Y
	 *
	 * @var string
	 */
	public $date_created;

	/**
	 * Meta data
	 *
	 * @var array
	 */
	public $meta_data = array();

	/**
	 * The table name
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * The table meta name
	 *
	 * @var string
	 */
	protected $table_meta_name;


	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 */
	public function __construct( $entry_id = null ) {
		$this->table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$this->table_meta_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );

		if ( is_numeric( $entry_id ) && $entry_id > 0 ) {
			$this->get( $entry_id );
		}
	}

	/**
	 * Load entry by id
	 * After load set entry to cache
	 *
	 * @since 1.0
	 * @param int $entry_id - the entry id
	 */
	public function get( $entry_id ) {
		global $wpdb;

		$cache_key 			= get_class( $this );
		$entry_object_cache = wp_cache_get( $entry_id, $cache_key );

		if ( $entry_object_cache ) {
			return $entry_object_cache;
		} else {
			$sql 		= "SELECT `entry_type`, `form_id`, `is_spam`, `date_created` FROM {$this->table_name} WHERE `entry_id` = %d";
			$entry = $wpdb->get_row( $wpdb->prepare( $sql, $entry_id ) );
			if ( $entry ){
				$this->entry_id 		= $entry_id;
				$this->entry_type 		= $entry->entry_type;
				$this->form_id 			= $entry->form_id;
				$this->is_spam			= $entry->is_spam;
				$this->date_created_sql = $entry->date_created;
				$this->date_created		= date_i18n( 'j M Y', strtotime( $entry->date_created ) );
				$this->load_meta();
				wp_cache_set( $entry_id, $this, $cache_key );
			}
		}
	}

	/**
	 * Set fields
	 *
	 * @since 1.0
	 * @param array $meta_array {
	 * 		Array of data to be saved
	 * 		@type key - string the meta key
	 * 		@type value - string the meta value
	 * }
	 *
	 * @return bool - true or false
	 */
	public function set_fields( $meta_array ) {
		global $wpdb;

		if ( $meta_array && !is_array( $meta_array ) && !empty( $meta_array ) ) {
			return false;
		}

		if ( !$this->entry_id ) {
			return false;
		}

		//clear cache first
		$cache_key 	= get_class( $this );
		wp_cache_delete( $this->entry_id, $cache_key );
		foreach ( $meta_array as $meta ) {
			if ( isset( $meta['name'] ) && isset( $meta['value'] ) ) {
				$key 	= $meta['name'];
				$value 	= $meta['value'];
				$key   	= wp_unslash( $key );
				$value 	= wp_unslash( $value );
				$value 	= maybe_serialize( $value );

				$wpdb->insert( $this->table_meta_name, array(
					'entry_id'     	=> $this->entry_id,
					'meta_key'   	=> $key,
					'meta_value'    => $value,
					'date_created'  => date_i18n( 'Y-m-d H:i:s' )
				) );
			}
		}
		return true;
	}

	/**
	 * Load all meta data for entry
	 *
	 * @since 1.0
	 * @param object|bool $db - the WP_Db object
	 */
	public function load_meta( $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$this->meta_data 	= array();
		$sql 				= "SELECT `meta_id`, `meta_key`, `meta_value` FROM {$this->table_meta_name} WHERE `entry_id` = %d";
		$results 			= $db->get_results( $db->prepare( $sql, $this->entry_id ) );
		foreach ( $results as $result ) {
			$this->meta_data[ $result->meta_key ] = array(
				'id' 	=> $result->meta_id,
				'value' => is_array( $result->meta_value ) ? array_map( 'maybe_unserialize', $result->meta_value ) : maybe_unserialize( $result->meta_value )
			);
		}
	}

	/**
	 * Get Meta
	 *
	 * @since 1.0
	 * @param string $meta_key - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function get_meta( $meta_key, $default_value = false ) {
		if ( !empty( $this->meta_data ) && isset( $this->meta_data[ $meta_key ] ) ) {
			return $this->meta_data[ $meta_key ]['value'];
		}
		return $this->get_grouped_meta( $meta_key, $default_value );
	}

	/**
	 * Get Grouped Meta
	 * Sometimes the meta prefix is same
	 *
	 * @since 1.0
	 * @param string $meta_key - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function get_grouped_meta( $meta_key, $default_value = false ) {
		if ( !empty( $this->meta_data ) ) {
			$response 		= '';
			$field_suffix 	= self::field_suffix();
			foreach ( $field_suffix as $suffix ) {
				if ( isset( $this->meta_data[ $meta_key . '-' . $suffix ] ) ) {
					$response .= $this->meta_data[ $meta_key . '-' . $suffix ]['value'] . ' ' . $suffix . ' , ';
				}
			}
			if ( !empty( $response ) ) {
				return substr( trim( $response ), 0, -1 );
			}

		}
		return $default_value;
	}

	/**
	 * Save entry
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function save() {
		global $wpdb;

		$result = $wpdb->insert( $this->table_name, array(
			'entry_type'    => $this->entry_type,
			'form_id'   	=> $this->form_id,
			'is_spam'		=> $this->is_spam,
			'date_created'  => date_i18n( 'Y-m-d H:i:s' )
		) );

		if ( ! $result )
			return false;
		wp_cache_delete( $this->form_id, 'forminator_total_entries' );
		$this->entry_id = (int) $wpdb->insert_id;

		return true;
	}

	/**
	 * Delete entry with meta
	 *
	 * @since 1.0
	 */
	public function delete() {
		self::delete_by_entry( $this->entry_id );
	}

	/**
	 * Field suffix
	 * Some fields are grouped and have the same suffix
	 *
	 * @since 1.0
	 * @return array
	 */
	public static function field_suffix() {
		return apply_filters( "forminator_field_suffix", array(
			'hours', 'minutes', 'ampm', 'country', 'city', 'state', 'zip', 'street_address', 'address_line', 'year', 'day', 'month', 'prefix',
			'first-name', 'middle-name','last-name', 'post-title', 'post-content', 'post-excerpt', 'post-image',
			'post-category', 'post-tags','product-id', 'product-quantity'
		) );
	}

	/**
	 * Ignored fields
	 * Fields not saved or shown
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public static function ignored_fields() {
		return apply_filters( 'forminator_entry_ignored_fields', array( 'html', 'pagination', 'captcha', 'section' ) );
	}

	/**
	 * List entries
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param int $per_page - results per page
	 * @param int $page - the current page. Defaults to 0
	 *
	 * @return array(
	 * 		Forminator_Form_Entry_Model
	 * )
	 */
	public static function list_entries( $form_id, $per_page, $page = 0 ) {
		global $wpdb;
		$entries 	= array();
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 		= "SELECT `entry_id` FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0 ORDER BY `entry_id` DESC LIMIT %d, %d ";
		$results 	= $wpdb->get_results( $wpdb->prepare( $sql, $form_id, $page, $per_page ) );

		if( !empty( $results ) ) {
			foreach ( $results as $result ) {
				$entries[] = new Forminator_Form_Entry_Model( $result->entry_id );
			}
		}

		return $entries;
	}


	/**
	 * Get all entries
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 *
	 * @return array(
	 * 		Forminator_Form_Entry_Model
	 * )
	 */
	public static function get_entries( $form_id ) {
		global $wpdb;
		$entries 	= array();
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 		= "SELECT `entry_id` FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0 ORDER BY `entry_id` DESC";
		$results 	= $wpdb->get_results( $wpdb->prepare( $sql, $form_id ) );

		if( !empty( $results ) ) {
			foreach ( $results as $result ) {
				$entries[] = new Forminator_Form_Entry_Model( $result->entry_id );
			}
		}

		return $entries;
	}

	/**
	 * Count entries by form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 *
	 * @return int - total entries
	 */
	public static function count_entries( $form_id, $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$cache_key		= 'forminator_total_entries';
		$entries_cache 	= wp_cache_get( $form_id, $cache_key );

		if ( $entries_cache ) {
			return $entries_cache;
		} else {
			$table_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
			$sql 			= "SELECT count(`entry_id`) FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0";
			$entries 		= $db->get_var( $db->prepare( $sql, $form_id ) );
			if ( $entries ) {
				wp_cache_set( $form_id, $entries, $cache_key );
				return $entries;
			}
		}

		return 0;
	}


	/**
	 * Count entries by form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 *
	 * @return int - total entries
	 */
	public static function count_entries_by_form_and_field( $form_id, $field ) {
		global $wpdb;
		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 				= "SELECT count(m.`meta_id`) FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND e.`is_spam` = 0";
		$entries 			= $wpdb->get_var( $wpdb->prepare( $sql, $form_id, $field ) );

		if ( $entries ) {
			return $entries;
		}

		return 0;
	}

	/**
	 * Get entry date by ip and form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $ip -  the user ip
	 *
	 * @return string|bool
	 */
	public static function get_entry_date_by_ip_and_form( $form_id, $ip ) {
		global $wpdb;
		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 				= "SELECT m.`date_created` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s order by m.`meta_id` desc limit 0,1";
		$entry_date 		= $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip ) );

		if ( $entry_date ) {
			return $entry_date;
		}

		return false;
	}

	/**
	 * Get last entry by IP and form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $ip -  the user ip
	 *
	 * @return string|bool
	 */
	public static function get_last_entry_by_ip_and_form( $form_id, $ip ) {
		global $wpdb;
		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 				= "SELECT m.`entry_id` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s order by m.`meta_id` desc limit 0,1";
		$entry_id 			= $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip ) );

		if ( $entry_id ) {
			return $entry_id;
		}

		return false;
	}

	/**
	 * Get entry date by ip and form
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $ip -  the user ip
	 * @param int $entry_id - the entry id
	 * @param string $interval - the mysql interval. Eg (INTERVAL 1 HOUR)
	 *
	 * @return string|bool
	 */
	public static function check_entry_date_by_ip_and_form( $form_id, $ip, $entry_id, $interval ) {
		global $wpdb;
		$current_date  		= date_i18n( 'Y-m-d H:i:s' );
		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 				= "SELECT m.`meta_id` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s AND m.`entry_id` = %d AND DATE_ADD(m.`date_created`, {$interval}) < %s order by m.`meta_id` desc limit 0,1";
		$entry 				= $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip, $entry_id, $current_date ) );

		if ( $entry ) {
			return $entry;
		}

		return false;
	}

	/**
	 * Bulk delete form entries
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param bool|object - the WP_Object optional param
	 */
	public static function delete_by_form( $form_id , $db = false  ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql 		= "SELECT GROUP_CONCAT(`entry_id`) FROM {$table_name} WHERE `form_id` = %d";
		$entries 	= $db->get_var( $db->prepare( $sql, $form_id ) );

		if ( $entries ) {
			self::delete_by_entrys( $form_id, $entries, $db );
		}
	}

	/**
	 * Delete by string of comma separated entry ids
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param string $entries - the entries
	 * @param bool|object - the WP_Object optional param
	 *
	 */
	public static function delete_by_entrys( $form_id, $entries , $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		if ( !$entries && !empty( $entries ) ) {
			return false;
		}

		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$table_meta_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );

		$sql = "DELETE FROM {$table_meta_name} WHERE `entry_id` IN ($entries)";
		$db->query( $sql );

		$sql = "DELETE FROM {$table_name} WHERE `entry_id` IN ($entries)";
		$db->query( $sql );

		wp_cache_delete( $form_id, 'forminator_total_entries' );
	}


	/**
	 * Delete by entry
	 *
	 * @since 1.0
	 * @param int $form_id - the form id
	 * @param int $entry_id - the entry id
	 * @param bool|object - the WP_Object optional param
	 */
	public static function delete_by_entry( $form_id, $entry_id , $db = false ) {
		if ( !$db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$table_name 		= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$table_meta_name 	= Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$cache_key 			= get_class( self );

		$sql = "DELETE FROM {$table_meta_name} WHERE `entry_id` = %d";
		$db->query( $db->prepare( $sql, $entry_id ) );

		$sql = "DELETE FROM {$table_name} WHERE `entry_id` = %d";
		$db->query( $db->prepare( $sql, $entry_id ) );

		wp_cache_delete( $entry_id, $cache_key );
		wp_cache_delete( $form_id, 'forminator_total_entries' );
	}
}