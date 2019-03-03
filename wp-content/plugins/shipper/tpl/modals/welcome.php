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
					<h3>Yarr, <?php echo esc_html( shipper_get_user_name() ); ?></h3>
				</div>
			</div>
			<div class="shipper-welcome-body">
				<div class="sui-box-body">
					<p>
						<?php esc_html_e( 'Welcome to Shipper - the easiest migration tool for WordPress.', 'shipper' ); ?>
						<?php echo esc_html( $message ); ?>
					</p>

					<div>
						<a
							href="<?php echo esc_url( $action ); ?>"
							<?php if ( ! preg_match( '/' . preg_quote( admin_url(), '/' ) . '/', $action ) ) { echo 'target="_blank"'; } ?>
							class="<?php echo esc_attr( $button_class ); ?> sui-button">
							<?php echo esc_html( $button ); ?>
						</a>

						<?php if ( ! empty( $skippable ) ) { ?>
						<p>
							<small><a href="#skip">
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