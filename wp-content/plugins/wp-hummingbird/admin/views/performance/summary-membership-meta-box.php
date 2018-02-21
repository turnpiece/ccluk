<div class="wphb-block-entry">

	<div class="wphb-block-entry-content">

		<div class="content">
			<a id="wphb-upgrade-membership-modal-link" class="hidden" href="#wphb-upgrade-membership-modal" rel="dialog">
				<?php _e( 'Upgrade Membership', 'wphb' ); ?>
			</a>
		</div><!-- end content -->

	</div><!-- end wphb-block-entry-content -->

</div><!-- end wphb-block-entry -->

<?php
	wphb_membership_modal();
?>

<script>
	jQuery( document).ready( function() {
		window.WPHB_Admin.utils.membershipModal.open();
	});
</script>