<?php 
/**
 *
 * Re-create the [gallery] shortcode and use thumbnails styling from kadencethemes
 *
 */
function kadence_shortcode_gallery($attr) {
  $post = get_post();
  static $instance = 0;
  $instance++;

  if (!empty($attr['ids'])) {
    if (empty($attr['orderby'])) {
      $attr['orderby'] = 'post__in';
    }
    $attr['include'] = $attr['ids'];
  }

  $output = apply_filters('post_gallery', '', $attr);

  if ($output != '') {
    return $output;
  }

  if (isset($attr['orderby'])) {
    $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
    if (!$attr['orderby']) {
      unset($attr['orderby']);
    }
  }

  extract(shortcode_atts(array(
    'order'      => 'ASC',
    'orderby'    => 'menu_order ID',
    'id'         => $post->ID,
    'itemtag'    => '',
    'icontag'    => '',
    'captiontag' => '',
    'columns'    => 3,
    'size'       => 'full',
    'include'    => '',
    'lightboxsize' => 'full',
    'exclude'    => ''
  ), $attr));

  $id = intval($id);

  if ($order === 'RAND') {
    $orderby = 'none';
  }

  $gallery_rn = (rand(10,100));

  if (!empty($include)) {
    $_attachments = get_posts(array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));

    $attachments = array();
    foreach ($_attachments as $key => $val) {
      $attachments[$val->ID] = $_attachments[$key];
    }
  } elseif (!empty($exclude)) {
    $attachments = get_children(array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
  } else {
    $attachments = get_children(array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby));
  }

  if (empty($attachments)) {
    return '';
  }

  if (is_feed()) {
    $output = "\n";
    foreach ($attachments as $att_id => $attachment) {
      $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
    }
    return $output;
  }
    // NORMAL
  $output .= '<div id="kad-wp-gallery'.esc_attr($gallery_rn).'" class="kad-wp-gallery kad-light-wp-gallery clearfix rowtight">'; 
    if ($columns == '2') {$itemsize = 'tcol-lg-6 tcol-md-6 tcol-sm-6 tcol-xs-12 tcol-ss-12'; $imgsize = 600;} 
      else if ($columns == '1') {$itemsize = 'tcol-lg-12 tcol-md-12 tcol-sm-12 tcol-xs-12 tcol-ss-12'; $imgsize = 1200;} 
      else if ($columns == '3'){ $itemsize = 'tcol-lg-4 tcol-md-4 tcol-sm-4 tcol-xs-6 tcol-ss-12'; $imgsize = 400;} 
      else if ($columns == '6'){ $itemsize = 'tcol-lg-2 tcol-md-2 tcol-sm-3 tcol-xs-4 tcol-ss-6'; $imgsize = 300;}
      else if ($columns == '8' || $columns == '9' || $columns == '7'){ $itemsize = 'tcol-lg-2 tcol-md-2 tcol-sm-3 tcol-xs-4 tcol-ss-4'; $imgsize = 260;}
      else if ($columns == '12' || $columns == '11'){ $itemsize = 'tcol-lg-1 tcol-md-1 tcol-sm-2 tcol-xs-2 tcol-ss-3'; $imgsize = 240;} 
      else if ($columns == '5'){ $itemsize = 'tcol-lg-25 tcol-md-25 tcol-sm-3 tcol-xs-4 tcol-ss-6'; $imgsize = 300;} 
      else {$itemsize = 'tcol-lg-3 tcol-md-3 tcol-sm-4 tcol-xs-6 tcol-ss-12'; $imgsize = 300;}
  $i = 0;
  foreach ($attachments as $id => $attachment) {
    $attachment_url = wp_get_attachment_url($id);
    $image = aq_resize($attachment_url, $imgsize, $imgsize, true);
    if(empty($image)) {$image = $attachment_url;}
    if($lightboxsize != 'full') {
            $attachment_url = wp_get_attachment_image_src( $id, $lightboxsize);
            $attachment_url = $attachment_url[0];
    }
    $link = isset($attr['link']) && 'post' == $attr['link'] ? wp_get_attachment_link($id, $size, true, false) : wp_get_attachment_link($id, $size, false, false);

    $output .= '<div class="'.esc_attr($itemsize).' g_item"><div class="grid_item kad_gallery_fade_in gallery_item"><a href="'.esc_url($attachment_url).'" rel="lightbox[pp_gal]" class="lightboxhover">
                          <img src="'.esc_url($image).'" alt="'.esc_attr($attachment->post_excerpt).'" class="light-dropshaddow"/>';
     $output .= '</a>';
    $output .= '</div></div>';
  }
  $output .= '</div>';
  
  return $output;
}
$pinnacle = get_option( 'pinnacle' );
$virtue = get_option( 'virtue' );
if(! function_exists( 'kadence_gallery' ) ) {
if( (isset($pinnacle['pinnacle_gallery']) && $pinnacle['pinnacle_gallery'] == '1') ||  (isset($virtue['virtue_gallery']) && $virtue['virtue_gallery'] == '1') )  {
  remove_shortcode('gallery');
  add_shortcode('gallery', 'kadence_shortcode_gallery');
} 
}