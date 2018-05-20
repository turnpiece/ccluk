<?php

/**
* For creating markup that does not fit into the exiting options.
*
* This extends SWP_Option rather than SWP_Section because it uses
* many of the same methods as an option and is a child of a
* section, even though this is neither necessarily
* an option or a section.
*
* @since 3.0.0
*/
class SWP_Section_HTML extends SWP_Option {


    /**
    * HTML
    *
    * The non-conformant markup this object represents.
    * Most of the sections and options can be created using
    * one of the existing SWP_{Item} classes. Sometimes we
    * need something that does not fit those boxes.
    * This class provides native methods for a few of those
    * cases, and an add_HTML() method for everything else.
    *
    * @var string $html
    */
    public $html = '';

    /**
    * The required constructor for PHP classes.
    *
    * @param string $name An arbitrary name, except for do_bitly_authentication_button
    * @param Optional string $key If the object requires access beyond itself, pass it a key.
    *                             Otherwise $name will be used.
    * @see  $this->do_bitly_authentication_button()
    *
    */
    public function __construct( $name, $key = null ) {
        $key = $key === null ? $name : $key;

        parent::__construct( $name, $key );

        $this->html = '';
    }


    /**
    * Allows custom HTML to be added.
    *
    * @param string $html The fully qualified, ready-to-print HTML to display.
    * @return SWP_Section_HTML $this This object for method chaining.
    */
    public function add_HTML( $html ) {
        if ( !is_string( $html) ) :
            $this->_throw( 'This requires a string of HTML!' );
        endif;

        $this->html .= $html;

        return $this;
    }

    public function do_admin_sidebar() {
        $status_title =  __( 'Press Ctrl+C to Copy this information.' , 'social-warfare' );
        $support_link = __( 'Need help? Check out our <a href="https://warfareplugins.com/support/" target="_blank">Knowledgebase.' , 'social-warfare' );
        $support_status = __( 'Opening a support ticket? Copy your System Status by clicking the button below.' , 'social-warfare' );
        $get_status = __( 'Get System Status' , 'social-warfare' );
        ob_start();
        ?>

    	<div class="sw-admin-sidebar sw-grid sw-col-220 sw-fit">
        	<a href="https://warfareplugins.com/affiliates/" target="_blank"><img src="<?= SWP_PLUGIN_URL ?>/images/admin-options-page/affiliate-300x150.jpg"></a>
        	<a href="https://warfareplugins.com/support-categories/getting-started/" target="_blank"><img src="<?= SWP_PLUGIN_URL ?>/images/admin-options-page/starter-guide-300x150.jpg"></a>
        	<a href="https://warfareplugins.com/how-to-measure-social-media-roi-using-google-analytics/" target="_blank"><img src="<?= SWP_PLUGIN_URL ?>/images/admin-options-page/measure-roi-300x150.jpg"></a>
        	<p class="sw-support-notice sw-italic"><?= $support_link ?></a></p>
        	<p class="sw-support-notice sw-italic"><?= $support_status ?></p>
        	<a href="#" class="button sw-blue-button sw-system-status"><?= $get_status ?></a>

        	<!-- Sytem Status Container -->
        	<div class="sw-clearfix"></div>
        	<div class="system-status-wrapper">
            	<h4><?= $status_title ?></h4>
            	<div class="system-status-container"><?= $this->system_status() ?></div>
        	</div>
    	</div>

        <?php

        $this->html = ob_get_contents();
        ob_end_clean();

        return $this->html;
    }

