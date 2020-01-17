<?php
/**
 * Shipper modal templates: site selection, select destination
 *
 * @since v1.0.3
 * @package shipper
 */

$is_import = Shipper_Model_Stored_Migration::TYPE_IMPORT === $type;
?>

<div class="sui-box-header sui-block-content-center">
	<h3 class="sui-box-title">
	<?php if ( $is_import ) { ?>
		<?php esc_html_e( 'Choose Source ', 'shipper' ); ?>
	<?php } else { ?>
		<?php esc_html_e( 'Choose Destination', 'shipper' ); ?>
	<?php } ?>
	</h3>
	<div class="sui-actions-right">
		<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper' ) ); ?>"
			class="shipper-go-back">
			<i class="sui-icon-close" aria-hidden="true"></i>
			<span><?php esc_html_e( 'Cancel', 'shipper' ); ?></span>
		</a>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<p><?php
	if ( $is_import ) {
		echo wp_kses_post(
			__( 'You can import any <b>live</b> site connected to the Hub. ', 'shipper' )
		);
		esc_html_e( 'Which website would you like to import here?  ', 'shipper' );
	} else {
		esc_html_e( 'You can export this site to any other site connected to the Hub. ', 'shipper' );
		esc_html_e( 'Where would you like to export this site to?  ', 'shipper' );
	}
	?></p>

	<input type="hidden" name="_wpnonce"
		value="<?php echo esc_attr( wp_create_nonce( 'shipper_prepare_hub_site' ) );?>" />
	<div class="shipper-form">
		<div class="shipper-form-bit shipper-destinations-reload">
			<a href="#refresh-locations"
				data-tooltip="<?php esc_attr_e( 'Reload list', 'shipper' ); ?>"
				class="shipper-button-refresh sui-tooltip shipper-work-activator">
				<i class="sui-icon-update" aria-hidden="true"></i>
			</a>
		</div><?php // .shipper-form-bit ?>
		<div class="shipper-form-bit shipper-selection select-name">
			<p><em><?php esc_html_e( 'Please, hold on', 'shipper' ); ?></em></p>
		</div><?php // .shipper-form-bit ?>
		<div class="shipper-form-bit shipper-continue">
			<button type="submit" class="sui-button sui-button-primary">
				<i class="sui-icon-arrow-right" aria-hidden="true"></i>
				<span><?php esc_html_e( 'Select', 'shipper' ); ?></span>
			</button>
		</div><?php // .shipper-form-bit ?>
	</div><?php // .shipper-form ?>

	<p class="shipper-note">
		<?php esc_html_e( 'Don\'t have your site connected to the Hub?', 'shipper' ); ?>
		<?php echo wp_kses_post( sprintf(
			__( '<a href="%s" target="_blank">Add a new site</a> and refresh the list.', 'shipper' ),
			'https://premium.wpmudev.org/hub/getting-started/?add-website'
		) ); ?>
	</p>

<?php if ( $is_import ) { ?>
<div class="sui-notice sui-notice-info">
	<p><?php esc_html_e( 'To migrate a local site here, initiate the export from the local site.', 'shipper' ); ?></p>
</div>
<?php } ?>

	</p>
</div>