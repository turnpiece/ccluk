(function( $, doc ) {
    "use strict";

    (function () {

        $( document ).ready( function () {
            // SELECT2 wpmudev-ui
            $( ".wpmudev-select" ).wpmuiSelect({
                allowClear: false,
                minimumResultsForSearch: Infinity,
                containerCssClass: "wpmudev-select2",
                dropdownCssClass: "wpmudev-select-dropdown"
            });

            // SELECT2 forminator-ui
            $( ".wpmudev-option--select" ).wpmuiSelect({
                allowClear: false,
                minimumResultsForSearch: Infinity,
                containerCssClass: "wpmudev-option--select2",
                dropdownCssClass: "wpmudev-option--select2-dropdown"
            });

			//cform,poll and quiz
            $( "#wpf-cform-check_all" ).on( "click", function ( e ) {
				var checked = this.checked;
				$( ".wpmudev-checkbox input" ).each( function () {
					this.checked = checked;
				});
				if ( $( 'form[name="bulk-action-form"] input[name="ids"]' ).length ) {
					var ids = $( ".wpmudev-checkbox input:checked" ).map( function() { if ( parseFloat( this.value ) ) return this.value; } ).get().join( ',' );
					$( 'form[name="bulk-action-form"] input[name="ids"]' ).val( ids );
				}
			});

			//cform,poll and quiz single check
			$( ".wpmudev-checkbox input" ).on( "click", function(){
				if ( $( 'form[name="bulk-action-form"] input[name="ids"]' ).length ) {
					var ids = $( ".wpmudev-checkbox input:checked" ).map( function() { if ( parseFloat( this.value ) ) return this.value; } ).get().join( ',' );
					$( 'form[name="bulk-action-form"] input[name="ids"]' ).val( ids );
				}
			});

			//Only for the entries
			$( ".wpmudev-checkbox input#forminator-entries-all" ).on( "click", function(){
				var checked = this.checked;
				$( ".wpmudev-entries--result .wpmudev-checkbox input" ).each( function () {
					this.checked = checked;
				});
			});

			$( ".wpmudev-check-all" ).on( "click", function ( e ) {
				e.preventDefault();
				$( ".wpmudev-multicheck .wpmudev-checkbox input" ).each( function () {
					this.checked = true;
				});
			});

			$( ".wpmudev-uncheck-all" ).on( "click", function ( e ) {
				e.preventDefault();
				$( ".wpmudev-multicheck .wpmudev-checkbox input" ).each( function () {
					this.checked = false;
				});
			});

			// ACTION minimize
			$( ".wpmudev-can--hide" ).ready( function () {
				var $this = $( this ),
					$button = $this.find( ".wpmudev-box-header" )
				;

				$button.on( "click", function () {
					var $parent = $( this ).closest( ".wpmudev-can--hide" );
					$parent.toggleClass( "wpmudev-is--hidden" );
				});
			});

			// ACTION open entries
			$(document).on('click', '.wpmudev-open-entry', function(e){
				
				if ($(e.target).attr('type') === 'checkbox' || $(e.target).hasClass('wpdui-icon-check') ) {
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				
				var $this = $(this),
					$entry_id = $this.data('entry'),
					$entry = $("#forminator-" + $entry_id),
					$open = true;
				
				if ( $entry.hasClass( 'wpmudev-is_open' ) ) {
					$open = false;
				}
				$('.wpmudev-entries--result').removeClass('wpmudev-is_open');
				if ( $open ) {
					$entry.toggleClass('wpmudev-is_open');
				}
			});

			// OPEN control menu
			$( ".wpmudev-result--menu" ).ready( function () {

				var $this = $( this ),
					$button = $this.find( ".wpmudev-button-action" );

				$button.on( "click", function () {
					var $menu = $( this ).next( ".wpmudev-menu" );

					// Close all already opened menus
					$( ".wpmudev-result--menu.wpmudev-active" ).removeClass( "wpmudev-active" );
					$( ".wpmudev-button-action.wpmudev-active" ).not( $( this ) ).removeClass( "wpmudev-active" );
					$( ".wpmudev-menu" ).not( $menu ).addClass( "wpmudev-hidden" );

					$( this ).toggleClass( "wpmudev-active" );
					$menu.toggleClass( "wpmudev-hidden" );
				});

			});

			// ITEMS position
			$( document ).ready( function () {

				var $this   = $( ".wpmudev-list" ),
					$table  = $this.find( ".wpmudev-list-table" ),
					$item   = $table.find( ".wpmudev-table-body tr" )
				;

				var $totalItems = $item.length,
					$itemCount  = $totalItems
				;

				$item.each(function(){
					$( this ).find( '.wpmudev-body-menu' ).css( 'z-index', $itemCount );
					$itemCount--;
				});

			});
        });
    }());
}( jQuery, document ));
