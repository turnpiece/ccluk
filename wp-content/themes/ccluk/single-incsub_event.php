<?php
global $blog_id, $wp_query, $booking, $post, $current_user;
$event = new Eab_EventModel($post);

get_header( );
?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
            <div class="event <?php echo Eab_Template::get_status_class($post); ?>" id="wpmudevevents-wrapper">
		        <div id="wpmudevents-single">
                    <?php
                    the_post();
                    
                    $start_day = date_i18n('m', strtotime(get_post_meta($post->ID, 'incsub_event_start', true)));
                    ?>
                    
                    <?php echo Eab_Template::get_error_notice(); ?>
                    
                    <div class="wpmudevevents-content">
                        <div id="wpmudevevents-contentheader">
                            <div class="wpmudevevents-contentmeta">
                                <?php echo Eab_Template::get_event_details($post); //event_details(); ?>
                            </div>
			                <div id="wpmudevevents-contentbody">
                                <?php 
                                    add_filter('agm_google_maps-options', 'eab_autoshow_map_off', 99);
                                    the_content(); 
                                    remove_filter('agm_google_maps-options', 'eab_autoshow_map_off');
                                ?>
                                <?php if ($event->has_venue_map()) : ?>
                                    <div class="wpmudevevents-map"><?php echo $event->get_venue_location(Eab_EventModel::VENUE_AS_MAP); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php comments_template( '', true ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php get_footer('event'); ?>
