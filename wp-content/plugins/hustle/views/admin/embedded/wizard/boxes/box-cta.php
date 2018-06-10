<div id="wph-wizard-content-cta" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Call to action button (optional)", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-aftercontent-cta-button" class="toggle-checkbox" type="checkbox" data-attribute="show_cta" {{_.checked(_.isTrue(show_cta), true)}}>

				<label class="wpmudev-switch-design" for="wph-aftercontent-cta-button" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-aftercontent-cta-button"><?php _e( "Show call to action button", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

        <div id="wph-wizard-content-cta-options" class="wpmudev-box-gray {{ ( _.isFalse(show_cta) ) ? 'wpmudev-hidden' : '' }}">

            <div class="wpmudev-cta-elements">

                <div class="wpmudev-cta-label">

                    <label><?php _e( "Label", Opt_In::TEXT_DOMAIN ); ?></label>

			        <input type="text" data-attribute="cta_label" id="wph_aftercontent_new_label" class="wpmudev-input_text" name="wph_aftercontent_new_label" placeholder="<?php esc_attr_e('Click me...', Opt_In::TEXT_DOMAIN) ?>" value="{{cta_label}}">

                </div>

                <div class="wpmudev-cta-url">

                    <label><?php _e( "URL", Opt_In::TEXT_DOMAIN ); ?></label>

			        <input type="text" data-attribute="cta_url" id="wph_aftercontent_new_url" class="wpmudev-input_text" name="wph_aftercontent_new_url" placeholder="<?php esc_attr_e('http://yourwebsite.com', Opt_In::TEXT_DOMAIN) ?>" value="{{cta_url}}">

                </div>

            </div>

            <label><?php _e( "Link opens in", Opt_In::TEXT_DOMAIN ); ?></label>

            <div class="wpmudev-tabs">

                <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_md wpmudev-cta-target-options">

                    <li class="wpmudev-tabs-menu_item {{ ( cta_target === 'blank' ) ? 'current' : '' }}">
                        <input type="checkbox" value="blank">
                        <label><?php _e( "New tab", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                    <li class="wpmudev-tabs-menu_item {{ ( cta_target === 'self' ) ? 'current' : '' }}">
                        <input type="checkbox" value="self">
                        <label><?php _e( "This tab", Opt_In::TEXT_DOMAIN ); ?></label>
                    </li>

                </ul>

            </div>

		</div>

	</div>

</div><?php // #wph-wizard-content-cta ?>