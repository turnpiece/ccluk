<?php
/**
 * Gzip meta box header on dashboard page.
 *
 * @package Hummingbird
 *
 * @var int    $issues      Number of issues.
 * @var string $title       Module title.
 */

?>
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="wphb-pills"><?php echo intval( $issues ); ?></div>
<?php endif; ?>