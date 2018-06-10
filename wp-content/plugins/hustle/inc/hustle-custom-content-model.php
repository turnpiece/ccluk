<?php

/**
 * Class Hustle_Custom_Content_Model
 *
 * @property string $subtitle
 * @property string $content
 * @property array $design
 * @property array $popup
 * @property array $slide_in
 * @property array $magic_bar
 * @property string $module_type
 * @property array $types_display
 * @property Hustle_Custom_Content_Decorator $decorated
 */
class Hustle_Custom_Content_Model extends Hustle_Model
{

    /**
     * @return Hustle_Custom_Content_Model
     */
    static function instance(){
        return new self;
    }

    function get_optin_provider(){
        return "custom_content";
    }

    /**
     * Optins types
     *
     * @var array
     */
    protected $types = array(
		'after_content',
        'popup',
        'slide_in',
        'shortcode',
        'widget',
    );

    function __get($field)
    {
        $from_parent = parent::__get($field);
        if( !empty( $from_parent ) )
            return $from_parent;

    }

    /**
     * Returns environments type array
     *
     * @return array
     */
    function get_types(){
        return $this->types;
    }

    function get_type(){
        return $this->get_optin_provider();
    }

    /**
     * @param $type
     * @return null|Hustle_Custom_Content_Model_Stats
     */
    function get_stats( $type ){

        if( in_array( $type, $this->types ) ){
            if( !isset( $this->_stats[ $type ] ) )
                $this->_stats[ $type ] = new Hustle_Custom_Content_Model_Stats($this, $type);

            return $this->_stats[ $type ];
        }

        return false;
    }

    /**
     * Decorates current model
     *
     * @return Hustle_Custom_Content_Decorator
     */
    function get_decorated(){

        if( !$this->_decorator )
            $this->_decorator = new Hustle_Custom_Content_Decorator( $this );

        return $this->_decorator;
    }

    function get_data(){
        return array_merge( (array) $this->_data, array(
            "subtitle" => $this->subtitle,
            "content" => $this->content,
        ));
    }

    /**
     * @return Hustle_Custom_Content_Design
     */
    function get_design()
    {
        return new Hustle_Custom_Content_Design( $this->get_settings_meta( self::KEY_DESIGN, "{}", true ), $this );
    }

	function get_after_content() {
		return new Hustle_Custom_Content_Meta_After_Content( $this->get_settings_meta( self::KEY_AFTER_CONTENT, "{}", true ), $this );
	}

    /**
     * @return Hustle_Custom_Content_Meta_Popup
     */
    function get_popup(){
        return new Hustle_Custom_Content_Meta_Popup( $this->get_settings_meta( self::KEY_POPUP, "{}", true ), $this );
    }

    /**
     * @return Hustle_Custom_Content_Meta_Slide_In
     */
    function get_slide_in(){
        return new Hustle_Custom_Content_Meta_Slide_In( $this->get_settings_meta( self::KEY_SLIDE_IN, "{}", true ), $this );
    }

    /**
     * @return Hustle_Custom_Content_Meta_Magic_Bar
     */
    function get_magic_bar(){
        return new Hustle_Custom_Content_Meta_Magic_Bar( $this->get_settings_meta( self::KEY_MAGIC_BAR, "{}", true ), $this );
    }

    /**
     * Returns optin settings
     *
     * @return Opt_In_Meta_Settings
     */
    public function get_parent_settings(){
        $settings_json = $this->get_meta( self::KEY_SETTINGS );
        return new Opt_In_Meta_Settings( json_decode( $settings_json ? $settings_json : "{}", true ), $this );
    }


    /**
     * Toggles state of optin or optin type
     *
     * @param null $environment
     * @return false|int|WP_Error
     */
    function toggle_state( $environment = null, $settings = false ){
        if( is_null( $environment ) ) {
			return parent::toggle_state( $environment );
		}

        if ( $settings ) {
            $obj_settings = json_decode($this->settings);
            $prev_value = $obj_settings->$environment;
            $prev_value->enabled = !isset( $prev_value->enabled ) || "false" === $prev_value->enabled ? "true": "false";
            $new_value = array_merge( (array) $obj_settings, array( $environment => $prev_value ));
            return $this->update_meta( self::KEY_SETTINGS,  json_encode( $new_value ) );

        } else {
            if( in_array( $environment, $this->types ) ) { // we are toggling state of a specific environment
                $prev_value = $this->{$environment}->to_object();
                $prev_value->enabled = !isset( $prev_value->enabled ) || "false" === $prev_value->enabled ? "true": "false";
                return $this->update_meta( $environment,  json_encode( $prev_value ) );
            } else{
                return new WP_Error("Invalid_env", "Invalid environment . " . $environment);
            }
        }
    }

