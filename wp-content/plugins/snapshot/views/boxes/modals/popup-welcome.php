<?php

$plugin = WPMUDEVSnapshot::instance();

/* Don't display this notice if it has already been seen */
if ( isset( $plugin->config_data['seen_welcome'] ) && $plugin->config_data['seen_welcome'] ) {
	return;
}

$plugin->config_data['seen_welcome'] = true;
$plugin->save_config();

?>
<div id="wps-welcome-message" class="snapshot-three wps-popup-modal show">

	<div class="wps-popup-mask"></div>

	<div class="wps-popup-content">
		<div class="wpmud-box">
			<div class="wpmud-box-title has-button can-close">
				<h3><?php esc_html_e('Welcome to Snapshot', SNAPSHOT_I18N_DOMAIN); ?></h3>
				<a href="#" class="button button-small button-outline button-gray wps-popup-close wps-dismiss-welcome">
					<?php esc_html_e('Skip', SNAPSHOT_I18N_DOMAIN); ?>
				</a>
			</div>

			<div class="wpmud-box-content">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<?php if ( $is_client && ! $has_snapshot_key) : ?>

							<p><?php esc_html_e('Welcome to Snapshot Pro, the hottest backups plugin for WordPress! Let’s start by choosing what type of backup you’d like to make - there are two types…', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php else : ?>

							<p><?php esc_html_e('Welcome to Snapshot, the hottest backups plugin for WordPress! With this plugin you can backup and migrate bits and pieces of your website to third party destinations like Dropbox, Google Drive, Amazon S3 & more.', SNAPSHOT_I18N_DOMAIN); ?></p>

						<?php endif; ?>
					</div>
				</div>

				<?php if ( ! $aws_sdk_compatible ) : ?>

					<?php if ( $is_client && ! $has_snapshot_key) : ?>
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="wps-welcome-sdk-incomp">

								<p><?php echo wp_kses_post( sprintf( __( 'Managed Backups and Amazon S3 Snapshot require PHP 5.5 or later and your host is using an older version of PHP (%s). Contact your host to upgrade your PHP version if you wish to use any of these.', SNAPSHOT_I18N_DOMAIN ), PHP_VERSION ) ); ?></p>

							</div>
						</div>
					</div>

					<?php endif; ?>

				<?php endif; ?>

				<?php if ( $is_client && ! $has_snapshot_key) : ?>
					<div class="row <?php echo ( ! $aws_sdk_compatible ) ? 'wps-aws-sdk-incompatible': ''; ?>">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="wps-welcome-message-pro <?php echo ( ! $aws_sdk_compatible ) ? 'wps-aws-sdk-incompatible': ''; ?>">
								<h3><?php esc_html_e('WPMU DEV Managed Backups', SNAPSHOT_I18N_DOMAIN); ?></h3>
								<p><small><?php esc_html_e('As part of your WPMU DEV membership you get 10GB free cloud storage to back up and store your entire WordPress website - including WordPress itself. You can schedule these backups to run daily, weekly or monthly and should you ever need it you can restore an entire website in just a few clicks.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
								<a class="button button-blue button-small wps-dismiss-welcome <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?>"
									href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-managed-backups') ); ?>">
										<?php esc_html_e( 'Activate Managed Backups', SNAPSHOT_I18N_DOMAIN ); ?>
								</a>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="wps-welcome-message-pro">
								<h3><?php esc_html_e('Snapshots', SNAPSHOT_I18N_DOMAIN); ?></h3>
								<p><small><?php esc_html_e('With Snapshots you can backup and migrate bits and pieces of your website. You can choose what files, plugins/themes and database tables to backup and then store them on third party destinations. To get started, let’s add your first destination.', SNAPSHOT_I18N_DOMAIN); ?></small></p>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<p><?php echo wp_kses_post( __("<strong>Let’s start by adding a new destination</strong>; where would you like to store your first snapshot?", SNAPSHOT_I18N_DOMAIN) ); ?></p>
					</div>
				</div>
				<?php if ( ! $aws_sdk_compatible ) : ?>
					<?php if ( ! ( $is_client && ! $has_snapshot_key ) ) : ?>
						<div class="row">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="wps-welcome-sdk-incomp">

									<p><?php echo wp_kses_post( sprintf( __( 'Amazon S3 requires PHP 5.5 or later and your host is using an older version of PHP (%s). Contact your host to upgrade your PHP version if you wish to use Amazon S3 as a destination.', SNAPSHOT_I18N_DOMAIN ), PHP_VERSION ) ); ?></p>

								</div>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // Dropbox ?>
									<td class="start-icon"><i class="wps-typecon dropbox"></i></td>
									<td class="start-name"><?php esc_html_e('Dropbox', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<?php
										$dropbox_link = add_query_arg(
												array( 'snapshot-action' => 'add',
														'type' => 'dropbox',
														'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
													),
												WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations')
											);
										?>
										<a class="button button-blue button-small wps-dismiss-welcome"
										href="<?php echo esc_url( $dropbox_link ); ?>">
											<?php esc_html_e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // Google Drive ?>
									<td class="start-icon"><i class="wps-typecon google"></i></td>
									<td class="start-name"><?php esc_html_e('Google', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<?php
										$google_link = add_query_arg(
												array( 'snapshot-action' => 'add',
														'type' => 'google-drive',
														'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
													),
												WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations')
											);
										?>
										<a class="button button-blue button-small wps-dismiss-welcome"
										href="<?php echo esc_url( $google_link ); ?>">
											<?php esc_html_e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row <?php echo ( ! $aws_sdk_compatible ) ? 'wps-aws-sdk-incompatible': ''; ?>">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<table cellpadding="0" cellspacing="0" <?php echo ( ! $aws_sdk_compatible ) ? 'class="wps-aws-sdk-incompatible-row"': ''; ?> >
							<tbody>
								<tr><?php // Amazon S3 ?>
									<td class="start-icon"><i class="wps-typecon aws"></i></td>
									<td class="start-name"><?php esc_html_e('Amazon S3', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<?php
										$aws_link = add_query_arg(
												array( 'snapshot-action' => 'add',
														'type' => 'aws',
														'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
													),
												WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations')
											);
										?>
										<a class="button button-blue button-small wps-dismiss-welcome <?php echo ( ! $aws_sdk_compatible ) ? 'disabled': ''; ?>"
										href="<?php echo esc_url( $aws_link ); ?>">
											<?php esc_html_e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // sFTP ?>
									<td class="start-icon"><i class="wps-typecon sftp"></i></td>
									<td class="start-name"><?php esc_html_e('FTP / sFTP', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<?php
										$ftp_link = add_query_arg(
												array( 'snapshot-action' => 'add',
														'type' => 'ftp',
														'destination-noonce-field' => wp_create_nonce( 'snapshot-destination' ),
													),
												WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url('snapshots-newui-destinations')
											);
										?>
										<a class="button button-blue button-small wps-dismiss-welcome"
										href="<?php echo esc_url( $ftp_link ); ?>">
											<?php esc_html_e('Add Destination', SNAPSHOT_I18N_DOMAIN); ?>
										</a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<table cellpadding="0" cellspacing="0">
							<tbody>
								<tr><?php // Local ?>
									<td class="start-icon"><i class="wps-typecon local"></i></td>
									<td class="start-name"><?php esc_html_e('Local', SNAPSHOT_I18N_DOMAIN); ?></td>
									<td class="start-btn">
										<a class="button button-gray button-small button-outline wps-dismiss-welcome"
										href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-new-snapshot' ) . '&snapshot-noonce-field=' . esc_attr( wp_create_nonce  ( 'snapshot-nonce' ) ) ); ?>" >
											<?php esc_html_e('Use Destination', SNAPSHOT_I18N_DOMAIN); ?></a>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>