<?php

class Opt_In_Condition_Pages extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		global $post;

		if ( !isset( $this->args->pages ) || empty( $this->args->pages ) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return true;
			} else {
				return false;
			}
		} elseif ( in_array("all", $this->args->pages) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return false;
			} else {
				return true;
			}
		}

		switch( $this->args->filter_type ){
			case  "only":
				if ( class_exists('woocommerce') ) {
					if( is_shop() ) return in_array( wc_get_page_id('shop'), (array) $this->args->pages );
				}
				if( !isset( $post ) || !( $post instanceof WP_Post ) || $post->post_type !== "page" ) return false;

				return in_array( $post->ID, (array) $this->args->pages );

				break;
			case "except":
				if ( class_exists('woocommerce') ) {
					if( is_shop() ) return !in_array( wc_get_page_id('shop'), (array) $this->args->pages );
				}
				if( !isset( $post ) || !( $post instanceof WP_Post ) || $post->post_type !== "page"  ) return true;

				return !in_array( $post->ID, (array) $this->args->pages );

				break;
			default:
				return true;
				break;
		}
	}


	function label(){
		if ( isset( $this->args->pages ) && !empty( $this->args->pages ) && is_array( $this->args->pages ) ) {
			$total = count( $this->args->pages );
			switch( $this->args->filter_type ){
				case  "only":
					return ( in_array("all", $this->args->pages) )
						? __("All pages", Opt_In::TEXT_DOMAIN)
						: sprintf( __("%d pages", Opt_In::TEXT_DOMAIN), $total );
					break;
				case "except":
					return ( in_array("all", $this->args->pages) )
						? __("No pages", Opt_In::TEXT_DOMAIN)
						: sprintf( __("All pages except %d", Opt_In::TEXT_DOMAIN), $total );
					break;

				default:
					return null;
					break;
			}
		} else {
			return ( !isset($this->args->filter_type) || $this->args->filter_type == "except" )
				? __("All pages", Opt_In::TEXT_DOMAIN)
				: __("No pages", Opt_In::TEXT_DOMAIN);
		}
	}
}