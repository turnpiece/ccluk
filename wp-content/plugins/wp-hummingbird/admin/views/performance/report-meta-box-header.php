<?php
/**
 * Common audits header meta box.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var int|bool $can_run_test  True or minutes before next test can be run.
 * @var string   $run_url       URL to trigger new performance test.
 */

?>

<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>

<div class="sui-actions-right">
	<?php if ( true === $can_run_test ) : ?>
		<a href="<?php echo esc_url( $run_url ); ?>" class="sui-button">
			<?php esc_html_e( 'New Test', 'wphb' ); ?>
		</a>
	<?php else : ?>
		<?php
		$tooltip = sprintf(
			/* translators: %d: number of minutes. */
			_n(
				'Hummingbird is just catching her breath - you can run another test in %d minute',
				'Hummingbird is just catching her breath - you can run another test in %d minutes',
				$can_run_test,
				'wphb'
			),
			number_format_i18n( $can_run_test )
		);
		?>
		<span class="sui-tooltip sui-tooltip-bottom sui-tooltip-constrained sui-tooltip-bottom-right" disabled="disabled" data-tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true">
			<a href="#" class="sui-button wphb-disabled-test" disabled="disabled" aria-hidden="true">
				<?php esc_html_e( 'New Test', 'wphb' ); ?>
			</a>
		</span>
	<?php endif; ?>
</div>
