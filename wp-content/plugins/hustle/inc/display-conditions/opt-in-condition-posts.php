<?php

class Opt_In_Condition_Posts extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		global $post;

		if ( !isset( $this->args->posts ) || empty( $this->args->posts ) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return true;
			} else {
				return false;
			}
		} elseif ( in_array("all", $this->args->posts) ) {
			if ( !isset($this->args->filter_type) || $this->args->filter_type == "except" ) {
				return false;
			} else {
				return true;
			}
		}

		switch( $this->args->filter_type ){
			case  "only":
				if( !isset( $post ) || !( $post instanceof WP_Post ) || $post->post_type !== "post" ) return false;

				return in_array( $post->ID, (array) $this->args->posts );

				break;
			case "except":
				if( !isset( $post ) || !( $post instanceof WP_Post ) || $post->post_type !== "post" ) return true;

				return !in_array( $post->ID, (array) $this->args->posts );

				break;

			default:
				return true;
				break;
		}
	}


	function label(){
		if ( isset( $this->args->posts ) && !empty( $this->args->posts ) && is_array( $this->args->posts ) ) {
			$total = count( $this->args->posts );
			switch( $this->args->filter_type ){
				case  "only":
					return ( in_array("all", $this->args->posts) )
						? __("All posts", Opt_In::TEXT_DOMAIN)
						: sprintf( __("%d posts", Opt_In::TEXT_DOMAIN), $total );
					break;
				case "except":
					return ( in_array("all", $this->args->posts) )
						? __("No posts", Opt_In::TEXT_DOMAIN)
						: sprintf( __("All posts except %d", Opt_In::TEXT_DOMAIN), $total );
					break;

				default:
					return null;
					break;
			}
		} else {
			return ( !isset($this->args->filter_type) || $this->args->filter_type == "except" )
				? __("All posts", Opt_In::TEXT_DOMAIN)
				: __("No posts", Opt_In::TEXT_DOMAIN);
		}
	}
}