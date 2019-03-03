<?php
/**
 * Shipper migrate page templates: migration complete, with errors
 *
 * @package shipper
 */

$is_remote_import_error = (bool) (
	! ! $has_remote_error &&
	Shipper_Model_Stored_Migration::TYPE_EXPORT === $type
);

$domain = $destination;
$action_url = esc_url( $domain );
if ( $is_remote_import_error ) {
	$model = new Shipper_Model_Stored_Destinations;
	$target = $model->get_by_domain( $domain );
	$current = $model->get_current();
	$action_url = trailingslashit( esc_url( $target['admin_url'] ) ) .
		'admin.php?page=shipper&type=import&site=' . $current['site_id']
	;
}
$errors = ! empty( $errors ) ? $errors : array();
?>

<div class="shipper-migration-error">
	<div class="shipper-page-header">
		<i class="sui-icon-warning-alert" aria-hidden="true"></i>
		<h2><?php esc_html_e( 'Migration failed', 'shipper' ); ?></h2>
	</div>

<?php foreach ( $errors as $error ) { ?>
	<div class="sui-notice-top sui-notice-error sui-can-dismiss">
		<div class="sui-notice-content">
			<p><?php echo esc_html( $error ); ?></p>
		</div>
		<span class="sui-notice-dismiss">
			<a role="button" href="#" class="sui-icon-check"></a>
		</span>
	</div>
<?php } ?>

	<p>
		<a
			href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper' ) ); ?>"
			class="sui-button sui-button-primary"
		><?php esc_html_e( 'Try again', 'shipper' ); ?></a>
		<a
			href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-tools' ) ); ?>"
			class="sui-button sui-button-ghost"
		><?php esc_html_e( 'Check logs', 'shipper' ); ?></a>
	</p>

	<p>
	<?php if ( ! empty( $is_remote_import_error ) ) { ?>
		<?php echo wp_kses_post( sprintf( __( 'Please visit <a href="%1$s" target="_blank">%2$s</a> now to continue your migration.', 'shipper' ), esc_url( $action_url ), $domain ) );  ?>
	<?php } else {} ?>
		<?php esc_html_e( 'Something went wrong with your migration. You can check migration logs for some errors or follow our troubleshooting guide to resolve the issues. ', 'shipper' ); ?>
	</p>

	<div class="shipper-migration-debug-tips">
		<h3><?php esc_html_e( 'Troubleshooting Tips', 'shipper' ); ?></h3>

		<p>
			<i class="sui-icon-warning-alert shipper-warning" aria-hidden="true"></i>
			<?php esc_html_e( 'You ignored few warnings in your destination server and files during the pre-flight check. Resolve those warnings and try migration again.', 'shipper' ); ?>
		</p>
		<p>
			<i class="sui-icon-warning-alert shipper-warning" aria-hidden="true"></i>
			<?php esc_html_e( 'You’re migrating between two different hosts and this could be the reason for failed migration. You can try to contact support for this.', 'shipper' ); ?>
		</p>
		<p>
			<i class="sui-icon-warning-alert" aria-hidden="true"></i>
			<?php printf(
				__( 'For more common migration issues, check our detailed migration <a href="%s" target="_blank">troubleshooting guide</a>. ', 'shipper' ),
				esc_url( 'https://premium.wpmudev.org/docs/migration-troubleshooting' )
			); ?>
		</p>
		<p>
			<i class="sui-icon-warning-alert" aria-hidden="true"></i>
			<?php printf(
				__( 'Still not able to migrate your site! Follow our <a href="%s" target="_blank">manual migration guide</a> to migrate your website manually.', 'shipper' ),
				esc_url( 'https://premium.wpmudev.org/docs/manual-migration' )
			); ?>
		</p>
		<p class="shipper-contact-support">
			<?php printf(
				__( 'Need help or have some questions? <a href="%s" target="_blank">Contact Support</a>', 'shipper' ),
				esc_url( 'https://premium.wpmudev.org/hub/support/' )
			); ?>
		</p>
	</div>

</div> <?php // .shipper-migration-error ?>