<?php
/** @var WP_Hummingbird_Settings_Page $this */
?>
<div class="sui-row-with-sidenav">
	<?php $this->show_tabs(); ?>
	<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
</div>

<script>
	jQuery(document).ready( function() {
		window.WPHB_Admin.getModule( 'settings' );
	});
</script>
