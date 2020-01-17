<?php
/**
 * Shipper modal templates: site selection, Shipper installation fail
 *
 * @since v1.0.3
 * @package shipper
 */

?>

<div class="sui-box-header sui-block-content-center">
	<h3 class="sui-box-title">
		<?php esc_html_e( 'Installation Failed', 'shipper' ); ?>
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
	<p>
		<?php echo wp_kses_post( sprintf(
			__( '%1$s, we couldn\'t automatically install Shipper on %2$s', 'shipper' ),
			shipper_get_user_name(),
			'<span class="shipper-site-domain">{{SITE_URL}}</span>'
		) ); ?>
		<?php echo wp_kses_post( sprintf(
			__( 'The quickest way to proceed is to download and install Shipper on %1$s manually.', 'shipper' ),
			'<span class="shipper-site-domain">{{SITE_URL}}</span>'
		) ); ?>
	</p>

	<p>
		<a
			href="https://premium.wpmudev.org/project/shipper-pro/"
			target="_blank" class="sui-button">
			<i class="sui-icon-download" aria-hidden="true"></i>
			<?php esc_html_e('Download Shipper', 'shipper' ); ?>
		</a>
	</p>

	</p>
</div>