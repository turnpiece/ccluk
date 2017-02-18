<?php
/**
 * Add button to the post editing screen
 */
add_action( 'media_buttons', 'rescue_shortcodes_media_button', 1000 );
function rescue_shortcodes_media_button( ) {

    $title = __( 'Shortcodes', 'rescue-shortcodes' );  ?>

    <a id="thickbox_shortcode_button" class="button thickbox" title="<?php echo esc_attr( $title ); ?>" href="#TB_inline?width=600&height=700&inlineId=thickbox_shortcode_window">
        <span class="rescue-shortcodes-icon"></span> <?php echo esc_html( $title ); ?>
    </a>

    <style>
      .rescue-shortcodes-icon:before {
        -moz-osx-font-smoothing: grayscale;
        color: #888;
        content: "\f334";
        font: 400 18px/1 dashicons;
        vertical-align: text-bottom;
      }
    </style>

<?php }

/**
 * Content displayed in the modal box
 */
add_action('admin_footer', 'rescue_shortcodes_thickbox_content');
function rescue_shortcodes_thickbox_content(){

  // Load the shortcode button script for the modal window
  wp_enqueue_script( 'rescue_shortcode_buttons' );

  /**
   * Allow for the "Example Text" string to be translated in the JS file
   * @link https://codex.wordpress.org/I18n_for_WordPress_Developers#Handling_JavaScript_files
   */
  $translation_array = array(
    'exampleText' => __( 'Example Text', 'rescue-shortcodes' )
  );
  wp_localize_script( 'rescue_shortcode_buttons', 'rescueTranslate', $translation_array );

?>

    <div id="thickbox_shortcode_window" style="display: none;">

    <p>
        <?php
            $rescue_plugin_link = 'https://rescuethemes.com/rescue-shortcodes-plugin';
            echo sprintf( wp_kses( __( 'See examples on the <a target="_blank" href="%s">Rescue Themes</a> site.', 'rescue-shortcodes' ),
                array(
                    'a' => array(
                        'href' => array(),
                        'title' => array(),
                        'target' => array()
                    ),
                )
            ), esc_url( $rescue_plugin_link ) );
        ?>
    </p>

    <table cellspacing="0" cellpadding="5" width="100%">

        <style>
           .wp-core-ui .button.insert_shortcode {
                margin-bottom: 0.75em;
                margin-right: 0.75em;
            }
            #TB_ajaxContent {
                width: 90%!important;
            }
            #TB_ajaxContent h3 {
                margin: 0;
            }
        </style>

        <tbody>
            <tr><th><h3><?php _e('Columns','rescue-shortcodes'); ?></h3></th></tr>
            <tr>
                <!-- Columns -->
                <td>
                <a id="rescue_half" class="insert_shortcode button"><?php _e('One Half','rescue-shortcodes'); ?></a>
                <a id="rescue_third" class="insert_shortcode button"><?php _e('One Third','rescue-shortcodes'); ?></a>
                <a id="rescue_fourth" class="insert_shortcode button"><?php _e('One Fourth','rescue-shortcodes'); ?></a>
                <a id="rescue_fifth" class="insert_shortcode button"><?php _e('One Fifth','rescue-shortcodes'); ?></a>
                <a id="rescue_sixth" class="insert_shortcode button"><?php _e('One Sixth','rescue-shortcodes'); ?></a>
                <a id="rescue_twothird" class="insert_shortcode button"><?php _e('One Seventh','rescue-shortcodes'); ?></a>
                <a id="rescue_threefourth" class="insert_shortcode button"><?php _e('Three Fourth','rescue-shortcodes'); ?></a>
                <a id="rescue_twofifth" class="insert_shortcode button"><?php _e('Two Fifth','rescue-shortcodes'); ?></a>
                <a id="rescue_threefifth" class="insert_shortcode button"><?php _e('Three Fifth','rescue-shortcodes'); ?></a>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr><th><h3><?php _e('Elements','rescue-shortcodes'); ?></h3></th></tr>
            <tr>
                <td>
                <a id="rescue_button" class="insert_shortcode button"><?php _e('Button','rescue-shortcodes'); ?></a>
                <a id="rescue_icon" class="insert_shortcode button"><?php _e('Icon','rescue-shortcodes'); ?></a>
                <a id="rescue_map" class="insert_shortcode button"><?php _e('Google Map','rescue-shortcodes'); ?></a>
                <a id="rescue_tabbed" class="insert_shortcode button"><?php _e('Tabbed Content','rescue-shortcodes'); ?></a>
                <a id="rescue_toggle" class="insert_shortcode button"><?php _e('Toggle','rescue-shortcodes'); ?></a>
                <a id="rescue_progress" class="insert_shortcode button"><?php _e('Progress Bar','rescue-shortcodes'); ?></a>
                <a id="rescue_spacing" class="insert_shortcode button"><?php _e('Spacing','rescue-shortcodes'); ?></a>
                <a id="rescue_clear" class="insert_shortcode button"><?php _e('Clear Floats','rescue-shortcodes'); ?></a>
                <p>
                    <?php
                        $fontawesome_link = 'http://fortawesome.github.io/Font-Awesome/icons';
                        echo sprintf( wp_kses( __( 'Complete list of icon names are available on the <a target="_blank" href="%s">Font Awesome</a> site.', 'rescue-shortcodes' ),
                            array(
                                'a' => array(
                                    'href' => array(),
                                    'title' => array(),
                                    'target' => array()
                                ),
                            )
                        ), esc_url( $fontawesome_link ) );
                    ?>
                </p>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr><th><h3><?php _e('Boxes','rescue-shortcodes'); ?></h3></th></tr>
            <tr>
                <!-- Content Boxes -->
                <td>
                <a id="rescue_box_blue" class="insert_shortcode button"><?php _e('Blue Box','rescue-shortcodes'); ?></a>
                <a id="rescue_box_gray" class="insert_shortcode button"><?php _e('Gray Box','rescue-shortcodes'); ?></a>
                <a id="rescue_box_green" class="insert_shortcode button"><?php _e('Green Box','rescue-shortcodes'); ?></a>
                <a id="rescue_box_red" class="insert_shortcode button"><?php _e('Red Box','rescue-shortcodes'); ?></a>
                <a id="rescue_box_yellow" class="insert_shortcode button"><?php _e('Yellow Box','rescue-shortcodes'); ?></a>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr><th><h3><?php _e('Highlights','rescue-shortcodes'); ?></h3></th></tr>
            <tr>
                <!-- Highlight Text -->
                <td>
                <a id="rescue_highlight_blue" class="insert_shortcode button"><?php _e('Blue Highlight','rescue-shortcodes'); ?></a>
                <a id="rescue_highlight_gray" class="insert_shortcode button"><?php _e('Gray Highlight','rescue-shortcodes'); ?></a>
                <a id="rescue_highlight_green" class="insert_shortcode button"><?php _e('Green Highlight','rescue-shortcodes'); ?></a>
                <a id="rescue_highlight_red" class="insert_shortcode button"><?php _e('Red Highlight','rescue-shortcodes'); ?></a>
                <a id="rescue_highlight_yellow" class="insert_shortcode button"><?php _e('Yellow Highlight','rescue-shortcodes'); ?></a>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr><th><h3><?php _e('Animations','rescue-shortcodes'); ?></h3></th></tr>
            <tr>
                <td>
                <a id="rescue_animate-slideInDown" class="insert_shortcode button"><?php _e('SlideInDown','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-slideInLeft" class="insert_shortcode button"><?php _e('slideInLeft','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-slideInRight" class="insert_shortcode button"><?php _e('slideInRight','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-fadeIn" class="insert_shortcode button"><?php _e('fadeIn','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-fadeInLeft" class="insert_shortcode button"><?php _e('fadeInLeft','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-fadeInRight" class="insert_shortcode button"><?php _e('fadeInRight','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-fadeInUp" class="insert_shortcode button"><?php _e('fadeInUp','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-fadeInDown" class="insert_shortcode button"><?php _e('fadeInDown','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-bounceIn" class="insert_shortcode button"><?php _e('bounceIn','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-bounceInLeft" class="insert_shortcode button"><?php _e('bounceInLeft','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-bounceInRight" class="insert_shortcode button"><?php _e('bounceInRight','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-bounceInUp" class="insert_shortcode button"><?php _e('bounceInUp','rescue-shortcodes'); ?></a>
                <a id="rescue_animate-bounceInDown" class="insert_shortcode button"><?php _e('bounceInDown','rescue-shortcodes'); ?></a>
                </td>
            </tr>

        </tbody>
    </table>

    </div><!-- #thickbox_shortcode_window -->
<?php }
