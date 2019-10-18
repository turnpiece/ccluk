<?php
$item = empty( $item ) ? '' : $item;
$checks = array(
	'PhpVersion' => array(
		'test'  => version_compare( PHP_VERSION, '5.5.0', '>=' ),
		'value' => PHP_VERSION,
	),
	'Mysqli'     => array( 'test' => true ),
	'Zip'        => array( 'test' => true ),
);
$requirements_test = Snapshot_Helper_Utility::check_system_requirements( $checks );
$disable_upload = empty( $requirements_test['checks']['PhpVersion']['test'] );
?>
<form id="managed-backup-upload" method="post" action="">
	<input type="hidden" id="archive"
	       name="archive" class="widefat archive"
	       value="<?php echo esc_attr( sanitize_text_field( $item ) ); ?>"/>

	<input type="hidden" name="security"
	       id="snapshot-ajax-nonce"
	       value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>"/>

	<section class="wpmud-box new-snapshot-main-box">
		<div class="wpmud-box-title has-button">
			<h3><?php esc_html_e( 'Upload Backup', SNAPSHOT_I18N_DOMAIN ); ?></h3>

			<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>"
			   class="button button-small button-gray button-outline">

				<?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?>
			</a>
		</div>

		<div class="wpmud-box-content">
			<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

			<div class="wpmud-box-tab configuration-box open">
				<div class="wpmud-box-tab-content">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="form-button-container">
								<span></span>
								<button type="submit" <?php echo $disable_upload ? 'disabled' : ''; ?>
								        class="button button-blue <?php echo $disable_upload ? 'disabled' : ''; ?>">
									<?php esc_html_e( 'Upload Backup', SNAPSHOT_I18N_DOMAIN ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>
</form>