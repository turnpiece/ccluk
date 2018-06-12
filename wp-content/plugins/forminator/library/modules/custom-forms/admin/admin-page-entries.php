<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class Forminator_CForm_View_Page
 *
 * @since 1.0
 */
class Forminator_CForm_View_Page extends Forminator_Admin_Page {

	/**
	 * Current model
	 *
	 * @var Forminator_Custom_Form_Model|bool
	 */
	protected $model = false;

	/**
	 * Current form id
	 *
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Entries
	 *
	 * @var array
	 */
	protected $entries = array();

	/**
	 * Fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Visible Fields
	 *
	 * @var array
	 */
	protected $visible_fields = array();


	/**
	 * Number of checked fields
	 *
	 * @var int
	 */
	protected $checked_fields = 0;

	/**
	 * Number of total fields
	 *
	 * @var int
	 */
	protected $total_fields = 0;

	/**
	 * Per page
	 *
	 * @var int
	 */
	protected $per_page = 10;

	/**
	 * Page number
	 *
	 * @var int
	 */
	protected $page_number = 1;

	/**
	 * Total Entries
	 *
	 * @var int
	 */
	protected $total_entries = 0;

	/**
	 * @var Forminator_Addon_Abstract[]
	 */
	private static $connected_addons = null;

	/**
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function before_render() {
		if ( isset( $_REQUEST['form_id'] ) ) { // WPCS: CSRF OK
			$this->form_id  = sanitize_text_field( $_REQUEST['form_id'] );
			$this->model 	= Forminator_Custom_Form_Model::model()->load( $this->form_id );
			if ( is_object( $this->model ) ) {
				$this->fields = $this->model->getFields();
				if ( is_null( $this->fields ) ) {
					$this->fields = array();
				}
			} else {
				$this->model = false;
			}

			$pagenum				= isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0; // WPCS: CSRF OK
			$this->per_page			= forminator_form_view_per_page( 'entries' );
			$this->page_number 		= max( 1, $pagenum );
			$this->total_fields 	= count( $this->fields );
			$this->checked_fields 	= $this->total_fields;

			$form_id           = (int) $this->form_id;
			$custom_form_model = $this->model;
			$visible_fields    = $this->get_visible_fields();

			/**
			 * Fires on custom form page entries render before request and result processed
			 *
			 * @since 1.1
			 *
			 * @param int                          $form_id           Current Form ID
			 * @param Forminator_Custom_Form_Model $custom_form_model Current Form Model
			 * @param array                        $visible_fields    Visible fields on page
			 * @param int                          $pagenum           current page number
			 */
			do_action(
				'forminator_custom_form_admin_page_entries',
				$form_id,
				$custom_form_model,
				$visible_fields,
				$pagenum
			);

