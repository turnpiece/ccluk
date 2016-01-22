<div id="portfolio_carousel_container" class="carousel_outerrim">
    <?php global $post, $virtue; 
    $text = get_post_meta( $post->ID, '_kad_portfolio_carousel_title', true );
    if(!empty($text)) { 
    	echo '<h3 class="title">'.$text.'</h3>'; 
    } else {
    	echo '<h3 class="title">'.__('Recent Projects', 'virtue').'</h3>';
    } ?>
        <div class="portfolio-carouselcase fredcarousel">
            <?php $itemsize = 'tcol-lg-3 tcol-md-3 tcol-sm-4 tcol-xs-6 tcol-ss-12';
            $slidewidth = 269;
            $slideheight = 269;
            $md = 4;
            $sm = 3;
            $xs = 2;
            $ss = 1; 
            if(isset($virtue['portfolio_type_under_title']) && $virtue['portfolio_type_under_title'] == '0') {
					$portfolio_item_types = false;
				} else {
					$portfolio_item_types = true;
				}?>
				<div id="carouselcontainer-portfolio" class="rowtight">
            	<div id="portfolio-carousel" class="caroufedselclass initcaroufedsel clearfix" data-carousel-container="#carouselcontainer-portfolio" data-carousel-transition="300" data-carousel-scroll="1" data-carousel-auto="true" data-carousel-speed="9000" data-carousel-id="portfolio" data-carousel-md="<?php echo esc_attr($md);?>" data-carousel-sm="<?php echo esc_attr($sm);?>" data-carousel-xs="<?php echo esc_attr($xs);?>" data-carousel-ss="<?php echo esc_attr($ss);?>">
                <?php $temp = $wp_query; 
				  $wp_query = null; 
				  $wp_query = new WP_Query();
				  $wp_query->query(array(
					'orderby' 		 => 'date',
					'order' 		 => 'DESC',
					'post_type' 	 => 'portfolio',
					'posts_per_page' => '8'
					)
				  );
					if ( $wp_query ) : 
					while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
						<div class="<?php echo esc_attr($itemsize); ?>">
							<div class="grid_item portfolio_item all postclass">
								<?php if (has_post_thumbnail( $post->ID ) ) {
										$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
										$thumbnailURL = $image_url[0]; 
										$image = aq_resize($thumbnailURL, $slidewidth, $slideheight, true);
										if(empty($image)) {$image = $thumbnailURL;}?>
										<div class="imghoverclass">
		                                       <a href="<?php the_permalink();  ?>" title="<?php the_title(); ?>">
		                                       <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" width="<?php echo esc_attr($slidewidth);?>" height="<?php echo esc_attr($slideheight);?>" class="lightboxhover" style="display: block;">
		                                       </a> 
		                                </div>
	                           				<?php $image = null; $thumbnailURL = null;?>
                           		<?php } ?>
              				<a href="<?php the_permalink() ?>" class="portfoliolink">
              					<div class="piteminfo">   
                          			<h5><?php the_title();?></h5>
                          			<?php if($portfolio_item_types == true) {
				                        	$terms = get_the_terms( $post->ID, 'portfolio-type' );
				                        	if ($terms) {?>
				                        		<p class="cportfoliotag"><?php $output = array(); foreach($terms as $term){ $output[] = $term->name;} echo implode(', ', $output); ?></p>
				                        	<?php } 
				                        } ?>
			                    </div>
			                </a>
			          	</div>
                 </div>
					<?php endwhile; else: ?>
					 
					<div class="error-not-found"><?php _e('Sorry, no portfolio entries found.', 'virtue');?></div>
						
				<?php endif;
				$wp_query = null; 
				$wp_query = $temp;  // Reset
				wp_reset_query(); ?>
				</div>									
			</div>
    <div class="clearfix"></div>
    	<a id="prevport-portfolio" class="prev_carousel icon-chevron-left" href="#"></a>
		<a id="nextport-portfolio" class="next_carousel icon-chevron-right" href="#"></a>
    </div>
</div><!-- Porfolio Container-->