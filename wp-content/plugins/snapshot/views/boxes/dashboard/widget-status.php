<?php

$backups = WPMUDEVSnapshot::instance()->config_data['items'];

$backup_status = array(
	'title' => __( 'No Backups', SNAPSHOT_I18N_DOMAIN ),
	'content' => __( "You haven't backed up your site yet. Create your first backup now<br>– it'll only take a minute.", SNAPSHOT_I18N_DOMAIN ),
	'date' => __( 'Never', SNAPSHOT_I18N_DOMAIN ),
	'size' => __( '-', SNAPSHOT_I18N_DOMAIN ),
);

$model = new Snapshot_Model_Full_Backup();

$is_dashboard_active = $model->is_dashboard_active();
$is_dashboard_installed = $is_dashboard_active && $model->is_dashboard_installed();
$has_dashboard_key = $model->has_dashboard_key();

$is_client = $is_dashboard_active && $has_dashboard_key;

$apiKey = $model->get_config( 'secret-key', '' );

$has_snapshot_key = $is_client && Snapshot_Model_Full_Remote_Api::get()->get_token() !== false && ! empty( $apiKey );

if ( ! empty( $latest_backup ) && $latest_backup ) {

	$one_week_ago = strtotime( '-1 week' );
	if ( $latest_backup['timestamp'] > $one_week_ago ) {
		$backup_status['title'] = __( 'All Backed up', SNAPSHOT_I18N_DOMAIN );
		$backup_status['content'] = __( 'Your last backup was created less than a week ago. Excellent work!', SNAPSHOT_I18N_DOMAIN );
	} else {
		$backup_status['title'] = __( 'Getting Older', SNAPSHOT_I18N_DOMAIN );
		$backup_status['content'] = __( 'Your last backup was over a week ago. Make sure you\'re backing up regulary!', SNAPSHOT_I18N_DOMAIN );
	}
	$backup_status['date'] = sprintf( _x( '%s ago', '%s = human-readable time difference', SNAPSHOT_I18N_DOMAIN ), human_time_diff( $latest_backup['timestamp'] ) );
	$backup_status['size'] = size_format( $latest_backup['file_size'] );
}

$snapshot = WPMUDEVSnapshot::instance()->config_data['items'];
$latest_snapshot = Snapshot_Helper_Utility::latest_backup( $snapshot );

?>

<section class="wps-backups-status<?php if ( ! $is_client ) echo ' wps-backups-status-free'; ?> wpmud-box">

	<div class="wpmud-box-content">
		<div class="wps-backups-summary">

			<div class="wps-backups-summary-align">

				<h3><?php echo wp_kses_post( sprintf( __( 'Hello, %s!', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></h3>

				<?php if ( $is_client ) : ?>
					<p><?php esc_html_e( 'Welcome to the Dashboard. Here you can manage all your snapshots and backups.', SNAPSHOT_I18N_DOMAIN ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Welcome to the Dashboard. Here you can manage all your snapshots.', SNAPSHOT_I18N_DOMAIN ); ?></p>
				<?php endif; ?>

			</div>
		</div>

		<div class="wps-backups-details">
			<table cellpadding="0" cellspacing="0">
				<tbody>
				<tr>
					<th><?php esc_html_e( 'Last Snapshot', SNAPSHOT_I18N_DOMAIN ); ?></th>

					<?php if ( isset( $latest_snapshot['timestamp'] ) ) : ?>
						<td class="fancy-date-time">
							<?php echo esc_html( Snapshot_Helper_Utility::show_date_time( $latest_snapshot['timestamp'], 'F j, Y ' ) ); ?>
							<span>
                            <?php
								echo wp_kses_post(
									sprintf(
											esc_html__( 'at %s', SNAPSHOT_I18N_DOMAIN ),
											Snapshot_Helper_Utility::show_date_time( $latest_snapshot['timestamp'], 'g:ia' )
									)
								);
							?>
							</span>
						</td>
					<?php else: ?>
						<td><?php esc_html_e( 'Never', SNAPSHOT_I18N_DOMAIN ); ?></span></td>
					<?php endif; ?>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Available Destinations', SNAPSHOT_I18N_DOMAIN ); ?></th>
					<td>
						<span class="wps-count"><?php echo count( WPMUDEVSnapshot::instance()->config_data['destinations'] ); ?></span>
					</td>
				</tr>

				<?php if ( $is_client ) : ?>
					<tr>
						<th><?php esc_html_e( 'Managed Backups Schedule', SNAPSHOT_I18N_DOMAIN ); ?></th>

						<?php if ( ! $has_snapshot_key ) { ?>
							<td>
								<a id="view-snapshot-key" class="button button-small button-blue"><?php esc_html_e( 'Activate', SNAPSHOT_I18N_DOMAIN ); ?></a>
							</td>
						<?php } elseif ( $model->get_config( 'disable_cron', false ) ) { ?>

							<td>
								<a id="wps-managed-backups-configure" class="button button-outline button-small button-gray"
								   href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) . '#wps-backups-settings-schedule' ); ?>">
									<?php esc_html_e( 'Enable', SNAPSHOT_I18N_DOMAIN ); ?>
								</a>
							</td>

						<?php } else { ?>

							<td class="fancy-date-time">
								<?php
								$frequencies = $model->get_frequencies();
								echo esc_html( $frequencies[$model->get_frequency()] );
								?>
								<span>
                                <?php
									$schedule_times = $model->get_schedule_times();
									echo wp_kses_post(
										sprintf(
												esc_html__( 'at %s', SNAPSHOT_I18N_DOMAIN ),
												$schedule_times[$model->get_schedule_time()]
										)
									);

								?>
								</span>
							</td>

						<?php } ?>

					</tr>
				<?php endif; ?>

				</tbody>
			</table>

		</div>
	</div>

</section>