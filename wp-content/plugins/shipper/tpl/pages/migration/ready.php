<?php
/**
 * Shipper migrate pages: begin migration partial
 *
 * @package shipper
 */

?>
<div class="sui-box shipper-migration-ready">
	<div class="sui-box-body">

		<div class="shipper-actions">
			<div class="shipper-actions-left">
				<a href="<?php echo esc_url( remove_query_arg( 'check' ) ); ?>" class="shipper-button-back">
					<i class="sui-icon-arrow-left" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
			<div class="shipper-actions-right">
				<a href="<?php echo esc_url( remove_query_arg( array( 'site', 'type', 'check' ) ) ); ?>" class="shipper-button-back">
					<i class="sui-icon-close" aria-hidden="true"></i>
					<span><?php esc_html_e( 'Go back', 'shipper' ); ?></span>
				</a>
			</div>
		</div>

		<div class="shipper-content">

			<div class="shipper-header">
				<i class="sui-icon-shipper-anchor" aria-hidden="true"></i>
				<h2><?php echo esc_html( sprintf( __( 'Ready to ship? %s', 'shipper' ), shipper_get_user_name() ) ); ?></h2>
				<?php
					$this->render( 'tags/domains-tag' );
				?>
			</div>

			<p><?php esc_html_e( 'You’re ready to go! Note that Shipper overwrites any existing files or database tables on your destination website. Please make sure you have a backup. ', 'shipper' ); ?></p>

			<p>
				<a href="<?php echo esc_url( add_query_arg( 'begin', 'true' ) ); ?>"
					class="sui-button sui-button-primary">
					<?php esc_html_e( 'Begin migration', 'shipper' ); ?>
				</a>
			</p>




			<?php echo Shipper_Helper_Assets::get_custom_hero_image_markup(); ?>
		</div><?php // .shipper-content ?>
	</div><?php // .sui-box-body ?>
</div><?php // .sui-box ?>