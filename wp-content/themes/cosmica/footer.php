			<div class="clearfix"></div>
	</div>
		<div class="pre-footer">
			<div class="container">
      <?php 
				 if ( is_active_sidebar( 'footer-sidebar-1' ) ) { ?>
					<div class="sidebar sidebar-footer col-md-4 col-xs-12">
						<?php dynamic_sidebar( 'footer-sidebar-1' ); ?>
					</div>
				<?php } ?>

				<?php if ( is_active_sidebar( 'footer-sidebar-2' ) ) { ?>
					<div class="sidebar sidebar-footer col-md-4 col-xs-12">
						<?php dynamic_sidebar( 'footer-sidebar-2' ); ?>
					</div>
				<?php } ?>

				<?php if ( is_active_sidebar( 'footer-sidebar-3' ) ) { ?>
					<div class="sidebar sidebar-footer col-md-4 col-xs-12">
						<?php dynamic_sidebar( 'footer-sidebar-3' ); ?>
					</div> 
				<?php }  ?>

				<div class="clearfix"></div>
			</div>
			<div class="clearfix"></div>
		</div>
		<footer id="footer">
				<div class="container">
					<div class="site-info clearfix">
						<?php 
				        		$site_url       	=   get_site_url();
				        		$blog_title 		=   get_bloginfo('name');
				        		$blog_description 	=   get_bloginfo('description');
				        ?>
				       <div class="copyright"> <?php echo esc_html('&copy; '.date("Y")); ?> <a href="<?php echo esc_url($site_url); ?>" title="<?php echo esc_attr($blog_title); ?>"><span><?php echo esc_html($blog_title);  ?></span></a> |  <?php _e('Theme by', 'cosmica') ?>: <a href="<?php echo esc_url('http://www.codeins.org'); ?>" target="_blank" title="Codeins"><span>Codeins</span></a> |  <?php _e('Proudly Powered by', 'cosmica') ?>: <a href="<?php echo esc_url('http://WordPress.org'); ?>" target="_blank" title="WordPress"><span>WordPress</span></a> </div>  <!-- .copyright -->	
					</div><!--steinfo -->
				</div>
				<div class="back-to-top" style="display:none;"><a title="<?php _e('Go to Top','cosmica') ?>" href="#gototop"></a></div>
		</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>