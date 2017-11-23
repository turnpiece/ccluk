<div class="wphb-block-entry">

	<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
		<img class="wphb-image"
             src="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary.png'; ?>"
             srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/graphic-hb-minify-summary@2x.png'; ?> 2x"
             alt="<?php _e('Hummingbird', 'wphb'); ?>">
	</div>

	<div class="wphb-block-entry-third">
		<span class="not-present">
			<?php if ( ! $percentage ) : ?>
				-
			<?php else : ?>
				<?php echo intval( $percentage ); ?>%
			<?php endif; ?>
		</span>
		<p><?php _e( 'Total minified file savings', 'wphb' ); ?></p>
	</div>

	<div class="wphb-block-entry-third">
		<ul class="dev-list">
			<li>
				<span class="list-label"><?php _e( 'Files found', 'wphb' ); ?></span>
				<span class="list-detail">
					<div class="wphb-dash-numbers"><?php echo intval( $enqueued_files ); ?></div>
				</span>
			</li>
			<li>
				<span class="list-label"><?php _e( 'Size reductions', 'wphb' ); ?></span>
				<span class="list-detail">
					<div class="wphb-dash-numbers"><?php echo intval( $compressed_size ); ?>kb</div>
				</span>
			</li>

			<?php if ( ! is_multisite() ) : ?>
				<?php if ( wphb_is_member() ) : ?>
					<li>
						<span class="list-label"><?php _e( 'WPMU DEV CDN', 'wphb' ); ?></span>
						<span class="list-detail">
					   <div class="toggle-actions">
							<span class="toggle tooltip-right" tooltip="<?php _e( 'Enable WPMU DEV CDN', 'wphb' ); ?>">
								<input type="checkbox" class="toggle-checkbox" name="use_cdn" id="use_cdn" <?php checked( $use_cdn ); ?> <?php disabled( ! $is_member ); ?>>
								<label for="use_cdn" class="toggle-label small"></label>
							</span>
						</div><!-- end toggle-actions -->
					</span>
					</li>
				<?php else : ?>
					<li>
						<span class="list-label"><?php _e( 'Host your files on WPMU DEV’s blazing fast CDN', 'wphb' ); ?></span>
						<span class="list-detail">
							<div><a class="button button-content-cta button-ghost" href="#wphb-upgrade-membership-modal" id="dash-uptime-update-membership" rel="dialog"><?php _e( 'Pro Feature', 'wphb' ); ?></a></div>
						</span>
					</li>
				<?php endif; ?>
			<?php endif; ?>
		</ul>
	</div>

</div><!-- end wphb-block-entry -->