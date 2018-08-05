<?php

/* Don't show the notice if managed backups are already enabled */
$model = new Snapshot_Model_Full_Backup();
$is_client = $model->is_dashboard_active() && $model->has_dashboard_key();
$api_key = $model->get_config( 'secret-key', '' );
if ( $is_client && false !== Snapshot_Model_Full_Remote_Api::get()->get_token() && ! empty( $api_key ) ) {
	return;
}

/* Set disable disable nonce */
$ajax_nonce = wp_create_nonce( "snapshot-disable-notif" );
$disable_notif_snapshot_page = get_option( 'snapshot-disable_notif_snapshot_page', null );

if ( isset( $disable_notif_snapshot_page ) ) {
	return;
}

?>
<section class="wpmud-box try-managed-backups-box">

	<div class="wpmud-box-content">

		<div class="wpmud-box-content-wrap">

			<p><?php echo wp_kses_post( sprintf( __( '%s, have you heard about WPMU DEV’s Managed Backups? As a WPMU DEV member, you get 10GB free and secure cloud storage, which you can use to store full backups of your website, including WordPress core files. If disaster strikes, you can quickly and easily restore your website any time.', SNAPSHOT_I18N_DOMAIN ), wp_get_current_user()->display_name ) ); ?></p>

			<p class="align-buttons">

				<a href="<?php echo esc_url( WPMUDEVSnapshot::instance()->snapshot_get_pagehook_url( 'snapshots-newui-managed-backups' ) ); ?>" class="button button-blue"><?php esc_html_e( 'Try managed backups', SNAPSHOT_I18N_DOMAIN ); ?></a>

				<a id="disable-notif" href="#" data-security="<?php echo esc_attr( $ajax_nonce ); ?>"><?php esc_html_e( 'No thanks', SNAPSHOT_I18N_DOMAIN ); ?></a>

			</p>

		</div>

	</div>

</section>