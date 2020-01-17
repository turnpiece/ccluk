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
<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
<p>
	<?php esc_html_e( 'Package migration lets you create a package of your website, upload it to your destination website and follow an installer wizard to complete the migration.', 'shipper' ); ?>
</p>
<button type="button" class="sui-button sui-button-primary shipper-new-package">
	<i class="sui-icon-plus" aria-hidden="true"></i>
	<?php esc_html_e( 'Create Package', 'shipper' ); ?>
</button>