<?php
$path         = forminator_plugin_url();
$suffix       = '.png';
$empty_icon   = $path . 'assets/img/providers/icons/no-image' . $suffix;
$empty_icon2x = $path . 'assets/img/providers/icons/no-image@2x' . $suffix;
if ( empty( $form_id ) ) {
	$form_id = 0;
}

$show_action       = false;
$icon_class_action = 'sui-icon-plus';
if ( isset( $addon['is_settings_available'] ) && ! empty( $addon['is_settings_available'] ) && true === $addon['is_settings_available'] ) {
	$show_action = true;
	if ( $addon['is_connected'] ) {
		$icon_class_action = 'sui-icon-widget-settings-config';
	}
}

$tooltip    = __( 'Configure Integration', Forminator::DOMAIN );
$action     = 'forminator_addon_settings';
$multi_id   = 0;
$multi_name = false;

if ( ! empty( $form_id ) ) {
	$action            = 'forminator_addon_form_settings';
	$show_action       = false;
	$icon_class_action = 'sui-icon-plus';
	if ( isset( $addon['is_form_settings_available'] ) && ! empty( $addon['is_form_settings_available'] ) && true === $addon['is_form_settings_available'] ) {
		$show_action = true;
		if ( $addon['is_form_connected'] ) {
			if ( $addon['is_allow_multi_on_form'] ) {
				if ( isset( $addon['multi_id'] ) ) {
					$icon_class_action = 'sui-icon-widget-settings-config';
					$tooltip           = __( 'Configure Integration', Forminator::DOMAIN );
					$multi_id          = $addon['multi_id'];
					$multi_name        = $addon['multi_name'];
				} else {
					$icon_class_action = 'sui-icon-plus';
					$tooltip           = __( 'Add Integration', Forminator::DOMAIN );
				}
			} else {
				$icon_class_action = 'sui-icon-widget-settings-config';
				$tooltip           = __( 'Configure Integration', Forminator::DOMAIN );
			}
		} else {
			$tooltip = __( 'Add Integration', Forminator::DOMAIN );
		}
	}
}

$action_available = false;
if ( ! empty( $show_pro_info ) && $show_pro_info ) {
	$show_pro_info = true;
} else {
	$show_pro_info = false;
}

/**
 * force Disable pro tag y default
 */
$show_pro_info = false;

$pro_url        = 'https://premium.wpmudev.org';
$pro_url_target = '_blank';

?>
<tr <?php if( $is_active ) { echo 'class="fui-integration-enabled"'; } // phpcs:ignore ?>>
	<td class="sui-table-image" aria-hidden="true"><?php if ( isset( $addon['icon'] ) && ! empty( $addon['icon'] ) ) { ?>
			<img src="<?php echo esc_url( $addon['icon'] ); ?>"
			     srcset="<?php echo esc_url( $addon['icon'] ); ?> 1x, <?php echo esc_url( $addon['icon_x2'] ); ?> 2x"
			     alt="<?php echo esc_attr( $addon['short_title'] ); ?>"
			     class="sui-image sui-image-center">
		<?php } else { ?>
			<img src="<?php echo esc_url( $empty_icon ); ?>"
			     srcset="<?php echo esc_url( $empty_icon ); ?> 1x, <?php echo esc_url( $empty_icon2x ); ?> 2x"
			     alt="<?php echo esc_attr( $addon['short_title'] ); ?>"
			     class="sui-image sui-image-center">
		<?php } ?></td>

	<td><strong><?php echo esc_html( $addon['title'] ); ?></strong>
		<span class="sui-table-actions-left">
			<?php if ( $show_pro_info && $addon['is_pro'] ): ?>
				<span class="sui-tag sui-tag-pro">
						<?php esc_html_e( "PRO", Forminator::DOMAIN ); ?>
				</span>
			<?php endif; ?>
		</span>
	</td>

	<td>
		<?php if ( ! empty( $multi_name ) ): ?>
			<span class="">
						<?php echo esc_html( $multi_name ); ?>
				</span>
		<?php endif; ?>
	</td>

	<td class="sui-table-action">
		<?php if ( $show_action ) : ?>
			<a class="sui-button-icon sui-tooltip sui-tooltip-top-left connect-integration"
			   data-slug="<?php echo esc_attr( $addon['slug'] ); ?>"
			   data-tooltip="<?php echo esc_attr( $tooltip ); ?>"
			   data-title="<?php echo esc_attr( $addon['title'] ); ?>"
			   data-image="<?php echo esc_attr( $addon['image'] ); ?>"
			   data-imagex2="<?php echo esc_attr( $addon['image_x2'] ); ?>"
			   data-nonce="<?php echo wp_create_nonce( 'forminator_addon_action' ); // WPCS: XSS ok. ?>"
			   data-action="<?php echo esc_attr( $action ); ?>"
			   data-form-id="<?php echo esc_attr( $form_id ); ?>"
			   data-multi-id="<?php echo esc_attr( $multi_id ); ?>"
			   role="button">
				<i class="<?php echo esc_attr( $icon_class_action ); ?>" aria-hidden="true"></i>
			</a>
		<?php endif; ?>
	</td>
</tr>