    private function system_status() {
        /**
    	 * System Status Generator
    	 */
    	global $swp_user_options;

    	if ( ! function_exists( 'get_plugins' ) ) {
    		require_once ABSPATH . 'wp-admin/includes/plugin.php';
    	}

    	$plugins = get_plugins();
    	$pluginList = '';

    	foreach ( $plugins as $plugin ) :
    		$pluginList .= '<tr><td><b>' . $plugin['Name'] . '</b></td><td>' . $plugin['Version'] . '</td></tr>';
    	endforeach;

    	if ( function_exists( 'fsockopen' ) ) :
    		$fsockopen = '<span style="color:green;">Enabled</span>';
    	else :
    		$fsockopen = '<span style="color:red;">Disabled</span>';
    	endif;

    	if ( function_exists( 'curl_version' ) ) :
    		$curl_version = curl_version();
    		$curl_status = '<span style="color:green;">Enabled: v' . $curl_version['version'] . '</span>';
    	else :
    		$curl_status = '<span style="color:red;">Disabled</span>';
    	endif;

    	$theme = wp_get_theme();

    	$system_status = '
    		<table style="width:100%;">
    			<tr><td><h2>Environment Statuses</h2></td><td></td></tr>
    			<tr><td><b>Home URL</b></td><td>' . get_home_url() . '</td></tr>
    			<tr><td><b>Site URL</b></td><td>' . get_site_url() . '</td></tr>
    			<tr><td><b>WordPress Version</b></td><td>' . get_bloginfo( 'version' ) . '</td></tr>
    			<tr><td><b>PHP Version</b></td><td>' . phpversion() . '</td></tr>
    			<tr><td><b>WP Memory Limit</b></td><td>' . WP_MEMORY_LIMIT . '</td></tr>
    			<tr><td><b>Social Warfare Version</b></td><td>' . SWP_VERSION . '</td></tr>
    			<tr><td><h2>Connection Statuses</h2></td><td></td></tr>
    			<tr><td><b>fsockopen</b></td><td>' . $fsockopen . '</td></tr>
    			<tr><td><b>cURL</b></td><td>' . $curl_status . '</td></tr>
    			<tr><td><h2>Plugin Statuses</h2></td><td></td></tr>
    			<tr><td><b>Theme Name</b></td><td>' . $theme['Name'] . '</td></tr>
    			<tr><td><b>Theme Version</b></td><td>' . $theme['Version'] . '</td></tr>
    			<tr><td><b>Caching Method</b></td><td>' . ucfirst($swp_user_options['cache_method']) . '</td></tr>
    			<tr><td><b>Active Plugins</b></td><td></td></tr>
    			<tr><td><b>Number of Active Plugins</b></td><td>' . count( $plugins ) . '</td></tr>
    			' . $pluginList . '
    		</table>
    		';

        return $system_status;
    }


    public function do_tweet_count_registration() {
        global $swp_user_options;

        // Check for a default value
        if ( isset( $swp_user_options['twitter_shares'] ) && $swp_user_options['twitter_shares'] == true ) :
            $status = 'on';
            $selected = 'checked';
        elseif ( isset( $swp_user_options['twitter_shares'] ) && $swp_user_options['twitter_shares'] == false ) :
            $status = 'off';
            $selected = '';
        else :
            $status = 'off';
            $selected = '';
        endif;


        $html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container ' . $this->key . '_wrapper" ' . $this->render_dependency() . '>';

		// Begin Registration Wrapper
		$html .= '<div class="tweet-count-wrapper" registration="false">';

		// Open the IS NOT Activated container
		$html .= '<div class="sw-grid sw-col-940 swp_tweets_not_activated">';

		// The Warning Notice & Instructions
		$html .= '<p class="sw-subtitle sw-registration-text sw-italic">Step 1: <a style="float:none;" class="button sw-navy-button" href="https://opensharecount.com" target="_blank">' . __( 'Click here to visit OpenShareCount.com (Recommended)' , 'social-warfare' ) . '</a>&nbsp;<a style="float:none;" class="button sw-navy-button" href="http://newsharecounts.com" target="_blank">' . __( 'Click here to visit NewShareCounts.com' , 'social-warfare' ) . '</a><br />' . __( 'Step 2: Follow the prompts on their website to create an account and add your domain to be tracked for share counts.' , 'social-warfare' ) . '<br />' . __( 'Step 3: Flip the switch below to "ON", select which tracking service the plugin should use, then save your changes.' , 'social-warfare' ) . '</p>';

		// Close the IS NOT ACTIVATED container
		$html .= '</div>';

		// Checkbox Module
		$html .= '<div class="sw-grid sw-col-300"><p class="sw-checkbox-label">Tweet Counts</p></div>';
		$html .= '<div class="sw-grid sw-col-300">';
		$html .= '<div class="sw-checkbox-toggle" status="' . $status . '" field="#twitter_shares"><div class="sw-checkbox-on">' . __( 'ON' , 'social-warfare' ) . '</div><div class="sw-checkbox-off">' . __( 'OFF' , 'social-warfare' ) . '</div></div>';
		$html .= '<input type="checkbox" class="sw-hidden" name="twitter_shares" id="twitter_shares" ' . $selected . ' />';
		$html .= '</div>';
		$html .= '<div class="sw-grid sw-col-300 sw-fit"></div>';

		// Close the Registration Wrapper
		$html .= '</div>';

		$html .= '<div class="sw-premium-blocker"></div>';
		$html .= '</div>';

        $this->html = $html;

        return $html;
    }



