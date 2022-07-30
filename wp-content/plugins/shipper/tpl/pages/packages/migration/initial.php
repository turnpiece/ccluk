<?php
/**
 * Shipper package migration templates: no previous migration template
 *
 * @since v1.1
 * @package shipper
 */

?>
<div class="shipper-packages-migration-hero">

</div>
<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
<p>
	<?php esc_html_e( 'Package migration lets you create a package of your website, upload it to your destination website and follow an installer wizard to complete the migration.', 'shipper' ); ?>
</p>

<div
	id="shipper-package-initial-notice"
	class="sui-notice sui-notice-warning"
>
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: settings page link. */
						__( 'For a better migration success rate on large sites, we suggest that you enable the <strong>Safe Mode</strong> on the <a href="%1$s" target="_blank">settings page</a>. This will help to prevent Package Migration failure due to a constrained server', 'shipper' ),
						esc_url(
							network_admin_url( 'admin.php?page=shipper-packages&tool=settings' )
						)
					)
				);
				?>
			</p>
		</div>
	</div>
</div>

<button type="button" class="sui-button sui-button-primary shipper-new-package">
	<i class="sui-icon-plus" aria-hidden="true"></i>
	<?php esc_html_e( 'Create Package', 'shipper' ); ?>
</button>