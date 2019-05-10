<?php
/**
 * First CPU Idle audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 * @var string   $url    URL to Performance audits page.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p><?php esc_html_e( "First CPU Idle is the time when 'most' elements on your page can respond to user interactions such as clicking a button or typing text into an input field. This provides feedback to your visitors that they can start interacting with your page.", 'wphb' ); ?></p>

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
				esc_html__( 'First CPU Idle time for your website is %s.', 'wphb' ),
				esc_html( $audit->displayValue )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<h4><?php esc_html_e( 'Recommendations', 'wphb' ); ?></h4>
<p><?php esc_html_e( 'Following are the recommendations which can help improve your First CPU Idle score:', 'wphb' ); ?></p>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Eliminate render-blocking resources, %2$s - <a> Defer unused CSS, %3$s - <a> Preload key requests, %4$s - <a> Preconnect to required origins, %5$s - </a> */
			esc_html__( 'Minimize the number of render-blocking stylesheets, and efficiently load third-party javascript. Improving the audit scores, %1$sEliminate render-blocking resources%5$s, %2$sDefer unused CSS%5$s, %3$sPreload key requests%5$s and %4$sPreconnect to required origins%5$s can improve content load speeds, and hence improve your First CPU Idle score.', 'wphb' ),
			'<a href="' . esc_url( $url . '#render-blocking-resources' ) . '">',
			'<a href="' . esc_url( $url . '#unused-css-rules' ) . '">',
			'<a href="' . esc_url( $url . '#uses-rel-preload' ) . '">',
			'<a href="' . esc_url( $url . '#uses-rel-preconnect' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Enable text compression, %2$s - </a> */
			esc_html__( 'Optimize your text-based assets to speed up their download. Refer to the %1$sEnable text compression%2$s audit for recommendations on optimizing text.', 'wphb' ),
			'<a href="' . esc_url( $url . '#uses-text-compression' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Serve static assets with an efficient cache policy, %2$s - </a> */
			esc_html__( 'Use Browser Caching to decrease load times during repeat visits. Refer to the %1$sServe static assets with an efficient cache%2$s policy audit, and follow the caching policy recommendations.', 'wphb' ),
			'<a href="' . esc_url( $url . '#uses-long-cache-ttl' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Efficiently encode images, %2$s - <a> Properly size images, %3$s - <a> Use video formats for animated content, %4$s - <a> Serve images in next-gen formats, %5$s - </a> */
			esc_html__( 'Optimizing your images can help your page load faster. Refer to these audits to optimize your images: %1$sEfficiently encode images%5$s, %2$sProperly size images%5$s, %3$sUse video formats for animated content%5$s and %4$sServe images in next-gen formats%5$s.', 'wphb' ),
			'<a href="' . esc_url( $url . '#uses-optimized-images' ) . '">',
			'<a href="' . esc_url( $url . '#uses-responsive-images' ) . '">',
			'<a href="' . esc_url( $url . '#efficient-animated-content' ) . '">',
			'<a href="' . esc_url( $url . '#uses-webp-images' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Defer offscreen images, %2$s - </a> */
			esc_html__( "Since users can't see offscreen images when they load a page, deferring them can help above the fold content load faster. Refer to %1\$sDefer offscreen images%2\$s for recommendations on lazy-loading your images.", 'wphb' ),
			'<a href="' . esc_url( $url . '#offscreen-images' ) . '">',
			'</a>'
		);
		?>
	</li>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Minimize main-thread work, %2$s - </a> */
			esc_html__( 'Minimizing your main-thread work can help the critical content of your page load faster. Refer to the %1$sMinimize main-thread work%2$s audit for recommendations on optimizing your main-thread.', 'wphb' ),
			'<a href="' . esc_url( $url . '#mainthread-work-breakdown' ) . '">',
			'</a>'
		);
		?>
	</li>
</ol>
