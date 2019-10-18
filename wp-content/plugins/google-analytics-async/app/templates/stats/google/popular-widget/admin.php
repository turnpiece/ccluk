<?php

/**
 * The widget admin form.
 *
 * @var WP_Widget $widget Widget class instance.
 * @var int       $number No. of items.
 * @var string    $title  Widget title.
 */

defined( 'WPINC' ) || die();

?>

<div class="widget-content">
	<p>
		<label for="<?php echo $widget->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'ga_trans' ); ?></label>
		<input id="<?php echo $widget->get_field_id( 'title' ); ?>" name="<?php echo $widget->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
	</p>
	<p>
		<label for="<?php echo $widget->get_field_id( 'number' ); ?>"><?php esc_html_e( 'Maximum number of posts to show:', 'ga_trans' ) ?></label>
		<input id="<?php echo $widget->get_field_id( 'number' ); ?>" name="<?php echo $widget->get_field_name( 'number' ); ?>" type="number" min="1" max="10" value="<?php echo $number; ?>" size="3">
	</p>
</div>