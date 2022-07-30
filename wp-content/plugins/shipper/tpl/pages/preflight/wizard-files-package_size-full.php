<?php
/**
 * Shipper templates: preflight check package size partial
 *
 * @package shipper
 */

?>
<p>
	<?php
	$this->render(
		'pages/preflight/wizard-files-package_size-summary',
		array(
			'package_size' => $package_size,
			'threshold'    => $threshold,
		)
	);
	?>
</p>
<p>
	<?php esc_html_e( 'Also, please note that the time your site takes to migrate may vary considerably depending on many other factors (such as the speed of your current host!).', 'shipper' ); ?>
</p>

<div class="sui-row shipper-package-size-full-captain-notice">
	<div class="sui-col-md-4">
		<div class="shipper-captain-image"></div>
		<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
	</div>
	<div class="sui-col-md-8">
		<div class="sui-notice sui-notice-info">
			<div class="sui-notice-content">
				<div class="sui-notice-message">
					<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>

					<p>
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: %s: website url. */
								__( 'Looks like a long time? You can use the <a href="%s" target="_blank"> Package Migration </a> method on your source site to create a package and upload it on this server to migrate in a matter of minutes.', 'shipper' ),
								network_admin_url( 'admin.php?page=shipper-packages' )
							)
						);
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>