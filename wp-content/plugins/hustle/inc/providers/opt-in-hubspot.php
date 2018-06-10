<?php

/**
 * Class Opt_In_HubSpot
 */
class Opt_In_HubSpot extends Opt_In_Provider_Abstract  implements  Opt_In_Provider_Interface {
	const ID = "hubspot";
	const NAME = "Hubspot";

	static function instance() {
		return new self;
	}

	function is_authorized() {
		return true;
	}

	/**
	 * @return bool|Opt_In_HubSpot_Api
	 */
	function api() {
		return self::static_api();
	}

	static function static_api() {
		if ( ! class_exists( 'Opt_In_HubSpot_Api' ) )
			require_once 'opt-in-hubspot-api.php';

		$api = new Opt_In_HubSpot_Api();

		return $api;
	}

	/**
	 * Updates api option
	 *
	 * @param $option_key
	 * @param $option_value
	 * @return bool
	 */
	function update_option($option_key, $option_value) {
		return update_site_option( self::ID . "_" . $option_key, $option_value);
	}

	/**
	 * Retrieves api option from db
	 *
	 * @param $option_key
	 * @param $default
	 * @return mixed
	 */
	function get_option($option_key, $default ) {
		return get_site_option( self::ID . "_" . $option_key, $default );
	}

	function subscribe( Hustle_Module_Model $module, array $data ) {
		$email_list = self::_get_email_list( $module );
		$err = new WP_Error();
		$err->add( 'something_wrong', __( 'Something went wrong. Please try again', Opt_In::TEXT_DOMAIN ) );

		$api = $this->api();

		if ( $api && ! $api->is_error && ! empty( $data['email'] ) ) {
			$email_exist = $api->email_exists( $data['email'] );

			if ( $email_exist ) {
				$contact_id = $email_exist->vid;
				$list_memberships = 'list-memberships';
				$add_to_list = false;

				if ( empty( $email_exist->{$list_memberships} ) )
					$add_to_list = true;

				if ( $add_to_list ) {
					$res = $api->add_to_contact_list( $contact_id, $data['email'], $email_list );

					if ( false === $res ) {
						$data['error'] = __( 'Unable to add this contact to contact list.', Opt_In::TEXT_DOMAIN );
						$module->log_error($data);
					}
				}
				$err->add( 'something_wrong', __( 'This email has already subscribe.', Opt_In::TEXT_DOMAIN ) );
			} else {
				$res = $api->add_contact( $data );

				if ( ! is_object( $res ) && (int) $res > 0 ) {
					$contact_id = $res;
					// Add new contact to contact list
					$res = $api->add_to_contact_list( $contact_id, $data['email'], $email_list );

					if ( false === $res ) {
						$data['error'] = __( 'Unable to add this contact to contact list.', Opt_In::TEXT_DOMAIN );
						$module->log_error($data);
					}
					return true;
				} elseif( is_wp_error( $res ) ) {
					$data['error'] = $res->get_error_message();
					$module->log_error( $data );
				} elseif ( isset( $res->status ) && 'error' == $res->status ) {
					$data['error'] = $res->message;
					$module->log_error($data);
				}
			}
		}

		return $err;
	}

	function get_options( $module_id ) {
		return array();
	}

	function get_account_options( $module_id ) {
		$options = array();
		$email_list = '';
		$api = $this->api();

		if ( $module_id ) {
			$module 	= Hustle_Module_Model::instance()->get( $module_id );
			$email_list = self::_get_email_list( $module );
		}
		$is_authorize = $api && ! $api->is_error && $api->is_authorized();

		$url 	= $api->get_authorization_uri( $module_id , true, $this->current_page );
		$link 	= sprintf( '<a href="%1$s" class="hubspot-authorize" data-optin="%2$s">%3$s</a>', $url, $module_id, __( 'click here', Opt_In::TEXT_DOMAIN ) );

		if ( $api && ! $api->is_error ) {
			if ( ! $is_authorize ) {
				$info = __( 'Please %s to connect to your Hubspot account. You will be asked to give us access to your selected account and will be redirected back to this page.', Opt_In::TEXT_DOMAIN );
				$info = sprintf( $info, $link );
				$options['info'] = array(
					'type' 	=> 'label',
					'value' => $info,
					'for' 	=> '',
				);
			} else {
				$info = __( 'Please %s to reconnect to your Hubspot account. You will be asked to give us access to your selected account and will be redirected back to this page.', Opt_In::TEXT_DOMAIN );
				$info = sprintf( $info, $link );
				$list = $api->get_contact_list();
				$options = array(
					array(
						'type' 	=> 'label',
						'value' => $info,
						'for' 	=> '',
					),
					array(
						'type' 	=> 'label',
						'class'	=> 'wpmudev-label--loading',
						'for' 	=> 'optin_email_list',
						"value" => "<span class='wpmudev-loading-text'>" . __( "Fetch Lists", Opt_In::TEXT_DOMAIN ) . "</span>",
					),
					array(
						'type' 		=> 'select',
						'id' 		=> 'wph-email-provider-lists',
						'name' 		=> 'optin_email_list',
						'options' 	=> $list,
						'selected' 	=> $email_list,
						'class'     => "wpmudev-select"
					)
				);
			}
		}

		return $options;
	}

	/**
	 * Get Provider Details
	 * General function to get provider details from database based on key
	 *
	 * @param Hustle_Module_Model $module
	 * @param String $field - the field name
	 *
	 * @return String
	 */
	private static function _get_provider_details( Hustle_Module_Model $module, $field ) {
		$details = '';
		$name = self::ID;
		if ( !is_null( $module->content->email_services )
			&& isset( $module->content->email_services[$name] )
			&& isset( $module->content->email_services[$name][$field] ) ) {

			$details = $module->content->email_services[$name][$field];
		}
		return $details;
	}

	private static function _get_email_list( Hustle_Module_Model $module ) {
		return self::_get_provider_details( $module, 'list_id' );
	}

	static function add_custom_field( $fields, $module_id ) {
		$api 	= self::static_api();
		$exist 	= false;

		if ( $api && ! $api->is_error ) {
			// Get the existing fields
			$props = $api->get_properties();

			$new_fields = array();

			if ( ! empty( $props ) ) {
				// Check for existing property
				foreach ( $props as $property_name => $property_label ){
					foreach ( $fields as $field ) {
						$name 	= $field['name'];
						$label 	= $field['label'];
						if ( $name != $property_name || $label != $property_label ) {
							$new_field = array(
								'name' => $property_name,
								'label' => $property_label
							);
							$new_fields[] = $new_field;
						}
					}
				}

			}

			if ( ! empty( $new_fields ) ) {
				foreach ( $new_fields as $field ) {
					// Add the new field as property
					$property = array(
						'name' => $field['name'],
						'label' => $field['label'],
						'type' => 'string',
						'fieldType' => 'text',
						'groupName' => 'contactinformation',
					);

					if ( $api->add_property( $property ) )
						$exist = true;
				}

			}
		}

		if ( $exist )
			return array( 'success' => true, 'field' => $fields );
		else
			return array( 'error' => true, 'code' => 'cannot_create_custom_field' );
	}
}

/**
 * Disable selected list description.
 */
add_filter( 'wpoi_optin_hubspot_show_selected_list', '__return_false' );