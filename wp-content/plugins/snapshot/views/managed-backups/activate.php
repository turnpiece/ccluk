
<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php
$model = new Snapshot_Model_Full_Backup();
$apiKey = $model->get_config('secret-key', '');
if ( version_compare(PHP_VERSION, '5.5.0', '<') ) {
	$aws_sdk_compatible = false;
} else {
	$aws_sdk_compatible = true;
}
$ajax_nonce = wp_create_nonce( "snapshot-save-key" );

$data = array(
	"hasApikey" => !empty($apiKey),
	"apiKey" => $apiKey,
	"apiKeyUrl" => $model->get_current_secret_key_link(),
	"aws_sdk_compatible" => $aws_sdk_compatible
);
?>

<div id="container" class="snapshot-three wps-page-backups">

	<section class="wpmud-box wps-widget-getkey">

		<div class="wpmud-box-title">
			<h3><?php esc_html_e('Get Started', SNAPSHOT_I18N_DOMAIN); ?></h3>
		</div>

		<div class="wpmud-box-content <?php echo ( ! $aws_sdk_compatible ) ? 'wps-aws-sdk-incompatible': ''; ?>">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<div class="wps-image img-snappie-four"></div>

					<div class="wps-getkey-box">

						<p><?php echo wp_kses_post( sprintf( __( '%s, as a WPMU DEV member you get 10GB free cloud storage included in your membership. Create and store full backups of your website, including WordPress core files. And if disaster strikes, you can quickly and easily restore your website any time.', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></p>

					</div>

					<p>
						<a id="view-snapshot-key-automatic" class="button <?php echo !empty($apiKey) ? 'has-key' : ''; ?> button-blue <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?>"><?php esc_html_e( 'Activate Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></a>
					</p>

				</div>

			</div>

			<div id="wps-snapshot-key-notice">

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<div class="wps-auth-message wps-automatic-try error hidden">

							<p id="wps-error-connecting"><?php echo wp_kses_post( __( 'We couldn\'t connect to the WPMU DEV to activate Managed Backups. You can <a href="#" >try activating again</a> or get your Snapshot key below to activate it manually.', SNAPSHOT_I18N_DOMAIN ) ); ?></p>

						</div>

						<div class="wpmud-box-gray">

							<div class="wps-auth-message wps-manual-try error hidden">

								<p id="wps-error-manual-connecting"><?php echo wp_kses_post( sprintf ( __( 'We couldn\'t verify your Snapshot key. <a href="%1$s" target="_blank">Get a Snapshot key</a> again, or reset it for this website in <a href="%2$s" target="_blank">The Hub</a> over at WPMU DEV.', SNAPSHOT_I18N_DOMAIN ), $data['apiKeyUrl'], $data['apiKeyUrl'] ) ); ?></p>

							</div>

							<p class="wps-get-snapshot-key">
								<?php esc_html_e( 'Get your Snapshot key from the Hub. Once you’ve got your key, enter it below:', SNAPSHOT_I18N_DOMAIN ); ?>
							</p>

							<p class="wps-get-snapshot-key">
								<a href="<?php echo esc_attr( $data['apiKeyUrl'] ) ; ?>" id="get-snapshot-key-notice" class="button button-blue " target="_blank"><?php esc_html_e( 'Get Snapshot key', SNAPSHOT_I18N_DOMAIN ); ?></a>
							</p>

							<div id="secret-key-notice">

								<form method="post" action="?page=snapshot_pro_managed_backups" data-security="<?php echo esc_attr( $ajax_nonce ); ?>">

									<div id="secret-key-form">

										<label for="secret-key"><?php esc_html_e( 'Snapshot key', SNAPSHOT_I18N_DOMAIN ); ?></label>
										<input type="text" name="secret-key" id="secret-key" value="<?php echo esc_attr( $apiKey ); ?>" placeholder="Place your key here">

										<button type="submit" name="activate" value="yes" class="button button-gray"><?php esc_html_e( 'Save key', SNAPSHOT_I18N_DOMAIN ); ?></button>

									</div>

								</form>

							</div>

						</div>

					</div>

				</div>

			</div>

		</div>

	</section>

</div>

<?php $this->render("boxes/modals/popup-snapshot", false, $data, false, false); ?>