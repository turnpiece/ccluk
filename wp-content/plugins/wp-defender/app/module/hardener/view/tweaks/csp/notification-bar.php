<div class="sui-wrap">
    <form method="post" action="<?php echo admin_url( 'admin-ajax.php' ) ?>">
		<?php wp_create_nonce( 'defender-csp-debug-staging' ) ?>
        <input type="hidden" name="action" value="defender-csp-debug-staging"/>
        <div class="sui-notice-top sui-notice-info sui-cant-dismiss defender-csp-debug-staging">
            <div class="sui-notice-content">
                <p>
					<?php _e( "The values have been added. You can now enforce the header or keep adjusting other directives. If you add values be sure to test before enforcing the header.", wp_defender()->domain ) ?>
                </p>
            </div>
            <span class="sui-cnotice-dismiss">
		        <button type="submit" class="sui-button-icon"><i class="sui-icon-check"></i></button>
            </span>
        </div>
    </form>
</div>