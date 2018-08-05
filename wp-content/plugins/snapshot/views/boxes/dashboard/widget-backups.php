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

		<h3
        <?php
        if ( $has_backups && $has_snapshot_key ) {
			echo ' class="has-count"'; }
		?>
		>
			<?php esc_html_e('Managed Backups', SNAPSHOT_I18N_DOMAIN); ?>

			<?php if ( $has_backups && $has_snapshot_key ) { ?>
				<span class="wps-count"><?php echo count( $snapshots ); ?></span>
			<?php } ?>
		</h3>

		<?php if ( true === $is_client ) { ?>

			<?php
            if ( true === $has_snapshot_key ) { /*

				<a href="<?php echo WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-settings') . '#wps-settings-backups'; ?>" class="button button-small button-outline button-gray"><?php _e('Configure' , SNAPSHOT_I18N_DOMAIN); ?></a>

				*/
				assert(true);
			}
?>

		<?php } else { ?>

			<span class="wps-tag wps-tag--green"><?php esc_html_e('Pro Feature', SNAPSHOT_I18N_DOMAIN); ?></span>

		<?php } ?>

	</div>

	<div class="wpmud-box-content<?php echo $is_client ? ' wps-pro' : ' wps-free'; ?>
	<?php if ( ( true === $has_snapshot_key )&&( true === $has_backups ) ) echo ' wps-pro-backups'; ?>">

		<div class="row">

			<div class="col-xs-12">

				<?php
                if ( true === $has_snapshot_key ) :

					if ( true === $has_backups ) :
                    ?>

						<p><?php echo wp_kses_post( sprintf( __( 'Backup your entire WordPress installation and store it securely in the <a href="%s">Hub</a> for simple site migration and one-click restoration.', SNAPSHOT_I18N_DOMAIN ), 'https://premium.wpmudev.org/hub/' ) ); ?></p>

						<table class="has-footer" cellpadding="0" cellspacing="0">

							<thead>
								<tr>
									<th class="wpsb-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
									<th class="wpsb-date"><?php esc_html_e( 'Date', SNAPSHOT_I18N_DOMAIN ); ?></th>
								</tr>
							</thead>

							<tbody>

							<?php

							/* Sort the backups by timestamp, descending */
							function _snapshot_sort_managed_backups_array( $a, $b ) {
								return - strcmp( $a['timestamp'], $b['timestamp'] );
							}

							usort( $snapshots, '_snapshot_sort_managed_backups_array' );

							foreach ( $snapshots as $key => $snapshot ) :

								if ( Snapshot_Helper_Backup::is_automated_backup( $snapshot['name'] ) ) {
									$backup_type_class = 'automated';
								} else {
									if ( ! empty( $snapshot['local'] ) ) {
										$backup_type_class = 'local';
									} else {
										$backup_type_class = 'cloud';
									}
								}

								?>

								<tr>
									<td class="wpsb-name">
										<span class="wps-typecon <?php echo esc_attr( $backup_type_class ); ?>"></span>
										<p>
											<?php echo esc_html( $snapshot['name'] ); ?>
											<small><?php echo esc_html( size_format( $snapshot['size'] ) ); ?></small>
										</p>

									<td class="wpsb-date"><?php echo esc_html( Snapshot_Helper_Utility::show_date_time( $snapshot['timestamp'], 'F j, Y' ) ); ?></td>
								</tr>

							<?php endforeach; ?>

							</tbody>
							<tfoot>
								<tr>
									<td colspan="2">
										<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>" class="button button-outline button-gray">
											<?php esc_html_e( 'View All', SNAPSHOT_I18N_DOMAIN ); ?>
										</a>

										<p>
											<small>
                                            <?php

												if ( $model->get_config( 'disable_cron', false ) ) {
													esc_html_e( 'Scheduled backups are disabled', SNAPSHOT_I18N_DOMAIN );

												} else {
													$schedule_times = $model->get_schedule_times();
													$frequencies = $model->get_frequencies( false );
													echo wp_kses_post(
                                                        sprintf(
															__( 'Backups are running %1$s at %2$s', SNAPSHOT_I18N_DOMAIN ),
															$frequencies[ $model->get_frequency() ],
															$schedule_times[ $model->get_schedule_time() ]
														)
													);
												}
                                                ?>
                                                </small>
										</p>

									</td>
								</tr>
							</tfoot>

						</table>

					<?php else: ?>

						<div class="wps-image img-snappie-two"></div>

						<p><?php esc_html_e('Automatically backup your entire website on a regular basis and store those backups on WPMU DEV\'s secure cloud servers. Restore your full website at anytime via the WPMU DEV Hub.', SNAPSHOT_I18N_DOMAIN); ?></p>

						<p><a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-managed-backups') ); ?>" class="button button-blue"><?php esc_html_e('Backup my site' , SNAPSHOT_I18N_DOMAIN); ?></a></p>

					<?php endif; ?>

				<?php else : ?>

					<div class="wps-image img-snappie-two"></div>

					<?php if ( true === ! $is_client ) : ?>

						<p><?php esc_html_e('Automatically backup your entire website on a regular basis and store those backups on WPMU DEV\'s secure cloud servers. Restore your full website at anytime via the WPMU DEV Hub.' , SNAPSHOT_I18N_DOMAIN); ?></p>

						<div class="wps-cta-box">

							<div class="wps-cta">

								<div class="wps-cta-text"><?php echo wp_kses_post( __( 'Fully automated managed backups are included in a WPMU DEV membership along with 100+ plugins & themes, 24/7 support and lots of handy site management tools  – <strong>Try it all absolutely FREE</strong>' , SNAPSHOT_I18N_DOMAIN ) ); ?></div>

							</div>

						</div>

					<?php else: ?>

						<p><?php esc_html_e('Automatically backup your entire website on a regular basis and store those backups on WPMU DEV\'s secure cloud servers. Restore your full website at anytime via the WPMU DEV Hub.', SNAPSHOT_I18N_DOMAIN); ?></p>

						<p><a id="view-snapshot-key-2" class="button <?php echo !empty($apiKey) ? 'has-key' : ''; ?> button-blue"><?php esc_html_e( 'Add Snapshot Key' , SNAPSHOT_I18N_DOMAIN ); ?></a></p>

					<?php endif; ?>

				<?php endif; ?>

			</div>

		</div>

	</div>

</section>