<?php
/**
 * Shipper packages page templates: main packages page hub
 *
 * @since v1.1
 * @package shipper
 */

$tools = array(
	'migration' => __( 'Package', 'shipper' ),
	'settings' => __( 'Settings', 'shipper' ),
);
?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'packages' ) ); ?>" >
	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Package Migration', 'shipper' ); ?></h1>
	</div>

	<div class="sui-row-with-sidenav">
		<div class="sui-sidenav">
			<ul class="sui-vertical-tabs sui-sidenav-hide-md">
			<?php foreach ( $tools as $tool => $label ) { ?>
				<li class="sui-vertical-tab <?php if ( $current_tool === $tool ) { echo 'current'; } ?>">
					<a href="<?php echo esc_url( add_query_arg( 'tool', $tool, remove_query_arg( 'tool' ) ) ); ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				</li>
			<?php } ?>
			</ul>
			<div class="sui-sidenav-hide-lg">
				<select class="sui-mobile-nav" style="display: none;">
				<?php foreach ( $tools as $tool => $label ) { ?>
					<option <?php selected( $current_tool, $tool ); ?> value="<?php echo esc_attr( $tool ); ?>">
						<?php echo esc_html( $label ); ?>
					</option>
				<?php } ?>
				</select>
			</div>
		</div>

		<?php $this->render( "pages/packages/{$current_tool}" ); ?>

	</div>

	<?php $this->render('pages/footer'); ?>
</div> <?php // .sui-wrap ?>