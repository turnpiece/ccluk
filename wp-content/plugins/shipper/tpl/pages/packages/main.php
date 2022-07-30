<?php
/**
 * Shipper packages page templates: main packages page hub
 *
 * @since v1.1
 * @package shipper
 */

$tools = array(
	'migration' => __( 'Package', 'shipper' ),
	'settings'  => __( 'Settings', 'shipper' ),
);
?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'packages' ) ); ?>">

	<?php $this->render( 'pages/header' ); ?>

	<?php if ( $show_flash ) : ?>
		<div class="sui-floating-notices">
			<div role="alert" id="shipper-package-ready" class="sui-notice sui-notice-success sui-active" aria-live="assertive" style="display: block;">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
						<p><?php esc_html_e( 'Your package build is complete and ready to migrate.', 'shipper' ); ?></p>
					</div>
					<div class="sui-notice-actions">
						<button class="sui-button-icon" data-notice-close="shipper-package-ready">
							<i class="sui-icon-check" aria-hidden="true"></i>
							<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>
	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Package Migration', 'shipper' ); ?></h1>
		<?php $this->render( 'pages/packages/view-docs' ); ?>
	</div>

	<div class="sui-row-with-sidenav">
		<div class="sui-sidenav">
			<ul class="sui-vertical-tabs sui-sidenav-hide-md">
				<?php foreach ( $tools as $tool => $label ) { ?>
					<li class="sui-vertical-tab <?php echo $current_tool === $tool ? 'current' : ''; ?>">
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

	<?php $this->render( 'pages/footer' ); ?>
</div> <?php // .sui-wrap ?>