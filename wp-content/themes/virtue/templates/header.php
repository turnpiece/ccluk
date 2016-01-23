<?php global $virtue; ?>
<header class="banner headerclass" role="banner">
<?php if (kadence_display_topbar()) : ?>
  <section id="topbar" class="topclass">
    <div class="container">
      <div class="row">
        <div class="col-md-6 col-sm-6 kad-topbar-left">
          <div class="topbarmenu clearfix">
          <?php if (has_nav_menu('topbar_navigation')) :
              wp_nav_menu(array('theme_location' => 'topbar_navigation', 'menu_class' => 'sf-menu'));
            endif;?>
            <?php if(kadence_display_topbar_icons()) : ?>
            <div class="topbar_social">
              <ul>
                <?php $top_icons = $virtue['topbar_icon_menu'];
                foreach ($top_icons as $top_icon) {
                  if(!empty($top_icon['target']) && $top_icon['target'] == 1) {
                    $target = '_blank';
                  } else {
                    $target = '_self';
                  }
                  echo '<li><a href="'.esc_url($top_icon['link']).'" target="'.esc_attr($target).'" title="'.esc_attr($top_icon['title']).'" data-toggle="tooltip" data-placement="bottom" data-original-title="'.esc_attr($top_icon['title']).'">';
                  if(!empty($top_icon['url'])) {
                    echo '<img src="'.esc_url($top_icon['url']).'"/>' ;
                  } else {
                    echo '<i class="'.esc_attr($top_icon['icon_o']).'"></i>';
                  }
                  echo '</a></li>';
                } ?>
              </ul>
            </div>
          <?php endif; ?>
            <?php if(isset($virtue['show_cartcount'])) {
               if($virtue['show_cartcount'] == '1') { 
                if (class_exists('woocommerce')) {
                  global $woocommerce; ?>
                    <ul class="kad-cart-total">
                      <li>
                      <a class="cart-contents" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'woocommerce'); ?>">
                          <i class="icon-shopping-cart" style="padding-right:5px;"></i> <?php _e('Your Cart', 'virtue');?> <span class="kad-cart-dash">-</span> <?php echo $woocommerce->cart->get_cart_total(); ?>
                      </a>
                    </li>
                  </ul>
                <?php } } }?>
          </div>
        </div><!-- close col-md-6 --> 
        <div class="col-md-6 col-sm-6 kad-topbar-right">
          <div id="topbar-search" class="topbar-widget">
            <?php if(kadence_display_topbar_widget()) { if(is_active_sidebar('topbarright')) { dynamic_sidebar('topbarright'); } 
              } else { if(kadence_display_top_search()) {get_search_form();} 
          } ?>
        </div>
        </div> <!-- close col-md-6-->
      </div> <!-- Close Row -->
    </div> <!-- Close Container -->
  </section>
<?php endif; ?>
<?php if(isset($virtue['logo_layout'])) {
  if($virtue['logo_layout'] == 'logocenter') {$logocclass = 'col-md-12'; $menulclass = 'col-md-12';}
  else if($virtue['logo_layout'] == 'logohalf') {$logocclass = 'col-md-6'; $menulclass = 'col-md-6';}
  else {$logocclass = 'col-md-4'; $menulclass = 'col-md-8';} 
} else {$logocclass = 'col-md-4'; $menulclass = 'col-md-8'; }?>
  <div class="container">
    <div class="row">
          <div class="<?php echo esc_attr($logocclass); ?> clearfix kad-header-left">
            <div id="logo" class="logocase">
              <a class="brand logofont" href="<?php echo home_url(); ?>/">
                <?php if (!empty($virtue['x1_virtue_logo_upload']['url'])) { ?>
                  <div id="thelogo">
                    <img src="<?php echo esc_url($virtue['x1_virtue_logo_upload']['url']); ?>" alt="<?php bloginfo('name');?>" class="kad-standard-logo" />
                    <?php if(!empty($virtue['x2_virtue_logo_upload']['url'])) {?>
                    <img src="<?php echo esc_url($virtue['x2_virtue_logo_upload']['url']);?>" class="kad-retina-logo" style="max-height:<?php echo esc_attr($virtue['x1_virtue_logo_upload']['height']);?>px" /> <?php } ?>
                  </div>
                <?php } else { 
                  bloginfo('name'); 
                } ?>
              </a>
              <?php if (isset($virtue['logo_below_text']) && !empty($virtue['logo_below_text'])) { ?>
                <p class="kad_tagline belowlogo-text"><?php echo $virtue['logo_below_text']; ?></p>
              <?php }?>
           </div> <!-- Close #logo -->
       </div><!-- close logo span -->
       <?php if (has_nav_menu('primary_navigation')) : ?>
         <div class="<?php echo esc_attr($menulclass); ?> kad-header-right">
           <nav id="nav-main" class="clearfix" role="navigation">
              <?php wp_nav_menu(array('theme_location' => 'primary_navigation', 'menu_class' => 'sf-menu')); ?>
           </nav> 
          </div> <!-- Close menuclass-->
        <?php endif; ?>       
    </div> <!-- Close Row -->
    <?php if (has_nav_menu('mobile_navigation')) : ?>
           <div id="mobile-nav-trigger" class="nav-trigger">
              <button class="nav-trigger-case mobileclass collapsed" data-toggle="collapse" data-target=".kad-nav-collapse">
                <span class="kad-navbtn"><i class="icon-reorder"></i></span>
                <span class="kad-menu-name"><?php echo __('Menu', 'virtue'); ?></span>
              </button>
            </div>
            <div id="kad-mobile-nav" class="kad-mobile-nav">
              <div class="kad-nav-inner mobileclass">
                <div class="kad-nav-collapse">
                <?php if(isset($virtue['mobile_submenu_collapse']) && $virtue['mobile_submenu_collapse'] == '1') {
                    wp_nav_menu( array('theme_location' => 'mobile_navigation','items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 'menu_class' => 'kad-mnav', 'walker' => new kadence_mobile_walker()));
                  } else {
                  wp_nav_menu( array('theme_location' => 'mobile_navigation','items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>', 'menu_class' => 'kad-mnav')); 
                  } ?>
               </div>
            </div>
          </div>   
          <?php  endif; ?> 
  </div> <!-- Close Container -->
  <?php
            if (has_nav_menu('secondary_navigation')) : ?>
  <section id="cat_nav" class="navclass">
    <div class="container">
     <nav id="nav-second" class="clearfix" role="navigation">
     <?php wp_nav_menu(array('theme_location' => 'secondary_navigation', 'menu_class' => 'sf-menu')); ?>
   </nav>
    </div><!--close container-->
    </section>
    <?php endif; ?> 
     <?php if (!empty($virtue['virtue_banner_upload']['url'])) { ?> <div class="container"><div class="virtue_banner"><img src="<?php echo esc_url($virtue['virtue_banner_upload']['url']); ?>" /></div></div> <?php } ?>
</header>