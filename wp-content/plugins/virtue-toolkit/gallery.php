<?php 

function kt_toolkit_get_srcset($width,$height,$url,$id) {
  if(empty($id) || empty($url)) {
    return;
  }
  
  $image_meta = get_post_meta( $id, '_wp_attachment_metadata', true );
  if(empty($image_meta['file'])){
    return;
  }
  // If possible add in our images on the fly sizes
  $ext = substr($image_meta['file'], strrpos($image_meta['file'], "."));
  $pathflyfilename = str_replace($ext,'-'.$width.'x'.$height.'' . $ext, $image_meta['file']);
  $pathretinaflyfilename = str_replace($ext, '-'.$width.'x'.$height.'@2x' . $ext, $image_meta['file']);
  $flyfilename = basename($image_meta['file'], $ext) . '-'.$width.'x'.$height.'' . $ext;
  $retinaflyfilename = basename($image_meta['file'], $ext) . '-'.$width.'x'.$height.'@2x' . $ext;

  $upload_info = wp_upload_dir();
  $upload_dir = $upload_info['basedir'];

  $flyfile = trailingslashit($upload_dir).$pathflyfilename;
  $retinafile = trailingslashit($upload_dir).$pathretinaflyfilename;
  if(empty($image_meta['sizes']) ){ $image_meta['sizes'] = array();}
    if (file_exists($flyfile)) {
      $kt_add_imagesize = array(
        'kt_on_fly' => array( 
          'file'=> $flyfilename,
          'width' => $width,
          'height' => $height,
          'mime-type' => isset($image_meta['sizes']['thumbnail']) ? $image_meta['sizes']['thumbnail']['mime-type'] : '',
          )
      );
      $image_meta['sizes'] = array_merge($image_meta['sizes'], $kt_add_imagesize);
    }
    if (file_exists($retinafile)) {
      $size = getimagesize( $retinafile );
      if(($size[0] == 2 * $width) && ($size[1] == 2 * $height) ) {
        $kt_add_imagesize_retina = array(
        'kt_on_fly_retina' => array( 
          'file'=> $retinaflyfilename,
          'width' => 2 * $width,
          'height' => 2 * $height,
          'mime-type' => isset($image_meta['sizes']['thumbnail']) ? $image_meta['sizes']['thumbnail']['mime-type'] : '',
          )
        );
        $image_meta['sizes'] = array_merge($image_meta['sizes'], $kt_add_imagesize_retina);
      }
    }
    if(function_exists ( 'wp_calculate_image_srcset') ){
      $output = wp_calculate_image_srcset(array( $width, $height), $url, $image_meta, $id);
    } else {
      $output = '';
    }
    return $output;
}
function kt_toolkit_get_srcset_output($width,$height,$url,$id) {
    $img_srcset = kt_toolkit_get_srcset( $width, $height, $url, $id);
    if(!empty($img_srcset) ) {
      $output = 'srcset="'.esc_attr($img_srcset).'" sizes="(max-width: '.esc_attr($width).'px) 100vw, '.esc_attr($width).'px"';
    } else {
      $output = '';
    }
    return $output;
}
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
    'link'      => 'file',
    'size'       => 'full',
    'include'    => '',
    'attachment_page' => 'false',
    'use_image_alt' => 'false',
    'gallery_id'  => (rand(10,100)),
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

  if ($columns == '2') {
    $itemsize = 'tcol-lg-6 tcol-md-6 tcol-sm-6 tcol-xs-12 tcol-ss-12'; $imgsize = 600;
  } else if ($columns == '1') {
    $itemsize = 'tcol-lg-12 tcol-md-12 tcol-sm-12 tcol-xs-12 tcol-ss-12'; $imgsize = 1200;
  } else if ($columns == '3'){
    $itemsize = 'tcol-lg-4 tcol-md-4 tcol-sm-4 tcol-xs-6 tcol-ss-12'; $imgsize = 400;
  } else if ($columns == '6'){
    $itemsize = 'tcol-lg-2 tcol-md-2 tcol-sm-3 tcol-xs-4 tcol-ss-6'; $imgsize = 300;
  } else if ($columns == '8' || $columns == '9' || $columns == '7'){ 
    $itemsize = 'tcol-lg-2 tcol-md-2 tcol-sm-3 tcol-xs-4 tcol-ss-4'; $imgsize = 260;
  } else if ($columns == '12' || $columns == '11'){ 
    $itemsize = 'tcol-lg-1 tcol-md-1 tcol-sm-2 tcol-xs-2 tcol-ss-3'; $imgsize = 240;
  } else if ($columns == '5'){ 
    $itemsize = 'tcol-lg-25 tcol-md-25 tcol-sm-3 tcol-xs-4 tcol-ss-6'; $imgsize = 300;
  } else {
    $itemsize = 'tcol-lg-3 tcol-md-3 tcol-sm-4 tcol-xs-6 tcol-ss-12'; $imgsize = 300;
  }

  $output .= '<div id="kad-wp-gallery'.esc_attr($gallery_rn).'" class="kad-wp-gallery kad-light-wp-gallery clearfix kt-gallery-column-'.esc_attr($columns).' rowtight">'; 
      
  $i = 0;
  foreach ($attachments as $id => $attachment) {
    $attachment_url = wp_get_attachment_url($id);
    $image = aq_resize($attachment_url, $imgsize, $imgsize, true, false, false, $id);
    if(empty($image[0])) {$image = array($attachment_url,$imgsize,$imgsize);} 
    $img_srcset_output = kt_toolkit_get_srcset_output( $image[1], $image[2], $attachment_url, $id);
    if($lightboxsize != 'full') {
            $attachment_url = wp_get_attachment_image_src( $id, $lightboxsize);
            $attachment_url = $attachment_url[0];
    }
    $lightbox_data = 'data-rel="lightbox"';
    if($link == 'attachment_page' || $attachment_page == 'true') {
      $attachment_url = get_permalink($id);
      $lightbox_data = '';
    }
    if($use_image_alt == 'true') {
      $alt = get_post_meta($id, '_wp_attachment_image_alt', true);
    } else {
      $alt = $attachment->post_excerpt;
    }

    $output .= '<div class="'.esc_attr($itemsize).' g_item"><div class="grid_item kad_gallery_fade_in gallery_item"><a href="'.esc_url($attachment_url).'" '.$lightbox_data.' class="lightboxhover">';
    $output .= '<img src="'.esc_url($image[0]).'" width="'.esc_attr($image[1]).'" height="'.esc_attr($image[2]).'" alt="'.esc_attr($alt).'" '.$img_srcset_output.' class="light-dropshaddow"/>';
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