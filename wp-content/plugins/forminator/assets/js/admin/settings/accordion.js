(function($){
    define([
        'admin/settings/toggle'
    ], function( Toggle ){

        return Toggle.extend({
            multiple: false,

            events: {
                'click .wpmudev-accordion--header .wpmudev-header--action': 'open'
            },

            className: 'wpmudev-option',

            get_value_html: function( value, index ){

                return '<label class="wpmudev-header--title">' + value.label + '</label>' +
                '<button class="wpmudev-header--action">' +
                    '<span class="wpmudev-icon--plus" aria-hidden="true"></span>' +
                    '<span class="wpmudev-sr-only">Open ' + value.label + '</span>' +
                '</button>';

            },

            get_field_html: function(){

                var container_class = '';

				if ( this.options.containerClass )
					container_class = ' ' + this.options.containerClass;
                
                return '<div class="wpmudev-accordion">' +
                    '<div class="wpmudev-accordion--header">' + this.get_values_html() + '</div>' +
                    '<div class="wpmudev-accordion--section' + container_class + '"></div>' +
                '</div>';

            },

            open: function(e){

                var $target = $( e.target ),
					$container = $target.closest( ".wpmudev-accordion" );

                $container.toggleClass( "wpmudev-is_active" );

                e.preventDefault();
                e.stopPropagation();
                
            }

        });

    });

})(jQuery);