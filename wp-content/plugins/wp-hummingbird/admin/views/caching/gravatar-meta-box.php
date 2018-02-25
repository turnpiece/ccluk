<?php
/**
 * Gravatar caching meta box.
 *
 * @package Hummingbird
 *
 * @var string        $activate_url      Activation URL.
 * @var bool|WP_Error $error             Error if present.
 */

?>
<div class="row settings-form with-bottom-border">
	<p><?php esc_html_e( 'Gravatar Caching stores local copies of avatars used in comments and in your theme. You can control how often you want the cache purged depending on how your website is set up.', 'wphb' ); ?></p>

	<?php if ( is_wp_error( $error ) ) : ?>
		<div class="wphb-caching-error wphb-notice wphb-notice-error">
			<p><?php echo esc_html( $error->get_error_message() ); ?></p>
		</div>
	<?php else : ?>
		<div class="wphb-caching-success wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'Gravatar Caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div><!-- end row -->

<!--
<div class="row settings-form with-bottom-border">
	<div class="col-third">
		<strong><?php esc_html_e( 'Cache Length', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'Choose the length of time you want to cache avatars for before Hummingbird will request new ones from Gravatar.', 'wphb' ); ?>
		</span>
	</div>
	<div class="col-two-third">
		<small>
			<?php esc_html_e( 'Tip: the longer the better - you can always purge the cache manually.', 'wphb' ); ?>
		</small>
	</div>
</div>
-->

<div class="row settings-form">
	<div class="col-third">
		<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
		<span class="sub">
			<?php esc_html_e( 'This will deactivate Gravatar Caching and clear your local avatar storage.', 'wphb' ); ?>
		</span>
	</div><!-- end col-third -->
	<div class="col-two-third">
		<a href="<?php echo esc_url( $deactivate_url ); ?>" class="button button-ghost">
			<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
		</a>
	</div><!-- end col-two-third -->
</div><!-- end row -->