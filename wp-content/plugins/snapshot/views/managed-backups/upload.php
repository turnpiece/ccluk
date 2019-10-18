<?php
check_admin_referer( 'snapshot-full_backups', 'snapshot-full_backups-noonce-field' );
$item = empty( $_GET['item'] ) ? '' : $_GET['item'];

if ( ! $item ) {
	return;
}
?>

<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container"
     class="wps-page-upload-backup snapshot-three wps-page-wizard">

	<?php
	$this->render(
		"managed-backups/partials/upload-backup-in-progress",
		false,
		array( 'item' => $item ),
		false,
		false
	);
	?>

	<?php
	$this->render(
		"managed-backups/partials/upload-backup-form",
		false,
		array( 'item' => $item ),
		false,
		false
	);
	?>
</div>

<?php $this->render( "boxes/modals/popup-dynamic", false, array(
	'modal_id'      => "wps-snapshot-log",
	'modal_title'   => __( 'Managed Backups Log', SNAPSHOT_I18N_DOMAIN ),
	'modal_content' => __( "<p>Here's a log of events for managed backups.</p>", SNAPSHOT_I18N_DOMAIN ),
), false, false ); ?>