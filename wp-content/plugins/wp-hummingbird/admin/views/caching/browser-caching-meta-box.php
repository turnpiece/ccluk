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

<div class="<?php echo $show_cf_notice ? 'sui-box-body' : ''; ?>">
	<?php if ( $htaccess_issue ) : ?>
		<div class="wphb-caching-error sui-notice sui-notice-error">
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
		<div class="sui-notice sui-notice-warning">
			<p>
				<?php
				printf(
					/* translators: %s: Number of issues */
					__( '%s of your cache types don’t meet the recommended expiry period of 8+ days. Configure browser caching <a href="#" id="configure-link">here</a>.', 'wphb' ),
					absint( $issues )
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="sui-notice sui-notice-success">
			<p><?php esc_html_e( 'All of your cache types meet the recommended expiry period of 8+ days. Great work!', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="wphb-border-frame">
		<div class="table-header">
			<div class="wphb-caching-summary-heading-type">
				<?php esc_html_e( 'File type', 'wphb' ); ?>
			</div>
			<div class="wphb-caching-summary-heading-expiry">
				<?php esc_html_e( 'Recommended expiry', 'wphb' ); ?>
			</div>
			<div class="wphb-caching-summary-heading-status">
				<?php esc_html_e( 'Current expiry', 'wphb' ); ?>
			</div>
		</div>
		<?php
		foreach ( $human_results as $type => $result ) :
			$expiry_tooltip = sprintf(
				/* translators: %s: Recommended expiration value */
				__( 'The recommended value for this file type is at least %s. The longer the better!', 'wphb' ),
				esc_html( $recommended[ $type ]['label'] )
			);

			$status         = __( 'Disabled', 'wphb' );
			$status_color   = 'warning';
			$status_tooltip = __( 'Caching is disabled', 'wphb' );

			if ( $result ) {
				if ( $recommended[ $type ]['value'] <= $results[ $type ] ) {
					$status         = $result;
					$status_color   = 'success';
					$status_tooltip = __( 'Caching is enabled', 'wphb' );
				} else {
					$status         = $result;
					$status_color   = 'warning';
					$status_tooltip = __( "Caching is enabled but you aren't using our recommended value", 'wphb' );
				}
			}

			if ( $cf_active ) {
				$cf_tooltip        = $expiry_tooltip;
				$cf_recommended    = $recommended[ $type ]['label'];
				$cf_status_color   = $status_color;
				$cf_status_tooltip = $status_tooltip;
				$cf_status         = $status;
			}
			?>
			<div class="table-row">
				<div class="wphb-caching-summary-item-type">
					<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( $type ); ?>" data-tooltip="<?php echo esc_attr( $caching_type_tooltips[ $type ] ); ?>" >
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
					<span class="sui-tag sui-tag-disabled sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $expiry_tooltip ); ?>">
						<?php echo esc_html( $recommended[ $type ]['label'] ); ?>
					</span>
				</div>
				<div class="wphb-caching-summary-item-status">
					<span id="wphb-caching-expiry-<?php echo esc_attr( $type ); ?>" class="sui-tag sui-tag-<?php echo esc_attr( $status_color ); ?>  sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $status_tooltip ); ?>">
						<?php echo esc_html( $status ); ?>
					</span>
				</div>
			</div>
			<?php
		endforeach;

		if ( $cf_active && isset( $caching_type_tooltips['cloudflare'] ) ) :
			?>
			<div class="table-row">
				<div class="wphb-caching-summary-item-type">
					<span class="wphb-filename-extension wphb-filename-extension-other sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $caching_type_tooltips['cloudflare'] ); ?>">oth</span>
					<?php esc_html_e( 'Cloudflare', 'wphb' ); ?>
				</div>
				<div class="wphb-caching-summary-item-expiry">
					<span class="sui-tag sui-tag-disabled sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $cf_tooltip ); ?>">
						<?php echo esc_html( $cf_recommended ); ?>
					</span>
				</div>
				<div class="wphb-caching-summary-item-status">
					<span class="sui-tag sui-tag-<?php echo esc_attr( $cf_status_color ); ?> sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_attr( $cf_status_tooltip ); ?>">
						<?php echo esc_html( $cf_status ); ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php if ( $show_cf_notice ) : ?>
	<div class="sui-box-settings-row sui-upsell-row cf-dash-notice sui-no-padding-top">
		<?php if ( ! WP_Hummingbird_Utils::hide_wpmudev_branding() ) : ?>
			<img class="sui-image sui-upsell-image"
				 src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-hb-cf-sell.png' ); ?>"
				 srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/graphic-hb-cf-sell@2x.png' ); ?> 2x"
				 alt="<?php esc_attr_e( 'Connect your account to Cloudflare', 'wphb' ); ?>">
		<?php endif; ?>

		<div class="<?php echo( WP_Hummingbird_Utils::hide_wpmudev_branding() ? esc_attr( 'sui-notice' ) : esc_attr( 'sui-upsell-notice' ) ); ?>">
			<p>
				<?php
				printf(
					/* translators: %s: notice text, */
					esc_html__( '%s Connect your account to control your settings via Hummingbird.', 'wphb' ),
					esc_html( $cf_notice )
				);

				if ( ! $cf_server ) {
					esc_html_e(
						'CloudFlare is a Content Delivery Network (CDN) that sends traffic through its global network
                    to automatically optimize the delivery of your site so your visitors can browse your site at top speeds.
                    There is a free plan and we recommend using it.',
						'wphb'
					);
				}
				?>

				<span>
					<a href="#" class="sui-button sui-button-ghost sui-button-icon-left connect-cloudflare-link">
						<i class="sui-icon-plus-circle" aria-hidden="true"></i>
						<?php esc_html_e( 'Connect', 'wphb' ); ?>
					</a>

					<a href="<?php echo WP_Hummingbird_Utils::hide_wpmudev_doc_link() ? '#' : 'https://premium.wpmudev.org/blog/cloudflare-review/'; ?>" target="_blank">
						<?php esc_html_e( 'Learn More', 'wphb' ); ?>
					</a>
				</span>

				<a href="#" id="dismiss-cf-notice"><?php esc_html_e( 'Dismiss', 'wphb' ); ?></a>
			</p>
		</div>
	</div>
<?php endif; ?>