    /**
    * Render the Bitly connection button on the Advanced tab.
    *
    * @return SWP_Section_HTML $this The calling instance, for method chaining.
    */
        public function do_bitly_authentication_button() {
        $link = "https://bitly.com/oauth/authorize?client_id=96c9b292c5503211b68cf4ab53f6e2f4b6d0defb&state=" . admin_url( 'admin-ajax.php' ) . "&redirect_uri=https://warfareplugins.com/bitly_oauth.php";

        if ( isset( $this->dependant) && !empty( $this->dependant) ):
            $text = __( 'Connected', 'social-warfare' );
            $color = 'sw-green-button';
        else:
            $text = __( 'Authenticate', 'social-warfare' );
            $color = 'sw-navy-button';
        endif;

        ob_start() ?>

            <div class="sw-grid sw-col-940 sw-fit sw-option-container <?= $this->key ?> '_wrapper" data-dep="bitly_authentication" data-dep_val="[true]">
                <div class="sw-grid sw-col-300">
                    <p class="sw-authenticate-label"><?php __( 'Bitly Link Shortening', 'social-warfare' ) ?></p>
                </div>
                <div class="sw-grid sw-col-300">
                    <a class="button <?= $color ?>" href="<?= $link ?>"><?= $text ?></a>
                </div>
                <div class="sw-grid sw-col-300 sw-fit"></div>
            </div>

        <?php

        $this->html = ob_get_contents();
        ob_end_clean();

        return $this;
    }


    /**
    * The buttons preview as shown on the Display tab.
    *
    * @return SWP_Section_HTML $this The calling instance, for method chaining.
    */
    public function do_buttons_preview() {
        ob_start() ?>

        <div class="swp_social_panel swp_flat_fresh swp_default_full_color swp_individual_full_color swp_other_medium_gray" data-position="both" data-float="ignore" data-count="6" data-floatcolor="#ffffff" data-scale="1" data-align="full_width">
            <div class="nc_tweetContainer swp_google_plus" data-id="2">
                <a target="_blank" href="https://plus.google.com/share?url=http%3A%2F%2Fwfa.re%2F1W28voz" data-link="https://plus.google.com/share?url=http%3A%2F%2Fwfa.re%2F1W28voz" class="nc_tweet">
                    <span class="iconFiller">
                        <span class="spaceManWilly">
                            <i class="sw swp_google_plus_icon"></i>
                            <span class="swp_share"><?php __( '+1','social-warfare' ) ?></span>
                        </span>
                    </span>
                    <span class="swp_count">1.2K</span>
                </a>
            </div>
            <div class="nc_tweetContainer swp_twitter" data-id="3">
                <a href="https://twitter.com/share?original_referer=/&text=Ultimate+Social+Share+%23WordPress+plugin%21+Beautiful%2C+super+fast+%26+more+http%3A%2F%2Fwarfareplugins.com+pic.twitter.com%2FA2zcCJwZtO&url=/&via=WarfarePlugins" data-link="https://twitter.com/share?original_referer=/&text=Ultimate+Social+Share+%23WordPress+plugin%21+Beautiful%2C+super+fast+%26+more+http%3A%2F%2Fwarfareplugins.com+pic.twitter.com%2FA2zcCJwZtO&url=/&via=WarfarePlugins" class="nc_tweet">
                    <span class="iconFiller">
                        <span class="spaceManWilly">
                            <i class="sw swp_twitter_icon"></i>
                            <span class="swp_share"><?php __( 'Tweet','social-warfare' ) ?></span>
                        </span>
                    </span>
                    <span class="swp_count">280</span>
                </a>
            </div>
            <div class="nc_tweetContainer swp_pinterest" data-id="6">
                <a data-link="https://pinterest.com/pin/create/button/?url=https://warfareplugins.com/&media=https%3A%2F%2Fwarfareplugins.com%2Fwp-content%2Fuploads%2Fget-content-shared-735x1102.jpg&description=Customize+your+Pinterest+sharing+options%2C+create+easy+%22click+to+tweet%22+buttons+within+your+blog+posts%2C+beautiful+sharing+buttons+and+more.+Social+Warfare+is+the+ultimate+social+sharing+arsenal+for+WordPress%21" class="nc_tweet" data-count="0">
                    <span class="iconFiller">
                        <span class="spaceManWilly">
                            <i class="sw swp_pinterest_icon"></i>
                            <span class="swp_share"><?php __( 'Pin','social-warfare' ) ?></span>
                        </span>
                    </span>
                    <span class="swp_count">104</span>
                </a>
            </div>
            <div class="nc_tweetContainer swp_facebook" data-id="4">
                <a target="_blank" href="http://www.facebook.com/share.php?u=http%3A%2F%2Fwfa.re%2F1W28vov" data-link="http://www.facebook.com/share.php?u=http%3A%2F%2Fwfa.re%2F1W28vov" class="nc_tweet">
                    <span class="iconFiller">
                        <span class="spaceManWilly">
                            <i class="sw swp_facebook_icon"></i>
                            <span class="swp_share"><?php __( 'Share','social-warfare' ) ?></span>
                        </span>
                    </span>
                    <span class="swp_count">157</span>
                </a>
            </div>
            <div class="nc_tweetContainer swp_linkedin" data-id="5">
                <a target="_blank" href="https://www.linkedin.com/cws/share?url=http%3A%2F%2Fwfa.re%2F1W28twH" data-link="https://www.linkedin.com/cws/share?url=http%3A%2F%2Fwfa.re%2F1W28twH" class="nc_tweet">
                    <span class="iconFiller">
                        <span class="spaceManWilly">
                            <i class="sw swp_linkedin_icon"></i>
                            <span class="swp_share"><?php __( 'Share','social-warfare' ) ?></span>
                        </span>
                    </span>
                    <span class="swp_count">51</span>
                </a>
            </div>
            <div class="nc_tweetContainer total_shares total_sharesalt" data-id="6" >
            <span class="swp_count">
                <span class="swp_label">Shares</span> 1.8K
            </span>
            </div>
        </div>

        <?php

        $this->html = ob_get_contents();
        ob_end_clean();

        return $this;
    }


