<?php
/**
 * Serve images in next-gen formats audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p>
	<?php esc_html_e( 'Image formats like JPEG 2000, JPEG XR, and WebP often provide better compression than PNG or JPEG, which means faster downloads and less data consumption.', 'wphb' ); ?>
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
		<p><?php esc_html_e( 'Nice! Everything looks perfect here.', 'wphb' ); ?></p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $audit->score ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %d - number of ms */
				esc_html__( 'You can potentially save %dms in page load time by serving the following images in WebP format.', 'wphb' ),
				absint( $audit->details->overallSavingsMs )
			);
			?>
		</p>
	</div>

	<?php if ( $audit->details->items ) : ?>
		<table class="sui-table">
			<thead>
			<tr>
				<th><?php esc_html_e( 'URL', 'wphb' ); ?></th>
				<th><?php esc_html_e( 'Size', 'wphb' ); ?></th>
				<th><?php esc_html_e( 'Savings', 'wphb' ); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $audit->details->items as $item ) : ?>
				<tr>
					<td>
						<a href="<?php echo esc_html( $item->url ); ?>" target="_blank">
							<?php echo esc_html( $item->url ); ?>
						</a>
					</td>
					<td><?php echo esc_html( WP_Hummingbird_Utils::format_bytes( $item->totalBytes ) ); ?></td>
					<td><?php echo esc_html( WP_Hummingbird_Utils::format_bytes( $item->wastedBytes ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<h4><?php esc_html_e( 'How to fix', 'wphb' ); ?></h4>
	<p>
		<?php
		$starting_link = '';
		$ending_link   = '';
		if ( WP_Hummingbird_Module_Smush::is_installed() && WP_Hummingbird_Module_Smush::is_enabled() && WP_Hummingbird_Module_Smush::can_be_configured() ) {
			$starting_link = '<a href="' . esc_html( WP_Hummingbird_Utils::get_link( 'smush' ) ) . '" target="_blank">';
			$ending_link   = '</a>';
		}
		printf(
			/* translators: %1$s - link to Smush project page, %2$s - closing a tag */
			esc_html__( '%1$sSmush Pro%2$s has a blazing-fast and intelligent CDN which can automatically convert and serve your images in WebP format to the compatible browsers or gracefully fall back to original PNGs or JPEGs on non-compatible browsers.', 'wphb' ),
			$starting_link,
			$ending_link
		);
		?>
	</p>

	<?php if ( WP_Hummingbird_Module_Smush::is_installed() && WP_Hummingbird_Module_Smush::is_enabled() && WP_Hummingbird_Module_Smush::can_be_configured() ) : ?>
		<a href="<?php menu_page_url( 'smush' ); ?>" class="sui-button">
			<?php esc_html_e( 'Configure Smush', 'wphb' ); ?>
		</a>
	<?php elseif ( WP_Hummingbird_Module_Smush::is_installed() && ! WP_Hummingbird_Module_Smush::is_enabled() && is_main_site() ) : ?>
		<?php
		if ( WP_Hummingbird_Module_Smush::$is_smush_pro ) {
			$url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=wp-smush-pro/wp-smush.php', 'activate-plugin_wp-smush-pro/wp-smush.php' );
		} else {
			$url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=wp-smushit/wp-smush.php', 'activate-plugin_wp-smushit/wp-smush.php' );
		}
		?>
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="sui-button">
			<?php esc_html_e( 'Activate Smush', 'wphb' ); ?>
		</a>
	<?php elseif ( is_main_site() ) : ?>
		<a href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'smush' ) ); ?>" target="_blank" class="sui-button">
			<?php esc_html_e( 'Install Smush', 'wphb' ); ?>
		</a>
	<?php endif; ?>

	<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
		<?php WP_Hummingbird_Utils::get_modal( 'membership' ); ?>
		<div class="wphb-upsell-performance-row wphb-negative-margin">
			<img class="sui-image sui-upsell-image"
				src="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/smush-share-widget.png' ); ?>"
				srcset="<?php echo esc_url( WPHB_DIR_URL . 'admin/assets/image/smush-share-widget@2x.png' ); ?> 2x"
				alt="<?php esc_attr_e( 'Get WP Smush Pro', 'wphb' ); ?>">
			<div class="sui-notice sui-notice-purple">
				<p>
					<?php
					printf(
						/* translators: %1$s - link to Smush project page, %2$s - closing a tag */
						esc_html__( "Upgrade to %1\$sSmush Pro%2\$s, and start serving your images in WebP format using Smush CDN. You'll also get 2x better compression on your images along with other pro features. Try it all free with a %3\$sWPMU DEV membership today%2\$s!", 'wphb' ),
						'<a href="' . esc_html( WP_Hummingbird_Utils::get_link( 'smush', 'hummingbird_report_smush_upsell_link' ) ) . '" target="_blank">',
						'</a>',
						'<a href="#" data-a11y-dialog-show="wphb-upgrade-membership-modal">'
					);
					?>
				</p>

				<div class="sui-notice-buttons">
					<a href="<?php echo esc_html( WP_Hummingbird_Utils::get_link( 'smush-plugin', 'hummingbird_report_smush_upsell_link' ) ); ?>" target="_blank" class="sui-button sui-button-purple">
						<?php esc_html_e( 'Learn More', 'wphb' ); ?>
					</a>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
