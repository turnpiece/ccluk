const Row = ( _element, _filter, _filter_sec ) => {
    let $el = _element,
        filter = _filter.toLowerCase(),
        filterSecondary = false,
        selected = false,
        visible = true;

    const $include = $el.find( '.toggle-include' ),
        $combine = $el.find( '.toggle-combine' ),
        $minify = $el.find( '.toggle-minify' ),
        $posFooter = $el.find( '.toggle-position-footer' ),
        $defer = $el.find( '.toggle-defer' ),
        $inline = $el.find( '.toggle-inline' ),
        $disableIcon = $el.find( '.toggle-cross > i' ),
        $selectCheckbox = $el.find( '.wphb-minification-file-select input[type=checkbox]' );

    if ( _filter_sec ) {
        filterSecondary = _filter_sec.toLowerCase();
    }

    return {
        hide: function() {
            $el.addClass( 'out-of-filter' );
            visible = false;
        },

        show: function() {
            $el.removeClass( 'out-of-filter' );
            visible = true;
        },

        getElement: function() {
            return $el;
        },

        getId: function() {
            return $el.attr( 'id' );
        },

        getFilter: function() {
            return filter;
        },

        matchFilter: function( text ) {
            if ( text === '' ) {
                return true;
            }

            text = text.toLowerCase();
            return filter.search( text ) > - 1;
        },

        matchSecondaryFilter: function( text ) {
            if ( text === '' ) {
                return true;
            }

            if ( ! filterSecondary ) {
                return false;
            }

            text = text.toLowerCase();
            return filterSecondary === text;
        },

        isVisible: function() {
            return visible;
        },

        isSelected: function() {
            return selected;
        },

        isType: function( type ) {
            return type === $selectCheckbox.attr( 'data-type' )
        },

        select: function() {
            selected = true;
			$selectCheckbox.prop( 'checked', true );
        },

        unSelect: function() {
            selected = false;
			$selectCheckbox.prop( 'checked', false );
        },

        change: function( what, value ) {
            switch ( what ) {
                case 'minify': {
                    $minify.prop( 'checked', value );
                    $minify.toggleClass('changed');
                    let row = $minify.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
                    break;
                }
                case 'combine': {
                    $combine.prop( 'checked', value );
                    $combine.toggleClass('changed');
                    let row = $combine.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
                    break;
                }
                case 'defer': {
                    $defer.prop( 'checked', value );
                    $defer.toggleClass('changed');
                    let row = $defer.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
                    break;
                }
				case 'inline': {
					$inline.prop( 'checked', value );
					$inline.toggleClass('changed');
                    let row = $inline.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
					break;
				}
                case 'include': {
                    $disableIcon.removeClass();
                    $include.prop( 'checked', value );
                    $include.toggleClass('changed');
                    let row = $include.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
                    if ( value ) {
                        $el.removeClass( 'disabled' );
                        $disableIcon.addClass( 'dev-icon dev-icon-cross' );
                        $include.attr( 'checked', true );
                    } else {
                        $el.addClass( 'disabled' );
                        $disableIcon.addClass( 'wdv-icon wdv-icon-refresh' );
                        $include.removeAttr( 'checked' );
                    }
                    break;
                }
                case 'footer': {
                    $posFooter.prop( 'checked', value );
                    $posFooter.toggleClass('changed');
                    let row = $posFooter.closest('.wphb-border-row');
                    let row_status = row.find('span.wphb-row-status');
                    row_status.removeClass('hidden');
                    break;
                }
            }
        }

    };
};

export default Row;
