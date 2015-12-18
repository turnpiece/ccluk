<?php
/**
 * Welcome screen intro template
 */
?>
<?php
$politics = wp_get_theme( 'politics' );

?>
<div class="theme-intro">

	<div class="intro-text">
		<h1 style="margin-right: 0;"><?php echo '<strong>Politics</strong> <sup>' . esc_attr( $politics['Version'] ) . '</sup>'; ?></h1>
		<p style="font-size: 1.2em;"><?php _e( 'Thanks for using the Politics theme! This info page should help you get started and serve as a handy reference area.', 'politics' ); ?></p>
		<p><?php _e( 'Politics is the most elegant and solidly built political WordPress theme available. It will help launch your online presence quickly so you can focus on your campaign and mission.', 'politics' ); ?></p>
	</div><!-- .intro-text -->

	<div class="theme-screenshot">
		<img src="<?php echo esc_url( get_template_directory_uri() ) . '/screenshot.png'; ?>" alt="politics" class="image-50" width="440" />
	</div><!-- .theme-screenshot -->

</div><!-- .theme-intro -->
