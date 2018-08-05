<?php

$data = array(
	'snapshots' => $snapshots,
	'results_count' => $results_count,
	'per_page' => $per_page,
	'max_pages' => $max_pages,
	'paged' => $paged,
	'offset' => $offset,
	'count_all_snapshots' => $count_all_snapshots,
);

?>

<section id="header">
	<h1><?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h1>
</section>

<div id="container" class="snapshot-three wps-page-snapshots">

	<?php if ( 0 === $count_all_snapshots ) { ?>

		<section class="wpmud-box get-started-box">

			<div class="wpmud-box-title">
				<h3><?php esc_html_e( 'Get Started', SNAPSHOT_I18N_DOMAIN ); ?></h3>
			</div>

			<div class="wpmud-box-content">

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<div class="wps-image img-snappie-three"></div>

						<p><?php esc_html_e( 'Create and store snapshots of your website. You choose what you want to back up and where you want to save it. Let\'s get started!', SNAPSHOT_I18N_DOMAIN ); ?></p>

						<p>
							<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ); ?>" class="button button-blue"><?php esc_html_e( 'Create Snapshot', SNAPSHOT_I18N_DOMAIN ); ?></a>
						</p>

					</div>

				</div>

			</div>

		</section>

		<?php $this->render( "boxes/widget-notification-managed-backups", false, array(), false, false ); ?>

	<?php } else { ?>

		<section class="wpmud-box available-snapshots">

			<div class="wpmud-box-title has-button">

				<h3><?php esc_html_e( 'Available Snapshots', SNAPSHOT_I18N_DOMAIN ); ?></h3>

				<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ); ?>" class="button button-small button-blue"><?php esc_html_e( 'New Snapshot', SNAPSHOT_I18N_DOMAIN ); ?></a>

			</div>

			<div class="wpmud-box-content">

				<div class="row">

					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

						<div class="wpmud-box-gray">

							<form>

								<span class="filter-label"><?php esc_html_e( 'Filter by', SNAPSHOT_I18N_DOMAIN ); ?></span>

								<input type="hidden" name="page" value="snapshot_pro_snapshots">

								<select name='destination'>
									<option><?php esc_html_e( 'All Destinations', SNAPSHOT_I18N_DOMAIN ); ?></option>
									<?php foreach ( WPMUDEVSnapshot::instance()->config_data['destinations'] as $key => $destination ) : ?>
										<option <?php echo ( $key === $filter ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $destination['name'] ); ?></option>
									<?php endforeach; ?>
								</select>

								<?php wp_nonce_field( 'snapshot-pagination-nonce', 'snapshot-pagination-nonce', false ); ?>
								<button type="submit" class="button button-outline button-gray"><?php esc_html_e( 'Filter', SNAPSHOT_I18N_DOMAIN ); ?></button>

							</form>

						</div>

						<div class="my-snapshots">

							<form id="snapshot-edit-listing" action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshot_pro_snapshots' ) ); ?>" method="post">
								<input type="hidden" name="snapshot-action" value="delete-bulk"/>
								<?php wp_nonce_field( 'snapshot-delete', 'snapshot-noonce-field' ); ?>
								<?php $this->render( "snapshots/partials/filter", false, $data, false, false ); ?>

								<div class="my-snapshots-content">

									<table cellpadding="0" cellspacing="0">

										<thead>

										<tr>

											<th class="msc-check">

												<div class="wps-input--checkbox">

													<input type="checkbox" id="my-snapshot-all"/>

													<label for="my-snapshot-all"></label>

												</div>

											</th>

											<th class="msc-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>

											<th class="msc-type"><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></th>

											<th class="msc-frequency"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></th>

											<th class="msc-size"><?php esc_html_e( 'Size', SNAPSHOT_I18N_DOMAIN ); ?></th>

											<th class="msc-date"><?php esc_html_e( 'Date', SNAPSHOT_I18N_DOMAIN ); ?></th>

											<th class="msc-info">&nbsp;</th>

										</tr>

										</thead>

										<tbody>
										<?php $per_page_snapshots = array_slice( $snapshots, $offset, $per_page ); ?>
										<?php
                                        foreach ( $per_page_snapshots as $key => $snapshot ) :
											$snapshot_locker = null;
											$snapshot_locker = new Snapshot_Helper_Locker( WPMUDEVSnapshot::instance()->get_setting( 'backupLockFolderFull' ), $snapshot['timestamp'] );
											if ( ! empty( $snapshot['data'] ) ) {

												$data_item = Snapshot_Helper_Utility::latest_data_item( $snapshot['data'] );

											} else {

												$data_item = array();

											}

											$destination_type = '';

											if ( 'local' === $snapshot['destination'] ) {

												$destination_type = 'local';

											} else {

												$destination_slug = $snapshot['destination'];

												if ( isset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_slug ] ) ) {

													$destination_type = WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_slug ]['type'];

												} else {

													$destination_type = 'local';

												}

											}

											$destination_name = '';

											if ( 'local' === $snapshot['destination'] ) {

												$destination_name = 'Local Snapshot';

											} else {

												$destination_slug = $snapshot['destination'];

												if ( ( isset( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_slug ]['name'] ) ) && ( strlen( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_slug ]['name'] ) ) ) {

													$destination_name = stripslashes( WPMUDEVSnapshot::instance()->config_data['destinations'][ $destination_slug ]['name'] );

												}

											}
                                            ?>

											<tr>

												<td class="msc-check">

													<div class="wps-input--checkbox">

														<input type="checkbox" name="delete-bulk[]" id="my-snapshot-<?php echo esc_attr( $snapshot['timestamp'] ); ?>" value="<?php echo esc_attr( $snapshot['timestamp'] ); ?>"/>

														<label for="my-snapshot-<?php echo esc_attr( $snapshot['timestamp'] ); ?>"></label>

													</div>

												</td>

												<td class="msc-name">

													<p>
														<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=view&amp;item=<?php echo esc_attr( $snapshot['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php echo esc_html( stripslashes( $snapshot['name'] ) ); ?></a>
														<small><?php echo wp_kses_post( $destination_name ); ?></small>
													</p>

												</td>

												<td class="msc-type">

													<span class="wps-typecon <?php echo esc_attr( $destination_type ); ?>"></span>

												</td>

												<td class="msc-frequency" data-text="<?php esc_html_e('Frequency', SNAPSHOT_I18N_DOMAIN); ?>:">
													<?php
													$interval_text = Snapshot_Helper_Utility::get_sched_display( $snapshot['interval'] );
													if ( $interval_text ) {
														echo wp_kses_post( $interval_text );
													} else {
														esc_html_e('Once off', SNAPSHOT_I18N_DOMAIN);
													}
													?>
												</td>

												<td class="msc-size" data-text="<?php esc_html_e('Size', SNAPSHOT_I18N_DOMAIN); ?>:">

													<?php
                                                    if ( isset( $data_item['file_size'] ) ) {

														$file_size = Snapshot_Helper_Utility::size_format( $data_item['file_size'] );

														echo esc_html( $file_size );

													} else {

														echo "-";

													}
                                                    ?>

												</td>

												<td class="msc-date" data-text="<?php esc_html_e('Date', SNAPSHOT_I18N_DOMAIN); ?>:">

													<?php
                                                    if ( isset( $data_item['timestamp'] ) ) {

														echo esc_html( Snapshot_Helper_Utility::show_date_time( $data_item['timestamp'] ) );

													} else {

														echo "-";

													}

													?>

												</td>

												<td class="msc-info msc-info-onload">

													<?php if ( ! $snapshot_locker->is_locked() ): ?>

														<span class="wps-spinner"></span>

													<?php else : ?>

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
																		<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=edit&amp;item=<?php echo esc_attr( $snapshot['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Edit', SNAPSHOT_I18N_DOMAIN ); ?></a>
																	</li>
																	<li>
																		<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=backup&amp;item=<?php echo esc_attr( $snapshot['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Regenerate', SNAPSHOT_I18N_DOMAIN ); ?></a>
																	</li>
																	<?php if ( isset( $data_item['timestamp'] ) && ! empty( $data_item['timestamp'] ) ): ?>
																		<li>
																			<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&snapshot-action=restore&item=<?php echo esc_attr( $snapshot['timestamp'] ); ?>&snapshot-data-item=<?php echo esc_attr( $data_item['timestamp'] ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php esc_html_e( 'Restore', SNAPSHOT_I18N_DOMAIN ); ?></a>
																		</li>
																	<?php endif; ?>
																	<li>
																		<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=delete-item&amp;item=<?php echo esc_attr( $snapshot['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce( 'snapshot-delete-item' ) ); ?>"><?php esc_html_e( 'Delete', SNAPSHOT_I18N_DOMAIN ); ?></a>
																	</li>

																</ul>

															</div>

														</div>

													<?php endif; ?>

												</td>

											</tr>

										<?php endforeach; ?>

										</tbody>

									</table>

								</div>

								<?php $this->render( "snapshots/partials/filter", false, $data, false, false ); ?>

							</form>
						</div>

					</div>

				</div>

			</div>

		</section>

	<?php } ?>
</div>