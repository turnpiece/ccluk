<?php
/*
* Visual Composer Elements
*/

add_action('init', 'onesocial_requireVcExtend',2);
onesocial_addShortcodes();


/**
 * Extend VC
 */
function onesocial_requireVcExtend(){

    /*** Testimonials ***/
    vc_map( array(
        "name" => "Testimonials",
        "base" => "testimonials",
        "category" => 'BuddyBoss',
        "icon" => "icon-buddyboss",
        "allowed_container_element" => 'vc_row',
        "params" => array(
            array(
                'type' => 'param_group',
                'heading' => 'Testimonial',
                'param_name' => 'testimonial_items',
                'description' => 'Add testimonials',
                'params' => array(
                    array(
                        "type" => "textarea",
                        "holder" => "div",
                        "class" => "",
                        "heading" => "Quote",
                        "param_name" => "quote",
                        "value" => "",
                        "description" => ""
                    ),
                    array(
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => "Author",
                        "param_name" => "author"
                    ),
                    array(
                        "type" => "attach_image",
                        "holder" => "div",
                        "class" => "",
                        "heading" => "Author Image",
                        "param_name" => "author_image"
                    ),
                )
            )
        )
    ) );

    /*** Service ***/
    vc_map( array(
        "name" => "Service",
        "base" => "service",
        "category" => 'BuddyBoss',
        "icon" => "icon-buddyboss",
        "allowed_container_element" => 'vc_row',
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Title",
                "param_name" => "title"
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Fontawesome Icon Class",
                "param_name" => "icon",
                "value" => "fa-bell"
            ),
            array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => "Icon Color",
                "param_name" => "color",
                "value" => "#2fd2d1"
            ),
            array(
                "type" => "textarea_html",
                "holder" => "div",
                "class" => "",
                "heading" => "Description",
                "param_name" => "description",
                "value" => "",
                "description" => ""
            )
        )
    ) );

    /*** Blog Posts ***/
    vc_map( array(
        "name" => "Blog Posts",
        "base" => "blog_posts",
        "category" => 'BuddyBoss',
        "icon" => "icon-buddyboss",
        "allowed_container_element" => 'vc_row',
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Title",
                "param_name" => "title"
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Post Count",
                "param_name" => "count"
            ),
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Post IDs",
                "param_name" => "posts_in"
            )
        )
    ) );

    /*** Blog Posts ***/
    vc_map( array(
        "name" => "Blog Post",
        "base" => "blog_post",
        "category" => 'BuddyBoss',
        "icon" => "icon-buddyboss",
        "allowed_container_element" => 'vc_row',
        "params" => array(
            array(
                "type" => "textfield",
                "holder" => "div",
                "class" => "",
                "heading" => "Post ID",
                "param_name" => "id"
            )
        )
    ) );

}

