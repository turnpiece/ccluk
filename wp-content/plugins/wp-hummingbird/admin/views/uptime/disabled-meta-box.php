<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
		     src="<?php echo wphb_plugin_url() . 'admin/assets/image/icon-uptime-small.png'; ?>"
		     srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/icon-uptime.png'; ?> 2x"
		     alt="<?php esc_attr_e( 'Monitor your website', 'wphb' ); ?>">

		<h2 class="title"><?php _e( 'Monitor your website', 'wphb' ); ?></h2>

		<div class="content">
			<p><?php echo sprintf( __( 'We can monitor your website\'s response time and let you know when you experience downtime. It\'s included with your WPMU DEV Membership and all <br> you have to do is flick a switch. What are you waiting for, %s?', 'wphb' ), $user); ?></p>
		</div><!-- end content -->

		<div class="buttons">
			<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-large" id="activate-uptime">
				<?php _e( 'Activate Uptime Monitoring', 'wphb' ); ?>
			</a>
		</div>

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->