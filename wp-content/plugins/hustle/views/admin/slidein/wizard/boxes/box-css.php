<div id="wph-wizard-design-css" class="wpmudev-box-content last">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Custom CSS", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "For more advanced customization options use custom CSS.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-slidein-custom_css" class="toggle-checkbox" type="checkbox" data-attribute="customize_css" {{_.checked(_.isTrue(customize_css), true)}}>

				<label class="wpmudev-switch-design" for="wph-slidein-custom_css" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-slidein-custom_css"><?php _e( "Use custom CSS for this module", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

	</div>

</div><?php // #wph-wizard-design-css ?>