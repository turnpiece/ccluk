<?php
$model = new Snapshot_Model_Full_Backup();
$backups =  $model->get_backups();

$has_backups = !empty( $backups );

?>

<section id="header">
	<h1><?php esc_html_e( 'Destinations', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-destinations">

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

	<?php

	if ( $has_backups ) {
		$this->render( "boxes/widget-notification-managed-backups", false, array(), false, false );
	}

	$destination_types = array( 'dropbox', 'google', 'amazon', 'sftp', 'local' );

	foreach ( $destination_types as $destination_type ) {
		$this->render( 'boxes/destinations/widget-' . $destination_type, false, array(), false, false );
	}

	?>
</div>
<?php
if( Snapshot_Helper_Utility::is_wpmu_hosting() ) {
	$this->render( 'boxes/modals/popup-hosting', false, array(), false, false );
}

WPMUDEVSnapshot::instance()->need_show_v4_modal() && $this->render( 'boxes/modals/popup-upgrade-to-v4', false, array(), false, false );