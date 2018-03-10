<div class="header-area">

	<?php
	global $post;

	$header_class = ' not-image';

	if ( has_post_thumbnail( $post->ID ) ) {
		$header_class	 = ' has-image';
		$image_id = get_post_thumbnail_id( $post->ID );
		$full_image = wp_get_attachment_image_src( $image_id, 'large-thumb' );

		ob_start();
		?>

		<div class="entry-post-thumbnail" style="background-image:url(<?php echo $full_image[ 0 ]; ?>);">
			<?php the_post_thumbnail('large-thumb'); ?>
		</div>

		<?php $image = ob_get_clean(); ?>

	<?php } ?>

	<!-- Title -->
	<header class="entry-header<?php echo $header_class; ?>">
		<h1 class="entry-title"><?php the_title(); ?><?php if(function_exists('sap_edit_post_link')) sap_edit_post_link(); ?></h1>
		<div class="profile-visible"><?php the_date(); ?></div>
	</header>

	<?php if (isset($image)) echo $image; ?>

</div>