<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 ?>
		<div id="content" class="container">
   		<div class="row">
      <div class="main <?php echo kadence_main_class(); ?>" role="main">
      <?php do_action( 'woocommerce_before_main_content' ); ?>

      	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			
			<div class="page-header">

				<?php woocommerce_catalog_ordering(); ?>
				<h1 class="page-title"><?php woocommerce_page_title(); ?></h1>
				<?php woocommerce_result_count(); ?>
				</div>
		
		<?php endif; ?>

		<div class="clearfix">
		<?php do_action( 'woocommerce_archive_description' ); ?>
		</div>

		<?php if ( have_posts() ) : ?>

			<?php
				/**
				 * woocommerce_before_shop_loop hook
				 * @hooked woocommerce_result_count - 20
				 * @hooked woocommerce_catalog_ordering - 30
				 */
				do_action( 'woocommerce_before_shop_loop' );
			?>

			<div class="clearfix rowtight product_category_padding"> <?php woocommerce_product_subcategories(); ?> </div>
	  		
			<?php woocommerce_product_loop_start(); ?>

				<?php while ( have_posts() ) : the_post(); ?>

					<?php woocommerce_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			<?php woocommerce_product_loop_end(); ?>

			<?php
				/**
				 * woocommerce_after_shop_loop hook
				 *
				 * @hooked woocommerce_pagination - 10
				 */
				do_action( 'woocommerce_after_shop_loop' );
			?>

		<?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

			<?php woocommerce_get_template( 'loop/no-products-found.php' ); ?>

		<?php endif; ?>
		<?php do_action( 'woocommerce_after_main_content' ); ?>
	</div>