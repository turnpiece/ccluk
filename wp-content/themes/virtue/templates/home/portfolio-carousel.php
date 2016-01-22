<div class="home-portfolio home-margin carousel_outerrim home-padding">
		<?php global $virtue;
			if(isset($virtue['portfolio_title'])) {
				$porttitle = $virtue['portfolio_title'];
			} else {
				$porttitle = __('Featured Projects', 'virtue');
			}
			if(!empty($virtue['home_portfolio_carousel_count'])) {
				$hp_pcount = $virtue['home_portfolio_carousel_count'];
			} else {
				$hp_pcount = '6';
			} 
			if(!empty($virtue['home_portfolio_order'])) {
				$hp_orderby = $virtue['home_portfolio_order'];
			} else {
				$hp_orderby = 'menu_order';
			}
			if($hp_orderby == 'menu_order' || $hp_orderby == 'title') {
				$p_order = 'ASC';
			} else {
				$p_order = 'DESC';
			} ?>
			<div class="clearfix"><h3 class="hometitle"><?php echo esc_html($porttitle); ?></h3></div>
			<?php  if(!empty($virtue['portfolio_type'])) {
							$port_cat = get_term_by ('id',$virtue['portfolio_type'],'portfolio-type');
							$portfolio_category = $port_cat -> slug;
						} else {
							$portfolio_category = '';
						}
					if(isset($virtue['portfolio_show_type'])) {
						$portfolio_item_types = $virtue['portfolio_show_type'];
					} else {
						$portfolio_item_types = '';
					}
					$itemsize = 'tcol-lg-4 tcol-md-4 tcol-sm-4 tcol-xs-6 tcol-ss-12';
					$slidewidth = 366;
					$slideheight = 366;
					$md = 3;
					$sm = 3;
					$xs = 2;
					$ss = 1; ?>
				<div class="home-margin fredcarousel">
				<div id="carouselcontainer-portfolio" class="rowtight fadein-carousel">
				<div id="portfolio-carousel" class="clearfix caroufedselclass initcaroufedsel clearfix" data-carousel-container="#carouselcontainer-portfolio" data-carousel-transition="700" data-carousel-scroll="1" data-carousel-auto="true" data-carousel-speed="9000" data-carousel-id="portfolio" data-carousel-md="<?php echo esc_attr($md);?>" data-carousel-sm="<?php echo esc_attr($sm);?>" data-carousel-xs="<?php echo esc_attr($xs);?>" data-carousel-ss="<?php echo esc_attr($ss);?>">
				<?php 
				$temp = $wp_query; 
				$wp_query = null; 
				$wp_query = new WP_Query();
				$wp_query->query(array(
					'orderby' 		=> $hp_orderby,
					'order' 		=> $p_order,
					'post_type' 	=> 'portfolio',
					'portfolio-type'=> $portfolio_category,
					'posts_per_page'=> $hp_pcount
					)
				  );
				if ( $wp_query ) : 
							 
					while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
					<div class="<?php echo $itemsize; ?> kad_portfolio_item">
						<div class="grid_item portfolio_item postclass">
                        <?php if (has_post_thumbnail( $post->ID ) ) {
									$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
									$thumbnailURL = $image_url[0]; 
									$image = aq_resize($thumbnailURL, $slidewidth, $slideheight, true); 
									 if(empty($image)) {$image = $thumbnailURL; } ?>

									<div class="imghoverclass">
	                                    <a href="<?php the_permalink()  ?>" title="<?php the_title(); ?>" class="kad_portfolio_link">
	                                   		<img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" class="lightboxhover" style="display: block;">
	                                    </a> 
	                                </div>
                           				<?php $image = null; $thumbnailURL = null;?>
                           <?php } ?>
					              	<a href="<?php the_permalink() ?>" class="portfoliolink">
					              		<div class="piteminfo">   
					                        <h5><?php the_title();?></h5>
					                        <?php if($portfolio_item_types == 1) { $terms = get_the_terms( $post->ID, 'portfolio-type' ); if ($terms) {?> 
					                           	<p class="cportfoliotag"><?php $output = array(); foreach($terms as $term){ $output[] = $term->name;} echo implode(', ', $output); ?></p> 
					                        <?php } } ?>
					                    </div>
					                </a>
                		</div>
                	</div>
					<?php endwhile; else: ?>
					<li class="error-not-found"><?php _e('Sorry, no portfolio entries found.', 'virtue');?></li>	
					<?php endif; 

                    $wp_query = null; 
                    $wp_query = $temp;
                    wp_reset_query(); ?>
                </div>
			 </div>
			 <div class="clearfix"></div>
			        <a id="prevport-portfolio" class="prev_carousel icon-chevron-left" href="#"></a>
					<a id="nextport-portfolio" class="next_carousel icon-chevron-right" href="#"></a>
			</div> <!-- fred Carousel-->
</div> <!--featclass -->