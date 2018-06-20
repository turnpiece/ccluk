<?php
/**
 * Gzip caching summary meta box.
 *
 * @package Hummingbird
 *
 * @var bool|WP_Error  $external_problem  Caching is enabled elsewhere or modules not installed.
 * @var array          $status            Gzip caching status.
 * @var int            $inactive_types    Number of inactive types.
 */

if ( $external_problem && is_wp_error( $external_problem ) ) : ?>
	<div class="sui-notice sui-notice-error">
		<p><?php esc_html_e( 'Gzip is not working properly:', 'wphb' ); ?></p>
		<?php echo $external_problem->get_error_message(); ?>
	</div>
<?php endif; ?>


<p><?php esc_html_e( 'Gzip compresses your web pages and style sheets before sending them over to the browser. This drastically reduces transfer time since the files are much smaller.', 'wphb' ); ?></p>
<?php if ( $inactive_types ) : ?>
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
			printf(
				/* translators: %d: Number of inactive types */
				__( '%d of your compression types are inactive. <a href="#" id="configure-gzip-link">Configure</a> compression for all files types below.', 'wphb' ), absint( $inactive_types )
			);
			?>
		</p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-success">
		<p><?php esc_html_e( 'GZip compression is currently active. Good job!', 'wphb' ); ?></p>
	</div>
<?php endif; ?>


<div class="wphb-border-frame two-columns">
	<div class="table-header">
		<div class="wphb-caching-summary-heading-type">
			<?php esc_html_e( 'File type', 'wphb' ); ?>
		</div>
		<div class="wphb-caching-summary-heading-status">
			<?php esc_html_e( 'Current status', 'wphb' ); ?>
		</div>
	</div>
	<?php
	foreach ( $status as $type => $result ) :
		$result_status = __( 'Inactive', 'wphb' );
		$result_status_color = 'warning';
		if ( $result ) {
			$result_status = __( 'Active', 'wphb' );
			$result_status_color = 'success';
		}
		?>
		<div class="table-row">
			<div class="wphb-caching-summary-item-type">
				<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_html( strtolower( $type ) ); ?>">
					<?php
					switch ( $type ) {
						case 'JavaScript':
							echo 'js';
							break;
						default:
							echo esc_html( strtolower( $type ) );
							break;
					}
					?>
				</span>
				<?php echo esc_html( $type ); ?>
			</div>

			<div>
				<span class="sui-tag sui-tag-<?php echo esc_attr( $result_status_color ); ?> sui-tooltip sui-tooltip-top-left"
					  data-tooltip="<?php printf( /* translators: %1$s: compressions status; %2$s: compression type */
							esc_html__( 'Gzip compression is %1$s for %2$s', 'wphb' ),
							esc_html( strtolower( $result_status ) ), esc_html( $type ) ); ?>">
					<?php echo esc_html( $result_status ); ?>
				</span>
			</div>
		</div>
	<?php endforeach; ?>
</div>