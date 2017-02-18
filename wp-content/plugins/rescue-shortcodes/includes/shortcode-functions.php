<?php

/**
 * Allow shortcodes in widgets
 */
add_filter('widget_text', 'do_shortcode');

/**
 * Fix Shortcodes
 */
if( !function_exists('rescue_fix_shortcodes') ) {
	function rescue_fix_shortcodes($content){
		$array = array (
			'<p>['		=> '[',
			']</p>'		=> ']',
			']<br />'	=> ']'
		);
		$content = strtr($content, $array);
		return $content;
	}
	add_filter('the_content', 'rescue_fix_shortcodes');
}

/**
 * Clear Floats
 */
if( !function_exists('rescue_clear_floats_shortcode') ) {
	function rescue_clear_floats_shortcode() {
	   return '<div class="rescue-clear-floats"></div>';
	}
	add_shortcode( 'rescue_clear_floats', 'rescue_clear_floats_shortcode' );
}

/**
 * Spacing
 */
if( !function_exists('rescue_spacing_shortcode') ) {
	function rescue_spacing_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'size'	=> '30px',
			'class'	=> '',
		  ),
		  $atts ) );
	 return '<hr class="rescue-spacing '. $class .'" style="height: '. $size .'" />';
	}
	add_shortcode( 'rescue_spacing', 'rescue_spacing_shortcode' );
}

/**
 * Highlights
 */
if ( !function_exists( 'rescue_highlight_shortcode' ) ) {
	function rescue_highlight_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color'			=> 'yellow',
			'class'			=> '',
			'visibility'	=> 'all',
		  ),
		  $atts ) );
		  return '<span class="rescue-highlight rescue-highlight-'. $color .' '. $class .' rescue-'. $visibility .'">' . do_shortcode( $content ) . '</span>';

	}
	add_shortcode('rescue_highlight', 'rescue_highlight_shortcode');
}

/**
 * Buttons
 */
if( !function_exists('rescue_button_shortcode') ) {
	function rescue_button_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color'				=> '',
			'colorhex'			=> '',
			'colorhexhover'		=> '',
			'url'				=> 'https://rescuethemes.com',
			'title'				=> 'Visit Site',
			'target'			=> 'self',
			'rel'				=> '',
			'border_radius'		=> '',
			'class'				=> '',
			'icon_left'			=> '',
			'icon_right'		=> '',
			'visibility'		=> 'all',
		), $atts ) );

		$rel = ( $rel ) ? 'rel="'.$rel.'"' : NULL;

		$button = NULL;

		$button .= '<a style="background: ' . $colorhex . ';border-radius: ' . $border_radius . ' " href="' . $url . '" class="rescue-button ' . $color . ' '. $class .' rescue-'. $visibility .'" target="_'.$target.'" title="'. $title .'" '. $rel .'>';
			$button .= '<span class="rescue-button-inner">';
				if ( $icon_left ) $button .= '<span class="rescue-button-icon-left icon-'. $icon_left .'"></span>';
				$button .= $content;
				if ( $icon_right ) $button .= '<span class="rescue-button-icon-right icon-'. $icon_right .'"></span>';
			$button .= '</span>';

		$button .= '</a>';

		return $button;
	}
	add_shortcode('rescue_button', 'rescue_button_shortcode');
}

/**
 * Boxes
 */
if( !function_exists('rescue_box_shortcode') ) {
	function rescue_box_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color'				=> 'gray',
			'float'				=> 'center',
			'text_align'		=> 'left',
			'width'				=> '100%',
			'margin_top'		=> '',
			'margin_bottom'		=> '',
			'class'				=> '',
			'visibility'		=> 'all',
		  ), $atts ) );

			$style_attr = '';
			if( $margin_bottom ) {
				$style_attr .= 'margin-bottom: '. $margin_bottom .';';
			}
			if ( $margin_top ) {
				$style_attr .= 'margin-top: '. $margin_top .';';
			}

		  $alert_content = '';
		  $alert_content .= '<div class="rescue-box ' . $color . ' '.$float.' '. $class .' rescue-'. $visibility .'" style="text-align:'. $text_align .'; width:'. $width .';'. $style_attr .'">';
		  $alert_content .= ' '. do_shortcode($content) .'</div>';
		  return $alert_content;
	}
	add_shortcode('rescue_box', 'rescue_box_shortcode');
}

/**
 * Columns
 */
if( !function_exists('rescue_column_shortcode') ) {
	function rescue_column_shortcode( $atts, $content = null ){
		extract( shortcode_atts( array(
			'size'			=> 'one-third',
			'position'		=>'first',
			'class'			=> '',
			'visibility'	=> 'all',
		  ), $atts ) );
		  return '<div class="rescue-column rescue-' . $size . ' rescue-column-'.$position.' '. $class .' rescue-'. $visibility .'">' . do_shortcode($content) . '</div>';
	}
	add_shortcode('rescue_column', 'rescue_column_shortcode');
}

/**
 * Toggle
 */
