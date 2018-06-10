<?php
$content_hide = false;
?>

<div id="wpmudev-dashboard-widget-popups" class="wpmudev-box wpmudev-box-close">

    <div class="wpmudev-box-head">

        <?php $this->render("general/icons/admin-icons/icon-popup" ); ?>

        <h2><?php _e("Pop-ups", Opt_In::TEXT_DOMAIN); ?></h2>

        <div class="wpmudev-box-action"><?php $this->render("general/icons/icon-plus" ); ?></div>

    </div>

    <div class="wpmudev-box-body<?php if ($content_hide === true) { echo ' wpmudev-hidden'; } ?>">

        <?php if ( count($popups) ) { ?>

            <table cellspacing="0" cellpadding="0" class="wpmudev-table">

                <thead>

                    <tr>

                        <th class="wpmudev-table--name"><?php _e("Name", Opt_In::TEXT_DOMAIN); ?></th>

                        <th class="wpmudev-table--views"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?></th>

                        <th class="wpmudev-table--rate"><?php _e( "Rate", Opt_In::TEXT_DOMAIN ); ?></th>

                        <th class="wpmudev-table--status"><?php _e( "Status", Opt_In::TEXT_DOMAIN ); ?></th>

                        <th class="wpmudev-table--button"></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach( $popups as $popup ) :

						$module_status = "off";
						if ( $popup->is_test_type_active( $popup->module_type ) ) {
							$module_status = "test";
						} elseif ( $popup->active ) {
							$module_status = "live";
						}

					?>

                        <tr>

                            <td class="wpmudev-table--name"><?php echo $popup->module_name; ?></td>

                            <td class="wpmudev-table--views" data-name="<?php _e( 'Views', Opt_In::TEXT_DOMAIN ); ?>"><?php echo $popup->get_statistics($popup->module_type)->views_count; ?></td>

                            <td class="wpmudev-table--rate" data-name="<?php _e( 'Rate', Opt_In::TEXT_DOMAIN ); ?>"><?php echo $popup->get_statistics($popup->module_type)->conversion_rate; ?>%</td>

                            <td class="wpmudev-table--status" data-name="<?php _e( 'Status', Opt_In::TEXT_DOMAIN ); ?>"><span class="module-status-<?php echo $module_status; ?>"><?php if ( $module_status === "off" ) _e( "Off", Opt_In::TEXT_DOMAIN ); if ( $module_status === "test" ) _e( "Test", Opt_In::TEXT_DOMAIN ); if ( $module_status === "live" ) _e( "Live", Opt_In::TEXT_DOMAIN ); ?></span></td>

                            <td class="wpmudev-table--button"><a href="<?php echo $popup->decorated->get_edit_url( Hustle_Module_Admin::POPUP_WIZARD_PAGE ,'' ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e("Edit", Opt_In::TEXT_DOMAIN); ?></a></td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

                <tfoot>

                    <tr><td colspan="5"><a href="<?php echo admin_url( "admin.php?page=" . Hustle_Module_Admin::POPUP_WIZARD_PAGE ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-blue"><?php _e("New Pop-up", Opt_In::TEXT_DOMAIN); ?></a></td></tr>

                </tfoot>

            </table>

        <?php } else { ?>

            <p><?php _e("You currently don't have any pop-ups. You can create a new pop-up with any kind of content e.g. An advert or a promotion. You can also create pop-ups for collecting your customers' emails.", Opt_In::TEXT_DOMAIN); ?></p>

            <p><a href="<?php echo admin_url( "admin.php?page=" . Hustle_Module_Admin::POPUP_WIZARD_PAGE ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e("Create Pop-up", Opt_In::TEXT_DOMAIN); ?></a></p>

        <?php } ?>

    </div>

</div>