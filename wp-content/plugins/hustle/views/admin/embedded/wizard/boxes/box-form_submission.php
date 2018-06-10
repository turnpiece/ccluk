<div id="wph-wizard-content-form_submission" class="wpmudev-box-content last">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Successful submission behavior", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Choose what you want to happen after your visitor has successfully submitted their email address.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "After successfull submission", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wpmudev-tabs">

            <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_full wpmudev-after-submit-options">

                <li class="wpmudev-tabs-menu_item {{ ( after_successful_submission === 'show_success' ) ? 'current' : '' }}">
                    <input type="checkbox" value="show_success">
                    <label><?php _e( "Show success message", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item {{ ( after_successful_submission === 'redirect' ) ? 'current' : '' }}">
                    <input type="checkbox" value="redirect">
                    <label><?php _e( "Page re-direct", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

            </ul>

        </div>

        <input id="wph-wizard-content-form_submission_redirect_url" type="text" data-attribute="redirect_url" value="{{redirect_url}}" placeholder="http://yourwebsite.com/success-page/" class="wpmudev-input_text {{ ( after_successful_submission === 'show_success' ) ? 'wpmudev-hidden' : '' }}">

	</div>

</div><?php // #wph-wizard-content-form_submission ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-form_message", array() ); ?>

<?php $this->render( "admin/embedded/wizard/boxes/box-form_success", array() ); ?>