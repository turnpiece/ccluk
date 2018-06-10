<?php

class Opt_In_Condition_Tags extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){

		if ( class_exists('woocommerce') ){
			if ( is_woocommerce() ) {
				return true;
			}
		}

		if ( !isset( $this->args->tags ) || empty( $this->args->tags ) ) {
			if ( !is_singular() ) {
				if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} elseif ( in_array("all", $this->args->tags) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return false;
			} else {
				return true;
			}
		}

		switch( $this->args->filter_type ){
			case  "only":
				return array_intersect( $this->_get_current_tags(), (array) $this->args->tags ) !== array();
				break;
			case "except":
				return array_intersect( $this->_get_current_tags(), (array) $this->args->tags ) === array();
				break;
			default:
				return true;
				break;
		}
	}

	/**
	 * Returns tags of current page|post
	 *
	 * @since 2.0
	 * @return array
	 */
	private function _get_current_tags(){
		global $post;
		if(!isset( $post )) return array();

		function _get_term_id($obj) {
			return (string) $obj->term_id;
		};
		$terms = get_the_tags( $post->ID );
		$terms = ( is_wp_error( $terms ) || empty( $terms ) ) ? array() : $terms;
		return array_map( "_get_term_id", $terms );
	}

	function label() {
		if ( isset( $this->args->tags ) && !empty( $this->args->tags ) ) {
			$total = is_array( $this->args->tags ) ? count($this->args->tags) : 0;
			switch( $this->args->filter_type ){
				case  "only":
					return ( in_array("all", $this->args->tags) )
						? __("All tags", Opt_In::TEXT_DOMAIN)
						: sprintf( __("%d tags", Opt_In::TEXT_DOMAIN), $total );
					break;
				case "except":
					return ( in_array("all", $this->args->tags) )
						? __("No tags", Opt_In::TEXT_DOMAIN)
						: sprintf( __("All tags except %d", Opt_In::TEXT_DOMAIN), $total );
					break;

				default:
					return null;
					break;
			}
		} else {
			return ( !isset($this->args->filter_type) || $this->args->filter_type == "except" )
				? __("All tags", Opt_In::TEXT_DOMAIN)
				: __("No tags", Opt_In::TEXT_DOMAIN);
		}
	}
}