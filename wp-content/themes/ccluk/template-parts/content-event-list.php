<?php

/**
 * The template used for displaying page content
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */

$datetime = get_post_meta(get_the_ID(), 'incsub_event_start', true);
$date = mysql2date(get_option('time_format') . ' \o\n jS F', $datetime);

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('list-item'); ?>>

	<header class="entry-header">
		<?php if (has_post_thumbnail()) : ?>
			<a href="<?php the_permalink() ?>">
				<?php the_post_thumbnail('medium-thumb') ?>
			</a>
		<?php endif; ?>

		<a href="<?php the_permalink() ?>">
			<h2 class="entry-title"><?php the_title(); ?></h2>
		</a>

		<div class="entry-meta">
			<span class="post-date">
				<time datetime="<?php echo $datetime ?>"><?php echo $date ?></time>
			</span>
		</div>
	</header>

	<div class="entry-content">
		<?php the_excerpt() ?>
	</div>

</article>