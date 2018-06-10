<div class="wph-flex wph-flex--column">

	<div class="wph-flex--box">

		<div class="wph-label--block">

			<label class="wph-label--alt"><?php _e("Choose email service provider:", Opt_In::TEXT_DOMAIN); ?></label>

		</div>

		<select class="wpmuiSelect" id="wph-provider-edit-modal-provider">

			<?php foreach( $providers as $provider ) : ?>

				<option  value="<?php echo esc_attr( $provider['id'] );  ?>" <?php selected( $selected_provider, $provider['id'] ); ?> ><?php echo $provider['name']; ?> </option>

			<?php endforeach; ?>

		</select>

	</div>

	<div id="optin_new_provider_account_details" class="wph-flex--box">

		<?php
		$current_provider = empty( $selected_provider ) ? $optin->optin_provider : $selected_provider;
		$provider = Opt_In::get_provider_by_id( $current_provider );

		if ( $provider ){

			$provider_instance = Opt_In::provider_instance( $provider );
			$options = $provider_instance->get_account_options( $optin->id );

			foreach( $options as $key =>  $option ){

				if( $option['type'] === 'wrapper'  ){ $option['apikey'] = $optin->api_key; }

				$option = apply_filters("wpoi_optin_filter_optin_options", $option, $optin );

				$this->render("general/option", array_merge( $option, array( "key" => $key ) ));

			}

			do_action("wpoi_optin_show_provider_account_options", $current_provider, $provider_instance );

		} ?>

	</div>

</div>