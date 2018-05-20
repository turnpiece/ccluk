<?php
/**
 * Creates the Panel of share buttons based on options and settings.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 */
class SWP_Buttons_Panel {
	/**
	 * Options
	 *
	 * We're using a local property to clone in the global $swp_user_options array. As a local options
	 * we and other developers accessing this object can use getters and setters to change any options
	 * possibly imaginable without affecting the global options.
	 *
	 * @var array
	 *
	 */
    public $options = array();


	/**
	 * The Post ID
	 *
	 * @var int
	 *
	 */
	public $post_id;


	/**
	 * The location of the buttons in relation to the content.
	 *
	 * @var string above | below | both | none
	 *
	 */
	public $location = 'above';


	/**
	 * Arguments
	 *
	 * A temporary property used to store and access any arguments passed into
	 * the constructor. These will then be processed into the other properties
	 * as possible.
	 *
	 * @var array $args;
	 *
	 */
	public $args = [];


	/**
	 * The Content
	 *
	 * The WordPress content to which we are going to append the HTML of these buttons.
	 *
	 * @var string $content;
	 *
	 */
	public $content = '';


	/**
     * The fully qualified HTML for the Buttons Panel.
     *
     * @var string $html;
     */
    public $html = '';


	/**
     * The array of active buttons for $this Social Panel.
     *
     * @var array $active_buttons;
     */
    public $active_buttons = [];


	/**
     * The sum of share counts across active networks.
     *
     * @var integer $total_shares;
     */
    public $total_shares = 0;


