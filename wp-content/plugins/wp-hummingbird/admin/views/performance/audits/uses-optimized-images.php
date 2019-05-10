<?php
/**
 * Efficiently encode images audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p>
	<?php esc_html_e( 'Images are an important asset to a visually entertaining website, however they also are the biggest contributor to slowing down website load times. Optimizing and compressing the images will result in faster page load times and happier customers, nice!', 'wphb' ); ?>
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
		<p><?php esc_html_e( "Nice! We couldn't find any unoptimized image.", 'wphb' ); ?></p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $audit->score ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %d - number of ms */
				esc_html__( 'You can potentially save %dms by compressing the following images without losing any visual quality.', 'wphb' ),
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
			/* translators: %1$s - link to Smush page, %2$s - closing a tag */
			esc_html__( 'To reduce the size of your images, you need to compress them to remove unused data without reducing the quality. The popular %1$sSmush%2$s plugin offers automatic bulk optimization to help you improve your score.', 'wphb' ),
			$starting_link,
			$ending_link
		);
		?>
	</p>
	<ol>
		<li><?php esc_html_e( "Install the plugin and follow the instructions to get set up. Once you've chosen your desired settings, smush all your existing images to ensure they are compressed.", 'wphb' ); ?></li>
		<li><?php esc_html_e( 'Next, switch to the Directory Smush tab and start a directory smush. Find your active theme, select it and proceed to optimize all the images in your theme.', 'wphb' ); ?></li>
		<li><?php esc_html_e( "If you are a WPMU DEV member you get access to the pro features of Smush including double the compression, the ability to serve your images via the WPMU DEV CDN, and an option to serve images to WebP. If you're a serious user, Smush Pro could be an ideal option for you.", 'wphb' ); ?></li>
		<li><?php esc_html_e( "Lastly, images should be displaying at the same size as their container. There is a setting in Smush to 'Detect incorrectly sized image'. Enable this feature and check your key pages to ensure images are being output correctly - this will highlight images you may be serving too large, or too small.", 'wphb' ); ?></li>
	</ol>

	<?php if ( WP_Hummingbird_Module_Smush::is_installed() && WP_Hummingbird_Module_Smush::is_enabled() && WP_Hummingbird_Module_Smush::can_be_configured() ) : ?>
		<a href="<?php menu_page_url( 'smush' ); ?>" class="sui-button">
			<?php esc_html_e( 'Optimize Images', 'wphb' ); ?>
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
<?php endif; ?>
