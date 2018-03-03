<?php 
$wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';
$wp_test_config_path = "";
if ( file_exists( $wp_tests_dir . '/wp-tests-config.php' ) ) {
	$wp_test_config_path = $wp_tests_dir . '/wp-tests-config.php';
} elseif ( file_exists( dirname( dirname( $wp_tests_dir ) ) . '/wp-tests-config.php' ) ) {
	$wp_test_config_path = dirname( dirname( $wp_tests_dir ) ) . '/wp-tests-config.php';
}

$multisite = (int) ( defined( 'WP_TESTS_MULTISITE') && WP_TESTS_MULTISITE );
system( WP_PHP_BINARY . ' ' . escapeshellarg( dirname( __FILE__ ) . '/install.php' ) . ' ' . escapeshellarg( $wp_test_config_path ) . ' ' . escapeshellarg( $wp_tests_dir ) . ' ' . $multisite );