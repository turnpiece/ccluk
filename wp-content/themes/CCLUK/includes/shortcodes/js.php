<?php


// DANGER ALERT

function alertdanger( $atts, $content = null ) {

   return '<div class="alert alert-danger alert-block fade in"><a class="close" data-dismiss="alert" href="#">&times;</a>' . do_shortcode($content) . '</div>';

}
add_shortcode('alertdanger', 'alertdanger');

// INFO ALERT

function alertinfo( $atts, $content = null ) {

   return '<div class="alert alert-info alert-block fade in"><a class="close" data-dismiss="alert" href="#">&times;</a>' . do_shortcode($content) . '</div>';

}
add_shortcode('alertinfo', 'alertinfo');

// SUCCESS ALERT

function alertsuccess( $atts, $content = null ) {

   return '<div class="alert alert-success alert-block fade in"><a class="close" data-dismiss="alert" href="#">&times;</a>' . do_shortcode($content) . '</div>';

}
add_shortcode('alertsuccess', 'alertsuccess');

// SUCCESS ALERT

function alert( $atts, $content = null ) {

   return '<div class="alert alert-block fade in"><a class="close" data-dismiss="alert" href="#">&times;</a>' . do_shortcode($content) . '</div>';

}
add_shortcode('alert', 'alert');


// TABS

add_shortcode( 'tabgroup', 'jqtools_tab_group' );

function jqtools_tab_group( $atts, $content ){

$GLOBALS['tab_count'] = 0;

do_shortcode( $content );

if( is_array( $GLOBALS['tabs'] ) ){

foreach( $GLOBALS['tabs'] as $tab ){

$tabs[] = '<li><a data-toggle="tab" href="#'.str_replace( ' ', '', $tab['title'] ).'">'.$tab['title'].'</a></li>';

$panes[] = '<div class="tab-pane fade" id="'.str_replace( ' ', '', $tab['title'] ).'">'.$tab['content'].'</div>';

}

$return = "\n".'<ul id="myTab" class="nav nav-tabs">'.implode( "\n", $tabs ).'</ul>'."\n".'<div id="myTabContent" class="tab-content">'.implode( "\n", $panes ).'</div>'."\n";

}

return $return;

}

add_shortcode( 'tab', 'jqtools_tab' );

function jqtools_tab( $atts, $content ){

extract(shortcode_atts(array(

'title' => 'Tab %d'

), $atts));

$x = $GLOBALS['tab_count'];

$GLOBALS['tabs'][$x] = array( 'title' => sprintf( $title, $GLOBALS['tab_count'] ), 'content' =>  $content );

$GLOBALS['tab_count']++;

}

 
	//Our hook
	add_shortcode('accordion', 'accordion');
	
	//Our Funciton
	function accordion($atts, $content = null) {
		
		//extracts our attrs . if not set set default
		extract(shortcode_atts(array(
                   "title" => 'Item Title'

		), $atts));
       
        $output ='';
        

   $output .= '<span class="toggle_anchor" href="#">'.$title.'</span><div class="togglebox"><div class="content">'.do_shortcode($content).'</div></div>';

	return $output;	
        
	}

        //Our hook
	add_shortcode('accordiongroup', 'accordiongroup');
	
	//Our Funciton
	function accordiongroup($atts, $content = null) {
		
		//extracts our attrs . if not set set default
		extract(shortcode_atts(array(
    

		), $atts));
       
        $output ='';
        

   $output .= '<div class="accordion_module">'.do_shortcode($content).'</div>';

	return $output;	
        
	}