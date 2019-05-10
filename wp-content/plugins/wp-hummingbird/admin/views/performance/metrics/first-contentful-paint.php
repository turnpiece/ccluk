<?php
/**
 * First Contentful Paint audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 * @var string   $url    URL to Performance audits page.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p><?php esc_html_e( "First Contentful Paint (FCP) is that period between clicking a link from another site (like a search engine) until the browser renders the first bit of content (text, an image, a canvas element or anything visual) from your website. This is an important milestone for visitors because it provides feedback that the page has started loading. If your FCP is perceived as 'slow' to new visitors, they may not wait long enough for the page to load and will bounce.", 'wphb' ); ?></p>

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
				esc_html__( 'FCP time for your website is %s.', 'wphb' ),
				esc_html( $audit->displayValue )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<h4><?php esc_html_e( 'Recommendations', 'wphb' ); ?></h4>
<p><?php esc_html_e( 'To improve First Contentful Paint, speed up how quickly resources load by minimizing render blocking resources. Follow the Hummingbird tips below:', 'wphb' ); ?></p>
<ol>
	<li>
		<?php
		printf(
			/* translators: %1$s - <a> Eliminate render-blocking resources, %2$s - <a> Defer unused CSS, %3$s - <a> Preload key requests, %4$s - <a> Preconnect to required origins, %5$s - </a> */
			esc_html__( 'Minimize the number of render-blocking stylesheets, and efficiently load third-party javascript. Improving the following audit scores can, in turn, improve your FMP time audit scores: %1$sEliminate render-blocking resources%5$s, %2$sDefer unused CSS%5$s, %3$sPreload key requests%5$s and %4$sPreconnect to required origins%5$s.', 'wphb' ),
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
			/* translators: %1$s - <a> JavaScript execution time, %2$s - </a> */
			esc_html__( 'Optimizing JavaScript bootup helps reduce the JavaScript work on the page load. The %1$sJavaScript execution time%2$s audit measures the JavaScript bootup time of your page and helps you improve the score.', 'wphb' ),
			'<a href="' . esc_url( $url . '#bootup-time' ) . '">',
			'</a>'
		);
		?>
	</li>
</ol>
