<?php

/**
 * Class Hustle_Module_Collection
 *
 *
 */
class Hustle_Module_Collection extends Hustle_Collection
{

	/**
	 * @return Hustle_Module_Collection
	 */
	public static function instance(){
		return new self;
	}

	/**
	 * Returns array of Hustle_Module_Model
	 *
	 *
	 * @param bool|true $active
	 * @param array $args
	 * @param int $limit
	 * @return array Hustle_Module_Model[]
	 */
	public function get_all( $active = true, $args = array(), $limit = -1 ){
		$blog_id = (int) ( isset( $args['blog_id'] ) ? $args['blog_id']  : get_current_blog_id() );
		$module_type = ( isset( $args['module_type'] ) ) ? $args['module_type'] : '' ;

		if( -1 != $limit ){
			$limit = "LIMIT $limit";
		}else{
			$limit = "";
		}

		$module_type_condition = ( !empty($module_type) ) ? "AND `module_type`='" . $module_type . "'" : "";
		$module_type_condition .= ( isset($args['except_types']) ) ? $this->prepare_except_module_types_condition( $args['except_types'] ) : "";

		if( is_null( $active ) ) {
			$ids = self::$_db->get_col( self::$_db->prepare( "SELECT `module_id` FROM " . $this->_get_table() . " WHERE `blog_id`=%d ". $module_type_condition ." ORDER BY  `module_name` $limit", $blog_id ) );
		} else {
			$ids = self::$_db->get_col( self::$_db->prepare( "SELECT `module_id` FROM " . $this->_get_table() ." WHERE `active`= %d AND `blog_id`=%d ". $module_type_condition ." ORDER BY  `module_name` $limit", (int) $active, $blog_id )  );
		}

		return array_map( array( $this, "return_model_from_id" ), $ids );
	}

