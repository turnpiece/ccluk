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

		<?php if (is_singular( array( 'post', 'ccluk_news' ) ) ) : ?>
		<div class="post-author-info">
			<div class="container">
				<div class="inner">
					<?php
					$author_id = $post->post_author;

					$author_name = get_the_author_meta( 'display_name', $author_id );

					$user_link = get_author_posts_url( $author_id );

					if ( function_exists( 'bp_core_get_userlink' ) && !function_exists( 'buddyboss_sap' ) ) {
						$user_link = bp_core_get_userlink( $author_id, false, true );
					}

					if ( function_exists( 'bp_core_get_userlink' ) && function_exists( 'buddyboss_sap' ) ) {
						$user_link = bp_core_get_userlink( $author_id, false, true ) . 'blog';
					}

					printf( '<span class="authors-avatar vcard table-cell"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr( sprintf( __( 'View all posts by %s', 'onesocial' ), $author_name ) ), get_avatar( $author_id, 85, '', $author_name ) );
					?>

					<div class="details table-cell">
						<?php
						printf( '<span class="author-name vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>', $user_link, esc_attr( sprintf( __( 'View all posts by %s', 'onesocial' ), $author_name ) ), $author_name
						);

						if ( buddyboss_is_bp_active() ):
							$bio_field = onesocial_get_option( 'boss_bio_field' );
							if ( $bio_field ) {
								$bio = bp_get_profile_field_data( array( 'field' => $bio_field, 'user_id' => $author_id ) );
								if ( $bio ) {
									?>
									<div class="author-bio"><?php echo onesocial_custom_excerpt( $bio, 15 ); ?></div>
									<?php
								}
							}
						endif;

						echo '<div class="entry-meta">';
						ccluk_posted_on();
						echo '</div>';
					?>
					</div><!--.details-->
				</div>
			</div>
		</div><!--.post-author-info-->
		<?php endif; ?>
	</header>

	<?php if (isset($image)) echo $image; ?>

</div>