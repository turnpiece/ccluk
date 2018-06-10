<?php


class Hustle_Dashboard_Data
{

	const CURRENT_COLOR_INDEX = 'hustle_color_index';
	const MODULE_GRAPH_COLOR = 'graph_color';

	var $modules = array();
	var $popups = array();
	var $slideins = array();
	var $embeds = array();
	var $social_sharings = array();
	var $active_modules = array();
	var $top_active_modules = array();
	var $today_total_conversions = 0;
	var $conversions_today = 0;
	var $most_converted_module = '-';
	var $ss_share_stats_data = array();
	var $ss_total_share_stats = 0;
	var $graph_date_conversions = array();
	var $graph_dates = array();

	var $color = 0;
	var $types = array();
	var  $colors = array(
		'#FF0000',
		'#FFFF00',
		'#00EAFF',
		'#AA00FF',
		'#FF7F00',
		'#BFFF00',
		'#0095FF',
		'#FF00AA',
		'#FFD400',
		'#6AFF00',
		'#0040FF',
		'#EDB9B9',
		'#B9D7ED',
		'#E7E9B9',
		'#DCB9ED',
		'#B8EDE0',
		'#8F2323',
		'#2362BF',
		'#8F6A23',
		'#6B238F',
		'#4F8F23',
		'#000000',
	);


	function __construct()
	{
		$this->_prepare_data();
	}

	private function _prepare_data() {
		$module_instance = Hustle_Module_Collection::instance();

		$this->popups = $module_instance->get_all( null, array( 'module_type' => 'popup' ) );
		$this->slideins = $module_instance->get_all( null, array( 'module_type' => 'slidein' ) );
		$this->embeds = $module_instance->get_all( null, array( 'module_type' => 'embedded' ) );
		$this->social_sharings = $module_instance->get_all( null, array( 'module_type' => 'social_sharing' ) );

		$this->active_modules = $module_instance->get_all(true, array(
			'except_types' => array( 'social_sharing' )
		));

		if ( is_array( $this->social_sharings ) && count( $this->social_sharings ) ) {
			$this->ss_share_stats_data = $module_instance->get_share_stats(0,5);
			$this->ss_total_share_stats = $module_instance->get_total_share_stats();
		}

		$end_day = strtotime( 'now' );
		$first_day = strtotime( "-1 month" );
		$last_week = date( 'Ymd', ( $end_day - WEEK_IN_SECONDS) );
		$prev_month = date( 'Ymd', $first_day );
		$today = date( 'Ymd', $end_day );

		$today_total_conversions = $module_instance->get_today_total_conversion( $today );
		$this->today_total_conversions = ( empty($today_total_conversions) ) ? 0 : $this->_parse_today_conversions($today_total_conversions);
		$top_conversions = $module_instance->get_top_module_conversion_without_ss( $prev_month, $today, 0, 5 );
		$most_converted = $module_instance->get_top_module_conversion_without_ss( null, null, 0, 1 );
		$this->most_converted_module = ( empty($most_converted) ) ? '-' : $this->_parse_most_converted($most_converted);

		// to be replaced
		$temp_index = 0;
		$this->color = (int) get_option( self::CURRENT_COLOR_INDEX, 0 );

		foreach( $top_conversions as $t ) {
			$module = Hustle_Module_Model::instance()->get( $t->module_id );
			$is_active = (bool) $module->active;

			if ( $is_active ) {

				$past_week = $module->get_module_conversion( $last_week, $today, false );
				$past_week = empty($past_week) ? 0 : $this->_parse_total_conversion($past_week);

				$all_time = $module->get_statistics($module->module_type)->conversions_count;
				$conversion_list = $module->get_module_conversion( $prev_month, $today, true );

				if ( !empty($conversion_list) ) {
					$conversion_list = $this->_parse_dates_for_graph($conversion_list);
				}

				$total_views = $module->get_statistics($module->module_type)->views_count;
				$rate = $module->get_statistics($module->module_type)->conversion_rate;

				if( is_array( $this->colors ) && ( $this->color >= count( $this->colors ) ) ) $this->color = 0;

				$color = $module->get_meta( self::MODULE_GRAPH_COLOR );

				if ( empty( $color ) ) {
					$color = $this->colors[ $this->color ];
					$module->update_meta( self::MODULE_GRAPH_COLOR, $color );
					$this->color++;
				}

				array_push( $this->top_active_modules, wp_parse_args(
					$module->get_data(),
					array(
						'module_id' => $t->module_id,
						'past_week' => $past_week,
						'past_month' => $t->conversions,
						'all_time' => $all_time,
						'conversion_list' => $conversion_list,
						'total_views' => $total_views,
						'rate' => $rate,
						'color' => $color,
					)
				) );
			}
		}

		// Update color index
		update_option( self::CURRENT_COLOR_INDEX, $this->color );

		// parse data for graph
		if ( !empty( $this->graph_dates ) ) {
			$this->_parse_conversions_for_graph($this->top_active_modules);
		}
	}

	private function _parse_total_conversion( $conversions ) {
		$sum = 0;
		foreach( $conversions as $conversion ) {
			$sum += (int) $conversion->conversions;
		}
		return $sum;
	}

	private function _parse_most_converted( $most_converted ) {
		$module_id = 0;
		if ( isset($most_converted[0]) && isset($most_converted[0]->module_id) ) {
			$module_id = $most_converted[0]->module_id;
		}
		if ( $module_id ) {
			$module = Hustle_Module_Model::instance()->get( $module_id );
			return $module->module_name;
		}
		return '-';
	}

	private function _parse_today_conversions( $today_conversions ) {
		$total = 0;
		if ( isset($today_conversions->conversions) ) {
			$total = (int) $today_conversions->conversions;
		}
		return $total;
	}

	private function _parse_conversions_for_graph( $top_active_modules ) {
		$this->graph_date_conversions = array();
		foreach( $this->graph_dates as $key => $dates ) {
			$conversions = array();
			foreach( $top_active_modules as $module ) {
				if ( isset( $module['conversion_list'] ) ) {
					if ( array_key_exists( $key, $module['conversion_list'] ) ) {
						$total_module_conversion = $module['conversion_list'][$key]['conversions'];
						array_push( $conversions, (int) $total_module_conversion );
					} else {
						array_push( $conversions, 0 );
					}
				}
			}
			$this->graph_date_conversions[ $key ] = array(
				'formatted' => $dates,
				'conversions' => $conversions,
			);
		}
	}

	private function _parse_dates_for_graph( $conversions ) {
		$updated_conversions = array();
		foreach( $conversions as $key => $conversion ) {
			$format_date = substr($conversion['dates'], 0, 4) . '-' . substr($conversion['dates'], 4, 2) . '-' . substr($conversion['dates'], 6, 2);
			$this->graph_dates[ $conversion['dates'] ] = $format_date;
			$updated_conversions[ $conversion['dates'] ] = $conversion;
		}
		return $updated_conversions;
	}

	public static function uasort( $a, $b ) {
		if ( $a['month'] == $b['month'] ) {
			return 0;
		} elseif ( $a['month'] > $b['month'] ) {
			return 1;
		} else {
			return -1;
		}
	}
}