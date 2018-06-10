<?php

/**
 * Class Hustle_Model
 *
 * @property int $module_id
 * @property int $blog_id
 * @property string $module_name
 * @property string $module_type
 * @property int $active
 * @property int $test_mode
 *
 */
abstract class Hustle_Model extends Hustle_Data
{

	/**
	 * Optin id
	 *
	 * @since 1.0.0
	 *
	 * @var $id int
	 */
	var $id;

	/**
	 * @var array
	 */
	protected $_test_types = array();
	protected $_track_types = array();

	protected $_stats = array();

	protected $_decorator = false;

	function __get($field)
	{
		$from_parent = parent::__get($field);
		if( !empty( $from_parent ) )
			return $from_parent;

		$meta = $this->get_meta( $field );
		if( !is_null( $meta )  )
			return $meta;
	}

	// function get_type(){
		// return "optin";
	// }

	// abstract function get_module_type();

	/**
	 * Returns optin based on provided id
	 *
	 * @param $id
	 * @return $this
	 */
	function get( $id ){
		$key = "hustle_model_data_" . $id;
		$this->_data  = wp_cache_get( $key );
		$this->id = (int) $id;

		if( false === $this->_data ){
			$this->_data = $this->_wpdb->get_row( $this->_wpdb->prepare( "SELECT * FROM  " . $this->get_table() . " WHERE `module_id`=%d", $this->id ), OBJECT );
			wp_cache_set( $key, $this->_data );
		}

		$this->_populate();

		return $this;
	}

	private function _populate(){
		if( $this->_data ){
			$this->id = $this->_data->module_id;
			foreach( $this->_data as $key => $data){
				$method =  "get_" . $key;
				$_d =  method_exists( $this, $method ) ? $this->{$method}() : $data;
				$this->{$key} = $_d;
			}
		}

		$this->get_test_types();
		$this->get_tracking_types();
	}
	/**
	 * Returns optin based on shortcode id
	 *
	 * @param string $shortcode_id
	 * @param bool $enforce_type Whether to get only embeds or sshares.
	 * @return $this
	 */
	function get_by_shortcode( $shortcode_id, $enforce_type = true ){

		$key = "hustle_shortcode_data_" . $shortcode_id;
		$this->_data  = wp_cache_get( $key );
		$prefix = $this->_wpdb->base_prefix;

		// If not cached.
		if( false === $this->_data ){
			if ( $enforce_type ) {
				// Enforce embedded/social_sharing type.
				$sql = $this->_wpdb->prepare( "
					SELECT * FROM  `" . $this->get_table() . "` as modules JOIN `{$prefix}hustle_modules_meta` as meta
			 	 	 ON modules.`module_id`=meta.`module_id`
			 	 	 WHERE `meta_key`='shortcode_id'
			 	 	 AND (`module_type` = 'embedded' OR `module_type` = 'social_sharing')
			 	 	 AND `meta_value`=%s", trim( $shortcode_id )
				);
			} else {
				// Do not enforce embedded/social_sharing type.
				$sql = $this->_wpdb->prepare( "
					SELECT * FROM  `" . $this->get_table() . "` as modules JOIN `{$prefix}hustle_modules_meta` as meta
			 	 	 ON modules.`module_id`=meta.`module_id`
			 	 	 WHERE `meta_key`='shortcode_id'
			 	 	 AND `meta_value`=%s", trim( $shortcode_id )
				);
			}

			// Get results and meta where the ID matches.
			$this->_data = $this->_data = $this->_wpdb->get_row( $sql, OBJECT );
		}

		$this->_populate();
		return $this;
	}


	/**
	 * Saves or updates optin
	 *
	 * @since 1.0.0
	 *
	 * @return false|int
	 */
	function save(){
		$data = get_object_vars($this);

		if( !isset( $data['blog_id'] ) )
			$data['blog_id'] = get_current_blog_id();

		$table = $this->get_table();
		if( empty( $this->id ) ){
			$this->_wpdb->insert($table, $this->_sanitize_model_data( $data ), array_values( $this->get_format() ));
			$this->id = $this->_wpdb->insert_id;
		}else{
			$this->_wpdb->update($table, $this->_sanitize_model_data( $data ), array( "module_id" => $this->id ), array_values( $this->get_format() ), array("%d") );
		}

		// Clear cache as well.
		$this->clear_object_cache();

		return $this->id;
	}

