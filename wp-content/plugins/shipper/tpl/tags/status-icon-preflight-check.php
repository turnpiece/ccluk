<?php
/**
 * Shipper tag templates: preflight check status icon
 *
 * @since v1.0.3
 * @package shipper
 */

$item = ! empty( $item ) && is_array( $item )
	? $item
	: array();

$icon = 'ok' === $item['status']
	? 'check-tick'
	: 'warning-alert';

$status = 'ok' !== $item['status']
	? $item['status']
	: 'success';

$this->render(
	'tags/status-icon',
	array(
		'icon' => $icon,
		'status' => $status,
	)
);