	/**
	 * The Construct Method
	 *
	 * @param optional array $args The arguments passed in via shortcode.
	 *
	 */
    public function __construct( $args = array(), $shortcode = false ) {
        global $swp_social_networks, $post;
        $this->networks = $swp_social_networks;
		$this->args = $args;
        $this->content = isset( $args['content'] ) ? $args['content'] : '';
        //* Access the $post once while we have it. Values may be overwritten.
        $this->post_data = [
            'ID'           => $post->ID,
            'post_type'    => $post->post_type,
            'permalink'    => get_the_permalink( $post->ID ),
            'post_title'   => $post->post_title,
            'post_status'  => $post->post_status,
            'post_content' => $post->post_content
        ];
        $this->is_shortcode = $shortcode;
        $this->localize_options( $args );
	    $this->establish_post_id();
		$this->shares = get_social_warfare_shares( $this->post_data['ID'] );
	    $this->establish_location();
		$this->establish_float_location();
		$this->establish_permalink();
        $this->establish_active_buttons();

        if ( true === _swp_is_debug( 'show_button_panel_data' ) ) :
                echo "<pre>";
                var_dump($this);
                echo "</pre>";
        endif;
    }
	/**
	 * Localize the global options
	 *
	 * The goal here is to move the global $swp_options array into a local property so that the options
	 * for this specific instantiation of the buttons panel can have the options manipulated prior to
	 * rendering the HTML for the panel. We can do this by using getters and setters or by passing in
	 * arguments.
	 *
	 * @since  3.0.0 | 09 APR 2018 | Created
	 * @param  array  $args Arguments that can be used to change the options of the buttons panel.
	 * @return none
	 * @access private
	 *
	 */
	private function localize_options() {
		global $swp_user_options;
		$this->options = array_merge( $swp_user_options, $this->args );
        $this->post_data['options'] = $swp_user_options;
	}
	/**
	 * Set an option
	 *
	 * This method allows you to change one of the options for the buttons panel.
	 *
	 * @since  3.0.0 | 09 APR 2018 | Created
	 * @param  string $option The key of the option to be set.
	 * @param  mixed  $value  The value to which we will set that option.
	 * @return object $this   Allows for method chaining.
	 * @access public
	 *
	 */
	public function set_option( $option , $value ) {
		$this->options[$this->options] = $value;
		return $this;
	}
	/**
	 * Set multiple options
	 *
	 * This method allows you to change multiple options for the buttons panel.
	 *
	 * @since  3.0.0 | 09 APR 2018 | Created
	 * @param  array  $this->options An array of options to be merged into the existing options.
	 * @return object $this   Allows for method chaining.
	 * @access public
	 *
	 */
	public function set_options( $options ) {
		array_merge( $this->options , $options );
		return $this;
	}
	/**
	 * Set the post ID for this buttons panel.
	 *
	 * We want to use the global post ID for whichever post is being looped
	 * through unless the post ID has been passed in as an argument.
	 *
	 * @since  3.0.0 | 09 APR 2018 | Created
	 * @param  array $args The array of args passed in.
	 * @return none
	 * @access public
	 *
	 */
	public function establish_post_id() {
		// Legacy support.
		if ( isset( $this->args['postID'] ) ) :
			$this->post_data['ID'] = $this->args['postID'];
        endif;
		// Current argument.
		if ( isset( $this->args['post_id'] ) ) :
			$this->post_data['ID'] = $this->args['post_id'];
        endif;
	}
	/**
	 * Establish the post content
	 *
	 * Take the content passed in via the $args and move it into a
	 * local property.
	 *
	 * @since  3.0.0 | 18 APR 2018 | Created
	 * @param  none
	 * @return none Everything is stored in a local property.
	 *
	 */
    public function establish_post_content() {
        if( isset( $this->args['content'] ) ):
			$this->content = $args['content'];
		endif;
    }
	/**
	 * Establish Location
	 *
	 * A method to handle figuring out where in the content these buttons are
	 * supposed to appear. It has to check the global options, the options set
	 * on the post, and be able to tell if this is being called without any
	 * content to which to append.
	 *
	 * @since  3.0.0 | 10 APR 2018 | Created
	 * @since  3.0.5 | 11 MAY 2018 | Added returns to the method won't keep processing.
	 * @since  3.0.7 | 15 MAY 2018 | Added conditionals to ensure $preset_location isn't an array.
	 * @param  none
	 * @return none All values are stored in local properties.
	 * @access public
	 *
	 */
	public function establish_location() {
        //* Establish a default.
        $this->location = 'none';
		if(empty($this->content)):
			$this->location = 'above';
			return;
		endif;
		/**
		 * Location from the Post Options
		 *
		 * If the location was specified on the post options, we'll make sure
		 * to use this instead of the global options.
		 *
		 */
		$preset_location = get_post_meta( $this->post_data['ID'], 'swp_post_location', true );
		if( is_array($preset_location) ) { $preset_location = $preset_location[0]; }
		// If the location is set in the post options, use that.
		if ( !empty( $preset_location ) && 'default' != $preset_location && !is_array( $preset_location ) ) {
			$this->location = $preset_location;
			return;
		};
		/**
		 * Global Location Settings
		 *
		 * Decide which post type we're on and pull the location setting
		 * for that type from the global options.
		 *
		 */
		// If we are on the home page
		if( is_front_page() ):
            $home = $this->options['location_home'];
			$this->location = isset( $home ) ? $home : 'none';
			return;
        endif;
		// If we are on a singular page
		if ( is_singular() && !is_front_page() ) :
            $location = $this->options[ 'location_' . $this->post_data['post_type'] ];
            if ( isset( $location ) ) :
                $this->location = $location;
				return;
            endif;
        endif;
        if ( is_archive() || is_home() ) :
            $this->location = $this->options['location_archive_categories'];
        endif;
	}


    /**
     * Takes a display name and returns the snake_cased key of that name.
     *
     * This is used to convert a network's name, such as Google Plus,
     * to the database-friendly key of google_plus.
     *
     * @since  3.0.0 | 18 APR 2018 | Created
     * @param  string $name The string to convert.
     * @return string The converted string.
     *
     */
    public function display_name_to_key( $string ) {
        return preg_replace( '/[\s]+/', '_', strtolower( trim ( $string ) ) );
    }


