<?php
/**
 * External product add to cart
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>
<?php do_action('woocommerce_before_add_to_cart_button'); ?>

<p class="cart">
	<a href="<?php echo esc_url( $product_url ); ?>" rel="nofollow" class="kad_add_to_cart headerfont kad-btn kad-btn-primary button alt"><?php echo $button_text; ?></a>
</p>

<?php do_action('woocommerce_after_add_to_cart_button'); ?>