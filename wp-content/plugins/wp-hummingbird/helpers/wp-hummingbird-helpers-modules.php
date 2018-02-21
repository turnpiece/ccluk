<?php

/**
 * Return the list of modules and their object instances
 *
 * Do not try to load before 'wp_hummingbird_loaded' action has been executed
 *
 * @return mixed
 */
function wphb_get_modules() {
	/* @var WP_Hummingbird $hummingbird */
	$hummingbird = WP_Hummingbird::get_instance();
	return $hummingbird->core->modules;
}

/**
 * Get a module instance
 *
 * @param string $module Module slug.
 *
 * @return WP_Hummingbird_Module|bool
 */
function wphb_get_module( $module ) {
	/* @var WP_Hummingbird $hummingbird */
	$hummingbird = WP_Hummingbird::get_instance();
	return isset( $hummingbird->core->modules[ $module ] ) ? $hummingbird->core->modules[ $module ] : false;
}

/**
 * Check php version compatibility.
 *
 * Minification requires at least php version 5.3 to work.
 *
 * @since  1.6.0
 * @return bool
 */
function wphb_can_execute_php() {
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		return false;
	}
	return true;
}

/**
 * Clear minified group files
 */
function wphb_minification_clear_files() {
	$groups = WP_Hummingbird_Module_Minify_Group::get_minify_groups();

	foreach ( $groups as $group ) {
		// This will also delete the file. See WP_Hummingbird_Module_Minify::on_delete_post().
		wp_delete_post( $group->ID );
	}

	wp_cache_delete( 'wphb_minify_groups' );
}

/**
 * Get all resources collected
 *
 * This collection is displayed in minification admin page
 */
function wphb_minification_get_resources_collection() {
	$collection = WP_Hummingbird_Sources_Collector::get_collection();
	$posts = WP_Hummingbird_Module_Minify_Group::get_minify_groups();
	foreach ( $posts as $post ) {
		$group = WP_Hummingbird_Module_Minify_Group::get_instance_by_post_id( $post->ID );
		if ( ! $group ) {
			continue;
		}
		foreach ( $group->get_handles() as $handle ) {
			if ( isset( $collection[ $group->type ][ $handle ] ) ) {
				$collection[ $group->type ][ $handle ]['original_size'] = $group->get_handle_original_size( $handle );
				$collection[ $group->type ][ $handle ]['compressed_size'] = $group->get_handle_compressed_size( $handle );
			}
		}
	}

	return $collection;
}


/**
 * Wrapper function for WP_Hummingbird_Module_Minify::init_scan()
 */
function wphb_minification_init_scan() {
	/* @var WP_Hummingbird_Module_Minify $minify_module */
	$minify_module = wphb_get_module( 'minify' );
	$minify_module->clear_cache( false );

	// Activate minification if is not.
	wphb_toggle_minification( true );

	// Init scan.
	$minify_module->scanner->init_scan();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Scanner::get_current_scan_step()
 *
 * @return bool
 */
function wphb_minification_get_current_scan_step() {
	if ( wphb_can_execute_php() ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );

		return $minify_module->scanner->get_current_scan_step();
	}
	return false;
}

/**
 * Wrapper function for WP_Hummingbird_Module_Scanner::is_scanning()
 *
 * @return bool
 */
function wphb_minification_is_scanning_files() {
	if ( wphb_can_execute_php() ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );

		return $minify_module->scanner->is_scanning();
	}
	return false;
}

/**
 * Wrapper function for WP_Hummingbird_Module_Scanner::is_files_scanned()
 *
 * @return bool
 */
function wphb_minification_is_scan_finished() {
	if ( wphb_can_execute_php() ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );

		return $minify_module->scanner->is_files_scanned();
	}
	return false;
}

/**
 * If minification scan hasn't finished after 4 minutes, stop it
 */
function wphb_minification_maybe_stop_scanning_files() {
	if ( ! wphb_minification_is_scanning_files() ) {
		/* @var WP_Hummingbird_Module_Minify $minify_module */
		$minify_module = wphb_get_module( 'minify' );
		$minify_module->scanner->finish_scan();
	}
}

