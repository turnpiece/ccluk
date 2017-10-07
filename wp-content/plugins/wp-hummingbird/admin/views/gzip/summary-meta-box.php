<?php
/**
 * Gzip caching summary meta box.
 *
 * @package Hummingbird
 *
 * @var bool  $external_problem  Caching is enabled elsewhere or modules not installed.
 * @var array $status            Gzip caching status.
 */

if ( $external_problem ) : ?>
	<div class="wphb-gzip-error wphb-notice wphb-notice-error">
		<p><?php esc_html_e( 'Gzip is not working properly:', 'wphb' ); ?></p>
		<ul>
			<li>- <?php esc_html_e( 'Your server may not have the "deflate" module enabled (mod_deflate for Apache, ngx_http_gzip_module for NGINX)', 'wphb' ); ?></li>
			<li>- <?php esc_html_e( 'Another plugin may be interfering with the configuration', 'wphb' ); ?></li>
		</ul>
		<?php /* translators: %s: support link */ ?>
		<p><?php printf( __( 'If re-checking and restarting does not resolve, please check with your host or <a href="%s" target="_blank">open a support ticket with us</a>.', 'wphb' ), wphb_support_link() ); ?></p>
	</div>
<?php endif; ?>

<ul class="dev-list">

	<div class="content">
		<p><?php esc_html_e( 'Gzip compresses your HTML, JavaScript, and Style Sheets before sending them over to the browser. This drastically reduces transfer time since the files are much smaller.', 'wphb' ); ?></p>
	</div>

	<?php foreach ( $status as $type => $result ) :
		if ( true === $result ) {
			$result_status = __( 'Enabled', 'wphb' );
			$result_status_color = 'green';
		} else {
			$result_status = __( 'Disabled', 'wphb' );
			$result_status_color = 'red';
		} ?>
		<li>
			<div>
				<span class="list-label"><?php echo esc_html( $type ); ?></span>
				<span class="list-detail">
					<?php /* translators: %1$s: compression status; %2$s: for html/js/css */ ?>
					<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $result_status_color ); ?>" tooltip="<?php echo sprintf( esc_html__( 'Gzip compression is %1$s for %2$s', 'wphb' ), esc_html( $result_status ), esc_html( $type ) ); ?>">
						<?php echo esc_html( $result_status ); ?>
					</span>
				</span>
			</div>
		</li>
	<?php endforeach; ?>

</ul>