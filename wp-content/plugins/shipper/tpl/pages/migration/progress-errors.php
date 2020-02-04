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

$domain     = $destination;
$action_url = esc_url( $domain );
if ( $is_remote_import_error ) {
	$model      = new Shipper_Model_Stored_Destinations;
	$target     = $model->get_by_domain( $domain );
	$current    = $model->get_current();
	$action_url = trailingslashit( esc_url( $target['admin_url'] ) ) .
	              'admin.php?page=shipper&type=import&site=' . $current['site_id'];
}
$errors = ! empty( $errors ) ? $errors : array();

$migration        = new Shipper_Model_Stored_Migration;
$ignored_warnings = (int) $migration->get( 'preflight_warnings', 0 );
?>

<div class="shipper-migration-error">
	<div class="shipper-page-header">
		<i class="sui-icon-warning-alert" aria-hidden="true"></i>
		<h2><?php esc_html_e( 'Migration failed', 'shipper' ); ?></h2>
		<?php
		$this->render( 'tags/domains-tag' );
		?>
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
			<?php echo wp_kses_post( sprintf( __( 'Please visit <a href="%1$s" target="_blank">%2$s</a> now to continue your migration.', 'shipper' ), esc_url( $action_url ), $domain ) ); ?>
		<?php } else {
		} ?>
		<?php esc_html_e( 'Something went wrong with your migration. You can check migration logs for some errors or follow our troubleshooting guide to resolve the issues. ', 'shipper' ); ?>
	</p>

	<div class="shipper-migration-debug-tips">
		<h3><?php esc_html_e( 'Troubleshooting Tips', 'shipper' ); ?></h3>

		<?php if ( ! empty( $ignored_warnings ) ) { ?>
			<p>
				<i class="sui-icon-warning-alert shipper-warning" aria-hidden="true"></i>
				<?php esc_html_e( 'You have ignored some warnings in your pre-flight check which might be causing your migration to fail. You can try to resolve those warnings and rerun the migration.', 'shipper' ); ?>
			</p>
			<p>
				<i class="sui-icon-warning-alert shipper-warning" aria-hidden="true"></i>
				<?php esc_html_e( 'You’re migrating between two different hosts and this could be the reason for failed migration. You can try to contact support for this.', 'shipper' ); ?>
			</p>
		<?php } ?>
		<p>
			<i class="sui-icon-warning-alert" aria-hidden="true"></i>
			<?php
			printf(
				__( 'Unable to migrate your site with the API migration method? You can try using the <a href="%s">Package Migration</a> method instead.', 'shipper' ),
				network_admin_url( 'admin.php?page=shipper-packages' )
			)
			?>
		</p>
		<?php if ( Shipper_Helper_Assets::has_docs_links() ) { ?>
<!--			<p>-->
<!--				<i class="sui-icon-warning-alert" aria-hidden="true"></i>-->
<!--				--><?php //printf(
//					__( 'Refer to our <a href="%s" target="_blank">troubleshooting guide</a> to fix some common migration problems. ', 'shipper' ),
//					esc_url( 'https://premium.wpmudev.org/docs/migration-troubleshooting' )
//				); ?>
<!--			</p>-->
<!--			<p>-->
<!--				<i class="sui-icon-warning-alert" aria-hidden="true"></i>-->
<!--				--><?php //printf(
//					__( 'Still not able to make Shipper work for this migration? Follow our <a href="%s" target="_blank">manual migration guide</a> to migrate your website manually.', 'shipper' ),
//					esc_url( 'https://premium.wpmudev.org/docs/manual-migration' )
//				); ?>
<!--			</p>-->
			<p class="shipper-contact-support">
				<?php printf(
					__( 'Need help or have some questions? <a href="%s" target="_blank">Contact Support</a>', 'shipper' ),
					esc_url( 'https://premium.wpmudev.org/hub/support/' )
				); ?>
			</p>
		<?php } ?>
	</div>

</div> <?php // .shipper-migration-error ?>