<section id="header">
    <h1><?php esc_html_e( 'Dashboard', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-dashboard">

	<?php
		WPMUDEVSnapshot::instance()->need_show_v4_notice() && $this->render(
			'common/v4-notice',
			false,
			array(
				'bg_image_url' => WPMUDEVSnapshot::get_file_url( '/assets/img/snapshot-hero-notice.svg' ),
			),
			false,
			false
		);
	?>

	<div class="row">

		<div class="col-xs-12">

			<?php

			$model  = new Snapshot_Model_Full_Backup();
			$apiKey = $model->get_config( 'secret-key', '' );

			$is_client = $model->is_dashboard_active() && $model->has_dashboard_key();
			$has_snapshot_key = $is_client && Snapshot_Model_Full_Remote_Api::get()->get_token() !== false && ! empty( $apiKey );

			if ( version_compare(PHP_VERSION, '5.5.0', '<') ) {
				$aws_sdk_compatible = false;
			} else {
				$aws_sdk_compatible = true;
			}

			$data = array(
				"hasApikey" => ! empty( $apiKey ),
				"apiKey" => $apiKey,
				"apiKeyUrl" => $model->get_current_secret_key_link(),
				"is_client" => $is_client,
				"has_snapshot_key" => $has_snapshot_key,
				"aws_sdk_compatible" => $aws_sdk_compatible
			);

			?>

			<?php

			$this->render( "boxes/dashboard/widget-status", false, $data, false, false );

			?>

		</div>

	</div>

	<div class="row">

		<div class="col-xs-12 col-md-6">
			<?php

			$this->render( 'boxes/dashboard/widget-snapshots', false, array(), false, false );

			if ( Snapshot_Helper_Utility::is_wpmu_hosting() ) {
				$this->render( 'boxes/dashboard/widget-hosting-backups', false, $data, false, false );
			} else {
				$this->render( 'boxes/dashboard/widget-backups', false, $data, false, false );
			}

			?>

		</div>

		<div class="col-xs-12 col-md-6">

			<?php

			$this->render( "boxes/dashboard/widget-destinations", false, array(), false, false );

			if ( ! $is_client ) {
				$this->render( "boxes/dashboard/widget-try-pro", false, array(), false, false );
			}

			?>

		</div>

	</div>

</div>

<?php

if( Snapshot_Helper_Utility::is_wpmu_hosting() ) {
	$this->render( 'boxes/modals/popup-hosting', false, array(), false, false );
}else {
	$this->render( 'boxes/modals/popup-welcome', false, $data, false, false );
}

$this->render( 'boxes/modals/popup-snapshot', false, $data, false, false );

WPMUDEVSnapshot::instance()->need_show_v4_modal() && $this->render( 'boxes/modals/popup-upgrade-to-v4', false, array(), false, false );