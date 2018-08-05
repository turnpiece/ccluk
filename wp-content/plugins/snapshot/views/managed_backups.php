<?php // phpcs:ignore

$storage_status = array(
	'used' => Snapshot_Model_Full_Remote_Storage::get()->get_used_remote_space(),
	'free' => Snapshot_Model_Full_Remote_Storage::get()->get_free_remote_space(),
	'total' => Snapshot_Model_Full_Remote_Storage::get()->get_total_remote_space(),
);

$percentage = $storage_status['used'] ? round( ( $storage_status['used'] / $storage_status['total'] ) * 100, 1 ) : 0;
if ( $percentage > 100 )
	$percentage = 100;

$data = array(
	'snapshots' => $backups,
	'results_count' => $results_count,
	'per_page' => $per_page,
	'max_pages' => $max_pages,
	'paged' => $paged,
	'offset' => $offset,
);

$disabled = $model->has_api_error() ? 'disabled="disabled"' : '';
$cron_disabled = $model->get_config( 'disable_cron', false );

$model = new Snapshot_Model_Full_Backup();

?>

	<section id="header">
		<h1><?php esc_html_e( 'Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
	</section>

	<div id="container" class="snapshot-three wps-page-backups">

		<section class="wpmud-box wps-widget-backups_status">

			<div class="wpmud-box-content">

				<div class="wps-backups-summary">

					<div class="wps-backups-summary-align">

						<div class="wps-summary-details">

							<div class="wps-summary-percentage">
								<figure class="chart-backups animate" title="<?php echo esc_attr( round( $percentage, 1 ) ); ?>%">

									<?php

									$cake_percent = min( 100, $percentage );
									$cake_percent = max( 0, $cake_percent );

									$r = 22;
									$cake_percent = ( ( 100 - $cake_percent ) / 100 ) * ( pi() * 2 * $r );

									?>

									<svg class="storage-svg" width="53" height="53">
										<circle r="22" cx="26.5" cy="26.5" fill="transparent" stroke-dasharray="0" stroke-dashoffset="0"></circle>
										<circle class="storage-cake" r="22" cx="5.4" cy="28.3" fill="transparent" stroke-dasharray="138" stroke-dashoffset="0"
										        style="stroke-dashoffset: <?php echo esc_attr( $cake_percent ); ?>px;"></circle>
									</svg>

							</div>

							<div class="wps-summary-text">
								<h1><?php echo $storage_status['used'] ? esc_html( size_format( $storage_status['used'], 1 ) ) : 0 ; ?>
									/ <?php echo esc_html( size_format( $storage_status['total'] ) ); ?></h1>
								<h5><?php esc_html_e( 'Cloud Storage Used', SNAPSHOT_I18N_DOMAIN ); ?></h5>
							</div>

						</div>

					</div>

				</div>

				<div class="wps-backups-details">
					<table cellpadding="0" cellspacing="0">
						<tbody>

						<tr>

							<th><?php esc_html_e( 'Last backup', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<?php if ( isset( $last_backup['timestamp'] ) ) : ?>
								<td>
									<?php echo esc_html( Snapshot_Helper_Utility::show_date_time( $last_backup['timestamp'], 'F j, Y ' ) ); ?>
									<span><?php echo wp_kses_post( __( 'at' ) . ' ' . Snapshot_Helper_Utility::show_date_time( $last_backup['timestamp'], 'g:ia' ) ); ?></span>
								</td>
							<?php else : ?>
								<td><?php esc_html_e( 'Never', SNAPSHOT_I18N_DOMAIN ); ?></span></td>
							<?php endif; ?>

						</tr>

						<tr>

							<th><?php esc_html_e( 'Snapshot Key', SNAPSHOT_I18N_DOMAIN ); ?>
								<?php
								if ( $hasApikey ) :
								?>
									<i class="wps-icon i-check"></i>
								<?php endif; ?>
							</th>

							<td>

								<?php if ( $hasApikey ) : ?>

									<a id="view-snapshot-key"
									   class="button has-key button-outline button-small button-gray"><?php esc_html_e( 'View Key', SNAPSHOT_I18N_DOMAIN ); ?></a>

								<?php else : ?>

									<a id="view-snapshot-key"
									   class="button button-outline button-small button-gray"><?php esc_html_e( 'Add snapshot key', SNAPSHOT_I18N_DOMAIN ); ?></a>

								<?php endif; ?>

							</td>

						</tr>

						<tr>
							<th><?php esc_html_e( 'Backups Schedule', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>

								<?php if ( ! $model->get_config( 'disable_cron', false ) ) { ?>
									<span class="wps-backups-schedule-summary">
                                    <?php
										$schedule_times = $model->get_schedule_times();
										$frequencies = $model->get_frequencies();
										echo wp_kses_post(
											sprintf(
												esc_html__( '%1$s at %2$s', SNAPSHOT_I18N_DOMAIN ),
												$frequencies[ $model->get_frequency() ],
												$schedule_times[ $model->get_schedule_time() ]
											)
										);

										?>
                                        </span>
								<?php } ?>

								<a id="wps-managed-backups-configure" class="button button-outline button-small button-gray">
									<?php
                                    echo $model->get_config( 'disable_cron', false ) ?
										esc_html__( 'Enable', SNAPSHOT_I18N_DOMAIN ) :
										esc_html__( 'Configure', SNAPSHOT_I18N_DOMAIN );
                                        ?>
								</a>
							</td>
						</tr>

						</tbody>

					</table>

				</div>

			</div>

		</section>

		<?php
		$backup_menu = 'backups';
		if ( isset( $_GET['tab'] ) ) {
			if ( ! isset( $_REQUEST['snapshot-full_backups-noonce-field']  ) ) {
				return;
			}
			if ( ! wp_verify_nonce( $_REQUEST['snapshot-full_backups-noonce-field'], 'snapshot-full_backups' ) ) {
				return;
			}
			$backup_menu = sanitize_text_field( $_GET['tab'] );
		}
		?>

		<section class="wps-managed-backups-tabs">

			<aside class="wps-managed-backups-menu">
				<input type="radio" name="wps-managed-backups-menu" id="wps-managed-backups-menu-list" value="wps-managed-backups-list"<?php checked( $backup_menu, 'backups' ); ?>>
				<label for="wps-managed-backups-menu-list"><?php esc_html_e( 'Backups', SNAPSHOT_I18N_DOMAIN ); ?></label>

				<input type="radio" name="wps-managed-backups-menu" id="wps-managed-backups-menu-config" value="wps-managed-backups-configs"<?php checked( $backup_menu, 'settings' ); ?>>
				<label for="wps-managed-backups-menu-config"><?php esc_html_e( 'Settings', SNAPSHOT_I18N_DOMAIN ); ?></label>

				<select name="wps-managed-backups-menu-mobile" class="hide">
					<option value="wps-managed-backups-list"<?php selected( $backup_menu, 'backups' ); ?>><?php esc_html_e( 'Backups', SNAPSHOT_I18N_DOMAIN ); ?></option>
					<option value="wps-managed-backups-configs"<?php selected( $backup_menu, 'settings' ); ?>><?php esc_html_e( 'Settings', SNAPSHOT_I18N_DOMAIN ); ?></option>
				</select>
			</aside>

			<div class="wps-managed-backups-pages">

				<section class="wpmud-box wps-managed-backups-list wps-widget-available_backups
                <?php
                if ( 'backups' !== $backup_menu ) {
					echo ' hidden';
				}
                ?>
                ">

					<div class="wpmud-box-title has-button">

						<?php if ( 0 === $results_count ) { ?>

							<h3><?php esc_html_e( 'Backups', SNAPSHOT_I18N_DOMAIN ); ?></h3>

						<?php } else { ?>

							<h3><?php esc_html_e( 'Available Backups', SNAPSHOT_I18N_DOMAIN ); ?></h3>

							<a href="#view-log-file" class="button button-small button-outline button-gray"><?php esc_html_e( 'Show Log', SNAPSHOT_I18N_DOMAIN ); ?></a>

							<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>&snapshot-action=backup&snapshot-full_backups-noonce-field=<?php echo esc_attr( wp_create_nonce  ( 'snapshot-full_backups' ) ); ?>" class="button button-small button-blue"><?php esc_html_e( 'New Backup', SNAPSHOT_I18N_DOMAIN ); ?></a>

						<?php } ?>

					</div>

					<div class="wpmud-box-content">

						<?php if ( 0 === $results_count ) { ?>

							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<div class="wps-image img-snappie-one"></div>
									<p><?php echo wp_kses_post( sprintf( __( "%s, you've enabled Managed Backups but haven't created your first backup yet. Do it now!", SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></p>
									<p>
										<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>&amp;snapshot-action=backup&amp;snapshot-full_backups-noonce-field=<?php echo esc_attr( wp_create_nonce  ( 'snapshot-full_backups' ) ); ?>"
										   class="button button-blue"><?php esc_html_e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?></a>
									</p>
								</div>
							</div>

						<?php } else { ?>

							<div class="row">

								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

									<p><?php esc_html_e( "Here's a list of your current backups. You can restore your entire website from them at any time.", SNAPSHOT_I18N_DOMAIN ); ?></p>

									<div class="my-backups">

										<form id="snapshot-edit-listing" method="post"
										      action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshot_pro_snapshots' ) ); ?>">

											<div class="my-backups-content">

												<table cellpadding="0" cellspacing="0">
													<thead>
													<tr>
														<th class="msc-name"><?php esc_html_e( 'Backup Details', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-size"><?php esc_html_e( 'Size', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-date"><?php esc_html_e( 'Date', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-info">&nbsp;</th>
													</tr>
													</thead>

													<tbody>

													<?php

													$per_page_snapshots = array_slice( $backups, $offset, $per_page );

													foreach ( $per_page_snapshots as $key => $backup ) :

														$data_item = empty( $backup['data'] ) ? array() : Snapshot_Helper_Utility::latest_data_item( $backup['data'] );

														/* Fetch the remote link for the backup */
														$backup_link = $model->remote()->get_backup_link( $backup['timestamp'] );

														/* If there is no remote URL, build a local download link */
														if ( ! $backup_link ) {
															$backup_link = WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' );
															$backup_link = add_query_arg( 'snapshot-action', 'download-backup-archive', $backup_link );
															$backup_link = add_query_arg( 'backup-item', sanitize_text_field( $backup['timestamp'] ), $backup_link );
														}

														if ( Snapshot_Helper_Backup::is_automated_backup( $backup['name'] ) ) {
															$backup_type_class = 'automated';
														} else {
															if ( ! empty( $backup['local'] ) ) {
																$backup_type_class = 'local';
															} else {
																$backup_type_class = 'cloud';
															}
														}

														?>

														<tr>
															<td class="msc-name">
																<table cellpadding="0" cellspacing="0">
																	<tbody>
																	<tr>
																		<td class="msc-name-type">
																			<span class="wps-typecon <?php echo esc_attr( $backup_type_class ); ?>"></span>
																		</td>

																		<td class="msc-name-desc">
																			<p>
																				<a href="<?php echo esc_url( $backup_link ); ?>"><?php echo esc_html( stripslashes( $backup['name'] ) ); ?></a>
																			</p>
																		</td>
																	</tr>
																	</tbody>
																</table>
															</td>

															<td class="msc-size" data-title="<?php esc_html_e( 'Size', SNAPSHOT_I18N_DOMAIN ); ?>:">
																<?php echo isset( $backup['size'] ) ? esc_html( Snapshot_Helper_Utility::size_format( $backup['size'] ) ) : '-'; ?>
															</td>

															<td class="msc-date" data-title="<?php esc_html_e( 'Date', SNAPSHOT_I18N_DOMAIN ); ?>:">

																<?php
                                                                if ( isset( $backup['timestamp'] ) ) {

																	echo esc_html( Snapshot_Helper_Utility::show_date_time( $backup['timestamp'] ) );

																} else {

																	echo "-";

																}

																?>

															</td>

															<td class="msc-info">
																<div class="wps-menu">

																	<div class="wps-menu-dots">

																		<div class="wps-menu-dot"></div>

																		<div class="wps-menu-dot"></div>

																		<div class="wps-menu-dot"></div>

																	</div>

																	<div class="wps-menu-holder">

																		<ul class="wps-menu-list">

																			<li class="wps-menu-list-title"><?php esc_html_e( 'Options', SNAPSHOT_I18N_DOMAIN ); ?></li>
																			<?php
																					$restore_link = add_query_arg(
																							array(
																								'snapshot-action' => 'restore',
																								'item' => $backup['timestamp'],
																								'snapshot-full_backups-noonce-field' => wp_create_nonce( 'snapshot-full_backups' ),
																							),
																							WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' )
																						);

																			?>
																			<li>
																				<a href="<?php echo esc_url( $restore_link ); ?>"><?php esc_html_e( 'Restore', SNAPSHOT_I18N_DOMAIN ); ?></a>
																			</li>
																			<?php
																					$delete_link = add_query_arg(
																							array(
																								'action' => 'delete',
																								'item' => $backup['timestamp'],
																								'snapshot-full_backups-list-nonce' => wp_create_nonce( 'snapshot-full_backups-list' ),
																								'delete-bulk' => array( $backup['timestamp'] )
																							),
																							WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' )
																						);

																			?>
																			<li>
																				<a href="<?php echo esc_url( $delete_link ); ?>"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></a>
																			</li>

																		</ul>

																	</div>

																</div>
															</td>

														</tr>

													<?php endforeach; ?>

													</tbody>

												</table>

											</div>

										</form>

									</div>

								</div>

							</div>

						<?php } ?>

					</div>

				</section><?php // .wps-widget-available_backups ?>

				<section class="wpmud-box wps-managed-backups-configs wps-widget-backups_settings
                <?php
                if ( 'settings' !== $backup_menu ) {
					echo ' hidden';
				}
                ?>
                ">

					<div class="wpmud-box-title">

						<h3><?php esc_html_e( 'Settings', SNAPSHOT_I18N_DOMAIN ); ?></h3>

					</div>

					<div class="wpmud-box-content">

						<div class="row">

							<div class="col-xs-12">

								<form class="row-box" id="managed-backup-update" method="post" action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>&tab=settings">

									<input type="hidden" id="snapshot-action" name="snapshot-action" value="update-managed-backup-setting"/>
									<input type="hidden" name="snapshot-ajax-nonce" id="snapshot-ajax-nonce" value="<?php echo esc_attr( wp_create_nonce( 'snapshot-ajax-nonce' ) ); ?>" />
									<?php wp_nonce_field( 'snapshot-full_backups', 'snapshot-full_backups-noonce-field' ); ?>
									<div id="wps-backups-settings-schedule" class="row-inner">

										<?php Snapshot_Model_Request::nonce( 'snapshot-full_backups-schedule' ); ?>

										<div class="col-left">

											<label><?php esc_html_e( 'Schedule', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<p>
												<small><?php esc_html_e( 'Set your full website managed backups to run automatically to a schedule that suits you. We highly recommend a weekly or daily frequency depending on how active your website is.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

										</div>

										<div class="col-right">


											<div class="wps-managed-backups-toggle">

												<div class="toggle">
													<input type="checkbox" id="wps-managed-backups-onoff" class="toggle-checkbox"
                                                    <?php
                                                    if ( false === $cron_disabled ) {
														echo ' checked';
													}
                                                    ?>
                                                    >
													<label class="toggle-label" for="wps-managed-backups-onoff"></label>
												</div>

												<label for="wps-managed-backups-onoff"><?php esc_html_e( 'Enable scheduled backups', SNAPSHOT_I18N_DOMAIN ); ?></label>

											</div>

											<input type="hidden" id="wps-managed-backups-onoff-hidden" value="yes" name="<?php echo ( $cron_disabled ) ? "snapshot-disable-cron" : "snapshot-enable-cron"; ?>"/>


											<div class="wps-managed-backups-schedule-form<?php if ( $cron_disabled ) echo ' hidden'; ?>">

												<label for="frequency"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></label>

												<select id="frequency" name="frequency" <?php echo esc_attr( $disabled ); ?> >
													<?php foreach ( $model->get_frequencies() as $key => $label ) { ?>
														<option value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $model->get_frequency() ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

												<select id="offset-weekly" class="offset weekly" name="offset" <?php echo esc_attr( $disabled ); ?> >
													<?php
														$wday_selected = empty($cron_disabled)
															? $model->get_offset_base()
															: rand(0, 6)
														;
													?>
													<?php foreach ( $model->get_offsets('weekly') as $wday => $label ) { ?>
														<option
																value="<?php echo esc_attr( $wday ); ?>"
															<?php selected( $wday, $wday_selected ); ?>
														><?php echo esc_html($label); ?></option>
													<?php } ?>
												</select>

												<select id="offset-monthly" class="offset monthly" name="offset" <?php echo esc_attr( $disabled ); ?> >
													<?php foreach ( $model->get_offsets('monthly') as $wday => $label ) { ?>
														<option
																value="<?php echo esc_attr( $wday ); ?>"
															<?php selected( $wday, $model->get_offset_base() ); ?>
														><?php echo esc_html($label); ?></option>
													<?php } ?>
												</select>

												<label for="schedule_time"><?php esc_html_e( 'Time of Day', SNAPSHOT_I18N_DOMAIN ); ?></label>

												<select id="schedule_time" name="schedule_time" <?php echo esc_attr( $disabled ); ?> >
													<?php foreach ( $model->get_schedule_times() as $key => $label ) { ?>
														<?php
															$random_schedule_time = array_rand($model->get_schedule_times());
															$key_selected = empty($cron_disabled)
																? $model->get_schedule_time()
																: $random_schedule_time
															;
														?>
														<option value="<?php echo esc_attr( $key ); ?>"
															<?php selected( $key, $key_selected ); ?>
														><?php echo esc_html( $label ); ?></option>
													<?php } ?>
												</select>

											</div>

										</div>

									</div>

									<div id="wps-backups-settings-storage" class="row-inner">

										<div class="col-left">

											<label><?php esc_html_e( 'Storage Limit', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<p>
												<small><?php esc_html_e( 'By default, Snapshot will run as many scheduled backups as you need. If you would like to keep all of your snapshot archives, just set your storage limit to 0.', SNAPSHOT_I18N_DOMAIN ); ?></small>
											</p>

										</div>

										<div class="col-right">

											<label><?php esc_html_e( 'Keep', SNAPSHOT_I18N_DOMAIN ); ?></label>

											<input type="number" min="0" name="backups-limit" id="snapshot-archive-count"
											       value="<?php echo esc_attr( Snapshot_Model_Full_Remote_Storage::get()->get_max_backups_limit() ); ?>">

											<label><?php esc_html_e( 'backups before removing older archives.', SNAPSHOT_I18N_DOMAIN ); ?></label>

										</div>

									</div>

									<div id="wps-backups-settings-update">
										<button type="submit" class="button button-blue" name="snapshot-schedule" value="yes">
											<?php esc_html_e( 'Update Settings', SNAPSHOT_I18N_DOMAIN ); ?>
										</button>
									</div>

								</form>

							</div>

						</div>

					</div>

				</section><?php // .wps-widget-backups_settings ?>

			</div>

		</section>

	</div>

<?php
$model = new Snapshot_Model_Full_Backup();
$apiKey = $model->get_config( 'secret-key', '' );
$data = array(
	"hasApikey" => ! empty( $apiKey ),
	"apiKey" => $apiKey,
	"apiKeyUrl" => $model->get_current_secret_key_link(),
);
$this->render( "boxes/modals/popup-snapshot", false, $data, false, false );

$modal_data = array(
	'modal_id' => "wps-snapshot-log",
	'modal_title' => __( 'Managed Backups Log', SNAPSHOT_I18N_DOMAIN ),
	'modal_content' => __( "<p>Here's a log of events for managed backups.</p>", SNAPSHOT_I18N_DOMAIN ),
);

$this->render( "boxes/modals/popup-dynamic", false, $modal_data, false, false );