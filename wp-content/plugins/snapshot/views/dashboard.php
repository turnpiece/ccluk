
<section id="header">
    <h1><?php esc_html_e( 'Dashboard', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-dashboard">

	<div class="row">

		<div class="col-xs-12">

			<?php

			$model  = new Snapshot_Model_Full_Backup();
			$apiKey = $model->get_config( 'secret-key', '' );

			$is_client = $model->is_dashboard_active() && $model->has_dashboard_key();
			$has_snapshot_key = $is_client && Snapshot_Model_Full_Remote_Api::get()->get_token() !== false && ! empty( $apiKey );

			$data = array(
				"hasApikey" => ! empty( $apiKey ),
				"apiKey" => $apiKey,
				"apiKeyUrl" => $model->get_current_secret_key_link(),
				"is_client" => $is_client,
				"has_snapshot_key" => $has_snapshot_key
			);

			$this->render( "boxes/dashboard/widget-status", false, $data, false, false );

			?>

		</div>

	</div>

	<div class="row">

		<div class="col-xs-12 col-md-6">
			<?php

			$this->render( 'boxes/dashboard/widget-snapshots', false, array(), false, false );
			$this->render( 'boxes/dashboard/widget-backups', false, $data, false, false );

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

$this->render( 'boxes/modals/popup-welcome', false, $data, false, false );
$this->render( 'boxes/modals/popup-snapshot', false, $data, false, false );