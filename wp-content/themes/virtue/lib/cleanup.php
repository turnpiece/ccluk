<?php

/**
 * Add body_class() classes
 */
function kadence_body_class($classes) {
  // Add post/page slug
  if (is_single() || is_page() && !is_front_page()) {
    $classes[] = basename(get_permalink());
  }

  return $classes;
}
add_filter('body_class', 'kadence_body_class');

/**
 * Add class="thumbnail" to attachment items
 */
function kadence_attachment_link_class($html) {
  $postid = get_the_ID();
  $html = str_replace('<a', '<a class="thumbnail"', $html);
  return $html;
}
add_filter('wp_get_attachment_link', 'kadence_attachment_link_class', 10, 1);

/**
 * Add-rel-lighbox
 */

add_filter('the_content', 'kad_addlightboxrel', 10);
function kad_addlightboxrel($content){

  /* Find internal links */

  //Check the page for link images direct to image (no trailing attributes)
  $string = '/<a href="(.*?).(jpg|jpeg|png|gif|bmp|ico)"><img(.*?)class="(.*?)wp-image-(.*?)" \/><\/a>/i';
  preg_match_all( $string, $content, $matches, PREG_SET_ORDER);

  //Check which attachment is referenced
  foreach ($matches as $val) {
    $post_caption = '';
    
    $post = get_post($val[5]);
    
    //Replace the instance with the lightbox and title(caption) references. Won't fail if caption is empty.
    $string = '<a href="' . $val[1] . '.' . $val[2] . '"><img' . $val[3] . 'class="' . $val[4] . 'wp-image-' . $val[5] . '" /></a>';
    $replace = '<a href="' . $val[1] . '.' . $val[2] . '" data-rel="lightbox"><img' . $val[3] . 'class="' . $val[4] . 'wp-image-' . $val[5] . '" /></a>';
    $content = str_replace( $string, $replace, $content);
  }

  return $content;
}

/**
 * Add Bootstrap thumbnail styling to images with captions
 *
 * @link http://justintadlock.com/archives/2011/07/01/captions-in-wordpress
 */
function kadence_caption($output, $attr, $content) {
  if (is_feed()) {
    return $output;
  }

  $defaults = array(
    'id'      => '',
    'align'   => 'alignnone',
    'width'   => '',
    'caption' => ''
  );

  $attr = shortcode_atts($defaults, $attr);

  // If the width is less than 1 or there is no caption, return the content wrapped between the [caption] tags
  if ($attr['width'] < 1 || empty($attr['caption'])) {
    return $content;
  }

  // Set up the attributes for the caption <figure>
  $attributes  = (!empty($attr['id']) ? ' id="' . esc_attr($attr['id']) . '"' : '' );
  $attributes .= ' class="thumbnail wp-caption ' . esc_attr($attr['align']) . '"';
  $attributes .= ' style="width: ' . esc_attr($attr['width']) . 'px"';

  $output  = '<figure' . $attributes .'>';
  $output .= do_shortcode($content);
  $output .= '<figcaption class="caption wp-caption-text">' . $attr['caption'] . '</figcaption>';
  $output .= '</figure>';

  return $output;
}
add_filter('img_caption_shortcode', 'kadence_caption', 10, 3);

/**
 * Clean up the_excerpt()
 */
function kadence_excerpt_length($length) {
  return POST_EXCERPT_LENGTH;
}

function kadence_remove_more_link_scroll( $link ) {
  $link = preg_replace( '|#more-[0-9]+|', '', $link );
  return $link;
}
add_filter( 'the_content_more_link', 'kadence_remove_more_link_scroll' );

function kadence_excerpt_more($more) {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'virtue') . '</a>';
}
add_filter('excerpt_length', 'kadence_excerpt_length');
add_filter('excerpt_more', 'kadence_excerpt_more');

/**
 * Add additional classes onto widgets
 *
 * @link http://wordpress.org/support/topic/how-to-first-and-last-css-classes-for-sidebar-widgets
 */
function kadence_widget_first_last_classes($params) {
  global $my_widget_num;

  $this_id = $params[0]['id'];
  $arr_registered_widgets = wp_get_sidebars_widgets();

  if (!$my_widget_num) {
    $my_widget_num = array();
  }

  if (!isset($arr_registered_widgets[$this_id]) || !is_array($arr_registered_widgets[$this_id])) {
    return $params;
  }

  if (isset($my_widget_num[$this_id])) {
    $my_widget_num[$this_id] ++;
  } else {
    $my_widget_num[$this_id] = 1;
  }

  $class = 'class="widget-' . $my_widget_num[$this_id] . ' ';

  if ($my_widget_num[$this_id] == 1) {
    $class .= 'widget-first ';
  } elseif ($my_widget_num[$this_id] == count($arr_registered_widgets[$this_id])) {
    $class .= 'widget-last ';
  }

  $params[0]['before_widget'] = preg_replace('/class=\"/', "$class", $params[0]['before_widget'], 1);

  return $params;
}
add_filter('dynamic_sidebar_params', 'kadence_widget_first_last_classes');
