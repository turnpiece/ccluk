<?php
$path = forminator_plugin_dir();
$icon_minus = $path . "assets/icons/admin-icons/minus.php";
$providers = $integrations["providers"];
?>

<div class="wpmudev-box wpmudev-can--hide">

    <div class="wpmudev-box-header">

        <div class="wpmudev-header--text">

            <h2 class="wpmudev-subtitle"><?php _e( "Email Providers", Forminator::DOMAIN ); ?></h2>

        </div>

        <div class="wpmudev-header--action">

			<button class="wpmudev-box--action">

                <span class="wpmudev-icon--plus" aria-hidden="true"></span>

                <span class="wpmudev-sr-only"><?php _e( "Hide box", Forminator::DOMAIN ); ?></span>

            </button>

		</div>

    </div>

    <div class="wpmudev-box-section">

        <div class="wpmudev-section--table">

            <label class="wpmudev-label--info"><span><?php _e( "Configure if you want to collect emails from modules and how you want those emails to be stored.", Forminator::DOMAIN ); ?></label>

            <table class="wpmudev-table">

                <thead>

                    <tr><th><?php _e( "Email Collection Service", Forminator::DOMAIN ); ?></th></tr>

                </thead>

                <tbody>

                    <tr>

                        <td>

                            <div class="wpmudev-table--text">

                                <div class="wpmudev-table--providers">

                                    <div class="wpmudev-provider--switch">

                                        <div class="wpmudev-toggle">

                                            <div class="wpmudev-toggle--design">
                                                <input id="forminator-provider-mailchimp" type="checkbox" checked="checked">
                                                <label for="forminator-provider-mailchimp" aria-hidden="true"></label>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="wpmudev-provider--icon"><?php include( $path . 'assets/icons/providers/icon-mailchimp.php' ); ?></div>

                                    <a href="" class="wpmudev-provider--info wpmudev-open-modal" data-modal="settings-popup-edit-provider">

                                        <span class="wpmudev-provider--name"><?php echo $providers["mailchimp"]["name"]; ?></span>

                                        <span class="wpmudev-provider--api"><?php if ( $providers["mailchimp"]["api"] === "" ) { _e( "Connect to start growing your lists.", Forminator::DOMAIN ); } else { echo $providers["mailchimp"]["api"]; } ?></span>

                                    </a>

                                </div>

                            </div>

                        </td>

                    </tr>

                    <?php foreach( $providers as $key => $provider ) { ?>

                        <?php if ( ( $key !== "mailchimp" ) && ( $provider["added"] === true ) ) { ?>

                            <tr>

                                <td>

                                    <div class="wpmudev-table--text">

                                        <div class="wpmudev-table--providers">

                                            <div class="wpmudev-provider--switch">

                                                <div class="wpmudev-toggle">

                                                    <div class="wpmudev-toggle--design">
                                                        <input id="forminator-provider-<?php echo $key; ?>" type="checkbox" checked="checked">
                                                        <label for="forminator-provider-<?php echo $key; ?>" aria-hidden="true"></label>
                                                    </div>

                                                </div>

                                            </div>

                                            <div class="wpmudev-provider--icon"><?php include( $path . 'assets/icons/providers/icon-' . $key . '.php' ); ?></div>

                                            <a href="" class="wpmudev-provider--info wpmudev-open-modal" data-modal="settings-popup-edit-provider">

                                                <span class="wpmudev-provider--name"><?php echo $provider["name"]; ?></span>

                                                <span class="wpmudev-provider--api"><?php if ( empty ( $provider["api"] ) ) { _e( "Connect to start growing your lists.", Forminator::DOMAIN ); } else { echo $provider["api"]; } ?></span>

                                            </a>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        <?php } ?>

                    <?php } ?>

                </tbody>

                <tfoot>

                    <tr>

                        <td><div class="wpmudev-table--text"><button class="wpmudev-button wpmudev-button-sm wpmudev-button-blue wpmudev-open-modal" data-modal="settings-popup-add-provider"><?php _e( "Add Another Service", Forminator::DOMAIN ); ?></button></div></td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</div>