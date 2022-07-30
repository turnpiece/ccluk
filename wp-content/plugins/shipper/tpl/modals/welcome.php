<?php
/**
 * Shipper modal templates: general welcome modal dialog template
 *
 * @package shipper
 */

$button_class = ! empty( $button_class ) ? $button_class : 'sui-button-ghost shipper-welcome-continue';
$message      = ! empty( $message ) ? $message : '';
$button       = ! empty( $button ) ? $button : __( 'Continue', 'shipper' );
$skippable    = ! empty( $skippable ) ? $skippable : '';
$action       = ! empty( $action ) ? $action : '#close'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable
$get_data     = wp_unslash( $_GET );
$notice       = '';

if ( ! empty( $message ) && ! empty( $skippable ) && ! empty( $get_data['done_this'] ) && wp_verify_nonce( $get_data['done_this'], '_wpnonce' ) ) {
	$notice = $this->get( 'msgs/welcome-dash-issue', array( 'action' => $action ) );
}

if ( empty( $message ) && empty( $skippable ) ) {
	$destinations = new Shipper_Model_Stored_Destinations();
	if ( count( $destinations->get_data() ) > 1 ) {
		// Yeah ok, not the first-time user.
		// No need to show them the dialog.
		return false;
	}
	$message = sprintf(
		/* translators: %s: current website name. */
		__( '<b>%s</b> has been added as a destination and is ready for migrating!', 'shipper' ),
		Shipper_Model_Stored_Destinations::get_current_domain()
	);
}
?>

<div
	class="sui-modal shipper-welcome sui-modal-md <?php echo ! empty( $skippable ) ? 'shipper-skippable' : ''; ?>"
	data-wpnonce="<?php echo esc_attr( wp_create_nonce( 'shipper_modal_close' ) ); ?>"
	aria-hidden="true"
>

	<div
		role="dialog"
		id="shipper-welcome"
		class="sui-modal-content sui-fade-in"
	>
		<div class="sui-box" role="document">
			<div class="shipper-welcome-title">
				<div class="shipper-wrapper">
					<h3><i class="sui-icon-shipper-anchor" aria-hidden="true"></i></h3>
					<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
				</div>
			</div>
			<div class="shipper-welcome-body">
				<div class="sui-box-body">
					<p>
						<?php esc_html_e( 'Welcome to Shipper - the easiest migration tool for WordPress.', 'shipper' ); ?>
						<?php echo wp_kses_post( $message ); ?>
					</p>

					<?php if ( ! empty( $notice ) ) { ?>
						<?php echo wp_kses_post( $notice ); ?>
					<?php } ?>

					<div>
						<a
							href="<?php echo esc_url( $action ); ?>" <?php echo ! preg_match( '/' . preg_quote( network_admin_url(), '/' ) . '/', $action ) ? 'target="_blank"' : ''; ?>
							class="<?php echo esc_attr( $button_class ); ?> sui-button">
							<?php echo esc_html( $button ); ?>
						</a>

						<?php if ( ! empty( $skippable ) ) { ?>
						<p>
							<small><a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'done_this', 1 ) ) ); ?>">
								<?php esc_html_e( 'I\'ve done this step', 'shipper' ); ?>
							</a></small>
						</p>
						<?php } ?>

					</div>

				</div><?php // .sui-box-body ?>
			</div><?php // .shipper-welcome-body ?>
		</div><?php // .sui-box ?>
	</div><?php // .sui-modal-content ?>
</div><?php // .sui-modal ?>