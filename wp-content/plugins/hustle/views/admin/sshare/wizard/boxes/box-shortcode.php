<?php
// CAN WE PLEASE ADD THIS FUNCTIONS SOMEWHERE TO USE IN THE FUTURE?
// THAT WAY IF WE NEED TO ADD A NEW FIELD WITH CONTENT TO BE COPIED
// WE CAN SIMPLY USE THE FUNCTION BELOW:

function wpmudev_copy_field($field_value) {

    echo '<div class="wpmudev-copy">
        <input class="wpmudev-input_text" type="text" value="' . $field_value . '" disabled>
        <button class="wpmudev-button wpmudev-button-sm">' . __( "Copy", Opt_In::TEXT_DOMAIN ) . '</button>
    </div>';

}
?>

<div id="wph-wizard-settings-shortcode" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Widgets & Shortcodes", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

		<label class="wpmudev-helper"><?php _e( "Widget & Shortcode display location is controlled manually.", Opt_In::TEXT_DOMAIN ); ?></label>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Widgets", Opt_In::TEXT_DOMAIN ); ?></label>

        <p><?php _e( "You can configure <strong>After Content</strong>, <strong>Pop-up</strong> and <strong>Slide-in</strong> opt-ins in the sections that follow.", Opt_In::TEXT_DOMAIN ); ?></p>

        <p><?php _e( "Widget opt-in can be set-up in <strong>Appearance » <a href='%'>Widgets</a></strong>", Opt_In::TEXT_DOMAIN ); ?></p>

        <label><?php _e( "Shortcode", Opt_In::TEXT_DOMAIN ); ?></label>

        <?php wpmudev_copy_field("[wd_hustle id='". $module->shortcode_id ."' type='social_sharing']"); ?>

	</div>

</div><?php // #wph-wizard-settings-shortcode ?>