<?php
	$model = new Snapshot_Model_Full_Backup();
	$snapshots =  $model->get_backups();

	$is_dashboard_active = $model->is_dashboard_active();
	$is_dashboard_installed = $is_dashboard_active
		? true
		: $model->is_dashboard_installed()
	;
	$has_dashboard_key = $model->has_dashboard_key();

	$is_client = $is_dashboard_installed && $is_dashboard_active && $has_dashboard_key;

	$apiKey = $model->get_config('secret-key', '');

	$has_snapshot_key = $is_client && Snapshot_Model_Full_Remote_Api::get()->get_token() !== false && !empty($apiKey);
	$has_backups = !empty( $snapshots );
?>

<section class="wpmud-box wps-widget-backups">

	<?php
	if (true === $has_snapshot_key) :
		$wpmu_box_title = ' has-button';
	else :
		$wpmu_box_title = ' has-tag';
	endif;
	?>

	<div class="wpmud-box-title<?php echo esc_attr( $wpmu_box_title ); ?>">

		<h3>
			<?php esc_html_e('Backups', SNAPSHOT_I18N_DOMAIN); ?>
			<span class="wps-count wps-hosting-backups-count" style="display: none;"><?php echo count( $snapshots ); ?></span>
		</h3>

	</div>

	<?php
	if ( $is_client ) {
		$widget_class = 'wps-pro';
	} else {
		$widget_class = 'wps-free';
	}
	?>

	<div class="wpmud-box-content <?php echo esc_attr( $widget_class ); ?>">

		<div class="row">

			<div class="col-xs-12">
				<p id="wps-hosting-backups-disclaimer"><?php esc_html_e( "Here's a list of available full website backups.", SNAPSHOT_I18N_DOMAIN ); ?></p>

				<div class="wps-hosting-backup-no-backup" style="display: none;">

					<div class="wps-image img-snappie-two"></div>

					<p><?php esc_html_e('We backup your site daily and retain backups for 30 days as part of your hosting plan. Your first backup will show here within 24 hours, or you can start it now!' , SNAPSHOT_I18N_DOMAIN); ?></p>

					<p><a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-hosting-backups') ); ?>" class="button button-blue <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?>"><?php esc_html_e( 'Run Backup' , SNAPSHOT_I18N_DOMAIN ); ?></a></p>

				</div>

				<div class="wps-hosting-backup-list" style="display: none;">

					<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />

					<div class="my-backups-content sui-component-snapshot">

						<table cellpadding="0" cellspacing="0">
							<thead>
							<tr>
								<th class="msc-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
								<th class="msc-size"><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></th>
								<th class="msc-date"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></th>
							</tr>

							</thead>
						</table>

						<table id="my-hosting-backups-table" cellpadding="0" cellspacing="0">

							<tbody>

							</tbody>

						</table>

						<table cellpadding="0" cellspacing="0">
							<tfoot>
								<tr>
									<td colspan="2">
										<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-hosting-backups') ); ?>" class="button button-outline button-gray">
											<?php esc_html_e( 'View All', SNAPSHOT_I18N_DOMAIN ); ?>
										</a>

										<p>
											<small>
												<?php printf( esc_html__( 'Backups are running Daily/%s', SNAPSHOT_I18N_DOMAIN ),  esc_html( Snapshot_Helper_Utility::get_hosting_backup_local_time() ) ); ?>
                                            </small>
										</p>

									</td>
								</tr>
							</tfoot>
						</table>

					</div>
				</div>

				<div class="wps-hosting-backup-list-loader">

					<div class="wpmud-box-gray">

						<p class="wps-hosting-spinner">

							<h3><?php echo esc_html_e( 'Loading Backups&hellip;', SNAPSHOT_I18N_DOMAIN ); ?></h3>

						</p>

					</div>

				</div>

				<div class="wps-notice-wrapper wps-backup-list-ajax-error hidden">
					<div class="wps-auth-message error">
						<p><?php echo wp_kses_post( sprintf(__( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', SNAPSHOT_I18N_DOMAIN ), 'https://premium.wpmudev.org/hub/support/#get-support' ) ); ?></p>
					</div>
					<a class="button button-small button-gray button-outline wps-reload-backup-listing"><i class="wdv-icon wdv-icon-fw wdv-icon-repeat"></i><?php esc_html_e('RELOAD', SNAPSHOT_I18N_DOMAIN ); ?></a>
				</div>

			</div>

		</div>

	</div>

</section>