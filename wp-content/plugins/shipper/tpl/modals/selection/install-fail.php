<?php
/**
 * Shipper modal templates: site selection, Shipper installation fail
 *
 * @since v1.0.3
 * @package shipper
 */

?>
	<h3 class="sui-box-title sui-lg">
		<?php esc_html_e( 'Installation Failed', 'shipper' ); ?>
	</h3>

	<div class="sui-button-icon sui-button-float--right shipper-cancel">
		<a href="<?php echo esc_url( network_admin_url( 'admin.php?page=shipper' ) ); ?>" class="shipper-go-back">
			<i class="sui-icon-close sui-md" aria-hidden="true"></i>
			<span class="sui-screen-reader-text">
				<?php esc_attr_e( 'Close the modal', 'shipper' ); ?>
			</span>
		</a>
	</div>
</div>

<div class="sui-box-body sui-box-body-slim sui-block-content-center">
	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %1$s %2$s: admin username and website url. */
				__( '%1$s, we couldn\'t automatically install Shipper on %2$s', 'shipper' ),
				shipper_get_user_name(),
				'<span class="shipper-site-domain">{{SITE_URL}}</span>'
			)
		);
		?>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %1$s %2$s: admin username and website url. */
				__( 'The quickest way to proceed is to download and install Shipper on %1$s manually.', 'shipper' ),
				'<span class="shipper-site-domain">{{SITE_URL}}</span>'
			)
		);
		?>
	</p>

	<p>
		<a
			href="https://wpmudev.com/project/shipper-pro/"
			target="_blank" class="sui-button">
			<i class="sui-icon-download" aria-hidden="true"></i>
			<?php esc_html_e( 'Download Shipper', 'shipper' ); ?>
		</a>
	</p>
</div>