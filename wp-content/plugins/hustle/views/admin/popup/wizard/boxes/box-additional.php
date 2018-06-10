<div id="wph-wizard-settings-additional" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Additional settings", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "After Pop-up is closed", Opt_In::TEXT_DOMAIN ); ?></label>

        <div class="wpmudev-box-gray">

            <select class="wpmudev-select" data-attribute="after_close" >
                <option value="no_show_on_post" {{ ( after_close === 'no_show_on_post' ) ? 'selected' : '' }} ><?php _e( "No longer show this message on this post / page", Opt_In::TEXT_DOMAIN ); ?></option>
                <option value="no_show_all" {{ ( after_close === 'no_show_all' ) ? 'selected' : '' }} ><?php _e( "No longer show this message across the site", Opt_In::TEXT_DOMAIN ); ?></option>
                <option value="keep_show" {{ ( after_close === 'keep_show' ) ? 'selected' : '' }} ><?php _e( "Keep showing this message", Opt_In::TEXT_DOMAIN ); ?></option>
            </select>

            <label><?php _e( "Expires (after expiry, user will see the Pop-up again)", Opt_In::TEXT_DOMAIN ); ?></label>

            <div class="wpmudev-fields-group">

                <input type="number" value="{{expiration}}" class="wpmudev-input_number" data-attribute="expiration" >

                <select class="wpmudev-select" data-attribute="expiration_unit" >
                    <option value="days" {{ ( expiration_unit === 'days' ) ? 'selected' : '' }} ><?php _e( "days", Opt_In::TEXT_DOMAIN ); ?></option>
                    <option value="weeks" {{ ( expiration_unit === 'weeks' ) ? 'selected' : '' }} ><?php _e( "weeks", Opt_In::TEXT_DOMAIN ); ?></option>
                    <option value="months" {{ ( expiration_unit === 'months' ) ? 'selected' : '' }} ><?php _e( "months", Opt_In::TEXT_DOMAIN ); ?></option>
                    <option value="years" {{ ( expiration_unit === 'years' ) ? 'selected' : '' }} ><?php _e( "years", Opt_In::TEXT_DOMAIN ); ?></option>
                </select>

            </div>

        </div>

        <div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-setting_scroll" class="toggle-checkbox" type="checkbox" data-attribute="allow_scroll_page" {{_.checked(_.isTrue(allow_scroll_page), true)}} >

				<label class="wpmudev-switch-design" for="wph-popup-setting_scroll" aria-hidden="true"></label>

			</div>

			<div class="wpmudev-switch-labels">

                <label class="wpmudev-switch-label" for="wph-popup-setting_scroll"><?php _e( "Allow page to be scrolled while Pop-up is visible", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

		</div>

        <div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-setting_close" class="toggle-checkbox" type="checkbox" data-attribute="not_close_on_background_click" {{_.checked(_.isTrue(not_close_on_background_click), true)}} >

				<label class="wpmudev-switch-design" for="wph-popup-setting_close" aria-hidden="true"></label>

			</div>

            <div class="wpmudev-switch-labels">

			    <label class="wpmudev-switch-label" for="wph-popup-setting_close"><?php _e( "Clicking on the background does not close Pop-up", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

		</div>

    </div>

</div><?php // #wph-wizard-settings-additional ?>