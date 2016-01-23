<?php
/*
 * Plugin Name: DD News Widget
 * Plugin URI: http://themeforest.net/user/DDStudios/portfolio
 * Description: A widget that displays recent news
 * Version: 1.0
 * Author: Dany Duchaine
 * Author URI: http://themeforest.net/user/DDStudios/
 */

/*
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'dd_news_widgets' );

/*
 * Register widget.
 */
function dd_news_widgets() {
	register_widget( 'DD_News_Widget' );
}

/*
 * Widget class.
 */
class dd_news_widget extends WP_Widget {

	/* ---------------------------- */
	/* -------- Widget setup -------- */
	/* ---------------------------- */
	
	function DD_News_Widget() {
	
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'dd_news_widget', 'description' => __('A widget that displays your latest news.', 'localization') );

		 /* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'dd_news_widget' );

		/* Create the widget. */
		$this->WP_Widget( 'dd_news_widget', __('DD News Widget','localization'), $widget_ops, $control_ops );
	}

	/* ---------------------------- */
	/* ------- Display Widget -------- */
	/* ---------------------------- */
	
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
                $title = apply_filters('widget_title', $instance['title'] );
	
		$postcount = $instance['postcount'];
                $categories = $instance['categories'];
                $viewall = $instance['viewall'];
	
		/* Before widget (defined by themes). */
 
              
                
         
                
		
              	/* Before widget (defined by themes). */
        echo $before_widget;
                
         
             
        	
        ?>

  <h3>
      
      <?php echo $title ?>
      
      
           <?php if ($viewall != '') { ?>
                    

                  <span><a href="<?php echo $viewall ?>"><?php _e('VIEW ALL &rarr;', 'localization'); ?></a></span>
                  
                                <?php } ?>
      
  
  </h3>
                    
              
                  
                
                     <ul>
                    
                         
                 <?php
                global $paged;


                $arguments = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'paged' => $paged,
                    'showposts' => $postcount,
                       'cat' => $categories
                );

                $blog_query = new WP_Query($arguments);

                dd_set_query($blog_query);
            ?>
                    
         <?php if ($blog_query->have_posts()) : while ($blog_query->have_posts()) : $blog_query->the_post(); ?>


                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);

?>
                      
                  
                           <li <?php post_class('dd_news_post'); ?>>
                               
                                     <?php if( $bigimg) { ?>
                               
                        <div class="postTitleWithImage clearfix">
                            
                        <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                               
                                       <?php } else { ?>
                               
                               
                                 <div class="postTitle clearfix">
                            
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                        
                                       <?php } ?>
                               
                  
                                <ul class="metaBtn clearfix">
                                    
                                    <li class="widgetAuthor"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" ><span><?php _e('By', 'localization'); ?></span> <?php the_author(); ?></a></li>
                                     <li><a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><span><?php _e('On', 'localization'); ?></span> <?php echo the_time('F j, Y'); ?></a></li>
                                   
                                    
                                </ul>
                        
                               <div class="postCategories"> <span><?php _e('Posted In', 'localization'); ?></span> <?php the_category(', ');?>    </div>
                               
                                       <?php the_excerpt(); ?>
                        
                               <a class="continue" href="<?php the_permalink(); ?>"><?php _e('CONTINUE READING', 'localization'); ?> &rarr;</a>
                        
                    </li>
                                     

      <?php endwhile; ?>
                    
                

<?php endif; ?>
                     
                     
                </ul>
                    
      
		<?php 

		/* After widget (defined by themes). */
                
		
        echo $after_widget;
                
         
		
	}

	/* ---------------------------- */
	/* ------- Update Widget -------- */
	/* ---------------------------- */
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
                $instance['title'] = strip_tags( $new_instance['title'] );
	
		$instance['postcount'] = strip_tags( $new_instance['postcount'] );
                	$instance['categories'] = strip_tags( $new_instance['categories'] );
                $instance['viewall'] = strip_tags( $new_instance['viewall'] );
		

		/* No need to strip tags for.. */

		return $instance;
	}
	
	/* ---------------------------- */
	/* ------- Widget Settings ------- */
	/* ---------------------------- */
	
	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	 
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
                'title' => 'LATEST NEWS',
'show_option_all' => 'All',
		'postcount' => '5',
				);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

	<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'localization') ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

            
		<!-- Postcount: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'postcount' ); ?>"><?php _e('Number of posts', 'localization') ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'postcount' ); ?>" name="<?php echo $this->get_field_name( 'postcount' ); ?>" value="<?php echo $instance['postcount']; ?>" />
		</p>
                
                  <p>
			<label for="<?php echo $this->get_field_id('categories'); ?>">
					<?php _e('Category:', 'ototw'); ?>
					<br />
			</label>
			
			<?php wp_dropdown_categories( 
				array( 
					'name' => $this->get_field_name("categories"), 
					'selected' => $instance["categories"], 
                                        'show_option_all' => 'All',
                                    	'hide_if_empty' => 1
				) 
			); ?>
			
		</p>
                
                <!-- Postcount: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'viewall' ); ?>"><?php _e('"View All" button URL', 'localization') ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'viewall' ); ?>" name="<?php echo $this->get_field_name( 'viewall' ); ?>" value="<?php echo $instance['viewall']; ?>" />
		</p>
		
		<!-- Tweettext: Text Input -->
				
	<?php
	}
}
?>