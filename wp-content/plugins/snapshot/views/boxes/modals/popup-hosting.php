<?php

$plugin = WPMUDEVSnapshot::instance();

if ( ( isset( $plugin->config_data['hosting_backups_notice_seen'] ) && $plugin->config_data['hosting_backups_notice_seen'] ) ) {
	return;
}

//If there are no backups show the different content in the modal
$full_bkp = new Snapshot_Model_Full_Backup();

$old_backups = $full_bkp->is_active() & $full_bkp->has_backups();

//Get users display name
global $current_user;
$user_name = !empty( $current_user->user_firstname ) ? $current_user->user_firstname : $current_user->display_name;
?>
<div id="wps-hosting-message" class="snapshot-three wps-popup-modal show wps-hosting-backup">

	<div class="wps-popup-mask"></div>

	<div class="wps-popup-content">
		<div class="wpmud-box">
			<div class="wpmud-box-content">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<h2><?php esc_html_e('Hey, you\'re hosting with us!', SNAPSHOT_I18N_DOMAIN); ?></h2>
						<?php if ( ! empty( $old_backups ) ): ?>
							<p class="no-margin-bottom"><?php printf( esc_html__( "%s, we've detected you're hosting this website with us. Great! Snapshot is now using our even more reliable (and super fast) server-side daily backup method included with your hosting package.", SNAPSHOT_I18N_DOMAIN ), esc_html( $user_name ) ); ?></p>
							<p><?php esc_html_e( "We'll keep your old Managed Backups until they expire, you can view both types in your Backups list.", SNAPSHOT_I18N_DOMAIN ); ?></p>
						<?php
						else:
						?>
							<p><?php printf( esc_html__( "%s, your hosting backups are already running on a daily schedule which is all you need. However you can still use Snapshot to create additional backups to third-party destinations.", SNAPSHOT_I18N_DOMAIN ), esc_html( $user_name ) ); ?></p>
						<?php
						endif;
						?>
						<a href="#" class="button button-small button-gray wps-dismiss-hosting-backups">
							<?php esc_html_e('OKAY THANKS!', SNAPSHOT_I18N_DOMAIN); ?>
						</a>
						<?php
						if ( $old_backups ):
							$view_backups_url = add_query_arg( array( 'dismiss_hosting_modal' => 1 ), WPMUDEVSnapshot::instance()->get_setting( 'SNAPSHOT_MENU_URL' ) . 'snapshot_pro_hosting_backups' );
							$view_backups_url = wp_nonce_url( $view_backups_url, 'dismiss_notice' );
						?>
							<a href="<?php echo esc_url_raw( $view_backups_url ); ?>" class="wps-view-backups"><?php esc_html_e("View Backups", SNAPSHOT_I18N_DOMAIN ); ?></a>
						<?php
							endif;
						?>
					</div>
				</div>

			</div>
		</div>
	</div>

</div>