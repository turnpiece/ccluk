<div class="row">
	<div class="col-half"><?php $this->do_meta_boxes( 'box-gzip-left' ); ?></div>
	<div class="col-half"><?php $this->do_meta_boxes( 'box-gzip-right' ); ?></div>
</div>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'gzip' );	   			 	 		  		   		
		}
	});
</script>