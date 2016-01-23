<?php 
/*-----------------------------------------------------------------------------------*/
/* This theme supports WooCommerce */
/*-----------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
  add_theme_support( 'woocommerce' );
}
/*-----------------------------------------------------------------------------------*/
/* WooCommerce Functions */
/*-----------------------------------------------------------------------------------*/

if (class_exists('woocommerce')) {

  // Disable WooCommerce styles
  if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
  add_filter( 'woocommerce_enqueue_styles', '__return_false' );
  } else {
    define( 'WOOCOMMERCE_USE_CSS', false );
  }
  // Disable WooCommerce Lightbox
  update_option( 'woocommerce_enable_lightbox', false );
    
}
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('kt_afterheader', 'kt_wc_print_notices');
function kt_wc_print_notices() {
  if (class_exists('woocommerce')) {
    if(!is_shop() and !is_woocommerce() and !is_cart() and !is_checkout() and !is_account_page() ) {
      echo '<div class="container">';
      echo do_shortcode( '[woocommerce_messages]' );
      echo '</div>';
    }
  }
}

// Set the number of columns to 3
function kad_woocommerce_cross_sells_columns( $columns ) {
  return 3;
}
add_filter( 'woocommerce_cross_sells_columns', 'kad_woocommerce_cross_sells_columns', 10, 1 );

// Limit the number of cross sells displayed to a maximum of 3
function kad_woocommerce_cross_sells_total( $limit ) {
  return 3;
}
add_filter( 'woocommerce_cross_sells_total', 'kad_woocommerce_cross_sells_total', 10, 1 );

// Redefine woocommerce_output_related_products()
function kadence_woo_related_products_limit() {
  global $product, $woocommerce;
  $related = $product->get_related();
  $args = array(
    'post_type'           => 'product',
    'no_found_rows'       => 1,
    'posts_per_page'      => 4,
    'ignore_sticky_posts'   => 1,
    //'orderby'               => $orderby,
    'post__in'              => $related,
    'post__not_in'          => array($product->id)
  );
  return $args;
}
add_filter( 'woocommerce_related_products_args', 'kadence_woo_related_products_limit' );
// Number of products per page
add_filter('loop_shop_per_page', 'kadence_wooframework_products_per_page');
if (!function_exists('kadence_wooframework_products_per_page')) {
  function kadence_wooframework_products_per_page() {
    global $virtue;
    if ( isset( $virtue['products_per_page'] ) ) {
      return $virtue['products_per_page'];
    }
  }
}

// Display product tabs?
add_action('wp_head','kadence_wooframework_tab_check');
if ( ! function_exists( 'kadence_wooframework_tab_check' ) ) {
  function kadence_wooframework_tab_check() {
    global $virtue;
    if ( isset( $virtue[ 'product_tabs' ] ) && $virtue[ 'product_tabs' ] == "0" ) {
      remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    }
  }
}

// Display related products?
add_action('wp_head','kadence_wooframework_related_products');
if ( ! function_exists( 'kadence_wooframework_related_products' ) ) {
  function kadence_wooframework_related_products() {
    global $virtue;
    if ( isset( $virtue[ 'related_products' ] ) && $virtue[ 'related_products' ] == "0" ) {
      remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    }
  }
}

add_filter('add_to_cart_fragments', 'kadence_woocommerce_header_add_to_cart_fragment');
function kadence_woocommerce_header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;
    ob_start(); ?>

    <a class="cart-contents" href="<?php echo esc_url($woocommerce->cart->get_cart_url()); ?>" title="<?php esc_attr_e('View your shopping cart', 'virtue'); ?>">
        <i class="icon-shopping-cart" style="padding-right:5px;"></i>  <?php _e('Your Cart', 'virtue');?> <span class="kad-cart-dash">-</span> <?php echo $woocommerce->cart->get_cart_total(); ?>
    </a>
    <?php
    $fragments['a.cart-contents'] = ob_get_clean();
    return $fragments;
}


remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
add_action( 'woocommerce_shop_loop_item_title', 'kt_woocommerce_template_loop_product_title', 10);
function kt_woocommerce_template_loop_product_title() {
  echo '<h5>'.get_the_title().'</h5>';
}



remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
add_action( 'woocommerce_single_variation', 'kt_woocommerce_single_variation', 10 );
add_action( 'woocommerce_single_variation', 'kt_woocommerce_single_variation_add_to_cart_button', 20 );

