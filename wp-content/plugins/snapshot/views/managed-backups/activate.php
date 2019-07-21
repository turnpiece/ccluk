
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

						<p><?php echo wp_kses_post( sprintf( __( '%s, as a WPMU DEV member you get 10GB free cloud storage included in your membership. Create and store full backups of your website, including WordPress core files. And if disaster strikes, you can quickly and easily restore your website any time. <br/>Add your Snapshot Key to enable this service.', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></p>

					</div>

					<p>
						<a id="view-snapshot-key" class="button <?php echo !empty($apiKey) ? 'has-key' : ''; ?> button-blue <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?>"><?php esc_html_e( 'Activate Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></a>
					</p>

				</div>

			</div>

		</div>

	</section>

</div>

<?php $this->render("boxes/modals/popup-snapshot", false, $data, false, false); ?>
