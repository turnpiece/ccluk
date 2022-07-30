<?php
/**
 * Shipper modal templates: new destination connecting partial
 *
 * @package shipper
 */

?>

<div class="sui-box-header">
	<a href="#refresh">
		<i class="sui-icon-update" aria-hidden="true"></i>
		<span><?php esc_html_e( 'Refresh', 'shipper' ); ?></span>
	</a>
	<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'shipper_list_hub_sites' ) ); ?>" />
	<h3 class="sui-dialog-title"><?php esc_html_e( 'Add Destination', 'shipper' ); ?></h3>
	<button data-a11y-dialog-hide="" class="sui-dialog-close" aria-label="<?php esc_attr_e( 'Close this dialog window', 'shipper' ); ?>"></button>
</div>
<div class="sui-box-body">

	<p>
		<?php esc_html_e( 'Great, choose any of your existing Hub websites, or add a new one.', 'shipper' ); ?>
	</p>

	<div class="shipper-select-site"><form>
	<div class="shipper-form">
		<div class="shipper-form-bit shipper-selection select-name">
			<p><em><?php esc_html_e( 'Please, hold on', 'shipper' ); ?></em></p>
		</div><?php // .shipper-form-bit ?>

		<div class="shipper-form-bit">
			<button type="submit" class="sui-button sui-button-primary">
				<i class="sui-icon-arrow-right" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Prepare', 'shipper' ); ?></span>
			</button>
		</div><?php // .shipper-form-bit ?>
	</div><?php // .shipper-form ?>
	</form></div>

	<p class="shipper-note">
		<?php esc_html_e( 'Still not seeing your site?', 'shipper' ); ?>
		<a href="#refresh"><?php esc_html_e( 'Refresh', 'shipper' ); ?></a>
		<?php esc_html_e( 'the list', 'shipper' ); ?>
	</p>

	<p>
		<a href="#connect-new-site" class="sui-button">
			<i class="sui-icon-hub" aria-hidden="true"></i>
			<?php esc_html_e( 'Connect new website', 'shipper' ); ?>
		</a>
	</p>

</div>