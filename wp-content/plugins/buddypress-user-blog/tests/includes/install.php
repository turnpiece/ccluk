<?php
/**
 * Installs BuddyPress for the purpose of the unit-tests
 *
 * @todo Reuse the init/load code in init.php
 * @todo Support MULTIBLOG
 */
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

$config_file_path = $argv[1];
$tests_dir_path = $argv[2];
$multisite = ! empty( $argv[3] );

require_once $config_file_path;
require_once $tests_dir_path . '/includes/functions.php';

require_once ABSPATH . '/wp-settings.php';

echo "Installing BP User Blog...\n";

$default_options = array(
    'enabled'           => true,
    'publish_post'      => 'on',
    'bookmark_post'     => 'on',
    'recommend_post'    => 'on',
);

update_option( 'buddyboss_sap_plugin_options', $default_options );

//here we can run script to create tables, insert some dummy data etc.