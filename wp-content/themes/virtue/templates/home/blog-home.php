<div class="home_blog home-margin clearfix home-padding">
	<?php if(kadence_display_sidebar()) {
		$home_sidebar 	= true;
		$img_width 		= 407;
		$postwidthclass = 'col-md-6 col-sm-6 home-sidebar';
	} else {
		$home_sidebar 	= false;
		$img_width 		= 270;
		$postwidthclass = 'col-md-6 col-sm-6';
	}
	global $virtue; 
	if(isset($virtue['blog_title'])) {
		$btitle = $virtue['blog_title'];
	} else {
		$btitle = __('Latest from the Blog', 'virtue'); 
	} ?>
	<div class="clearfix"><h3 class="hometitle"><?php echo esc_html($btitle); ?></h3></div>
		<div class="row">
		<?php if(isset($virtue['home_post_count'])) {
			$blogcount = $virtue['home_post_count'];
		} else {
			$blogcount = '4';
		} 
		if(!empty($virtue['home_post_type'])) { 
			$blog_cat 	   = get_term_by ('id',$virtue['home_post_type'],'category');
			$blog_cat_slug = $blog_cat -> slug;
		} else {
			$blog_cat_slug = '';
		}

			$temp 	  = $wp_query; 
			$wp_query = null; 
			$wp_query = new WP_Query();
			$wp_query->query(array(
				'posts_per_page' => $blogcount,
				'category_name'	 => $blog_cat_slug,
				'ignore_sticky_posts' => true
				)
			);
			$xyz = 0;
			if ( $wp_query ) : while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
				<div class="<?php echo esc_attr($postwidthclass); ?> clearclass<?php echo esc_attr( ($xyz++%2) ); ?>">
				  	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	                    <div class="rowtight">
	                    	<?php if(isset($virtue['post_summery_default']) && ($virtue['post_summery_default'] != 'text')) {
	                    			if($home_sidebar == true) {
	                    				$textsize = 'tcol-md-12 tcol-sm-12 tcol-ss-12';
	                    				$imagesize = 'tcol-md-12 tcol-sm-12 tcol-ss-12';
	                    			} else {
	                    				$textsize = 'tcol-md-7 tcol-sm-12 tcol-ss-12';
	                    				$imagesize = 'tcol-md-5 tcol-sm-12 tcol-ss-12';
	                    			}
	                    			if (has_post_thumbnail( $post->ID ) ) {
										$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
										$thumbnailURL = $image_url[0]; 
										$image = aq_resize($thumbnailURL, $img_width, 270, true);
										if(empty($image)) { $image = $thumbnailURL; }
							 		} else {
								 		$thumbnailURL = virtue_post_default_placeholder();
										$image = aq_resize($thumbnailURL, $img_width, 270, true);
										if(empty($image)) { $image = $thumbnailURL; }
							 		} ?>
								 <div class="<?php echo $imagesize;?>">
									 <div class="imghoverclass">
		                           		<a href="<?php the_permalink()  ?>" title="<?php the_title(); ?>">
		                           			<img src="<?php echo esc_attr($image); ?>" alt="<?php the_title(); ?>" class="iconhover" style="display:block;">
		                           		</a> 
		                             </div>
		                         </div>

                           		<?php $image = null; $thumbnailURL = null; ?> 
                           		<?php } else {
                           		if (has_post_thumbnail( $post->ID ) ) {
                           			if($home_sidebar == true) {
                           				$textsize = 'tcol-md-12 tcol-sm-12 tcol-ss-12';
                           				$imagesize = 'tcol-md-12 tcol-sm-12 tcol-ss-12';
                           			} else {
                           				$textsize = 'tcol-md-7 tcol-sm-12 tcol-ss-12';
                           				$imagesize = 'tcol-md-5 tcol-sm-12 tcol-ss-12';
                           			}
										$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
										$thumbnailURL = $image_url[0]; 
										$image = aq_resize($thumbnailURL, $img_width, 270, true);
										if(empty($image)) { $image = $thumbnailURL; }
										?>
									<div class="<?php echo esc_attr($imagesize);?>">
									 	<div class="imghoverclass">
			                           		<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
			                           			<img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" class="iconhover" style="display:block;">
			                           		</a> 
		                             	</div>
		                         	</div>
		                        	<?php $image = null; $thumbnailURL = null; ?> 
		                        <?php } else { 
		                        	$textsize = 'tcol-md-12 tcol-ss-12';
		                        	} 
		                    }?>
	                       		<div class="<?php echo esc_attr($textsize);?> postcontent">
	                       			<div class="postmeta updated color_gray">
				                        	<div class="postdate bg-lightgray headerfont" itemprop="datePublished">
				                        		<span class="postday"><?php echo get_the_date('j'); ?></span>
				                        		<?php echo get_the_date('M Y');?>
				                        	</div>
				                            
				                        </div>
				                    <header class="home_blog_title">
			                          	<a href="<?php the_permalink() ?>">
			                          		<h4 class="entry-title"><?php the_title(); ?></h4>
			                          	</a>

			                          		<div class="subhead color_gray">
			                          			<span class="postauthortop author vcard" data-toggle="tooltip" data-placement="top" data-original-title="<?php echo get_the_author() ?>">
			                          				<span itemprop="author" class="kt_hidden"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>" class="fn" rel="author"><?php echo get_the_author() ?></a></span>
			                          				<i class="icon-user"></i>
			                          			</span>
			                          			<span class="kad-hidepostauthortop"> | </span>
			                          				<?php $post_category = get_the_category($post->ID); if (!empty($post_category)) { ?> 
			                          					<span class="postedintop" data-toggle="tooltip" data-placement="top" data-original-title="<?php 
			                          						foreach ($post_category as $category)  { 
			                          								echo $category->name .'&nbsp;'; 
			                          						} ?>"><i class="icon-folder-open"></i></span>
			                          		 		<?php }?>
			                          			<?php if(comments_open()) { ?>
			                          				<span class="kad-hidepostedin">|</span>
			                        				<span class="postcommentscount" data-toggle="tooltip" data-placement="top" data-original-title="<?php $num_comments = get_comments_number(); echo esc_attr($num_comments); ?>">
			                        					<i class="icon-comments-alt"></i>
			                        				</span>
			                        			<?php } ?>
			                        		</div>
			                        </header>
		                        	<div class="entry-content">
		                          		<p><?php echo virtue_excerpt(34); ?> <a href="<?php the_permalink() ?>"><?php _e('READ MORE', 'virtue');?></a></p>
		                        	</div>
		                      		<footer>
                       				</footer>
							</div>
	                   	</div>
                    </article>
                </div>

                    <?php endwhile; else: ?>
						<li class="error-not-found"><?php _e('Sorry, no blog entries found.', 'virtue');?></li>
					<?php endif; ?>
                
				
				<?php $wp_query = null; $wp_query = $temp;  // Reset ?>
				<?php wp_reset_query(); ?>

	</div>
</div> <!--home-blog -->