<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = compact( 'last_test', 'params' );
?>

<table class="body" style="Margin: 0; background-color: #e9ebe7; border-collapse: collapse; border-spacing: 0; color: #555555; font-family: Arial, sans-serif; font-size: 15px; font-weight: normal; height: 100%; line-height: 26px; margin: 0; padding: 0; text-align: left; vertical-align: top; width: 100%;">
	<tbody>
	<tr style="padding: 0; text-align: left; vertical-align: top;">
		<td class="center" align="center" valign="top" style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">

			<center style="min-width: 600px; width: 100%;">

				<table class="container" style="Margin: 0 auto; background: #fefefe; background-color: #fff; border-collapse: collapse; border-spacing: 0; margin: 0 auto; padding: 0; text-align: inherit; vertical-align: top; width: 600px;">
					<tbody>
					<tr style="padding: 0; text-align: left; vertical-align: top;">
						<td style="-moz-hyphens: auto; -webkit-hyphens: auto; Margin: 0; border-collapse: collapse !important; color: #555555; font-family: Arial, sans-serif; font-size: 15px; font-weight: normal; hyphens: auto; line-height: 26px; margin: 0; padding: 0; text-align: left; vertical-align: top; word-wrap: break-word;">

							<?php WP_Hummingbird_Module_Reporting::load_template( 'header', $args ); ?>
							<?php WP_Hummingbird_Module_Reporting::load_template( 'main', $args ); ?>
							<?php WP_Hummingbird_Module_Reporting::load_template( 'footer', $args ); ?>

						</td>
					</tr>
					</tbody>
				</table>

			</center>

		</td>
	</tr>
	</tbody>
</table>