    protected function establish_float_location() {
        // Set the options for the horizontal floating bar
        // $spec_float_where = get_post_meta( $this->post_data['ID'] , 'nc_float_location' , true );
        //
        // if ( isset( $this->args['floating_panel'] ) && $this->args['floating_panel'] == 'ignore' ) :
        //     $floatOption = 'float_ignore';
        // elseif ( $spec_float_where == 'off' && $this->options['button_alignment'] != 'float_ignore' ) :
        //         $floatOption = 'floatNone';
        // elseif ( $this->options['floating_panel'] && is_singular() && $this->options[ 'float_location_' . $this->post_data['post_type'] ] == 'on' ) :
        //     $floatOption = 'floating_panel' . ucfirst( $this->options['float_location'] );
        // else :
        //     $floatOption = 'floatNone';
        // endif;
    }


    protected function establish_permalink() {
        $this->permalink = get_permalink( $this->post_data['ID'] );
    }


    //* When we have known incompatability with other themes/plugins,
    //* we can put those checks in here.
    /**
     * Checks for known conflicts with other plugins and themes.
     *
     * If there is a fatal conflict, returns true and exits printing.
     * If there are other conflicts, they are silently handled and can still
     * print.
     *
     * @return bool $conflict True iff the conflict is fatal.
     */
    protected function has_plugin_conflict() {
        $conflict = false;
		// Disable subtitles plugin to prevent it from injecting subtitles
		// into our share titles.
		if ( is_plugin_active( 'subtitles/subtitles.php' ) && class_exists( 'Subtitles' ) ) :
			remove_filter( 'the_title', array( Subtitles::getinstance(), 'the_subtitle' ), 10, 2 );
		endif;
        //* Disable on BuddyPress pages.
        if ( function_exists( 'is_buddypress' ) && is_buddypress() ) :
            $conflict = true;
        endif;
        return $conflict;
    }


    /**
     * Tells you true/false if the buttons should print on this page.
     *
     * Each variable is a boolean value. For the buttons to eligible for printing,
     * each of the variables must evaluate to true.
     *
     * $user_settings: Options editable by the Admin user.
     * $desired_conditions: WordPress conditions we require for the buttons.
     * $undesired_conditions: WordPress pages where we do not display the buttons.
     *
     *
     * @return Boolean True if the buttons are okay to print, else false.
     */
    public function should_print() {
        $user_settings = $this->location !== 'none';

        $desired_conditions = is_main_query() && in_the_loop() && get_post_status( $this->post_data['ID'] ) === 'publish';

        $undesired_conditions = !is_admin() && !is_feed() && !is_search() && !is_attachment();
        return $user_settings && $desired_conditions && $undesired_conditions;
    }


	/**
	 * The method that renderes the button panel HTML.
	 *
	 * @since  3.0.0 | 25 APR 2018 | Created
	 * @since  3.0.3 | 09 MAY 2018 | Switched the button locations to use the
	 *                               location methods instead of the raw options value.
	 * @since  3.0.6 | 15 MAY 2018 | Uses $this->option() method to prevent undefined index error.
	 * @param  boolean $echo Echo's the content or returns it if false.
	 * @return string        The string of HTML.
	 *
	 */
    public function render_HTML( $echo = false ) {

		if ( ! $this->should_print() ) :
			return $this->content;
		endif;

        $total_shares_html = $this->render_total_shares_html();
        $buttons = $this->render_buttons_html();
		// Create the HTML Buttons panel wrapper
        $container = '<div class="swp_social_panel swp_' . $this->option('button_shape') .
            ' swp_default_' . $this->option('default_colors') .
            ' swp_individual_' . $this->option('single_colors') .
            ' swp_other_' . $this->option('hover_colors') .
            ' scale-' . $this->option('button_size') * 100 .
            ' scale-' . $this->option('button_alignment') .
            '" data-position="' . $this->option('location_post') .
            '" data-float="' . $this->get_float_location() .
            '" data-float-mobile="' . $this->get_mobile_float_location() .
            '" data-count="' . $this->total_shares .
            '" data-floatcolor="' . $this->option('float_background_color') . '
            ">';
            //* This should be inserted via addon, not here.
            //'" data-emphasize="'.$this->option('emphasize_icons').'
        if ($this->option('totals_alignment') === 'totals_left') :
            $buttons = $total_shares_html . $buttons;
        else:
            $buttons .= $total_shares_html;
        endif;
        $html = $container . $buttons . '</div>';
        $this->html = $html;
        if ( $echo ) :
			if( true == _swp_is_debug('buttons_output')):
				echo 'Echoing, not returning. In SWP_Buttons_Panel on line '.__LINE__;
			endif;
            echo $html;
        endif;
        return $html;
    }


