<?php
/**
 * Minify CSS audit.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var stdClass $audit  Audit object.
 */

?>

<h4><?php esc_html_e( 'Overview', 'wphb' ); ?></h4>
<p>
	<?php esc_html_e( 'CSS files control the look and feel of your website. Often, these files come with a lot of extra \'bloat\' that they don\'t need. By compressing those files and removing all the excess you\'ll reduce payload sizes and reduce your page load speed. Optimizing CSS files includes removing comments, formatting and duplicate code.', 'wphb' ); ?>
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
		<p><?php esc_html_e( "Nice! We couldn't find any uncompressed CSS files.", 'wphb' ); ?></p>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $audit->score ) ); ?>">
		<p>
			<?php
			printf(
				/* translators: %s - properly formatted bytes value */
				esc_html__( 'You can potentially save %s by minifying the following CSS files.', 'wphb' ),
				esc_html( WP_Hummingbird_utils::format_bytes( $audit->details->overallSavingsBytes, 0 ) )
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
	<p><?php esc_html_e( 'Use the Hummingbird Asset Optimization module to compress yourCSS files. To minify, locate the CSS files in the assets page and click the compress button on the right. Hummingbird Pro users get access to the WPMU DEV CDN for additional savings.', 'wphb' ); ?></p>
	<?php if ( $url = WP_Hummingbird_Utils::get_admin_menu_url( 'minification' ) ) : ?>
		<a href="<?php echo esc_url( $url ); ?>" class="wphb-button-link">
			<?php esc_html_e( 'Configure Asset Optimization', 'wphb' ); ?>
		</a>
	<?php endif; ?>
<?php endif; ?>
