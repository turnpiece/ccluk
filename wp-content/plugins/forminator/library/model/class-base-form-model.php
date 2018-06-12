<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Author: Hoang Ngo
 */
abstract class Forminator_Base_Form_Model {
	const META_KEY = 'forminator_form_meta';
	/**
	 * Form ID
	 * @int
	 */
	public $id;
	/**
	 * Form name
	 * @string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $client_id;

	/**
	 * Contain fields of this form
	 * @array
	 */
	public $fields = array();

	/**
	 * Form settings
	 * array
	 */
	public $settings = array();

	/**
	 * WP_Post
	 */
	public $raw;
	/**
	 * This post type
	 * @string
	 */
	protected $post_type;

	/**
	 * Save form
	 *
	 * @since 1.0
	 * @param bool $clone
	 *
	 * @return mixed
	 */
	public function save( $clone = false ) {
		//todo use save_post for saving the form and update_post_meta for saving fields
		//prepare the data
		$maps     = array_merge( $this->getDefaultMaps(), $this->getMaps() );
		$post_data = array();
		$meta_data = array();

		if( !empty( $maps ) ) {
			foreach ( $maps as $map ) {
				$attribute = $map['property'];
				if ( 'post' === $map['type'] ) {
					$post_data[ $map['field'] ] = $this->$attribute;
				} else {
					if ( 'fields' === $map['field'] ) {
						$meta_data[ $map['field'] ] = $this->getFieldsAsArray();
					} else {
						$meta_data[ $map['field'] ] = $this->$attribute;
					}
				}
			}
		}

		$post_data['post_type']   = $this->post_type;
		$post_data['post_status'] = 'publish';

		//storing
		if ( is_null( $this->id ) ) {
			$id = wp_insert_post( $post_data );
		} else {
			$id = wp_update_post( $post_data );
		}

		// If cloned we have to update the fromID
		if( $clone ) {
			$meta_data['settings']['formID'] = $id;
		}

		update_post_meta( $id, self::META_KEY, $meta_data );

		return $id;
	}

	/**
	 * @since 1.0
	 * @return Forminator_Form_Field_Model[]
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @since 1.0
	 * @param $property
	 * @param $name
	 * @param $array
	 */
	public function setVarInArray( $property, $name, $array ) {
		$val             = isset( $array[ $name ] ) ? $array[ $name ] : null;
		$val             = sanitize_title( $val );
		$this->$property = $val;
	}

	/**
	 * Add field
	 *
	 * @since 1.0
	 * @param $field
	 */
	public function addField( $field ) {
		$this->fields[] = $field;
	}

	/**
	 * Get field
	 *
	 * @since 1.0
	 * @param $slug
	 *
	 * @return Forminator_Form_Field|null
	 */
	public function getField( $slug ) {
		//get a field and return as object
		return isset( $this->fields[ $slug ] ) ? $this->fields[ $slug ] : null;
	}

	/**
	 * Remove field
	 *
	 * @since 1.0
	 * @param $slug
	 */
	public function removeField( $slug ) {
		unset( $this->fields[ $slug ] );
	}

	/**
	 * Clear fields
	 *
	 * @since 1.0
	 */
	public function clearFields() {
		$this->fields = array();
	}

	/**
	 * Load model
	 *
	 * @since 1.0
	 * @param $id
	 *
	 * @return bool|Forminator_Base_Form_Model
	 */
	public function load( $id, $callback = false ) {
		$post = get_post( $id );

		if ( ! is_object( $post ) ) {
			// If we haven't saved yet, fallback to latest ID and replace the data
			if( $callback ) {
				$id = $this->get_latest_id();
				$post = get_post( $id );

				if ( ! is_object( $post ) ) {
					return false;
				}
			} else {
				return false;
			}
		}

		return $this->_load( $post );
	}

	/**
	 * Return latest id for the post_type
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_latest_id() {
		$id   = 1;
		$args = array(
			'post_type'   => $this->post_type,
			'numberposts' => 1,
			'fields'      => 'ids'
		);

		$post = get_posts( $args );

		if( isset( $post[0] ) ) {
			$id = $post[0];
		}

		return $id;
	}

	/**
	 * Count all form types
	 *
	 * @since 1.0
	 * @return int
	 */
	public function countAll() {
		$count_posts = wp_count_posts( $this->post_type );
		return $count_posts->publish;
	}

	/**
	 * Get all
	 *
	 * @since 1.0
	 * @param int $currentPage
	 *
	 * @return Forminator_Base_Form_Model[]
	 */
	public function getAll( $current_page = 1 ) {
		$args   = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => forminator_form_view_per_page(),
			'paged'          => $current_page
		);
		$query  = new WP_Query( $args );
		$models = array();

		foreach ( $query->posts as $post ) {
			$models[] = $this->_load( $post );
		}

