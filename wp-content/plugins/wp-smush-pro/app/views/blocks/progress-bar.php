<?php
/**
 * Progress bar block.
 *
 * @package WP_Smush
 *
 * @var object $count  WP_Smush_Core
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

?>

<div class="wp-smush-bulk-progress-bar-wrapper sui-hidden">
	<p class="wp-smush-bulk-active roboto-medium">
		<?php
		printf(
			/* translators: %1$s: strong opening tag, %2$s: strong closing tag */
			esc_html__( '%1$sBulk smush is currently running.%2$s You need to keep this page open for the process to complete.', 'wp-smushit' ),
			'<strong>',
			'</strong>'
		);
		?>
	</p>

	<div class="sui-notice sui-notice-warning sui-hidden"></div>

	<div class="sui-notice sui-hidden" id="bulk_smush_warning">
		<p>
			<?php
			$upgrade_url = add_query_arg(
				array(
					'utm_source'   => 'smush',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'smush_bulksmush_limit_reached_notice',
				),
				esc_url( 'https://premium.wpmudev.org/project/wp-smush-pro/' )
			);

			printf(
				/* translators: %s1$d - bulk smush limit, %2$s - upgrade link, %3$s - </a>, %4$s - <strong>, $5$s - </strong> */
				esc_html__( 'The free version of Smush allows you to compress %1$d images at a time. You can easily click %4$sResume%5$s to optimize another %1$d images, or %2$sUpgrade to Pro%3$s to compress unlimited images at once.', 'wp-smushit' ),
				absint( WP_Smush_Core::$max_free_bulk ),
				'<a href="' . esc_url( $upgrade_url ) . '" target="_blank">',
				'</a>',
				'<strong>',
				'</strong>'
			)
			?>
		</p>

		<div class="sui-notice-buttons">
			<a class="wp-smush-all sui-button wp-smush-started">
				<i class="sui-icon-play" aria-hidden="true"></i>
				<?php esc_html_e( 'Resume', 'wp-smushit' ); ?>
			</a>
		</div>
	</div>

	<div class="sui-progress-block sui-progress-can-close">
		<div class="sui-progress">
			<span class="sui-progress-icon" aria-hidden="true">
				<i class="sui-icon-loader sui-loading"></i>
			</span>
			<div class="sui-progress-text">
				<span class="wp-smush-images-percent">0%</span>
			</div>
			<div class="sui-progress-bar">
				<span class="wp-smush-progress-inner" style="width: 0%"></span>
			</div>
		</div>
		<button class="sui-progress-close sui-button-icon sui-tooltip wp-smush-cancel-bulk" type="button" data-tooltip="<?php esc_html_e( 'Stop current bulk smush process.', 'wp-smushit' ); ?>">
			<i class="sui-icon-close"></i>
		</button>
		<button class="sui-progress-close sui-button-icon sui-tooltip wp-smush-all sui-hidden" type="button" data-tooltip="<?php esc_html_e( 'Resume scan.', 'wp-smushit' ); ?>">
			<i class="sui-icon-play"></i>
		</button>
	</div>

	<div class="sui-progress-state">
		<span class="sui-progress-state-text">
			<span>0</span>/<span><?php echo absint( $count->remaining_count ); ?></span> <?php esc_html_e( 'images optimized', 'wp-smushit' ); ?>
		</span>
	</div>
</div>
