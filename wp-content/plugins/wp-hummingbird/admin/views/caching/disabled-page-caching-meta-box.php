<div class="wphb-block-entry">

	<div class="wphb-block-entry-content wphb-block-content-center">

		<img class="wphb-image wphb-image-center wphb-image-icon-content-top"
			 src="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-pagecaching-disabled.png'; ?>"
			 srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hb-graphic-pagecaching-disabled@2x.png'; ?> 2x"
			 alt="<?php esc_attr_e( 'Page Caching', 'wphb' ); ?>">

		<div class="content">
			<p><?php esc_html_e( 'Page caching stores static HTML copies of your pages and posts. These static files are then served to visitors, reducing the processing load on the server and dramatically speeding up your page load time. It’s probably the best performance feature ever.', 'wphb' ); ?></p>
			<a href="<?php echo esc_url( '' ); ?>" class="button button-large" id="activate-page-caching"><?php esc_html_e( 'Activate', 'wphb' ); ?></a>
		</div><!-- end content -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->