<?php
/**
 * Browser caching meta box.
 *
 * @package Hummingbird
 *
 * @var bool   $htaccess_issue    Problems writing htaccess file.
 * @var array  $results           Current report.
 * @var int    $issues            Number of issues.
 * @var array  $human_results     Current report in readable format.
 * @var array  $recommended       Recommended values.
 */

?>
<div class="box-content">

	<?php if ( $htaccess_issue ) : ?>
		<div class="wphb-caching-error wphb-notice wphb-notice-error">
			<p><?php esc_html_e( 'Browser Caching is not working properly:', 'wphb' ); ?></p>
			<ul>
				<li>- <?php esc_html_e( 'Your server may not have the "expires" module enabled (mod_expires for Apache, ngx_http_headers_module for NGINX)', 'wphb' ); ?></li>
				<li>- <?php esc_html_e( 'Another plugin may be interfering with the configuration', 'wphb' ); ?></li>
			</ul>
			<p>
				<?php printf(
					/* translators: %s: Support link */
					__( 'If re-checking and restarting does not resolve, please check with your host or <a href="%s" target="_blank">open a support ticket with us</a>.', 'wphb' ),
					esc_url( wphb_support_link() )
				); ?>
			</p>
		</div>
	<?php endif; ?>

	<p><?php esc_html_e( "Caching stores temporary data on your visitors devices so that they don't have to download assets twice if they don't have to. This results in a much faster second time around page load speed. Enabling caching will set the recommended expiry times for your content.", 'wphb' ); ?></p>

	<?php if ( $issues ) : ?>
		<div class="wphb-notice wphb-notice-warning">
			<p>
				<?php printf(
					/* translators: %s: Number of issues */
				__( '%s of your cache types don’t meet the recommended expiry period of 8 days.', 'wphb' ), absint( $issues ) );
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'All of your cache types meet the recommended expiry period of 8 days. Great work!', 'wphb' ); ?></p>
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
		<?php foreach ( $human_results as $type => $result ) :
			$expiry_tooltip = sprintf(
				/* translators: %s: Recommended expiration value */
				__( 'The recommended value for this file type is at least %s. The longer the better!', 'wphb' ),
				esc_html( $recommended[ $type ]['label'] )
			);
			if ( $result ) :
				if ( $recommended[ $type ]['value'] <= $results[ $type ] ) {
					$status = $result;
					$status_color = 'green';
					$status_tooltip = __( 'Caching is enabled', 'wphb' );
				} else {
					$status = $result;
					$status_color = 'yellow';
					$status_tooltip = __( "Caching is enabled but you aren't using our recommended value", 'wphb' );
				}
			else :
				$status = __( 'Disabled', 'wphb' );
				$status_color = 'yellow';
				$status_tooltip = __( 'Caching is disabled', 'wphb' );
			endif; ?>
			<div class="table-row">
				<div class="wphb-caching-summary-item-type">
					<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( $type ); ?>">
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
						} ?>
					</span>
					<?php echo esc_html( $type ); ?>
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
		<?php endforeach; ?>
	</div>

</div><!-- end box-content -->