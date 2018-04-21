<?php
/**
 * Advanced tools database cleanup settings meta box footer.
 *
 * @package Hummingbird
 * @since 1.8
 */

if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="content-box-two-cols-image-left sui-upsell-row">
		<div class="wphb-block-entry-content wphb-upsell-free-message">
			<?php printf(
				__( '<p>Regular cleanups of your database ensures you’re regularly removing extra bloat which can slow down your host server. Upgrade to Hummingbird Pro as part of a WPMU DEV membership to unlock this feature today! <a href="%s" target="_blank">Learn More</a>.</p>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dbcleanup_schedule_upsell_link' )
			); ?>
		</div>
	</div>
<?php else : ?>
	<div class="sui-actions-right">
		<i class="sui-icon-loader sui-loading sui-fw sui-hidden" aria-hidden="true"></i>
		<input type="submit" class="sui-button sui-button-primary" name="submit" value="<?php esc_attr_e( 'Save changes', 'wphb' ); ?>">
	</div>
<?php endif; ?>