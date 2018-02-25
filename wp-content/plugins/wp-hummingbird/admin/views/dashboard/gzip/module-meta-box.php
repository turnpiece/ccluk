<?php
/**
 * Caching meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var array  $status    Array of results.
 * @var int    $inactive_types    Number of inactive types.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Gzip compresses your webpages and style sheets before sending them over to the browser.', 'wphb' ); ?></p>
	<?php if ( $inactive_types ) : ?>
		<div class="wphb-notice wphb-notice-warning">
			<p>
				<?php
					printf(
						/* translators: %s: Number of inactive types */
					__( '%s of your compression types are inactive.', 'wphb' ), absint( $inactive_types ) );
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'GZip compression is currently active. Good job!', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<div class="wphb-dash-table two-columns">
	<div class="wphb-dash-table-header">
		<span><?php esc_html_e( 'File Type', 'wphb' ); ?></span>
		<span><?php esc_html_e( 'Status', 'wphb' ); ?></span>
	</div>

	<?php foreach ( $status as $type => $result ) :
		$result_status       = __( 'Inactive', 'wphb' );
		$result_status_color = 'yellow';
		if ( $result ) {
			$result_status       = __( 'Active', 'wphb' );
			$result_status_color = 'green';
		} ?>
		<div class="wphb-dash-table-row">
			<div>
				<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_html( strtolower( $type ) ); ?>">
					<?php
					switch ( $type ) {
						case 'JavaScript':
							echo 'js';
							break;
						default:
							echo esc_html( strtolower( $type ) );
							break;
					} ?>
				</span>
				<?php echo esc_html( $type ); ?>
			</div>

			<div>
				<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $result_status_color ); ?> tooltip-right"
					  tooltip="<?php printf( /* translators: %1$s: compressions status; %2$s: compression type */
							esc_html__( 'Gzip compression is %1$s for %2$s', 'wphb' ),
							esc_html( strtolower( $result_status ) ), esc_html( $type ) ); ?>">
					<?php echo esc_html( $result_status ); ?>
				</span>
			</div>
		</div>
	<?php endforeach; ?>
</div>