<?php
$time_key = time();
$item = array();

while ( true ) {
	if ( ! isset( WPMUDEVSnapshot::instance()->config_data['items'][ $time_key ] ) ) {
		break;
	}
	$time_key = time();
}

$requirements_test = Snapshot_Helper_Utility::check_system_requirements();
$checks = $requirements_test['checks'];
$all_good = $requirements_test['all_good'];
$warning = $requirements_test['warning'];

$disabled = $model->has_api_error() ? 'disabled="disabled"' : '';
$cron_disabled = $model->get_config( 'disable_cron', false );

?>

<section id="header">
	<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<?php
$this->render(
	"managed-backups/partials/create-backup-progress", false, array(
		'item' => $item,
		'time_key' => $time_key
	), false, false
);
?>

<div id="snapshot-ajax-out">
	<div class="out"></div>
</div>

<form id="managed-backup-update" method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'settings', WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ) ); ?>">

	<input type="hidden" id="snapshot-action" name="snapshot-action" value="update-managed-backup-setting"/>

	<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />

	<input type="hidden" id="snapshot-backup-action" name="snapshot-schedule" value="yes"/>

	<?php wp_nonce_field( 'snapshot-full_backups', 'snapshot-full_backups-noonce-field' ); ?>

	<div id="container" class="snapshot-three wps-page-wizard">

		<section class="wpmud-box new-snapshot-main-box">

			<div class="wpmud-box-title has-button">

				<h3><?php esc_html_e( 'Backups Wizard', SNAPSHOT_I18N_DOMAIN ); ?></h3>

				<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>" class="button button-small button-gray button-outline"><?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?></a>

			</div>

			<div class="wpmud-box-content">

				<?php $this->render( "common/requirements-test", false, $requirements_test, false, false ); ?>

				<div class="wpmud-box-tab configuration-box<?php if ( $all_good ) echo ' open'; ?>">

					<div class="wpmud-box-tab-title can-toggle">
						<h3><?php esc_html_e( 'Configuration', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						<?php if ( $all_good ): ?>
							<i class="wps-icon i-arrow-right"></i>
						<?php endif; ?>
					</div>

					<?php if ( $all_good ): ?>

						<div class="wpmud-box-tab-content">

							<div id="wps-check-notice" class="row">

								<div class="col-xs-12">
                                    <?php
                                    if ( ! $all_good ) {
										$wps_auth_message = 'error';
									} else if ( $warning ) {
										$wps_auth_message = 'warning';
									} else {
										$wps_auth_message = 'success';
									}
                                    ?>
									<div class="wps-auth-message <?php echo esc_attr( $wps_auth_message ); ?>">
										<?php if ( ! $all_good ) { ?>
											<p><?php esc_html_e( 'You must meet the server requirements before proceeding.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else if ( $warning ) { ?>
											<p><?php esc_html_e( 'You have 1 or more requirements warnings. You can proceed, however Snapshot may run into issues due to the warnings.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } else { ?>
											<p><?php esc_html_e( 'You meet the server requirements. You can proceed now.', SNAPSHOT_I18N_DOMAIN ); ?></p>
										<?php } ?>
									</div>

								</div>

							</div>

							<div id="wps-new-destination" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Destination', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">
                                        <?php

											$storage = Snapshot_Model_Full_Remote_Storage::get();

											$label_title = sprintf(
													__( "Managed backups can only be stored on WPMU DEV's cloud servers. You have <strong>%1\$s</strong> of your %2\$s storage remaining.", SNAPSHOT_I18N_DOMAIN ),
													size_format( $storage->get_free_remote_space() ),
													size_format( $storage->get_total_remote_space() )
												);
										?>
										<label class="label-title"><?php echo wp_kses_post( $label_title ); ?></label>

										<div class="wpmud-box-gray">

											<div class="radio-destination">


												<div class="wps-input--item">

													<div class="wps-input--radio">

														<input checked="checked" type="radio">

														<label for="snap-cloud"></label>

													</div>

													<label for="snap-cloud"><span><?php esc_html_e( 'WPMU DEV Cloud', SNAPSHOT_I18N_DOMAIN ); ?></span><i class="wps-typecon cloud"></i></label>

												</div>

											</div>
										</div>

									</div>

								</div>

							</div>

							<div id="wps-new-frequency" class="row">

								<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">

									<label class="label-box"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></label>

								</div>

								<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">

									<div class="wpmud-box-mask">

										<label class="label-title">
											<?php esc_html_e( 'Would you like to schedule managed backups to run regularly or once-off?', SNAPSHOT_I18N_DOMAIN ); ?>
										</label>

										<div class="wps-input--group">

											<div class="wps-input--item">

												<div class="wps-input--radio">
													<input id="frequency-once" type="radio"<?php checked( $model->get_config( 'disable_cron', false ) ); ?> name="frequency" value="once">

													<label for="frequency-once"></label>

												</div>
												<label for="frequency-once"><?php esc_html_e( 'Once-off', SNAPSHOT_I18N_DOMAIN ); ?></label>
											</div>

											<div class="wps-input--item">
												<div class="wps-input--radio">
													<input id="frequency-daily" type="radio" name="frequency" value="schedule"
	                                                    <?php
														checked( ! $model->get_config( 'disable_cron', false ) );
	                                                    ?>
                                                    >

													<label for="frequency-daily"></label>
												</div>

												<label for="frequency-daily"><?php esc_html_e( 'Run daily, weekly or monthly', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

										<div id="snapshot-schedule-options-container" class="wpmud-box-gray">

											<h3><?php esc_html_e( 'Schedule', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<?php Snapshot_Model_Request::nonce( 'snapshot-full_backups-schedule' ); ?>

											<div class="wps-new-backups-schedule schedule-inline-form">

												<select id="frequency" name="frequency" <?php echo esc_attr( $disabled ); ?> >
													<?php foreach ( $model->get_frequencies() as $key => $label ) { ?>
														<option
																value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $model->get_frequency() ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

												<select id="schedule_time" name="schedule_time" <?php echo esc_attr( $disabled ); ?> >
													<?php foreach ( $model->get_schedule_times() as $key => $label ) { ?>
														<option
																value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $model->get_schedule_time() ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

											</div>

											<h3><?php esc_html_e( 'Storage Limit', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="storage-inline-form">

												<span class="inbetween">Keep</span>

												<?php
												if ( ! isset( $item['archive-count'] ) ) {
													$item['archive-count'] = Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit();
												}

												?>
												<input type="number" name="backups-limit" id="snapshot-archive-count"
												       value="<?php echo esc_attr( $item['archive-count'] ); ?>">

												<span class="inbetween"><?php esc_html_e( 'backups before removing older archives.', SNAPSHOT_I18N_DOMAIN ); ?></span>

											</div>

											<p>
												<small><?php esc_html_e( "By default, Snapshot will run as many scheduled backups as you need. We recommend that you remove older backups to avoid filling your remote storage limit. If you would like to keep all of your backup archives, just set your storage limit to 0.", SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

											<h3><?php esc_html_e( 'Optional', SNAPSHOT_I18N_DOMAIN ); ?></h3>

											<div class="wps-input--item">

												<div class="wps-input--checkbox">

													<input type="checkbox" id="checkbox-run-backup-now" class="" value="1" checked/>

													<label for="checkbox-run-backup-now"></label>

												</div>

												<label for="checkbox-run-backup-now"><?php esc_html_e( 'Also run a backup now', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

										</div>

									</div>

								</div>

							</div>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<div class="form-button-container form-button-single">

										<button type="submit" class="button button-blue"
										        data-update-settings-text="<?php esc_attr_e( 'Update Settings', SNAPSHOT_I18N_DOMAIN ); ?>"
										        data-run-backup-text="<?php esc_attr_e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>">
											<?php esc_html_e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>
										</button>

									</div>

								</div>

							</div>

						</div>

					<?php endif; ?>

				</div>

			</div>

		</section>

	</div>
</form>