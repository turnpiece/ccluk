<div class="sliderclass kad-desktop-slider">
  <?php  global $virtue; 
        if(isset($virtue['slider_size'])) {
          $slideheight = $virtue['slider_size'];
        } else {
          $slideheight = 400;
        }
        if(isset($virtue['slider_size_width'])) {
          $slidewidth = $virtue['slider_size_width'];
        } else {
          $slidewidth = 1140;
        }
        if(isset($virtue['slider_captions'])) {
          $captions = $virtue['slider_captions'];
        } else {
          $captions = '';
        }
        if(isset($virtue['home_slider'])) {
          $slides = $virtue['home_slider'];
        } else {
          $slides = '';
        }
        if(isset($virtue['trans_type'])) {
          $transtype = $virtue['trans_type'];
        } else {
          $transtype = 'slide';
        }
        if(isset($virtue['slider_transtime'])) {
          $transtime = $virtue['slider_transtime'];
        } else {
          $transtime = '300';
        }
        if(isset($virtue['slider_autoplay'])) {
          $autoplay = $virtue['slider_autoplay'];
        } else {
          $autoplay = 'true';
        }
        if(isset($virtue['slider_pausetime'])) {
          $pausetime = $virtue['slider_pausetime'];
        } else {
          $pausetime = '7000';
        } ?>
  <div id="imageslider" class="container">
    <div class="flexslider kt-flexslider loading" style="max-width:<?php echo esc_attr($slidewidth);?>px; margin-left: auto; margin-right:auto;" data-flex-speed="<?php echo esc_attr($pausetime);?>" data-flex-anim-speed="<?php echo esc_attr($transtime);?>" data-flex-animation="<?php echo esc_attr($transtype); ?>" data-flex-auto="<?php echo esc_attr($autoplay);?>">
        <ul class="slides">
            <?php foreach ($slides as $slide) : 
                    if(!empty($slide['target']) && $slide['target'] == 1) {$target = '_blank';} else {$target = '_self';}
                    $image = aq_resize($slide['url'], $slidewidth, $slideheight, true);
                    if(empty($image)) {$image = $slide['url'];} ?>
                      <li> 
                        <?php if($slide['link'] != '') echo '<a href="'.esc_url($slide['link']).'" target="'.esc_attr($target).'">'; ?>
                          <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($slide['title']); ?>" />
                              <?php if ($captions == '1') { ?> 
                                <div class="flex-caption">
              								  <?php if (!empty($slide['title'])) {
                                  echo '<div class="captiontitle headerfont">'.$slide['title'].'</div>'; 
                                }
              								  if (!empty($slide['description'])) {
                                  echo '<div><div class="captiontext headerfont"><p>'.$slide['description'].'</p></div></div>';
                                } ?>
                                </div> 
                              <?php } ?>
                        <?php if($slide['link'] != '') echo '</a>'; ?>
                      </li>
                  <?php endforeach; ?>
        </ul>
      </div> <!--Flex Slides-->
  </div><!--Container-->
</div><!--sliderclass-->