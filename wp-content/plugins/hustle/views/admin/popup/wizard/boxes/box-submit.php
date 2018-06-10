<div id="wph-wizard-settings-submit" class="wpmudev-box-content last">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Form submit behavior", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <label class="wpmudev-info"><?php _e( "If your pop-up contains a form, you can change the on submit behavior here.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "After form is submitted", Opt_In::TEXT_DOMAIN ); ?></label>

        <select class="wpmudev-select" data-attribute="on_submit" >
            <option value="close" {{ ( on_submit === 'close' ) ? 'selected' : '' }} ><?php _e( "Close the pop-up", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="redirect" {{ ( on_submit === 'redirect' ) ? 'selected' : '' }} ><?php _e( "Re-direct to form target URL", Opt_In::TEXT_DOMAIN ); ?></option>
            <option value="nothing" {{ ( on_submit === 'nothing' ) ? 'selected' : '' }} ><?php _e( "Do nothing (use for Ajax Forms)", Opt_In::TEXT_DOMAIN ); ?></option>
        </select>

    </div>

</div><?php // #wph-wizard-settings-submit ?>