    /**
     * Returns parsed content to be shown on the modal
     *
     * @since 2.0
     * @return mixed|void
     */
    function get_content(){
		global $wp_filter;

		$the_content_filters = false;
		$callback_list = array();

		if ( ! empty( $wp_filter['the_content'] ) && ! empty( $wp_filter['the_content']->callbacks ) ) {
			$the_content_filters = $wp_filter['the_content'];
			$callbacks = $wp_filter['the_content']->callbacks;

			$allowed_filters = array(
				'wptexturize',
				'wpautop',
				'shortcode_unautop',
				'prepend_attachment',
				'wp_make_content_images_responsive',
				'capital_P_dangit',
				'do_shortcode',
				'convert_smilies',
			);

			/**
			 * Filter the list of allowed filters to be executed at CC content.
			 *
			 * @since 2.0.3
			 *
			 * @param (array) $allowed_filters		The list of allowed filters.
			 **/
			$allowed_filters = apply_filters( 'hustle_cc_allowed_filters', $allowed_filters );


			foreach ( $callbacks as $priority => $callback ) {
				foreach ( $callback as $filter_name => $filter_callback ) {
                    $allowed_match_found = (bool) preg_match( '%run_shortcode|autoembed%', $filter_name );
					if ( ! in_array( $filter_name, $allowed_filters ) && !$allowed_match_found ) {
						unset( $wp_filter['the_content']->callbacks[ $priority ][ $filter_name ] );
					}

					// Set back the callback list
					$callback_list[ $priority ][ $filter_name ] = $filter_callback;
				}
			}
		}

		$message = apply_filters( 'the_content', $this->optin_message );

		/**
		 * Restore original `the_content` filter list **/
		if ( ! empty( $the_content_filters ) ) {
			$the_content_filters->callbacks = $callback_list;
			$wp_filter['the_content'] = $the_content_filters;
		}

		return $message;
    }

    function get_module_type(){
        return "custom_content";
    }

    /**
     * Checkes if module has type
     *
     * @param $type_name
     * @return bool
     */
    function has_type( $type_name ){
        return in_array( $type_name, $this->types );
    }


    /**
     * Checkes if type should be displayed on frontend
     *
     * @return array
     */
    function get_types_display_conditions(){
        $display = array();

        foreach( $this->types as $type )
            $display[ $type ] = $this->should_display_type( $type );

        return $display;
    }

    function should_display_type( $type ){
		// if the type method not exists e.g. shortcode,
		// return true since don't have display conditions
		if ( !method_exists($this,"get_$type") ) {
			return true;
		}

		global $post;
        $display = true;
        $settings = $this->{"get_$type"}();

        if( !$settings->enabled ) false;
        $_conditions = $settings->get_conditions();
		$skip_all_cpt = false;
		if( !empty( $_conditions ) ) {

			if ( is_singular() ) {
				// unset not needed post_type
				if ( $post->post_type == 'post' ) {
					unset($_conditions->pages);
					$skip_all_cpt = true;
				} elseif ( $post->post_type == 'page' ) {
					unset($_conditions->posts);
					unset($_conditions->categories);
					unset($_conditions->tags);
					$skip_all_cpt = true;
				} else {
					// unset posts and pages since this is CPT
					unset($_conditions->posts);
					unset($_conditions->pages);
				}
			} else {

                // do not display after_content on archive page
                if ( $type == 'after_content' ) {
                    return false;
                }

				// unset posts and pages
				unset($_conditions->posts);
				unset($_conditions->pages);
				$skip_all_cpt = true;
				// unset not needed taxonomy
				if ( is_category() ) {
					unset($_conditions->tags);
				}
				if ( is_tag() ) {
					unset($_conditions->categories);
				}
			}
			// $display is TRUE if all conditions were met
			foreach ($_conditions as $condition_key => $args) {
				// only cpt have 'post_type' and 'post_type_label' properties
				if ( is_array($args) && isset($args['post_type']) && isset($args['post_type_label']) ) {
					if ( $skip_all_cpt || $post->post_type != $args['post_type'] ) {
						continue;
					}
					$condition = Hustle_Condition_Factory::build('cpt', $args);
				} else {
					$condition = Hustle_Condition_Factory::build($condition_key, $args);
				}
				if ( $condition ) {
					$condition->set_type($type);
					$display = ( $display && $condition->is_allowed($this) );
				}
            }
        }

        return $display;
    }

    /**
     * Returns array of active conditions objects
     *
     * @param $type
     * @return array
     */
    function get_type_conditions( $type ){
        $conditions = array();
        if( !in_array( $type, $this->types ) ) $conditions;

        $method = "get_$type";

        if ( $type == 'shortcode' ) {
            $settings = $this->get_parent_settings()->get_shortcode();
        } elseif ( $type == 'widget' ) {
            $settings = $this->get_parent_settings()->get_widget();
        } else {
            $settings = $this->{$method}();
        }

		// defaults
		$_conditions = array(
			'posts' => array(),
			'pages' => array(),
			'categories' => array(),
			'tags' => array()
		);
		$_conditions = wp_parse_args($settings->get_conditions(), $_conditions);
        if( !empty( $_conditions ) ){
            foreach( $_conditions as $condition_key => $args ){
				// only cpt have 'post_type' and 'post_type_label' properties
				if ( is_array($args) && isset($args['post_type']) && isset($args['post_type_label']) ) {
					$conditions[$condition_key] = Hustle_Condition_Factory::build( 'cpt', $args );
				} else {
					$conditions[$condition_key] = Hustle_Condition_Factory::build( $condition_key, $args );
				}

				if ( is_object( $conditions[ $condition_key ] ) && method_exists( $conditions[ $condition_key ], 'set_type' ) ) {
					$conditions[$condition_key]->set_type( $type );
				}

            }
        }

        return $conditions;
    }

}