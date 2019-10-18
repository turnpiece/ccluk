<?php
$item = empty( $item ) ? '' : $item;
$managed_backups_url = WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' );
$restore_link = add_query_arg(
	array(
		'snapshot-action'                    => 'restore',
		'item'                               => $item,
		'snapshot-full_backups-noonce-field' => wp_create_nonce( 'snapshot-full_backups' ),
	),
	$managed_backups_url
);
?>

<div id="managed-backup-upload-progress" class="hidden snapshot-three wps-page-builder">
	<section class="wpmud-box">
		<div class="wpmud-box-title has-button">
			<h3><?php esc_html_e( 'Upload Backup', SNAPSHOT_I18N_DOMAIN ); ?></h3>
			<a href="#view-log-file"
			   class="button button-small button-outline button-gray">

				<?php esc_html_e( 'Show Log', SNAPSHOT_I18N_DOMAIN ); ?>
			</a>
		</div>

		<div class="wpmud-box-content">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<?php
					$this->render( "managed-backups/partials/upload-error", false, array(
						'managed_backups_url' => $managed_backups_url,
					), false, false );

					$this->render( "managed-backups/partials/upload-success", false, array(
						'restore_link'        => $restore_link,
						'managed_backups_url' => $managed_backups_url,
					), false, false );

					$this->render( "managed-backups/partials/upload-abort", false, array(), false, false );

					$this->render( "managed-backups/partials/upload-progress", false, array(), false, false );
					?>
				</div>
			</div>
		</div>
	</section>
</div>