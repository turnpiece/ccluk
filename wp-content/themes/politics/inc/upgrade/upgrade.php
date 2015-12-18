<?php
/**
 * Display upgrade notice on customizer page
 */
function politics_upgrade_notice() {

	// Enqueue the script
	wp_enqueue_script(
		'politics-customizer-upgrade',
		get_template_directory_uri() . '/inc/upgrade/label.js',
		array(), '1.0',
		true
	);

	// Localize the script for main label
	wp_localize_script(
		'politics-customizer-upgrade',
		'politicsUpgrade',
		array(
			'politicsUpgradeURL'		=> esc_url( 'https://rescuethemes.com/wordpress-themes/politics-plus/' ),
			'politicsUpgradeLabel'	=> __( 'Upgrade to Politics Plus', 'politics' ),
		)
	);

	// Enqueue the script
	wp_enqueue_script(
		'politics-customizer-mini-label-upgrade',
		get_template_directory_uri() . '/inc/upgrade/minilabel.js',
		array(), '1.0',
		true
	);

	// Localize the script for mini label
	wp_localize_script(
		'politics-customizer-mini-label-upgrade',
		'politicsMiniUpgrade',
		array(
			'politicsMiniUpgradeLabel'	=> __( 'Plus', 'politics' ),
		)
	);

}
add_action( 'customize_controls_enqueue_scripts', 'politics_upgrade_notice' );
