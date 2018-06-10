<div id="wpmudev-dashboard-widget-modules" class="wpmudev-row">

    <div class="wpmudev-col col-12 col-md-6">

        <?php $this->render("admin/dashboard/modules/module-popup", array(
            'popups' => $popups
        ) ); ?>

        <?php $this->render("admin/dashboard/modules/module-shortcode", array(
            'embeds' => $embeds
        ) ); ?>

    </div>

    <div class="wpmudev-col col-12 col-md-6">

        <?php $this->render("admin/dashboard/modules/module-slidein", array(
            'slideins' => $slideins
        ) ); ?>

        <?php $this->render("admin/dashboard/modules/module-sharing", array(
            'social_sharings' => $social_sharings,
			'ss_share_stats_data' => $ss_share_stats_data,
            'ss_total_share_stats' => $ss_total_share_stats,
        ) ); ?>

    </div>

</div><?php // #wpmudev-dashboard-widget-modules ?>