	/**
	 * A function to avoid getting undefined index notices.
	 *
	 * @since  3.0.5 | 10 MAY 2018 | Created
	 * @param  string $key The name of the option.
	 * @return mixed       The value of that option.
	 *
	 */
	private function option($key) {

		$defaults = array();
		$defaults = apply_filters('swp_options_page_defaults' , $defaults );

		if( isset( $this->options[$key] ) ):
			return $this->options[$key];
		elseif( isset( $defaults[$key] ) ):
			return $defaults[$key];
		else:
			return false;
		endif;

	}


	/**
	 * A Method to determine the location of the floating buttons
	 *
	 * This method was created because we can't just use the option as it is set
	 * in the options page. Instead, we must first check that we are on a single.php
	 * page and second we must check that the floating buttons toggle is turned on.
	 * Then and only then will we check the actual floating location and return it.
	 *
	 * @since  3.0.0 | 09 MAY 2018 | Created
	 * @since  3.0.4 | 09 MAY 2018 | Added check for the global post type on/off toggle.
	 * @param  none
	 * @return string A string containing the float bar location.
	 *
	 */
	public function get_float_location() {
		if( is_home() || is_front_page() ):
			return 'none';
		elseif( is_single() && true == $this->option('floating_panel') && 'on' == $this->option('float_location_' . $this->post_data['post_type'] ) ):
			return $this->option('float_location');
		else:
			return 'none';
		endif;
	}


	/**
	 * A Method to determine the location of the floating buttons on mobile devices
	 *
	 * This method was created because we can't just use the option as it is set
	 * in the options page. Instead, we must first check that we are on a single.php
	 * page and second we must check that the floating buttons toggle is turned on.
	 * Then and only then will we check the actual floating location and return it.
	 *
	 * @since  3.0.0 | 09 MAY 2018 | Created
	 * @since  3.0.4 | 09 MAY 2018 | Added check for the global post type on/off toggle.
	 * @param  none
	 * @return string A string containing the float bar location.
	 *
	 */
	public function get_mobile_float_location() {
		if( is_home() || is_front_page() ):
			return 'none';
		elseif( is_single() && true == $this->option('floating_panel') && 'on' == $this->option('float_location_' . $this->post_data['post_type'] ) ):
			return $this->option('float_mobile');
		else:
			return 'none';
		endif;
	}


