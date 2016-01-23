<?php
//Shortcode for accordion
function kad_accordion_shortcode_function($atts, $content ) {
	extract(shortcode_atts(array(
'id' => rand(1, 99)
), $atts));
	$GLOBALS['pane_count'] = 0;
	$GLOBALS['panes'] = '';
	do_shortcode( $content );
	if( is_array( $GLOBALS['panes'] ) ){
	$i = 0;
	foreach( $GLOBALS['panes'] as $tab ){
		if ($i % 2 == 0) {$eo = "even";} else {$eo = "odd";}
	$tabs[] = '<div class="panel panel-default panel-'.$eo.'"><div class="panel-heading"><a class="accordion-toggle '.$tab['open'].'" data-toggle="collapse" data-parent="#accordionname'.$id.'" href="#collapse'.$id.$tab['link'].'"><h5><i class="icon-minus primary-color"></i><i class="icon-plus"></i>'.$tab['title'].'</h5></a></div><div id="collapse'.$id.$tab['link'].'" class="panel-collapse collapse '.$tab['in'].'"><div class="panel-body postclass">'.$tab['content'].'</div></div></div>';
	$i++;
}
$return = "\n".'<div class="panel-group" id="accordionname'.$id.'">'.implode( "\n", $tabs ).'</div>'."\n";
}
return $return;
}

function kad_accordion_pane_function($atts, $content ) {
	extract(shortcode_atts(array(
	'title' => 'Pane %d',
	'start' => ''
	), $atts));
if ($start != '') {$open = '';} else {$open = 'collapsed';}
if ($start != '') {$in = 'in';} else {$in = '';}

$x = $GLOBALS['pane_count'];
$GLOBALS['panes'][$x] = array( 'title' => $title, 'open' => $open, 'in' => $in, 'link' => $GLOBALS['pane_count'], 'content' =>  do_shortcode( $content ) );

$GLOBALS['pane_count']++;
}
function kad_tab_shortcode_function($atts, $content ) {
		extract(shortcode_atts(array(
'id' => rand(1, 99)
), $atts));

	$GLOBALS['tab_count'] = 0;
	$GLOBALS['tabs'] = '';
	do_shortcode( $content );
	if( is_array( $GLOBALS['tabs'] ) ){
		
	foreach( $GLOBALS['tabs'] as $nav ){
	$tabnav[] = '<li class="'.$nav['active'].'"><a href="#sctab'.$id.$nav['link'].'">'.$nav['title'].'</a></li>';
	}
		
	foreach( $GLOBALS['tabs'] as $tab ){
	$tabs[] = '<div class="tab-pane clearfix '.$tab['active'].'" id="sctab'.$id.$tab['link'].'">'.$tab['content'].'</div>';
	}
	
$return = "\n".'<ul class="nav nav-tabs sc_tabs">'.implode( "\n", $tabnav ).'</ul> <div class="tab-content postclass">'.implode( "\n", $tabs ).'</div>'."\n";
}
return $return;
}
function kad_tab_pane_function($atts, $content ) {
	extract(shortcode_atts(array(
'title' => 'Tab %d',
'start' => ''
), $atts));
if ($start != '') {$active = 'active';} else {$active = '';}

$x = $GLOBALS['tab_count'];
$GLOBALS['tabs'][$x] = array( 'title' => $title, 'active' => $active, 'link' => $GLOBALS['tab_count'], 'content' =>  do_shortcode( $content ) );

$GLOBALS['tab_count']++;
}

