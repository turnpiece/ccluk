<?php

if ( isset( $item['data'] ) ) {
	$item['data_item'] = Snapshot_Helper_Utility::latest_data_item( $item['data'] );
}

$uploaded = false;

if ( empty( $item['destination'] ) || 'local' === $item['destination'] ) {
	$uploaded = null;
}

if ( ! empty( $item['data_item']['destination-status'] ) ) {
	$destination_status = Snapshot_Helper_Utility::latest_data_item( $item['data_item']['destination-status'] );
	$uploaded = isset( $destination_status['sendFileStatus'] ) && $destination_status['sendFileStatus'];
}

?>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-snapshots">

	<section class="wpmud-box snapshot-info-box">

		<div class="wpmud-box-title has-button">

			<h3 class="has-button">
				<?php esc_html_e( 'Snapshot Info', SNAPSHOT_I18N_DOMAIN ); ?>
				<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>" class="button button-outline button-small button-gray">
					<?php esc_html_e( 'Back', SNAPSHOT_I18N_DOMAIN ); ?>
				</a>
			</h3>

			<div class="wps-menu">

				<div class="wps-menu-dots">

					<div class="wps-menu-dot"></div>

					<div class="wps-menu-dot"></div>

					<div class="wps-menu-dot"></div>

				</div>

				<div class="wps-menu-holder">

					<ul class="wps-menu-list">

						<li class="wps-menu-list-title"><?php esc_html_e( 'Options', SNAPSHOT_I18N_DOMAIN ); ?></li>
						<li>
							<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=edit&amp;item=<?php echo esc_attr( $item['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Edit', SNAPSHOT_I18N_DOMAIN ); ?></a>
						</li>
						<li>
							<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=backup&amp;item=<?php echo esc_attr( $item['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Regenerate', SNAPSHOT_I18N_DOMAIN ); ?></a>
						</li>
						<?php if ( isset( $item['data_item']['timestamp'] ) && ! empty( $item['data_item']['timestamp'] ) ): ?>
							<li>
								<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&snapshot-action=restore&item=<?php echo esc_attr( $item['timestamp'] ); ?>&snapshot-data-item=<?php echo esc_attr( $item['data_item']['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Restore', SNAPSHOT_I18N_DOMAIN ); ?></a>
							</li>
						<?php endif; ?>
						<li>
							<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=delete-item&amp;item=<?php echo esc_attr( $item['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce( 'snapshot-delete-item' ) ); ?>"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></a>
						</li>

					</ul>

				</div>

			</div>

		</div>

		<div class="wpmud-box-content">

			<div class="row">

				<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

					<table class="has-footer" cellpadding="0" cellspacing="0">

						<tbody>

						<tr>
							<th><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p><?php echo esc_html( $item['name'] ); ?></p>
							</td>
						</tr>

						<?php if ( isset( $item['data_item']['filename'] ) ) { ?>
						<tr>
							<th><?php esc_html_e( 'Filename', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
									<?php
                                    if ( isset( $item['data_item']['timestamp'] ) ) {

										printf(
                                            '<a href="%s" title="%s">%s</a>',
											esc_url(
                                                add_query_arg(
													array(
														'snapshot-action' => 'download-archive',
														'snapshot-item' => $item['timestamp'],
														'snapshot-data-item' => $item['data_item']['timestamp'],
													)
                                                )
                                            ),
											esc_attr__( 'Download the snapshot archive', SNAPSHOT_I18N_DOMAIN ),
											esc_html( $item['data_item']['filename'] )
										);
									} else {
										echo esc_html( $item['data_item']['filename'] );
									}
                                    ?>
								</p>
							</td>
						</tr>
						<?php } ?>

						<tr>
							<th><?php esc_html_e( 'Last run', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
									<?php
									if ( isset( $item['data_item']['timestamp'] ) ) {
										$date_time_format = get_option( 'date_format' ) . _x( ' @ ', 'date and time separator', SNAPSHOT_I18N_DOMAIN ) . get_option( 'time_format' );
										echo esc_html( Snapshot_Helper_Utility::show_date_time( $item['data_item']['timestamp'], $date_time_format ) );
									} else {
										echo "-";
									}
									?>
								</p>
							</td>
						</tr>

						<?php if ( ! is_null( $uploaded ) ) { ?>
						<tr>
							<th><?php esc_html_e( 'Status', SNAPSHOT_I18N_DOMAIN ); ?></th>

							<td class="wps-upload-status">
								<?php

								if ( isset( $destination_status ) && $destination_status['errorStatus'] ) {

									if ( $destination_status['errorArray'] ) {

										echo wp_kses_post( '<p>' .  __( 'An error occurred during the most recent upload attempt:', SNAPSHOT_I18N_DOMAIN ) . '</p>' );

										foreach ( $destination_status['errorArray'] as $error_message ) {
											echo wp_kses_post( '<p class="wps-auth-message error">' .  esc_html( $error_message ) . '</p>' );
										}

										echo wp_kses_post( '<p>' .  __( 'Further attempts to upload will continue to be made. However, you may want to investigate this issue to ensure that they are successful.', SNAPSHOT_I18N_DOMAIN ) . '</p>' );

									} else {
										esc_html_e( 'An unknown error occurred during the last upload attempt. Further attempts to upload will continue to be made.', SNAPSHOT_I18N_DOMAIN );
									}

								} else {

									echo $uploaded ?
										wp_kses_post( '<p>' . __( 'Uploaded', SNAPSHOT_I18N_DOMAIN ) . '</p>' ) :
										wp_kses_post( '<p class="wps-spinner">' . __( 'Uploading&hellip;', SNAPSHOT_I18N_DOMAIN ) . '</p>' );

								}

								?>
							</td>
						</tr>
						<?php } ?>

						<tr>
							<th><?php esc_html_e( 'Destination', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<?php $destination = WPMUDEVSnapshot::instance()->config_data['destinations'][ $item['destination'] ]; ?>
								<p class="has-typecon">
									<span class="wps-typecon <?php echo esc_attr( $destination['type'] ); ?>"></span> <?php echo esc_html( $destination['name'] ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
									<?php
									$interval_text = Snapshot_Helper_Utility::get_sched_display( $item['interval'] );

									if ( $interval_text ) {
										$running_timestamp = wp_next_scheduled( 'snapshot_backup_cron', array( intval( $item['timestamp'] ) ) );
										echo wp_kses_post( $interval_text ), wp_kses_post( _x( ' @ ', 'interval and time separator', SNAPSHOT_I18N_DOMAIN ) );
										echo wp_kses_post( Snapshot_Helper_Utility::show_date_time( $running_timestamp, get_option( 'time_format' ) ) );
									} else {
										esc_html_e( 'Once off', SNAPSHOT_I18N_DOMAIN );
									}
									?>
								</p>

							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Filesize', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
                                <?php
									if ( isset( $item['data_item']['file_size'] ) ) {
										$file_size = Snapshot_Helper_Utility::size_format( $item['data_item']['file_size'] );
										echo esc_html( $file_size );
									} else {
										echo "-";
									}
                                    ?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Files', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
                                <?php
                                if ( isset( $item['files-option'] ) ) {
										if ( 'none' === $item['files-option'] ) {
											esc_html_e( 'None', SNAPSHOT_I18N_DOMAIN );
										} else if ( 'all' === $item['files-option'] ) {
											esc_html_e( 'All Files', SNAPSHOT_I18N_DOMAIN );
										} else {
											if ( isset( $item['files-sections'] ) ) {
												echo wp_kses_post( ucwords( implode( ', ', $item['files-sections'] ) ) );
											}
										}
									} else {
										echo "-";
									}
									?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'URL exclusions', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
									<?php
									if ( isset( $item['files-ignore'] ) && count( $item['files-ignore'] ) ) {
										echo wp_kses_post( implode( '<br>', $item['files-ignore'] ) );
									} else {
										echo '-';
									}
									?>
								</p>
							</td>
						</tr>

						<tr>
							<th><?php esc_html_e( 'Database Tables', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td>
								<p>
									<?php
									if ( isset( $item['tables-option'] ) ) {
										if ( 'none' === $item['tables-option'] ) {
											esc_html_e( 'None', SNAPSHOT_I18N_DOMAIN );
										} else if ( 'all' === $item['tables-option'] ) {
											esc_html_e( 'All', SNAPSHOT_I18N_DOMAIN );
										} else {
											if ( isset( $item['tables-sections'] ) ) {
												foreach ( $item['tables-sections'] as $section_key => $section_tables ) {

													if ( ! empty( $section_tables ) ) {
														if ( "wp" === $section_key ) {
															esc_html_e( 'core', SNAPSHOT_I18N_DOMAIN );
														} else if ( "non" === $section_key ) {
															esc_html_e( 'non-core', SNAPSHOT_I18N_DOMAIN );
														} else if ( "other" === $section_key ) {
															esc_html_e( 'other', SNAPSHOT_I18N_DOMAIN );
														} else if ( "error" === $section_key ) {
															esc_html_e( 'error', SNAPSHOT_I18N_DOMAIN );
														} else if ( "global" === $section_key ) {
															esc_html_e( 'global', SNAPSHOT_I18N_DOMAIN );
														}
														echo ': ';
														echo wp_kses_post( implode( ', ', $section_tables ) );
														echo '<br/>';

													}

												}
											}
										}
									} else {
										echo '-';
									}
									?>
								</p>
							</td>
						</tr>

						<?php if ( isset( $item['data_item']['timestamp'] ) && ! empty( $item['data_item']['timestamp'] ) ): ?>
							<tr>
								<th><?php esc_html_e( 'Log', SNAPSHOT_I18N_DOMAIN ); ?></th>
								<td>

									<a id="wps-snapshot-log-view" class="button button-small button-outline button-gray" href="#"><?php esc_html_e( 'view', SNAPSHOT_I18N_DOMAIN ); ?></a>
									<a class="button button-small button-outline button-gray" href="<?php echo '?page=snapshot_pro_snapshots&amp;snapshot-action=download-log&amp;snapshot-item=' . esc_attr( $item['timestamp'] ) . '&amp;snapshot-data-item=' . esc_attr( $item['data_item']['timestamp'] ) . '&amp;live=0'; ?>"><?php esc_html_e( 'download', SNAPSHOT_I18N_DOMAIN ); ?>
									</a>

								</td>
							</tr>
						<?php endif; ?>

						</tbody>

						<tfoot>

						<tr>
							<td>

								<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=delete-item&amp;item=<?php echo esc_attr( $item['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce( 'snapshot-delete-item' ) ); ?>" class="button button-outline button-gray"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></a>

							</td>
							<td>

								<?php if ( isset( $item['data_item']['timestamp'] ) && ! empty( $item['data_item']['timestamp'] ) ): ?>
                                    <?php
										$restore_button = add_query_arg(
												array(
													'snapshot-action' => 'restore',
													'item' => $item['timestamp'],
													'snapshot-data-item' => $item['data_item']['timestamp'],
													'snapshot-noonce-field' => wp_create_nonce  ( 'snapshot-nonce' ),
												), WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' )
											);
                                    ?>
									<a class="button button-blue" href="<?php echo esc_url( $restore_button ); ?>">
										<?php esc_html_e( 'Restore', SNAPSHOT_I18N_DOMAIN ); ?>
									</a>
								<?php endif; ?>

							</td>
						</tr>

						</tfoot>

					</table>
					<?php if ( isset( $item['data_item']['timestamp'] ) && ! empty( $item['data_item']['timestamp'] ) ): ?>
						<?php
						$modal_data = array(
							'modal_id' => "wps-snapshot-log",
							'modal_title' => __( 'View Logs', SNAPSHOT_I18N_DOMAIN ),
							'modal_content' => __( "<p>Here's a log of events for this snapshot.</p>", SNAPSHOT_I18N_DOMAIN ),
							'modal_content_ajax' => admin_url() . 'admin-ajax.php?action=snapshot_view_log_ajax&amp;snapshot-item=' . $item['timestamp'] . '&amp;snapshot-data-item=' . $item['data_item']['timestamp'] . '&amp;snapshot-noonce-field=' . wp_create_nonce( 'snapshot-view-log' ),
							'modal_action_title' => __( 'Download', SNAPSHOT_I18N_DOMAIN ),
							'modal_action_url' => '?page=snapshot_pro_snapshots&amp;snapshot-action=download-log&amp;snapshot-item=' . $item['timestamp'] . '&amp;snapshot-data-item=' . $item['data_item']['timestamp'] . '&amp;live=0',
							'modal_cancel_title' => __( 'Cancel', SNAPSHOT_I18N_DOMAIN ),
							'modal_cancel_url' => '#',
						);
						$this->render( "boxes/modals/popup-dynamic", false, $modal_data, false, false );
						?>
					<?php endif; ?>

				</div>

			</div>

		</div>

	</section>

</div>