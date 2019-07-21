<?php
/**
 * Shipper migration page templates: select migration type subpage
 *
 * @package shipper
 */

?>

<div class="shipper-relfix">
<?php
	$this->render( 'modals/check/hub' );
	$this->render( 'modals/check/system' );
?>
</div>

<div class="sui-box shipper-select-type">
	<div class="sui-box-body">
		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php echo esc_html( sprintf( __( 'Ready to ship it, %s?', 'shipper' ), shipper_get_user_name() ) ); ?></h2>
			</div>

			<p>
				<?php esc_html_e( 'Do you want to export this website to another server, or import a website here?', 'shipper' ); ?>
			</p>

			<ul class="shipper-migration-types">
				<li>
					<a href="<?php echo esc_url( add_query_arg( 'type', 'export', remove_query_arg( 'type' ) ) ); ?>">
						<i class="sui-icon-arrow-up" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Export', 'shipper' ); ?></span>
					</a>
				</li>

				<li>
					<a href="<?php echo esc_url( add_query_arg( 'type', 'import', remove_query_arg( 'type' ) ) ); ?>">
						<i class="sui-icon-arrow-down" aria-hidden="true"></i>
						<span><?php esc_html_e( 'Import', 'shipper' ); ?></span>
					</a>
				</li>
			</ul>

			<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>
