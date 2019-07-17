<?php
$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
$ignored = $setting->getIgnore();
?>
<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Ignored", wp_defender()->domain ) ?>
        </h3>
        <div class="sui-actions-left">
			<?php if ( count( $ignored ) ): ?>
                <span class="sui-tag"><?php echo count( $ignored ) ?></span>
			<?php endif; ?>
        </div>
    </div>
    <div class="sui-box-body">
        <p>
			<?php _e( "You have chosen to ignore these fixes. You can restore and action them at any time.", wp_defender()->domain ) ?>
        </p>
		<?php if ( count( $ignored ) == 0 ): ?>
            <div class="sui-notice">
                <p>
					<?php _e( "Well, turns out you haven't ignored anything yet - keep up the good fight!", wp_defender()->domain ) ?>
                </p>
            </div>
		<?php endif; ?>
    </div>
	<?php if ( count( $ignored ) ): ?>
        <div class="sui-accordion sui-accordion-flushed">
			<?php foreach ( $ignored as $rule ): ?>
				<?php
				$rule->showRestoreForm();
				?>
			<?php endforeach; ?>
        </div>
        <div class="clearfix"></div>
        <div class="padding-bottom-30"></div>
	<?php endif; ?>
</div>