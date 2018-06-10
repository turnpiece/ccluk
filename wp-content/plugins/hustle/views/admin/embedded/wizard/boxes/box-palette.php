<div id="wph-wizard-design-palette" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Colors Palette", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Choose a pre-made palette for your Embed and further customize it’s appearance.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Select color palette", Opt_In::TEXT_DOMAIN ); ?></label>

		<select class="wpmudev-select" data-attribute="style">

			<option value="gray_slate" {{ ( style === 'gray_slate' ) ? 'selected' : '' }} ><?php _e( "Gray Slate", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="coffee" {{ ( style === 'coffee' ) ? 'selected' : '' }} ><?php _e( "Coffee", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="ectoplasm" {{ ( style === 'ectoplasm' ) ? 'selected' : '' }} ><?php _e( "Ectoplasm", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="blue" {{ ( style === 'blue' ) ? 'selected' : '' }} ><?php _e( "Blue", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="sunrise" {{ ( style === 'sunrise' ) ? 'selected' : '' }} ><?php _e( "Sunrise", Opt_In::TEXT_DOMAIN ); ?></option>
			<option value="midnight" {{ ( style === 'midnight' ) ? 'selected' : '' }} ><?php _e( "Midnight", Opt_In::TEXT_DOMAIN ); ?></option>

		</select>

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-aftercontent-custom_palette" class="toggle-checkbox" type="checkbox" data-attribute="customize_colors" {{_.checked(_.isTrue(customize_colors), true)}}>

				<label class="wpmudev-switch-design" for="wph-aftercontent-custom_palette" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-aftercontent-custom_palette"><?php _e( "Customize colors", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<?php $this->render( "admin/commons/wizard/colors-palette", array() ); ?>

	</div>

</div><?php // #wph-wizard-design-palette ?>