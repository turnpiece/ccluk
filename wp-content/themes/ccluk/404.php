<?php

/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 */
get_header();
?>

<div id="primary" class="site-content">

	<div id="content" role="main">

		<article id="post-0" class="post error404 no-results not-found">

			<header class="entry-header">
				<h1 class="entry-title"><?php _e('404', 'ccluk'); ?></h1>
			</header>

			<div class="entry-content">
				<p><?php _e('Sorry, that page doesn\'t exist.', 'ccluk'); ?></p>
			</div>
		</article>
	</div>
</div>

<?php
get_footer();
