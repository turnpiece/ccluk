<?php
$captcha_key      = get_option( "forminator_captcha_key", "" );
$captcha_secret   = get_option( "forminator_captcha_secret", "" );
$captcha_language = get_option( "forminator_captcha_language", "" );
$captcha_theme    = get_option( "forminator_captcha_theme", "" );
$nonce            = wp_create_nonce( 'forminator_save_popup_captcha' );
?>

<div class="sui-box-body wpmudev-popup-form">

	<div class="sui-form-field">
		<label for="captcha_key" class="sui-label"><?php esc_html_e( "Site Key", Forminator::DOMAIN ); ?></label>
		<input id="captcha_key" class="sui-form-control" name="captcha_key" value="<?php echo esc_attr( $captcha_key ); ?>">
		<span class="sui-description">
			<?php esc_html_e( "Get reCaptcha API credentials by registering", Forminator::DOMAIN ); ?>
			<a href="https://www.google.com/recaptcha/intro/index.html" target="_blank"><?php esc_html_e( "here.", Forminator::DOMAIN ); ?></a>
		</span>
	</div>

	<div class="sui-form-field">
		<label for="captcha_secret" class="sui-label"><?php esc_html_e( "Secret Key", Forminator::DOMAIN ); ?></label>
		<input id="captcha_secret" class="sui-form-control" name="captcha_secret" value="<?php echo esc_attr( $captcha_secret ); ?>">
	</div>

	<div class="sui-form-field">
		<label for="captcha_language" class="sui-label"><?php esc_html_e( "Language", Forminator::DOMAIN ); ?></label>
		<input id="captcha_language" class="sui-form-control" name="captcha_language" value="<?php echo esc_attr( $captcha_language ); ?>">
		<span class="sui-description">
			<?php esc_html_e( "Find your language code", Forminator::DOMAIN ); ?>
			<a href="https://developers.google.com/recaptcha/docs/language" target="_blank"><?php esc_html_e( "here.", Forminator::DOMAIN ); ?></a>
		</span>
	</div>

	<div class="sui-form-field">
		<label for="captcha_theme" class="sui-label"><?php esc_html_e( "Theme", Forminator::DOMAIN ); ?></label>
		<select name="captcha_theme" class="wpmudev-select" id="captcha_theme">
			<option value="light" <?php if( "ligh" === $captcha_theme ) echo 'selected="selected"'; ?>>Light</option>
			<option value="dark" <?php if( "dark" === $captcha_theme ) echo 'selected="selected"'; ?>>Dark</option>
		</select>
	</div>
</div>

<div class="sui-box-footer">
	<button class="sui-button forminator-popup-cancel" data-a11y-dialog-hide="forminator-popup"><?php esc_html_e( 'Cancel', Forminator::DOMAIN ); ?></button>
	<div class="sui-actions-right">
		<button class="sui-button sui-button-primary wpmudev-action-done" data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<?php esc_html_e( "Save", Forminator::DOMAIN ); ?>
		</button>
	</div>
</div>