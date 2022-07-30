<?php
/**
 * Shipper templates: initial page (no websites connected)
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

<div class="sui-box shipper-no-sites">
	<div class="sui-box-body">
		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php esc_html_e( 'No sites?', 'shipper' ); ?></h2>
			</div>

			<p>
				<?php /* translators: %s: username. */ ?>
				<?php echo esc_html( sprintf( __( '%s, we\'ve noticed you don\'t have any other websites ready for migrations.', 'shipper' ), shipper_get_user_name() ) ); ?>
				<?php esc_html_e( 'Once you have other websites set up with Shipper you\'ll be able to migrate between them freely with just a few clicks.', 'shipper' ); ?>
			</p>

			<p>
				<button type="button" class="sui-button sui-button-primary shipper-add-website">
					<i class="sui-icon-plus" aria-hidden="true"></i>
					<?php esc_html_e( 'Add destination', 'shipper' ); ?>
				</button>
			</p>

			<p>
				<small>
					<?php esc_html_e( 'Already added a new website?', 'shipper' ); ?>
					<?php
					echo wp_kses(
						/* translators: %s current page url. */
						sprintf( __( '<a href="%s">Refresh this page</a> to see it here.', 'shipper' ), '#refresh-locations' ),
						array( 'a' => array( 'href' => array() ) )
					);
					?>
				</small>
			</p>

			<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_hero_image_markup() ); ?>
		</div><?php // .shipper-content ?>

	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>