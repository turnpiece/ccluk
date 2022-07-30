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

$domain     = $destination; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$action_url = esc_url( $domain );
if ( $is_remote_import_error ) {
	$model      = new Shipper_Model_Stored_Destinations();
	$target     = $model->get_by_domain( $domain );
	$current    = $model->get_current();
	$action_url = trailingslashit( esc_url( $target['admin_url'] ) ) . 'admin.php?page=shipper&type=import&site=' . $current['site_id'];
}

$errors           = ! empty( $errors ) ? $errors : array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$migration        = new Shipper_Model_Stored_Migration();
$ignored_warnings = (int) $migration->get( 'preflight_warnings', 0 );

$error_msg = $is_remote_import_error
	? __( 'method on your source site to create a package, upload it on this server, and follow the installation prompts to migrate.', 'shipper' )
	: __( 'method to upload a package of your site onto your destination server and follow the installation prompts.', 'shipper' );
?>

<div class="shipper-migration-error">
	<div class="shipper-page-header">
		<i class="sui-icon-warning-alert" aria-hidden="true"></i>
		<h2><?php esc_html_e( 'Migration failed', 'shipper' ); ?></h2>
		<?php
		$this->render( 'tags/domains-tag' );
		?>
	</div>

	<?php foreach ( $errors as $key => $error ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable ?>
		<div class="sui-floating-notices">
			<div role="alert" id="shipper-progress-error-<?php echo esc_attr( $key ); ?>" class="sui-notice sui-notice-error sui-active sui-can-dismiss" aria-live="assertive" style="display: block;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<p><?php echo esc_html( $error ); ?></p>
					</div>
					<div class="sui-notice-actions">
						<button class="sui-button-icon sui-notice-dismiss" data-notice-close="shipper-progress-error-<?php echo esc_attr( $key ); ?>">
							<i class="sui-icon-check" aria-hidden="true"></i>
							<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php } ?>

	<p>
		<?php if ( ! empty( $is_remote_import_error ) ) { ?>
			<?php /* translators: %1$s %2$s: website url. */ ?>
			<?php echo wp_kses_post( sprintf( __( 'Please visit <a href="%1$s" target="_blank">%2$s</a> now to continue your migration.', 'shipper' ), esc_url( $action_url ), $domain ) ); ?>
		<?php } ?>
		<?php esc_html_e( 'Something went wrong with your migration. You can check the logs for errors, or follow our troubleshooting tips below to resolve the issues.', 'shipper' ); ?>
	</p>

	<p>
		<a
			href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-api' ) ); ?>"
			class="sui-button sui-button-primary"
		>
			<?php esc_html_e( 'Try again', 'shipper' ); ?></a>
		<a
			href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-tools' ) ); ?>"
			class="sui-button sui-button-ghost"
		>
			<?php esc_html_e( 'Check logs', 'shipper' ); ?>
		</a>
	</p>

	<div class="shipper-migration-debug-tips">
		<h3><?php esc_html_e( 'Troubleshooting Tips', 'shipper' ); ?></h3>

		<?php if ( ! empty( $ignored_warnings ) ) { ?>
			<p>
				<i class="sui-icon-warning-alert shipper-info" aria-hidden="true"></i>
				<?php esc_html_e( 'You ignored some warnings in your pre-flight check, which might have caused your migration to fail. You can try to resolve those warnings and rerun the migration.', 'shipper' ); ?>
			</p>

			<p>
				<i class="sui-icon-warning-alert shipper-info" aria-hidden="true"></i>
				<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %1$s %2$s: website url, error message */
							__( 'Use the <a href="%1$s" target="_blank">Package Migration</a> %2$s', 'shipper' ),
							esc_url( network_admin_url( 'admin.php?page=shipper-packages' ) ),
							$error_msg
						)
					);
				?>
			</p>

			<?php if ( Shipper_Helper_Assets::has_docs_links() ) : ?>
				<p>
					<i class="sui-icon-warning-alert shipper-info" aria-hidden="true"></i>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: wpmudev support url. */
							__( 'None of the above was helpful? <a href="%s" target="_blank">Contact Support</a>', 'shipper' ),
							esc_url( 'https://wpmudev.com/hub2/support/' )
						)
					);
					?>
				</p>
			<?php endif; ?>
		<?php } ?>
	</div>
</div> <?php // .shipper-migration-error ?>