/**
 * Update the current scan step
 *
 * @param int $step  Step number.
 */
function wphb_minification_update_scan_step( $step ) {
	/* @var WP_Hummingbird_Module_Minify $minify_module */
	$minify_module = wphb_get_module( 'minify' );
	$minify_module->scanner->update_current_step( $step );
}

/**
 * Wrapper function for WP_Hummingbird_Module_Minify::get_scan_steps()
 *
 * @return int
 */
function wphb_minification_get_scan_steps_number() {
	return WP_Hummingbird_Module_Minify_Scanner::get_scan_steps();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Minify::get_scan_urls()
 *
 * @return array
 */
function wphb_minification_get_scan_urls() {
	return WP_Hummingbird_Module_Minify_Scanner::get_scan_urls();
}

/**
 * Scan URL.
 *
 * @param  string $url  URL to send the request to.
 * @return array
 */
function wphb_minification_scan_url( $url ) {
	return WP_Hummingbird_Module_Minify_Scanner::scan_url( $url );
}

/**
 * Return the number of files used by minification.
 *
 * @since 1.4.5
 */
function wphb_minification_files_count() {
	// Get files count.
	$collection = wphb_minification_get_resources_collection();
	// Remove those assets that we don't want to display.
	foreach ( $collection['styles'] as $key => $item ) {
		if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
			unset( $collection['styles'][ $key ] );
		}
	}
	foreach ( $collection['scripts'] as $key => $item ) {
		if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
			unset( $collection['scripts'][ $key ] );
		}
	}

	return ( count( $collection['scripts'] ) + count( $collection['styles'] ) );
}

function wphb_minification_optimizied_count() {
	// Get files count.
	$collection = wphb_minification_get_resources_collection();
	// Remove those assets that we don't want to display and that are not optimized (minified).
	foreach ( $collection['styles'] as $key => $item ) {
		if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'styles' ) ) {
			unset( $collection['styles'][ $key ] );
		}
		if ( ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
			unset( $collection['styles'][ $key ] );
		}
	}
	foreach ( $collection['scripts'] as $key => $item ) {
		if ( ! apply_filters( 'wphb_minification_display_enqueued_file', true, $item, 'scripts' ) ) {
			unset( $collection['scripts'][ $key ] );
		}
		if ( ! preg_match( '/\.min\.(css|js)/', basename( $item['src'] ) ) ) {
			unset( $collection['scripts'][ $key ] );
		}
	}

	return ( count( $collection['scripts'] ) + count( $collection['styles'] ) );
}

/**
 * Get the Gzip status data
 *
 * @return array
 */
function wphb_get_gzip_status() {
	/* @var WP_Hummingbird_Module_GZip $gzip_module */
	$gzip_module = wphb_get_module( 'gzip' );

	return $gzip_module->status;
}

/**
 * Get the Caching status data
 *
 * @return array
 */
function wphb_get_caching_status() {
	/* @var WP_Hummingbird_Module_Caching $caching_module */
	$caching_module = wphb_get_module( 'caching' );

	return $caching_module->status;
}

/**
 * Get caching/gzip data from the api.
 *
 * @since 1.7.0
 * @param string $module  Accepts: caching, gzip.
 */
function wphb_get_status_from_api( $module ) {
	$do_api = false;

	if ( 'caching' === $module ) {
		$do_api = true;
		/* @var WP_Hummingbird_Module_Caching $module */
		$module = wphb_get_module( 'caching' );
	}

	if ( 'gzip' === $module ) {
		$do_api = true;
		/* @var WP_Hummingbird_Module_GZip $module */
		$module = wphb_get_module( 'gzip' );
	}

	if ( $do_api ) {
		$module->get_analysis_data( true, true );
	}
}

/**
 * Get the number of issues for selected module
 *
 * @param string $module Module name.
 *
 * @return int
 */