    /**
    * Renders the three column table on the Display tab.
    *
    * @since  3.0.4 | 09 MAY 2018 | Added check for is_numeric to avoid throwing errors.
    * @since  3.0.5 | 09 MAY 2018 | Switched to using an iterator. Many post types are
    *                               being returned with associative keys, not numeric ones.
    * @param  none
    * @return SWP_Section_HTML $this The calling instance, for method chaining.
    *
    */
    public function do_button_position_table() {
        $static_options = [
            'above'=> 'Above the Content',
            'below' => 'Below the Content',
            'both' => 'Both Above and Below the Content',
            'none' => 'None/Manual Placement'
        ];

        $default_types = ['page', 'post', 'home', 'archive_categories'];
		$other_types = get_post_types( ['public' => true, '_builtin' => false ], 'names' );

        $post_types = array_merge( $default_types, $other_types );

        $panel_locations = [
            'above' => 'Above the Content',
            'below' => 'Below the Content',
            'both'  => 'Both Above and Below the Content',
            'none'  => 'None/Manual Placement'
        ];

        $float_locations = [
            'on'    => 'On',
            'off'   => 'Off'
        ];

        $html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container" ';
        $html .= $this->render_dependency();
        $html .= $this->render_premium();
        $html .= '>';

        $html .= '<div class="sw-grid sw-col-300">';
            $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Post Type' ,'social-warfare' ) . '</p>';
        $html .= '</div>';
        $html .= '<div class="sw-grid sw-col-300">';
            $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Static Buttons' ,'social-warfare' ) . '</p>';
        $html .= '</div>';
        $html .= '<div class="sw-grid sw-col-300 sw-fit">';
            $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Floating Buttons (If Activated)' ,'social-warfare' ) . '</p>';
        $html .= '</div>';

		$i = 0;
        foreach( $post_types as $index => $post ) {

            $priority = ($i + 1) * 10; $i++;

            $html .= '<div class="sw-grid sw-col-940 sw-fit sw-option-container ' . $post . '_wrapper">';

                $html .= '<div class="sw-grid sw-col-300">';
                    $html .= '<p class="sw-input-label">' . str_replace('_', ' & ', ucfirst($post)) . '</p>';
                $html .= '</div>';

                $html .= '<div class="sw-grid sw-col-300">';

                    $panel = new SWP_Option_Select( 'Panel '. ucfirst( $post ), 'location_' . $post );
                    $panel->set_priority( $priority )
                        ->set_size( 'sw-col-300' )
                        ->set_choices( $panel_locations )
                        ->set_default( 'both' );

                    $html .= $panel->render_HTML_element();

                $html .= '</div>';
                $html .= '<div class="sw-grid sw-col-300 sw-fit">';

                if ( $post !== 'home' && $post !== 'archive_categories' ) :

                    $float = new SWP_Option_Select( 'Float ' . ucfirst( $post ), 'float_location_' . $post );
                    $float->set_priority( $priority + 5 )
                        ->set_size( 'sw-col-300' )
                        ->set_choices( $float_locations )
                        ->set_default( 'on' );

                    $html .= $float->render_HTML_element();

                endif;

                $html .= '</div>';

            $html .= '</div>';

        }

        $html .= '</div>';

        $this->html = $html;

        return $this;
    }


