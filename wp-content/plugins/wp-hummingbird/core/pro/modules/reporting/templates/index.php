<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = compact( 'last_test', 'params' );
?>
<body style="-moz-box-sizing: border-box; -ms-text-size-adjust: 100%; -webkit-box-sizing: border-box; -webkit-text-size-adjust: 100%; Margin: 0; background-color: #e9ebe7; box-sizing: border-box; color: #555555; font-family: Arial, sans-serif; font-size: 15px; font-weight: normal; line-height: 26px; margin: 0; min-width: 100%; padding: 0; text-align: left; width: 100% !important;">
	<?php WP_Hummingbird_Module_Reporting::load_template( 'body', $args ); ?>
<!-- end body -->
</body>