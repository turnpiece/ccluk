<?php
$path = forminator_plugin_url();

$captcha_key     = get_option( "forminator_captcha_key", "" );
$captcha_secret  = get_option( "forminator_captcha_secret", "" );

$new = true;
?>

<div class="sui-box">

	<div class="sui-box-header">

		<h3 class="sui-box-title"><?php esc_html_e( "Google reCaptcha", Forminator::DOMAIN ); ?></h3>

	</div>

	<?php if ( forminator_has_captcha_settings() ) { ?>

		<div class="sui-box-body">

			<div class="sui-notice sui-notice-sm sui-notice-warning">

				<p><?php esc_html_e( "Please note, these settings are required only if you decide to use the reCaptcha field.", Forminator::DOMAIN ); ?></p>

			</div>

			<div class="sui-notice sui-notice-sm sui-notice-info">

				<p><?php esc_html_e('Make sure you register your reCaptcha site type as invisible reCaptcha to support both reCaptcha v2 and invisible reCaptcha', Forminator::DOMAIN); ?></p>

			</div>

		</div>

		<table class="sui-table sui-accordion fui-table-exports">

			<tbody>

				<tr>

					<td><?php esc_html_e( "Site Key", Forminator::DOMAIN ); ?></td>

					<td><?php echo esc_html( $captcha_key ); ?></td>

				</tr>

				<tr>

					<td><?php esc_html_e( "Secret Key", Forminator::DOMAIN ); ?></td>

					<td><?php echo esc_html( $captcha_secret ); ?></td>

				</tr>

			</tbody>

		</table>

		<div class="sui-box-footer">

			<button class="sui-button wpmudev-open-modal" data-modal="captcha" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_captcha' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Edit Credentials", Forminator::DOMAIN ); ?></button>

		</div>

	<?php } else { ?>

		<div class="sui-box-body">

			<div class="sui-notice sui-notice-warning">

				<p><?php esc_html_e( "Add Google reCaptcha settings to enable reCaptcha field.", Forminator::DOMAIN ); ?></p>

			</div>

			<div class="sui-block-content-center">

				<img src="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?>"
					srcset="<?php echo $path . 'assets/img/forminator-face.png'; // WPCS: XSS ok. ?> 1x, <?php echo $path . 'assets/img/forminator-face@2x.png'; // WPCS: XSS ok. ?> 2x" alt="<?php esc_html_e( 'Forminator', Forminator::DOMAIN ); ?>"
					class="sui-image sui-image-center fui-image" />

				<p><?php esc_html_e( "reCaptcha is a free service that protects your site from spam and abuse.", Forminator::DOMAIN ); ?></p>

				<button class="sui-button sui-button-primary wpmudev-open-modal" data-modal="captcha" data-nonce="<?php echo wp_create_nonce( 'forminator_popup_captcha' ); // WPCS: XSS ok. ?>"><?php esc_html_e( "Add Credentials", Forminator::DOMAIN ); ?></button>

			</div>

		</div>

	<?php } ?>

</div>