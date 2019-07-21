<?php
/**
 * Shipper email templates: import migration subject
 *
 * @package shipper
 */

if ( ! ! $status ) {
	echo esc_html( 
		sprintf(
			__('Shipper successfully Imported your site to %s', 'shipper' ),
			$migration->get_source()
		)
	);
} else {
	esc_html_e( 'Shipper Encountered An Error', 'shipper' );
}
