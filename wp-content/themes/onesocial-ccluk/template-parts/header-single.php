<div class="header-area">

	<?php
	global $post;

	$header_class = ' not-image';

	if ( has_post_thumbnail( $post->ID ) ) {
		$header_class	 = ' has-image';
		$image_id = get_post_thumbnail_id( $post->ID );
		$full_image = wp_get_attachment_image_src( $image_id, 'ccluk-hero' );

		ob_start();
		?>

		<div class="entry-post-thumbnail" style="background-image:url(<?php echo $full_image[ 0 ]; ?>);">
			<?php the_post_thumbnail('ccluk-hero'); ?>
		</div>

		<?php $image = ob_get_clean(); ?>

	<?php } ?>

	<!-- Title -->
	<header class="entry-header<?php echo $header_class; ?>">
		<h1 class="entry-title"><?php the_title(); ?><?php if(function_exists('sap_edit_post_link')) sap_edit_post_link(); ?></h1>

		<?php if (is_singular( array( 'post', 'ccluk_news' ) ) ) : ?>
		<div class="post-author-info">
			<div class="container">
				<div class="inner">
					<?php if (is_singular('post')) :

					$author_id = $post->post_author;

					$author_name = get_the_author_meta( 'display_name', $author_id );

					$user_link = ccluk_get_user_link( $author_id );

					printf( '<span class="authors-avatar vcard table-cell"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr( sprintf( __( 'View all posts by %s', 'onesocial' ), $author_name ) ), get_avatar( $author_id, 85, '', $author_name ) );

					endif; ?>

					<div class="details table-cell">
						<?php if (is_singular('post')) :

						printf( '<span class="author-name vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr( sprintf( __( 'View all posts by %s', 'onesocial' ), $author_name ) ), $author_name
						);

						endif;?>

						<div class="entry-meta">
							<?php
							// date posted
							ccluk_posted_on();

							// categories
				            $categories_list = get_the_category_list(', ');
				            if (!empty($categories_list)) : ?>
				                &nbsp; / &nbsp; <span class="cat-links"><?php echo $categories_list ?></span>
				            <?php endif; ?>
						</div>
					</div><!--.details-->
				</div>
			</div>
		</div><!--.post-author-info-->
		<?php endif; ?>
	</header>

	<?php if (isset($image)) echo $image; ?>

</div>
