<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<div class="content">
			<p><?php _e( 'Monitor your website and get notified if/when it\'s inaccessible. We\'ll also watch your server response time.', 'wphb' ); ?></p>
		</div><!-- end content -->

		<div class="row">
			<div class="col-half">
				<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ); ?>">
					<div class="report-status">
						<i class="hb-icon-performancetest"></i>
						<strong><?php _e( 'Performance Test', 'wphb' ); ?></strong>
						<?php if ( ! $performance_is_active ) : ?>
							<button class="inactive" disabled><?php _e( 'Inactive', 'wphb' ); ?></button>
						<?php else : ?>
							<button><i class="hb-wpmudev-icon-tick"></i> <?php echo $frequency; ?></button>
						<?php endif; ?>
					</div>
				</a>
			</div>
			<!--
			<div class="col-half">
				<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'uptime' ); ?>">
					<div class="report-status <?php echo ( ! $uptime_is_active ) ? 'with-corner grey' : ''; ?>">
						<i class="hb-icon-smush"></i>
						<strong><?php _e( 'Uptime Report', 'wphb' ); ?></strong>
						<?php if ( ! $uptime_is_active ) : ?>
							<div class="corner">
								<span class="tooltip-right" tooltip="<?php _e( 'To enable this report you must first activate Uptime Monitoring', 'wphb' ); ?>">
									<i class="hb-icon-reports"></i>
								</span>
							</div>
						<?php else : ?>
							<button><i class="hb-wpmudev-icon-tick"></i> <?php _e( 'Weekly', 'wphb' ); ?></button>
						<?php endif; ?>
					</div>
				</a>
			</div>
			-->
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->