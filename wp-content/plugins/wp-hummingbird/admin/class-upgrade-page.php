<?php
/**
 * Hummingbird PRO upgrade page.
 *
 * @since 2.0.1
 * @package Hummingbird
 */

/**
 * Class WP_Hummingbird_Upgrade_Page
 */
class WP_Hummingbird_Upgrade_Page extends WP_Hummingbird_Admin_Page {

	/**
	 * Render the page (overwrites parent class method).
	 */
	public function render() {
		$settings = WP_Hummingbird_Settings::get_settings( 'settings' );
		?>
		<div class="sui-wrap<?php echo $settings['accessible_colors'] ? ' sui-color-accessible ' : ' '; ?>wrap-wp-hummingbird wrap-wp-hummingbird-page <?php echo 'wrap-' . esc_attr( $this->slug ); ?>">
			<?php $this->render_inner_content(); ?>
			<?php $this->render_footer(); ?>
		</div><!-- end container -->
		<?php
	}

}
