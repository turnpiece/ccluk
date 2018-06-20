<?php
/**
 * Caching page layout.
 *
 * @package Hummingbird
 */

if ( $this->has_meta_boxes( 'summary' ) ) {
	$this->do_meta_boxes( 'summary' );
}

if ( $this->has_meta_boxes( 'box-caching' ) ) {
	$this->do_meta_boxes( 'box-caching' );
} ?>

<div class="sui-row-with-sidenav">
	<?php $this->show_tabs(); ?>

	<?php if ( 'main' === $this->get_current_tab() ) : ?>
		<form id="page-caching-form" method="post">
			<?php $this->do_meta_boxes( 'main' ); ?>
		</form>
	<?php elseif ( 'rss' === $this->get_current_tab() ) : ?>
		<form id="rss-caching-settings" method="post">
			<?php $this->do_meta_boxes( 'rss' ); ?>
		</form>
	<?php elseif ( 'settings' === $this->get_current_tab() ) : ?>
		<form id="other-caching-settings" method="post">
			<?php $this->do_meta_boxes( 'settings' ); ?>
		</form>
	<?php else : ?>
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	<?php endif; ?>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'caching' );
		}
	});
</script>