<div class="row">
	<?php $this->do_meta_boxes( 'box-gzip-top' ); ?>
	<?php $this->do_meta_boxes( 'box-gzip-bottom' ); ?>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'gzip' );
		}
	});
</script>