function onesocial_addShortcodes(){

    /* Service */
    if (!function_exists('service')) {

        function service($atts, $content = null) {
            $args = array(
                "title"     => "",
                "icon"     => "fa-bell",
                "color"     => "#2fd2d1",
                "description"   => ""
            );

            extract(shortcode_atts($args, $atts));

            ob_start();
            ?>
            <div class="onesocial-service">
                <i class="fa <?php echo $icon; ?>" style="color: <?php echo $color; ?>"></i>
                <div class="service-content">
                    <h4 class="title"><?php echo $title; ?></h4>
                    <p><?php echo $description; ?></p>
                </div>

            </div>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
    }
    add_shortcode('service', 'service');

    /* Blog Posts */
    if (!function_exists('blog_posts')) {

        function blog_posts($atts, $content = null) {

            $args = array(
                "title"     => "",
                "count"     => 3,
                "posts_in"     => ""
            );

            extract(shortcode_atts($args, $atts));

            ob_start();
            ?>
			<div class="onesocial-posts-carousel">
				<?php
				$query_args = array();

                if ( $posts_in != '' ) {
                    $query_args['post__in'] = explode( ",", $posts_in );
                }

                // Post teasers count
                if ( $count != '' && ! is_numeric( $count ) ) {
                    $count = - 1;
                }
                if ( $count != '' && is_numeric( $count ) ) {
                    $query_args['posts_per_page'] = $count;
                }

				$posts = new WP_Query( $query_args );

				if ( $posts->have_posts() ) :
					?>

					<div id="posts-carousel">

						<div class="clearfix bb-carousel-header">
							<h3 class="title"><?php echo $title; ?></h3>

							<span class="arrows">
								<a href="#" id="prev" class="bb-icon-chevron-left"></a>
								<a href="#" id="next" class="bb-icon-chevron-right"></a>
							</span>
						</div>

						<ul>
							<!-- Start the Loop -->
							<?php
                            $i = 0;
							while ( $posts->have_posts() ) :
								$posts->the_post();
                                if($i == 0) echo '<li>';
								?>
                                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                                    <div class="header-area">
                                        <?php
                                        $thumb_class	 = '';

                                        if ( has_post_thumbnail() ) :
                                            $thumb_class = 'category-thumb';
                                            $size		 = 'thumbnail';
                                            ?>

                                            <a class="<?php echo $thumb_class; ?>" href="<?php the_permalink(); ?>">
                                                <?php the_post_thumbnail( $size ); ?>
                                            </a>

                                        <?php endif; ?>

                                        <?php
                                            printf( '<a href="%1$s" title="%2$s" rel="bookmark" class="entry-date"><i class="fa fa-calendar"></i><time datetime="%3$s">%4$s</time></a>',
                                                   esc_url( get_permalink() ),
                                                   esc_attr( get_the_time() ),
                                                   esc_attr( get_the_date( 'c' ) ),
                                                   esc_html( get_the_date() )
                                            );
                                        ?>
                                        <h4><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'onesocial' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h4>

                                    </div>

                                </article>
								<?php
                                    if($i == 1) echo '</li><!-- li -->';
                                    $i++;
                                    if($i == 2) $i = 0;
                                ?>
							<?php endwhile; ?>
							<?php if($i == 1) echo '</li><!-- li -->'; ?>
							<?php wp_reset_query(); ?>
						</ul>
					</div>

				<?php endif; ?>

			</div>
            <?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
    }
    add_shortcode('blog_posts', 'blog_posts');

    /* Blog Post */
    if (!function_exists('blog_post')) {

        function blog_post($atts, $content = null) {

            $args = array(
                "id"     => ""
            );

            extract(shortcode_atts($args, $atts));

            ob_start();

            global $post;
            $save_post = $post;
            $post = get_post( $id );
            ?>

            <article class="post-box">

                <?php
                if ( has_post_thumbnail() ) :
                    $thumb_class = 'category-thumb';
                    $size		 = 'medium-thumb';
                    ?>

                    <a class="<?php echo $thumb_class; ?>" href="<?php the_permalink(); ?>">
                        <?php the_post_thumbnail( $size ); ?>
                    </a>

                <?php endif; ?>
                <div class="entry-summary">
                    <?php
                        printf( '<a href="%1$s" title="%2$s" rel="bookmark" class="entry-date"><i class="fa fa-calendar"></i><time datetime="%3$s">%4$s</time></a>',
                               esc_url( get_permalink() ),
                               esc_attr( get_the_time() ),
                               esc_attr( get_the_date( 'c' ) ),
                               esc_html( get_the_date() )
                        );
                    ?>
                    <h3><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'onesocial' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h3>
                    <p><?php the_excerpt(); ?></p>
                    <a class="button content-button" href="<?php the_permalink(); ?>">More</a>
                </div>
            </article>

            <?php
            $post = $save_post;
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
    }
    add_shortcode('blog_post', 'blog_post');

    /* Testimonials */
    if (!function_exists('testimonials')) {

        function testimonials($atts, $content = null) {

            $args = array(
                "testimonial_items"   => "",
            );

            extract(shortcode_atts($args, $atts));
            $testimonials = (array) vc_param_group_parse_atts( $testimonial_items );
            ob_start();
            ?>

            <div class="testimonials-wrap">
                <!-- Teatimonials  -->
                <div class="testimonial-items">
                    <?php
                    $authors = '';
                    $i = 1;
                    foreach ( $testimonials as $testimonial ) {
                    ?>
                    <!-- Testimonial -->
                    <div class="testimonial" id="<?php echo $i; ?>">
                        <div>
                            <div class="quote">
                                <p>"<?php echo $testimonial['quote']; ?>"</p>
                            </div>
                            <span class="autor">
                                <?php echo $testimonial['author']; ?>
                            </span>
                        </div>
                    </div>
                    <!-- Testimonial -->
                    <?php
                        if ( $testimonial['author_image'] > 0 ) {
                            $post_thumbnail = wp_get_attachment_image( $testimonial['author_image'], 'thumbnail' );
                        }
                        $authors .=
                        '<li>
                            <span data-id="'.$i.'">
                                '.$post_thumbnail.'
                            </span>
                        </li>';
                        $i++;
                    ?>
                    <?php } ?>
                </div>
                <!-- Testimonials -->

                <!-- Authors -->
                <ul class="author-images">
                    <?php echo $authors; ?>
                </ul>
                <!-- End Authors -->
            </div>

            <?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
    }
    add_shortcode('testimonials', 'testimonials');
}