//Shortcode for columns
function kad_column_shortcode_function( $atts, $content ) {
	return '<div class="row">'.do_shortcode($content).'</div>';
}
function kad_hcolumn_shortcode_function( $atts, $content ) {
	return '<div class="row">'.do_shortcode($content).'</div>';
}
function kad_column11_function( $atts, $content ) {
	return '<div class="col-md-11">'.do_shortcode($content).'</div>';
}
function kad_column10_function( $atts, $content ) {
	return '<div class="col-md-10">'.do_shortcode($content).'</div>';
}
function kad_column9_function( $atts, $content ) {
	return '<div class="col-md-9">'.do_shortcode($content).'</div>';
}
function kad_column8_function( $atts, $content ) {
	return '<div class="col-md-8">'.do_shortcode($content).'</div>';
}
function kad_column7_function( $atts, $content ) {
	return '<div class="col-md-7">'.do_shortcode($content).'</div>';
}
function kad_column6_function( $atts, $content ) {
	return '<div class="col-md-6">'.do_shortcode($content).'</div>';
}
function kad_column5_function( $atts, $content ) {
	return '<div class="col-md-5">'.do_shortcode($content).'</div>';
}
function kad_column4_function( $atts, $content ) {
	return '<div class="col-md-4">'.do_shortcode($content).'</div>';
}
function kad_column3_function( $atts, $content ) {
	return '<div class="col-md-3">'.do_shortcode($content).'</div>';
}
function kad_column2_function( $atts, $content ) {
	return '<div class="col-md-2">'.do_shortcode($content).'</div>';
}
function kad_column1_function( $atts, $content ) {
	return '<div class="col-md-1">'.do_shortcode($content).'</div>';
}
//Shortcode for Icons
function kad_icon_shortcode_function( $atts) {
	extract(shortcode_atts(array(
		'icon' => '',
		'size' => '',
		'color' => '',
		'float'=> ''
), $atts));
if ($float != '') $output = '<i class="'.$icon.'" style="font-size:'.$size.'; color:'.$color.'; float:'.$float.'; padding:10px;"></i>';
else $output = '<i class="'.$icon.'" style="font-size:'.$size.'; color:'.$color.';"></i>';
	return $output;
}
// Video Shortcode
function kad_video_shortcode_function( $atts, $content) {
	extract(shortcode_atts(array(
		'width' => '',
), $atts));
	if($width != '') { $output = '<div style="max-width:'.$width.'px;"><div class="videofit">'.$content.'</div></div>';}
	else { $output = '<div class="videofit">'.$content.'</div>'; }
	return $output;
}
// Based on Ultimate Shortcodes youtube and vimeo shortcodes
function kad_youtube_shortcode_function( $atts, $content) {
		// Prepare data
		$return = array();
		$params = array();
		$atts = shortcode_atts(array(
				'url'  => false,
				'width' => 600,
				'height' => 400,
				'maxwidth' => '',
				'autoplay' => 'false',
				'controls' => 'true',
				'hidecontrols' => 'false',
				'fs' => 'true',
				'modestbranding' => 'false',
				'theme' => 'dark'
		), $atts, 'kad_youtube' );

		if ( !$atts['url'] ) return '<p class="error">YouTube: ' . __( 'please specify correct url', 'virtue-toolkit' ) . '</p>';
		$id = ( preg_match( '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $atts['url'], $match ) ) ? $match[1] : false;
		// Check that url is specified
		if ( !$id ) return '<p class="error">YouTube: ' . __( 'please specify correct url', 'virtue-toolkit' ) . '</p>';
		// Prepare params
		if($atts['hidecontrols'] == 'true') {$atts['controls'] = 'false';}
		foreach ( array('autoplay', 'controls', 'fs', 'modestbranding', 'theme' ) as $param ) $params[$param] = str_replace( array( 'false', 'true', 'alt' ), array( '0', '1', '2' ), $atts[$param] );
		// Prepare player parameters
		$params = http_build_query( $params );
		if($atts['maxwidth']) {$maxwidth = 'style="max-width:'.$atts['maxwidth'].'px;"';} else{ $maxwidth = '';}
		// Create player
		$return[] = '<div class="kad-youtube-shortcode videofit" '.$maxwidth.' >';
		$return[] = '<iframe width="' . $atts['width'] . '" height="' . $atts['height'] . '" src="//www.youtube.com/embed/' . $id . '?' . $params . '" frameborder="0" allowfullscreen="true"></iframe>';
		$return[] = '</div>';
		// Return result
		return implode( '', $return );
}
function kad_vimeo_shortcode_function( $atts, $content) {
		$return = array();
		$atts = shortcode_atts( array(
				'url'        => false,
				'width'      => 600,
				'height'     => 400,
				'maxwidth' => '',
				'autoplay'   => 'no'
			), $atts, 'vimeo' );
		if ( !$atts['url'] ) return '<p class="error">Vimeo: ' . __( 'please specify correct url', 'virtue-toolkit' ) . '</p>';
		$id = ( preg_match( '~(?:<iframe [^>]*src=")?(?:https?:\/\/(?:[\w]+\.)*vimeo\.com(?:[\/\w]*\/videos?)?\/([0-9]+)[^\s]*)"?(?:[^>]*></iframe>)?(?:<p>.*</p>)?~ix', $atts['url'], $match ) ) ? $match[1] : false;
		// Check that url is specified
		if ( !$id ) return '<p class="error">Vimeo: ' . __( 'please specify correct url', 'virtue-toolkit' ) . '</p>';

		if($atts['maxwidth']) {$maxwidth = 'style="max-width:'.$atts['maxwidth'].'px;"';} else{ $maxwidth = '';}
		$autoplay = ( $atts['autoplay'] === 'yes' ) ? '&amp;autoplay=1' : '';
		// Create player
		$return[] = '<div class="kad-vimeo-shortcode  videofit '.$maxwidth.'">';
		$return[] = '<iframe width="' . $atts['width'] . '" height="' . $atts['height'] .
			'" src="//player.vimeo.com/video/' . $id . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff' .
			$autoplay . '" frameborder="0" allowfullscreen="true"></iframe>';
		$return[] = '</div>';
		// Return result
		return implode( '', $return );
	}
//Button
function kad_button_shortcode_function( $atts) {
	extract(shortcode_atts(array(
		'id' => rand(1, 99),
		'bcolor' => '',
		'bhovercolor' => '',
		'thovercolor' => '',
		'link' => '',
		'text' => '',
		'target' => '_self',
		'tcolor' => '',
), $atts));
	$output = '<a href="'.$link.'" class="kad-btn kad-btn-primary" id="kadbtn'.$id.'" target="'.$target.'" style="';
	if(!empty($bcolor)) {
		$output .= 'background-color:'.$bcolor.';';
	}
	if(!empty($tcolor)) {
		$output .= 'color:'.$tcolor.';';
	}
	$output .= '"';
	if($thovercolor == $tcolor) {$thovercolor = null;}
	if(!empty($bhovercolor) || !empty($thovercolor)) {
		$output .= 'onMouseOver="this.style.background=\''.$bhovercolor.'\',this.style.color=\''.$thovercolor.'\'" onMouseOut="this.style.background=\''.$bcolor.'\', this.style.color=\''.$tcolor.'\'"';
	}
	$output .= '>'.$text.'</a>';

	return $output;
}
function kad_blockquote_shortcode_function( $atts, $content) {
	extract(shortcode_atts(array(
		'align' => 'center',
), $atts));
		switch ($align)
	{
		case "center":
		$output = '<div class="blockquote-full postclass clearfix">' . do_shortcode($content) . '</div>';
		break;
		
		case "left":
		$output = '<div class="blockquote-left postclass clearfix">' . do_shortcode($content) . '</div>';
		break;
		
		case "right":
		$output = '<div class="blockquote-right postclass clearfix">' . do_shortcode($content) . '</div>';
		break;
	}
	  return $output;
}
function kad_pullquote_shortcode_function( $atts, $content) {
   extract( shortcode_atts( array(
	  'align' => 'center'
  ), $atts ));

	switch ($align)
	{
		case "center":
		$output = '<div class="pullquote-center">' . do_shortcode($content) . '</div>';
		break;
		
		case "right":
		$output = '<div class="pullquote-right">' . do_shortcode($content) . '</div>';
		break;
		
		case "left":
		$output = '<div class="pullquote-left">' . do_shortcode($content) . '</div>';
		break;
	}

   return $output;
}
function kad_hrule_function( ) {
	return '<div class="hrule clearfix"></div>';
}
function kad_hrpadding10_function( ) {
	return '<div class="space_20 clearfix"></div>';
}
function kad_hrpadding20_function( ) {
	return '<div class="space_40 clearfix"></div>';
}
function kad_hrpadding40_function( ) {
	return '<div class="space_80 clearfix"></div>';
}
function kad_clearfix_function( ) {
	return '<div class="clearfix"></div>';
}
function kad_columnhelper_function( ) {
	return '';
}
function virtuetoolkit_register_shortcodes(){
   add_shortcode('accordion', 'kad_accordion_shortcode_function');
   add_shortcode('pane', 'kad_accordion_pane_function');
   add_shortcode('tabs', 'kad_tab_shortcode_function');
   add_shortcode('tab', 'kad_tab_pane_function');
   add_shortcode('columns', 'kad_column_shortcode_function');
   add_shortcode('hcolumns', 'kad_hcolumn_shortcode_function');
   add_shortcode('span11', 'kad_column11_function');
   add_shortcode('span10', 'kad_column10_function');
   add_shortcode('span9', 'kad_column9_function');
   add_shortcode('span8', 'kad_column8_function');
   add_shortcode('span7', 'kad_column7_function');
   add_shortcode('span6', 'kad_column6_function');
   add_shortcode('span5', 'kad_column5_function');
   add_shortcode('span4', 'kad_column4_function');
   add_shortcode('span3', 'kad_column3_function');
   add_shortcode('span2', 'kad_column2_function');
   add_shortcode('span1', 'kad_column1_function');
   add_shortcode('columnhelper', 'kad_columnhelper_function');
   add_shortcode('icon', 'kad_icon_shortcode_function');
   add_shortcode('pullquote', 'kad_pullquote_shortcode_function');
   add_shortcode('blockquote', 'kad_blockquote_shortcode_function');
   add_shortcode('btn', 'kad_button_shortcode_function');
   add_shortcode('hr', 'kad_hrule_function');
   add_shortcode('space_20', 'kad_hrpadding10_function');
   add_shortcode('space_40', 'kad_hrpadding20_function');
   add_shortcode('space_80', 'kad_hrpadding40_function');
      add_shortcode('kad_youtube', 'kad_youtube_shortcode_function');
   add_shortcode('kad_vimeo', 'kad_vimeo_shortcode_function');
   add_shortcode('clear', 'kad_clearfix_function');
}
add_action( 'init', 'virtuetoolkit_register_shortcodes');


function virtue_register_button( $buttons ) {
   array_push( $buttons, "|", "kadcolumns" );
   array_push( $buttons, "|", "kaddivider" );
   array_push( $buttons, "|", "kadaccordion" );
   array_push( $buttons, "|", "kadquote" );
   array_push( $buttons, "|", "kadbtn" );
   array_push( $buttons, "|", "kadicon" );
   array_push( $buttons, "|", "kadyoutube" );
   array_push( $buttons, "|", "kadvimeo" );      
   return $buttons;
}
function virtue_add_plugin( $plugin_array ) {
   $plugin_array['kadcolumns'] = VIRTUE_TOOLKIT_URL . '/shortcodes/columns/columns_shortgen.js';
   $plugin_array['kadicon'] = VIRTUE_TOOLKIT_URL . '/shortcodes/icons/icon_shortgen.js';
   $plugin_array['kadaccordion'] = VIRTUE_TOOLKIT_URL . '/shortcodes/accordion/accordion_shortgen.js';
   $plugin_array['kadyoutube'] = VIRTUE_TOOLKIT_URL . '/shortcodes/youtube/youtube_shortgen.js';
   $plugin_array['kadvimeo'] = VIRTUE_TOOLKIT_URL . '/shortcodes/vimeo/vimeo_shortgen.js';
   $plugin_array['kadquote'] = VIRTUE_TOOLKIT_URL . '/shortcodes/pullquote/quote_shortgen.js';
   $plugin_array['kadbtn'] = VIRTUE_TOOLKIT_URL . '/shortcodes/btns/btns_shortgen.js';
   $plugin_array['kaddivider'] = VIRTUE_TOOLKIT_URL . '/shortcodes/divider/divider_shortgen.js';
   return $plugin_array;
}
function virtue_tinymce_shortcode_button() {

   if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
      return;
   }

   if ( get_user_option('rich_editing') == 'true' ) {
      add_filter( 'mce_external_plugins', 'virtue_add_plugin' );
      add_filter( 'mce_buttons_3', 'virtue_register_button' );
   }

}
add_action('init', 'virtue_tinymce_shortcode_button');

//    Clean up Shortcodes
function kadtool_content_clean_shortcodes($content){   
    $array = array (
        '<p>[' => '[', 
        ']</p>' => ']', 
        ']<br />' => ']'
    );
    $content = strtr($content, $array);
    return $content;
}
add_filter('the_content', 'kadtool_content_clean_shortcodes');
function kadtool_widget_clean_shortcodes($text){   
    $array = array (
        '<p>[' => '[', 
        ']</p>' => ']', 
        '<p></p>' => '', 
        ']<br />' => ']',
        '<br />[' => '['
    );
    $text = strtr($text, $array);
    return $text;
}
add_filter('widget_text', 'kadtool_widget_clean_shortcodes');
add_filter('widget_text', 'do_shortcode', 50);
