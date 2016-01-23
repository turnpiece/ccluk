<div class="sliderclass carousel_outerrim kad-desktop-slider">
  <?php global $virtue; 
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
        if(isset($virtue['slider_autoplay']) && $virtue['slider_autoplay'] == 0) {
          $autoplay = 'false';
        } else {
          $autoplay = 'true';
        }
        if(isset($virtue['slider_pausetime'])) {
          $pausetime = $virtue['slider_pausetime'];
        } else {
          $pausetime = '7000';
        } ?>
    <div id="imageslider" class="loading">
      <div class="carousel_slider_outer fredcarousel fadein-carousel" style="overflow:hidden; max-width:<?php echo esc_attr($slidewidth);?>px; height: <?php echo esc_attr($slideheight);?>px; margin-left: auto; margin-right:auto;">
        <div class="carousel_slider initcarouselslider" data-carousel-container=".carousel_slider_outer" data-carousel-transition="600" data-carousel-height="<?php echo esc_attr($slideheight); ?>" data-carousel-auto="<?php echo esc_attr($autoplay); ?>" data-carousel-speed="<?php echo esc_attr($pausetime); ?>" data-carousel-id="carouselslider">
            <?php foreach ($slides as $slide) : 
                    if(!empty($slide['target']) && $slide['target'] == 1) {$target = '_blank';} else {$target = '_self';}
                    $image = aq_resize($slide['url'], null, $slideheight, false, false);
                    if(empty($image)) {$image = array($slide['url'],$slidewidth,$slideheight);} 
                        echo '<div class="carousel_gallery_item" style="float:left; display: table; position: relative; text-align: center; margin: 0; width:auto; height:'.esc_attr($image[2]).'px;">';
                        echo '<div class="carousel_gallery_item_inner" style="vertical-align: middle; display: table-cell;">';
                        if($slide['link'] != '') echo '<a href="'.esc_url($slide['link']).'" target="'.esc_attr($target).'">';
                        echo '<img src="'.esc_url($image[0]).'" width="'.esc_attr($image[1]).'" height="'.esc_attr($image[2]).'" />';
                        if ($captions == '1') { ?> 
                                <div class="flex-caption">
                                <?php if ($slide['title'] != '') echo '<div class="captiontitle headerfont">'.$slide['title'].'</div>'; 
                                 if ($slide['description'] != '') echo '<div><div class="captiontext headerfont"><p>'.$slide['description'].'</p></div></div>';?>
                                </div> 
                        <?php } 
                         if($slide['link'] != '') echo '</a>'; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
            </div>
            <div class="clearfix"></div>
              <a id="prevport-carouselslider" class="prev_carousel icon-arrow-left" href="#"></a>
              <a id="nextport-carouselslider" class="next_carousel icon-arrow-right" href="#"></a>
          </div> <!--fredcarousel-->
  </div><!--Container-->
</div><!--sliderclass-->