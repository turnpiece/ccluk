<?php	
	header("Content-type: text/css;");
	$current_url = dirname(__FILE__);
	$wp_content_pos = strpos($current_url, 'wp-content');
	$wp_content = substr($current_url, 0, $wp_content_pos);
	require_once($wp_content . 'wp-load.php');

	global $options;

	if ( $options ) {
		
		// Get the options from the databases
		$buttons = $options['buttons'];
                $buttonstext = $options['buttonstext'];
   
                $maina = $options['maina'];
                $mainahover = $options['mainahover'];
                $topbarcolor = $options['topbarcolor'];
                $footercolor = $options['footercolor'];
                $smallfooter = $options['smallfooter'];
                $linkscolor = $options['linkscolor'];
                $linkshovercolor = $options['linkshovercolor'];
                $accent = $options['accent'];
		
	
		?>

	<?php /* Color Scheme */ echo '
		

.button-small-theme, .wpcf7-submit  { background:' . $buttons . ' !important; }
    
.button-small-theme, .wpcf7-submit  { color:' . $buttonstext . '!important; } 


.mainNav a, .current_page_item a ul li a, .sub-menu li a { color:' . $maina . '; }
.mainNav a:hover, .mainNav .active, .current-menu-item a, .sub-menu .current-menu-item a, .current_page_item a, .home .homelink a  { color:' . $mainahover . '; }
  #topbar, .sf-menu li li, .sf-menu li li li  { background:' . $topbarcolor . '; }
        footer { background:' . $footercolor . '; }
              .smallFooter { background:' . $smallfooter . '; }
                  
a,
.metaBtn li a,
.widget h3 a,
a.continue,
.caption-btn li a,
.postCategories a

{ color:' . $linkscolor . '; }
    
a:hover,
.metaBtn li a:hover,
.widget h3 a:hover,
a.continue:hover,
.caption-btn li a:hover

{ color:' . $linkshovercolor . '; }
      
.dd_events_post .continue, 
.widget_btn .continue,
.dd_causes_widget li ul a,
.dd_news h1 a, 
.dd_news_post h1 a

{ background:' . $accent . '; }

.dd_news h1, .dd_news_post h1 {
    -webkit-box-shadow:inset 10px 0 0 ' . $accent . ';
    -moz-box-shadow:inset 10px 0 0 ' . $accent . ';
    box-shadow:inset 10px 0 0 ' . $accent . ';
}

.dd_news h1 a, .dd_news_post h1 a{
    -webkit-box-shadow:10px 0 0 ' . $accent . ' ;
    -moz-box-shadow: 10px 0 0 ' . $accent . ' ;
    box-shadow: 10px 0 0 ' . $accent . ';
}

footer .one-third, .mainNav  {
    border-top: 3px solid ' . $accent . ';
}

footer h3, footer ul h3  {
    color: ' . $accent . ';
}


     

	'; 
} ?>
