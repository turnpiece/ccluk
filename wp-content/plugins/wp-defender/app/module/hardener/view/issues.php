<?php
$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
$issues  = $setting->getIssues();
?>
<div class="sui-box-header">
    <h2 class="sui-box-title"><?php _e( "Issues", wp_defender()->domain ) ?></h2>
    <div class="sui-actions-left">
		<?php if ( count( $issues ) ): ?>
            <span class="sui-tag sui-tag-warning"><?php echo count( $issues ) ?></span>
		<?php endif; ?>
    </div>
</div>
<div class="sui-box-body">
    <p>
		<?php _e( "Activate security tweaks to strengthen your website against harmful hackers and bots who try to break in. We recommend you action as many tweaks as possible, some may require your server provider to help.", wp_defender()->domain ) ?>
    </p>
	<?php
	if ( count( $issues ) == 0 ) {
		?>
        <div class="sui-notice sui-notice-success">
            <p>
				<?php _e( "You have actioned all available security tweaks, great work!", wp_defender()->domain ) ?>
            </p>
        </div>
		<?php
	}
	?>
</div>
<?php if ( count( $issues ) ) : ?>
    <div class="sui-accordion sui-accordion-flushed">
		<?php
		foreach ( $issues as $rule ) {
			$rule->getDescription();
		} ?>
    </div>
    <div class="clearfix"></div>
    <div class="padding-bottom-30"></div>
<?php endif; ?>