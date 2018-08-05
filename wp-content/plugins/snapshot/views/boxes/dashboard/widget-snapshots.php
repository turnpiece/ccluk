<?php
$all_snapshots = WPMUDEVSnapshot::instance()->config_data['items'];
$snapshots_counts = count( $all_snapshots );
$snapshots = array();

foreach ( $all_snapshots as $key => $snapshot ) {
	if ( isset( $snapshot['data'] ) ) {
		$snapshot['data_item'] = Snapshot_Helper_Utility::latest_data_item( $snapshot['data'] );
		$snapshots[ $key ] = $snapshot;
	}
}

function _snapshot_sort_snapshots_array( $snapshot1, $snapshot2 ){
	return intval( $snapshot2['data_item']['timestamp'] ) - intval( $snapshot1['data_item']['timestamp'] );
}

usort( $snapshots, '_snapshot_sort_snapshots_array' );

$snapshots = array_slice( $snapshots, 0, 3 );
?>

<section class="wpmud-box wps-widget-snapshots<?php echo empty( $snapshots ) ? '-off' : '-on'; ?>">

	<div class="wpmud-box-title<?php echo empty( $snapshots ) ? ' has-button' : ''; ?>">

		<h3<?php echo empty( $snapshots ) ? ' class="has-count"' : ''; ?>>
			<?php esc_html_e( 'Snapshots', SNAPSHOT_I18N_DOMAIN ); ?>

			<?php if ( ! empty( $snapshots ) ) : ?>
				<span class="wps-count"><?php echo esc_html( $snapshots_counts ); ?></span>
			<?php endif; ?></h3>

		<?php if ( ! empty( $snapshots ) ) : ?>

			<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ); ?>" class="button button-small button-blue"><?php esc_html_e( 'Create', SNAPSHOT_I18N_DOMAIN ); ?></a>

		<?php endif; ?>

	</div>

	<div class="wpmud-box-content">

		<div class="row">

			<div class="col-xs-12">

				<?php if ( empty( $snapshots ) ) : ?>

					<div class="wps-image img-snappie-one"></div>

					<p><?php esc_html_e( 'Snapshots are restore points for your site. Simply choose what you want to back up and then store it on destinations such as Dropbox, Amazon S3 and more.', SNAPSHOT_I18N_DOMAIN ); ?></p>

					<p>
						<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ); ?>" class="button button-blue"><?php esc_html_e( 'Create Snapshot', SNAPSHOT_I18N_DOMAIN ); ?></a>
					</p>

				<?php else : ?>

					<p><?php esc_html_e( 'Snapshots are restore points for your site. Here are your latest snapshots.', SNAPSHOT_I18N_DOMAIN ); ?></p>

					<table class="has-footer" cellpadding="0" cellspacing="0">

						<thead>

						<tr>

							<th class="wss-name"><?php esc_html_e( 'Name', SNAPSHOT_I18N_DOMAIN ); ?></th>

							<th class="wss-date"><?php esc_html_e( 'Date', SNAPSHOT_I18N_DOMAIN ); ?></th>

						</tr>

						</thead>

						<tbody>

						<?php
						foreach ( $snapshots as $key => $snapshot ) :

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
                            ?>

							<tr>

								<td class="wss-name">

									<span class="wps-typecon <?php echo esc_attr( $destination_type ); ?>"></span>

									<p>

										<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>&amp;snapshot-action=view&amp;item=<?php echo esc_attr( $snapshot['timestamp'] ); ?>&amp;snapshot-noonce-field=<?php echo esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ); ?>"><?php echo esc_html( stripslashes( $snapshot['name'] ) ); ?></a>

										<?php
                                        if ( ( isset( $snapshot['data'] ) ) && ( count( $snapshot['data'] ) ) ) {

											$data_item = Snapshot_Helper_Utility::latest_data_item( $snapshot['data'] );

											if ( isset( $data_item ) ) {

												if ( isset( $data_item['file_size'] ) ) {
                                                ?>

													<small><?php echo esc_html( size_format( $data_item['file_size'] ) ); ?></small>

												<?php
                                                }

											}

										}
                                        ?>

									</p>

								</td>

								<td class="wss-date">

									<?php
                                    if ( ( isset( $snapshot['data'] ) ) && ( count( $snapshot['data'] ) ) ) {

										$data_item = Snapshot_Helper_Utility::latest_data_item( $snapshot['data'] );

										if ( isset( $data_item ) ) {

											if ( isset( $data_item['timestamp'] ) ) {

												echo esc_html( Snapshot_Helper_Utility::show_date_time( $data_item['timestamp'], 'F j, Y' ) );

											}

										}

									}
                                    ?>

								</td>


							</tr>

						<?php endforeach; ?>

						</tbody>

						<tfoot>

						<tr>

							<td colspan="2">

								<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-snapshots' ) ); ?>" class="button button-outline button-gray"><?php esc_html_e( 'View All', SNAPSHOT_I18N_DOMAIN ); ?></a>

							</td>

						</tr>

						</tfoot>

					</table>

				<?php endif; ?>

			</div>

		</div>

	</div>

</section>