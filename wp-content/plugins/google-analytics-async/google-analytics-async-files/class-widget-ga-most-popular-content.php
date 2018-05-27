<?php
// Widget for Subscribe
class Google_Analytics_Async_Frontend_Widget extends WP_Widget {
    //constructor
    function __construct() {
        global $google_analytics_async_dashboard;

        $this->text_domain = $google_analytics_async_dashboard->text_domain;

        $widget_ops = array( 'description' => __( 'Your site\'s most popular posts', $this->text_domain) );
        parent::__construct( false, __( 'Most popular posts', $this->text_domain ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        global $google_analytics_async_dashboard;

        extract( $args );

        $title = isset($instance['title']) ? apply_filters( 'widget_title', $instance['title'] ) : '';
        $number = (is_numeric($instance['number']) ) ? $instance['number'] : 10;

        ob_start();
        $google_analytics_async_dashboard->set_up_ga_data();
        $google_analytics_async_dashboard->google_analytics_frontend_widget(array('number' => $number));
        $stats = ob_get_clean();

        global $google_analytics_frontend_widget_count;

        if(!isset($google_analytics_frontend_widget_count) || $google_analytics_frontend_widget_count) {
            if(!isset($google_analytics_frontend_widget_count)) {
                wp_enqueue_script('google_analytics_async_frontend', $google_analytics_async_dashboard->plugin_url . 'google-analytics-async-files/ga-async-frontend.js', array('jquery'), 340);
                $google_analytics_async_dashboard->setup_script_variables('google_analytics_async_frontend');
            }

            if(isset($before_widget))
                echo $before_widget;

            if(isset($before_title))
                echo $before_title;
            if ( $title )
                echo $title;
            if(isset($after_title))
                echo $after_title;

            echo $stats;

            if(isset($after_widget))
                echo $after_widget;
        }
    }

    /** @see WP_Widget::update */
    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title']  = strip_tags($new_instance['title']);
        $instance['number']  = strip_tags($new_instance['number']);
        $instance['number'] = $instance['number'] > 10 ? 10 : ($instance['number'] < 1 ? 1 : $instance['number']);

        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : __( 'Most popular posts', $this->text_domain);
        $number = (isset( $instance['number'] ) && is_numeric($instance['number'])) ? esc_attr( $instance['number'] ) : 10;

        ?>
        <div class="msreader_widget_recent_posts">
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', $this->text_domain ) ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Maximum number of posts to show:', $this->text_domain ) ?></label>
                <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" min="1" max="10" value="<?php echo $number; ?>" size="3">
            </p>
        </div>
        <?php
    }
}