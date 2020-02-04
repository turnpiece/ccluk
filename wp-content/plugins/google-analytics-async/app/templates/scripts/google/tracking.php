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

/**
 * Filter hook to change the function name in GA script.
 *
 * @param string $ga_tag Ga tag name.
 *
 * @since 3.2.0
 */
$ga_tag = apply_filters( 'beehive_google_analytics_function_name', 'beehive_ga' );

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
	} )( window, document, 'script', '//www.google-analytics.com/analytics.js', '<?php echo esc_attr( $ga_tag ); ?>' );

	function beehive_ga_track() {
<?php if ( ! empty( $network_tracking_code ) ) : // Network tracking. ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'create', '<?php echo esc_html( $network_tracking_code ); ?>', 'auto' ); // Create network tracking.
<?php if ( $network_anonymize ) : ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'set', 'anonymizeIp', true ); // Anonymize IP.
<?php endif; ?>
<?php if ( $network_advertising ) : ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'require', 'displayfeatures' ); // Display advertising.
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
		<?php echo esc_attr( $ga_tag ); ?>( 'send', 'pageview' ); // Send pageview.
<?php endif; ?>
<?php if ( ! empty( $tracking_code ) ) : // Sub site tracking. ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'create', '<?php echo esc_html( $tracking_code ); ?>', 'auto', { 'name': 'single' } ); // Create single site tracking.
<?php if ( $anonymize ) : ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'single.set', 'anonymizeIp', true ); // Anonymize IP.
<?php endif; ?>
<?php if ( $advertising ) : ?>
		<?php echo esc_attr( $ga_tag ); ?>( 'single.require', 'displayfeatures' ); // Display advertising.
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
		<?php echo esc_attr( $ga_tag ); ?>( 'single.send', 'pageview' ); // Send pageview.
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