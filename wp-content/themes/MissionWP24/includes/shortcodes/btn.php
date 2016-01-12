<?php

// 
// 
// 
// BUTTONS SHORTCODES
// 
// 
// 
// 
// 

function button($atts, $content = null) {  
    extract(shortcode_atts(array(  
        "color" => 'salmon',
        "type" => 'small',
        "url" => '#',
        "radius" => '3'  
    ), $atts));  
    return '<a class="'.$color.' rounded'.$radius.' button-'.$type.'" href="'.$url.'">'.$content.'</a>';  
}  
add_shortcode("button", "button");  

