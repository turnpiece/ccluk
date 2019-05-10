<?php
/**
 * Performance report settings meta box.
 *
 * @package Hummingbird
 *
 * @var bool  $dismissed      Report dismissed status.
 * @var array $widget         Widget settings.
 * @var array $hub            Hub widget settings.
 * @var bool  $subsite_tests  Sub-site tests status.
 */

WP_Hummingbird_Utils::get_modal( 'dismiss-report' );

?>

<form method="post" class="settings-frm">
	<div class="sui-box-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Dashboard Widget', 'wphb' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Customize the test results shown on the plugin dashboard as per your preference.', 'wphb' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<strong><?php esc_html_e( 'Device', 'wphb' ); ?></strong>
			<span class="sui-description">
				<?php esc_html_e( 'Choose which device you want to show the performance test results for on the Dashboard widget.', 'wphb' ); ?>
			</span>
			<div class="sui-side-tabs">
				<div class="sui-tabs-menu">
					<label for="desktop_report-true" class="sui-tab-item <?php echo $widget['desktop'] ? 'active' : ''; ?>">
						<input type="radio" name="desktop-report" value="1" id="desktop_report-true" <?php checked( $widget['desktop'] ); ?>>
						<?php esc_html_e( 'Desktop', 'wphb' ); ?>
					</label>

					<label for="desktop_report-false" class="sui-tab-item <?php echo $widget['desktop'] ? '' : 'active'; ?>">
						<input type="radio" name="desktop-report" value="0" id="desktop_report-false" <?php checked( $widget['desktop'], false ); ?>>
						<?php esc_html_e( 'Mobile', 'wphb' ); ?>
					</label>
				</div>
			</div>

			<strong><?php esc_html_e( 'Customize', 'wphb' ); ?></strong>
			<span class="sui-description">
				<?php esc_html_e( 'Choose what results do you to see in your Performance Test widget on your plugin dashboard.', 'wphb' ); ?>
			</span>
			<label for="metrics" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
				<input type="checkbox" name="metrics" id="metrics" <?php checked( $widget['show_metrics'] ); ?> />
				<span aria-hidden="true"></span>
				<span><?php esc_html_e( 'Score Metrics', 'wphb' ); ?></span>
			</label>
			<label for="audits" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
				<input type="checkbox" name="audits" id="audits" <?php checked( $widget['show_audits'] ); ?> />
				<span aria-hidden="true"></span>
				<span><?php esc_html_e( 'Audits', 'wphb' ); ?></span>
			</label>
			<label for="field-data" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
				<input type="checkbox" name="field-data" id="field-data" <?php checked( $widget['show_historic'] ); ?> />
				<span aria-hidden="true"></span>
				<span><?php esc_html_e( 'Historic Field Data', 'wphb' ); ?></span>
			</label>
		</div>
	</div>

	<?php if ( ! is_multisite() || ( is_multisite() && is_network_admin() ) ) : ?>
		<input type="hidden" name="network_admin" value="1" />
		<?php if ( WP_Hummingbird_Utils::is_member() ) : ?>
			<div class="sui-box-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Hub Widget', 'wphb' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Customize the test results shown under the performance tab in the Hub as per your preference.', 'wphb' ); ?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<strong><?php esc_html_e( 'Customize', 'wphb' ); ?></strong>
					<span class="sui-description">
						<?php esc_html_e( 'Choose what results do you to see in your Performance Test widget in the Hub.', 'wphb' ); ?>
					</span>
					<label for="hub-metrics" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
						<input type="checkbox" name="hub-metrics" id="hub-metrics" <?php checked( $hub['show_metrics'] ); ?> />
						<span aria-hidden="true"></span>
						<span><?php esc_html_e( 'Score Metrics', 'wphb' ); ?></span>
					</label>
					<label for="hub-audits" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
						<input type="checkbox" name="hub-audits" id="hub-audits" <?php checked( $hub['show_audits'] ); ?> />
						<span aria-hidden="true"></span>
						<span><?php esc_html_e( 'Audits', 'wphb' ); ?></span>
					</label>
					<label for="hub-field-data" class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
						<input type="checkbox" name="hub-field-data" id="hub-field-data" <?php checked( $hub['show_historic'] ); ?> />
						<span aria-hidden="true"></span>
						<span><?php esc_html_e( 'Historic Field Data', 'wphb' ); ?></span>
					</label>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! is_multisite() || ( is_multisite() && is_main_site() ) ) : ?>
		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<span class="sui-settings-label">
					<?php esc_html_e( 'Ignore Current Score', 'wphb' ); ?>
				</span>
				<span class="sui-description">
					<?php esc_html_e( 'If you don’t wish to see your current performance test results, you can ignore them here.', 'wphb' ); ?>
				</span>
			</div>
			<div class="sui-box-settings-col-2">
				<a class="sui-button sui-button-ghost" id="dismiss-report" data-a11y-dialog-show="dismiss-report-modal" <?php disabled( $dismissed ); ?>>
					<i class="sui-icon-eye-hide" aria-hidden="true"></i>
					<?php esc_html_e( 'Ignore Results', 'wphb' ); ?>
				</a>

				<span class="sui-description">
					<?php esc_html_e( 'Note: You can re-run the test anytime to check your performance score again.', 'wphb' ); ?>
				</span>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( is_multisite() && is_network_admin() ) : ?>
		<div class="sui-box-settings-row">
			<div class="sui-box-settings-col-1">
				<span class="sui-settings-label">
					<?php esc_html_e( 'Subsites', 'wphb' ); ?>
				</span>
				<span class="sui-description">
					<?php esc_html_e( 'Choose the minimum user role required to run the performance tests on your subsites.', 'wphb' ); ?>
				</span>
			</div>
			<div class="sui-box-settings-col-2">
				<div class="sui-side-tabs">
					<div class="sui-tabs-menu">
						<label for="subsite_tests-false" class="sui-tab-item <?php echo ! $subsite_tests || 'super-admins' === $subsite_tests ? 'active' : ''; ?>">
							<input type="radio" name="subsite-tests" value="super-admins" id="subsite_tests-false" <?php checked( $subsite_tests, 'super-admins' ); ?>>
							<?php esc_html_e( 'Super Admin', 'wphb' ); ?>
						</label>

						<label for="subsite_tests-true" class="sui-tab-item <?php echo true === $subsite_tests ? 'active' : ''; ?>">
							<input type="radio" name="subsite-tests" value="true" id="subsite_tests-true" <?php checked( $subsite_tests, true ); ?>>
							<?php esc_html_e( 'Subsite Admin', 'wphb' ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
