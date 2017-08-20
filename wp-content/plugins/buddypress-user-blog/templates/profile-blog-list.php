<article class="post sap-post sap-member-post">

	<div class="post-featured-image">
		<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'bp-user-blog' ); ?> <?php the_title_attribute(); ?>">
			<?php
			if ( function_exists( 'has_post_thumbnail' ) && has_post_thumbnail( get_the_ID() ) ) {
				the_post_thumbnail( 'thumbnail', array( 'class' => 'avatar' ) );
			} else {
				echo get_avatar( get_the_author_meta( 'user_email' ), '150' );
			}
			?>
		</a>
	</div>

	<div class="post-content">

		<div class="sap-post-title">
			<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'bp-user-blog' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                        <?php sap_edit_post_link() ; ?>
		</div>
		<p class="date"><?php printf( __( '%1$s', 'bp-user-blog' ), get_the_date() ); ?></p>

		<div class="sap-excerpt">
			<?php the_excerpt(); ?>
		</div>
	</div>

</article>