    public function render_floating_HTML( $echo = true ) {
        //* BEGIN Old boilerplate that needs to be refactored.

		if( is_singular() && 'none' !== $this->get_float_location() ):

	        $class = "";
	        $size = $this->option('float_size') * 100;
	        $side = $this->option('float_location');
	        $max_buttons = $this->option( 'float_button_count' );
			if( false == $max_buttons || 0 == $max_buttons ):
				$max_buttons = 5;
			endif;
	        // Acquire the social stats from the networks
	        if ( isset( $array['url'] ) ) :
	            $buttonsArray['url'] = $array['url'];
	        else :
	            $buttonsArray['url'] = get_permalink( $this->post_id );
	        endif;
	        if ( 'none' != $this->get_float_location() ) :
	            $float_location =  $this->option('float_location');
	            $class = "swp_float_" . $this->option('float_location');
	        else :
	            $float_location = 'ignore';
	        endif;
	        if ( $this->options['float_style_source'] == true ) :
	            $this->options['float_default_colors'] = $this->option('default_colors');
	            $this->options['float_single_colors'] = $this->option('single_colors');
	            $this->options['float_hover_colors'] = $this->option('hover_colors');
	        endif;
	        // *Get the vertical position
	        if ($this->option('float_alignment')  ) :
	            $class .= " swp_side_" . $this->option('float_alignment');
	        endif;
	        // *Set button size
	        if ( isset($this->options['float_size']) ) :
	            $position = $this->option('float_alignment');
	            $class .= " scale-${size} float-position-${position}-${side}";
	        endif;
	        //* END old boilerplate.
	        $share_counts = $this->render_total_shares_HTML();

	        $buttons = $this->render_buttons_HTML( (int) $max_buttons );
	        $container = '<div class="swp_social_panelSide swp_social_panel swp_'. $this->option('float_button_shape') .
	            ' swp_default_' . $this->option('float_default_colors') .
	            ' swp_individual_' . $this->option('float_single_colors') .
	            ' swp_other_' . $this->option('float_hover_colors') . '
	            ' . $this->option('transition') . '
	            ' . $class . '
	            ' . '" data-position="' . $this->option('location_post') .
	            ' scale-' . $this->option('float_size') * 100 .
	            '" data-float="' . $float_location .
	            '" data-count="' . count($this->networks) .
	            '" data-floatColor="' . $this->option('float_background_color') .
	            '" data-screen-width="' . $this->option('float_screen_width') .
	            '" data-transition="' . $this->option('transition') .
	            '" data-float-mobile="'.$this->option('float_mobile').'">';
	        if ($this->option('totals_alignment') === 'totals_left') :
	            $buttons = $share_counts . $buttons;
	        else:
	            $buttons .= $share_counts;
	        endif;
	        $html = $container . $buttons . '</div>';
	        $this->html = $html;
	        if ( $echo ) :
	            echo $html;
	        endif;
	        return $html;
		endif;
    }


	/**
	 * A method to establish the active buttons for this panel.
	 *
	 * First it will check to see if user arguments have been passed in. If not, it will
	 * check to see if they are set to manual or dynamic sorting. If manual, we will use
	 * the buttons in the order they were stored in the options array (they were set in
	 * this order on the options page.) If dynamic, we will look at the share counts and
	 * order them with the largest share counts appearing first.
	 *
	 * The results will be stored as an ordered array of network objects in the
	 * $this->networks property.
	 *
	 * @since  3.0.0 | 04 MAY 2018 | Created
	 * @param  none
	 * @return object $this Allows for method chaining.
	 *
	 */
    public function establish_active_buttons() {
        $network_objects = [];

        //* Specified buttons take precedence to global options.
        if ( isset( $this->args['buttons'] ) ) :
            $this->args['buttons'] = explode( ',', $this->args['buttons'] );

            foreach ( $this->args['buttons'] as $counts_key ) {
                $network_key = $this->display_name_to_key( $counts_key );
                foreach( $this->networks as $key => $network ):
                    if( $network_key === $key ):
                        $network_objects[] = $network;
                    endif;
                endforeach;
            }

        //* Use global button settings.
        else :

			// Order manually using the user's specified order.
            if ( $this->options['order_of_icons_method'] === 'manual' ) :
                $order = $this->options['order_of_icons'];

			// Order them dynamically according to share counts.
            else :
                $order = $this->get_dynamic_buttons_order();
            endif;

			$network_objects = $this->order_network_objects($order);
        endif;

        $this->networks = $network_objects;
        return $this;
    }


	/**
	 * A method to order the networks dynamically.
	 *
	 * @since  3.0.0 | 04 MAY 2018 | Created
	 * @param  none
	 * @return object An ordered array containing the social network objects.
	 */
    protected function get_dynamic_buttons_order() {
		$order = array();
		if( !empty( $this->shares ) && is_array( $this->shares ) ):
			arsort( $this->shares );
			foreach( $this->shares as $key => $value ):
				if($key !== 'total_shares'):
					$order[$key] = $key;
				endif;
			endforeach;
			$this->options['order_of_icons'] = $order;
		else:
			$order = $this->options['order_of_icons'];
		endif;
		return $order;
    }


