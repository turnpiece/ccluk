<div id="wph-wizard-design-size" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Custom slide-in size", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-slidein-custom_size" class="toggle-checkbox" type="checkbox" data-attribute="customize_size" {{_.checked(_.isTrue(customize_size), true)}}>

				<label class="wpmudev-switch-design" for="wph-slidein-custom_size" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-slidein-custom_size"><?php _e( "Use custom size", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<div id="wph-wizard-design-size-options" class="wpmudev-box-gray {{ ( _.isTrue(customize_size) ) ? 'wpmudev-show' : 'wpmudev-hidden' }}">

			<div class="wpmudev-row">

				<div class="wpmudev-col">

					<label><?php _e( "Width", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{custom_width}}" data-attribute="custom_width" class="wpmudev-input_number">

				</div>

				<div class="wpmudev-col">

					<label><?php _e( "Height", Opt_In::TEXT_DOMAIN ); ?></label>

					<input type="number" value="{{custom_height}}" data-attribute="custom_height" class="wpmudev-input_number">

				</div>

			</div>

		</div>

	</div>

</div><?php // #wph-wizard-design-size ?>