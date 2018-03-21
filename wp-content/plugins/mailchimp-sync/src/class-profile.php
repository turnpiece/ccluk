<?php

namespace MC4WP\Sync;

use WP_User;

class Profile {

	private $options;
	private $user_tools;

	/**
	* @var array $options
	* @var users $user_tools
	*/
	public function __construct( $options, Users $user_tools ) {
		$this->options = $options;
		$this->user_tools = $user_tools;
	}

	public function add_hooks() {	
		// wordpress
		add_action( 'personal_options_update', array( $this, 'process_user_update' ), 20 );
		add_action( 'edit_user_profile_update', array( $this, 'process_user_update' ), 20 );
		add_action( 'show_user_profile', array( $this, 'user_profile' ), 5 );
		add_action( 'edit_user_profile', array( $this, 'user_profile' ), 5 );

		// woocommerce
		add_action( 'woocommerce_edit_account_form', array( $this, 'user_profile' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'process_user_update' ) );

		// general purpose action hook for custom forms
		add_action( 'mailchimp_sync_output_optin_field', array( $this, 'user_profile' ), 10, 2 );
	}

	/**
	 * Show status on User Profile page
	 * @param int $user
	 * @param string $field_type
	 */
	public function user_profile( $user = null, $field_type = '' ) {

		if( ! $user instanceof WP_User ) {
			if( is_numeric( $user ) ) {
				$user = get_userdata( $user );
			} else {
				$user = wp_get_current_user();
			}
		}

		// only show if user control is enabled
		if( $this->options['enable_user_control'] == false ) {
			return;
		}

		// do nothing if plugin isn't enabled or fully configured
		if( ! $this->user_tools instanceof Users ) {
			return;
		}

		// only show if this user matches role criteria from settings
		if( ! $this->user_tools->should( $user ) ) {
			return;
		}

		$default_status = $this->options['default_optin_status'] === 'subscribed';
		$opted_in = $this->user_tools->get_optin_status( $user, $default_status );
		$name_attr = $this->user_tools->get_meta_key_for_optin_status();	
		$heading_text = $this->options['user_profile_heading_text'];
		$label_text = $this->options['user_profile_label_text'];

		// output HTML
		if( in_array( current_action(), array( 'show_user_profile', 'edit_user_profile' ) ) || $field_type === 'table' ) {
			$this->form_field_table( $heading_text, $label_text, $name_attr, $opted_in );
		} else {
			$this->form_field_paragraph( $heading_text, $label_text, $name_attr, $opted_in );
		}
	}

	private function form_field_table( $heading, $label, $name_attr, $checked ) {
		?>
		<table class="form-table">
			<tr>
				<th><?php echo esc_html( $heading ); ?></th>
				<td>
					<input type="hidden" name="<?php echo esc_attr( $name_attr ); ?>" value="0" />
					<label><input type="checkbox" name="<?php echo esc_attr( $name_attr ); ?>" value="1" <?php checked( $checked, true ); ?> /> <?php echo esc_html( $label ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	private function form_field_paragraph( $heading, $label, $name_attr, $checked ) {
		?>
		<p class="form-row form-row-wide">
			<label><?php echo esc_html( $heading ); ?></label>
			<input type="hidden" name="<?php echo esc_attr( $name_attr ); ?>" value="0" />
			<label><input type="checkbox" name="<?php echo esc_attr( $name_attr ); ?>" value="1" <?php checked( $checked, true ); ?> /> <?php echo esc_html( $label ); ?></label>
		</p>		
		<?php
	}

	/**
	* @param int $user_id
	*/
	public function process_user_update( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// only change optin status if post data contains our field
		$name_attr = $this->user_tools->get_meta_key_for_optin_status();
		if( isset( $_POST[$name_attr] ) ) {
			$this->user_tools->set_optin_status( $user_id, $_POST[$name_attr] === "1" );
		}
	}
}
