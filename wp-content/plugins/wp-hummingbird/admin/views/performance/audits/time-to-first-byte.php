<?php
/**
 * Reduce server response times (TTFB) audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p>
	<?php esc_html_e( "Time To First Byte identifies the time it takes for a visitor's browser to receive the first byte of page content from the server. Ideally, TTFB for your server should be under 600 ms. ", 'wphb' ); ?>
</p>

<h4><?php esc_html_e( 'Status', 'wphb' ); ?></h4>
<?php if ( isset( $audit->errorMessage ) && ! isset( $audit->score ) ) { ?>
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
	<?php
	return;
}
?>
<?php if ( isset( $audit->score ) && 1 === $audit->score ) : ?>
	<div class="sui-notice sui-notice-success">
		<p>
			<?php
			printf(
				/* translators: %s - number of ms */
				esc_html__( 'Nice! TTFB for your server was %s.', 'wphb' ),
				esc_html( str_replace( 'Root document took ', '', $audit->displayValue ) )
			);
			?>
		</p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $audit->score ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %s - number of ms */
				esc_html__( 'It took %s to receive the first byte of page content.', 'wphb' ),
				esc_html( str_replace( 'Root document took ', '', $audit->displayValue ) )
			);
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How to fix', 'wphb' ); ?></h4>
	<ol>
		<?php if ( ! isset( $_SERVER['WPMUDEV_HOSTED'] ) ) : ?>
			<li>
				<?php
				printf(
					/* translators: %1$s - link to Hosting project page, %2$s - closing a tag */
					esc_html__( 'TTFB largely depends on your server’s performance capacity. Host your website on %1$sWPMU DEV Hosting%2$s which comes with features such as dedicated resources, object caching, support for the latest PHP versions, and a blazing fast CDN.', 'wphb' ),
					'<a href="' . esc_html( WP_Hummingbird_Utils::get_link( 'hosting', 'hummingbird_test_ttfb_hosting_upsell_link' ) ) . '" target="_blank">',
					'</a>'
				);
				?>

				<div class="wphb-upsell-performance-row">
					<img class="sui-image sui-upsell-image"
						src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hosting.png' ); ?>"
						srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/hosting@2x.png' ); ?> 2x"
						alt="<?php esc_attr_e( 'WPMU DEV Hosting', 'wphb' ); ?>">
					<div class="sui-notice sui-notice-purple">
						<p><?php esc_html_e( 'WPMU DEV Hosting offers 3 free websites with features such as dedicated resources, object & page caching, and blazing fast CDN.', 'wphb' ); ?></p>

						<div class="sui-notice-buttons">
							<a href="<?php echo esc_html( WP_Hummingbird_Utils::get_link( 'hosting', 'hummingbird_test_response_time_hosting_upsell_learnmore_button' ) ); ?>" target="_blank" class="sui-button sui-button-purple">
								<?php esc_html_e( 'Learn More', 'wphb' ); ?>
							</a>
						</div>
					</div>
				</div>
			</li>
		<?php endif; ?>
		<li>
			<?php esc_html_e( "Enable Hummingbird's page caching. This can substantially improve your server response time for logged out visitors and search engine bots.", 'wphb' ); ?>
			<?php if ( $url = WP_Hummingbird_Utils::get_admin_menu_url( 'caching' ) ) : ?>
				<a href="<?php echo esc_url( $url ); ?>" class="sui-button">
					<?php esc_html_e( 'Configure Page Caching', 'wphb' ); ?>
				</a>
			<?php endif; ?>
		</li>
		<?php if ( isset( $_SERVER['WPMUDEV_HOSTED'] ) ) : ?>
			<li>
				<?php
				printf(
					/* translators: %1$s - link to Hosting project page, %2$s - closing a tag */
					esc_html__( 'If yours is a high traffic site, upgrade your server resources to improve your server response time. Check out the upgrade plans for your WPMU DEV hosting %1$shere%2$s.', 'wphb' ),
					'<a href="' . esc_html( WP_Hummingbird_Utils::get_link( 'hosting', 'hummingbird_test_response_time_hosting_upgrade_plan_link' ) ) . '" target="_blank">',
					'</a>'
				);
				?>
			</li>
		<?php endif; ?>
		<li>
			<?php
			printf(
				/* translators: %1$s - link to Query Monitor wp.org page, %2$s - closing a tag */
				esc_html__( 'Usually, your installed WordPress plugins have a huge impact on your page generation time. Some are horribly inefficient, and some are just resource intensive. Test the performance impact of your plugins using a plugin like %1$sQuery Monitor%2$s, then remove the worst offenders, or replace them with a suitable alternative.', 'wphb' ),
				'<a href="https://wordpress.org/plugins/query-monitor/" target="_blank">',
				'</a>'
			);
			?>
		</li>
	</ol>
<?php endif; ?>
