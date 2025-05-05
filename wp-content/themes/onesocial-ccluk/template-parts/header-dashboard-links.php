<div id="dashboard-links" class="dashboard-links">
	<ul>
		<?php if (is_multisite()): ?>
			<?php if (is_super_admin()): ?>
				<li class="menupop">
					<a class="ab-item" href="<?php echo admin_url('my-sites.php'); ?>"><?php _e('Sites', 'ccluk'); ?></a>
					<div class="ab-sub-wrapper">
						<ul class="ab-submenu">
							<li class="menupop network-menu">
								<a class="ab-item" href="<?php echo network_admin_url(); ?>"><?php _e('Network Admin', 'ccluk'); ?></a>
								<div class="ab-sub-wrapper">
									<ul class="ab-submenu">
										<li>
											<a href="<?php echo network_admin_url(); ?>"><?php _e('Dashboard', 'ccluk'); ?></a>
											<a href="<?php echo network_admin_url('admin.php?page=onesocial_options'); ?>"><?php _e('CCLUK Options', 'ccluk'); ?></a>
											<a href="<?php echo network_admin_url('sites.php'); ?>"><?php _e('Sites', 'ccluk'); ?></a>
											<a href="<?php echo network_admin_url('users.php'); ?>"><?php _e('Users', 'ccluk'); ?></a>
											<a href="<?php echo network_admin_url('themes.php'); ?>"><?php _e('Themes', 'ccluk'); ?></a>
											<a href="<?php echo network_admin_url('plugins.php'); ?>"><?php _e('Plugins', 'ccluk'); ?></a>
										</li>
									</ul>
								</div>
							</li>
							<?php
							$current_blog_id = get_current_blog_id();

							global $wp_admin_bar;
							foreach ((array) $wp_admin_bar->user->blogs as $blog) {
								switch_to_blog($blog->userblog_id);
								$blogname = empty($blog->blogname) ? $blog->domain : $blog->blogname;
							?>
								<li class="menupop">
									<a class="ab-item" href="<?php echo home_url(); ?>"><?php echo $blogname; ?></a>
									<div class="ab-sub-wrapper">
										<ul class="ab-submenu">
											<li>
												<a href="<?php echo admin_url(); ?>"><?php _e('Dashboard', 'ccluk'); ?></a>
												<a href="<?php echo admin_url('admin.php?page=onesocial_options'); ?>"><?php _e('CCLUK Options', 'ccluk'); ?></a>
												<a href="<?php echo admin_url('users.php'); ?>"><?php _e('Users', 'ccluk'); ?></a>
												<a href="<?php echo admin_url('themes.php'); ?>"><?php _e('Themes', 'ccluk'); ?></a>
												<a href="<?php echo admin_url('plugins.php'); ?>"><?php _e('Plugins', 'ccluk'); ?></a>
											</li>
										</ul>
									</div>
								</li>
							<?php
							}

							//switch back to current blog
							switch_to_blog($current_blog_id);
							?>
						</ul>
					</div>
				</li>
			<?php endif; ?>
			<li class="menupop">
				<a class="ab-item" href="<?php echo admin_url(); ?>"><?php _e('Dashboard', 'ccluk'); ?></a>
				<div class="ab-sub-wrapper">
					<ul class="ab-submenu">
						<li>
							<a href="<?php echo admin_url('post-new.php'); ?>"><?php _e('Write Blog', 'ccluk'); ?></a>
							<a href="<?php echo admin_url('post-new.php?post_type=ccluk_news'); ?>"><?php _e('Write News', 'ccluk'); ?></a>
						</li>
					</ul>
				</div>
			</li>
		<?php else: ?>
			<li class="menupop">
				<a class="ab-item" href="<?php echo admin_url(); ?>"><?php _e('Dashboard', 'ccluk'); ?></a>
				<div class="ab-sub-wrapper">
					<ul class="ab-submenu">
						<li>
							<a href="<?php echo admin_url('post-new.php'); ?>"><?php _e('Write Blog', 'ccluk'); ?></a>
							<a href="<?php echo admin_url('post-new.php?post_type=ccluk_news'); ?>"><?php _e('Write News', 'ccluk'); ?></a>
						</li>
					</ul>
				</div>
			</li>
		<?php endif; ?>
	</ul>
</div>