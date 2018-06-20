<?php
/**
 * Reports meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool   $db_cleanup             Status of database cleanup.
 * @var string $db_frequency           Frequency of database cleanups.
 * @var string $frequency              Frequency of performance reports.
 * @var bool   $performance_is_active  Status of performance reports.
 */

?>

<p class="sui-margin-bottom">
	<?php esc_html_e( 'Automate your workflow with daily, weekly or monthly reports sent directly to your inbox.', 'wphb' ); ?>
</p>

<div class="sui-row">
	<div class="sui-col-lg-6">
		<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'performance' ) . '&view=reports#wphb-box-reporting-summary'; ?>">
			<div class="report-status">
				<i class="sui-icon-hummingbird" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Performance Test', 'wphb' ); ?></strong>
				<?php if ( ! $performance_is_active ) : ?>
					<span class="sui-tag sui-tag-inactive"><?php esc_html_e( 'Inactive', 'wphb' ); ?></span>
				<?php else : ?>
					<span class="sui-tag sui-tag-success"><?php echo esc_html( $frequency ); ?></span>
				<?php endif; ?>
			</div>
		</a>
	</div>
	<div class="sui-col-lg-6">
		<a href="<?php echo WP_Hummingbird_Utils::get_admin_menu_url( 'advanced' ) . '&view=db#wphb-box-advanced-db-settings'; ?>">
			<div class="report-status">
				<i class="sui-icon-user-reputation-points" aria-hidden="true"></i>
				<strong><?php esc_html_e( 'Database Cleanup', 'wphb' ); ?></strong>
				<?php if ( ! $db_cleanup ) : ?>
					<span class="sui-tag sui-tag-inactive"><?php esc_html_e( 'Inactive', 'wphb' ); ?></span>
				<?php else : ?>
					<span class="sui-tag sui-tag-success"><?php echo esc_html( $db_frequency ); ?></span>
				<?php endif; ?>
			</div>
		</a>
	</div>
</div>