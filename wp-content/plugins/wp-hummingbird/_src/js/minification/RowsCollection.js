const RowsCollection = () => {
    let items = [];
    let currentFilter = '';
    let currentSecondaryFilter = '';

    return {
        push: function( row ) {
            if ( typeof row === 'object' ) {
                items.push( row );
            }
        },

        getItems: function() {
            return items;
        },

        getItem: function( i ) {
            if ( items[i] ) {
                return items[i];
            }
            return false;
        },

        /**
         * Get a collection item by type and ID
         * @param type
         * @param id
         */
        getItemById: function( type, id ) {
            let value = false;
            for ( let i in items ) {
                if ( 'wphb-file-' + type + '-' + id === items[i].getId() ) {
                    value = items[i];
                    break;
                }
            }
            return value;
        },

        getItemsByDataType: function( type ) {
			let selected = [];

			for ( let i in items ) {
				if ( items[i].isType( type ) ) {
					selected.push( items[i] );
                }
			}

			return selected;
        },

        getVisibleItems: function() {
            let visible = [];
            for ( let i in items ) {
                if ( items[i].isVisible() ) {
                    visible.push( items[i] );
                }
            }
            return visible;
        },

        getSelectedItems: function() {
            let selected = [];

            for ( let i in items ) {
                if ( items[i].isVisible() && items[i].isSelected() ) {
                    selected.push( items[i] );
                }
            }

            return selected;
        },

        addFilter: function( filter, type ) {
            if ( type === 'secondary' ) {
                currentSecondaryFilter = filter;
            }
            else {
                currentFilter = filter;
            }
        },

        applyFilters: function() {
            for ( let i in items ) {
                if ( items[i] ) {
                    if ( items[i].matchFilter( currentFilter ) && items[i].matchSecondaryFilter( currentSecondaryFilter ) ) {
                        items[i].show();
                    }
                    else {
                        items[i].hide();
                    }
                }

            }
        }
    };
};

export default RowsCollection;