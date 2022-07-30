<?php
/**
 * Shipper tools: System info subpage template
 *
 * @package shipper
 */

$model = new Shipper_Model_System();
$data  = $model->get_data();

$labels = array(
	'php'       => __( 'PHP', 'shipper' ),
	'mysql'     => __( 'MySQL', 'shipper' ),
	'server'    => __( 'Server', 'shipper' ),
	'wordpress' => __( 'WordPress', 'shipper' ),
);
?>
<div class="sui-box shipper-page-tools-sysinfo">

	<div class="sui-box-header">
		<h2 class="sui-box-title"><?php esc_html_e( 'System Information', 'shipper' ); ?></h2>
	</div>

	<div class="sui-box-body">
		<p>
			<?php esc_html_e( 'Use this info if you are having issues with Shipper and your server setup.', 'shipper' ); ?>
			<?php esc_html_e( 'It will give you the most up to date information about your stack.', 'shipper' ); ?>
		</p>

		<div class="shipper-section-selection">
			<select id="shipper-sysinfo-section">
			<?php foreach ( array_keys( $data ) as $section ) { ?>
				<option value="<?php echo esc_attr( $section ); ?>">
					<?php echo esc_html( ! empty( $labels[ $section ] ) ? $labels[ $section ] : $section ); ?>
				</option>
			<?php } ?>
			</select>
		</div>

	<?php foreach ( $data as $section => $info ) { ?>
		<div class="shipper-info-section shipper-info-section-<?php echo sanitize_html_class( $section ); ?>">
			<table class="sui-table">
			<?php foreach ( $info as $name => $value ) { ?>
				<?php
				// We don't need to list down all the sub-sites, so skip.
				if ( 'MS_SUBSITES' === $name ) {
					continue;
				}
				?>
				<tr>
					<th><?php echo esc_html( $name ); ?></th>
					<td><?php echo esc_html( $model->get_output_value( $section, $name, $value ) ); ?></td>
				</tr>
			<?php } ?>
			</table>
		</div>
	<?php } ?>

	</div>

</div>