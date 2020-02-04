<?php
/**
 * Shipper modal templates: general welcome modal dialog template
 *
 * @package shipper
 */

$button_class = ! empty( $button_class ) ? $button_class : 'sui-button-ghost shipper-welcome-continue';
$message = ! empty( $message ) ? $message : '';
$button = ! empty( $button ) ? $button : __( 'Continue', 'shipper' );
$skippable = ! empty( $skippable ) ? $skippable : '';
$action = ! empty( $action ) ? $action : '#close';

$notice = '';
if ( ! empty( $message ) && ! empty( $skippable ) && ! empty( $_GET['done_this'] ) ) {
	$notice = $this->get( 'msgs/welcome-dash-issue', array( 'action' => $action ) );
}

if ( empty( $message ) && empty( $skippable ) ) {
	$destinations = new Shipper_Model_Stored_Destinations;
	if ( count( $destinations->get_data() ) > 1 ) {
		// Yeah ok, not the first-time user.
		// No need to show them the dialog.
		return false;
	}
	$message = sprintf(
		__( '<b>%s</b> has been added as a destination and is ready for migrating!', 'shipper' ),
		Shipper_Model_Stored_Destinations::get_current_domain()
	);
}
?>

<div class="sui-dialog shipper-welcome <?php
if ( ! empty( $skippable ) ) { echo 'shipper-skippable'; }
	?>" data-wpnonce="<?php
		echo esc_attr( wp_create_nonce( 'shipper_modal_close' ) );
	?>"
aria-hidden="true">
	<div class="sui-dialog-overlay" tabindex="-1" data-a11y-dialog-hide></div>

	<div class="sui-dialog-content" role="dialog">

		<div class="sui-box" role="document">
			<div class="shipper-welcome-title">
				<div class="shipper-wrapper">
					<h3><i class="sui-icon-shipper-anchor" aria-hidden="true"></i></h3>
					<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
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
							href="<?php echo esc_url( $action ); ?>"
							<?php if ( ! preg_match( '/' . preg_quote( admin_url(), '/' ) . '/', $action ) ) { echo 'target="_blank"'; } ?>
							class="<?php echo esc_attr( $button_class ); ?> sui-button">
							<?php echo esc_html( $button ); ?>
						</a>

						<?php if ( ! empty( $skippable ) ) { ?>
						<p>
							<small><a href="<?php echo esc_url( add_query_arg( 'done_this', 1 ) ); ?>">
								<?php esc_html_e( 'I\'ve done this step', 'shipper' ); ?>
							</a></small>
						</p>
						<?php } ?>

					</div>

				</div><?php // .sui-box-body ?>
			</div><?php // .shipper-welcome-body ?>
		</div><?php // .sui-box ?>
	</div><?php // .sui-dialog-content ?>
</div><?php // .sui-dialog ?>