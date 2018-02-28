<?php
$captcha_key       = get_option( "forminator_captcha_key", "" );
$captcha_secret    = get_option( "forminator_captcha_secret", "" );
$captcha_language  = get_option( "forminator_captcha_language", "" );
$captcha_theme		 = get_option( "forminator_captcha_theme", "" );
?>
<div class="wpmudev-hidden-popup wpmudev-popup-form" style="display: none">

	<div class="wpmudev-row">

	    <div class="wpmudev-col col-12">

	        <label><?php _e( "Site Key", Forminator::DOMAIN ); ?></label>

	        <input class="wpmudev-input" name="captcha_key" value="<?php echo $captcha_key; ?>">

			<div style="padding-bottom: 10px; margin-top: -15px;"><?php _e( "Get reCaptcha API credentials by registering", Forminator::DOMAIN ); ?> <a href="https://www.google.com/recaptcha/intro/index.html" target="_blank"><?php _e( "here.", Forminator::DOMAIN ); ?></a></div>

		</div>

	</div>

	<div class="wpmudev-row">

	    <div class="wpmudev-col col-12">

	        <label><?php _e( "Secret Key", Forminator::DOMAIN ); ?></label>

	        <input class="wpmudev-input" name="captcha_secret" value="<?php echo $captcha_secret; ?>">

	    </div>

	</div>

	<div class="wpmudev-row">

	    <div class="wpmudev-col col-12">

	        <label><?php _e( "Language", Forminator::DOMAIN ); ?></label>

	        <input class="wpmudev-input" name="captcha_language" value="<?php echo $captcha_language; ?>">

			<div style="padding-bottom: 10px; margin-top: -15px;"><?php _e( "Find your language code", Forminator::DOMAIN ); ?> <a href="https://developers.google.com/recaptcha/docs/language" target="_blank"><?php _e( "here.", Forminator::DOMAIN ); ?></a></div>

		</div>

	</div>

	<div class="wpmudev-row">

	    <div class="wpmudev-col col-12">

	        <label><?php _e( "Theme", Forminator::DOMAIN ); ?></label>

			  <select name="captcha_theme" class="wpmudev-select">
				  <option value="light" <?php if( $captcha_theme == "ligh" ) echo 'selected="selected"'; ?>>Light</option>
				  <option value="dark" <?php if( $captcha_theme == "dark" ) echo 'selected="selected"'; ?>>Dark</option>
			  </select>
	    </div>

	</div>

	<div class="wpmudev-row">

		 <div class="wpmudev-col col-12">

			  <button class="wpmudev-button wpmudev-action-done wpmudev-button-blue" data-nonce="<?php echo wp_create_nonce( 'forminator_save_popup_captcha' ) ?>"><?php _e( "Done", Forminator::DOMAIN ); ?> </button>

		 </div>

	</div>

</div>