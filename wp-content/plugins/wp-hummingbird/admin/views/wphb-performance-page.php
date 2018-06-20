<?php
/**
 * Render page.
 *
 * @package Hummingbird
 *
 * @var array|wp_error $report  Report, set in render_inner_content().
 */

if ( $this->has_meta_boxes( 'summary' ) ) {
	$this->do_meta_boxes( 'summary' );
} ?>

<div class="sui-row-with-sidenav">
	<?php if ( $report ) : ?>
		<?php $this->show_tabs(); ?>
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	<?php else : ?>
		<?php $this->do_meta_boxes( 'main' ); ?>
	<?php endif; ?>
</div><!-- end row -->

<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'performance' );
	});
</script>