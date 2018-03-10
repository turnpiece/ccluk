<?php
/**
 * Browser caching meta box on dashboard page when CloudFlare is active.
 *
 * @package Hummingbird
 *
 * @var string $caching_url              Caching URL.
 * @var array  $human_results            Array of results. Readable format.
 * @var array  $recommended              Array of recommended values.
 * @var array  $results                  Array of results. Raw.
 * @var int    $issues                   Number of issues.
 * @var bool   $show_cf_notice           Show the CloudFlare notice.
 * @var string $cf_notice                CloudFlare copy to show.
 * @var string $cf_connect_url           Connect CloudFlare URL.
 * @var array  $caching_type_tooltips    Caching types array if browser caching is enabled.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Store temporary data on your visitors devices so that they don’t have to download assets twice if they don’t have to.', 'wphb' ); ?></p>
	<?php if ( $issues ) : ?>
		<div class="wphb-notice wphb-notice-warning">
			<p>
				<?php
				printf(
				/* translators: %s: Number of issues */
					__( '%1$s of your cache types don’t meet the recommended expiry period of 8+ days. Configure browser caching <a href="%2$s" id="configure-link">here</a>.', 'wphb' ),
					absint( $issues ),
					esc_attr( $configure_caching_url )
				);
				?>
			</p>
		</div>
	<?php else : ?>
		<div class="wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'All of your cache types meet the recommended expiry period of 8+ days. Great work!', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div>

<div class="wphb-dash-table two-columns">
	<div class="wphb-dash-table-header">
		<span><?php esc_html_e( 'File Type', 'wphb' ); ?></span>
		<span><?php esc_html_e( 'Current expiry', 'wphb' ); ?></span>
	</div>

	<div class="wphb-dash-table-row">
		<div>
			<?php
			foreach ( $human_results as $type => $result ) :
				if ( $result && $recommended[ $type ]['value'] <= $results[ $type ] ) {
					$result_status       = $result;
					$result_status_color = 'green';
					$tooltip_text        = __( 'Caching is enabled', 'wphb' );
				} elseif ( $result ) {
					$result_status       = $result;
					$result_status_color = 'yellow';
					$tooltip_text        = __( "Caching is enabled but you aren't using our recommended value", 'wphb' );
				} else {
					$result_status       = __( 'Disabled', 'wphb' );
					$result_status_color = 'yellow';
					$tooltip_text        = __( 'Caching is disabled', 'wphb' );
				}
				?>
				<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( $type ); ?> tooltip-left" tooltip="<?php echo esc_attr( $caching_type_tooltips[ $type ] ); ?>">
					<?php
					switch ( $type ) {
						case 'javascript':
							echo 'js';
							break;
						case 'images':
							echo 'img';
							break;
						case 'css':
							echo esc_html( $type );
							break;
						case 'media':
							echo esc_html( $type );
							break;
					}
					?>
				</span>
			<?php endforeach; ?>
			<span class="wphb-filename-extension wphb-filename-extension-other tooltip-left" tooltip="<?php echo esc_attr( $caching_type_tooltips['cloudflare'] ); ?>">
				oth
			</span>
		</div>
		<div>
			<span class="wphb-button-label wphb-button-label-<?php echo esc_attr( $result_status_color ); ?> tooltip-right" tooltip="<?php echo esc_attr( $tooltip_text ); ?>">
				<?php echo esc_html( $result_status ); ?>
			</span>
		</div>
	</div>
</div>