<?php
/*
 * Plugin Name: DD Causes Widget
 * Plugin URI: http://themeforest.net/user/DDStudios/portfolio
 * Description: A widget that displays recent causes
 * Version: 1.0
 * Author: Dany Duchaine
 * Author URI: http://themeforest.net/user/DDStudios/
 */

/*
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'dd_causes_widgets' );

/*
 * Register widget.
 */
function dd_causes_widgets() {
	register_widget( 'DD_Causes_Widget' );
}

/*
 * Widget class.
 */
class dd_causes_widget extends WP_Widget {

	/* ---------------------------- */
	/* -------- Widget setup -------- */
	/* ---------------------------- */
	
	function DD_Causes_Widget() {
	
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'dd_causes_widget', 'description' => __('A widget that displays your latest causes.', 'localization') );

		 /* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'dd_causes_widget' );

		/* Create the widget. */
		$this->WP_Widget( 'dd_causes_widget', __('DD Causes Widget','localization'), $widget_ops, $control_ops );
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
                    
                <?php if (($categories  == '0') || ($categories  === '')) { ?>
                                                   
                       <?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$arguments = array(
    'post_type' => 'post_causes',
    'post_status' => 'publish',
    'paged' => $paged,
    'showposts' => $postcount
);


$causes_query = new WP_Query($arguments);

dd_set_query($causes_query);

?>
                                                   
                                                               <?php } else { ?>
                                                   
                                                               <?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;



          $arguments = array(
                        'posts_per_page' => $postcount,
               'post_status' => 'publish',
    'paged' => $paged,
                        'tax_query' => array(
                                array(
                                        'taxonomy' => 'causes_item_types',
                                        'field' => 'id',
                                        'terms' => $categories
                                )
                        )
                );
          
$causes_query = new WP_Query($arguments);

dd_set_query($causes_query);

?>               
                                                               <?php } ?>   
                     
                       <?php if ($causes_query->have_posts()) : while ($causes_query->have_posts()) : $causes_query->the_post(); ?>
                      
                         
                    
                    
                    
                       
                            
                              <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);


?>
                            
                         
                             <?php if( $bigimg) { ?>
                         
                           <li <?php post_class('dd_causes_post causesWidgetWImg'); ?>>
                        
                               <div class="widgetWrapper">
                                   
                                   <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                       
                        <ul>
                            
                            <li><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></li> 
                            <li><a href="<?php the_permalink(); ?>">MORE DETAILS</a></li> 
                                
                        </ul>
                                   
                               </div>
                        
                    </li>
                    
                                  <?php } else { ?>
                    
                       <li <?php post_class('dd_causes_post'); ?>>
                       
                        <ul>
                            
                            <li><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></li> 
                            <li><a href="<?php the_permalink(); ?>">MORE DETAILS</a></li> 
                                
                        </ul>
                        
                    </li>
                         
                                  <?php } ?>
                      
                                            
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
                'title' => 'OUR CAUSES',
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
					'taxonomy'  => 'causes_item_types',
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