if ( ! function_exists( 'kt_woocommerce_single_variation_add_to_cart_button' ) ) {
  /**
   * Output the add to cart button for variations.
   */
  function kt_woocommerce_single_variation_add_to_cart_button() {
    global $product;
    ?>
    <div class="variations_button">
      <?php woocommerce_quantity_input( array( 'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1 ) ); ?>
      <button type="submit" class="kad_add_to_cart headerfont kad-btn kad-btn-primary single_add_to_cart_button"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
      <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->id ); ?>" />
      <input type="hidden" name="product_id" value="<?php echo absint( $product->id ); ?>" />
      <input type="hidden" name="variation_id" class="variation_id" value="" />
    </div>
    <?php
  }
}

if ( ! function_exists( 'kt_woocommerce_single_variation' ) ) {
  /**
   * Output placeholders for the single variation.
   */
  function kt_woocommerce_single_variation() {
    echo '<div class="single_variation headerfont"></div>';
  }
}

// Shop Page Image
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'woocommerce_before_shop_loop_item_title', 'kt_woocommerce_template_loop_product_thumbnail', 10 );
function kt_woocommerce_template_loop_product_thumbnail() {
  global $virtue, $woocommerce_loop, $post;

  // Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) )
  $woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );

if ($woocommerce_loop['columns'] == '3'){ 
  $productimgwidth = 365;
} else {
  $productimgwidth = 268;
}


  if(isset($virtue['product_img_resize']) && $virtue['product_img_resize'] == 0) {
    $resizeimage = 0;
  } else {
    $resizeimage = 1;
  }

      if($resizeimage == 1) { 
          if ( has_post_thumbnail() ) {
          $product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); 
          $product_image_url = $product_image[0]; 
          $image_product = aq_resize($product_image_url, $productimgwidth, $productimgwidth, true);
                if(empty($image_product)) {$image_product = $product_image_url;} ?> 
                 <img width="<?php echo $productimgwidth;?>" height="<?php echo esc_attr($productimgwidth);?>" src="<?php echo esc_attr($image_product);?>" class="attachment-shop_catalog wp-post-image" alt="<?php the_title();?>">
                 <?php } elseif ( woocommerce_placeholder_img_src() ) {
                 echo woocommerce_placeholder_img( 'shop_catalog' );
                 }  
      } else { 
        echo '<div class="kad-woo-image-size">';
        echo woocommerce_template_loop_product_thumbnail();
        echo '</div>';
         }
  }

  remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 5 );

remove_action( 'woocommerce_before_subcategory_title', 'woocommerce_subcategory_thumbnail', 10 );

remove_action( 'woocommerce_shop_loop_subcategory_title', 'woocommerce_template_loop_category_title', 10 );
add_action( 'woocommerce_shop_loop_subcategory_title', 'kt_woocommerce_template_loop_category_title', 10 );

 function kt_woocommerce_template_loop_category_title( $category ) {
        ?>
        <h5>
            <?php
                echo $category->name;

                if ( $category->count > 0 )
                    echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category );
            ?>
        </h5>
        <?php
    }



// define the woocommerce_loop_add_to_cart_link callback
function kt_filter_woocommerce_loop_add_to_cart_link( $html, $product ) {
    $html = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button kad-btn headerfont kad_add_to_cart %s product_type_%s">%s</a>',
      esc_url( $product->add_to_cart_url() ),
      esc_attr( $product->id ),
      esc_attr( $product->get_sku() ),
      $product->is_purchasable() ? 'add_to_cart_button' : '',
      esc_attr( $product->product_type ),
      esc_html( $product->add_to_cart_text() )
    );
    return $html;
}   
// add the filter
add_filter( 'woocommerce_loop_add_to_cart_link', 'kt_filter_woocommerce_loop_add_to_cart_link', 10, 2 );

remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
remove_action( 'woocommerce_after_subcategory', 'woocommerce_template_loop_category_link_close', 10 );
add_action( 'woocommerce_before_subcategory', 'kt_woocommerce_template_loop_category_link_open', 10 );
add_action( 'woocommerce_after_subcategory', 'kt_woocommerce_template_loop_category_link_close', 10 );

function kt_woocommerce_template_loop_category_link_open( $category ) {
    echo '<a href="' . get_term_link( $category->slug, 'product_cat' ) . '">';
}
function kt_woocommerce_template_loop_category_link_close() {
    echo '</a>';
}
