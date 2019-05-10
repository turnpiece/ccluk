<?php
/**
 * Main template file.
 *
 * @package Hummingbird
 *
 * @var array  $params     Parameters array: REPORT_TYPE, USER_NAME, SCAN_PAGE_LINK, SITE_MANAGE_URL, SITE_URL, SITE_NAME.
 * @var object $last_test  Latest performance report.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<table class="wrapper main" align="center" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
	<tbody>
	<tr style="padding: 0; text-align: left; vertical-align: top;">
		<td class="wrapper-inner main-inner" style="-moz-hyphens: auto; -webkit-hyphens: auto; border-collapse: collapse !important; color: #555555; font-family: Arial, sans-serif; font-size: 14px; font-weight: normal; hyphens: auto; line-height: 30px; margin: 0; padding: 30px 60px; text-align: left; vertical-align: top; word-wrap: break-word;">

			<table class="main-content" style="border-collapse: collapse; border-spacing: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
				<tbody>
				<tr style="padding: 0; text-align: left; vertical-align: top;">
					<td class="main-content-text" style="-moz-hyphens: auto; -webkit-hyphens: auto; border-collapse: collapse !important; color: #555555; font-family: Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 30px; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">
						<?php /* translators: %s: Username. */ ?>
						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 32px;font-weight: normal;line-height: 37px;margin: 0 0 30px;padding: 0;text-align: left"><?php printf( esc_html__( 'Hi %s,', 'wphb' ), esc_attr( $params['USER_NAME'] ) ); ?></p>

						<?php
						$data_time    = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_test->time ) ) );
						$time_string  = esc_html( date_i18n( get_option( 'date_format' ), $data_time ) );
						$time_string .= sprintf(
							/* translators: %s - time in proper format */
							esc_html_x( ' at %s', 'Time of the last performance report', 'wphb' ),
							esc_html( date_i18n( get_option( 'time_format' ), $data_time ) )
						);
						?>
						<p style="color: #888888;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 0 30px;padding: 0;text-align: left;letter-spacing: -0.25px;"><?php esc_html_e( 'Here’s your latest Performance Test summary of', 'wphb' ); ?> <a class="brand" href="<?php echo esc_attr( $params['SITE_MANAGE_URL'] ); ?>" target="_blank" style="color: #17A8E3;font-family: Arial, sans-serif;font-weight: inherit;line-height: 30px;margin: 0;padding: 0;text-align: left;text-decoration: none"><?php echo esc_html( $params['SITE_URL'] ); ?></a> <?php printf( __( 'tested on %s.', 'wphb' ), $time_string ); ?></p>

						<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
							<thead>
							<tr style="border-bottom: 1px solid #e6e6e6">
								<td style="color: #555555;font-family: Arial, sans-serif;font-size: 32px;font-weight: normal;line-height: 37px; width: 300px">
									<?php esc_html_e( 'Overall Score', 'wphb' ); ?>
								</td>
								<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
									<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
										<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-desktop.png' ); ?>" style="height: 16px">
										<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Desktop', 'wphb' ); ?></span>
									</td>
								<?php endif; ?>
								<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
									<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
										<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-mobile.png' ); ?>" style="height: 16px;">
										<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Mobile', 'wphb' ); ?></span>
									</td>
								<?php endif; ?>
							</tr>
							</thead>
							<tbody>
							<tr class="report-list-item">
								<td class="report-list-item-info" style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.22px;width: 300px;padding: 10px 0">
									<?php esc_html_e( 'Here are your latest performance test results. A score above 85 on desktop and 80 on mobile is considered as a good benchmark.', 'wphb' ); ?>
								</td>
								<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
									<td class="report-list-item-result" align="right" style="color: #555555;font-family: Arial, sans-serif;font-size: 50px;font-weight: normal;line-height: 55px">
										<table>
											<tr>
												<td rowspan="2"><?php echo absint( $last_test->desktop->score ); ?></td>
												<td style="text-align: left">
													<?php if ( 'a' === $last_test->desktop->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php elseif ( 'b' === $last_test->desktop->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php elseif ( 'c' === $last_test->desktop->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php endif; ?>
												</td>
											</tr>
											<tr>
												<td>
													<span style="color: #555555;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.3px;width: 300px;vertical-align: top">/100</span>
												</td>
											</tr>
										</table>
									</td>
								<?php endif; ?>
								<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
									<td class="report-list-item-result" align="right" style="color: #555555;font-family: Arial, sans-serif;font-size: 50px;font-weight: normal;line-height: 55px">
										<table>
											<tr>
												<td rowspan="2"><?php echo absint( $last_test->mobile->score ); ?></td>
												<td style="text-align: left">
													<?php if ( 'a' === $last_test->mobile->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php elseif ( 'b' === $last_test->desktop->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php elseif ( 'c' === $last_test->desktop->score_class ) : ?>
														<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; height: 16px; outline: none; text-decoration: none; width: auto;" />
													<?php endif; ?>
												</td>
											</tr>
											<tr>
												<td>
													<span style="color: #555555;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.3px;width: 300px;vertical-align: top">/100</span>
												</td>
											</tr>
										</table>
									</td>
								<?php endif; ?>
							</tr>
							</tbody>
						</table>

						<?php if ( $params['SHOW_METRICS'] ) : ?>
							<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
								<thead>
								<tr style="border-bottom: 1px solid #e6e6e6">
									<td style="color: #555555;font-family: Arial, sans-serif;font-size: 32px;font-weight: normal;line-height: 37px; width: 300px">
										<?php esc_html_e( 'Score Metrics', 'wphb' ); ?>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-desktop.png' ); ?>" style="height: 16px">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Desktop', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-mobile.png' ); ?>" style="height: 16px;">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Mobile', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="3" style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.22px;padding: 10px 0">
										<?php esc_html_e( 'Your performance score is calculated based on how your site performs on each of the following 6 metrics.', 'wphb' ); ?>
									</td>
								</tr>
								<?php foreach ( $last_test->desktop->metrics as $index => $metric ) : ?>
									<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
											<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php echo esc_html( $metric->title ); ?></span>
										</td>
										<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-info" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px"><?php echo esc_html( $metric->displayValue ); ?></span>
												<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-' . WP_Hummingbird_Module_Performance::get_impact_class( $metric->score ) . '.png' ); ?>" alt="<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $metric->score ) ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: right; display: inline-block; margin: 2px 0 0 10px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;">
											</td>
										<?php endif; ?>
										<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-result ok" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px"><?php echo esc_html( $last_test->mobile->metrics->{$index}->displayValue ); ?></span>
												<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-' . WP_Hummingbird_Module_Performance::get_impact_class( $last_test->mobile->metrics->{$index}->score ) . '.png' ); ?>" alt="<?php echo esc_attr( WP_Hummingbird_Module_Performance::get_impact_class( $last_test->mobile->metrics->{$index}->score ) ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: right; display: inline-block; margin: 2px 0 0 10px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;">
											</td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<?php if ( $params['SHOW_AUDITS'] ) : ?>
							<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
								<thead>
								<tr style="border-bottom: 1px solid #e6e6e6">
									<td style="color: #555555;font-family: Arial, sans-serif;font-size: 32px;font-weight: normal;line-height: 37px; width: 300px">
										<?php esc_html_e( 'Audit', 'wphb' ); ?>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-desktop.png' ); ?>" style="height: 16px">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Desktop', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-mobile.png' ); ?>" style="height: 16px;">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Mobile', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="3" style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.22px;padding: 10px 0">
										<?php esc_html_e( 'Audit results are divided into following three categories. Opportunities and Diagnostics provide recommendations to improve the performance score.', 'wphb' ); ?>
									</td>
								</tr>
								<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
									<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
										<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php esc_html_e( 'Opportunities', 'wphb' ); ?></span>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<?php
											$class = WP_Hummingbird_Module_Performance::get_audits_class( $last_test->desktop->audits->opportunities );
											$color = '#1ABC9C';
											if ( 'error' === $class ) {
												$color = '#FF6D6D';
											} elseif ( 'warning' === $class ) {
												$color = '#FECF2F';
											}
											?>
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: <?php echo esc_attr( $color ); ?>;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->desktop->audits->opportunities ) ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<?php
											$class = WP_Hummingbird_Module_Performance::get_audits_class( $last_test->mobile->audits->opportunities );
											$color = '#1ABC9C';
											if ( 'error' === $class ) {
												$color = '#FF6D6D';
											} elseif ( 'warning' === $class ) {
												$color = '#FECF2F';
											}
											?>
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: <?php echo esc_attr( $color ); ?>;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->mobile->audits->opportunities ) ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
									<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
										<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php esc_html_e( 'Diagnostics', 'wphb' ); ?></span>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<?php
											$class = WP_Hummingbird_Module_Performance::get_audits_class( $last_test->desktop->audits->diagnostics );
											$color = '#1ABC9C';
											if ( 'error' === $class ) {
												$color = '#FF6D6D';
											} elseif ( 'warning' === $class ) {
												$color = '#FECF2F';
											}
											?>
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: <?php echo esc_attr( $color ); ?>;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->desktop->audits->diagnostics ) ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<?php
											$class = WP_Hummingbird_Module_Performance::get_audits_class( $last_test->mobile->audits->diagnostics );
											$color = '#1ABC9C';
											if ( 'error' === $class ) {
												$color = '#FF6D6D';
											} elseif ( 'warning' === $class ) {
												$color = '#FECF2F';
											}
											?>
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: <?php echo esc_attr( $color ); ?>;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->mobile->audits->diagnostics ) ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
									<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
										<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php esc_html_e( 'Passed Audits', 'wphb' ); ?></span>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: #1ABC9C;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->desktop->audits->passed ) ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 27px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
											<span style="color: inherit; display: inline-block; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px;background-color: #1ABC9C;width: 39px;height: 26px;border-radius: 13px;text-align: center;"><?php echo count( get_object_vars( $last_test->mobile->audits->passed ) ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								</tbody>
							</table>
						<?php endif; ?>

						<?php if ( $params['SHOW_HISTORIC'] ) : ?>
							<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
								<thead>
								<tr style="border-bottom: 1px solid #e6e6e6">
									<td style="color: #555555;font-family: Arial, sans-serif;font-size: 32px;font-weight: normal;line-height: 37px; width: 300px">
										<?php esc_html_e( 'Historic Field Data', 'wphb' ); ?>
									</td>
									<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-desktop.png' ); ?>" style="height: 16px">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Desktop', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
									<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
										<td style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 15px; text-align: right; width:119px">
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-mobile.png' ); ?>" style="height: 16px;">
											<span style="margin-left: 5px; vertical-align: top;"><?php esc_html_e( 'Mobile', 'wphb' ); ?></span>
										</td>
									<?php endif; ?>
								</tr>
								</thead>
								<tbody>
								<tr>
									<td colspan="3" style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.22px;padding: 10px 0">
										<?php
										printf(
											/* translators: %1$s - starting a tag, %2$s - closing a tag */
											esc_html__( 'We use %1$sChrome User Experience Report%2$s to generate insights about the real users’ experience with your webpage over the last 30 days.', 'wphb' ),
											'<a class="external" href="https://developers.google.com/web/tools/chrome-user-experience-report/" target="_blank" style="color: #17A8E3;font-family: Arial, sans-serif;font-weight: inherit;line-height: 30px;margin: 0;padding: 0;text-align: left;text-decoration: none">',
											'</a>'
										);
										?>
									</td>
								</tr>
								<?php if ( ! $last_test->desktop->field_data ) : ?>
									<tr>
										<td colspan="3">
											<div style="border-radius: 4px;border: 1px solid #aaa;border-left: 2px solid #aaa;padding: 5px 15px">
												<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-notice.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 15px 10px 0 10px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;">
												<p style="color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;letter-spacing: -0.25px; margin-left:35px">
													<?php esc_html_e( 'The Chrome User Experience Report does not have sufficient real-world speed data for this page. Note: This report can take months to populate and is aimed at well established websites.', 'wphb' ); ?>
												</p>
											</div>
										</td>
									</tr>
								<?php else : ?>
									<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
											<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php esc_html_e( 'First Contentful Paint (FCP)', 'wphb' ); ?></span>
										</td>
										<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-info" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px">
													<?php
													/* translators: %s - number of seconds */
													printf( '%s s', esc_html( round( $last_test->desktop->field_data->FIRST_CONTENTFUL_PAINT_MS->percentile / 1000, 1 ) ) );
													?>
												</span>
												<?php if ( 'FAST' === $last_test->desktop->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'AVERAGE' === $last_test->desktop->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'SLOW' === $last_test->desktop->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php endif; ?>
											</td>
										<?php endif; ?>
										<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-result ok" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px">
													<?php
													/* translators: %s - number of seconds */
													printf( '%s s', esc_html( round( $last_test->mobile->field_data->FIRST_CONTENTFUL_PAINT_MS->percentile / 1000, 1 ) ) );
													?>
												</span>
												<?php if ( 'FAST' === $last_test->mobile->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'AVERAGE' === $last_test->mobile->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'SLOW' === $last_test->mobile->field_data->FIRST_CONTENTFUL_PAINT_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php endif; ?>
											</td>
										<?php endif; ?>
									</tr>
									<tr class="report-list-item" style="padding: 0;text-align: left;vertical-align: top">
										<td class="report-list-item-info" style="border-collapse: collapse !important;color: #333333;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;line-height: 22px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
											<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle; letter-spacing: -0.25px;"><?php esc_html_e( 'First Input Delay (FID)', 'wphb' ); ?></span>
										</td>
										<?php if ( 'both' === $params['DEVICE'] || 'desktop' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-info" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px">
													<?php
													/* translators: %s - number of seconds */
													printf( '%s s', esc_html( $last_test->desktop->field_data->FIRST_INPUT_DELAY_MS->percentile ) );
													?>
												</span>
												<?php if ( 'FAST' === $last_test->desktop->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'AVERAGE' === $last_test->desktop->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'SLOW' === $last_test->desktop->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php endif; ?>
											</td>
										<?php endif; ?>
										<?php if ( 'both' === $params['DEVICE'] || 'mobile' === $params['DEVICE'] ) : ?>
											<td class="report-list-item-result ok" style="border-collapse: collapse !important;color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: normal;line-height: 22px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
												<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;letter-spacing: -0.25px">
													<?php
													/* translators: %s - number of seconds */
													printf( '%s s', esc_html( $last_test->mobile->field_data->FIRST_INPUT_DELAY_MS->percentile ) );
													?>
												</span>
												<?php if ( 'FAST' === $last_test->mobile->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-success.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'AVERAGE' === $last_test->mobile->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php elseif ( 'SLOW' === $last_test->mobile->field_data->FIRST_INPUT_DELAY_MS->category ) : ?>
													<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 0 0 -20px; height: 16px; outline: none; text-decoration: none; width: auto; vertical-align: middle;" />
												<?php endif; ?>
											</td>
										<?php endif; ?>
									</tr>
								<?php endif; ?>
								</tbody>
							</table>
						<?php endif; ?>

						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 20px 30px 0;padding: 0;text-align: left; float: left"><a href="<?php echo esc_url( $params['SCAN_PAGE_LINK'] ); ?>" class="brand-button" style="background: #17A8E3;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 20px;margin: 0;padding: 10px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 4px;text-transform: uppercase"><?php esc_html_e( 'View full report', 'wphb' ); ?></a></p>
						<a style="color: #888888;font-family: Arial, sans-serif;font-size: 13px;font-weight: bold;letter-spacing: -0.22px;line-height: 40px;text-decoration: none" href="<?php echo esc_url( $params['SCAN_PAGE_LINK'] . '&view=reports' ); ?>" class="brand-link" target="_blank">
							<?php esc_html_e( 'Customize email report', 'wphb' ); ?>
						</a>

						<p style="color: #666666;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 20px;margin: 0 0 20px;padding: 0;text-align: left;clear: both"><?php esc_html_e( 'Stay humming.', 'wphb' ); ?></p>
						<strong><?php esc_html_e( 'Hummingbird', 'wphb' ); ?></strong>
						<p style="color: #666666;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 15px;margin: 0 0 10px;padding: 0;text-align: left"><?php esc_html_e( 'Performance Hero', 'wphb' ); ?></p>
						<p style="color: #666666;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 15px;margin: 0 0 30px;padding: 0;text-align: left"><?php esc_html_e( 'WPMU DEV', 'wphb' ); ?></p>
					</td>
				</tr>
				</tbody>
			</table>

		</td>
	</tr>
	</tbody>
</table>
