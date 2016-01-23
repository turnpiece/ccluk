<?php
/**
 * Product Loop Start
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */
global $woocommerce, $woocommerce_loop;
if ( empty( $woocommerce_loop['columns'] ) ) $woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
  if(kadence_display_sidebar()) {
            $columns = "shopcolumn".$woocommerce_loop['columns']." shopsidebarwidth"; 
      } else {
			$columns = "shopcolumn".$woocommerce_loop['columns']." shopfullwidth"; 
      } ?>
<div id="product_wrapper" class="products kt-masonry-init rowtight <?php echo esc_attr($columns); ?>" data-masonry-selector=".kad_product">