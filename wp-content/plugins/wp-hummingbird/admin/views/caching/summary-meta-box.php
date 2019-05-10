<?php
/**
 * Caching summary meta box.
 *
 * @since 1.9.1
 *
 * @package Hummingbird
 *
 * @var int  $cached     Number of cached pages.
 * @var bool $gravatar   Gravatar Caching status.
 * @var int  $issues     Number of Browser Caching issues.
 * @var int  $pages      Total number of posts and pages in WP.
 * @var bool $pc_active  Page caching status.
 * @var int  $rss        RSS caching duration.
 */

?>
<div class="sui-summary-image-space"></div>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<?php if ( $pc_active ) : ?>
			<span class="sui-summary-large"><?php echo absint( $cached ); ?></span>
		<?php else : ?>
			<span class="sui-summary-large">-</span>
		<?php endif; ?>
		<span class="sui-summary-sub">
			<?php esc_html_e( 'Cache files', 'wphb' ); ?>
			<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Pages are cached when someone first visits them. This number is the total count of static files being cached (not pages) and can be larger than the total number of physical pages you have.', 'wphb' ); ?>">
				<i class="sui-icon-info" aria-hidden="true"></i>
			</span>
		</span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label">
				<?php esc_html_e( 'Browser Caching', 'wphb' ); ?>
			</span>
			<span class="sui-list-detail">
				<?php if ( 0 < $issues ) : ?>
					<span class="sui-tag sui-tag-warning"><?php echo absint( $issues ); ?></span>
				<?php else : ?>
					<i class="sui-icon-check-tick sui-lg sui-success" aria-hidden="true"></i>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Gravatar Caching', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php if ( $gravatar ) : ?>
					<i class="sui-icon-check-tick sui-lg sui-success" aria-hidden="true"></i>
				<?php else : ?>
					<div class="sui-tag sui-tag-disabled"><?php esc_html_e( 'Inactive', 'wphb' ); ?></div>
				<?php endif; ?>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'RSS Caching', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<div class="wphb-dash-numbers"><?php echo absint( $rss ) / 60; ?> minutes</div>
			</span>
		</li>
	</ul>
</div>

