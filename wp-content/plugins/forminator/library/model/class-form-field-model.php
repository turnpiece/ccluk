<?php
/**
 * Author: Hoang Ngo
 */

class Forminator_Form_Field_Model {
	/**
	 * This should be unique
	 * @var
	 */
	public $slug;

	/**
	 * This is parent form ID, optional
	 * @int
	 */
	public $formID;

	/**
	 * This contains all the parsed json data from frontend form
	 * @var array
	 */
	protected $raw = array();

	/**
	 * @since 1.0
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function __get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}

		$value = isset( $this->raw[ $name ] ) ? $this->raw[ $name ] : null;
		$value = apply_filters( 'forminator_get_field_' . $this->slug, $value, $this->formID, $name );

		return $value;
	}

	/**
	 * @since 1.0
	 * @param $name
	 * @param $value
	 */
	public function __set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;

			return;
		}
		$value              = apply_filters( 'forminator_set_field_' . $this->slug, $value, $this->formID, $name );
		$this->raw[ $name ] = $value;
	}

	/**
	 * To JSON
	 *
	 * @since 1.0
	 * @return string
	 */
	public function toJSON() {
		return json_encode( $this->toArray() );
	}

	/**
	 * To array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function toArray() {
		$data = array(
			'id'         => $this->slug,
			'element_id' => $this->slug,
			'formID'     => $this->formID
		);

		return array_merge( $data, $this->raw );
	}

	/**
	 * @since 1.0
	 * @return array
	 */
	public function toFormattedArray() {
		return $this->raw;
	}

	/**
	 * @since 1.0
	 * @param $data
	 */
	public function import( $data ) {
		if( empty( $data ) ) return;

		foreach ( $data as $key => $val ) {
			$this->$key = $val;
		}
	}
}