<?php

abstract class Hustle_Meta {

	protected  $data;
	protected  $model;

	function __construct( array $data, Hustle_Model $model ){
		$this->data = $data;
		$this->model = $model;
	}

	/**
	 * Implements getter magic method
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $field
	 * @return mixed
	 */
	function __get( $field ){

		if( method_exists( $this, "get_" . $field ) )
			return $this->{"get_". $field}();

		if( !empty( $this->data ) && isset( $this->data[ $field ] ) ){
			$val = $this->data[ $field ];
			if( $val  === "true" )
				return true;
			if( $val === "false" )
				return false;
			if( $val === "null" )
				return null;

			return $val;
		}

	}

	function to_object(){
		return (object) $this->to_array();
	}

	function to_array(){
		if( isset( $this->defaults ) && is_array( $this->defaults   ) )
			return wp_parse_args( $this->data,  $this->defaults );

		return $this->data;
	}

	function to_json(){
		return json_encode( $this->to_array() );
	}

}










