<?php
/**
 * Main template file.
 *
 * @package Hummingbird
 * @var array $params  Parameters array: USER_NAME, SCAN_PAGE_LINK, SITE_MANAGE_URL, SITE_URL, SITE_NAME.
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

						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 0 30px;padding: 0;text-align: left"><?php esc_html_e( 'It’s Hummingbird here, straight from the', 'wphb' ); ?> <strong><a class="brand" href="<?php echo esc_attr( $params['SITE_MANAGE_URL'] ); ?>" target="_blank" style="color: #333;font-family: Arial, sans-serif;font-weight: inherit;line-height: 30px;margin: 0;padding: 0;text-align: left;text-decoration: none"><?php echo esc_html( $params['SITE_URL'] ); ?></a></strong> <?php esc_html_e( 'engine room. Here’s your latest Performance Test summary.', 'wphb' ); ?></p>

						<table class="reports-list" align="center" style="border-collapse: collapse;border-spacing: 0;border-top: 1px solid #e6e6e6;margin: 0 0 30px;padding: 0;text-align: left;vertical-align: top;width: 100%">
							<tbody>
							<?php foreach ( $last_test->rule_result as $rule => $rule_result ) : ?>
								<tr class="report-list-item" style="border-bottom: 1px solid #E6E6E6;padding: 0;text-align: left;vertical-align: top">
									<td class="report-list-item-info" style="border-collapse: collapse !important;color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;padding: 10px 0;text-align: left;vertical-align: top">
										<?php if ( 'aplus' === $rule_result->impact_score_class || 'a' === $rule_result->impact_score_class || 'b' === $rule_result->impact_score_class ) : ?>
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-ok.png' ); ?>" alt="<?php esc_attr_e( 'Ok', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 10px 0 0; outline: none; text-decoration: none; width: auto; vertical-align: middle;" /><span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html( $rule_result->label ); ?></span>
										<?php elseif ( 'c' === $rule_result->impact_score_class || 'd' === $rule_result->impact_score_class ) : ?>
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-warning.png' ); ?>" alt="<?php esc_attr_e( 'Warning', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 10px 0 0; outline: none; text-decoration: none; width: auto; vertical-align: middle;" /><span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html( $rule_result->label ); ?></span>
										<?php elseif ( 'e' === $rule_result->impact_score_class || 'f' === $rule_result->impact_score_class ) : ?>
											<img src="<?php echo esc_url( WPHB_DIR_URL . 'core/pro/modules/reporting/templates/images/icon-error.png' ); ?>" alt="<?php esc_attr_e( 'Critical', 'wphb' ); ?>" style="-ms-interpolation-mode: bicubic; border: none; clear: both; float: left; display: inline-block; margin: 5px 10px 0 0; outline: none; text-decoration: none; width: auto; vertical-align: middle;" /><span style="color: inherit; display: inline; font-size: inherit; font-family: inherit; line-height: inherit; vertical-align: middle;"><?php echo esc_html( $rule_result->label ); ?></span>
										<?php endif; ?>
									</td>
									<?php if ( 'aplus' === $rule_result->impact_score_class || 'a' === $rule_result->impact_score_class || 'b' === $rule_result->impact_score_class ) : ?>
										<td class="report-list-item-result ok" style="border-collapse: collapse !important;color: #1ABC9C;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top"><?php echo absint( $rule_result->impact_score ); ?>/100</td>
									<?php elseif ( 'c' === $rule_result->impact_score_class || 'd' === $rule_result->impact_score_class ) : ?>
										<td class="report-list-item-result warning" style="border-collapse: collapse !important;color: #FECF2F;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top"><?php echo absint( $rule_result->impact_score ); ?>/100</td>
									<?php elseif ( 'e' === $rule_result->impact_score_class || 'f' === $rule_result->impact_score_class ) : ?>
										<td class="report-list-item-result critical" style="border-collapse: collapse !important;color: #FF6D6D;font-family: Arial, sans-serif;font-size: 15px;font-weight: 700;line-height: 30px;margin: 0;min-width: 65px;padding: 10px 0;text-align: right;vertical-align: top"><?php echo absint( $rule_result->impact_score ); ?>/100</td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<p style="color: #555555;font-family: Arial, sans-serif;font-size: 15px;font-weight: normal;line-height: 30px;margin: 0 0 30px;padding: 0;text-align: left"><a href="<?php echo esc_url( $params['SCAN_PAGE_LINK'] ); ?>" class="brand-button" style="background: #17A8E3;color: #ffffff;font-family: Arial, sans-serif;font-size: 12px;font-weight: normal;line-height: 20px;margin: 0;padding: 10px 20px;text-align: center;text-decoration: none;display: inline-block;border-radius: 4px;text-transform: uppercase"><?php esc_html_e( 'View full report', 'wphb' ); ?></a></p>
					</td>
				</tr>
				</tbody>
			</table>

		</td>
	</tr>
	</tbody>
</table>