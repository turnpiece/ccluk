<?php
$setting  = \WP_Defender\Module\Hardener\Model\Settings::instance();
$resolved = $setting->getFixed();
?>
<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Resolved", wp_defender()->domain ) ?>
        </h3>
        <div class="sui-actions-left">
			<?php if ( count( $resolved ) ): ?>
                <span class="sui-tag sui-tag-success"><?php echo count( $resolved ) ?></span>
			<?php endif; ?>
        </div>
    </div>
    <div class="sui-box-body">
        <p>
			<?php _e( "Excellent work. The following vulnerabilities have been fixed.", wp_defender()->domain ) ?>
        </p>
    </div>
	<?php if ( count( $resolved ) ): ?>
        <div class="sui-accordion sui-accordion-flushed">
			<?php foreach ( $resolved as $rule ): ?>
				<?php
				$rule->getDescription();
				?>
			<?php endforeach; ?>
        </div>
        <div class="clearfix"></div>
        <div class="padding-bottom-30"></div>
	<?php endif; ?>
</div>