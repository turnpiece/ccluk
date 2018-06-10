<div id="wph-wizard-content-titles" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Pop-up titles", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Titles are an optional part of the design", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-titles" class="toggle-checkbox" type="checkbox" data-attribute="has_title" {{_.checked(_.isTrue(has_title), true)}}>

				<label class="wpmudev-switch-design" for="wph-popup-titles" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-titles"><?php _e( "Use titles", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

		<div id="wph-wizard-content-title-textboxes" class="wpmudev-box-gray {{ ( _.isFalse(has_title) ) ? 'wpmudev-hidden' : '' }}">

			<label><?php _e( "Title (optional)", Opt_In::TEXT_DOMAIN ); ?></label>

			<input type="text" data-attribute="title" id="wph_popup_new_title" class="wpmudev-input_text" name="title" placeholder="<?php esc_attr_e('Type title here...', Opt_In::TEXT_DOMAIN) ?>" value="{{title}}" >

			<label><?php _e( "Subtitle (optional)", Opt_In::TEXT_DOMAIN ); ?></label>

			<input type="text" data-attribute="sub_title" id="wph_popup_new_subtitle" class="wpmudev-input_text" name="sub_title" placeholder="<?php esc_attr_e('Type subtitle here...', Opt_In::TEXT_DOMAIN) ?>" value="{{sub_title}}" >

		</div>

	</div>

</div><?php // #wph-wizard-content-titles ?>