	/**
	 * A method to shuffle the array of network objects.
	 *
	 * @since  3.0.0 | 04 MAY 2018 | Created
	 * @param  array $order An ordered array of network keys.
	 * @return array        An ordered array of network objects.
	 *
	 */
	public function order_network_objects( $order ) {
		$network_objects = array();
		foreach( $order as $network_key ) {
            foreach( $this->networks as $key => $network ) :
                if ( $key === $network_key ) :
                    $network_objects[$key] = $network;
                endif;
            endforeach;
        }
		return $network_objects;
	}


    public function render_buttons_HTML( $max_count = null) {
        $html = '';
        $count = 0;
        foreach( $this->networks as $key => $network ) {
            if ( isset( $max_count) && $count === $max_count) :
                return $html;
            endif;
			// Pass in some context for this specific panel of buttons
			$context['shares'] = $this->shares;
			$context['options'] = $this->options;
			$context['post_data'] = $this->post_data;
            $html .= $network->render_HTML( $context );
            $count++;
        }
        return $html;
    }


    /**
     * The Total Shares Count
     *
     * If share counts are active, renders the Total Share Counts HTML.
     *
     * @since  3.0.0 | 18 APR 2018 | Created
     * @param  none
     * @return string $html The fully qualified HTML to display share counts.
     *
     */
    public function render_total_shares_html() {
        $buttons = isset( $this->args['buttons'] ) ? $this->args['buttons'] : [];

        if ( empty( $this->shares['total_shares']) || $this->shares['total_shares'] < $this->option('minimum_shares') || false == $this->option('total_shares')  || $this->is_shortcode && !in_array( 'total', $buttons ) ) {
            return '';
        }
        $html = '<div class="nc_tweetContainer total_shares total_sharesalt" >';
            $html .= '<span class="swp_count ">' . swp_kilomega( $this->shares['total_shares'] ) . ' <span class="swp_label">' . __( 'Shares','social-warfare' ) . '</span></span>';
        $html .= '</div>';
        return $html;
    }


    //* TODO: This has not been refactored.
    protected function handle_timestamp() {
        if ( swp_is_cache_fresh( $this->post_data['ID'] ) == false  && isset($this->options['cache_method']) && 'legacy' === $this->options['cache_method'] ) :
			delete_post_meta( $this->post_data['ID'],'swp_cache_timestamp' );
			update_post_meta( $this->post_data['ID'],'swp_cache_timestamp',floor( ((date( 'U' ) / 60) / 60) ) );
		endif;
    }


    /**
     * Handles whether to echo the HTML or return it as a string.
     *
     * @return mixed null if set to echo, else a string of HTML.
     * @since  3.0.6 | 14 MAY 2018 | Removed the swp-content-locator div.
     *
     */
    public function do_print() {

        $this->render_HTML();

        //* Add the Panel markup based on the location.
        if ( $this->location === 'both' ) :
            $content = $this->html . $this->content . $this->html;
        elseif ( $this->location === 'above' ) :
            $content = $this->html . $this->content;
        else :
            $content = $this->content . $this->html;
        endif;

        $this->content = $content;

        if ( isset( $this->args['echo']) && true === $this->args['echo'] ) {
			if( true == _swp_is_debug('buttons_output')):
				echo 'Echoing, not returning. In SWP_Buttons_Panel on line '.__LINE__;
			endif;
            echo $this->content;
        }

        return $this->content;
    }

    /**
     * Runs checks before ordering a set of buttons.
     *
     * @param  string $content The WordPress content, if passed in.
     * @return function @see $this->do_print
     * @since  3.0.6 | 14 MAY 2018 | Removed the swp-content-locator div.
     *
     */
    public function the_buttons( $content = null ) {
        if ( empty( $this->content ) ) :
            return $this->do_print();
        endif;

        if ( ! $this->should_print() ) :
            return $this->args['content'];
        endif;

        if ( null !== $content && gettype( $content ) === 'string' ) :
            $this->args['content'] = $content;
        endif;

        if ( $this->has_plugin_conflict() ) {
            return;
        }

		$this->handle_timestamp();

        return $this->do_print();
    }
}
