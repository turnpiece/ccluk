;( function( $ ) {

	var Filter = function( $root, type, filter_callback ) {

		function get_filter_form_field() {
			return $( '.shipper-filter-area [data-filter-field="' + type + '"]', $root );
		}

		function get_filter_form_value() {
			return get_filter_form_field().find( ':input' ).val();
		}

		function reset_filter_form_field() {
			get_filter_form_field()
				.find( ':input' ).val( '' )
			;
			clear_active_filter();
		}

		function get_active_filter( ) {
			return $( '.shipper-active-filters .shipper-filter', $root )
				.filter( '[data-filter-type="' + type + '"]' );
		}

		function clear_active_filter() {
			get_active_filter().removeClass( 'shipper-filter-active' );
		}

		function is_active_filter() {
			return get_active_filter().is( '.shipper-filter-active' );
		}

		function update_active_filter( value ) {
			clear_active_filter();
			if ( ! value ) {
				return false;
			}
			get_active_filter()
				.addClass( 'shipper-filter-active' )
				.find( '.shipper-filter-target' ).text( value )
			;
		}

		function get_filter_callback() {
			return filter_callback( get_filter_form_value() );
		}
		
		var filter = {
			apply_filter: function () {
				update_active_filter( get_filter_form_value() );
				return get_filter_callback();
			},
			reset_filter: function () {
				reset_filter_form_field();
				filter.apply_filter();
			},
			is_active: is_active_filter,
		};

		return filter;
	};

	var Pagination = function( $root ) {

		var PER_PAGE = parseInt((_shipper || {}).per_page || 10);

		var _current_page = 1;

		function get_current_page() {
			return _current_page || 1;
		}

		function set_current_page( idx ) {
			_current_page = idx || 1;
		}

		function apply_pagination( page ) {
			if ( page ) {
				set_current_page( page );
			}
			page = get_current_page();

			var start = ( page - 1 ) * PER_PAGE + 1,
				end = page * PER_PAGE,
				$rows = $( '.shipper-filelist tr.shipper-paginated', $root ),
				$pagination_items = $( '.sui-pagination li', $root ),
				current = 0
			;
			$rows.removeClass( 'shipper-paginated-visible' );
			$rows.each( function () {
				current++;
				if ( current < start ) { return true; }
				if ( current > end ) { return false; }
				var $row = $( this );
				$row.addClass( 'shipper-paginated-visible' );
			} );

			$pagination_items
				.removeClass( 'sui-active' )
				.filter( '[data-idx="' + page + '"]' ).addClass( 'sui-active' )
			;
			$root.trigger( 'shipper-pagination-applied' );
		}

		function build_pagination() {
			var $paginations = $( '.sui-pagination', $root ),
				$rows = $( '.shipper-filelist tr.shipper-paginated', $root ),
				items = Math.ceil( $rows.length / PER_PAGE )
			;
			if ( $rows.length <= PER_PAGE ) {
				$paginations.remove();
				return false;
			}
			$paginations.each( function () {
				var $pagination = $( this ),
					$first = $pagination.find( 'li:first' )
				;
				for ( var i = 1; i <= items; i++ ) {
					var $tpl = $first.clone();
					$tpl
						.attr( 'data-idx', i )
						.find( 'a' )
							.attr( 'href', '#page-' + i )
							.text( i )
							.off( 'click' )
							.on( 'click', function ( e ) {
								apply_pagination( $( this ).closest( 'li' ).attr( 'data-idx' ) );
								return stop_prop( e );
							})
					;
					$pagination.find( 'li:last' ).before( $tpl );
				}
			});

			$root
				.off( 'click', '.sui-pagination a[href="#first"]' )
				.on( 'click', '.sui-pagination a[href="#first"]', function( e ) {
					apply_pagination( 1 );
					return stop_prop( e );
				} )
			;
			$root
				.off( 'click', '.sui-pagination a[href="#last"]' )
				.on( 'click', '.sui-pagination a[href="#last"]', function( e ) {
					apply_pagination( $( this ).closest( 'ul' ).find( 'li' ).length - 2 );
					return stop_prop( e );
				} )
			;

			$( '.shipper-files-count', $root )
				.text( $rows.length )
					.closest( '.sui-pagination-results' )
				.show();

			$paginations.show();
		}

		function destroy_pagination() {
			var $paginations = $( '.sui-pagination', $root ),
				$items = $paginations.find( '[data-idx]' )
			;
			$items.remove();
			$paginations.hide();
		}

		build_pagination();

		return {
			build: build_pagination,
			apply: apply_pagination,
			destroy: destroy_pagination,
			get_current: get_current_page
		};
	};

	var PaginatedFilterArea = function( $root ) {

		var FILTER_PATH = 'path';
		var FILTER_TYPE = 'type';
		var FILTER_SIZE = 'size';

		var _filters = {};

		_filters[ FILTER_PATH ] = new Filter( $root, FILTER_PATH, function ( value, $el ) {
			var rx = new RegExp( value, 'i' );
			return function ( index ) {
				if ( ! value ) return true;
				return !! rx.exec( $( this ).attr( 'data-path' ) );
			};
		});
		_filters[ FILTER_TYPE ] = new Filter( $root, FILTER_TYPE, function ( value, $el ) {
			return function ( index ) {
				if ( ! value ) return true;
				return $( this ).attr( 'data-type' ) === value;
			}
		});
		_filters[ FILTER_SIZE ] = new Filter( $root, FILTER_SIZE, function ( value, $el ) {
			var size_mb = parseInt( value, 10 ) * 1048576;
			return function ( index ) {
				if ( ! value ) return true;
				return parseInt( $( this ).attr( 'data-size' ), 10 ) > size_mb;
			}
		});

		var _pagination = new Pagination( $root );
		_pagination.apply();


		function filter_form_reset() {
			$.each(_filters, function( type, filter ) {
				filter.reset_filter();
			} );
			unselect_all();
			_pagination.destroy();
			_pagination.build();
			_pagination.apply();
		}

		function filter_form_apply() {
			unselect_all();
			$( 'tr.shipper-paginated', $root )
				.removeClass( 'shipper-paginated-visible' )
				.filter( function ( index ) {
					var me = this,
						show = true
					;
					$.each( _filters, function( type, filter ) {
						show = filter.apply_filter().call( me, index );
						if ( ! show ) return false;
					} );
					return show;
				} ).addClass( 'shipper-paginated-visible' );
			;
			_pagination.destroy();
		}

		function get_active_filter_types() {
			var active = [];
			$.each( _filters, function( type, filter ) {
				if ( filter.is_active() ) {
					active.push( type );
				}
			} );
			return active;
		}

		function has_active_filters() {
			return !!get_active_filter_types().length;
		}

		function handle_remove_active_filter( e ) {
			var $active = $( this );
			if ( ! $active.is( '.shipper-filter' ) ) {
				$active = $active.closest( '.shipper-filter' );
			}
			var type = $active.attr( 'data-filter-type' );
			_filters[ type ].reset_filter();
			filter_form_apply();

			if ( ! has_active_filters() ) {
				filter_form_reset();
			}

			return stop_prop( e );
		}

		function toggle_all_selection_visible() {
			var $rows = $( '.shipper-filelist tr.shipper-paginated-visible', $root ),
				$me = $( '.shipper-filelist :checkbox[name="shipper-bulk-all"]', $root );
			if ( $me.attr( 'checked' ) ) {
				select_all();
			} else {
				unselect_all();
			}
		}

		function select_all() {
			var $rows = $( '.shipper-filelist tr.shipper-paginated-visible', $root );
			$rows.each( function( idx, row ) {
				select( $( row ) );
			} );
			$( '.shipper-filelist :checkbox[name="shipper-bulk-all"]', $root )
				.attr( 'checked', true );
			toggle_bulk_actions_disabled();
		}

		function select( $row ) {
			$row.find( ':checkbox[name="shipper-bulk"]' )
				.attr( 'checked', true );
			toggle_bulk_actions_disabled();
		}

		function unselect_all() {
			var $rows = $( '.shipper-filelist tr.shipper-paginated-visible', $root );
			$rows.each( function( idx, row ) {
				unselect( $( row ) );
			} );
			$( '.shipper-filelist :checkbox[name="shipper-bulk-all"]', $root )
				.attr( 'checked', false );
			toggle_bulk_actions_disabled();
		}

		function unselect( $row ) {
			$row.find( ':checkbox[name="shipper-bulk"]' )
				.attr( 'checked', false );
			toggle_bulk_actions_disabled();
		}

		function toggle_bulk_actions_disabled_raw() {
			var $rows = $( '.shipper-filelist tr.shipper-paginated-visible' )
				.find( ':checkbox[name="shipper-bulk"]:checked' );
			$( '.shipper-bulk-actions-field select, .shipper-bulk-actions-field button' )
				.attr( 'disabled', ! $rows.length );
		}
		var toggle_bulk_actions_disabled = _.debounce( toggle_bulk_actions_disabled_raw, 100 );

		function handle_filter_area_toggle( e ) {
			var $el = $( this ),
				$target = $( '.shipper-filter-area', $root )
			;
			$el.toggleClass( 'sui-active' );
			unselect_all();

			if ( $el.is( '.sui-active' ) ) {
				$target.show();
			} else {
				$target.hide();
				if ( ! has_active_filters() ) {
					filter_form_reset();
				}
			}

			return stop_prop( e );
		}

		function apply_bulk_actions() {
			var $me = $( '.shipper-bulk-actions-field', $root ),
				$els = $( ':checkbox[name="shipper-bulk"]:checked', $root ),
				action = $( this ).closest( '.sui-form-field' ).find( 'select' ).val(),
				els = []
			;
			var $msgroot = $('.shipper-toggle-success'),
				hide_warning = function () {
					$msgroot
						.find('.sui-notice-content').hide().end()
						.hide()
					;
				}
			;
			hide_warning();
			if ( ! action || ! $els.length ) return false;

			$me
				.find( 'button' ).attr( 'disabled', true ).end()
				.find( 'select' ).attr( 'disabled', true ).end()
				.find( '.sui-with-button' ).append( '<i class="sui-icon-loader sui-loading"></i>' )
			;
			$els.each( function() {
				var $el = $( this ).closest( 'tr' ),
					dfr = new $.Deferred()
				;
				if ( 'exclude' === action && $el.is( '.shipper-file-excluded' ) ) {
					// Already excluded, don't bother.
					return true;
				}
				if ( 'include' === action && !$el.is( '.shipper-file-excluded' ) ) {
					// Already included, carry on.
					return true;
				}
				els.push({
					path: $el.attr( 'data-path' ),
					_wpnonce: $el.find( '[data-wpnonce]' ).attr( 'data-wpnonce' )
				} );
			} );

			$.post( ajaxurl, { action: 'shipper_bulk_process_paths', apply: action, paths: els } )
				.done( function ( data ) {
					$me
						.find( 'button' ).attr( 'disabled', false ).end()
						.find( 'select' ).attr( 'disabled', false ).end()
						.find( '.sui-loading' ).remove()
					;
					var cls = '.shipper-' + action + '-success';
					clearTimeout($msgroot.data('shipper-timeout'));
					$msgroot
						.find(cls)
							.find('.shipper-toggle-count').text(els.length).end()
							.show().end()
						.show()
					;
					var tmout = setTimeout(hide_warning, 3000);
					$msgroot.data('shipper-timeout', tmout);
					update_exclusions( ( data || {} ).data );
					update_file_item_rows();
					update_package_size_message();
			});
		}

		function enter_to_apply_filters( e ) {
			var key = e.which;
			if ( 13 === key ) {
				filter_form_apply();
			}
		}

		function toggle_files_top_level_warning() {
			/*
			var $notice = $( '.shipper-wizard-files .sui-notice.sui-notice-warning' ),
				$warn_statuses = $( '.shipper-wizard-files .shipper-check-status .sui-tag-warning' )
			;
			if ( ! $notice.length ) {
				return false;
			}
			if ( $warn_statuses.length ) {
				$notice.show();
			} else {
				$notice.hide();
			}
			*/
		}

		function update_package_size_message() {
			var $package_size = $( '#shipper-preflight-results [data-section="files"] div.sui-accordion-item:last .shipper-check-status' );

			if ( ! $package_size.find( 'i.sui-loading' ).length ) {
				$package_size.append(
					'<i class="sui-icon-loader sui-loading"></i>'
				);
			}
			$.post(
				ajaxurl,
				{ action: 'shipper_get_package_size_message' },
				function( rsp ) {
					var oversized = ( ( rsp || {} ).data || {} ).oversized || false,
						markup = ( ( rsp || {} ).data || {} ).markup || false,
						excluded = ( ( rsp || {} ).data || {} ).excluded || 0,
						package_size = ( ( rsp || {} ).data || {} ).package_size || ''
					;
					if ( ! markup ) {
						// No markup, nothing to do here.
						return false;
					}

					var $target = $( '#shipper-preflight-results [data-section="files"] div.sui-accordion-item:last' ),
						$msg = $( '.shipper-package-size-summary' ),
						$file_items = $( '#shipper-preflight-results .shipper-filelist tbody tr' ),
						$status = $target.find( 'div.shipper-check-status .sui-tag' ),
						$title_status = $target.find( '.sui-accordion-item-title i' )
					;

					if ( !! oversized ) {
						$status
							.removeClass( 'sui-tag-success' )
							.addClass( 'sui-tag-warning' )
							.text( package_size );
						$title_status
							.removeClass( 'sui-success' )
							.addClass( 'sui-warning' );
					} else {
						$status
							.removeClass( 'sui-tag-warning' )
							.addClass( 'sui-tag-success' )
							.text( package_size );
						$title_status
							.removeClass( 'sui-warning' )
							.addClass( 'sui-success' );
					}
					$msg.each( function () {
						$( this ).replaceWith( markup );
					} );

					update_file_item_rows();
					toggle_files_top_level_warning();
				}
			).always( function() {
				$package_size.find( 'i.sui-loading' ).remove();
				update_files_tab_status();
			} );
		}
		var debounced_msg_update = _.debounce( update_package_size_message, 1000 );

		function update_file_item_check_row( row_cls ) {
			var $root = $( '#shipper-preflight-results [data-section="files"]'),
				$row = $root.find( 'div.sui-accordion-item' + row_cls ),
				$cnt_row = $row.find( 'div.sui-accordion-item-body' ),
				$status = $row.find( '.sui-tag' ),
				$title_status = $row.find( '.sui-accordion-item-title i' )
				ex = $cnt_row.find( 'tbody .shipper-paginated:not(.shipper-file-excluded)' ).length
			;

			// Update the counts.
			$status.text( ex );

			// Update the colors.
			if ( ex ) {
				$status
					.removeClass( 'sui-tag-success' )
					.addClass( 'sui-tag-warning' );
				$title_status
					.removeClass( 'sui-success' )
					.addClass( 'sui-warning' );
			} else {
				$status
					.removeClass( 'sui-tag-warning' )
					.addClass( 'sui-tag-success' );
				$title_status
					.removeClass( 'sui-warning' )
					.addClass( 'sui-success' );
			}

			// Update the status messages.
			var $cell = $row.find( '[data-shipper-success-msg]' );
			if ( $status.is( '.sui-tag-success' ) ) {
				$cell.text( $cell.attr( 'data-shipper-success-msg' ) );
			} else {
				$cell.text( $cell.attr( 'data-shipper-warning-msg' ) );
			}
		}

		function update_file_item_rows() {
			update_file_item_check_row( '.shipper-file_sizes' );
			update_file_item_check_row( '.shipper-file_names' );
		}

		function update_files_tab_status() {
			$( document ).trigger(
				'shipper:preflight-files:status',
				[ $( '.shipper-wizard-files .shipper-check-status .sui-tag-warning' ).length ]
			);
		}

		function exclude_file( $el ) {
			var exclude = $el.attr( 'data-path' );

			return $.post(ajaxurl, {
				action: 'shipper_toggle_path_exclusion',
				path: exclude,
				_wpnonce: $el.find( '[data-wpnonce]' ).attr( 'data-wpnonce' )
				}, function( resp ) {
					var exs = (resp || {}).data || {};
					update_exclusion( exs, $el );
					debounced_msg_update();
				})
			;
		}

		function update_exclusions( excludes ) {
			excludes = excludes || {};
			$( '.shipper-filelist tr[data-path]', $root ).each( function() {
				update_exclusion( excludes, $( this ) );
			} );
		}

		function update_exclusion( excludes, $el ) {
			var path = $el.attr( 'data-path' );
			if ( ! ! ( excludes[path] || "" ).length ) {
				$el.addClass( 'shipper-file-excluded' );
			} else {
				$el.removeClass( 'shipper-file-excluded' );
			}
		}

		function load_exclusions() {
			return $.post(ajaxurl, {
				action: 'shipper_get_path_exclusions',
				}, function( resp ) {
					var exs = (resp || {}).data || {};
					update_exclusions( exs );
					update_file_item_rows();
					$( document ).trigger( 'shipper:preflight-files:status' );
				})
			;
		}

		function boot() {
			$( '.shipper-filelist tr.shipper-paginated-visible :checkbox[name="shipper-bulk"]', $root )
				.on( 'change', toggle_bulk_actions_disabled );
			$( '.shipper-filelist :checkbox[name="shipper-bulk-all"]', $root )
				.off( 'change' )
				.on( 'change', toggle_all_selection_visible )
			;
			$( '.shipper-filter-area .shipper-filter-reset', $root )
				.off( 'click' )
				.on( 'click', filter_form_reset )
			;
			$( '.shipper-filter-area .shipper-filter-apply', $root )
				.off( 'click' )
				.on( 'click', filter_form_apply )
			;
			$( '.shipper-filter-area :input', $root )
				.off( 'keydown' )
				.on( 'keydown', enter_to_apply_filters )
			;
			$root
				.off( 'click', '.shipper-filter .sui-active-filter-remove' )
				.on( 'click', '.shipper-filter .sui-active-filter-remove', handle_remove_active_filter )
			;

			$root
				.off( 'click', '.sui-pagination-open-filter' )
				.on( 'click', '.sui-pagination-open-filter', handle_filter_area_toggle )
			;

			$root
				.off( 'click', '.shipper-bulk-action' )
				.on( 'click', '.shipper-bulk-action', apply_bulk_actions )
			;

			$root.on( 'click', '.shipper-filelist tr a', function( e ) {
				exclude_file( $( this ).closest( 'tr' ) );
				return stop_prop( e );
			} );

			$root.on( 'shipper-pagination-applied', function () {
				unselect_all();
			});

			update_files_tab_status();
			load_exclusions();
			toggle_files_top_level_warning();
			toggle_bulk_actions_disabled();
		}

		boot();

		return {
			get_filters: function() {
				return _filters;
			},
			get_active_filters: get_active_filter_types,
			apply_pagination: function() {
				_pagination.apply();
			},
			get_current_page: function () {
				return _pagination.get_current();
			}
		}
	};

	function stop_prop( e ) {
		if ( e && e.preventDefault ) e.preventDefault();
		if ( e && e.stopPropagation ) e.stopPropagation();
		return false;
	}

	var _areas = [];
	function bootstrap() {
		$( '.shipper-wizard-result-files' ).each( function() {
			_areas.push( new PaginatedFilterArea( $( this ) ) );
		} );
	}

	$( window ).on('load', function() {
		if ( $( '.shipper-wizard-result-files' ).length ) {
			bootstrap();
		}
	});
} )( jQuery );