function wphb_get_number_of_issues( $module ) {
	$issues = 0;

	switch ( $module ) {
		case 'caching':
			$caching_status = wphb_get_caching_status();

			$recommended = wphb_get_recommended_caching_values();
			if ( ! $caching_status ) {
				break;
			}

			foreach ( $caching_status as $type => $value ) {
				if ( empty( $value ) || ( $recommended[ $type ]['value'] > $value ) ) {
					$issues++;
				}
			}
			break;
		case 'gzip':
			$gzip_status = wphb_get_gzip_status();

			if ( ! $gzip_status ) {
				break;
			}

			$issues = count( $gzip_status ) - count( array_filter( $gzip_status ) );
			break;
		case 'performance':
			$last_report = wphb_performance_get_last_report();
			if ( ! $last_report || is_wp_error( $last_report ) ) {
				break;
			}
			$last_report = $last_report->data;
			foreach ( $last_report->rule_result as $recommendation ) {
				if ( 'a' !== $recommendation->impact_score_class ) {
					$issues++;
				}
			}
			break;
	} // End switch().

	return $issues;
}

/**
 * Get uptime last report.
 *
 * @param string $time   Report period.
 * @param bool   $force  Force status refresh.
 *
 * @return bool|WP_Error
 */
function wphb_uptime_get_last_report( $time = 'week', $force = false ) {
	/* @var WP_Hummingbird_Module_Uptime $uptime_module */
	$uptime_module = wphb_get_module( 'uptime' );
	return $uptime_module->get_last_report( $time, $force );
}

/**
 * Enable Uptime locally
 */
function wphb_uptime_enable_locally() {
	/* @var WP_Hummingbird_Module_Uptime $uptime */
	$uptime = wphb_get_module( 'uptime' );
	if ( $uptime ) {
		WP_Hummingbird_Module_Uptime::enable_locally();
	}
}

/**
 * Disable Uptime locally
 */
function wphb_uptime_disable_locally() {
	/* @var WP_Hummingbird_Module_Uptime $uptime */
	$uptime = wphb_get_module( 'uptime' );
	if ( $uptime ) {
		WP_Hummingbird_Module_Uptime::disable_locally();
	}
}

/**
 * Check if Smush plugin is activated
 *
 * @return boolean
 */
function wphb_smush_is_smush_active() {
	if ( ! wphb_smush_is_smush_installed() ) {
		return false;
	}

	return WP_Hummingbird_Module_Smush::is_smush_active();
}

/**
 * Check if Smush plugin is installed
 *
 * @return boolean
 */
function wphb_smush_is_smush_installed() {
	return WP_Hummingbird_Module_Smush::is_smush_installed();
}

/**
 * Get Smush install url.
 *
 * @return string
 */
function wphb_smush_get_install_url() {
	return WP_Hummingbird_Module_Smush::get_smush_install_url();
}

/**
 * Check if Cloudflare enabled.
 *
 * @param bool $force  Force new check.
 */
function wphb_has_cloudflare( $force = false ) {
	WP_Hummingbird_Module_Cloudflare::has_cloudflare( $force );
}

/**
 * Check if Cloudflare is active
 *
 * @return bool
 *
 * @since 1.5.0
 */
function wphb_cloudflare_is_active() {
	/* @var WP_Hummingbird_Module_Cloudflare $cf_module */
	$cf_module = wphb_get_module( 'cloudflare' );
	$cf_active = false;
	if ( $cf_module->is_connected() && $cf_module->is_zone_selected() ) {
		$cf_active = true;
	}

	return $cf_active;
}

/**
 * Check if Cloudflare is disconnected.
 */
function wphb_cloudflare_disconnect() {
	/* @var WP_Hummingbird_Module_Cloudflare $cloudflare */
	$cloudflare = wphb_get_module( 'cloudflare' );
	$settings = wphb_get_settings();
	$cloudflare->clear_caching_page_rules();

	$settings['cloudflare-email'] = '';
	$settings['cloudflare-api-key'] = '';
	$settings['cloudflare-zone'] = '';
	$settings['cloudflare-zone-name'] = '';
	$settings['cloudflare-connected'] = false;
	$settings['cloudflare-plan'] = '';
	wphb_update_settings( $settings );
}

