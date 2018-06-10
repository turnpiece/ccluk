<script id="hustle-sshare-front-tpl" type="text/template">
    <div id="hustle-sshare-module-display" class="hustle-sshare-{{service_type}} hustle-sshare-{{module_display_type}} hustle-sshare-module-id-{{module_id}}" >
        <# if ( module_display_type === 'floating_social' ) { #>

            <?php $this->render( "general/modals/shares-floating", array() ); ?>

        <# } else { #>

            <?php $this->render( "general/modals/shares-widget", array() ); ?>

        <# } #>
    </div>
</script>