    /**
    * Creates the Click To Tweet preview for the Styles tab.
    *
    * @return SWP_Section_HTML $this The calling instance, for method chaining.
    */
    public function do_ctt_preview() {
        //* Pull these variables out just to make the $html string easier to read.
        $link = "https://twitter.com/share?text=We+couldn%27t+find+one+social+sharing+plugin+that+met+all+of+our+needs%2C+so+we+built+it+ourselves.&amp;url=http://warfareplugins.com&amp;via=warfareplugins";
        $data_link = "https://twitter.com/share?text=We+couldn%27t+find+one+social+sharing+plugin+that+met+all+of+our+needs%2C+so+we+built+it+ourselves.&amp;url=http://wfa.re/1PtqdNM&amp;via=WarfarePlugins";
        $text = "We couldn't find one social sharing plugin that met all of our needs, so we built it ourselves.";

        $html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container ' . $this->key . '_wrapper">';
            $html .= '<a class="swp_CTT style1"  data-style="style1" href="' . $link . '" data-link="' . $data_link . '" target="_blank">';
                $html .= '<span class="sw-click-to-tweet">';
                    $html .= '<span class="sw-ctt-text">' . $text . '</span>';
                    $html .= '<span class="sw-ctt-btn">Click To Tweet';
                        $html .= '<i class="sw swp_twitter_icon"></i>';
                    $html .= '</span>';
                $html .= '</span>';
            $html .= '</a>';
        $html .= '</div>';


        $this->html = $html;

        return $this;

    }


    /**
    * Renders the three column table on the Display tab.
    *
    * @return SWP_Section_HTML $this The calling instance, for method chaining.
    */
    public function do_yummly_display() {
        $html = '<div class="sw-grid sw-col-940 sw-fit sw-option-container ' . $this->key . '_wrapper" ';
        $html .= $this->render_dependency();
        $html .= $this->render_premium();
        $html .= '>';


            //* Table headers
            $html .= '<div class="sw-grid sw-col-300">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding"></p>';
            $html .= '</div>';

            $html .= '<div class="sw-grid sw-col-300">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Choose Category' ,'social-warfare' ) . '</p>';
            $html .= '</div>';

            $html .= '<div class="sw-grid sw-col-300 sw-fit">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Choose Tag' ,'social-warfare' ) . '</p>';
            $html .= '</div>';

            $yummly_categories = new SWP_Option_Text( 'Yummly Categories', 'yummly_categories' );
            $categories_html = $yummly_categories->set_priority( 10 )
                ->set_default( '' )
                ->render_HTML_element();

            $yummly_tags = new SWP_Option_Text( 'Yummly Tags', 'yummly_tags' );
            $tags_html = $yummly_tags->set_priority( 10 )
                ->set_default( '' )
                ->render_HTML_element();

            //* Table body
            $html .= '<div class="sw-grid sw-col-300">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding">' . __( 'Yummly Terms' ,'social-warfare' ) . '</p>';
            $html .= '</div>';

            $html .= '<div class="sw-grid sw-col-300">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding">' . $categories_html . '</p>';
            $html .= '</div>';

            $html .= '<div class="sw-grid sw-col-300 sw-fit">';
                $html .= '<p class="sw-select-label sw-short sw-no-padding">' . $tags_html . '</p>';
            $html .= '</div>';

        $html .= '</div>';

        $this->html = $html;

        return $this;
    }


    /**
    * The rendering method common to all classes.
    *
    * Unlike the other option classes, this class creates its HTML
    * and does not immediately return it. Instead, it stores the
    * HTML inside itself and waits for the render_html method to be called.
    *
    * @return This object's saved HTML.
    */
    public function render_HTML() {
        return $this->html;
    }
}
