<?php
/**
 * Main template file.
 *
 * @package Hummingbird
 * @var array $params  Parameters array: REPORT_TYPE, USER_NAME, SCAN_PAGE_LINK, SITE_MANAGE_URL, SITE_URL, SITE_NAME.
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

						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 0 30px;padding: 0;text-align: left"><?php esc_html_e( 'Here’s a quick summary of your weekly uptime and performance data for', 'wphb' ); ?> <strong><a class="brand" href="<?php echo esc_attr( $params['SITE_MANAGE_URL'] ); ?>" target="_blank" style="color: #333;font-family: Arial, sans-serif;font-weight: inherit;line-height: 30px;margin: 0;padding: 0;text-align: left;text-decoration: none"><?php echo esc_html( $params['SITE_URL'] ); ?></a></strong></p>

						<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #e6e6e6;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
							<tbody>
							<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
								<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
									<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html_e( 'Availability', 'wphb' ); ?></span>
								</td>
                                <td class="report-list-item-result" style="border-collapse: collapse !important;font-family: Arial, sans-serif;font-size: 15px;font-weight: 400;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
									<?php echo esc_html( $last_test->availability ); ?>
								</td>
							</tr>
							<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
								<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
									<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html_e( 'Outages', 'wphb' ); ?></span>
								</td>
                                <td class="report-list-item-result" style="border-collapse: collapse !important;font-family: Arial, sans-serif;font-size: 15px;font-weight: 400;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
									<?php echo esc_html( $last_test->outages ); ?>
								</td>
							</tr>
							<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
								<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
									<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html_e( 'Downtime', 'wphb' ); ?></span>
								</td>
                                <td class="report-list-item-result" style="border-collapse: collapse !important;font-family: Arial, sans-serif;font-size: 15px;font-weight: 400;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
									<?php
                                    if ( isset( $last_test->downtime ) && false !== $last_test->downtime ) {
	                                    echo esc_html( $last_test->downtime );
                                    } else {
                                        echo '-';
                                    }
                                    ?>
								</td>
							</tr>
							<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
								<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
									<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html_e( 'Average Response Time', 'wphb' ); ?></span>
								</td>
                                <td class="report-list-item-result" style="border-collapse: collapse !important;font-family: Arial, sans-serif;font-size: 15px;font-weight: 400;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
									<?php echo esc_html( $last_test->response_time ); ?>
								</td>
							</tr>
							<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
								<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
									<span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html_e( 'Up Since', 'wphb' ); ?></span>
								</td>
								<td class="report-list-item-result" style="border-collapse: collapse !important;font-family: Arial, sans-serif;font-size: 15px;font-weight: 400;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top">
									<?php
									if ( isset( $last_test->up_since ) && false !== $last_test->up_since ) {
										$gmt_date  = date( 'Y-m-d H:i:s', $last_test->up_since );
										$site_date = get_date_from_gmt( $gmt_date, get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
										echo $site_date;
									} else {
										echo '-';
									}
									?>
								</td>
							</tr>
							</tbody>
						</table>
						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 0 30px;padding: 0;text-align: left"><a href="<?php echo esc_url( $params['SCAN_PAGE_LINK'] ); ?>" class="brand-button" style="background: #17A8E3;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 20px;margin: 0;padding: 10px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 4px;text-transform: uppercase"><?php esc_html_e( 'View full report', 'wphb' ); ?></a></p>

						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 20px;margin: 0 0 20px;padding: 0;text-align: left"><?php esc_html_e( 'Stay humming.', 'wphb' ); ?></p>
						<strong><?php esc_html_e( 'Hummingbird', 'wphb' ); ?></strong>
						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 15px;margin: 0 0 10px;padding: 0;text-align: left"><?php esc_html_e( 'Performance Hero', 'wphb' ); ?></p>
						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 15px;margin: 0 0 30px;padding: 0;text-align: left"><?php esc_html_e( 'WPMU DEV', 'wphb' ); ?></p>
					</td>
				</tr>
				</tbody>
			</table>

		</td>
	</tr>
	</tbody>
</table>