	/**
	 * Get top module conversion
	 * @param $starting_date
	 * @param $ending_date
	 * @param $offset
	 * @param $limit
	 * @return (array|object|null) Database query results
	 */
	function get_top_module_conversion( $starting_date, $ending_date, $offset, $limit ){
		$date_format = '%Y%m%d';
		$conversion_query = '%_conversion';
		$date_condition = ( !is_null($starting_date) && !is_null($ending_date) && !empty($starting_date) && !empty($ending_date) )
			? "WHERE c.dates >= '". $starting_date ."' AND c.dates <= '". $ending_date ."' "
			: "";

		$offset = ( is_null($offset) || empty($offset) ) ? 0 : $offset;
		$limit = ( is_null($limit) || empty($limit) ) ? 5 : $limit;

		return self::$_db->get_results( self::$_db->prepare( "
			SELECT COUNT(c.dates) AS conversions, c.module_id FROM (SELECT DATE_FORMAT(FROM_UNIXTIME(SUBSTRING(meta_value,9,10)), '%s') AS dates, module_id FROM `". $this->_get_meta_table() ."` WHERE meta_key LIKE '%s') AS c ". $date_condition ."GROUP BY c.module_id ORDER BY conversions DESC LIMIT %d, %d", $date_format, $conversion_query, $offset, $limit ) );
	}

	/**
	 * Get top module conversion without social sharing
	 * @param $starting_date
	 * @param $ending_date
	 * @param $offset
	 * @param $limit
	 * @return (array|object|null) Database query results
	 */
	function get_top_module_conversion_without_ss( $starting_date, $ending_date, $offset, $limit ){
		$date_format = '%Y%m%d';
		$conversion_query = '%_conversion';
		$date_condition = ( !is_null($starting_date) && !is_null($ending_date) && !empty($starting_date) && !empty($ending_date) )
			? "WHERE c.dates >= '". $starting_date ."' AND c.dates <= '". $ending_date ."' "
			: "";

		$offset = ( is_null($offset) || empty($offset) ) ? 0 : $offset;
		$limit = ( is_null($limit) || empty($limit) ) ? 5 : $limit;

		return self::$_db->get_results( self::$_db->prepare( "
			SELECT COUNT(c.dates) AS conversions, c.module_id FROM (SELECT DATE_FORMAT(FROM_UNIXTIME(SUBSTRING(meta_value,9,10)), '%s') AS dates, module_id FROM `". $this->_get_meta_table() ."` WHERE meta_key LIKE '%s') AS c INNER JOIN ". $this->_get_table() ." AS whd ON whd.module_id = c.module_id ". $date_condition ."AND whd.module_type != 'social_sharing' GROUP BY c.module_id ORDER BY conversions DESC LIMIT %d, %d", $date_format, $conversion_query, $offset, $limit ) );
	}

	/**
	 * Get today's total conversion
	 * @param $today
	 * @return (array|object|null) Database query results
	 */
	function get_today_total_conversion( $today){
		$date_format = '%Y%m%d';
		$conversion_query = '%_conversion';
		$exclude_sshare = 'floating_social_conversion';

		return self::$_db->get_row( self::$_db->prepare( "
			SELECT COUNT(c.dates) AS conversions FROM (SELECT DATE_FORMAT(FROM_UNIXTIME(SUBSTRING(meta_value,9,10)), '%s') AS dates FROM `". $this->_get_meta_table() ."` WHERE meta_key LIKE '%s' AND meta_key != '%s') AS c WHERE c.dates = '%s'", $date_format, $conversion_query, $exclude_sshare, $today ) );
	}

	function prepare_except_module_types_condition( $excepts ) {
		$except_condition = "";
		foreach( $excepts as $except ) {
			$except_condition .= " AND `module_type` != '". $except ."'";
		}
		return $except_condition;
	}

	function return_model_from_id( $id ){
		if( empty( $id )) return array();
		$module = Hustle_Module_Model::instance()->get( $id );
		if ( $module ) {
			if ( $module->module_type == 'social_sharing' ) {
				return Hustle_SShare_Model::instance()->get( $id );
			} else {
				return $module;
			}
		} else {
			return array();
		}
	}

	public function get_all_id_names(){
		return self::$_db->get_results( self::$_db->prepare( "SELECT `module_id`, `module_name` FROM " . $this->_get_table() ." WHERE `active`=%d AND `blog_id`=%d", 1, get_current_blog_id() ), OBJECT );
	}

	/**
	 * Includes Embed and Social Sharing module
	*/
	public function get_embed_id_names( $module_types = array() ) {
		$types = '';
		if ( !empty($module_types) ) {
			$temp_array = array();
			foreach( $module_types as $type ) {
				array_push( $temp_array, '`module_type` = "'. $type .'"' );
			}
			$types = ' AND ( '. implode( ' OR ', $temp_array ) . ' )';
		}
		return self::$_db->get_results( self::$_db->prepare( "SELECT `module_id`, `module_name` FROM " . $this->_get_table() ." WHERE `active`=%d AND `blog_id`=%d" . $types, 1, get_current_blog_id() ), OBJECT );
	}

	/**
	 * Social Sharing stuffs
	*/
	public function get_share_stats( $offset, $limit ) {
		$stats = self::$_db->get_results( self::$_db->prepare(" SELECT `meta_key`, `meta_value` FROM " . self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META . " WHERE `meta_key` LIKE '%s' ORDER BY `meta_value` DESC LIMIT %d, %d ", '%' . Hustle_Data::KEY_PAGE_SHARES, $offset, $limit ) );
		return array_map( array( $this, "return_wp_from_stats" ), $stats );
	}

	function return_wp_from_stats($stats){
		if( empty($stats) ) return array();
		$page_id = (int) $stats->meta_key;
		$page = get_post($page_id);

		// page_id = 0 assume it as homepage
		if ( is_null($page) ) {
			$page = new stdClass();
			$page->ID = 0;
		}
		$page->page_shares = $stats->meta_value;
		return $page;
	}

	public function get_total_share_stats() {
		$stats = self::$_db->get_col( self::$_db->prepare(" SELECT COUNT(`meta_key`) FROM " . self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META . " WHERE `meta_key` LIKE '%s' ", '%' . Hustle_Data::KEY_PAGE_SHARES) );
		return ( isset($stats[0]) )
			? $stats[0]
			: 0;
	}

	public function update_page_share( $page_id ) {
		$meta_key = $page_id . '_' . Hustle_Data::KEY_PAGE_SHARES;
		$shares = self::$_db->get_col( self::$_db->prepare(" SELECT SUM(`meta_value`) AS total FROM " . self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META . " WHERE `meta_key` = '%s' ", $meta_key) );

		if ( isset($shares[0]) ) {
			// update
			$shared = ( (int) $shares[0] ) + 1;
			return self::$_db->update(self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META, array(
				"meta_value" => $shared
			), array(
				'module_id' => $page_id,
				'meta_key' => $meta_key
			),
				array(
					"%d",
				),
				array(
					"%d",
					"%s"
				)
			);
		} else {
			// add new
			return self::$_db->insert( self::$_db->base_prefix . Hustle_Db::TABLE_HUSTLE_MODULES_META, array(
				"module_id" => $page_id,
				"meta_key" => $meta_key,
				"meta_value" => 1
			), array(
				"%d",
				"%s",
				"%d",
			));
		}
	}

	public function get_hustle_20_optins() {
		$optins = self::$_db->get_results( "SELECT * FROM `". self::$_db->base_prefix ."optins`" );
		foreach( $optins as $optin ) {

			// common properties for modules
			$optin->settings = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'settings' ) );
			$optin->settings = ( isset( $optin->settings[0] ) ) ? $optin->settings[0] : '';

			$optin->shortcode_id = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'shortcode_id' ) );
			$optin->shortcode_id = ( isset( $optin->shortcode_id[0] ) ) ? $optin->shortcode_id[0] : '';

			$optin->graph_color = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'graph_color' ) );
			$optin->graph_color = ( isset( $optin->graph_color[0] ) ) ? $optin->graph_color[0] : '';

			$optin->track_types = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'track_types' ) );
			$optin->track_types = ( isset( $optin->track_types[0] ) ) ? $optin->track_types[0] : '';

