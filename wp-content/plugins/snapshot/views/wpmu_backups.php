<?php // phpcs:ignore

$storage_status = array(
	'used' => Snapshot_Model_Full_Remote_Storage::get()->get_used_remote_space(),
	'free' => Snapshot_Model_Full_Remote_Storage::get()->get_free_remote_space(),
	'total' => Snapshot_Model_Full_Remote_Storage::get()->get_total_remote_space(),
);

$percentage = $storage_status['used'] ? round( ( $storage_status['used'] / $storage_status['total'] ) * 100, 1 ) : 0;
if ( $percentage > 100 )
	$percentage = 100;

if ( version_compare(PHP_VERSION, '5.5.0', '<') ) {
	$aws_sdk_compatible = false;
} else {
	$aws_sdk_compatible = true;
}

//Managed Backup Notice - Code Block
$plugin = WPMUDEVSnapshot::instance();
$managed_backup_notice = true;
if ( ! $show_managed_backup_notice || ( isset( $plugin->config_data['managed_backups_notice_dismissed'] ) && $plugin->config_data['managed_backups_notice_dismissed'] ) ) {
	$managed_backup_notice = false;
}

?>

	<section id="header">
		<h1><?php esc_html_e( 'Backups', SNAPSHOT_I18N_DOMAIN ); ?></h1>
	</section>

	<div id="container" class="snapshot-three wps-page-backups wps-page-hosting-backups">

		<section class="wpmud-box wps-widget-hosting-backups-status">

			<div class="wpmud-box-content">

				<div class="wps-hosting-backups-summary">
					<div class="wps-hosting-backups-summary-align">
						<h3><?php echo wp_kses_post( sprintf( __( 'Hey, %s!', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></h3>
						<p><?php esc_html_e( 'Here you can manage all your backups.', SNAPSHOT_I18N_DOMAIN ); ?></p>
					</div>
				</div>

				<div class="wps-hosting-backups-details">
					<table cellpadding="0" cellspacing="0">
						<tbody>

						<tr>

							<th><?php esc_html_e( 'Last backup', SNAPSHOT_I18N_DOMAIN ); ?></th>
							<td class="wps-hosting-backups-last-backup">
								<span class="wps-hosting-spinner"></span>
							</td>
						</tr>

						<tr>
							<th>
								<?php esc_html_e( 'Backups Schedule', SNAPSHOT_I18N_DOMAIN ); ?>
							</th>
							<td>
								<?php esc_html_e( 'Daily', SNAPSHOT_I18N_DOMAIN ); ?>
								<span><?php esc_html_e( 'at', SNAPSHOT_I18N_DOMAIN ); ?> <?php echo esc_html( Snapshot_Helper_Utility::get_hosting_backup_local_time() ); ?></span>
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

						<div class="wps-available-backups-header">
							<h3><?php esc_html_e( 'Available Backups', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						</div>
						<div class="wps-no-available-backups-header">
							<h3><?php esc_html_e( 'Backups', SNAPSHOT_I18N_DOMAIN ); ?></h3>
						</div>

						<input type="submit" id="start_hosting_backup" name="start_hosting_backup" class="button button-small button-blue" value="<?php esc_html_e( 'New Backup', SNAPSHOT_I18N_DOMAIN ); ?>" />

					</div>

					<div class="wpmud-box-content">

						<div class="row">

							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

								<p id="wps-hosting-backups-disclaimer"><?php esc_html_e( "Here's a list of available full website backups.", SNAPSHOT_I18N_DOMAIN ); ?></p>
								<?php
								if ( $managed_backup_notice ):
									?>
									<div class="wps-notice wps-managed-backup-notice">
										<?php
											$backups_page = WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-hosting-backups' )
										?>
										<p>
											<?php
												printf( esc_html__( 'Note: You still have access to your Managed Backups and can restore your website at any time via the %1$1sBackups Tab%2$2s in The Hub.', SNAPSHOT_I18N_DOMAIN ), '<a href="' . esc_attr( $model->get_current_site_management_link() . '#backups' ) . '" target="_blank">', '</a>' );
											?>
										</p>
										<i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i>
									</div>
								<?php endif; ?>
								<div class="wps-new-hosting-backup-state hidden">
									<div class="wps-progress-loader">
										<div class="wps-notice">
											<p>
												<?php
													esc_html_e( 'Your backup is in progress using the WPMU DEV hosting backups engine. Once complete, you can use the backup at any time to fully restore your website.', SNAPSHOT_I18N_DOMAIN );
													?>
											</p>
											<i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i>
										</div>
									</div>
									<div class="wps-progress-success">
										<?php
										echo wp_kses_post( '<div class="wps-auth-message success"><p>' . __( 'Your backup has been successfully created and stored!', SNAPSHOT_I18N_DOMAIN ) . '</p><i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i></div>' );
										?>
									</div>
									<div class="wps-restore-progress-success">
										<?php
										echo wp_kses_post( '<div class="wps-auth-message success"><p>' . sprintf( __( 'Your website has been successfully restored! <a href="%s" target="_blank">View website</a>.', SNAPSHOT_I18N_DOMAIN ), esc_url( get_site_url() ) ) . '</p><i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i></div>' );
										?>
									</div>
									<div class="wps-progress-error">
										<?php
										echo wp_kses_post( '<div class="wps-auth-message error"><p>' . sprintf( __( 'An error occurred while creating your latest backup. <a href="%1$s" class="%2$s">Give it another try</a> and if the issue persists <a href="%3$s" target="_blank">contact our support team</a> for assistance.', SNAPSHOT_I18N_DOMAIN ), '#', 'retry-hosting-backup-creation', 'https://premium.wpmudev.org/hub/support/#get-support' ) . '</p><i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i></div>' );
										?>
									</div>
									<div class="wps-restore-progress-error">
										<?php
										echo wp_kses_post( '<div class="wps-auth-message error"><p>' . sprintf( __( 'An error occurred while restoring your backup. <a href="%1$s" class="%2$s">Try restoring the backup</a> again and if the issue persists <a href="%3$s" target="_blank">our support team</a> is available 24/7 to help.', SNAPSHOT_I18N_DOMAIN ), '#', 'snapshot-hosting-backup-restore', 'https://premium.wpmudev.org/hub/support/#get-support' ) . '</p><i class="wps-icon i-close wps-popup-close wps-dismiss-notice"></i></div>' );
										?>
									</div>
								</div>

								<div class="wps-hosting-backup-list-loader">
									<div class="wpmud-box-gray">
										<p class="wps-hosting-spinner">
											<h3><?php echo esc_html_e( 'Loading Backups&hellip;', SNAPSHOT_I18N_DOMAIN ); ?></h3>
										</p>
									</div>
								</div>
								<div class="wps-notice-wrapper wps-backup-list-ajax-error wpmud-box-gray hidden">
									<div class="wps-auth-message error">
										<p><?php echo wp_kses_post( sprintf(__( 'We were unable to fetch backup data from the API due to a connection problem. Give it another try below, or <a href="%s" target="_blank">contact our support team</a> if the problem persists.', SNAPSHOT_I18N_DOMAIN ), 'https://premium.wpmudev.org/hub/support/#get-support' ) ); ?></p>
									</div>
									<a class="button button-small button-gray button-outline wps-reload-backup-listing"><i class="wdv-icon wdv-icon-fw wdv-icon-repeat"></i><?php esc_html_e('RELOAD', SNAPSHOT_I18N_DOMAIN ); ?></a>
								</div>

								<div class="wps-my-hosting-backups">
									<form id="snapshot-edit-listing" method="post"
											action="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshot_pro_snapshots' ) ); ?>">

										<div class="my-backups-content sui-component-snapshot">

											<table id="my-hosting-backups-table" cellpadding="0" cellspacing="0">
												<thead>
													<tr class="hosting-backups-headers">
														<th class="msc-name"><?php esc_html_e( 'Backup Details', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-size"><?php esc_html_e( 'Type', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-date"><?php esc_html_e( 'Frequency', SNAPSHOT_I18N_DOMAIN ); ?></th>
														<th class="msc-info">&nbsp;</th>
													</tr>
													<tr class="wps-progress-loader-row">
														<td colspan="4">
															<div class="wpmud-box-gray">
																<?php
																echo wp_kses_post( '<p class="wps-spinner">' . __( 'Please wait, backup is in progress&hellip;', SNAPSHOT_I18N_DOMAIN ) . '</p>' );
																?>
															</div>
														</td>
													</tr>
													<tr class="wps-restore-progress-loader-row">
														<td colspan="4">
															<div class="wpmud-box-gray">
																<?php
																echo wp_kses_post( '<p class="wps-spinner">' . __( 'We recommend to keep this window open while we\'re restoring your website in the background. When it\'s complete, there\'s a chance you\'ll be logged out. You can simply log back in to see your freshly restored site.', SNAPSHOT_I18N_DOMAIN ) . '</p>' );
																?>
															</div>
														</td>
													</tr>
													</thead>

													<tbody>

													</tbody>

												</table>

										</div>

									</form>

								</div>

								<div class="wps-no-hosting-managed-backups">
									<p>
									<?php esc_html_e( 'We backup your site daily, and retain backups for 30 days as part of your hosting plan. Your first backup will show here within 24 hours, or you can start it now!', SNAPSHOT_I18N_DOMAIN ); ?>
									</p>
									<input type="submit" id="start_first_hosting_backup" name="start_first_hosting_backup" class="button button-small button-blue" value="<?php esc_html_e( 'Run Backup', SNAPSHOT_I18N_DOMAIN ); ?>" />
								</div>

							</div>

						</div>

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

							<div id="wps-hosting-backups-settings-storage" class="row-inner">

								<div class="col-left">

									<label><?php esc_html_e( 'Storage Limit', SNAPSHOT_I18N_DOMAIN ); ?></label>

									<p>
										<small><?php esc_html_e( 'By default your hosting plan will store 30 days of backups before cycling the oldest within the newest. Currently this can not be changed.', SNAPSHOT_I18N_DOMAIN ); ?></small>
									</p>

								</div>

								<div class="col-right">
									<input type="text" name="backups-limit" id="snapshot-archive-count"
										value="30" disabled="disabled">

									<p><small><?php esc_html_e( 'This limit does not include any manual backups you create.', SNAPSHOT_I18N_DOMAIN ); ?></small></p>

								</div>

							</div>

							<div id="wps-hosting-backups-settings-schedule" class="row-inner">

								<?php Snapshot_Model_Request::nonce( 'snapshot-full_backups-schedule' ); ?>

								<div class="col-left">

									<label><?php esc_html_e( 'Schedule', SNAPSHOT_I18N_DOMAIN ); ?></label>

									<p>
										<small><?php esc_html_e( 'Backups performed on our hosting platform are performed incrementally on a daily basis.', SNAPSHOT_I18N_DOMAIN ); ?></small>
									</p>

								</div>

								<div class="col-right">

									<input type="text" name="schedule" id="snapshot-schedule"
										value="<?php echo esc_html__( 'Daily', SNAPSHOT_I18N_DOMAIN ) . '/ ' . esc_html( Snapshot_Helper_Utility::get_hosting_backup_local_time() ); ?>" disabled="disabled">

									<p><small><?php esc_html_e( 'This is the schedule for your backups.', SNAPSHOT_I18N_DOMAIN ); ?></small></p>


								</div>

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
$this->render( "boxes/modals/popup-hosting", false, array(), false, false );