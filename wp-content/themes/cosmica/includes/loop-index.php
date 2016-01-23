<?php
if ( have_posts() ) :
	// Start the Loop.
	while ( have_posts() ) : the_post();
?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
					<?php  the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );  ?>

					<div class="entry-meta">
						
						<?php if ( 'post' == get_post_type() ) : 
						printf('<div class="bypostauthor vcard author">
									<span class="fn n"> <a  href="%1$s" title="%2$s" rel="author">%3$s</a> </span>
								</div>',
								esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
								esc_attr( sprintf( __( 'View all posts by %s', 'cosmica' ), get_the_author() ) ),
								get_the_author()
							);
						endif; ?>

						<div class="date updated"> <?php  cosmica_entry_date(); ?> </div>
						<?php $comments_count = wp_count_comments(get_the_id());  
						if($comments_count->approved > 0 ){ 
								$comments_str = ($comments_count->approved > 1)?__('Comments','cosmica'):__('Comment','cosmica');
						?>
							<div class="coments-count"> <a href="<?php echo esc_url( get_permalink() );?>#comments"> <?php echo number_format_i18n($comments_count->approved).' '.$comments_str; ?> </a> </div>
						<?php
						 }

							$categories_list = get_the_category_list( __( ', ', 'cosmica' ) ); 
							if ( $categories_list ) { 
						?>	<div class="categories-links"> 	<?php echo $categories_list; ?> </div>

						<?php	} ?>

						
						<?php if(current_user_can( 'edit_posts' )):?><div class="edit-link">	<?php edit_post_link( __( 'Edit', 'cosmica' ), '<span class="edit-link">', '</span>' ); ?> </div> <?php endif; ?>
					</div><!-- .entry-meta -->

			</header><!-- .entry-header -->
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="post-img"> <?php the_post_thumbnail(); ?> </div>
			<?php endif; ?>
			<div class="entry-summary">
						<?php
							the_excerpt();
						?>
						<a class="read-more" href="<?php the_permalink(); ?>"> <?php _e('Read More', 'cosmica'); ?></a>

			</div><!-- .entry-content --> 
			<footer class="post-footer">
				 <?php $tag_list = get_the_tag_list( '', __( ', ', 'cosmica' ) );
					if ( $tag_list ) {
				?>
					<div class="tags-links"> <?php echo $tag_list ; ?> </div>

				<?php	} ?> 

			</footer>
		</article><!-- #post-## -->				
<?php
	endwhile;
endif;
?>