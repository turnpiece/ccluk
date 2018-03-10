<?php
/**
 * Gzip caching summary meta box.
 *
 * @package Hummingbird
 *
 * @var bool  $external_problem  Caching is enabled elsewhere or modules not installed.
 * @var array $status            Gzip caching status.
 * @var int   $inactive_types    Number of inactive types.
 */

if ( $external_problem ) : ?>
	<div class="wphb-gzip-error wphb-notice wphb-notice-error">
		<p><?php esc_html_e( 'Gzip is not working properly:', 'wphb' ); ?></p>
		<ul>
			<li>- <?php esc_html_e( 'Your server may not have the "deflate" module enabled (mod_deflate for Apache, ngx_http_gzip_module for NGINX).', 'wphb' ); ?></li>
			<li>- <?php esc_html_e( 'Contact your host. If deflate is enabled, ask why all .htaccess or nginx.conf compression rules are not being applied.', 'wphb' ); ?></li>
		</ul>
		<?php /* translators: %s: support link */ ?>
		<p><?php printf( __( 'If re-checking and restarting does not resolve, please check with your host or <a href="%s" target="_blank">open a support ticket with us</a>.', 'wphb' ), WP_Hummingbird_Utils::get_link( 'support' ) ); ?></p>
	</div>
<?php endif; ?>


<p><?php esc_html_e( 'Gzip compresses your web pages and style sheets before sending them over to the browser. This drastically reduces transfer time since the files are much smaller.', 'wphb' ); ?></p>
<?php if ( $inactive_types ) : ?>
	<div class="wphb-notice wphb-notice-warning">
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
	<div class="wphb-notice wphb-notice-success">
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
		$result_status_color = 'yellow';
		if ( $result ) {
			$result_status = __( 'Active', 'wphb' );
			$result_status_color = 'green';
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