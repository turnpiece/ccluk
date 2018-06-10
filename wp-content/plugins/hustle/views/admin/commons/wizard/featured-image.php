<div id="wph-{{module_type}}-choose_image" class="wpmudev-choose_image">

	<div class="wpmudev-wrap-show_image">

		<div class="wpmudev-show_image" aria-hidden="true">

			<div class="wpmudev-inserted_image" style="background-image: url({{feature_image}});"></div>

		</div>

		<input type="text" value="{{feature_image}}" data-attribute="feature_image" placeholder="<?php _e( 'Click browse to add image...', Opt_In::TEXT_DOMAIN ); ?>" class="wpmudev-input_text wpmudev-feature-image-src-input_text">

		<div class="wpmudev-clear_image">

			<button id="wpmudev-feature-image-clear" class="wpmudev-button wpmudev-button-sm"><?php _e( "Clear", Opt_In::TEXT_DOMAIN ); ?></button>

		</div>

	</div>

	<button id="wpmudev-feature-image-browse" class="wpmudev-button"><?php _e( "Browse", Opt_In::TEXT_DOMAIN ); ?></button>

</div>