			$optin->test_types = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'test_types' ) );
			$optin->test_types = ( isset( $optin->test_types[0] ) ) ? $optin->test_types[0] : '';

			$optin->widget_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'widget_view' ) );

			$optin->widget_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'widget_conversion' ) );

			$optin->shortcode_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'shortcode_view' ) );

			$optin->shortcode_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'shortcode_conversion' ) );

			if ( $optin->optin_provider != 'social_sharing' ) {

				$optin->design = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'design' ) );
				$optin->design = ( isset( $optin->design[0] ) ) ? $optin->design[0] : '';

				// only for optin and custom content
				$optin->popup_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'popup_view' ) );

				$optin->popup_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'popup_conversion' ) );

				$optin->slidein_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'slide_in_view' ) );

				$optin->slidein_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'slide_in_conversion' ) );

				$optin->after_content_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'after_content_view' ) );

				$optin->after_content_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'after_content_conversion' ) );

			} else {
				// only for social sharing
				$optin->floating_social_views = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'floating_social_view' ) );

				$optin->floating_social_conversions = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'floating_social_conversion' ) );

				$optin->page_shares = self::$_db->get_results( self::$_db->prepare( "SELECT meta_key, meta_value FROM `" . self::$_db->base_prefix . "optin_meta` WHERE optin_id = %d AND meta_key like '%s'", $optin->optin_id, '%_page_shares' ) );
			}

			// specific for each module
			if ( $optin->optin_provider == 'custom_content' ) {
				// custom content
				$optin->subtitle = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'subtitle' ) );
				$optin->subtitle = ( isset( $optin->subtitle[0] ) ) ? $optin->subtitle[0] : '';

				$optin->popup = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'popup' ) );
				$optin->popup = ( isset( $optin->popup[0] ) ) ? $optin->popup[0] : '';

				$optin->slide_in = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'slide_in' ) );
				$optin->slide_in = ( isset( $optin->slide_in[0] ) ) ? $optin->slide_in[0] : '';

				$optin->after_content = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'after_content' ) );
				$optin->after_content = ( isset( $optin->after_content[0] ) ) ? $optin->after_content[0] : '';

			} else if ( $optin->optin_provider == 'social_sharing' ) {
				// social sharing
				$optin->services = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'services' ) );
				$optin->services = ( isset( $optin->services[0] ) ) ? $optin->services[0] : '';

				$optin->appearance = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'appearance' ) );
				$optin->appearance = ( isset( $optin->appearance[0] ) ) ? $optin->appearance[0] : '';

				$optin->floating_social = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'floating_social' ) );
				$optin->floating_social = ( isset( $optin->floating_social[0] ) ) ? $optin->floating_social[0] : '';
			} else {
				// optins
				$optin->provider_args = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'provider_args' ) );
				$optin->provider_args = ( isset( $optin->provider_args[0] ) ) ? $optin->provider_args[0] : '';

				$optin->api_key = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'api_key' ) );
				$optin->api_key = ( isset( $optin->api_key[0] ) ) ? $optin->api_key[0] : '';

				$optin->save_to_local_collection = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'save_to_local_collection' ) );
				$optin->save_to_local_collection = ( isset( $optin->save_to_local_collection[0] ) ) ? $optin->save_to_local_collection[0] : '';

				$optin->error_logs = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'error_logs' ) );
				$optin->error_logs = ( isset( $optin->error_logs[0] ) ) ? $optin->error_logs[0] : '';

				$optin->subscription = self::$_db->get_col( self::$_db->prepare( "SELECT meta_value FROM `". self::$_db->base_prefix ."optin_meta` WHERE optin_id = %d AND meta_key = '%s'", $optin->optin_id, 'subscription' ) );
			}

		}
		return $optins;
	}
}