	/*
 	 * Clear object cache on save to prevent stale settings.
 	 *
 	 * return mixed (bool or string)
 	 */
	public function clear_object_cache() {
		global $wp_object_cache;
		// Confirm object cache object exists to prevent errors.
		if ( $wp_object_cache && is_object( $wp_object_cache ) ) {
			try {
				// Clear object cache.
				wp_cache_flush();
				return true;
			} catch ( Exception $exception ) {
				// If error, pass that on.
				return "Object Cache Error:" . $exception->getMessage();
			}
		}
	}

	/**
	 * Returns populated model attributes
	 *
	 * @return array
	 */
	public function get_attributes(){
		return $this->_sanitize_model_data( $this->data );
	}

	/**
	 * Matches given data to the data format
	 *
	 * @param $data
	 * @return array
	 */
	private function _sanitize_model_data( array $data ){
		$d = array();
		foreach($this->get_format() as $key => $format ){
			$d[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : "";
		}
		return $d;
	}

	/**
	 * Adds meta for the current optin
	 *
	 * @since 1.0.0
	 *
	 * @param $meta_key
	 * @param $meta_value
	 * @return false|int
	 */
	function add_meta( $meta_key, $meta_value ){
		return $this->_wpdb->insert( $this->get_meta_table(), array(
			"module_id" => $this->id,
			"meta_key" => $meta_key,
			"meta_value" => is_array( $meta_value ) || is_object( $meta_value ) ?  json_encode( $meta_value ) : $meta_value
		), array(
			"%d",
			"%s",
			"%s",
		));
	}

	/**
	 * Updates meta for the current optin
	 *
	 * @since 1.0.0
	 *
	 * @param $meta_key
	 * @param $meta_value
	 * @return false|int
	 */
	function update_meta( $meta_key, $meta_value ){

		if( $this->has_meta( $meta_key ) ) {
			return $this->_wpdb->update($this->get_meta_table(), array(
				"meta_value" => is_array($meta_value) || is_object($meta_value) ? json_encode($meta_value) : $meta_value
			), array(
				'module_id' => $this->id,
				'meta_key' => $meta_key
			),
				array(
					"%s",
				),
				array(
					"%d",
					"%s"
				)
			);

		}

		return $this->add_meta( $meta_key, $meta_value );

	}

	/**
	 * Checks if optin has $meta_key added disregarding the meta_value
	 *
	 * @param $meta_key
	 * @return bool
	 */
	public function has_meta( $meta_key ){
		return (bool)$this->_wpdb->get_row( $this->_wpdb->prepare( "SELECT * FROM " . $this->get_meta_table() .  " WHERE `meta_key`=%s AND `module_id`=%d", $meta_key, (int) $this->id ) );
	}

	/**
	 * Retrieves optin meta from db
	 *
	 * @param string $meta_key
	 * @param mixed $default
	 * @return null|string|$default
	 */
	public function get_meta( $meta_key, $default = null ){
		$value = wp_cache_get( $this->id, 'hustle_meta_' . $meta_key );
		if( false === $value ){
			$value = $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT `meta_value` FROM " . $this->get_meta_table() .  " WHERE `meta_key`=%s AND `module_id`=%d", $meta_key, (int) $this->id ) );
			wp_cache_add( $this->id, $value,  'hustle_meta_' . $meta_key );
		}

		return  is_null( $value ) ? $default : $value;
	}

	/**
	 * Returns db data for current optin
	 *
	 * @return array
	 */
	function get_data(){
		return (array) $this->_data;
	}

	/**
	 * Toggles state of optin or optin type
	 *
	 * @param null $environment
	 * @return false|int|WP_Error
	 */
	function toggle_state( $environment = null ){
		// Clear cache.
		$this->clear_object_cache();

		if( is_null( $environment ) ){ // so we are toggling state of the optin
			return $this->_wpdb->update( $this->get_table(), array(
				"active" => (1 - $this->active)
			), array(
				"module_id" => $this->id
			), array(
				"%d"
			) );
		}
	}

	/**
	 * Toggles state of display type (popup, slide-in, floating_social etc) for each module
	 *
	 * @param null $environment
	 * @return false|int|WP_Error
	 */
	function toggle_display_type_state( $environment = null, $settings = false ){
		if( is_null( $environment ) ) {
			return $this->toggle_state( $environment );
		}

		if ( $settings ) {
			$obj_settings = json_decode($this->settings);
			$prev_value = $obj_settings->$environment;
			$prev_value->enabled = !isset( $prev_value->enabled ) || "false" === $prev_value->enabled ? "true": "false";
			$new_value = array_merge( (array) $obj_settings, array( $environment => $prev_value ));
			return $this->update_meta( self::KEY_SETTINGS,  json_encode( $new_value ) );
		} else {
			if( in_array( $environment, $this->types ) ) { // we are toggling state of a specific environment
				$prev_value = $this->{$environment}->to_object();
				$prev_value->enabled = !isset( $prev_value->enabled ) || "false" === $prev_value->enabled ? "true": "false";
				return $this->update_meta( $environment,  json_encode( $prev_value ) );
			} else{
				return new WP_Error("Invalid_env", "Invalid environment . " . $environment);
			}
		}
	}

	/**
	 * Logs interactions done on the optin
	 *
	 * @param $data
	 * @param string $type
	 * @return false|int
	 */
	private function _log($data, $type = self::KEY_VIEW){

		$data = wp_parse_args($data, array(
			"date"      => current_time('timestamp'),
			'ip'        => Opt_In::get_client_ip(),
			'deleted'   => 0,
			'page_type' => "",
			'page_id'   => 0
		));

		return $this->add_meta( $type, $data );
	}

	/**
	 * Logs optin view
	 *
	 * @param $data
	 * @param string $type
	 * @return false|int
	 */
	function log_view( $data, $type ){
		return $this->_log( $data,  $type  . '_' . self::KEY_VIEW  );
	}


	/**
	 * Logs optin conversion
	 *
	 * @param $data
	 * @param $optin_type
	 * @return false|int
	 */
	function log_conversion( $data, $optin_type ){
		return $this->_log( $data, $optin_type  . '_' . self::KEY_CONVERSION );
	}

	/**
	 * Converts the model to json
	 *
	 * @since 1.0.0
	 * @return string json
	 */
	function to_json(){
		$model_data = array_merge(
			$this->_sanitize_model_data( get_object_vars( $this ) ),
			array("id" => $this->id),
			array( "save_to_local" => $this->save_to_collection )
		);
		return json_encode( $model_data );
	}

	/**
	 * Deletes optin from optin table and optin meta table
	 *
	 * @return bool
	 */
	function delete(){

		// delete optin
		$result = $this->_wpdb->delete( $this->get_table(), array(
			"module_id" => $this->id
		),
			array(
				"%d"
			)
		);

		//delete metas
		return $result && $this->_wpdb->delete( $this->get_meta_table(), array(
			"module_id" => $this->id
		),
			array(
				"%d"
			)
		);

	}

	/**
	 * Checks if this optin is allowed to show up in frontend for current user
	 *
	 * @return bool
	 */
	function is_allowed_for_current_user(){
		return  1 === (int)$this->test_mode || current_user_can( 'manage_options' );
	}

	/**
	 * Retrieves active types from db
	 *
	 * @return null|array
	 */
	function get_test_types(){
		$this->_test_types = json_decode( $this->get_meta( self::TEST_TYPES ), true );
		return $this->_test_types;
	}

	/**
	 * Retrieves active tracking types from db
	 *
	 * @return null|array
	 */
	function get_tracking_types(){
		$this->_track_types = json_decode( $this->get_meta( self::TRACK_TYPES ), true );
		return $this->_track_types;
	}

	/**
	 * Checks if $type is active
	 *
	 * @param $type
	 * @return bool
	 */
	function is_test_type_active( $type ){
		return isset( $this->_test_types[ $type ] );
	}

	/**
	 * Checks if $type is active
	 *
	 * @param $type
	 * @return bool
	 */
	function is_test_active(){
		// return isset( $this->_test_types[ $type ] );
		// TODO: get the actual test value
		return true;
	}

	/**
	 * Checks if $type is active
	 *
	 * @param $type
	 * @return bool
	 */
	function is_tracking_enabled() {
		// return isset( $this->_test_types[ $type ] );
		// TODO: get the actual tracking types
		return true;
	}

	/**
	 * Checks if $type is allowed to track views and conversions
	 *
	 * @param $type
	 * @return bool
	 */
	function is_track_type_active( $type ){
		return isset( $this->_track_types[ $type ] );
	}

	/**
	 * Toggles $type's test mode
	 *
	 * @param $type
	 * @return bool
	 */
	function toggle_type_test_mode( $type ){

		if( $this->is_test_type_active( $type ) )
			unset( $this->_test_types[ $type ] );
		else
			$this->_test_types[ $type ] = true;

		// Clear cache.
		$this->clear_object_cache();

		return $this->update_meta( self::TEST_TYPES, $this->_test_types );
	}

	/**
	 * Toggles $type's tracking mode
	 *
	 * @param $type
	 * @return bool
	 */
	function toggle_type_track_mode( $type ){

		if( $this->is_track_type_active( $type ) )
			unset( $this->_track_types[ $type ] );
		else
			$this->_track_types[ $type ] = true;

		return $this->update_meta( self::TRACK_TYPES, $this->_track_types );
	}

	/**
	 * Returns settings saved as meta
	 *
	 * @since 2.0
	 * @param string $key
	 * @param string $default json string
	 * @return object|array
	 */
	protected function get_settings_meta( $key, $default = "{}", $as_array = false ){
		$settings_json = $this->get_meta( $key );
		return json_decode( $settings_json ? $settings_json : $default, $as_array );
	}

	/**
	 * Checks if module is active for admin user
	 *
	 * @return int
	 */
	function get_is_active_for_admin(){
		return (int) $this->get_meta( self::ACTIVE_FOR_ADMIN, 1 );
	}

	/**
	 * Checks if module is active for logged in user
	 *
	 * @return int
	 */
	function get_is_active_for_logged_in_user(){
		return (int) $this->get_meta( self::ACTIVE_FOR_LOGGED_IN, 1 );
	}

	function toggle_activity_for_user( $user_type ){

		if( !in_array( $user_type, array( "admin", "logged_in" ) ) ) return new WP_Error("invalid arg", __("Invalid user type provided", Opt_In::TEXT_DOMAIN), $user_type);

		$key = $user_type === "admin" ? self::ACTIVE_FOR_ADMIN : self::ACTIVE_FOR_LOGGED_IN;
		$val = $user_type === "admin" ? $this->is_active_for_admin : $this->is_active_for_logged_in_user;

		return $this->update_meta( $key, 1 - $val );

	}


	/**
	 * Checkes if module has type
	 *
	 * @param $type_name
	 * @return bool
	 */
	function has_type( $type_name ){
		return in_array( $type_name, $this->types );
	}

	/**
	 * Checks if module should be displayed on frontend
	 *
	 * @return bool
	 */
	function get_display(){

		/**
		 * Return true if any test type if active
		 */
		$test_types = $this->get_test_types();
		if( !empty( $test_types ) && current_user_can('administrator')  )
			return true;

		if( !$this->active )
			return false;

		if( current_user_can('administrator') && !$this->is_active_for_admin )
			return false;

		if( is_user_logged_in() && !current_user_can('administrator') && !$this->is_active_for_logged_in_user )
			return false;


		return true;
	}

	/**
	 * @param $type
	 * @return null|Hustle_Model_Stats
	 */
	function get_statistics( $type ){

		if( !isset( $this->_stats[ $type ] ) ) {
			$this->_stats[ $type ] = new Hustle_Model_Stats($this, $type);
		}

		return $this->_stats[ $type ];
	}


	/**
	 * Get all module conversion base on the given dates
	 * @param $starting_date
	 * @param $ending_date
	 * @return (array|object|null) Database query results
	 */
	function get_module_conversion( $starting_date, $ending_date, $is_array ){
		$date_format = '%Y%m%d';
		$conversion_query = '%_conversion';
		$date_condition = ( !is_null($starting_date) && !is_null($ending_date) && !empty($starting_date) && !empty($ending_date) )
			? "WHERE c.dates >= '". $starting_date ."' AND c.dates <= '". $ending_date ."' "
			: "";

		$return_type = ( $is_array ) ? ARRAY_A : OBJECT;

		return $this->_wpdb->get_results( $this->_wpdb->prepare( "
			SELECT c.dates, COUNT(c.dates) AS conversions FROM (SELECT DATE_FORMAT(FROM_UNIXTIME(SUBSTRING(meta_value,9,10)), '%s') AS dates FROM `". $this->get_meta_table() ."` WHERE module_id = %d AND meta_key LIKE '%s') AS c ". $date_condition ."GROUP BY c.dates", $date_format, $this->id, $conversion_query ), $return_type );
	}

}