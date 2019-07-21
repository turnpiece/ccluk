<?php
/**
 * Shipper migrate pages: begin migration partial
 *
 * @package shipper
 */

// Just show the all-good preflight check result page.
$this->render('pages/migration/selection-check', array(
	'destinations' => $destinations,
	'type' => $type,
	'site' => $site,
));
