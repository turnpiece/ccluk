<?php

/**
 * Google Analytics tracking code.
 *
 * @var string $network_tracking_code Tracking code.
 * @var bool   $network_anonymize     Is anonymize IP enabled.
 * @var bool   $network_advertising   Is advertising enabled?.
 * @var string $tracking_code         Tracking code.
 * @var bool   $anonymize             Is anonymize IP enabled.
 * @var bool   $advertising           Is advertising enabled?.
 *
 * @since 3.2.0
 */

defined( 'WPINC' ) || die();

?>

<?php if ( beehive_analytics()->is_pro() ) : ?>
<!-- Google Analytics tracking code output by Beehive Analytics Pro https://premium.wpmudev.org/project/google-analytics-for-wordpress-mu-sitewide-and-single-blog-solution/ -->
<?php else : ?>
<!-- Google Analytics tracking code output by Beehive Analytics – https://wordpress.org/plugins/beehive-analytics/ -->
<?php endif; ?>
<script type="text/javascript">
	( function( i, s, o, g, r, a, m ) {
		i[ 'GoogleAnalyticsObject' ] = r;
		i[ r ] = i[ r ] || function() {
			( i[ r ].q = i[ r ].q || [] ).push( arguments )
		}, i[ r ].l = 1 * new Date();
		a = s.createElement( o ),
			m = s.getElementsByTagName( o )[ 0 ];
		a.async = 1;
		a.src = g;
		m.parentNode.insertBefore( a, m )
	} )( window, document, 'script', '//www.google-analytics.com/analytics.js', 'beehive_ga' );

	function beehive_ga_track() {
<?php if ( ! empty( $network_tracking_code ) ) : // Network tracking. ?>
		beehive_ga( 'create', '<?php echo esc_html( $network_tracking_code ); ?>', 'auto' ); // Create network tracking.
<?php if ( $network_anonymize ) : ?>
		beehive_ga( 'set', 'anonymizeIp', true ); // Anonymize IP.
<?php endif; ?>
<?php if ( $network_advertising ) : ?>
		beehive_ga( 'require', 'displayfeatures' ); // Display advertising.
<?php endif; ?>
<?php
/**
 * See beehive_google_network_tracking_vars.
 *
 * @deprecated 3.2.0
 */
do_action_deprecated(
	'ga_plus_network_tracking_code_add_vars',
	[],
	'3.2.0',
	'beehive_google_network_tracking_vars'
);
/**
 * Action hook to add something in GA network tracking.
 *
 * @since 3.2.0
 */
do_action( 'beehive_google_network_tracking_vars' );
?>
		beehive_ga( 'send', 'pageview' ); // Send pageview.
<?php endif; ?>
<?php if ( ! empty( $tracking_code ) ) : // Sub site tracking. ?>
		beehive_ga( 'create', '<?php echo esc_html( $tracking_code ); ?>', 'auto', { 'name': 'single' } ); // Create single site tracking.
<?php if ( $anonymize ) : ?>
		beehive_ga( 'single.set', 'anonymizeIp', true ); // Anonymize IP.
<?php endif; ?>
<?php if ( $advertising ) : ?>
		beehive_ga( 'single.require', 'displayfeatures' ); // Display advertising.
<?php endif; ?>
<?php
/**
 * See beehive_google_tracking_vars.
 *
 * @deprecated 3.2.0
 */
do_action_deprecated(
	'ga_plus_site_tracking_code_add_vars',
	[ 'b' ],
	'3.2.0',
	'beehive_google_tracking_vars'
);
/**
 * Action hook to add something in GA single tracking.
 *
 * @since 3.2.0
 */
do_action( 'beehive_google_tracking_vars' );
?>
		beehive_ga( 'single.send', 'pageview' ); // Send pageview.
<?php endif; ?>
	}

<?php
/**
 * See beehive_google_load_tracking.
 *
 * @deprecated 3.2.0
 */
$load_tracking = apply_filters_deprecated(
	'ga_load_tracking',
		[ true ],
	'3.2.0',
	'beehive_google_load_tracking'
);
/**
 * Filter hook to disable auto loading of tracking.
 *
 * @param bool $load_tracking Should load?.
 *
 * @since 3.2.0
 */
$load_tracking = apply_filters( 'beehive_google_load_tracking', $load_tracking );
?>
<?php if ( $load_tracking ) : ?>
	beehive_ga_track(); // Load tracking.
<?php endif; ?>
</script>
<!-- End Google Analytics -->