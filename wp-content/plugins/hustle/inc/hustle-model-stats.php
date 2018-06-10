<?php
/**
 *
 * @property int $views_count
 * @property int $conversions_count
 * @property int $conversion_rate
 * @property Hustle_Model_Stats $slide_in
 * @property Hustle_Model_Stats $popup
 * @property Hustle_Model_Stats $after_content
 *
 * Class Hustle_Model_Stats
 */
class Hustle_Model_Stats extends Hustle_Data
{
	/**
	 * @var Hustle_Model $_module
	 */
	private $_module;

	/**
	 * Type of module we are getting stats for
	 *
	 * @var string $_module_type
	 */
	public $_module_type;

	/**
	 * Inits class
	 *
	 * Hustle_Model_Stats constructor.
	 * @param Hustle_Model $module
	 * @param $module_type
	 */
	function __construct( Hustle_Model $module, $module_type ){
		parent::__construct();
		$this->_module = $module;
		$this->_module_type = $module_type;

	}

	/**
	 * Returns stat key
	 *
	 * @param $suffix
	 * @return string
	 */
	private function _get_key( $suffix ){
		return $this->_module_type . "_" . $suffix;
	}

	/**
	 * Fetches views count from db
	 *
	 * @return int
	 */
	function get_views_count(){
		return (int) $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT COUNT(meta_id) FROM " . $this->get_meta_table() . " WHERE `module_id`=%d AND `meta_key`=%s ", $this->_module->id,  $this->_get_key( self::KEY_VIEW ) ) );
	}

	/**
	 * Fetches conversions count from db
	 *
	 * @return int
	 */
	function get_conversions_count(){
		return (int) $this->_wpdb->get_var( $this->_wpdb->prepare( "SELECT COUNT(meta_id) FROM " . $this->get_meta_table() . " WHERE `module_id`=%d AND `meta_key`=%s ", $this->_module->id,  $this->_get_key( self::KEY_CONVERSION )  ) );
	}

	/**
	 * Calculates and Returns conversion rate
	 *
	 * @return float|int
	 */
	function get_conversion_rate(){
		return (int) $this->views_count > 0 ?  round( ( $this->conversions_count / $this->views_count )  * 100, 2 ) : 0;
	}

	/**
	 * Fetches conversion data from db
	 *
	 * @return array
	 */
	function get_conversion_data(){
		return (object) $this->_wpdb->get_results( $this->_wpdb->prepare( "SELECT * FROM " . $this->get_meta_table() . " WHERE `module_id`=%d AND `meta_key`=%s ", $this->_module->id,  $this->_get_key( self::KEY_CONVERSION )  ) );
	}
}