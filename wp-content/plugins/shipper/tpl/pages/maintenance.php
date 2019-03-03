<?php
/**
 * Shipper templates: maintenance page template
 *
 * @package shipper
 */

?><!doctype html !>
<html>
	<head>
		<meta name="robots" content="noindex,nofollow" />
		<style>
		body {
			background-color: #0B172C;
			position: relative;
			font-size: 48px;
			font-family: sans-serif;
			color: #fff;
		}
		.maintenance-message {
			position: absolute;
			top: 50%;
			left: 50%;
			text-align: center;
			transform: translate(-50%, -50%);
		}
		h1 {
			text-transform: lowercase;
		}
		</style>
	</head>

	<body>
		<div class="maintenance-message">
			<h1><?php esc_html_e( 'Scheduled maintenance', 'shipper' ); ?></h1>
			<p>
				<?php esc_html_e( 'We are currently doing some super-awesome work on this site.', 'shipper' ); ?>
				<?php esc_html_e( 'This work also happens to be super-secret as well.', 'shipper' ); ?>
				<?php esc_html_e( 'Please, come back later.', 'shipper' ); ?>
			</p>
		</div>
	</body>
</html>