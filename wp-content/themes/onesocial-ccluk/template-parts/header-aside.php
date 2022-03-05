<div id="header-aside">
	<div id="header-aside-inner">

		<?php
		$create_new_post_page	 = null;
		$bookmarks_page			 = null;
		$bookmark_post			 = null;

		if ( function_exists( 'buddyboss_sap' ) && buddyboss_is_bp_active() ) {
			$create_new_post_page	 = buddyboss_sap()->option( 'create-new-post' );
			$bookmarks_page			 = buddyboss_sap()->option( 'bookmarks-page' );
			$bookmark_post			 = buddyboss_sap()->option( 'bookmark_post' );
		}
		?>

		<?php if ( buddyboss_is_bp_active() && is_user_logged_in() ) { ?>

			<?php get_template_part( 'template-parts/header-user-messages' ); ?>

			<?php get_template_part( 'template-parts/header-user-notifications' ); ?>

		<?php } ?>

		<?php do_action( 'onesocial_notification_buttons' ); ?>

		<div id="header-search" class="search-form">
			<?php echo get_search_form(); ?>
			<a href="#" id="search-open" class="header-button boss-tooltip" data-tooltip="<?php _e( 'Search', 'onesocial' ); ?>"><i class="bb-icon-search"></i></a>
		</div>

		<?php get_template_part( 'template-parts/header-user-links' ); ?>

	</div>
</div>