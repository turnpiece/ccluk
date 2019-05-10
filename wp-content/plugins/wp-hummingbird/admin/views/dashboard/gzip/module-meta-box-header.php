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
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="sui-actions-left">
		<span class="sui-tag sui-tag-warning"><?php echo intval( $issues ); ?></span>
	</div>
<?php endif; ?>
