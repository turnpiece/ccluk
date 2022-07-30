<?php
/**
 * Shipper email templates: import body
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
</head>

<body style="background-color: #f1f1f1; font-family: Roboto, Arial, sans-serif; letter-spacing: -0.25px; font-size: 18px; line-height: 30px; font-weight: 400; color: #333333; padding-top: 30px">
<table cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f1f1; font-family: Roboto, Arial, sans-serif; letter-spacing: -0.25px; font-size: 18px; line-height: 30px; font-weight: 400; color: #333333; padding-top: 30px; margin-left: auto; margin-right: auto; width: 600px">
	<tbody style="background-color: white">
	<tr>
		<?php if ( Shipper_Helper_Assets::has_custom_hero_image() ) : ?>
			<td style="text-align: center">
				<img
					style="max-width: 600px; max-height: 180px"
					src="<?php echo esc_url( Shipper_Helper_Assets::get_custom_hero_image() ); ?>"
					alt="<?php echo esc_attr( 'Shipper Captain' ); ?>"
				>
			</td>
		<?php else : ?>
			<td style="background-image: linear-gradient(0deg, #33D3F3 0%, #17A8E3 100%); border-top-left-radius: 3px; border-top-right-radius: 3px;">
				<img
					style="max-width: 600px;max-height: 180px;margin-bottom: -10px;padding-top: 30px;"
					src="<?php echo esc_url( Shipper_Helper_Assets::get_image( 'captain-ripple.png' ) ); ?>"
					alt="<?php echo esc_attr( 'Shipper Captain' ); ?>"
				>
			</td>
		<?php endif; ?>
	</tr>

	<tr>
		<td style="padding: 40px 60px 0 60px">
			<?php
			echo ! ! $status
				/* translators: %1$s user name. */
				? esc_html( sprintf( __( 'Ahoy, Captain %s!', 'shipper' ), $name ) )
				/* translators: %1$s user name. */
				: esc_html( sprintf( __( 'Aye, matey %s!', 'shipper' ), $name ) );
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
						__( 'Your site <a style="%1$s" href="http://%2$s" target="_blank">%3$s</a> was successfully imported to <a style="%4$s" href="http://%5$s" target="_blank">%6$s</a>.', 'shipper' ),
						'color: #17A8E3',
						$migration->get_destination( true ),
						$migration->get_destination( true ),
						'color: #17A8E3',
						$migration->get_source( true ),
						$migration->get_source( true )
					)
				)
				: wp_kses_post(
					sprintf(
					/* translators: %1$s %2$s %3$s %4$s %5$s %6$s: source and destination site url. */
						__( 'Alas, Shipper was unable to transfer your data from <a style="%1$s" href="http://%2$s" target="_blank">%3$s</a> to <a style="%4$s" href="http://%5$s" target="_blank">%6$s</a>.', 'shipper' ),
						'color: #17A8E3',
						$migration->get_destination( true ),
						$migration->get_destination( true ),
						'color: #17A8E3',
						$migration->get_source( true ),
						$migration->get_source( true )
					)
				);
			?>
		</td>
	</tr>

	<tr>
		<td style="padding: 20px 60px 0 60px">
			<?php
			! ! $status
				? esc_html_e( 'This calls for drinks all around.', 'shipper' )
				: esc_html_e( 'Check the Shipper logs for details. Our support crew is standing by if you need assistance.', 'shipper' );
			?>
		</td>
	</tr>

	<tr>
		<td style="padding: 30px 60px">
			<?php
			! ! $status
				? esc_html_e( 'Raise your glass!', 'shipper' )
				: esc_html_e( 'Happy Sailing!', 'shipper' );
			?>
		</td>
	</tr>

	<tr>
		<td style="padding: 10px 60px 0 60px">
			<?php esc_html_e( 'Shipper', 'shipper' ); ?>
		</td>
	</tr>

	<tr>
		<td style="padding: 0 60px 100px 60px; font-size: 14px">
			<?php esc_html_e( 'WPMU DEV Migration Hero', 'shipper' ); ?>
		</td>
	</tr>
	</tbody>

	<tfoot style="text-align: center">
	<tr>
		<td style="padding-top: 40px">
			<?php if ( Shipper_Helper_Assets::has_custom_hero_image() ) : ?>
				<img
					style="max-width: 130px; max-height: 50px;"
					src="<?php echo esc_url( Shipper_Helper_Assets::get_custom_hero_image() ); ?>"
					alt="<?php echo esc_attr( 'Custom logo' ); ?>"
				>
			<?php else : ?>
				<img
					style="max-width: 130px; max-height: 50px;"
					src="<?php echo esc_url( Shipper_Helper_Assets::get_image( 'wpmudev-logo.png' ) ); ?>"
					alt="<?php echo esc_attr( 'WPMU DEV logo' ); ?>"
				>
			<?php endif; ?>
		</td>
	</tr>

	<?php if ( Shipper_Helper_Assets::has_custom_footer() ) : ?>
		<tr>
			<td style="font-size: 14px; font-style: italic; color: #666666; padding-top: 30px;">
				<?php echo wp_kses_post( Shipper_Helper_Assets::get_custom_footer() ); ?>
			</td>
		</tr>
	<?php else : ?>
		<tr>
			<td style="font-size: 14px; font-style: italic; color: #666666; padding-top: 30px;">
				<p style="margin: 0; line-height: 20px"><?php esc_html_e( 'Everything You Need For WordPress.', 'shipper' ); ?></p>
				<p style="margin: 0; line-height: 20px"><?php esc_html_e( 'One place, one low price, unlimited sites.', 'shipper' ); ?></p>
			</td>
		</tr>

		<tr>
			<td style="font-size: 10px; color: #AAAAAA; line-height: 30px; padding-top: 30px">
				<?php esc_html_e( 'INCSUB PO BOX 163, ALBERT PARK, VICTORIA.3206 AUSTRALIA', 'shipper' ); ?>
			</td>
		</tr>
	<?php endif; ?>
	</tfoot>
</table>
</body>
</html>