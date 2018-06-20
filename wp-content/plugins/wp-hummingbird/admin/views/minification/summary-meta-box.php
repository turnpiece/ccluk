<div class="sui-summary-image-space">
</div>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<span class="sui-summary-large">
			<?php if ( ! $percentage || '0.0' === $percentage ) : ?>
				-
			<?php else : ?>
				<?php echo esc_html( $percentage ); ?>%
			<?php endif; ?>
		</span>
		<span class="sui-summary-sub"><?php esc_html_e( 'Compression savings', 'wphb' ); ?></span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Total files', 'wphb' ); ?></span>
			<span class="sui-list-detail"><?php echo intval( $enqueued_files ); ?></span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Filesize reductions', 'wphb' ); ?></span>
			<span class="sui-list-detail"><?php echo intval( $compressed_size ); ?>kb</span>
		</li>
		<?php if ( ! is_multisite() ) : ?>
			<?php if ( WP_Hummingbird_Utils::is_member() ) : ?>

				<li>
					<span class="sui-list-label"><?php esc_html_e( 'WPMU DEV CDN', 'wphb' ); ?></span>
					<span class="sui-list-detail"><div class="toggle-actions">
						<label class="sui-toggle sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
							<input type="checkbox" name="use_cdn" id="use_cdn" <?php checked( $use_cdn ); ?> <?php disabled( ! $is_member ); ?>>
							<span class="sui-toggle-slider"></span>
						</label>
					</span>
				</li>
			<?php else : ?>
				<li>
					<span class="sui-list-label"><?php esc_html_e( 'WPMU DEV CDN', 'wphb' ); ?></span>
					<span class="sui-list-detail">
						<span class="sui-tag sui-tag-upsell sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'Host your files on WPMU DEV’s blazing fast CDN', 'wphb' ); ?>" data-a11y-dialog-show="wphb-upgrade-membership-modal" id="dash-uptime-update-membership">
										<?php esc_html_e( 'Pro Feature', 'wphb' ); ?>
									</span>
					</span>
				</li>
			<?php endif; ?>
		<?php else : ?>
			<li>
				<span class="sui-list-label"><?php esc_html_e( 'WPMU DEV CDN', 'wphb' ); ?></span>
				<?php if ( $use_cdn ) : ?>
					<span class="sui-list-detail"><span class=" sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'The Network Admin has the WPMU DEV CDN turned on', 'wphb' ); ?>"> <i class="sui-icon-check-tick sui-md sui-info"></i></span></span>
				<?php else : ?>
					<span class="sui-list-detail"><span class="sui-tag sui-tag-disabled sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_html_e( 'The Network Admin has the WPMU DEV CDN turned off', 'wphb' ); ?>"><?php esc_html_e( 'Disabled', 'wphb' ); ?></span></span>
				<?php endif; ?>
			</li>
		<?php endif; ?>
	</ul>
</div>