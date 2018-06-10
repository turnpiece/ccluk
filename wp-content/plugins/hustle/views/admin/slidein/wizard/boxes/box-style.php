<div id="wph-wizard-design-style" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Style & Colors", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Choose a pre-made style for your Slide-in and further customize it’s appearance.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Select a style to use:", Opt_In::TEXT_DOMAIN ); ?></label>

		<select class="wpmudev-select" data-attribute="style">

			<option value="simple" {{ ( style === 'simple' ) ? 'selected' : '' }} ><?php _e( "Simple", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="minimal" {{ ( style === 'minimal' ) ? 'selected' : '' }} ><?php _e( "Minimal", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="cabriolet" {{ ( style === 'cabriolet' ) ? 'selected' : '' }} ><?php _e( "Cabriolet", Opt_In::TEXT_DOMAIN ); ?></option>

		</select>

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-slidein-style_colors" class="toggle-checkbox" type="checkbox" data-attribute="customize_colors" {{_.checked(_.isTrue(customize_colors), true)}}>

				<label class="wpmudev-switch-design" for="wph-slidein-style_colors" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-slidein-style_colors"><?php _e( "Customize colors", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<?php $this->render( "admin/commons/wizard/colors-style", array() ); ?>

	</div>

</div><?php // #wph-wizard-design-style ?>