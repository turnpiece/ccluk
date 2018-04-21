<?php
/**
 * Advanced tools.
 */
?>


<div class="sui-row-with-sidenav">
	<?php $this->show_tabs(); ?>

	<?php if ( 'main' === $this->get_current_tab() ) : ?>
		<form id="advanced-general-settings" method="post">
			<?php $this->do_meta_boxes( 'main' ); ?>
		</form>
	<?php elseif ( 'db' === $this->get_current_tab() ) : ?>
		<form id="advanced-db-settings" method="post">
			<?php $this->do_meta_boxes( 'db' ); ?>
		</form>
	<?php else : ?>
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	<?php endif; ?>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'advanced' );
		}
	});
</script>