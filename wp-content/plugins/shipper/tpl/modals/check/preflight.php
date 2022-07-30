<?php
/**
 * Shipper modals: preflight check modal resolution template
 *
 * @package shipper
 */

$args = array(
	'destinations' => $destinations,
	'site'         => $site,
);

if ( $ctrl->is_done() ) {
	$this->render( 'modals/check/preflight-done', $args );
} else {
	$this->render( 'modals/check/preflight-working', $args );
}