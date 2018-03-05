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
	 * @var onject
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
	 * Initialise variables
	 *
	 * @since 1.0
	 */
	public function init() {
		if ( isset( $_REQUEST['form_id'] ) ) {
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

			$pagenum				= isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
			$this->per_page			= forminator_form_view_per_page( 'entries' );
			$this->page_number 		= max( 1, $pagenum );
			$this->total_fields 	= count( $this->fields );
			$this->checked_fields 	= $this->total_fields;
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
		$nonce = $_POST['forminatorEntryNonce'];
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
			} else if ( isset( $_POST['entries-action-bottom'] ) ) {
				$action = $_POST['entries-action-bottom'];
			}

			switch ( $action ) {
				case 'delete-all' :
					if ( isset( $_POST['entry'] ) && is_array( $_POST['entry'] ) ) {
						$entries = implode( ",", $_POST['entry'] );
						Forminator_Form_Entry_Model::delete_by_entrys( $this->model->id, $entries );

						wp_redirect( admin_url( 'admin.php?page=forminator-cform-view&form_id=' . $this->model->id ) );
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
			if ( in_array( $slug, $this->visible_fields ) ) {
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
			if ( in_array( $slug, $this->visible_fields ) ) {
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
		printf( __( "Showing %s of %s fields", Forminator::DOMAIN ), $this->checked_fields, $this->total_fields );
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
	 * @param object $item - the entry
	 * @param string $column_name - the column name
	 *
	 * @return string
	 */
	public function render_entry( $item , $column_name ) {
		$data =  $item->get_meta( $column_name, '' );
		if ( $data ) {
			$currency_symbol 	= forminator_get_currency_symbol();
			if ( is_array( $data ) ) {
				$output 		= '';
				$product_cost 	= 0;
				$is_product 	= false;
				$countries 		= forminator_get_countries_list();
				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						if ( $key == 'file' && isset( $value['file_url'] ) ) {
							$file_name 	= basename( $value['file_url'] );
							$file_name 	= "<a href='" .$value['file_url'] . "' target='_blank' rel='noreferrer' title='". __( 'View File', Forminator::DOMAIN ) ."'>$file_name</a> ,";
							$output 	.= $file_name;
						}

					} else {
						if ( !is_int( $key ) ) {
							if ( $key == 'postdata' ) {
								$url 	= get_edit_post_link( $value );
								$name 	= get_the_title( $value );
								$output .= "<a href='" .$url . "' target='_blank' rel='noreferrer' title='". __( 'Edit Post', Forminator::DOMAIN ) ."'>$name</a> ,";
							}else {

								if ( is_string( $key ) ) {
									if ( $key == 'product-id' || $key == 'product-quantity' ) {
										if ( $product_cost == 0 ) {
											$product_cost = $value;
										} else {
											$product_cost = $product_cost * $value;
										}
										$is_product = true;
									} else {
										if ( $key  == 'country' ) {
											if ( isset( $countries[$value] ) ) {
												$output .=  sprintf( __( '<strong>Country : </strong> %s', Forminator::DOMAIN ), $countries[$value] ) . "<br/> ";
											} else {
												$output .=  sprintf( __( '<strong>Country : </strong> %s', Forminator::DOMAIN ), $value ) . "<br/> ";
											}
										} else {
											$key = strtolower( $key );
											$key = ucfirst( str_replace( '-', ' ', $key ) );
											$output .= sprintf( __( '<strong>%s : </strong> %s', Forminator::DOMAIN ), $key, $value ) . "<br/> ";
										}
									}

								}

							}
						}
					}
				}
				if ( $is_product ) {
					$output = sprintf( __( '<strong>Total</strong> %s', Forminator::DOMAIN ), $currency_symbol . '' .$product_cost );
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
	 * Render entry values raw
	 *
	 * @since 1.0
	 * @param object $item - the entry
	 * @param string $column_name - the column name
	 *
	 * @return mixed
	 */
	public function render_raw_entry( $item , $column_name ) {
		$data =  $item->get_meta( $column_name, '' );
		if ( $data ) {
			if ( is_array( $data ) ) {
				$output 		= '';
				$product_cost 	= 0;
				$is_product 	= false;

				foreach ( $data as $key => $value ) {
					if ( is_array( $value ) ) {
						if ( $key == 'file' && isset( $value['file_url'] ) ) {
							$output 	.= $value['file_url'] . ", ";
						}

					} else {
						if ( !is_int( $key ) ) {
							if ( $key == 'postdata' ) {
								$output .= "$value, ";
							}else {

								if ( is_string( $key ) ) {
									if ( $key == 'product-id' || $key == 'product-quantity' ) {
										if ( $product_cost == 0 ) {
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

	public function bulk_actions( $position = 'top' ) {
		?>
		<div class="wpmudev-action--bulk">

			<select class="wpmudev-select" name="<?php echo ( $position == 'top' ) ? 'entries-action' : 'entries-action-bottom'; ?>">

				<option value=""><?php _e( "Bulk Actions", Forminator::DOMAIN ); ?></option>
				<option value="delete-all"><?php _e( "Delete Entries", Forminator::DOMAIN ); ?></option>

			</select>

			<button class="wpmudev-button wpmudev-button-ghost"><?php _e( "Apply", Forminator::DOMAIN ); ?></button>

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
}