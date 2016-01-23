	<div id="pageheader" class="titleclass">
		<div class="container">
			<?php get_template_part('templates/page', 'header'); ?>
		</div><!--container-->
	</div><!--titleclass-->
	
    <div id="content" class="container">
   		<div class="row">
	      	<div class="main <?php echo kadence_main_class(); ?>" role="main">
		      	<?php echo category_description(); ?> 
		      	<?php if (!have_posts()) : ?>
				  <div class="alert">
				    <?php _e('Sorry, no results were found.', 'virtue'); ?>
				  </div>
				  <?php get_search_form();
				endif;
				global $virtue;
				if(isset($virtue['portfolio_type_columns']) && $virtue['portfolio_type_columns'] == '4') {
					$itemsize = 'tcol-md-3 tcol-sm-4 tcol-xs-6 tcol-ss-12';
					$slidewidth = 269;
					$slideheight = 269;
				} elseif(isset($virtue['portfolio_type_columns']) && $virtue['portfolio_type_columns'] == '5') {
					$itemsize 		= 'tcol-md-25 tcol-sm-3 tcol-xs-4 tcol-ss-6';
					$slidewidth 	= 240;
					$slideheight 	= 240; 
				} else {
					$itemsize 		= 'tcol-md-4 tcol-sm-4 tcol-xs-6 tcol-ss-12';
					$slidewidth 	= 366; 
					$slideheight 	= 366; 
				}
				if(isset($virtue['portfolio_type_under_title']) && $virtue['portfolio_type_under_title'] == '0') {
					$portfolio_item_types = false;
				} else {
					$portfolio_item_types = true;
				}
				?>
				<div id="portfoliowrapper" class="rowtight">
				<?php while (have_posts()) : the_post(); ?>
					<div class="<?php echo esc_attr($itemsize);?>">
		                <div class="grid_item portfolio_item postclass">
							<?php global $post;
								if (has_post_thumbnail( $post->ID ) ) {
									$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
									$thumbnailURL = $image_url[0]; 
									$image = aq_resize($thumbnailURL, $slidewidth, $slideheight, true);
									if(empty($image)) {$image = $thumbnailURL;} ?>
										<div class="imghoverclass">
			                                <a href="<?php the_permalink()  ?>" title="<?php the_title(); ?>">
			                                   	<img src="<?php echo esc_url($image); ?>" width="<?php echo esc_attr($slidewidth);?>" height="<?php echo esc_attr($slideheight);?>" alt="<?php the_title(); ?>" class="lightboxhover" style="display: block;">
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
				<?php endwhile; ?>
		        </div> <!--portfoliowrapper-->
		        
		        <?php  	if ($wp_query->max_num_pages > 1) :
					        virtue_wp_pagenav();
					    endif; 

		                $wp_query = null; 
		                wp_reset_query(); ?>
			</div><!-- /.main -->