<?php
/**
 * Shipper email templates: export migration subject
 *
 * @package shipper
 */

if ( ! ! $status ) {
	echo esc_html( 
		sprintf(
			__('Shipper successfully Exported your site from %s', 'shipper' ),
			$migration->get_source()
		)
	);
} else {
	esc_html_e( 'Shipper Encountered An Error', 'shipper' );
}
