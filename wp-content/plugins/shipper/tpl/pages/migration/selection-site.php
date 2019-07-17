<?php
/**
 * Shipper templates: site selection subpage
 *
 * @package shipper
 */

?>
<div class="sui-box shipper-select-site">
	<div class="sui-box-body">
		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php echo esc_html( sprintf( __( 'Ready to ship it, %s?', 'shipper' ), shipper_get_user_name() ) ); ?></h2>
			</div>

			<p>
			<?php
			if ( 'export' === $type ) {
				esc_html_e( 'Great, where would you like to migrate this website to?', 'shipper' );
			} else {
				esc_html_e( 'Great, what website would you like to import?', 'shipper' );
				echo ' ';
				esc_html_e( 'If you don\'t see your website in the list, make sure you\'ve got both Shipper and the WPMU DEV Dashboard installed on your source website.', 'shipper' );
			}
			?>
			</p>


			<?php
				$this->render(
					'modals/selection',
					array( 'modal' => 'loading', 'type' => $type )
				);
				$this->render(
					'modals/selection',
					array( 'modal' => 'loading-error', 'type' => $type )
				);
				$this->render(
					'modals/selection',
					array( 'modal' => 'destination', 'type' => $type )
				);
				$this->render(
					'modals/selection',
					array( 'modal' => 'prepare', 'type' => $type )
				);
				$this->render(
					'modals/selection',
					array( 'modal' => 'install-fail', 'type' => $type )
				);
			?>


			<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>

<?php $this->render( 'modals/migration-deletedest' ); ?>
<?php $this->render( 'msgs/migration-destdelete-success' ); ?>
