<?php

/**
 * Customizer additions.
 *
 * @package No Sidebar Pro
 * @author  StudioPress
 * @link    http://my.studiopress.com/themes/no-sidebar/
 * @license GPL2-0+
 */

/**
 * Get default link color for Customizer.
 *
 * Abstracted here since at least two functions use it.
 *
 * @since 1.0.0
 *
 * @return string Hex color code for link color.
 */
function ns_customizer_get_default_link_color() {
	return '#ee2324';
}

/**
 * Get default accent color for Customizer.
 *
 * Abstracted here since at least two functions use it.
 *
 * @since 1.0.0
 *
 * @return string Hex color code for accent color.
 */
 
function ns_customizer_get_default_accent_color() {
	return '#34313b';
}

add_action( 'customize_register', 'ns_customizer_register' );
/**
 * Register settings and controls with the Customizer.
 *
 * @since 1.0.0
 * 
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function ns_customizer_register() {

	global $wp_customize;

	$wp_customize->add_setting(
		'ns_link_color',
		array(
			'default' => ns_customizer_get_default_link_color(),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'ns_link_color',
			array(
				'description' => __( 'Change the default color for links.', 'no-sidebar' ),
			    'label'       => __( 'Link Color', 'no-sidebar' ),
			    'section'     => 'colors',
			    'settings'    => 'ns_link_color',
			)
		)
	);

	$wp_customize->add_setting(
		'ns_accent_color',
		array(
			'default' => ns_customizer_get_default_accent_color(),
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'ns_accent_color',
			array(
				'description' => __( 'Change the default color for button hovers.', 'no-sidebar' ),
			    'label'       => __( 'Accent Color', 'no-sidebar' ),
			    'section'     => 'colors',
			    'settings'    => 'ns_accent_color',
			)
		)
	);

}