/**
 * Remove quick setup
 *
 * @since 1.5.0
 */
function wphb_remove_quick_setup() {
	$quick_setup = get_option( 'wphb-quick-setup' );
	$quick_setup['finished'] = true;
	update_option( 'wphb-quick-setup', $quick_setup );
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::get_last_report()
 *
 * @return bool|mixed
 */
function wphb_performance_get_last_report() {
	return WP_Hummingbird_Module_Performance::get_last_report();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::refresh_report()
 */
function wphb_performance_refresh_report() {
	WP_Hummingbird_Module_Performance::refresh_report();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::is_doing_report()
 *
 * @return bool|mixed
 */
function wphb_performance_is_doing_report() {
	return WP_Hummingbird_Module_Performance::is_doing_report();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::stopped_report()
 *
 * @return mixed
 */
function wphb_performance_stopped_report() {
	return WP_Hummingbird_Module_Performance::stopped_report();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::set_doing_report()
 *
 * @param bool $status  If set to true, it will start a new Performance Report, otherwise it will stop the current one.
 */
function wphb_performance_set_doing_report( $status = true ) {
	WP_Hummingbird_Module_Performance::set_doing_report( $status );
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::cron_scan()
 */
function wphb_performance_cron_report() {
	return WP_Hummingbird_Module_Performance::cron_scan();
}

/**
 * Return human readable names of active modules that have a cache.
 *
 * Checks Page, Gravatar & Minification.
 *
 * @return array
 */
function wphb_get_active_cache_modules() {
	$modules = array(
		'page-caching' => __( 'Page', 'wphb' ),
		'cloudflare'   => __( 'CloudFlare', 'wphb' ),
		'gravatar'     => __( 'Gravatar', 'wphb' ),
		'minify'       => __( 'Minification', 'wphb' ),
	);

	// Remove minification module where php is not supported.
	if ( ! wphb_can_execute_php() ) {
		unset( $modules['minify'] );
	}

	$active_modules = array();

	foreach ( $modules as $module => $module_name ) {
		$mod = wphb_get_module( $module );

		// If inactive, skip to next step.
		if ( ! $mod->is_active() && 'cloudflare' !== $module ) {
			continue;
		}

		// Fix CloudFlare clear cache appearing on dashboard if it had been previously enabled but then uninstalled and reinstalled HB.
		if ( 'cloudflare' === $module && ! $mod->is_connected() && ! $mod->is_zone_selected() ) {
			continue;
		}

		$active_modules[] = $module_name;
	}
	return $active_modules;
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::set_report_dismissed()
 */
function wphb_performance_set_report_dismissed() {
	WP_Hummingbird_Module_Performance::set_report_dismissed();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::remove_report_dismissed()
 */
function wphb_performance_remove_report_dismissed() {
	WP_Hummingbird_Module_Performance::remove_report_dismissed();
}

/**
 * Wrapper function for WP_Hummingbird_Module_Performance::report_dismissed()
 *
 * @return bool
 */
function wphb_performance_report_dismissed() {
	return WP_Hummingbird_Module_Performance::report_dismissed();
}

/**
 * Get default caching types for HB or CloudFlare.
 *
 * @since 1.7.1
 * @return array
 */
function wphb_get_browser_caching_types() {
	$caching_types = array();
	$caching_types['javascript']     = 'txt | xml | js';
	$caching_types['css']            = 'css';
	$caching_types['media']          = 'flv | ico | pdf | avi | mov | ppt | doc | mp3 | wmv | wav | mp4 | m4v | ogg | webm | aac | eot | ttf | otf | woff | svg';
	$caching_types['images']         = 'jpg | jpeg | png | gif | swf | webp';
	$caching_types['cloudflare']     = 'bmp | pict | csv | pls | tif | tiff | eps | ejs | midi | mid | woff2 | svgz | docx | xlsx | xls | pptx | ps | class | jar';
	return $caching_types;
}