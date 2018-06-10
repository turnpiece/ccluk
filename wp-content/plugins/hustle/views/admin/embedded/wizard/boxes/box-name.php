<div id="wph-wizard-content-name" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Embed name", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Pick a name for your Embed module. This is for you to be able to identify embeds and will not be displayed on your site.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Embed name", Opt_In::TEXT_DOMAIN ); ?></label>

		<input type="text" data-attribute="module_name" id="wph_embedded_new_name" class="wpmudev-input_text wpmudev-required" name="module_name" placeholder="<?php esc_attr_e('Type name here...', Opt_In::TEXT_DOMAIN) ?>" value="{{module_name}}">

		<label class="wpmudev-label--notice {{ ( _.isEmpty ( module_name ) ? '' : 'wpmudev-hidden' )  }}"><span><?php _e( "Oops, you need to name your embed before you proceed.", Opt_In::TEXT_DOMAIN ); ?></span></label>

	</div>

</div><?php // #wph-wizard-content-name ?>