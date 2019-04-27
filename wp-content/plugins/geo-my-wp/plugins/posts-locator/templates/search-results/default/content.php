<?php
/**
 * Posts locator "default" search results template file. 
 * 
 * This file outputs the search results.
 * 
 * You can modify this file to apply custom changes. However, it is not recomended
 * to make the changes directly in this file,
 * because your changes will be overwritten with the next update of the plugin.
 * 
 * Instead you can copy-paste this template ( the "gray" folder contains this file 
 * and the "css" folder ) into the theme's or child theme's folder of your site, 
 * and apply your changes from there. 
 * 
 * The custom template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/posts-locator/search-results/
 * 
 * Once the template folder is in the theme's folder, you will be able to select 
 * it in the form editor. It will show in the "Search results" dropdown menu labed with "Custom: ".
 *
 * @param $gmw  ( array ) the form being used
 * @param $gmw_form ( object ) the form object
 * @param $post ( object ) post object in the loop
 * 
 */
?>
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper default gmw-pt-default-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">

	<?php if ( $gmw_form->has_locations() ) : ?>

        <div class="gmw-results">

			<?php do_action( 'gmw_search_results_start', $gmw ); ?>
			
			<div class="gmw-results-count">
				<span><?php gmw_results_message( $gmw ); ?></span>
				<?php do_action( 'gmw_search_results_after_results_message', $gmw ); ?>
			</div>
			
			<?php do_action( 'gmw_search_results_before_top_pagination', $gmw ); ?>
			
			<div class="pagination-per-page-wrapper top gmw-pt-pagination-wrapper gmw-pt-top-pagination-wrapper">
				<?php gmw_per_page( $gmw ); ?>
				<?php gmw_pagination( $gmw ); ?>
			</div> 
				
		    <?php gmw_results_map( $gmw ); ?>
			
			<div class="clear"></div>
			
			<?php do_action( 'gmw_search_results_before_loop', $gmw ); ?>
			
			<ul class="gmw-posts-wrapper">
				
				<?php while ( $gmw_query->have_posts() ) : $gmw_query->the_post(); ?>
					
					<?php global $post; ?>

					<li id="single-post-<?php echo absint( $post->ID ); ?>" class="<?php echo esc_attr( $post->location_class ); ?>">
					
						<?php do_action( 'gmw_search_results_loop_item_start' , $gmw, $post ); ?>
						
		                <div class="wppl-title-holder">
		                    <h2 class="wppl-h2">
		                        <a href="<?php echo the_permalink(); ?>">
		                        	<?php echo $post->location_count; ?>) <?php the_title(); ?>		
		                        </a>
		                        <?php gmw_search_results_distance( $post, $gmw ); ?>
		                    </h2>
		                </div>
		                
		                <?php do_action( 'gmw_search_results_before_image', $gmw, $post ); ?>

						<?php gmw_search_results_featured_image( $post, $gmw ); ?>

		                <?php do_action( 'gmw_search_results_before_excerpt' , $gmw, $post ); ?>
							
						<?php gmw_search_results_post_excerpt( $post, $gmw ); ?>

		                <?php do_action( 'gmw_search_results_before_taxonomies' , $gmw, $post ); ?>
		                
		                <?php gmw_search_results_taxonomies( $post, $gmw ); ?>
		                
		                <div class="wppl-info">

		                    <div class="wppl-info-left">

		                       <?php do_action( 'gmw_search_results_before_contact_info', $post, $gmw ); ?>
							   		
						   		<?php gmw_search_results_location_meta( $post, $gmw ); ?>

							    <?php do_action( 'gmw_search_results_before_hours_of_operation', $post, $gmw ); ?>

							    <?php gmw_search_results_hours_of_operation( $post, $gmw ); ?>

		                    </div>

		                    <div class="wppl-info-right">

		                    	<?php do_action( 'gmw_search_results_before_address', $post, $gmw ); ?>

		                        <div class="address-wrapper">
							    	<?php gmw_search_results_address( $post, $gmw ); ?>
							    </div>
		           	
								<?php do_action( 'gmw_search_results_before_get_directions', $gmw, $post ); ?>
									
			    				<?php gmw_search_results_directions_link( $post, $gmw ); ?>
		                    </div>
		                </div>
		                
		                <?php do_action( 'gmw_search_results_loop_item_end' , $gmw, $post ); ?>
		            </li>

		           <div class="clear"></div>  

		        <?php endwhile; ?>

		    </ul>
			
			<?php do_action( 'gmw_search_results_before_bottom_pagination', $gmw ); ?>
			
		    <div class="pagination-per-page-wrapper bottom gmw-pt-pagination-wrapper gmw-pt-bottom-pagination-wrapper">  
		        <?php gmw_per_page( $gmw ); ?>
		        <?php gmw_pagination( $gmw ); ?>
		    </div> 
		</div>

	<?php else : ?>

        <div class="gmw-no-results">
            
            <?php do_action( 'gmw_no_results_start', $gmw ); ?>

            <?php gmw_no_results_message( $gmw ); ?>
            
            <?php do_action( 'gmw_no_results_end', $gmw ); ?> 

        </div>

    <?php endif; ?>

</div>
