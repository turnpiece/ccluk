( function ($) {
	define([
	], function() {
		return Backbone.View.extend({
			events: {},
			className: 'wpmudev-option',
			initialize: function( options ) {
				this.options = options;

				if( ! this.model ) return;

				this.id = this.options.id;
				this.name = this.options.name;
				this.layout = this.options.layout;
				this.multiple = typeof this.options.multiple !== 'undefined' ? this.options.multiple : (typeof this.multiple != 'undefined' ? this.multiple : false);
				this.title = typeof this.options.title !== 'undefined' ? this.options.title : '';
				this.label = typeof this.options.label !== 'undefined' ? this.options.label : '';
				this.default_value = typeof this.options.default_value !== 'undefined' ? this.options.default_value : (this.multiple ? [] : '');

				if ( this.init )
				this.init( this.options );

				if ( this.options.init )
				this.options.init();
				if ( this.options.change )
					this.on( 'changed', this.options.change, this );
				else
					this.on( 'changed', this.change, this );
				if ( this.options.focus )
					this.on( 'focus', this.options.focus, this );
				if ( this.options.blur )
					this.on( 'blur', this.options.blur, this );
				if ( this.options.rendered )
					this.on( 'rendered', this.options.rendered, this );
				if ( this.options.on_click )
					this['on_click'] = this.options.on_click;
				if ( this.options.on_change )
					this.on( 'updated', this.options.on_change, this );

				this.on('changed rendered', this.trigger_show);

				this.once( 'rendered', function () {
					var me = this;
					this.get_field().on( 'focus', function () {
						me.trigger( 'focus' );
					}).on( 'blur', function(){
						me.trigger( 'blur' );
					});
				}, this);

				return this.render();
			},

			get_field_id: function () {
				return this.id;
			},

			get_name: function () {
				return this.name;
			},

			get_field: function () {
				return this.$el.find( '[name=' + this.get_name() + ']' );
			},

			get_value: function () {
				var $field = this.get_field();
				if ( ! this.multiple || $field.size() === 1 )
					return $field.val();
				else
					return _.map( $field, function ( el ) { return $( el ).val(); });
			},

			set_value: function ( value ) {
				this.get_field().val(value);
			},

			get_saved_value: function () {
				var value = this.model.get( this.name );

				if( _.isString( value ) && ! _.isEmpty( value ) ) {
					value = value.replace(/"/g, '&quot;');
				}

				return value ? value : this.default_value;
			},

			save_value: function ( value ) {
				this.model.set( this.name, value );
			},

			trigger_show: function () {
				if ( this.options.show )
					this.options.show( this.get_saved_value() );
			},

			change: function ( value ) {
				this.save_value( value );
				if ( this.options.on_change )
					this.options.on_change( value );
			},

			get_title_html: function () {
				if ( this.options.show_title === true ) return '<label class="wpmudev-label--title">' + this.title + '</label>';
			},

			get_label_html: function () {
				if ( this.options.hide_label === true ) return '';

				var attr = {
					'for': this.get_field_id(),
					'class': 'wpmudev-label'
				};

				return '<label ' + this.get_field_attr_html( attr ) + '>' + this.label + '</label>';
			},

			get_field_attr_html: function ( attr ) {
				return _.map( attr, function( value, att ) {
					return att + '="' + value + '"';
				}).join(' ');
			},

			get_field_html: function () {
				return '';
			},

			on_render: function () {
				return false;
			},

			render: function () {
				this.$el.html('');
				this.$el.append( this.get_title_html() );
				this.$el.append( this.get_label_html() );
				this.$el.append( this.get_field_html() );

				this.trigger( 'rendered', this.get_value() );

				this.on_render();
			}
		});
	});
})( jQuery );
