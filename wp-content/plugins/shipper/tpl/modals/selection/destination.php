<?php
/**
 * Shipper modal templates: site selection, select destination
 *
 * @since v1.0.3
 * @package shipper
 */

$is_import = Shipper_Model_Stored_Migration::TYPE_IMPORT === $type;
$meta      = new Shipper_Model_Stored_MigrationMeta();
$migration = new Shipper_Model_Stored_Migration();
?>

	<h3 class="sui-box-title sui-lg">
		<?php if ( $is_import ) { ?>
			<?php esc_html_e( 'Choose Source ', 'shipper' ); ?>
		<?php } else { ?>
			<?php esc_html_e( 'Choose Destination', 'shipper' ); ?>
		<?php } ?>
	</h3>
	<button class="sui-button-icon sui-button-float--right">
		<a
			href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper-api' ) ); ?>"
			class="shipper-go-back"
		>
			<span class="sui-screen-reader-text"><?php esc_html_e( 'Close this modal', 'shipper' ); ?></span>
			<i class="sui-icon-close sui-md" aria-hidden="true"></i>
		</a>
	</button>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<p class="heading">
		<?php
		if ( $is_import ) {
			echo wp_kses_post(
				__( 'You can import any <b>live</b> site connected to the Hub. ', 'shipper' )
			);
			esc_html_e( 'Which website would you like to import here?  ', 'shipper' );
		} else {
			if ( is_multisite() && $meta->is_extract_mode() ) {
				echo wp_kses_post(
					sprintf(
						/* translators: %s: source website url. */
						__( 'You can export the <strong class="subsite-slug">%s</strong> subsite to any other single site connected to the Hub.', 'shipper' ),
						$meta->get_source()
					)
				);
			} else {
				esc_html_e( 'You can export this site to any other site connected to the Hub. ', 'shipper' );
			}
			esc_html_e( 'Where would you like to export this site to?  ', 'shipper' );
		}
		?>
	</p>

		<div class="shipper-form">
			<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'shipper_prepare_hub_site' ) ); ?>"/>
			<div class=shipper-form-bit shipper-destinations-reload="">
				<a
					href="#refresh-locations"
					data-tooltip="<?php esc_attr_e( 'Reload list', 'shipper' ); ?>"
					class="shipper-button-refresh sui-tooltip shipper-work-activator">
					<i class="sui-icon-update" aria-hidden="true"></i>
				</a>
			</div>

			<div class="shipper-form-bit shipper-selection select-name">
				<p><em><?php esc_html_e( 'Please, hold on', 'shipper' ); ?></em></p>
			</div><?php // .shipper-form-bit ?>
			<div class="shipper-form-bit shipper-continue">
				<button type="submit" class="sui-button sui-button-primary">
					<i class="sui-icon-arrow-right" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Select', 'shipper' ); ?></span>
				</button>
			</div><?php // .shipper-form-bit ?>
		</div> <!-- shipper-form -->

	<p class="shipper-note">
		<?php esc_html_e( 'Don\'t have your site connected to the Hub?', 'shipper' ); ?>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %s: shipper doc url. */
				__( '<a href="%s" target="_blank">Add a new site</a> and refresh the list.', 'shipper' ),
				'https://wpmudev.com/hub2/connect/choose-method/'
			)
		);
		?>
	</p>

	<?php if ( $is_import ) { ?>
		<div class="sui-notice sui-notice-info">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>

					<?php if ( is_multisite() ) : ?>
						<p><?php esc_html_e( 'Note that this option will not merge the current network with the source network. It will migrate the whole multisite and overwrite the entire destination network.', 'shipper' ); ?></p>
					<?php endif; ?>

					<p><?php echo wp_kses_post( sprintf( __( 'To migrate a <strong>local site</strong> here, initiate the export from the local site.', 'shipper' ) ) ); ?></p>
				</div>
			</div>
		</div>
	<?php } ?>
</div>