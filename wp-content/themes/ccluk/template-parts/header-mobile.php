<?php
/*
 * Mobile Logo Option
 */

$logo_id = 366;
$logo	 = $logo_id ? wp_get_attachment_image($logo_id, 'medium', '', array('class' => 'boss-mobile-logo')) : get_bloginfo('name');
?>

<div id="mobile-header">

	<div class="mobile-header-inner">
		<!-- Right button -->
		<a href="#" id="main-nav" class="right-btn" data-position="right" title="Menu"><i data-lucide="menu"></i></a>
	</div>

	<div id="mobile-logo">
		<h1 class="site-title">
			<a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
				<?php echo $logo; ?>
			</a>
		</h1>
	</div>

</div><!-- #mobile-header -->