			$this->process_request();
			$this->prepare_results();
		}
	}

	/**
	 * Process request
	 *
	 * @since 1.0
	 */
	public function process_request() {
		if ( ! isset( $_POST['forminatorEntryNonce'] ) ) {
			return;
		}

		$nonce = $_POST['forminatorEntryNonce']; // WPCS: CSRF OK
		if ( ! wp_verify_nonce( $nonce, 'forminatorCustomFormEntries' ) ) {
			return;
		}

		if ( isset( $_POST['field'] ) ) {
			$this->visible_fields = $_POST['field'];
			$this->checked_fields = count( $this->visible_fields );
		}

		if ( isset( $_POST['entries-action'] ) || isset( $_POST['entries-action-bottom'] ) ) {
			if ( isset( $_POST['entries-action'] ) && ! empty( $_POST['entries-action'] ) ) {
				$action = $_POST['entries-action'];
			} elseif ( isset( $_POST['entries-action-bottom'] ) ) {
				$action = $_POST['entries-action-bottom'];
			}

			switch ( $action ) {
				case 'delete-all' :
					if ( isset( $_POST['entry'] ) && is_array( $_POST['entry'] ) ) {
						$entries = implode( ",", $_POST['entry'] );
						Forminator_Form_Entry_Model::delete_by_entrys( $this->model->id, $entries );
						$url = add_query_arg( '', '' );
						wp_redirect( $url );
						exit;
					}
					break;
			}
		}

		if( isset( $_POST['formninator_action'] ) ) {
			switch ( $_POST['formninator_action'] ) {
				case 'delete':
					if( isset( $_POST['id'] ) ) {
						$id = $_POST['id'];

						Forminator_Form_Entry_Model::delete_by_entrys( $this->model->id, $id );
						$url = add_query_arg( '', '' );
						wp_safe_redirect( $url );
						exit;
					}
					break;
			}
		}
	}

	/**
	 * Content boxes
	 *
	 * @since 1.0
	 */
    public function register_content_boxes() {
        $this->add_box(
			'custom-form/entries/popup/exports-list',
			__( 'Your Exports', Forminator::DOMAIN ),
			'entries-popup-exports-list',
			null,
			null,
			null
        );

        $this->add_box(
			'custom-form/entries/popup/schedule-export',
			__( 'Edit Schedule Export', Forminator::DOMAIN ),
			'entries-popup-schedule-export',
			null,
			null,
			null
		);
	}

	/**
	 * Get fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Visible fields
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_visible_fields() {
		return $this->visible_fields;
	}

	/**
	 * Return visible fields as string
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_visible_fields_as_string() {
		return implode( ',', $this->visible_fields );
	}

	/**
	 * Checked field option
	 *
	 * @since 1.0
	 * @param string $slug - the field slug
	 *
	 * @return string
	 */
	public function checked_field( $slug ) {
		if ( !empty( $this->visible_fields ) && is_array( $this->visible_fields ) ) {
			if ( in_array( $slug, $this->visible_fields, true ) ) {
				return checked( $slug, $slug );
			} else {
				return '';
			}
		}

		return checked( $slug, $slug );
	}

	/**
	 * Show a field if selected
	 *
	 * @since 1.0
	 * @param string $slug - the field slug
	 *
	 * @return bool
	 */
	public function is_selected_field( $slug ) {
		if ( !empty( $this->visible_fields ) && is_array( $this->visible_fields ) ) {
			if ( in_array( $slug, $this->visible_fields, true ) ) {
				return true;
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get model name
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_model_name() {
		if ( $this->model ) {
			return $this->model->name;
		}

		return '';
	}

	/**
	 * Fields header
	 *
	 * @since 1.0
	 * @return string
	 */
	public function fields_header() {
		printf( esc_html__( "Showing %s of %s fields", Forminator::DOMAIN ), $this->checked_fields, $this->total_fields ); // phpcs:ignore
	}

	/**
	 * Prepare results
	 *
	 * @since 1.0
	 */
	public function prepare_results() {
		if ( is_object( $this->model ) ) {
			$paged    = $this->page_number;
			$per_page = $this->per_page;
			$offset   = ( $paged - 1 ) * $per_page;

			$this->total_entries 	= Forminator_Form_Entry_Model::count_entries( $this->model->id );
			$this->entries 			= Forminator_Form_Entry_Model::list_entries( $this->model->id, $per_page, $offset );
		}
	}

	/**
	 * The total entries
	 *
	 * @since 1.0
	 * @return int
	 */
	public function total_entries() {
		return $this->total_entries;
	}

	/**
	 * Get Entries
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_entries() {
		return $this->entries;
	}

	/**
	 * Get Page Number
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_page_number() {
		return $this->page_number;
	}

	/**
	 * Get Per Page
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_per_page() {
		return $this->per_page;
	}

	/**
	 * Render entry
	 *
	 * @since 1.0
	 *
	 * @param object $item        - the entry
	 * @param string $column_name - the column name
	 *
	 * @param null   $field @since 1.0.5, optional Forminator_Form_Field_Model
	 *
	 * @return string
	 * TO-DO: replace Forminator_CForm_View_Page::render_entry() by render_entry() on other files
	 */
	public static function render_entry( $item, $column_name, $field = null ) {
		return render_entry( $item , $column_name, $field );
	}

	/**
	 * Render entry values raw
	 *
	 * @since 1.0
	 * @param object $item - the entry
	 * @param string $column_name - the column name
	 *
	 * @return mixed
	 */
	public static function render_raw_entry( $item, $column_name ) {
		$data =  $item->get_meta( $column_name, '' );
		if ( $data ) {
			if ( is_array( $data ) ) {
				$output 		= '';
				$product_cost 	= 0;
				$is_product 	= false;

				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						if ( 'file' === $key && isset( $value['file_url'] ) ) {
							$output 	.= $value['file_url'] . ", ";
						}

					} else {
						if ( !is_int( $key ) ) {
							if ( 'postdata' === $key ) {
								$output .= "$value, ";
							} else {

								if ( is_string( $key ) ) {
									if (  'product-id' === $key || 'product-quantity' === $key ) {
										if ( 0 === $product_cost ) {
											$product_cost = $value;
										} else {
											$product_cost = $product_cost * $value;
										}
										$is_product = true;
									} else {
										$output .= "$value $key , ";
									}
								}
							}
						}
					}
				}
				if ( $is_product ) {
					$output = $product_cost;
				} else {
					if ( !empty( $output ) ) {
						$output = substr( trim( $output ), 0, -1 );
					} else {
						$output = implode( ",", $data );
					}
				}
				return $output;
			} else {
				return $data;
			}
		}

		return '';
	}

	/**
	 * Get fields table
	 *
	 * @since 1.0
	 * @return Forminator_Entries_List_Table
	 */
	public function get_table() {
		return new Forminator_Entries_List_Table( array(
			'model' 			=> $this->model,
			'visible_fields' 	=> $this->visible_fields
		) );
	}

	public function bulk_actions( $position = 'top' ) { ?>

		<div class="fui-bulk-actions">

			<select class="fui-select-small" name="<?php echo ( 'top' === $position ) ? 'entries-action' : 'entries-action-bottom'; ?>">
				<option value=""><?php esc_html_e( "Bulk Actions", Forminator::DOMAIN ); ?></option>
				<option value="delete-all"><?php esc_html_e( "Delete Entries", Forminator::DOMAIN ); ?></option>
			</select>

			<button class="sui-button"><?php esc_html_e( "Apply", Forminator::DOMAIN ); ?></button>

		</div>

	<?php
	}

	/**
	 * Pagination
	 *
	 * @since 1.0
	 */
	public function paginate() {
		$count = $this->total_entries();
		forminator_list_pagination( $count, 'entries' );
	}

	/**
	 * Mimic from export
	 *
	 * @see Forminator_Export::get_custom_form_export_mappers()
	 * TODO: decouple this function so it can be called on multiple occasions (export, entries render) with single place to update
	 *
	 * @return array
	 */
	private function build_fields_mappers() {
		/** @var  Forminator_Custom_Form_Model $model */
		$model               = $this->model;
		$fields              = $model->getFields();
		$visible_fields      = $this->get_visible_fields();
		$ignored_field_types = Forminator_Form_Entry_Model::ignored_fields();

		/** @var  Forminator_Form_Field_Model $fields */
		$mappers = array(
			array(
				// read form model's property
				'property' => 'entry_id', // must be on entries
				'label'    => __( 'ID', Forminator::DOMAIN ),
				'type'     => 'entry_entry_id',
			),
			array(
				// read form model's property
				'property' => 'time_created', // must be on entries
				'label'    => __( 'Date Submitted', Forminator::DOMAIN ),
				'type'     => 'entry_time_created',
			),
		);

		foreach ( $fields as $field ) {
			$field_type = $field->__get( 'type' );

			if ( in_array( $field_type, $ignored_field_types, true ) ) {
				continue;
			}

			if ( ! empty( $visible_fields ) ) {
				if ( ! in_array( $field->slug, $visible_fields, true ) ) {
					continue;
				}
			}


			// base mapper for every field
			$mapper             = array();
			$mapper['meta_key'] = $field->slug;
			$mapper['label']    = $field->get_label_for_entry();
			$mapper['type']     = $field_type;


			// fields that should be displayed as multi column (sub_metas)
			if ( 'name' === $field_type ) {
				$is_multiple_name = filter_var( $field->__get( 'multiple_name' ), FILTER_VALIDATE_BOOLEAN );
				if ( $is_multiple_name ) {
					$prefix_enabled      = filter_var( $field->__get( 'prefix' ), FILTER_VALIDATE_BOOLEAN );
					$first_name_enabled  = filter_var( $field->__get( 'fname' ), FILTER_VALIDATE_BOOLEAN );
					$middle_name_enabled = filter_var( $field->__get( 'mname' ), FILTER_VALIDATE_BOOLEAN );
					$last_name_enabled   = filter_var( $field->__get( 'lname' ), FILTER_VALIDATE_BOOLEAN );
					// at least one sub field enabled
					if ( $prefix_enabled || $first_name_enabled || $middle_name_enabled || $last_name_enabled ) {
						// sub metas
						$mapper['sub_metas'] = array();
						if ( $prefix_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'prefix' );
							$label                 = $field->__get( 'prefix_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'prefix',
								'label' => ( $label ? $label : $default_label ),
							);
						}

						if ( $first_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'first-name' );
							$label                 = $field->__get( 'fname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'first-name',
								'label' => ( $label ? $label : $default_label ),
							);
						}

						if ( $middle_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'middle-name' );
							$label                 = $field->__get( 'mname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'middle-name',
								'label' => ( $label ? $label : $default_label ),
							);
						}
						if ( $middle_name_enabled ) {
							$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'last-name' );
							$label                 = $field->__get( 'lname_label' );
							$mapper['sub_metas'][] = array(
								'key'   => 'last-name',
								'label' => ( $label ? $label : $default_label ),
							);
						}
					} else {
						// if no subfield enabled when multiple name remove mapper (means dont show it on export)
						$mapper = array();
					}
				}
			} elseif ( 'address' === $field_type ) {
				$street_enabled  = filter_var( $field->__get( 'street_address' ), FILTER_VALIDATE_BOOLEAN );
				$line_enabled    = filter_var( $field->__get( 'address_line' ), FILTER_VALIDATE_BOOLEAN );
				$city_enabled    = filter_var( $field->__get( 'address_city' ), FILTER_VALIDATE_BOOLEAN );
				$state_enabled   = filter_var( $field->__get( 'address_state' ), FILTER_VALIDATE_BOOLEAN );
				$zip_enabled     = filter_var( $field->__get( 'address_zip' ), FILTER_VALIDATE_BOOLEAN );
				$country_enabled = filter_var( $field->__get( 'address_country' ), FILTER_VALIDATE_BOOLEAN );
				if ( $street_enabled || $line_enabled || $city_enabled || $state_enabled || $zip_enabled || $country_enabled ) {
					$mapper['sub_metas'] = array();
					if ( $street_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'street_address' );
						$label                 = $field->__get( 'street_address_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'street_address',
							'label' => ( $label ? $label : $default_label ),
						);
					}
					if ( $line_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'address_line' );
						$label                 = $field->__get( 'address_line_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'address_line',
							'label' => ( $label ? $label : $default_label ),
						);
					}
					if ( $city_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'city' );
						$label                 = $field->__get( 'address_city_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'city',
							'label' => ( $label ? $label : $default_label ),
						);
					}
					if ( $state_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'state' );
						$label                 = $field->__get( 'address_state_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'state',
							'label' => ( $label ? $label : $default_label ),
						);
					}
					if ( $zip_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'zip' );
						$label                 = $field->__get( 'address_zip_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'zip',
							'label' => ( $label ? $label : $default_label ),
						);
					}
					if ( $country_enabled ) {
						$default_label         = Forminator_Form_Entry_Model::translate_suffix( 'country' );
						$label                 = $field->__get( 'address_country_label' );
						$mapper['sub_metas'][] = array(
							'key'   => 'country',
							'label' => ( $label ? $label : $default_label ),
						);
					}
				} else {
					// if no subfield enabled when multiple name remove mapper (means dont show it on export)
					$mapper = array();
				}
			}

			if ( ! empty( $mapper ) ) {
				$mappers[] = $mapper;
			}
		}

		return $mappers;
	}

	/**
	 * Nested Mappers
	 *
	 * @var array
	 */
	protected $fields_mappers = array();

	/**
	 * Flatten version of mappers
	 *
	 * @var array
	 */
	protected $flatten_field_mappers = array();

	/**
	 * Get Fields Mappers based on current state of form
	 *
	 * @return array
	 */
	public function get_fields_mappers() {
		if ( empty( $this->fields_mappers ) ) {
			$this->fields_mappers = $this->build_fields_mappers();
		}

		return $this->fields_mappers;
	}

	/**
	 * @return array
	 */
	public function get_flatten_fields_mappers() {
		if ( empty( $this->flatten_field_mappers ) ) {
			$fields_mappers = $this->get_fields_mappers();
			//flatten field mappers for multi field
			$flatten_fields_mappers = array();
			foreach ( $fields_mappers as $fields_mapper ) {
				if ( ! isset( $fields_mapper['sub_metas'] ) ) {
					$flatten_fields_mappers[] = $fields_mapper;
				} else {
					foreach ( $fields_mapper['sub_metas'] as $sub_meta ) {
						$sub_meta['parent']       = $fields_mapper;
						$flatten_fields_mappers[] = $sub_meta;
					}
				}
			}

			$this->flatten_field_mappers = $flatten_fields_mappers;

		}

		return $this->flatten_field_mappers;
	}

	/**
	 * Build Html Entries Header
	 */
	public function entries_header() {
		$flatten_fields_mappers = $this->get_flatten_fields_mappers();

		//start from 2, since first two is ID and Date
		//length is 2 because we only display first two fields only
		$fields_headers = array_slice( $flatten_fields_mappers, 2, 2 );
		//minus by header fields
		$actual_num_fields = count( $flatten_fields_mappers ) - 2;
		$fields_left       = $actual_num_fields - count( $fields_headers );
		?>
		<thead>
		<th><label class="sui-checkbox">
				<input type="checkbox" id="wpf-cform-check_all">
				<span></span>

				<div class="sui-description"><?php esc_html_e( 'ID', Forminator::DOMAIN ); ?></div>
			</label>
		</th>
		<th colspan="3"><?php esc_html_e( 'Date Submitted', Forminator::DOMAIN ); ?></th>
		<?php
		foreach ( $fields_headers as $header ) {
			?>
			<th colspan="2"><?php echo esc_html( $header['label'] ); ?></th>
			<?php
		}

		if ( $fields_left > 0 ) {
			?>
			<th colspan="3" data-num-hidden-fields="<?php echo $fields_left; // WPCS: XSS ok. ?>"></th>
			<?php
		}
		?>

		</thead>
		<?php
	}

	/**
	 * @return array
	 */
	public function entries_iterator() {
		/**
		 * @example
		 * {
		 *  id => 'ENTRY_ID'
		 *  summary = [
		 *      'num_fields_left' => true/false,
		 *      'items' => [
		 *          [
		 *              'colspan' => 2/...,
		 *              'value' => '----',
		 *          ]
		 *          [
		 *              'colspan' => 2/...
		 *              value' => '----',
		 *          ]
		 *      ],
		 *  ],
		 *  detail = [
		 *      'colspan' => '',
		 *      'items' => [
		 *          [
		 *              'label' => '----',
		 *              'value' => '-----'
		 *              'sub_entries' => [
		 *                  [
		 *                      'label' => '----',
		 *                      'value' => '-----'
		 *                  ]
		 *              ]
		 *          ]
		 *          [
		 *              'label' => '----',
		 *              'value' => '-----'
		 *          ]
		 *      ],
		 * ]
		 * }
		 */
		$entries_iterator = array();

		$total_colspan          = 4; //Colspan for ID + Date Submitted
		$fields_mappers         = $this->get_fields_mappers();
		$flatten_fields_mappers = $this->get_flatten_fields_mappers();

		//start from 2, since first two is ID and Date
		//length is 2 because we only display first two fields only
		$fields_headers    = array_slice( $flatten_fields_mappers, 2, 2 );
		$actual_num_fields = count( $flatten_fields_mappers ) - 2;
		$fields_left       = $actual_num_fields - count( $fields_headers );

		$total_colspan += ( count( $fields_headers ) * 2 ); // 2 for each header colspan
		if ( $fields_left > 0 ) {
			$total_colspan += 3;
		}

		// all headers including Id + Date, start from 0 and max is 4
		$headers = array_slice( $flatten_fields_mappers, 0, 4 );

		$numerator_id = $this->total_entries;
		if ( $this->page_number > 1 ) {
			$numerator_id = $this->total_entries - ( ( $this->page_number - 1 ) * $this->per_page );
		}

		foreach ( $this->entries as $entry ) {
			/**@var Forminator_Form_Entry_Model $entry */

			//create placeholder
			$iterator = array(
				'id'       => $numerator_id,
				'entry_id' => $entry->entry_id,
				'summary'  => array(),
				'detail'   => array(),
			);

			$iterator['summary']['num_fields_left'] = $fields_left;
			$iterator['summary']['items']           = array();

			$iterator['detail']['colspan'] = $total_colspan;
			$iterator['detail']['items']   = array();

			// Build array for summary row
			$summary_items = array();
			foreach ( $headers as $header ) {
				$colspan = 2;
				if ( isset( $header['type'] ) && 'entry_entry_id' === $header['type'] ) {
					$summary_items[] = array(
						'colspan' => 1,
						'value'   => $numerator_id,
					);
					continue;
				} elseif ( isset( $header['type'] ) && 'entry_time_created' === $header['type'] ) {
					$colspan = 3;
				}

				if ( isset( $header['parent'] ) ) {
					$value = $this->get_entry_field_value( $entry, $header['parent'], $header['key'], false, 100 );
				} else {
					$value = $this->get_entry_field_value( $entry, $header, '', false, 100 );
				}
				$summary_items[] = array(
					'colspan' => $colspan,
					'value'   => $value,
				);
			}

			// Build array for -content row
			$detail_items = array();
			foreach ( $fields_mappers as $mapper ) {
				//skip entry id
				if ( isset( $mapper['type'] ) && 'entry_entry_id' === $mapper['type'] ) {
					continue;
				}

				$label       = $mapper['label'];
				$value       = '';
				$sub_entries = array();

				if ( ! isset( $mapper['sub_metas'] ) ) {
					$value = $this->get_entry_field_value( $entry, $mapper, '', true );
				} else {
					if ( ! empty( $mapper['sub_metas'] ) ) {
						foreach ( $mapper['sub_metas'] as $sub_meta ) {
							$sub_entries[] = array(
								'label' => $sub_meta['label'],
								'value' => $this->get_entry_field_value( $entry, $mapper, $sub_meta['key'], true ),
							);
						}
					}
				}
				$detail_items[] = array(
					'label'       => $label,
					'value'       => $value,
					'sub_entries' => $sub_entries,
				);

			}

			//Additional render for addons
			$addons_detail_items = $this->attach_addon_on_render_entry( $entry );
			$detail_items        = array_merge( $detail_items, $addons_detail_items );

			$iterator['summary']['items'] = $summary_items;
			$iterator['detail']['items']  = $detail_items;

			$entries_iterator[] = $iterator;
			$numerator_id--;
		}


		return $entries_iterator;
	}

	/**
	 * Get entry field value helper
	 *
	 * @param Forminator_Form_Entry_Model $entry
	 * @param                             $mapper
	 * @param string                      $sub_meta_key
	 * @param bool                        $allow_html
	 * @param int                         $truncate
	 *
	 * @return string
	 */
	private function get_entry_field_value( $entry, $mapper, $sub_meta_key = '', $allow_html = false, $truncate = PHP_INT_MAX ) {
		/** @var Forminator_Form_Entry_Model $entry */
		if ( isset( $mapper['property'] ) ) {
			if ( property_exists( $entry, $mapper['property'] ) ) {
				$property = $mapper['property'];
				// casting property to string
				$value = (string) $entry->$property;
			} else {
				$value = '';
			}
		} else {
			$meta_value = $entry->get_meta( $mapper['meta_key'], '' );
			// meta_key based
			if ( ! isset( $mapper['sub_metas'] ) ) {
				$value = Forminator_Form_Entry_Model::meta_value_to_string( $mapper['type'], $meta_value, $allow_html, $truncate );
			} else {
				if ( empty( $sub_meta_key ) ) {
					$value = '';
				} else {
					if ( isset( $meta_value[ $sub_meta_key ] ) && ! empty( $meta_value[ $sub_meta_key ] ) ) {
						$value      = $meta_value[ $sub_meta_key ];
						$field_type = $mapper['type'] . '.' . $sub_meta_key;
						$value      = Forminator_Form_Entry_Model::meta_value_to_string( $field_type, $value, $allow_html, $truncate );
					} else {
						$value = '';
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Executor of adding additional items on entry page
	 *
	 * @see   Forminator_Addon_Form_Hooks_Abstract::on_render_entry()
	 * @since 1.1
	 *
	 * @param Forminator_Form_Entry_Model $entry_model
	 *
	 * @return array
	 */
	private function attach_addon_on_render_entry( Forminator_Form_Entry_Model $entry_model ) {
		$additonal_items = array();
		//find is_form_connected
		$connected_addons = $this->get_connected_addons();

		foreach ( $connected_addons as $connected_addon ) {
			try {
				$form_hooks = $connected_addon->get_addon_form_hooks( $this->form_id );
				$meta_data  = forminator_find_addon_meta_data_from_entry_model( $connected_addon, $entry_model );

				$addon_additional_items = $form_hooks->on_render_entry( $entry_model, $meta_data );// run and forget
				$addon_additional_items = self::format_addon_additional_items( $addon_additional_items );
				$additonal_items        = array_merge( $additonal_items, $addon_additional_items );
			} catch ( Exception $e ) {
				forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to on_render_entry', $e->getMessage() );
			}
		}

		return $additonal_items;
	}

	/**
	 * Ensuring additional items for addons met the entries data requirement
	 * Format used is,
	 * - label
	 * - value
	 * - subentries[]
	 *      - label
	 *      - value
	 *
	 * @since 1.1
	 *
	 * @param  array $addon_additional_items
	 *
	 * @return mixed
	 */
	private static function format_addon_additional_items( $addon_additional_items ) {
		//to `name` and `value` basis
		$formatted_additional_items = array();
		if ( ! is_array( $addon_additional_items ) ) {
			return array();
		}

		foreach ( $addon_additional_items as $additional_item ) {
			// make sure label and value exist, without it, it will display empty row, so leave it
			if ( ! isset( $additional_item['label'] ) || ! isset( $additional_item['value'] ) ) {
				continue;
			}
			$sub_entries = array();

			// do below check if sub_entries available
			if ( isset( $additional_item['sub_entries'] ) && is_array( $additional_item['sub_entries'] ) ) {
				foreach ( $additional_item['sub_entries'] as $sub_entry ) {
					// make sure label and value exist, without it, it will display empty row, so leave it
					if ( ! isset( $sub_entry['label'] ) || ! isset( $sub_entry['value'] ) ) {
						continue;
					}
					$sub_entries[] = array(
						'label' => $sub_entry['label'],
						'value' => $sub_entry['value'],
					);
				}
			}

			$formatted_additional_items[] = array(
				'label'       => $additional_item['label'],
				'value'       => $additional_item['value'],
				'sub_entries' => $sub_entries,
			);
		}

		return $formatted_additional_items;
	}

	/**
	 * Get Connected Addons on current form, avoid overhead for checking connected addons many times
	 *
	 * @since 1.1
	 *
	 * @return array|Forminator_Addon_Abstract[]
	 */
	public function get_connected_addons() {
		if ( is_null( self::$connected_addons ) ) {
			self::$connected_addons = array();

			$connected_addons = forminator_get_addons_instance_connected_with_form( $this->form_id );
			foreach ( $connected_addons as $connected_addon ) {
				try {
					$form_hooks = $connected_addon->get_addon_form_hooks( $this->form_id );
					if ( $form_hooks instanceof Forminator_Addon_Form_Hooks_Abstract ) {
						self::$connected_addons[] = $connected_addon;
					}
				} catch ( Exception $e ) {
					forminator_addon_maybe_log( $connected_addon->get_slug(), 'failed to get_addon_form_hooks', $e->getMessage() );
				}
			}
		}

		return self::$connected_addons;
	}
}