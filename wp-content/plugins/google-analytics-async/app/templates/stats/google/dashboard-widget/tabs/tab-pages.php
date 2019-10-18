<?php

/**
 * Audience tab template.
 *
 * @var bool   $logged_in     Is logged in?.
 * @var bool   $network       Is network admin?.
 * @var string $settings_url  Settings page url.
 * @var bool   $can_get_stats Can view status?.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

if ( ! $logged_in && ! $can_get_stats ) : // Not logged in.

	$this->view( 'stats/google/dashboard-widget/elements/notice', [
		'id'      => 'beehive-analytics-pages-auth',
		'type'    => 'error',
		'message' => sprintf( __( 'Please, <a href="%s">authorize your account</a> to see the statistics.', 'ga_trans' ), $settings_url ),
		'dismiss' => true,
	] );

endif;

$this->view( 'stats/google/dashboard-widget/elements/table', [
	'id'      => 'analytics-pages-stats',
	'class'   => 'paginated',
	'caption' => esc_html__( 'Top 5 visited pages.', 'ga_trans' ),
	'headers' => [
		[
			'col'   => 6,
			'title' => esc_html__( 'Top Pages/most visited', 'ga_trans' ),
		],
		[
			'col'   => 2,
			'title' => esc_html__( 'Avg. time', 'ga_trans' ),
		],
		[
			'col'   => 2,
			'title' => esc_html__( 'Views', 'ga_trans' ),
		],
		[
			'col'   => 2,
			'title' => esc_html__( 'Trend', 'ga_trans' ),
		],
	],
] );