<?php
/**
 * Browser caching meta box.
 *
 * @package Hummingbird
 *
 * @var bool   $htaccess_issue           Problems writing htaccess file.
 * @var array  $results                  Current report.
 * @var int    $issues                   Number of issues.
 * @var array  $human_results            Current report in readable format.
 * @var array  $recommended              Recommended values.
 * @var string $cf_notice                CloudFlare notification.
 * @var bool   $show_cf_notice           Show the notice.
 * @var bool   $cf_server                Are a CloudFlare server.
 * @var bool   $cf_active                CloudFlare active.
 * @var array  $caching_type_tooltips    Caching types array if browser caching is enabled.
 */

?>
<div class="box-content<?php ( ! $show_cf_notice ) ? esc_attr_e( ' no-background-image', 'wphb' ) : ''; ?>">

	<?php if ( $htaccess_issue ) : ?>
		<div class="wphb-caching-error wphb-notice wphb-notice-error">
			<p><?php esc_html_e( 'Browser Caching is not working properly:', 'wphb' ); ?></p>
			<ul>
				<li>- <?php esc_html_e( 'Your server may not have the "expires" module enabled (mod_expires for Apache, ngx_http_headers_module for NGINX)', 'wphb' ); ?></li>
				<li>- <?php esc_html_e( 'Another plugin may be interfering with the configuration', 'wphb' ); ?></li>
			</ul>

			<?php
			printf(
				/* translators: %s: Support link */
				__( 'If re-checking and restarting does not resolve, please check with your host or <a href="%s" target="_blank">open a support ticket with us</a>.', 'wphb' ),
				esc_url( WP_Hummingbird_Utils::get_link( 'support' ) )
			);
			?>
		</div>
	<?php endif; ?>

	<p><?php esc_html_e( 'Store temporary data on your visitors devices so that they don’t have to download assets twice if they don’t have to. This results in a much faster second time round page load speed.', 'wphb' ); ?></p>

	<?php if ( $issues ) : ?>
		<div class="wphb-notice wphb-notice-warning">
			<p>
				<?php
				printf(
					/* translators: %s: Number of issues */
				__( '%s of your cache types don’t meet the recommended expiry period of 8+ days. Configure browser caching <a href="#" id="configure-link">here</a>.', 'wphb' ), absint( $issues ) );
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'All of your cache types meet the recommended expiry period of 8+ days. Great work!', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="wphb-border-frame">
		<div class="table-header">
			<div class="wphb-caching-summary-heading-type">
				<?php esc_html_e( 'File Type', 'wphb' ); ?>
			</div>
			<div class="wphb-caching-summary-heading-expiry">
				<?php esc_html_e( 'Recommended Expiry', 'wphb' ); ?>
			</div>
			<div class="wphb-caching-summary-heading-status">
				<?php esc_html_e( 'Current Expiry', 'wphb' ); ?>
			</div>
		</div>
		<?php
		foreach ( $human_results as $type => $result ) :
			$expiry_tooltip = sprintf(
				/* translators: %s: Recommended expiration value */
				__( 'The recommended value for this file type is at least %s. The longer the better!', 'wphb' ),
				esc_html( $recommended[ $type ]['label'] )
			);

			if ( $result ) {
				if ( $recommended[ $type ]['value'] <= $results[ $type ] ) {
					$status = $result;
					$status_color = 'green';
					$status_tooltip = __( 'Caching is enabled', 'wphb' );
				} else {
					$status = $result;
					$status_color = 'yellow';
					$status_tooltip = __( "Caching is enabled but you aren't using our recommended value", 'wphb' );
				}
			} else {
				$status         = __( 'Disabled', 'wphb' );
				$status_color   = 'yellow';
				$status_tooltip = __( 'Caching is disabled', 'wphb' );
			}

			if ( $cf_active ) {
				$cf_tooltip = $expiry_tooltip;
				$cf_recommended = $recommended[ $type ]['label'];
				$cf_status_color = $status_color;
				$cf_status_tooltip = $status_tooltip;
				$cf_status = $status;
			}
			?>
			<div class="table-row">
				<div class="wphb-caching-summary-item-type">
					<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( $type ); ?>" tooltip="<?php echo esc_attr( $caching_type_tooltips[ $type ] ); ?>" >
						<?php
						switch ( $type ) {
							case 'javascript':
								$label = 'JavaScript';
								echo 'js';
								break;
							case 'images':
								$label = 'Images';
								echo 'img';
								break;
							case 'css':
								$label = 'CSS';
								echo esc_html( $type );
								break;
							case 'media':
								$label = 'Media';
								echo esc_html( $type );
								break;
							default:
								$label = esc_html( $type );
								echo esc_html( $type );
								break;
						}
						?>
					</span>
					<?php echo esc_html( $label ); ?>
				</div>
				<div class="wphb-caching-summary-item-expiry">
					<span class="wphb-button-label wphb-button-label-light" tooltip="<?php echo esc_attr( $expiry_tooltip ); ?>">
						<?php echo esc_html( $recommended[ $type ]['label'] ); ?>
					</span>
				</div>
				<div class="wphb-caching-summary-item-status">
					<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $status_color ); ?>" tooltip="<?php echo esc_attr( $status_tooltip ); ?>">
						<?php echo esc_html( $status ); ?>
					</span>
				</div>
			</div>
		<?php endforeach;

		if ( $cf_active ) : ?>
			<div class="table-row">
				<div class="wphb-caching-summary-item-type">
					<span class="wphb-filename-extension wphb-filename-extension-other" tooltip="<?php echo esc_attr( $caching_type_tooltips['cloudflare'] ); ?>">oth</span>
					<?php esc_html_e( 'Cloudflare', 'wphb' ); ?>
				</div>
				<div class="wphb-caching-summary-item-expiry">
					<span class="wphb-button-label wphb-button-label-light" tooltip="<?php echo esc_attr( $cf_tooltip ); ?>">
						<?php echo esc_html( $cf_recommended ); ?>
					</span>
				</div>
				<div class="wphb-caching-summary-item-status">
					<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $cf_status_color ); ?>" tooltip="<?php echo esc_attr( $cf_status_tooltip ); ?>">
						<?php echo esc_html( $cf_status ); ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( $show_cf_notice ) { ?>
		<div class="content cf-dash-notice">
			<div class="content-box content-box-two-cols-image-left">
				<div class="wphb-block-entry-content wphb-cf-notice">
					<p>
						<?php
						echo esc_html( $cf_notice );
						?>
						<a href="#" id="connect-cloudflare-link">
							<?php esc_html_e( 'Connect your account', 'wphb' ); ?>
						</a>
						<?php
						esc_html_e( 'to control your settings via Hummingbird.', 'wphb' );
						if ( ! $cf_server ) {
							echo '<br>';
							printf(
								/* translators: %s: CloudFlare information link */
								__( 'CloudFlare is a Content Delivery Network (CDN) that sends traffic through its global network to automatically optimize the delivery of your site so your visitors can browse your site at top speeds. There is a free plan and we recommend using it. <a href="%s" target="_blank">Learn more.</a>', 'wphb' ),
								'https://premium.wpmudev.org/blog/cloudflare-review/'
							);
						}
						?>
						<span class="cf-dismiss">
							<a href="#" id="dismiss-cf-notice"><?php esc_html_e( 'Dismiss', 'wphb' ); ?></a>
						</span>
					</p>
				</div>
			</div>
		</div>
	<?php } ?>
</div><!-- end box-content -->