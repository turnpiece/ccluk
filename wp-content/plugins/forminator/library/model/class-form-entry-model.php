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
	 * Time created in sql format D M Y @ H:i A
	 *
	 * @var string
	 */
	public $time_created;

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
	 * Hold information about connected addons
	 *
	 * @since 1.1
	 * @var array
	 */
	private static $connected_addons = array();


	/**
	 * Initialize the Model
	 *
	 * @since 1.0
	 * @since 1.1 Add instantiate connected addons
	 */
	public function __construct( $entry_id = null ) {
		$this->table_name      = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$this->table_meta_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );

		if ( is_numeric( $entry_id ) && $entry_id > 0 ) {
			$this->get( $entry_id );
			// get connected addons since
			self::get_connected_addons( $this->form_id );
		}

	}

	/**
	 * Load entry by id
	 * After load set entry to cache
	 *
	 * @since 1.0
	 *
	 * @param int $entry_id - the entry id
	 *
	 * @return bool|mixed
	 */
	public function get( $entry_id ) {
		global $wpdb;

		$cache_key          = get_class( $this );
		$entry_object_cache = wp_cache_get( $entry_id, $cache_key );

		if ( $entry_object_cache ) {
			$this->entry_id         = $entry_object_cache->entry_id;
			$this->entry_type       = $entry_object_cache->entry_type;
			$this->form_id          = $entry_object_cache->form_id;
			$this->is_spam          = $entry_object_cache->is_spam;
			$this->date_created_sql = $entry_object_cache->date_created_sql;
			$this->date_created     = $entry_object_cache->date_created;
			$this->time_created     = $entry_object_cache->time_created;
			$this->meta_data        = $entry_object_cache->meta_data;

			return $entry_object_cache;
		} else {
			$sql   = "SELECT `entry_type`, `form_id`, `is_spam`, `date_created` FROM {$this->table_name} WHERE `entry_id` = %d";
			$entry = $wpdb->get_row( $wpdb->prepare( $sql, $entry_id ) ); // WPCS: unprepared SQL ok. false positive
			if ( $entry ) {
				$this->entry_id         = $entry_id;
				$this->entry_type       = $entry->entry_type;
				$this->form_id          = $entry->form_id;
				$this->is_spam          = $entry->is_spam;
				$this->date_created_sql = $entry->date_created;
				$this->date_created     = date_i18n( 'j M Y', strtotime( $entry->date_created ) );
				$this->time_created     = date_i18n( 'j M Y @ H:i A', strtotime( $entry->date_created ) );
				$this->load_meta();
				wp_cache_set( $entry_id, $this, $cache_key );
			}
		}
	}

	/**
	 * Set fields
	 *
	 * @since 1.0
	 *
	 * @param array $meta_array {
	 *                          Array of data to be saved
	 *
	 * @type key - string the meta key
	 * @type value - string the meta value
	 * }
	 *
	 * @return bool - true or false
	 */
	public function set_fields( $meta_array ) {
		global $wpdb;

		if ( $meta_array && ! is_array( $meta_array ) && ! empty( $meta_array ) ) {
			return false;
		}

		if ( ! $this->entry_id ) {
			return false;
		}

		//clear cache first
		$cache_key = get_class( $this );
		wp_cache_delete( $this->entry_id, $cache_key );
		foreach ( $meta_array as $meta ) {
			if ( isset( $meta['name'] ) && isset( $meta['value'] ) ) {
				$key   = $meta['name'];
				$value = $meta['value'];
				$key   = wp_unslash( $key );
				$value = wp_unslash( $value );
				$value = maybe_serialize( $value );

				$meta_id = $wpdb->insert(
					$this->table_meta_name,
					array(
						'entry_id'     => $this->entry_id,
						'meta_key'     => $key,
						'meta_value'   => $value,
						'date_created' => date_i18n( 'Y-m-d H:i:s' ),
					)
				);

				/**
				 * Set Meta data for later usage
				 *
				 * @since 1.0.3
				 */
				if ( $meta_id ) {
					$this->meta_data[ $key ] = array(
						'id'    => $meta_id,
						'value' => is_array( $value ) ? array_map( 'maybe_unserialize', $value ) : maybe_unserialize( $value ),
					);
				}
			}
		}

		return true;
	}

	/**
	 * Load all meta data for entry
	 *
	 * @since 1.0
	 *
	 * @param object|bool $db - the WP_Db object
	 */
	public function load_meta( $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$this->meta_data = array();
		$sql             = "SELECT `meta_id`, `meta_key`, `meta_value` FROM {$this->table_meta_name} WHERE `entry_id` = %d";
		$results         = $db->get_results( $db->prepare( $sql, $this->entry_id ) );
		foreach ( $results as $result ) {
			$this->meta_data[ $result->meta_key ] = array(
				'id'    => $result->meta_id,
				'value' => is_array( $result->meta_value ) ? array_map( 'maybe_unserialize', $result->meta_value ) : maybe_unserialize( $result->meta_value ),
			);
		}
	}

	/**
	 * Get Meta
	 *
	 * @since 1.0
	 *
	 * @param string      $meta_key      - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function get_meta( $meta_key, $default_value = false ) {
		if ( ! empty( $this->meta_data ) && isset( $this->meta_data[ $meta_key ] ) ) {
			return $this->meta_data[ $meta_key ]['value'];
		}

		return $this->get_grouped_meta( $meta_key, $default_value );
	}

	/**
	 * Get Grouped Meta
	 * Sometimes the meta prefix is same
	 *
	 * @since 1.0
	 *
	 * @param string      $meta_key      - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function get_grouped_meta( $meta_key, $default_value = false ) {
		if ( ! empty( $this->meta_data ) ) {
			$response     = '';
			$field_suffix = self::field_suffix();
			foreach ( $field_suffix as $suffix ) {
				if ( isset( $this->meta_data[ $meta_key . '-' . $suffix ] ) ) {
					$response .= $this->meta_data[ $meta_key . '-' . $suffix ]['value'] . ' ' . $suffix . ' , ';
				}
			}
			if ( ! empty( $response ) ) {
				return substr( trim( $response ), 0, - 1 );
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

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'entry_type'   => $this->entry_type,
				'form_id'      => $this->form_id,
				'is_spam'      => $this->is_spam,
				'date_created' => date_i18n( 'Y-m-d H:i:s' ),
			)
		);

		if ( ! $result ) {
			return false;
		}
		wp_cache_delete( $this->form_id, 'forminator_total_entries' );
		wp_cache_delete( 'all_form_types', 'forminator_total_entries' );
		$this->entry_id = (int) $wpdb->insert_id;

		return true;
	}

	/**
	 * Delete entry with meta
	 *
	 * @since 1.0
	 */
	public function delete() {
		self::delete_by_entry( $this->form_id, $this->entry_id );
	}

	/**
	 * Field suffix
	 * Some fields are grouped and have the same suffix
	 *
	 * @since 1.0
	 * @return array
	 */
	public static function field_suffix() {
		return apply_filters(
			"forminator_field_suffix",
			array(
				'hours',
				'minutes',
				'ampm',
				'country',
				'city',
				'state',
				'zip',
				'street_address',
				'address_line',
				'year',
				'day',
				'month',
				'prefix',
				'first-name',
				'middle-name',
				'last-name',
				'post-title',
				'post-content',
				'post-excerpt',
				'post-image',
				'post-category',
				'post-tags',
				'product-id',
				'product-quantity',
			)
		);
	}

	/**
	 * Field suffix label
	 * Displayable label for suffix
	 *
	 * @since 1.0.5
	 * @return string
	 */
	public static function translate_suffix( $suffix ) {
		$translated_suffix = $suffix;
		$field_suffixes    = self::field_suffix();
		$default_label_map = array(
			'hours'            => esc_html__( 'Hour', Forminator::DOMAIN ),
			'minutes'          => esc_html__( 'Minute', Forminator::DOMAIN ),
			'ampm'             => esc_html__( 'AM/PM', Forminator::DOMAIN ),
			'country'          => esc_html__( 'Country', Forminator::DOMAIN ),
			'city'             => esc_html__( 'City', Forminator::DOMAIN ),
			'state'            => esc_html__( 'State', Forminator::DOMAIN ),
			'zip'              => esc_html__( 'Zip', Forminator::DOMAIN ),
			'street_address'   => esc_html__( 'Street Address', Forminator::DOMAIN ),
			'address_line'     => esc_html__( 'Address Line 2', Forminator::DOMAIN ),
			'year'             => esc_html__( 'Year', Forminator::DOMAIN ),
			'day'              => esc_html__( 'Day', Forminator::DOMAIN ),
			'month'            => esc_html__( 'Month', Forminator::DOMAIN ),
			'prefix'           => esc_html__( 'Prefix', Forminator::DOMAIN ),
			'first-name'       => esc_html__( 'First Name', Forminator::DOMAIN ),
			'middle-name'      => esc_html__( 'Middle Name', Forminator::DOMAIN ),
			'last-name'        => esc_html__( 'Last Name', Forminator::DOMAIN ),
			'post-title'       => esc_html__( 'Post Title', Forminator::DOMAIN ),
			'post-content'     => esc_html__( 'Post Content', Forminator::DOMAIN ),
			'post-excerpt'     => esc_html__( 'Post Excerpt', Forminator::DOMAIN ),
			'post-image'       => esc_html__( 'Post Image', Forminator::DOMAIN ),
			'post-category'    => esc_html__( 'Post Category', Forminator::DOMAIN ),
			'post-tags'        => esc_html__( 'Post Tags', Forminator::DOMAIN ),
			'product-id'       => esc_html__( 'Product ID', Forminator::DOMAIN ),
			'product-quantity' => esc_html__( 'Product Quantity', Forminator::DOMAIN ),
		);

		// could be filtered out field_suffix
		if ( in_array( $suffix, $field_suffixes, true ) && isset( $default_label_map[ $suffix ] ) ) {
			$translated_suffix = $default_label_map[ $suffix ];
		}

		/**
		 * Translatable suffix
		 *
		 * @param string $translated_suffix
		 * @param string $suffix            original suffix
		 * @param array  $default_label_map default translated suffix
		 *
		 * @since 1.0.5
		 */
		return apply_filters( "forminator_translate_suffix", $translated_suffix, $suffix, $default_label_map );
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
	 *
	 * @param int $form_id  - the form id
	 * @param int $per_page - results per page
	 * @param int $page     - the current page. Defaults to 0
	 *
	 * @return Forminator_Form_Entry_Model[]
	 */
	public static function list_entries( $form_id, $per_page, $page = 0 ) {
		global $wpdb;
		$entries    = array();
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql        = "SELECT `entry_id` FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0 ORDER BY `entry_id` DESC LIMIT %d, %d ";
		$results    = $wpdb->get_results( $wpdb->prepare( $sql, $form_id, $page, $per_page ) ); // WPCS: unprepared SQL ok. false positive

		if ( ! empty( $results ) ) {
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
	 *
	 * @param int $form_id - the form id
	 *
	 * @return Forminator_Form_Entry_Model[]
	 */
	public static function get_entries( $form_id ) {
		global $wpdb;
		$entries    = array();
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql        = "SELECT `entry_id` FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0 ORDER BY `entry_id` DESC";
		$results    = $wpdb->get_results( $wpdb->prepare( $sql, $form_id ) ); // WPCS: unprepared SQL ok. false positive

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$entries[] = new Forminator_Form_Entry_Model( $result->entry_id );
			}
		}

		return $entries;
	}

	/**
	 * Group count of Entries with extra selected
	 *
	 * @since   1.0.5
	 *
	 * @example = [
	 *  'FIELDS_WITH_EXTRA_ELEMENT_ID' => [
	 *      'META_VALUE-1' => COUNT
	 *      'META_VALUE-2' => COUNT
	 * ],
	 * 'answer-3' => [
	 *      'javascript is the best' => 8
	 *      'php is the best' => 7
	 * ],
	 * ]
	 *
	 * @param $form_id
	 * @param $fields_element_id_with_extra
	 *
	 * @return array|null|object
	 */
	public static function count_polls_with_extra( $form_id, $fields_element_id_with_extra ) {
		global $wpdb;

		$polls_with_extras = array();

		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );

		foreach ( $fields_element_id_with_extra as $field_element_id_with_extra ) {
			$sql
				       = "SELECT m.entry_id AS entry_id
							FROM {$table_name} m
							LEFT JOIN {$entry_table_name} e
							ON (m.entry_id = e.entry_id)
							WHERE e.form_id = %d
							AND m.meta_key = %s
							GROUP BY m.entry_id";
			$sql       = $wpdb->prepare( $sql, $form_id, $field_element_id_with_extra ); // WPCS: unprepared SQL ok. false positive
			$entry_ids = $wpdb->get_col( $sql ); // WPCS: unprepared SQL ok. false positive

			if ( ! empty( $entry_ids ) ) {
				$entry_id_placeholders = implode( ', ', array_fill( 0, count( $entry_ids ), '%d' ) );

				$sql
					 = "SELECT m.meta_value AS meta_value, COUNT(1) votes
							FROM {$table_name} m
							WHERE m.entry_id IN ({$entry_id_placeholders})
							AND m.meta_key = 'extra'
							GROUP BY m.meta_value ORDER BY votes DESC";
				$sql = $wpdb->prepare( $sql, $entry_ids ); // WPCS: unprepared SQL ok. false positive

				$votes = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL ok. false positive

				$polls_with_extras[ $field_element_id_with_extra ] = array();
				foreach ( $votes as $vote ) {
					$polls_with_extras[ $field_element_id_with_extra ][ $vote['meta_value'] ] = $vote['votes'];
				}
			}
		}

		return $polls_with_extras;
	}

	/**
	 * Count entries by form
	 *
	 * @since 1.0
	 *
	 * @param int $form_id - the form id
	 *
	 * @return int - total entries
	 */
	public static function count_entries( $form_id, $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$cache_key     = 'forminator_total_entries';
		$entries_cache = wp_cache_get( $form_id, $cache_key );

		if ( $entries_cache ) {
			return $entries_cache;
		} else {
			$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
			$sql        = "SELECT count(`entry_id`) FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0";
			$entries    = $db->get_var( $db->prepare( $sql, $form_id ) );
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
	 * @deprecated
	 *
	 * @param int $form_id - the form id
	 *
	 * @return int - total entries
	 */
	public static function count_entries_by_form_and_field( $form_id, $field ) {
		_deprecated_function( 'count_entries_by_form_and_field', '1.0.5' );
		global $wpdb;
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT count(m.`meta_id`) FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND e.`is_spam` = 0";
		$entries          = $wpdb->get_var( $wpdb->prepare( $sql, $form_id, $field ) ); // WPCS: unprepared SQL ok. false positive

		if ( $entries ) {
			return $entries;
		}

		return 0;
	}

	/**
	 * Map Polls Entries with its votes
	 *
	 * @since   1.0.5
	 *
	 * @example {
	 *  'ELEMENT_ID' => 'NUMBER'
	 *  'answer-1' = 9
	 * }
	 *
	 * @param       $form_id
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function map_polls_entries( $form_id, $fields ) {
		global $wpdb;
		$map_entries      = array();
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );

		$element_ids = array();
		foreach ( $fields as $field ) {
			$element_id    = (string) $field['element_id'];
			$element_ids[] = $element_id;
			$title         = sanitize_title( $field['title'] );

			// First, escape the link for use in a LIKE statement.
			$new_element_id_format = $wpdb->esc_like( 'answer-' );
			// Add wildcards
			$new_element_id_format = $new_element_id_format . '%';

			// find old format entries of this field
			$sql
				= "SELECT count(1) FROM {$table_name} m LEFT JOIN {$entry_table_name} e
					ON (e.`entry_id` = m.`entry_id`)
					WHERE e.form_id = {$form_id} AND m.meta_key NOT LIKE '{$new_element_id_format}' AND m.meta_value = '1' AND m.meta_key = '{$title}' LIMIT 1";

			// todo : it can not be prepared by $wpdb->prepare since element_id because of `LIKE` query
			$old_format_entries = $wpdb->get_var( $sql );

			// old format exist
			if ( $old_format_entries ) {
				// update old format entries if avail
				self::maybe_update_poll_entries_meta_key_to_element_id( $form_id, $title, $element_id );
			}
		}

		if ( ! empty( $element_ids ) ) {
			$element_ids_placeholders = implode( ', ', array_fill( 0, count( $element_ids ), '%s' ) );

			$sql
				= "SELECT m.meta_key as element_id, count(1) as votes
					FROM {$table_name} m LEFT JOIN {$entry_table_name} e
					ON (e.`entry_id` = m.`entry_id`)
					WHERE e.form_id = {$form_id} AND m.meta_key IN ({$element_ids_placeholders}) GROUP BY m.meta_key";

			$sql = $wpdb->prepare( $sql, $element_ids ); // WPCS: unprepared SQL ok. false positive

			$results = $wpdb->get_results( $sql, ARRAY_A ); // WPCS: unprepared SQL ok. false positive
			foreach ( $results as $result ) {
				$map_entries[ $result['element_id'] ] = $result['votes'];
			}

		}

		return $map_entries;
	}

	/**
	 * Update poll entries meta_key to its element_id
	 *
	 * @since 1.0.5
	 *
	 * @param $form_id
	 * @param $old_meta_key
	 * @param $element_id
	 */
	public static function maybe_update_poll_entries_meta_key_to_element_id( $form_id, $old_meta_key, $element_id ) {
		global $wpdb;
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		// find entries that using old format
		$sql = "SELECT entry_id FROM {$entry_table_name} where form_id = %d";

		$sql       = $wpdb->prepare( $sql, $form_id ); // WPCS: unprepared SQL ok. false positive
		$entry_ids = $wpdb->get_col( $sql ); // WPCS: unprepared SQL ok. false positive
		if ( ! empty( $entry_ids ) && count( $entry_ids ) > 0 ) {
			$entry_ids = implode( ', ', $entry_ids );
			$sql       = "UPDATE {$table_name} SET meta_key = '{$element_id}', meta_value = '{$old_meta_key}' WHERE entry_id IN ({$entry_ids}) AND meta_key = '{$old_meta_key}' AND meta_value = '1'";

			// todo: refactor this to use wpdb prepare
			$wpdb->query( $sql );
		}
	}

	/**
	 * Get entry date by ip and form
	 *
	 * @since 1.0
	 *
	 * @param int    $form_id - the form id
	 * @param string $ip      -  the user ip
	 *
	 * @return string|bool
	 */
	public static function get_entry_date_by_ip_and_form( $form_id, $ip ) {
		global $wpdb;
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT m.`date_created` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s order by m.`meta_id` desc limit 0,1";
		$entry_date       = $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip ) ); // WPCS: unprepared SQL ok. false postive

		if ( $entry_date ) {
			return $entry_date;
		}

		return false;
	}

	/**
	 * Get last entry by IP and form
	 *
	 * @since 1.0
	 *
	 * @param int    $form_id - the form id
	 * @param string $ip      -  the user ip
	 *
	 * @return string|bool
	 */
	public static function get_last_entry_by_ip_and_form( $form_id, $ip ) {
		global $wpdb;
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT m.`entry_id` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s order by m.`meta_id` desc limit 0,1";
		$entry_id         = $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip ) ); // WPCS: unprepared SQL ok. false positive

		if ( $entry_id ) {
			return $entry_id;
		}

		return false;
	}

	/**
	 * Get entry date by ip and form
	 *
	 * @since 1.0
	 *
	 * @param int    $form_id  - the form id
	 * @param string $ip       -  the user ip
	 * @param int    $entry_id - the entry id
	 * @param string $interval - the mysql interval. Eg (INTERVAL 1 HOUR)
	 *
	 * @return string|bool
	 */
	public static function check_entry_date_by_ip_and_form( $form_id, $ip, $entry_id, $interval ) {
		global $wpdb;
		$current_date     = date_i18n( 'Y-m-d H:i:s' );
		$table_name       = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT m.`meta_id` FROM {$table_name} m LEFT JOIN {$entry_table_name} e ON(e.`entry_id` = m.`entry_id`) WHERE e.`form_id` = %d AND m.`meta_key` = %s AND m.`meta_value` = %s AND m.`entry_id` = %d AND DATE_ADD(m.`date_created`, {$interval}) < %s order by m.`meta_id` desc limit 0,1";
		$entry            = $wpdb->get_var( $wpdb->prepare( $sql, $form_id, '_forminator_user_ip', $ip, $entry_id, $current_date ) ); // WPCS: unprepared SQL ok. false positive

		if ( $entry ) {
			return $entry;
		}

		return false;
	}

	/**
	 * Bulk delete form entries
	 *
	 * @since 1.0
	 *
	 * @param int $form_id - the form id
	 * @param bool|object - the WP_Object optional param
	 */
	public static function delete_by_form( $form_id, $db = false ) {
		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql        = "SELECT GROUP_CONCAT(`entry_id`) FROM {$table_name} WHERE `form_id` = %d";
		$entries    = $db->get_var( $db->prepare( $sql, $form_id ) );

		if ( $entries ) {
			self::delete_by_entrys( $form_id, $entries, $db );
		}
	}

	/**
	 * Delete by string of comma separated entry ids
	 *
	 * @since 1.0
	 * @since 1.1 Add init addons and Add hooks `forminator_before_delete_entry`
	 *
	 * @param      $form_id
	 * @param      $entries
	 * @param bool $db
	 *
	 * @return bool
	 */
	public static function delete_by_entrys( $form_id, $entries, $db = false ) {

		// get connected addons since
		self::get_connected_addons( $form_id );

		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}
		if ( ! $entries && ! empty( $entries ) ) {
			return false;
		}

		$table_name      = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$table_meta_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );

		forminator_maybe_log( 'delete_by_entrys', $form_id, $entries );
		$entries_to_array = explode( ',', $entries );
		forminator_maybe_log( 'delete_by_entrys', $form_id, $entries_to_array );
		if ( ! empty( $entries_to_array ) && is_array( $entries_to_array ) ) {
			foreach ( $entries_to_array as $entry_id ) {
				$form_id  = (int) $form_id;
				$entry_id = (int) $entry_id;
				forminator_maybe_log( 'forminator_before_delete_entry', $form_id, $entry_id );
				$entry_model = new Forminator_Form_Entry_Model( $entry_id );
				self::attach_addons_on_before_delete_entry( $form_id, $entry_model );
			}
		}

		/**
		 * Fires just before an entry getting deleted
		 *
		 * @since 1.1
		 *
		 * @param int $form_id  Current Form ID
		 * @param int $entry_id Current Entry ID to be deleted
		 */
		do_action_ref_array( 'forminator_before_delete_entries', array( $form_id, $entries ) );

		$sql = "DELETE FROM {$table_meta_name} WHERE `entry_id` IN ($entries)";
		$db->query( $sql );

		$sql = "DELETE FROM {$table_name} WHERE `entry_id` IN ($entries)";
		$db->query( $sql );

		wp_cache_delete( $form_id, 'forminator_total_entries' );
		wp_cache_delete( 'all_form_types', 'forminator_total_entries' );
	}


	/**
	 * Delete by entry
	 *
	 * @since 1.0
	 * @since 1.1 Add init addons and Add hooks `forminator_before_delete_entry`
	 *
	 * @param int $form_id  - the form id
	 * @param int $entry_id - the entry id
	 * @param bool|object - the WP_Object optional param
	 */
	public static function delete_by_entry( $form_id, $entry_id, $db = false ) {

		// get connected addons since
		self::get_connected_addons( $form_id );

		if ( ! $db ) {
			global $wpdb;
			$db = $wpdb;
		}

		$table_name      = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$table_meta_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$cache_key       = 'Forminator_Form_Entry_Model';

		$form_id  = (int) $form_id;
		$entry_id = (int) $entry_id;
		forminator_maybe_log( 'forminator_before_delete_entry', $form_id, $entry_id );
		/**
		 * Fires just before an entry getting deleted
		 *
		 * @since 1.1
		 *
		 * @param int $form_id  Current Form ID
		 * @param int $entry_id Current Entry ID to be deleted
		 */
		do_action_ref_array( 'forminator_before_delete_entry', array( $form_id, $entry_id ) );
		$entry_model = new Forminator_Form_Entry_Model( $entry_id );
		self::attach_addons_on_before_delete_entry( $form_id, $entry_model );

		$sql = "DELETE FROM {$table_meta_name} WHERE `entry_id` = %d";
		$db->query( $db->prepare( $sql, $entry_id ) );

		$sql = "DELETE FROM {$table_name} WHERE `entry_id` = %d";
		$db->query( $db->prepare( $sql, $entry_id ) );

		wp_cache_delete( $entry_id, $cache_key );
		wp_cache_delete( $form_id, 'forminator_total_entries' );
		wp_cache_delete( 'all_form_types', 'forminator_total_entries' );
	}

	/**
	 * Convert meta value to string
	 * Useful on displaying metadata without PHP warning on conversion
	 *
	 * @since 1.0.5
	 *
	 * @param      $field_type
	 * @param      $meta_value
	 * @param bool $allow_html
	 * @param int  $truncate truncate returned string (usefull if display container is limited)
	 *
	 * @return string
	 */
	public static function meta_value_to_string( $field_type, $meta_value, $allow_html = false, $truncate = PHP_INT_MAX ) {
		switch ( $field_type ) {
			case 'postdata':
				if ( ! isset( $meta_value['postdata'] ) || empty( $meta_value['postdata'] ) ) {
					$string_value = '';
				} else {
					$post_id = $meta_value['postdata'];
					$url     = get_edit_post_link( $post_id, 'link' );
					if ( $url ) {
						$string_value = $url;
						if ( $allow_html ) {
							// make link
							$title = get_the_title( $post_id );
							$title = ! empty( $title ) ? $title : __( '(no title)', Forminator::DOMAIN );
							//truncate
							if ( strlen( $title ) > $truncate ) {
								$title = substr( $title, 0, $truncate ) . '...';
							}
							$string_value = '<a href="' . $url . '" target="_blank" title="' . __( 'Edit Post', Forminator::DOMAIN ) . '">' . $title . '</a>';
						} else {
							//truncate url
							if ( strlen( $string_value ) > $truncate ) {
								$string_value = substr( $string_value, 0, $truncate ) . '...';
							}
						}
					} else {
						$string_value = '';
					}

				}
				break;
			case 'time':
				if ( ! isset( $meta_value['hours'] ) || ! isset( $meta_value['minutes'] ) ) {
					$string_value = '';
				} else {
					$string_value = sprintf( "%02d", $meta_value['hours'] ) . ':' . sprintf( "%02d", $meta_value['minutes'] ) . ' ' . ( isset( $meta_value ['ampm'] ) ? $meta_value['ampm'] : '' );
				}
				//truncate
				if ( strlen( $string_value ) > $truncate ) {
					$string_value = substr( $string_value, 0, $truncate ) . '...';
				}
				break;
			case 'date':
				if ( ! isset( $meta_value['year'] ) || ! isset( $meta_value['month'] ) || ! isset( $meta_value['day'] ) ) {
					$string_value = '';
				} else {
					$string_value = $meta_value['year'] . '/' . sprintf( "%02d", $meta_value['month'] ) . '/' . sprintf( "%02d", $meta_value['day'] );
				}
				//truncate
				if ( strlen( $string_value ) > $truncate ) {
					$string_value = substr( $string_value, 0, $truncate ) . '...';
				}
				break;
			case 'email':
				if ( ! empty( $meta_value ) ) {
					$string_value = $meta_value;
					//truncate
					if ( $allow_html ) {
						// make link
						$email = $string_value;
						//truncate
						if ( strlen( $email ) > $truncate ) {
							$email = substr( $email, 0, $truncate ) . '...';
						}
						$string_value = '<a href="mailto:' . $email . '" target="_blank" title="' . __( 'Send Email', Forminator::DOMAIN ) . '">' . $email . '</a>';
					} else {
						//truncate url
						if ( strlen( $string_value ) > $truncate ) {
							$string_value = substr( $string_value, 0, $truncate ) . '...';
						}
					}
				} else {
					$string_value = '';
				}

				break;
			case 'url':
				if ( ! empty( $meta_value ) ) {
					$string_value = $meta_value;
					//truncate
					if ( $allow_html ) {
						// make link
						$website = $string_value;
						//truncate
						if ( strlen( $website ) > $truncate ) {
							$website = substr( $website, 0, $truncate ) . '...';
						}
						$string_value = '<a href="' . $website . '" target="_blank" title="' . __( 'View Website', Forminator::DOMAIN ) . '">' . $website . '</a>';
					} else {
						//truncate url
						if ( strlen( $string_value ) > $truncate ) {
							$string_value = substr( $string_value, 0, $truncate ) . '...';
						}
					}
				} else {
					$string_value = '';
				}

				break;
			case 'upload':
				$file = '';
				if ( isset( $meta_value['file'] ) ) {
					$file = $meta_value['file'];
				}
				if ( ! empty( $file ) && is_array( $file ) && isset( $file['file_url'] ) ) {
					$string_value = $file['file_url'];
					if ( $allow_html ) {
						// make link
						$url       = $string_value;
						$file_name = basename( $url );
						$file_name = ! empty( $file_name ) ? $file_name : __( '(no filename)', Forminator::DOMAIN );
						//truncate
						if ( strlen( $file_name ) > $truncate ) {
							$file_name = substr( $file_name, 0, $truncate ) . '...';
						}
						$string_value = '<a href="' . $url . '" target="_blank" title="' . __( 'View File', Forminator::DOMAIN ) . '">' . $file_name . '</a>';
					} else {
						//truncate url
						if ( strlen( $string_value ) > $truncate ) {
							$string_value = substr( $string_value, 0, $truncate ) . '...';
						}
					}

				} else {
					$string_value = '';
				}
				break;
			case 'checkbox':
				if ( ! is_array( $meta_value ) ) {
					$string_value = '';
				} else {
					$string_value = implode( ', ', $meta_value );
				}
				//truncate
				if ( strlen( $string_value ) > $truncate ) {
					$string_value = substr( $string_value, 0, $truncate ) . '...';
				}
				break;
			default:
				// base flattener
				// implode on array
				if ( is_array( $meta_value ) ) {
					$string_value = implode( ', ', $meta_value );
				} else {
					// or juggling to string
					$string_value = (string) $meta_value;
				}
				//truncate
				if ( strlen( $string_value ) > $truncate ) {
					$string_value = substr( $string_value, 0, $truncate ) . '...';
				}
				break;
		}

		return $string_value;
	}

	/**
	 * Count all entries for all form_type
	 */
	public static function count_all_entries() {
		global $wpdb;
		$cache_key     = 'forminator_total_entries';
		$entries_cache = wp_cache_get( 'all_form_types', $cache_key );

		if ( $entries_cache ) {
			return $entries_cache;
		} else {
			$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
			$sql        = "SELECT count(`entry_id`) FROM {$table_name} WHERE `is_spam` = %d";
			$entries    = $wpdb->get_var( $wpdb->prepare( $sql, 0 ) ); // WPCS: unprepared SQL ok. false positive
			if ( $entries ) {
				wp_cache_set( 'all_form_types', $entries, $cache_key );

				return $entries;
			}
		}

		return 0;
	}

	/**
	 * Get Latest Entry
	 *
	 * @param string $entry_type
	 *
	 * @return Forminator_Form_Entry_Model|null
	 */
	public static function get_latest_entry( $entry_type = 'custom-forms' ) {
		$available_entry_types = array(
			'custom-forms',
			'quizzes',
			'poll',
			'all',
		);

		if ( ! in_array( $entry_type, $available_entry_types, true ) ) {
			return null;
		}

		global $wpdb;
		$entry      = null;
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		if ( 'all' !== $entry_type ) {
			$sql = "SELECT `entry_id` FROM {$table_name} WHERE `entry_type` = %s AND `is_spam` = 0 ORDER BY `date_created` DESC";
			$sql = $wpdb->prepare( $sql, $entry_type ); // WPCS: unprepared SQL ok. false positive
		} else {
			$sql = "SELECT `entry_id` FROM {$table_name} WHERE `is_spam` = 0 ORDER BY `date_created` DESC";
		}
		$entry_id = $wpdb->get_var( $sql ); // WPCS: unprepared SQL ok. false positive

		if ( ! empty( $entry_id ) ) {
			$entry = new Forminator_Form_Entry_Model( $entry_id );
		}

		return $entry;
	}

	/**
	 * Get Latest Entry by form_id
	 *
	 * @param $form_id
	 *
	 * @return Forminator_Form_Entry_Model|null
	 */
	public static function get_latest_entry_by_form_id( $form_id ) {

		global $wpdb;
		$entry      = null;
		$table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql        = "SELECT `entry_id` FROM {$table_name} WHERE `form_id` = %d AND `is_spam` = 0 ORDER BY `date_created` DESC";
		$entry_id   = $wpdb->get_var( $wpdb->prepare( $sql, $form_id ) ); // WPCS: unprepared SQL ok. false positive

		if ( ! empty( $entry_id ) ) {
			$entry = new Forminator_Form_Entry_Model( $entry_id );
		}

		return $entry;
	}

	/**
	 * Get Connected Addons for form_id, avoid overhead for checking connected addons many times
	 *
	 * @since 1.1
	 *
	 * @param $form_id
	 *
	 * @return array|Forminator_Addon_Abstract[]
	 */
	public static function get_connected_addons( $form_id ) {
		if ( ! isset( self::$connected_addons[ $form_id ] ) ) {
			self::$connected_addons[ $form_id ] = array();

			$connected_addons = forminator_get_addons_instance_connected_with_form( $form_id );

			foreach ( $connected_addons as $connected_addon ) {
				try {
					$form_hooks = $connected_addon->get_addon_form_hooks( $form_id );
					if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
						self::$connected_addons[ $form_id ][] = $connected_addon;
					}
				} catch ( Exception $e ) {
					forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to get_addon_form_hooks', $e->getMessage() );
				}
			}
		}

		return self::$connected_addons[ $form_id ];
	}

	/**
	 * Attach hooks for delete entry on connected addons
	 *
	 * @since 1.1
	 *
	 * @param                             $form_id
	 * @param Forminator_Form_Entry_Model $entry_model
	 */
	public static function attach_addons_on_before_delete_entry( $form_id, Forminator_Form_Entry_Model $entry_model ) {
		//find is_form_connected
		$connected_addons = self::get_connected_addons( $form_id );

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks      = $connected_addon->get_addon_form_hooks( $form_id );
				$addon_meta_data = forminator_find_addon_meta_data_from_entry_model( $connected_addon, $entry_model );
				$form_hooks->on_before_delete_entry( $entry_model, $addon_meta_data );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_before_delete_entry', $e->getMessage() );
			}

		}
	}

	/*
	 * Get entries by email
	 *
	 * @since 1.0.6
	 *
	 * @param $email
	 *
	 * @return array
	 */
	public static function get_custom_form_entry_ids_by_email( $email ) {
		global $wpdb;
		$meta_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY_META );
		$sql
		                 = "SELECT m.entry_id AS entry_id
							FROM {$meta_table_name} m
							WHERE (m.meta_key LIKE %s OR m.meta_key LIKE %s)
							AND m.meta_value = %s
							GROUP BY m.entry_id";

		$sql       = $wpdb->prepare(
			$sql,
			$wpdb->esc_like( 'email-' ) . '%',
			$wpdb->esc_like( 'text-' ) . '%',
			$email
		);
		$entry_ids = $wpdb->get_col( $sql );

		return $entry_ids;
	}

	/**
	 * Get entries older than $date_created
	 *
	 * @since 1.0.6
	 *
	 * @param $entry_type
	 * @param $date_created
	 *
	 * @return array
	 */
	public static function get_older_entry_ids( $entry_type, $date_created ) {
		global $wpdb;
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT e.entry_id AS entry_id
							FROM {$entry_table_name} e
							WHERE e.entry_type = %s
							AND e.date_created < %s";

		$sql = $wpdb->prepare(
			$sql,
			$entry_type,
			$date_created
		);

		$entry_ids = $wpdb->get_col( $sql );

		return $entry_ids;
	}

	/**
	 * Get entries older than $date_created of form_id
	 *
	 * @since 1.0.6
	 *
	 * @param $form_id
	 * @param $date_created
	 *
	 * @return array
	 */
	public static function get_older_entry_ids_of_form_id( $form_id, $date_created ) {
		global $wpdb;
		$entry_table_name = Forminator_Database_Tables::get_table_name( Forminator_Database_Tables::FORM_ENTRY );
		$sql
		                  = "SELECT e.entry_id AS entry_id
							FROM {$entry_table_name} e
							WHERE e.form_id = %d
							AND e.date_created < %s";

		$sql = $wpdb->prepare(
			$sql,
			$form_id,
			$date_created
		);

		$entry_ids = $wpdb->get_col( $sql );

		return $entry_ids;
	}

	/**
	 * Update Meta
	 *
	 * @since 1.0.6
	 *
	 * @param             $meta_id
	 * @param string      $meta_key      - the meta key
	 * @param bool|object $default_value - the default value
	 *
	 * @return bool|string
	 */
	public function update_meta( $meta_id, $meta_key, $default_value = false ) {
		global $wpdb;
		$wpdb->update(
			$this->table_meta_name,
			array(
				'entry_id'   => $this->entry_id,
				'meta_key'   => $meta_key,
				'meta_value' => $default_value,
			),
			array(
				'meta_id' => $meta_id,
			)
		);
		$cache_key = get_class( $this );
		wp_cache_delete( $this->entry_id, $cache_key );
		$this->get( $this->entry_id );
	}
}