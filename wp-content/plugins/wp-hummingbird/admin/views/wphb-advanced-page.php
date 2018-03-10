<?php
/**
 * Advanced tools.
 */
?>

<div class="row">
	<div class="col-fifth">
		<?php $this->show_tabs(); ?>
	</div><!-- end col-sixth -->

	<div class="col-four-fifths">
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	</div>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'advanced' );
		}
	});
</script>