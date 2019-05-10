<?php
/**
 * First Meaningful Paint audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 * @var string   $url    URL to Performance audits page.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p><?php esc_html_e( "First Meaningful Paint (FMP) identifies the time required for the primary content — the content you want your visitors to engage with first — to become visible. The sooner the primary content is visible, the sooner your visitors perceive your page as useful. Primary content differs from page to page. For example, on Twitter, the primary content is the first tweet, while on a news site it's likely the title and featured image.", 'wphb' ); ?></p>

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
				esc_html__( 'FMP time for your website is %s.', 'wphb' ),
				esc_html( $audit->displayValue )
			);
			?>
		</p>
	</div>
<?php endif; ?>

<h4><?php esc_html_e( 'Recommendations', 'wphb' ); ?></h4>
<p><?php esc_html_e( 'Identify the most critical UI elements on a page, and ensure the initial load contains just the code needed to render those elements. The following can help you improve the FCP:', 'wphb' ); ?></p>
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
			/* translators: %1$s - <a> Minimize main-thread work, %2$s - </a> */
			esc_html__( 'Minimizing your main-thread work can help the critical content of your page load faster. Refer to the %1$sMinimize main-thread work%2$s audit for recommendations on optimizing your main-thread.', 'wphb' ),
			'<a href="' . esc_url( $url . '#mainthread-work-breakdown' ) . '">',
			'</a>'
		);
		?>
	</li>
</ol>
