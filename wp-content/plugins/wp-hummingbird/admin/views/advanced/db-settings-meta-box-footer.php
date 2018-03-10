<?php
/**
 * Advanced tools database cleanup settings meta box footer.
 *
 * @package Hummingbird
 * @since 1.8
 */

if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
	<div class="content-box-two-cols-image-left">
		<div class="wphb-block-entry-content wphb-upsell-free-message">
			<?php printf(
				__( '<p>Regular cleanups of your database ensures you’re regularly removing extra bloat which can slow down your host server. Upgrade to Hummingbird Pro as part of a WPMU DEV membership to unlock this feature today! <a href="%s" target="_blank">Learn More</a>.</p>', 'wphb' ),
				WP_Hummingbird_Utils::get_link( 'plugin', 'hummingbird_dbcleanup_schedule_upsell_link' )
			); ?>
		</div>
	</div>
<?php else : ?>
	<div class="buttons buttons-on-right">
		<span class="spinner standalone"></span>
		<input type="submit" class="button" name="submit" value="<?php esc_attr_e( 'Save changes', 'wphb' ); ?>">
	</div>
<?php endif; ?>