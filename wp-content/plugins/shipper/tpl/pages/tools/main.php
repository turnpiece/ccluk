<?php
/**
 * Shipper tools page templates: main tools page hub
 *
 * @package shipper
 */

$tools = array(
	'logs'    => __( 'Logs', 'shipper' ),
	'sysinfo' => __( 'System information', 'shipper' ),
);
?>
<div class="<?php echo esc_attr( Shipper_Helper_Assets::get_page_class( 'tools' ) ); ?>" >

	<?php $this->render( 'pages/header' ); ?>

	<div class="sui-header">
		<h1 class="sui-header-title"><?php esc_html_e( 'Tools', 'shipper' ); ?></h1>
		<?php $this->render( 'pages/tools/view-docs' ); ?>
	</div>

	<?php if ( ! empty( $errors ) ) { ?>
	<div class="sui-box">
		<div class="sui-box-body">
		<?php foreach ( $errors as $error ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global variable ?>
			<div class="sui-notice sui-notice-error">
				<div class="sui-notice-content">
					<div class="sui-notice-message">
						<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
						<p><?php echo wp_kses( $error->get_error_message() ); ?></p>
					</div>
				</div>
			</div>
		<?php } ?>
		</div>
	</div>
	<?php } ?>

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

		<?php $this->render( "pages/tools/{$current_tool}" ); ?>

	</div>



	<?php $this->render( 'pages/footer' ); ?>
</div> <?php // .sui-wrap ?>