<?php

/**
 * The Header for your theme.
 *
 * Displays all of the <head> section and everything up until <div id="main">
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 */
?>
<!DOCTYPE html>

<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="msapplication-tap-highlight" content="no" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />

	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> data-inputs="1">

	<?php get_template_part('template-parts/mobile-right-panel'); ?>

	<div id="main-wrap">

		<?php do_action('ccluk_before_header'); ?>

		<header id="masthead" class="site-header" data-infinite="on">
			<div class="header-wrapper">
				<?php get_template_part('template-parts/header-logo'); ?>
				<?php get_template_part('template-parts/header-nav'); ?>
				<?php get_template_part('template-parts/header-aside'); ?>
			</div>
		</header>

		<?php get_template_part('template-parts/header-mobile'); ?>

		<?php
		$show_single_header	 = apply_filters('ccluk_single_header', (is_single()));
		if ($show_single_header) {
			get_template_part('template-parts/header-single');
		}
		?>

		<div id="inner-wrap">

			<?php global $post; ?>

			<div id="page" class="<?php echo (is_single() && get_post_type($post->ID) == 'post' && has_post_thumbnail($post->ID)) ? 'has-thumbnail ' : ''; ?>hfeed site">

				<div id="main" class="wrapper">