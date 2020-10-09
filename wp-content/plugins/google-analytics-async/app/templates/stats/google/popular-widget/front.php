<?php
/**
 * The widget frontend view.
 *
 * @var WP_Widget $widget  Widget instance.
 * @var string    $content Widget content.
 *
 * @package Beehive
 */

defined( 'WPINC' ) || die();

?>

<?php if ( isset( $before_widget ) ) : ?>
	<?php echo $before_widget; // phpcs:ignore ?>
<?php endif; ?>

<?php if ( isset( $content ) ) : // Only when content set. ?>

	<div id="<?php echo esc_attr( $args['id'] ); ?>">

		<?php if ( isset( $before_title ) ) : ?>
			<?php echo $before_title; // phpcs:ignore ?>
		<?php endif; ?>

		<?php if ( isset( $widget['title'] ) ) : ?>
			<h2 class="widget-title">
				<?php
				/**
				 * Filter hook to alter the widget title.
				 *
				 * @param string $title Title.
				 *
				 * @since 1.0.0
				 */
				echo esc_html( apply_filters( 'widget_title', $widget['title'] ) );
				?>
			</h2>
		<?php endif; ?>

		<?php if ( isset( $after_title ) ) : ?>
			<?php echo $after_title; // phpcs:ignore ?>
		<?php endif; ?>

		<p class="beehive-frontend-widget">
			<?php echo $content; // phpcs:ignore ?>
		</p>
	</div>

<?php endif; ?>

<?php if ( isset( $after_widget ) ) : ?>
	<?php echo $after_widget; // phpcs:ignore ?>
<?php endif; ?>