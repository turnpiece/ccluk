<?php
/**
 * Header template.
 *
 * @var string $title (Optional)
 */

defined( 'WPINC' ) || die();

?>

<div class="sui-wrap" id="sui-wrap"> <!-- Open sui-wrap -->

	<?php
	/**
	 * Action hook to display notifications.
	 *
	 * This action hook can be used to display notifications.
	 *
	 * @since 3.2.0
	 */
	do_action( 'beehive_admin_top_notices' );
	?>

	<div class="sui-header">
		<h1 class="sui-header-title"><?php echo empty( $title ) ? esc_html__( 'Settings', 'ga_trans' ) : esc_html( $title ); ?></h1>
		<div class="sui-actions-right">
			<a class="sui-button sui-button-ghost" href="https://premium.wpmudev.org/docs/wpmu-dev-plugins/beehive/" target="_blank">
				<i class="sui-icon-academy"></i> <?php esc_html_e( 'View Documentation', 'ga_trans' ); ?>
			</a>
		</div>
	</div>

	<?php wp_nonce_field( 'beehive_admin_nonce', 'beehive_admin_nonce' ); // This can be used for form processing. ?>
	<input type="hidden" name="beehive_admin_type" id="beehive_admin_type" value="<?php echo is_network_admin() ? 1 : 0; ?>">

	<?php
	/**
	 * Action hook to display notifications.
	 *
	 * This action hook can be used to display notifications.
	 *
	 * @since 3.2.0
	 */
	do_action( 'beehive_admin_notices' );
	?>