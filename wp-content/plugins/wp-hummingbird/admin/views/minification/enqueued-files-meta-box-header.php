<h3><?php echo esc_html( $title ); ?></h3>
<div class="buttons buttons-group">
    <p class="wphb-label-notice-inline hide-to-mobile"><?php _e( 'Not seeing all your files in this list?', 'wphb' ); ?></p>
    <input type="submit" class="button button-ghost" name="clear-cache" value="<?php esc_attr_e( 'Re-Check Files', 'wphb' ); ?>"/>
    <a href="<?php echo esc_url( add_query_arg( 'wphb-clear', 'true' ) ); ?>" class="button button-ghost"><?php _e( 'Start over', 'wphb' ); ?></a>
</div>