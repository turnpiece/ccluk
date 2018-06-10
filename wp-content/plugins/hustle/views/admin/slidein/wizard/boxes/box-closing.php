<div id="wph-wizard-settings-closing" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Closing behavior", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

        <div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

                <input id="wph-slidein-auto_hide" class="toggle-checkbox" type="checkbox" data-attribute="auto_hide" {{_.checked(_.isTrue(auto_hide), true)}}>

				<label class="wpmudev-switch-design" for="wph-slidein-auto_hide" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-slidein-auto_hide"><?php _e( "Automatically hide Slide-in", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

        <div id="wpmudev-display-auto_hide-options" class="wpmudev-box-gray {{( _.isTrue(auto_hide) ) ? 'wpmudev-show' : 'wpmudev-hidden'}}">

            <label><?php _e( "Automatically hide Slide-in after", Opt_In::TEXT_DOMAIN ); ?></label>

        	<div class="wpmudev-fields-group">
            	<input type="number" class="wpmudev-input_number" data-attribute="auto_hide_time" value="{{auto_hide_time}}">

            	<select class="wpmudev-select" data-attribute="auto_hide_unit">

                	<option value="hours" {{ ( auto_hide_unit === 'hours' ) ? 'selected' : '' }}><?php _e( "hours", Opt_In::TEXT_DOMAIN ); ?></option>
                	<option value="minutes" {{ ( auto_hide_unit === 'minutes' ) ? 'selected' : '' }}><?php _e( "minutes", Opt_In::TEXT_DOMAIN ); ?></option>
                	<option value="seconds" {{ ( auto_hide_unit === 'seconds' ) ? 'selected' : '' }}><?php _e( "seconds", Opt_In::TEXT_DOMAIN ); ?></option>

            	</select>
			</div>

        </div>

        <div id="wph-slidein-close">

            <h5><?php _e( "After Slide-in is closed", Opt_In::TEXT_DOMAIN ); ?></h5>

        	<label class="wpmudev-helper"><?php _e( "Choose how your Slide-in will behave when it is closed.", Opt_In::TEXT_DOMAIN ); ?></label>


        	<label class="wpmudev-label--notice"><span><?php _e( "This option does not work with auto-hide because a user action is required.", Opt_In::TEXT_DOMAIN ); ?></span></label>

            <div class="wpmudev-box-gray">

				<select class="wpmudev-select" data-attribute="after_close" >
					<option value="no_show_on_post" {{ ( after_close === 'no_show_on_post' ) ? 'selected' : '' }} ><?php _e( "No longer show this message on this post / page", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="no_show_all" {{ ( after_close === 'no_show_all' ) ? 'selected' : '' }} ><?php _e( "No longer show this message across the site", Opt_In::TEXT_DOMAIN ); ?></option>
					<option value="keep_show" {{ ( after_close === 'keep_show' ) ? 'selected' : '' }} ><?php _e( "Keep showing this message", Opt_In::TEXT_DOMAIN ); ?></option>
				</select>

                <label><?php _e( "Expires (after expiracy, user will see the Slide-in again)", Opt_In::TEXT_DOMAIN ); ?></label>

        		<div class="wpmudev-fields-group">

                    <input type="number" class="wpmudev-input_number" value="{{expiration}}" data-attribute="expiration">

                    <select class="wpmudev-select" data-attribute="expiration_unit">

                	    <option value="months" {{ ( expiration_unit === 'months' ) ? 'selected' : '' }}><?php _e( "months", Opt_In::TEXT_DOMAIN ); ?></option>
                	    <option value="weeks" {{ ( expiration_unit === 'weeks' ) ? 'selected' : '' }}><?php _e( "weeks", Opt_In::TEXT_DOMAIN ); ?></option>
                	    <option value="days" {{ ( expiration_unit === 'days' ) ? 'selected' : '' }}><?php _e( "days", Opt_In::TEXT_DOMAIN ); ?></option>
                	    <option value="hours" {{ ( expiration_unit === 'hours' ) ? 'selected' : '' }}><?php _e( "hours", Opt_In::TEXT_DOMAIN ); ?></option>
                	    <option value="minutes" {{ ( expiration_unit === 'minutes' ) ? 'selected' : '' }}><?php _e( "minutes", Opt_In::TEXT_DOMAIN ); ?></option>
                	    <option value="seconds" {{ ( expiration_unit === 'seconds' ) ? 'selected' : '' }}><?php _e( "seconds", Opt_In::TEXT_DOMAIN ); ?></option>

                    </select>

                </div>

            </div>

        </div>

	</div>

</div><?php // #wph-wizard-settings-closing ?>