<?php
/**
 * Shipper migrate page templates: migration complete
 *
 * @package shipper
 */

$is_reachable = true;
$domain       = Shipper_Model_Stored_Destinations::get_current_domain(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$migration    = new Shipper_Model_Stored_Migration();
$action_url   = esc_url( $domain );

$target = $destinations->get_by_domain(
	$migration->get_destination()
);

if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $type ) {
	if ( empty( $target ) ) {
		$target       = $destinations->get_current();
		$is_reachable = false;
	} else {
		$ping         = new Shipper_Task_Api_Destinations_Ping();
		$is_reachable = $ping->apply(
			array(
				'domain' => $target['domain'],
			)
		);
		$domain       = $target['domain']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
	}
}

if ( ! $is_reachable ) {
	$current    = $destinations->get_current();
	$action_url = trailingslashit( esc_url( $target['admin_url'] ) ) . 'admin.php?page=shipper&type=import&site=' . $current['site_id'];
}

?>

<div class="shipper-migration-content shipper-migration-done-content" style="display:none">
	<div class="shipper-page-header">
		<i class="sui-icon-check" aria-hidden="true"></i>
		<h2>
		<?php if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $type ) { ?>
			<?php if ( $is_reachable ) { ?>
				<?php esc_html_e( 'Migration complete!', 'shipper' ); ?>
			<?php } else { ?>
				<?php esc_html_e( 'Export complete!', 'shipper' ); ?>
			<?php } ?>
		<?php } else { ?>
			<?php esc_html_e( 'Import complete!', 'shipper' ); ?>
		<?php } ?>
		</h2>
		<?php
			$this->render( 'tags/domains-tag' );
		?>
	</div>

	<p>
	<?php if ( Shipper_Model_Stored_Migration::TYPE_EXPORT === $type ) { ?>
		<?php if ( $is_reachable ) { ?>
			<?php esc_html_e( 'Your website has been successfully migrated.', 'shipper' ); ?>
			<?php /* translators: %1$s* %2$s website url */ ?>
			<?php echo wp_kses_post( sprintf( __( 'Visit <a href="%1$s" target="_blank">%2$s</a> now.', 'shipper' ), esc_url( $target['domain'] ), $target['domain'] ) ); ?>
		<?php } else { ?>
			<?php esc_html_e( 'Your website has been successfully exported.', 'shipper' ); ?>
			<?php /* translators: %1$s* %2$s website url */ ?>
			<?php echo wp_kses_post( sprintf( __( 'Please visit <a href="%1$s" target="_blank">%2$s</a> now to continue your migration.', 'shipper' ), esc_url( $action_url ), $domain ) ); ?>
		<?php } ?>
	<?php } else { ?>
		<?php esc_html_e( 'Your website has been successfully imported.', 'shipper' ); ?>
		<?php esc_html_e( 'Refresh this page to see your new website.', 'shipper' ); ?>
	<?php } ?>
	</p>

	<p>
	<?php if ( Shipper_Model_Stored_Migration::TYPE_IMPORT === $type || $is_reachable ) { ?>
		<button class="sui-button sui-button-ghost shipper-refresh-page">
			<?php esc_html_e( 'Refresh page', 'shipper' ); ?>
		</button>
	<?php } else { ?>
		<a href="<?php echo esc_url( $action_url ); ?>" class="sui-button sui-button-ghost">
			<?php esc_html_e( 'Continue', 'shipper' ); ?>
		</a>
	<?php } ?>
	</p>

</div> <?php // .shipper-migration-done-content ?>