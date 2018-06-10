<?php

class Opt_In_Condition_Categories extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){

		if ( class_exists('woocommerce') ){
			if ( is_woocommerce() ) {
				return true;
			}
		}

		if ( !isset( $this->args->categories ) || empty( $this->args->categories ) ) {
			if ( !is_singular() ) {
				if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
					return true;
				} else {
					return false;
				}
			} else {
				return true;
			}
		} elseif ( in_array("all", $this->args->categories) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return false;
			} else {
				return true;
			}
		}

		switch( $this->args->filter_type ){
			case  "only":
				return array_intersect( $this->_get_current_categories(), (array) $this->args->categories ) !== array();
				break;
			case "except":
				return array_intersect( $this->_get_current_categories(), (array) $this->args->categories ) === array();
				break;
			default:
				return true;
				break;
		}
	}

	/**
	 * Returns categories of current page|post
	 *
	 * @since 2.0
	 * @return array
	 */
	private function _get_current_categories(){
		global $post;
		if( !isset( $post ) ) return array();
		// If PHP <5.3 as 5.2 does not support anonymous functions.
		function _get_term_id ($obj) {
			return (string) $obj->term_id;
		};
		$terms = get_the_terms( $post, "category" );
		return array_map( "_get_term_id", empty( $terms ) ? array( ) : $terms );
	}

	function label(){
		if ( isset( $this->args->categories ) && !empty( $this->args->categories ) && is_array( $this->args->categories ) ) {
			$total = count( $this->args->categories );
			switch( $this->args->filter_type ){
				case  "only":
					return ( in_array("all", $this->args->categories) )
						? __("All categories", Opt_In::TEXT_DOMAIN)
						: sprintf( __("%d categories", Opt_In::TEXT_DOMAIN), $total );
					break;
				case "except":
					return ( in_array("all", $this->args->categories) )
						? __("No categories", Opt_In::TEXT_DOMAIN)
						: sprintf( __("All categories except %d", Opt_In::TEXT_DOMAIN), $total );
					break;

				default:
					return null;
					break;
			}
		} else {
			return ( !isset($this->args->filter_type) || $this->args->filter_type == "except" )
				? __("All categories", Opt_In::TEXT_DOMAIN)
				: __("No categories", Opt_In::TEXT_DOMAIN);
		}
	}
}