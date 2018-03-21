<?php if ( $this->has_meta_boxes( 'box-caching' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'box-caching' ); ?>
	</div>
<?php endif; ?>

<div class="row">
	<div class="col-fifth">
		<?php $this->show_tabs(); ?>
	</div><!-- end col-sixth -->

	<div class="col-four-fifths">
		<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
	</div>
</div>

<div class="row">
	<div class="col-half"><?php $this->do_meta_boxes( 'box-caching-left' ); ?></div>
	<div class="col-half"><?php $this->do_meta_boxes( 'box-caching-right' ); ?></div>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'caching' );
		}
	});
</script>