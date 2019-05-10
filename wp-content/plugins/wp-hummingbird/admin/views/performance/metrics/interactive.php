<?php
/**
 * Time to Interactive audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 * @var string   $url    URL to Performance audits page.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p>
	<?php
	printf(
		/* translators: %1$s - <strong>, %2$s - </strong> */
		esc_html__( "Time to interactive (TTI) is the amount of time it takes for your page to become %1\$sfully%2\$s interactive, which requires: 1) the useful content of a page is visible, and 2) the page responds to user interactions within 50ms. In layman’s terms, it's the time it takes for a user to be able to scroll the page, click a button or type text into an input field without the page lagging.", 'wphb' ),
		'<strong>',
		'</strong>'
	);
	?>
</p>

<h4><?php esc_html_e( 'Status', 'wphb' ); ?></h4>
<?php if ( isset( $audit->errorMessage ) && ! isset( $audit->score ) ) : ?>
	<div class="sui-notice sui-notice-error">
		<p>
			<?php
			printf(
				/* translators: %s - error message */
				esc_html__( 'Error: %s', 'wphb' ),
				esc_html( $audit->errorMessage )
			);
			?>
		</p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $audit->score * 100 ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %s - number of seconds */
				esc_html__( 'Time to Interactive for your website is %s.', 'wphb' ),
				esc_html( $audit->displayValue )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<h4><?php esc_html_e( 'Recommendations', 'wphb' ); ?></h4>
<p><?php esc_html_e( 'To improve your TTI, remove unnecessary JavaScript work occurring during page load. Following are the recommendations to serve the JavaScript efficiently:', 'wphb' ); ?></p>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> JavaScript execution time, %2$s - </a> */
			esc_html__( 'Optimizing JavaScript bootup helps reduce the JavaScript work on the page load. The %1$sJavaScript execution time%2$s audit measures the JavaScript bootup time of your page and helps you improve the score.', 'wphb' ),
			'<a href="' . esc_url( $url . '#bootup-time' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Minimize main-thread work, %2$s - </a> */
			esc_html__( 'The browser generally spends the most time on parsing, compiling, and executing your JavaScript in your main-thread. Minimizing your main-thread work can help your page become interactive faster. Refer to the %1$sMinimize main-thread work%2$s audit for recommendations on optimizing your main-thread.', 'wphb' ),
			'<a href="' . esc_url( $url . '#mainthread-work-breakdown' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Preload key requests, %2$s - <a> Preconnect to required origins, %3$s - </a> */
			esc_html__( '%1$sPreload key requests%3$s and %2$sPreconnect to required origins%3$s help you efficiently load third-party JavaScripts.', 'wphb' ),
			'<a href="' . esc_url( $url . '#uses-rel-preload' ) . '">',
			'<a href="' . esc_url( $url . '#uses-rel-preconnect' ) . '">',
			'</a>'
		);
		?>
	</li>
</ol>
