<?php

$content_hide = false;
?>

<div id="wpmudev-dashboard-widget-embedded" class="wpmudev-box wpmudev-box-close">

    <div class="wpmudev-box-head">

        <?php $this->render("general/icons/admin-icons/icon-shortcode" ); ?>

        <h2><?php _e("Embeds", Opt_In::TEXT_DOMAIN); ?></h2>

        <div class="wpmudev-box-action"><?php $this->render("general/icons/icon-plus" ); ?></div>

    </div>

    <div class="wpmudev-box-body<?php if ($content_hide === true) { echo ' wpmudev-hidden'; } ?>">

        <?php if ( count($embeds) ) { ?>

            <table cellspacing="0" cellpadding="0" class="wpmudev-table">

                <thead>

                    <tr>

                        <th class="wpmudev-table--name"><?php _e("Name", Opt_In::TEXT_DOMAIN); ?></th>

                        <th class="wpmudev-table--multistatus"></th>

                        <th class="wpmudev-table--views"><?php _e( "Views", Opt_In::TEXT_DOMAIN ); ?></th>

                        <th class="wpmudev-table--rate"><?php _e( "Rate", Opt_In::TEXT_DOMAIN ); ?></th>

                        <th class="wpmudev-table--button"></th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach( $embeds as $embed ) :

						$after_content_statistics = $embed->get_statistics('after_content');
						$widget_statistics = $embed->get_statistics('widget');
						$shortcode_statistics = $embed->get_statistics('shortcode');

						$total_views = (int) $after_content_statistics->views_count;
						$total_views += (int) $widget_statistics->views_count;
						$total_views += (int) $shortcode_statistics->views_count;

						$total_conversion = (int) $after_content_statistics->conversions_count;
						$total_conversion += (int) $widget_statistics->conversions_count;
						$total_conversion += (int) $shortcode_statistics->conversions_count;

						$conversion_rate = (int) $total_views > 0 ?  round( ( $total_conversion / $total_views )  * 100, 2 ) : 0;

						// status
						$status_label = array(
							'off' => __( 'OFF', Opt_In::TEXT_DOMAIN ),
							'test' => __( 'TEST', Opt_In::TEXT_DOMAIN ),
							'live' => __( 'LIVE', Opt_In::TEXT_DOMAIN )
						);
						$after_content_status = "off";
						if ( $embed->is_test_type_active( 'after_content' ) ) {
							$after_content_status = "test";
						} elseif ( $embed->is_embedded_type_active( 'after_content' ) && !$embed->is_test_type_active( 'after_content' ) ) {
							$after_content_status = "live";
						}
						$widget_status = "off";
						if ( $embed->is_test_type_active( 'widget' ) ) {
							$widget_status = "test";
						} elseif ( $embed->is_embedded_type_active( 'widget' ) && !$embed->is_test_type_active( 'widget' ) ) {
							$widget_status = "live";
						}
						$shortcode_status = "off";
						if ( $embed->is_test_type_active( 'shortcode' ) ) {
							$shortcode_status = "test";
						} elseif ( $embed->is_embedded_type_active( 'shortcode' ) && !$embed->is_test_type_active( 'shortcode' ) ) {
							$shortcode_status = "live";
						}

					?>

                        <tr>

                            <td class="wpmudev-table--name"><?php echo $embed->module_name; ?></td>

                            <td class="wpmudev-table--multistatus">

                                <div class="wpmudev-multistatus--wrap">

                                    <div class="wpmudev-multistatus--status wpmudev-tip" data-tip="<?php printf( __( 'After Content is %s', Opt_In::TEXT_DOMAIN ), $status_label[$after_content_status] ); ?>">

                                        <div class="wpmudev-multistatus--module-<?php echo $after_content_status; ?>"></div>

                                        <div class="wpmudev-multistatus--module-icon"><?php $this->render("general/icons/admin-icons/icon-embedded" ); ?></div>

                                    </div>

                                    <div class="wpmudev-multistatus--status wpmudev-tip" data-tip="<?php printf( __( 'Widget is %s', Opt_In::TEXT_DOMAIN ), $status_label[$widget_status] ); ?>">

                                        <div class="wpmudev-multistatus--module-<?php echo $widget_status; ?>"></div>

                                        <div class="wpmudev-multistatus--module-icon"><?php $this->render("general/icons/admin-icons/icon-widget" ); ?></div>

                                    </div>

                                    <div class="wpmudev-multistatus--status wpmudev-tip" data-tip="<?php printf( __( 'Shortcode is %s', Opt_In::TEXT_DOMAIN ), $status_label[$shortcode_status] ); ?>">

                                        <div class="wpmudev-multistatus--module-<?php echo $shortcode_status; ?>"></div>

                                        <div class="wpmudev-multistatus--module-icon"><?php $this->render("general/icons/admin-icons/icon-shortcode" ); ?></div>

                                    </div>

                                </div>

                            </td>

                            <td class="wpmudev-table--views" data-name="<?php _e( 'Views', Opt_In::TEXT_DOMAIN ); ?>"><?php echo $total_views; ?></td>

                            <td class="wpmudev-table--rate" data-name="<?php _e( 'Rate', Opt_In::TEXT_DOMAIN ); ?>"><?php echo $conversion_rate; ?>%</td></td>

                            <td class="wpmudev-table--button"><a href="<?php echo $embed->decorated->get_edit_url( Hustle_Module_Admin::EMBEDDED_WIZARD_PAGE ,'' ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e("Edit", Opt_In::TEXT_DOMAIN); ?></a></td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

                <tfoot>

                    <tr><td colspan="4"><a href="<?php echo admin_url( "admin.php?page=" . Hustle_Module_Admin::EMBEDDED_WIZARD_PAGE ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-blue"><?php _e("New Embed", Opt_In::TEXT_DOMAIN); ?></a></td></tr>

                </tfoot>

            </table>

        <?php } else { ?>

            <p><?php _e("Here you can create an Embed module with your custom content, or for collectiong users' emails. Embed modules can then be appended to posts or pages, or displayed inside widgets and shortcodes.", Opt_In::TEXT_DOMAIN); ?></p>

            <p><a href="<?php echo admin_url( "admin.php?page=" . Hustle_Module_Admin::EMBEDDED_WIZARD_PAGE ); ?>" class="wpmudev-button wpmudev-button-sm wpmudev-button-ghost"><?php _e("Create Embed Module", Opt_In::TEXT_DOMAIN); ?></a></p>

        <?php } ?>

    </div>

</div>