		return array(
			'totalPages'   => $query->max_num_pages,
			'totalRecords' => $query->post_count,
			'models'       => $models
		);
	}

	/**
	 * Get Models
	 *
	 * @since 1.0
	 * @param int $total - the total. Defaults to 4
	 *
	 * @return array $models
	 */
	public function getModels( $total = 4 ) {
		$args   = array(
			'post_type'      => $this->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => $total,
			'order' 		 => 'DESC',
		);
		$query  = new WP_Query( $args );
		$models = array();

		foreach ( $query->posts as $post ) {
			$models[] = $this->_load( $post );
		}

		return $models;
	}

	/**
	 * @since 1.0
	 * @param $post
	 *
	 * @return mixed
	 */
	private function _load( $post ) {
		if ( $this->post_type === $post->post_type ) {
			$class  = get_class( $this );
			$object = new $class();
			$meta   = get_post_meta( $post->ID, self::META_KEY, true );
			$maps   = array_merge( $this->getDefaultMaps(), $this->getMaps() );

			if( !empty( $maps ) ) {
				foreach ( $maps as $map ) {
					$attribute = $map['property'];
					if ( 'post' === $map['type'] ) {
						$att                = $map['field'];
						$object->$attribute = $post->$att;
					} else {
						if ( 'fields' === $map['field'] ) {
							foreach ( $meta['fields'] as $fieldData ) {
								$field         = new Forminator_Form_Field_Model();
								$field->formID = $post->ID;
								$field->slug   = $fieldData['id'];
								unset( $fieldData['id'] );
								$field->import( $fieldData );
								$object->addField( $field );
							}
						} else {
							if ( isset( $meta[ $map['field'] ] ) ) {
								$object->$attribute = $meta[ $map['field'] ];
							}
						}
					}
				}
			}

			$object->raw = $post;

			return $object;
		}

		return false;
	}

	/**
	 * Return fields as array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getFieldsAsArray() {
		$arr = array();

		if( empty( $this->fields ) ) return $arr;

		foreach ( $this->fields as $field ) {
			$arr[] = $field->toArray();
		}

		return $arr;
	}

	/**
	 * Return fields grouped
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getFieldsGrouped() {
		$wrappers = array();

		if( empty( $this->fields ) ) return $wrappers;

		foreach ( $this->fields as $field ) {
			if ( ! isset( $wrappers[ $field->formID ] ) ) {
				$wrappers[ $field->formID ] = array(
					'wrapper_id' => $field->formID,
					'fields'     => array()
				);
			}

			$wrappers[ $field->formID ]['fields'][] = $field->toFormattedArray();
		}
		$wrappers = array_values( $wrappers );

		return $wrappers;
	}

	/**
	 * Model to array
	 *
	 * @since 1.0
	 * @return array
	 */
	public function toArray() {
		$data = array();
		$maps = array_merge( $this->getDefaultMaps(), $this->getMaps() );

		if( empty( $maps ) ) return $data;

		foreach ( $maps as $map ) {
			$property          = $map['property'];
			$data[ $property ] = $this->$property;
		}

		return $data;
	}

	/**
	 * Model to json
	 *
	 * @since 1.0
	 * @return mixed|string
	 */
	public function toJson() {
		$wrappers = array();

		if( !empty( $this->fields ) ) {
			foreach ( $this->fields as $field ) {
				$wrappers[] = $field->toJSON();
			}
		}

		$settings = $this->settings;
		$data     = array_merge( array(
			'wrappers' => array(
				'fields' => $wrappers
			)
		), $settings );
		$ret      = array(
			'formName' => $this->name,
			'data'     => $data
		);

		return wp_json_encode( $ret );
	}

	/**
	 * In here we will define how we store the properties
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getDefaultMaps() {
		return array(
			array(
				'type'     => 'post',
				'property' => 'id',
				'field'    => 'ID'
			),
			array(
				'type'     => 'post',
				'property' => 'name',
				'field'    => 'post_title'
			),
			array(
				'type'     => 'meta',
				'property' => 'fields',
				'field'    => 'fields'
			),
			array(
				'type'     => 'meta',
				'property' => 'settings',
				'field'    => 'settings'
			),
			array(
				'type'     => 'meta',
				'property' => 'client_id',
				'field'    => 'client_id'
			),
		);
	}

	/**
	 * This should be get override by children
	 *
	 * @since 1.0
	 * @return array
	 */
	public function getMaps() {
		return array();
	}

	/**
	 * Return model
	 *
	 * @since 1.0
	 * @param string $class_name
	 *
	 * @return mixed
	 */
	public static function model( $class_name = __CLASS__ ) {
		$class = new $class_name();

		return $class;
	}

	/**
	 * Get Post Type of cpt
	 * @since 1.0.5
	 *
	 * @return mixed
	 */
	public function getPostType() {
		return $this->post_type;
	}
}