if( !function_exists('rescue_toggle_shortcode') ) {
	function rescue_toggle_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'title'			=> 'Toggle Title',
			'class'			=> '',
			'visibility'	=> 'all',
		), $atts ) );

		// Enque scripts
		wp_enqueue_script('rescue_toggle');

		// Display the Toggle
		return '<div class="rescue-toggle '. $class .' rescue-'. $visibility .'"><h3 class="rescue-toggle-trigger">'. $title .'</h3><div class="rescue-toggle-container">' . do_shortcode($content) . '</div></div>';
	}
	add_shortcode('rescue_toggle', 'rescue_toggle_shortcode');
}

/**
 * Tab Group
 */
if (!function_exists('rescue_tabgroup_shortcode')) {
	function rescue_tabgroup_shortcode( $atts, $content = null ) {

		//Enque scripts
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('rescue_tabs');

		// Display Tabs
		$defaults = array();
		extract( shortcode_atts( $defaults, $atts ) );
		preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );
		$tab_titles = array();
		if( isset($matches[1]) ){ $tab_titles = $matches[1]; }
		$output = '';
		if( count($tab_titles) ){
		    $output .= '<div id="rescue-tab-'. rand(1, 100) .'" class="rescue-tabs">';
			$output .= '<ul class="ui-tabs-nav rescue-clearfix">';
			foreach( $tab_titles as $tab ){
				$output .= '<li><a href="#rescue-tab-'. sanitize_title( $tab[0] ) .'">' . $tab[0] . '</a></li>';
			}
		    $output .= '</ul>';
		    $output .= do_shortcode( $content );
		    $output .= '</div>';
		} else {
			$output .= do_shortcode( $content );
		}
		return $output;
	}
	add_shortcode( 'rescue_tabgroup', 'rescue_tabgroup_shortcode' );
}
if (!function_exists('rescue_tab_shortcode')) {
	function rescue_tab_shortcode( $atts, $content = null ) {
		$defaults = array(
			'title'			=> 'Tab',
			'class'			=> '',
			'visibility'	=> 'all',
		);
		extract( shortcode_atts( $defaults, $atts ) );
		return '<div id="rescue-tab-'. sanitize_title( $title ) .'" class="tab-content '. $class .' rescue-'. $visibility .'"><p>'. do_shortcode( $content ) .'</p></div>';
	}
	add_shortcode( 'rescue_tab', 'rescue_tab_shortcode' );
}

/**
 * Donation Tab Group
 */
if (!function_exists('rescue_donation_tabgroup_shortcode')) {
	function rescue_donation_tabgroup_shortcode( $atts, $content = null ) {

		//Enque scripts
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('rescue_donation_tabs');

		$defaults = array(
			'group_title' => '',
		);
		extract( shortcode_atts( $defaults, $atts ) );


		preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );
		$tab_titles = array();
		if( isset($matches[1]) ){ $tab_titles = $matches[1]; }
		$output = '';
		if( count($tab_titles) ){
		    $output .= '<div id="rescue-tab-'. rand(1, 100) .'" class="rescue-donation-tabs tabs-bottom">';
			$output .= '<ul class="ui-tabs-nav rescue-clearfix">';
			foreach( $tab_titles as $tab ){

				$output .= '<li><a href="#rescue-tab-'. sanitize_title( $tab[0] ) .'"><span>' . $tab[0] . '</span></a></li>';
			}
		    $output .= '</ul>';
		    $output .= '<div class="rescue_donation_header"> '. $group_title .' </div>';
		    $output .= do_shortcode( $content );
		    $output .= '</div><!-- .rescue-donation-tabs .tabs-bottom -->';
		} else {
			$output .= do_shortcode( $content );
		}
		return $output;
	}
	add_shortcode( 'rescue_donation_tabgroup', 'rescue_donation_tabgroup_shortcode' );
}
if (!function_exists('rescue_donation_tab_shortcode')) {
	function rescue_donation_tab_shortcode( $atts, $content = null ) {
		$defaults = array(
			'title'			=> 'Tab',
			'class'			=> '',
			'visibility'	=> 'all',
		);
		extract( shortcode_atts( $defaults, $atts ) );
		return '<div id="rescue-tab-'. sanitize_title( $title ) .'" class="tab-content '. $class .' rescue-'. $visibility .'"><p>'. do_shortcode( $content ) .'</p></div>';
	}
	add_shortcode( 'rescue_donation_tab', 'rescue_donation_tab_shortcode' );
}

/**
 * Donation Progress
 */
if( !function_exists('rescue_progressbar_shortcode') ) {
	function rescue_progressbar_shortcode( $atts  ) {
		extract( shortcode_atts( array(
			'title'			=> '',
			'percentage'	=> '75',
			'color'			=> '#f1c40f',
			'class'			=> '',
			'show_percent'	=> 'true',
			'visibility'	=> 'all',
		), $atts ) );

		// Enque scripts
		wp_enqueue_script('rescue_progressbar');
		wp_enqueue_script('rescue_waypoints');

		// Display the accordion	';
		$output = '<div class="rescue-progressbar rescue-clearfix '. $class .' rescue-'. $visibility .'" data-percent="'. $percentage .'%">';
			if ( $title !== '' ) $output .= '<div class="rescue-progressbar-title" style="background: '. $color .';"><span>'. $title .'</span></div>';
			$output .= '<div class="rescue-progressbar-bar" style="background: '. $color .';"></div>';
			if ( $show_percent == 'true' ) {
				$output .= '<div class="rescue-progress-bar-percent">'.$percentage.'%</div>';
			}
		$output .= '</div>';

		return $output;
	}
	add_shortcode( 'rescue_progressbar', 'rescue_progressbar_shortcode' );
}

