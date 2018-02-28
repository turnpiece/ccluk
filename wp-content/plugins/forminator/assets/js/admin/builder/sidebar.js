( function ($) {
	define([
		'admin/builder/sidebar/field-settings',
		'text!admin/templates/builder-sidebar.html'
	], function( sidebarSettings, sidebarTpl ) {
		// Create settings sidebar element
		$( 'body' ).append( '<div id="wpmudev-sidebar-settings" class="wpmudev-ui" />' );

		var Sidebar = Backbone.View.extend({
			sidebarTpl: Forminator.Utils.template( $( sidebarTpl ).find( '#builder-sidebar-tpl' ).html() ),

			tabsTpl: Forminator.Utils.template( $( sidebarTpl ).find( '#builder-sidebar-field-tabs-tpl' ).html() ),

			field_settings_view: false,

			events: {
				"click .forminator-sidebar-close": "destroy_sidebar"
			},

			initialize: function ( options ) {
				//Open settings
				this.listenTo( Forminator.Events, "forminator:sidebar:open:settings", this.create_settings );

				//Destroy settings when button is clicked
				this.listenTo( Forminator.Events, "forminator:sidebar:close:settings", this.destroy_settings );

				return this.render();
			},

			render: function() {
				this.$el.html( this.sidebarTpl() );

				Forminator.Events.trigger( "forminator:sidebar:rendered" );

				// Render field categories
				this.render_tabs();

				// Initialize dragging new elements
				this.init_drag();
			},

			render_tabs: function() {
				var tabs = Forminator.Data.fields;

				// If captcha settings not set up, remove captcha field
				if( ! Forminator.Data.hasCaptcha ) {
					tabs = _.filter( tabs, function( field ) {
						return field.slug !== 'captcha';
					});
				}

				// Group fields by category
				tabs = _.groupBy( tabs, 'category' );

				// Get tabs headings
				var headings = this.get_headings( tabs );

				this.$el.append( this.tabsTpl({
					tabs: tabs,
					headings: headings
				}));

				// Init tabs
				this.$el.find( ".wpmudev-sort-fields" ).tabs();

				Forminator.Events.trigger( "forminator:field_tabs:rendered" );
			},

			get_headings: function ( tabs ) {
				var headings = Forminator.Data.defaultTabs;

				_.each( tabs, function ( tab, key ) {
					key = key.toLowerCase();

					if( ! _.contains( headings, key ) ) {
						headings.push( key );
					}
				});

				return headings;
			},

			init_drag: function () {
				var $fields = this.$el.find( '.draggable-element' );

				_.each( $fields, function ( field ) {
					var $field = $( field );

					// Add field with click
					$field.on( 'click', function ( e ) {
						var shadow_type = $field.data( 'shadowid' );
						Forminator.Events.trigger( "forminator:add:field:click", shadow_type );
					});

					// Add field with Drag & Drop
					$field.on( 'mousedown', function ( e ) {

						var $main = $( ".wpmudev-builder--form" ),
							shadow_type = $field.data( 'shadowid' ),
							$shadow = $( '[data-shadow=' + shadow_type + ']' ),
							position = $shadow.position(),
							offset = $shadow.offset(),
							height = $shadow.outerHeight(),
							width = $shadow.outerWidth(),
							$clone = $field.clone(),
							clone_width = $field.outerWidth(),
							clone_height = $field.outerHeight()
						;

						$shadow.css({
							 position: "absolute",
							 top: ( e.pageY - ( offset.top - position.top ) - ( height / 2 ) ) ,
							 left: ( e.pageX - ( offset.left - position.left ) - ( width / 2 ) ),
							 visibility: "hidden",
							 zIndex: -1
						})
						.one( 'mousedown', function ( e ) {

						})
						.trigger( e )
						.one( 'dragstart', function ( e, ui ) {
							$field.addClass( 'field-drag-active' );
							$( 'body' ).append( $clone );
							$clone.addClass( 'element-dragging' );
							$clone.css({
								position: "absolute",
								left: e.pageX - ( clone_width / 2 ),
								top: e.pageY - ( clone_height / 2 ),
								zIndex: 9999
							});
						})
						.on('drag', function ( e, ui ) {
							 $clone.css({
								 left: e.pageX - ( clone_width / 2 ),
								 top: e.pageY - ( clone_height / 2 )
							 });
						})
						.one('dragstop', function ( e, ui ) {
							 $field.removeClass( 'field-drag-active' );
							 $clone.remove();
						});
					});
				});
			},

			create_settings: function ( field ) {
				var self = this,
					field_settings = _.where( Forminator.Data.fields, { slug: field.get( 'type' ) })[0]
				;

				this.field_settings_view = new sidebarSettings({
					model: this.model,
					field: field,
					field_settings: field_settings
				});

				// Add settings to sidebar and show
				$( '#wpmudev-sidebar-settings' ).html( this.field_settings_view.el );
				$( '#wpmudev-sidebar-settings' ).find( '.wpmudev-sidebar' ).addClass('wpmudev-is_active');
				$( 'body' ).addClass( 'wpmudev-sidebar-is_active' );

				setTimeout( function () {
					$( '#wpmudev-sidebar-settings' ).find( '.wpmudev-sidebar--wrap' ).addClass( 'wpmudev-show' );
					if( self.close_animation ) {
						clearTimeout( self.close_animation );
					}

					if( self.close_action ) {
						clearTimeout( self.close_action );
					}
				}, 10);

				Forminator.Events.trigger( "forminator:field:settings:created" );

			},

			destroy_settings: function() {
				var $this = $( '#wpmudev-sidebar-settings' ),
					$sidebar = $this.find( '.wpmudev-sidebar' ),
					$wrap = $this.find( '.wpmudev-sidebar--wrap' )
				;

				$wrap.removeClass( 'wpmudev-show' ).addClass( 'wpmudev-hide' );

				this.close_animation = setTimeout( function(){
					$sidebar.removeClass( 'wpmudev-is_active' );
					$( 'body' ).removeClass( 'wpmudev-sidebar-is_active' );
					$wrap.removeClass( 'wpmudev-hide' );
				}, 1000 );

				// Destroy field settings
				if ( this.field_settings_view ) {
					var self = this;
					this.close_action = setTimeout( function(){
						if( self.field_settings_view ) {
							self.field_settings_view.cleanUp();
							self.field_settings_view = false;
						}
					}, 1200 );

				}

				Forminator.Events.trigger("forminator:field:settings:destroyed");

			},

			destroy_sidebar: function ( e ) {
				e.preventDefault();

				// Hide sidebar on Mobile & Appearance settings
				$( '#wpmudev-sidebar' ).removeClass( 'wpmudev-show' ).addClass( 'wpmudev-hide' );

				setTimeout( function(){
					$( '#wpmudev-sidebar' ).removeClass( 'wpmudev-is_active' );
					$( '#wpmudev-sidebar' ).removeClass( 'wpmudev-hide' );
					$( 'body.wp-admin' ).removeClass( 'wpmudev-disabled--mobile' );
				}, 1000 );

			}

		});

		return Sidebar;
	});
})( jQuery );
