<?php
/**
 * The widget admin form.
 *
 * @var WP_Widget $widget Widget class instance.
 * @var int       $number No. of items.
 * @var string    $title  Widget title.
 *
 * @package Beehive
 */

defined( 'WPINC' ) || die();

?>

<div class="widget-content">
	<p>
		<label for="<?php echo esc_html( $widget->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'ga_trans' ); ?></label>
		<input id="<?php echo esc_html( $widget->get_field_id( 'title' ) ); ?>" name="<?php echo esc_html( $widget->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $title ); ?>"/>
	</p>
	<p>
		<label for="<?php echo esc_html( $widget->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Maximum number of posts to show:', 'ga_trans' ); ?></label>
		<input id="<?php echo esc_html( $widget->get_field_id( 'number' ) ); ?>" name="<?php echo esc_html( $widget->get_field_name( 'number' ) ); ?>" type="number" min="1" max="10" value="<?php echo intval( $number ); ?>" size="3">
	</p>
</div>