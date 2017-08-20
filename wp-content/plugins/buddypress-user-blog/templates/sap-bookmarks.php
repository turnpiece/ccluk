<?php
/**
 * The template for displaying WordPress pages, including HTML from BuddyPress templates.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
get_header();
?>

<div id="primary" class="site-content">

	<div id="content" role="main">

		<header class="entry-header">
			<h1 class="entry-title"><?php echo get_the_title( get_the_ID() ); ?></h1>
		</header>

		<?php
		if ( is_user_logged_in() ) {

			$bookmarked = get_user_option( 'sap_user_bookmarks', get_current_user_id() );

			if ( !empty( $bookmarked ) ) {
				?>

				<?php
				foreach ( $bookmarked as $id ) {
					?>

					<article class="post hentry bookmarked-post">

						<div class="header-area">

							<?php if ( has_post_thumbnail( $id ) ) { ?>
								<a href="<?php echo get_permalink( $id ); ?>" class="entry-post-thumbnail">
									<?php echo get_the_post_thumbnail( $id, 'post-thumb' ) ?>
								</a>
							<?php } ?>

							<header class="entry-header category-thumb">

								<h2 class="entry-title">
									<a href="<?php echo get_permalink( $id ); ?>" rel="bookmark"><?php echo get_the_title( $id ); ?></a>
								</h2>

							</header>

						</div>

						<div class="entry-content entry-summary">

							<?php
							$content_post	 = get_post( $id );
							$post_content	 = $content_post->post_content;
							?>

							<p><?php echo wp_trim_words( $post_content, 55 ) ?></p>

							<footer class="entry-meta">
								<a href="<?php echo get_permalink( $id ); ?>" class="read-more"><?php _e( 'Continue reading', 'bp-user-blog' ); ?></a>
								<span class="sep"><?php _e( '.', 'bp-user-blog' ) ?></span>
								<span><?php echo boss_estimated_reading_time( $post_content ); ?></span>
								<a href="#" class="to-top bb-icon-arrow-top-f"></a>
							</footer>

						</div>

					</article><?php
				}
			}
		} else {
			_e( 'Please login to view your bookmarks', 'bp-user-blog' );
		}
		?>

	</div>
</div>

<?php
get_sidebar();

get_footer();
