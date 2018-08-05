<?php

if ( 'snapshot/snapshot.php' !== WP_UNINSTALL_PLUGIN ) {
	return;
}

if ( ! isset( $wpmudev_snapshot ) ) {
	include dirname( __FILE__ ) . '/snapshot.php';
	$wpmudev_snapshot = WPMUDEVSnapshot::instance();
}

$wpmudev_snapshot->uninstall_snapshot();