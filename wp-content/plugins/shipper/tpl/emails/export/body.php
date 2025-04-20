<?php
/**
 * Shipper email templates: export body
 *
 * @since 1.2.6
 * @package shipper
 */

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>">
	<title><?php bloginfo( 'name' ); ?></title>
	<style>
	table tr td>a:hover {
		color: #0C33A9 !important;
	}
	</style>
</head>

<body
	style="background-color: #f1f1f1; font-family: Roboto, Arial, sans-serif; letter-spacing: -0.25px; font-size: 18px; line-height: 30px; font-weight: 400; color: #333333; padding-top: 30px">
	<table cellpadding="0" cellspacing="0" border="0"
		style="background-color: #f1f1f1; font-family: Roboto, Arial, sans-serif; letter-spacing: -0.25px; font-size: 18px; line-height: 30px; font-weight: 400; color: #333333; padding-top: 30px; margin-left: auto; margin-right: auto; width: 600px">
		<tbody>
			<tr>
				<?php if ( Shipper_Helper_Assets::has_custom_hero_image() ) : ?>
				<td style="text-align: center">
					<img style="max-width: 600px; max-height: 180px"
						src="<?php echo esc_url( Shipper_Helper_Assets::get_custom_hero_image() ); ?>"
						alt="<?php echo esc_attr__( 'Shipper Captain', 'shipper' ); ?>">
				</td>
				<?php else : ?>
				<td style="direction:ltr;font-size:0px;text-align:center">

					<div class="mj-column-per-100"
						style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
							<tbody>
								<tr>
									<td id="wpmudev-hero"
										style="background-color:#7b5499;border-radius:20px 20px 0 0;vertical-align:middle;height: 100px;width:600px">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation"
											width="100%">
											<tbody>
												<tr>
													<td align="center"
														style="font-size:0px;padding:0px 25px;word-break:break-word">
														<table border="0" cellpadding="0" cellspacing="0"
															role="presentation"
															style="border-collapse:collapse;border-spacing:0px">
															<tbody>
																<tr>
																	<td>
																		<img id="wpmudev-logo"
																			src="<?php echo esc_attr( Shipper_Helper_Assets::get_image( 'shipper-icon.png' ) ); ?>"
																			alt="<?php esc_attr_e( 'Shipper Logo', 'shipper' ); ?>"
																			style="display:block;">
																	</td>
																	<td style="padding-left: 15px;">
																		<span
																			style="display: block;color:#FFF;font-size: 20px;font-weight: 700;font-family: 'Roboto';">
																			<?php esc_html_e( 'Site import', 'shipper' ); ?>
																		</span>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

				</td>
				<?php endif; ?>
			</tr>
			<tr style="background-color: white;">
				<td>

					<table>
						<tbody>
							<tr>
								<td style="padding: 40px 60px 0 60px">
									<?php
									echo esc_html( sprintf( __( 'Hi %s,', 'shipper' ), $name ) );
									?>
								</td>
							</tr>

							<tr>
								<td style="padding: 20px 60px 0 60px">
									<?php
									echo ! ! $status
									? wp_kses_post(
										sprintf(
										/* translators: %1$s %2$s %3$s %4$s %5$s %6$s: source and destination site url. */
											__( 'Your site <a style="%1$s" href="http://%2$s" target="_blank">%3$s</a> was successfully ported to <a style="%4$s" href="http://%5$s" target="_blank">%6$s</a>.', 'shipper' ),
											'color: #0059FF',
											$migration->get_source( true ),
											$migration->get_source( true ),
											'color: #0059FF',
											$migration->get_destination( true ),
											$migration->get_destination( true )
										)
									)
									: wp_kses_post(
										sprintf(
										/* translators: %1$s %2$s %3$s %4$s %5$s %6$s: source and destination site url. */
											__( 'Unfortunately, Shipper was unable to transfer your data from <a style="%1$s" href="http://%2$s" target="_blank">%3$s</a> to <a style="%4$s" href="http://%5$s" target="_blank">%6$s</a>.', 'shipper' ),
											'color: #0059FF',
											$migration->get_source( true ),
											$migration->get_source( true ),
											'color: #0059FF',
											$migration->get_destination( true ),
											$migration->get_destination( true )
										)
									);
									?>
								</td>
							</tr>
							<tr>
								<td style="padding-top: 40px;">
								</td>
							</tr>
							<tr>
								<td style="padding: 10px 60px 0 60px">
									<?php esc_html_e( 'Cheers,', 'shipper' ); ?>
								</td>
							</tr>

							<tr>
								<td style="padding: 0 60px 45px 60px;">
									<?php esc_html_e( 'The WPMU DEV Team.', 'shipper' ); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<tr>
				<td style="border-radius:0 0 15px 15px;height: 80px;width:600px;background-color: #E7F1FB;">
					<table style="margin: auto;" role="presentation">
						<tbody>
							<tr>
								<td>
									<img id="wpmudev-logo"
										src="<?php echo esc_attr( Shipper_Helper_Assets::get_image( 'incsub-icon.png' ) ); ?>"
										alt="<?php esc_attr_e( 'Shipper Logo', 'shipper' ); ?>" style="display:block;">
								</td>
								<td style="padding-left: 10px;">
									<span
										style="display:block;color:#fff;font-size: 14px;font-weight:700;font-family:'Roboto';max-width: 145px;line-height: 16px;color: #1A1A1A;">
										<?php esc_html_e( 'Build A Better WordPress Business', 'shipper' ); ?> </span>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>

		</tbody>

		<tfoot style="text-align: center">

			<?php if ( Shipper_Helper_Assets::has_custom_footer() ) : ?>
			<tr>
				<td style="font-size: 14px; font-style: italic; color: #666666; padding-top: 30px;">
					<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_footer() ); ?>
				</td>
			</tr>
			<?php else : ?>
			<tr>
				<td style="direction:ltr;font-size:0px;padding:25px 20px 15px;text-align:center;">
					<!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:560px;" ><![endif]-->
					<div class="mj-column-per-100 mj-outlook-group-fix"
						style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
						<table border="0" cellpadding="0" cellspacing="0" role="presentation"
							style="vertical-align:top;" width="100%">
							<tbody>
								<tr>
									<td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0"
											role="presentation" style="float:none;display:inline-table;">
											<tr>
												<td style="vertical-align:middle;">
													<span
														style="color:#333333;font-size:13px;font-weight:700;font-family:Roboto, Arial, sans-serif;line-height:25px;text-decoration:none;"><?php esc_html_e( 'Follow us', 'shipper' ); ?></span>
												</td>
											</tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0"
											role="presentation" style="float:none;display:inline-table;">
											<tr>
												<td
													style="padding:1px;vertical-align:middle; font-size:0;height:25px;vertical-align:middle;width:25px;text-align: center;">

													<a href="https://www.facebook.com/wpmudev" target="_blank">
														<img height="25"
															src="<?php echo esc_attr( Shipper_Helper_Assets::get_image( 'mail-button-logo-facebook.png' ) ); ?>"
															style="height: 12px;width: auto;display: inline-block;position: absolute;bottom: 1px;"
															width="25" />
													</a>
												</td>
											</tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0"
											role="presentation" style="float:none;display:inline-table;">
											<tr>
												<td
													style="padding:1px;vertical-align:middle;font-size:0;height:25px;vertical-align:middle;width:25px;text-align: center;">

													<a href="https://www.instagram.com/wpmu_dev/" target="_blank">
														<img height="25"
															src="<?php echo esc_attr( Shipper_Helper_Assets::get_image( 'mail-button-logo-instagram.png' ) ); ?>"
															style="height: 12px;width: auto;display: inline-block;position: absolute;bottom: 1px;"
															width="25" />
													</a>
												</td>
											</tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0"
											role="presentation" style="float:none;display:inline-table;">
											<tr>
												<td
													style="padding:1px;vertical-align:middle;font-size:0;height:25px;vertical-align:middle;width:25px;text-align: center;">

													<a style="display: block;height: 34px;"
														href="https://twitter.com/wpmudev" target="_blank">
														<img height="25"
															src="<?php echo esc_attr( Shipper_Helper_Assets::get_image( 'mail-button-logo-twitter.png' ) ); ?>"
															style="height: 26px;width:auto;display:inline-block;"
															width="25" />
													</a>
												</td>
											</tr>
										</table>
										<!--[if mso | IE]></td></tr></table><![endif]-->
									</td>
								</tr>
								<tr>
									<td
										style="font-size:10px;color: #505050;line-height: 15px;text-align: center;font-weight: 400;">
										<?php esc_html_e( 'INCSUB PO BOX 163, ALBERT PARK, VICTORIA.3206 AUSTRALIA', 'shipper' ); ?>
									</td>
								</tr>
								<!-- <tr>
									<td style="text-align: center;">
										<a style="font-size:10px;color: #000000;line-height: 30px;font-weight: 400;"
											href="#" target="_blank">
											<?php// esc_html_e( 'Unsubscribe', 'shipper' ); ?>
										</a>
									</td>
								</tr> -->
							</tbody>
						</table>
					</div>
					<!--[if mso | IE]></td></tr></table><![endif]-->
				</td>
			</tr>

			<?php endif; ?>
		</tfoot>
	</table>
</body>

</html>