/**
 * Google Map
 */
if (! function_exists( 'rescue_shortcode_googlemaps' ) ) :
	function rescue_shortcode_googlemaps($atts, $content = null) {

		$atts = shortcode_atts(array(
				'title'			=> '',
				'location'		=> '',
				'width'			=> '',
				'height'		=> '300',
				'zoom'			=> 8,
				'align'			=> '',
				'class'			=> '',
				'visibility'	=> 'all',
				'key'			=> '',
		), $atts );

		// load scripts
		wp_enqueue_script('rescue_googlemap');
		
		$title = sanitize_text_field( $atts['title'] );
		$location = sanitize_text_field( $atts['location'] );
		$width = intval( $atts['width'] );
		$height = intval( $atts['height'] );
		$zoom = intval( $atts['zoom'] );
		$align = sanitize_text_field( $atts['align'] );
		$class = sanitize_text_field( $atts['class'] );
		$visibility = sanitize_text_field( $atts['visibility'] );
		$key = sanitize_text_field( $atts['key'] );
		
		// Load Google API key if provided. Google requires new site to use API
		$api_key = ! empty( $key ) ? '&key=' . $key : '';

		wp_enqueue_script('rescue_googlemap_api', 'https://maps.googleapis.com/maps/api/js?sensor=false' . $api_key , array('jquery'), '1.0', true );



		$output = '<div id="map_canvas_'.rand(1, 100).'" class="googlemap '. $class .' rescue-'. $visibility .'" style="height:'.$height.'px;width:100%">';
			$output .= (!empty($title)) ? '<input class="title" type="hidden" value="'.$title.'" />' : '';
			$output .= '<input class="location" type="hidden" value="'.$location.'" />';
			$output .= '<input class="zoom" type="hidden" value="'.$zoom.'" />';
			$output .= '<div class="map_canvas"></div>';
		$output .= '</div>';

		return $output;

	}
	add_shortcode("rescue_googlemap", "rescue_shortcode_googlemaps");
endif;

/**
 * Font Awesome Icons
 */
if (! function_exists( 'RescueFontAwesome' ) ) :
	function RescueFontAwesome($atts) {

	    extract(shortcode_atts(array(
	    'type'  	=> '',
	    'size' 		=> '',
	    'rotate' 	=> '',
	    'flip' 		=> '',
	    'pull' 		=> '',
	    'animated' 	=> '',
	    'color' 	=> '',

	    ), $atts));

		// load scripts
		wp_enqueue_style('font_awesome');

	    $type = ($type) ? 'fa-'.$type. '' : '';
	    $size = ($size) ? 'fa-'.$size. '' : '';
	    $rotate = ($rotate) ? 'fa-rotate-'.$rotate. '' : '';
	    $flip = ($flip) ? 'fa-flip-'.$flip. '' : '';
	    $pull = ($pull) ? 'pull-'.$pull. '' : '';
	    $animated = ($animated) ? 'fa-spin' : '';
	    $color = ($color) ? ''.$color. '' : '';

	    $theAwesomeFont = '<i style="color:'.$color.'" class="fa '.sanitize_html_class($type).' '.sanitize_html_class($size).' '.sanitize_html_class($rotate).' '.sanitize_html_class($flip).' '.sanitize_html_class($pull).' '.sanitize_html_class($animated).'"></i>';

	    return $theAwesomeFont;
	}

	add_shortcode('icon', 'RescueFontAwesome');
endif;

/**
 * Animation Effects
 */
if (! function_exists( 'rescue_animate_shortcode' ) ) :
	function rescue_animate_shortcode($atts, $content = null) {

	    extract(shortcode_atts(array(
	    'type'  	=> '',
	    'duration' 	=> '',
	    'delay' 	=> '',
	    'iteration' => '',
	    'offset' 	=> '',

	    ), $atts));

		// load scripts
		wp_enqueue_script('rescue_wow');
		wp_enqueue_script('rescue_wow_init');
		wp_enqueue_style('rescue_animate');

	    $type = ($type) ? ''.$type. '' : '';
	    $duration = ($duration) ? ''.$duration. '' : '';
	    $delay = ($delay) ? ''.$delay. '' : '';
	    $iteration = ($iteration) ? ''.$iteration. '' : '';

	    $rescue_animate = '<div class="wow '.$type.'" data-wow-duration="'.$duration.'" data-wow-offset="'.$offset.'" data-wow-delay="'.$delay.'" data-wow-iteration="'.$iteration.'">' . do_shortcode($content) . '</div>';

	    return $rescue_animate;
	}

	add_shortcode('rescue_animate', 'rescue_animate_shortcode');
endif;
