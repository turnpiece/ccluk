<div id="wph-wizard-content-form_success" class="wpmudev-box-content last">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Success message closing", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Choose if you want user to close success message, or close it automaticaly after a certain amount of time.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-aftercontent-success_message_closing" class="toggle-checkbox" type="checkbox" data-attribute="auto_close_success_message" {{_.checked(_.isTrue(auto_close_success_message), true)}} >

				<label class="wpmudev-switch-design" for="wph-aftercontent-success_message_closing" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-aftercontent-success_message_closing"><?php _e( "Automatically close success message", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

        <div id="wph-wizard-content-form_success_options" class="wpmudev-box-gray {{ ( _.isFalse(auto_close_success_message) ) ? 'wpmudev-hidden' : 'wpmudev-show' }}">

            <label><?php _e( "Automatically close message after", Opt_In::TEXT_DOMAIN ); ?></label>

            <div class="wpmudev-fields-group">

                <input type="number" data-attribute="auto_close_time" value="{{auto_close_time}}" class="wpmudev-input_number" min="0">

                <select class="wpmudev-select" data-attribute="auto_close_unit">
                    <option value="seconds" {{ ( auto_close_unit === 'seconds' ) ? 'selected' : '' }}>Seconds</option>
                    <option value="minutes" {{ ( auto_close_unit === 'minutes' ) ? 'selected' : '' }}>Minutes</option>
                </select>

            </div>

        </div>

	</div>

</div><?php // #wph-wizard-content-form_success ?>