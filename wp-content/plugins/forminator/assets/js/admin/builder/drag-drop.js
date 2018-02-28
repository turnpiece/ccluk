( function ($) {
	define(function () {
		var DragDrop = function ( view, model, wrapper, type, layout ) {
			this.initialize( view, model, wrapper, type, layout );
		};

		DragDrop.prototype = {
			view: false,
			model: false,
			layout: false,
			type: false,
			wrapper: false,
			$self: false,
			$main: false,
			$wrapper: false,
			$helper: false,

			wrappers: false,
			drops: false,
			drop: false,
			timeout: 50,
			time: false,
			event: false,

			initialize: function ( view, model, wrapper, type, layout ) {
				this.view = view;
				this.model = model;
				this.layout = layout;
				this.type = type;
				this.wrapper = wrapper;

				this.drops = [];
				this.drop = false;

				// Add new field with click
				Forminator.Events.on( "forminator:add:field:click", this.add_with_click, this );

				this.start();
			},

			start: function () {
				this.$self = this.view.$el;
				this.$main = $( '.wpmudev-builder--form' );

				// Add new field with Drag and Drop
				this.$self.draggable({
					revert: true,
					revertDuration: 0,
					zIndex: 100,
					helper: 'clone',
					cancel: '',
					distance: 10,
					appendTo: this.$main,
					start: $.proxy( this.on_start, this ),
					drag: $.proxy( this.on_drag, this ),
					stop: $.proxy( this.on_stop, this )
				});
			},

			on_start: function ( e, ui ) {
				// Detect if Row or Col is dragged and add class
				if( this.$self.hasClass( 'wpmudev-form-row' ) ) {
					this.$self.addClass( 'forminator-row-dragging' );
				} else {
					this.$self.addClass( 'forminator-col-dragging' );
				}

				this.prepare_drag();

				Forminator.Events.trigger( "field:drag:start", this.view, this.model );
			},

			on_drag: function ( e, ui ) {
				var self = this;

				this.event = e;

				this.update_drop_timeout();
			},

			on_stop: function ( e, ui ) {
				// If mine drop zone skip move
				if( ! this.drop.is_me ) {
					this.update_model();
				}

				this.reset();
				Forminator.Events.trigger( "forminator:field:drap:stop", this.view, this.model );
			},

			prepare_drag: function () {
				this.update_variables();

				this.$self.css( 'visibility', 'hidden' );
				this.$self.css( 'position', '' );

				this.$main.addClass( 'forminator-dragging' );
				this.$helper.css( 'width', this.$self.width() );

				// If wrapper, hide all childs
				if( this.$self.hasClass( 'wpmudev-form-row' ) ) {
					_.each( this.$self.find( '.wpmudev-form-col' ), function ( child ) {
						$( child ).css( 'visibility', 'hidden' );
					});
 				}

				// Create drop points around fields
				this.create_drop_points();

				// Uncomment to enable drop zones visually
				//this.show_debug_data();
			},

			update_variables: function () {
				this.$wrapper = this.$self.closest( '.wpmudev-form-row' );
				this.$helper = $( '.ui-draggable-dragging' );
			},

			placeholder_drop: function () {
				var $placeholder = $( '.wpmudev-form--placeholder' ),
					$placeholder_data = this.get_position( $placeholder ),
					id = this.new_id()
				;

				this.drops.push({
					id: id,
					top: $placeholder_data.top,
					bottom: $placeholder_data.bottom,
					left: $placeholder_data.left,
					right: $placeholder_data.right,
					type: 'full',
					insert: ['over', $placeholder],
					wrapper: false,
					priority: 1
				});

				$drop = $('<div id="forminator-drop-'+ id +'" class="forminator-drop-placeholder"></div>');

				$drop.css({
					width: $placeholder_data.right - $placeholder_data.left,
					height: $placeholder_data.bottom - $placeholder_data.top,
				})

				$drop.insertBefore( $placeholder );
			},

			create_drop_points: function () {
				var self = this,
					wrappers = this.get_wrappers(),
					wrapper_models = this.layout.get( "wrappers" );
					latest_wrapper = false
				;

				// If no wrappers, show placeholder message

				if( _.size( wrapper_models ) === 0 ) {
					this.placeholder_drop();
					return;
				}

				// List each wrapper
				_.each( wrappers, function ( wrapper, key ) {

					var $wrap = wrapper.view.$el,
						$wrap_data = self.get_position( $wrap ),
						$wrapper_field_id = $wrap.find( '.wpmudev-form-col' ).attr( "id" ),
						$field_id = self.view.$el.attr( "id" ),
						is_me = $wrapper_field_id === $field_id
					;


					// Do not create dropzones for dragged row
					if( $wrap.hasClass( "forminator-row-dragging" ) ) return;

					// Wrapper top drop zone
					self.drops.push({
						id: self.new_id(),
						top: $wrap_data.top - 50,
						bottom: $wrap_data.center.y,
						left: $wrap_data.left,
						right: $wrap_data.right,
						type: 'full',
						insert: ['before', $wrap],
						wrapper: wrapper,
						priority: 1,
						is_me: is_me
					});

					// Create left and right drop zones only if col is dragged
					if( ! self.is_row( self.$self ) && ! self.has_max_cols( wrapper ) ) {

						// Get fields from wrapper
						var fields = wrapper[ 'fields' ] || [],
							latest_field
						;

						// Allow only 3 columns per row
						if( _.size( fields ) < 3 ) {

							_.each ( fields, function ( field ) {

								var $field = field.view.$el,
									$field_data = self.get_position( $field ),
									is_field_me = ( $field.attr( "id" ) === self.view.$el.attr( "id" ) )
								;

								// Field Left drop zone
								self.drops.push({
									id: self.new_id(),
									top: $field_data.top,
									bottom: $field_data.bottom,
									left: $field_data.left,
									right: $field_data.left + $field_data.width,
									type: 'side-before',
									insert: ['before', $field],
									wrapper: wrapper,
									field: field,
									priority: 5,
									is_me: is_field_me
								});

								latest_field = field;

							});

							// Field Right drop zone

							var $laters_field = latest_field.view.$el,
								$latest_data = self.get_position( $laters_field ),
								is_last_field_me = ( $laters_field.attr( "id" ) === self.view.$el.attr( "id" ) )
							;

							self.drops.push({
								id: self.new_id(),
								top: $latest_data.top,
								bottom: $latest_data.bottom,
								left: $latest_data.center.x,
								right: $latest_data.right + 20,
								type: 'side-after',
								insert: ['after', $laters_field],
								wrapper: wrapper,
								field: latest_field,
								priority: 5,
								is_me: is_last_field_me
							});

						}

					}

					latest_wrapper = wrapper;

					// Wrapper Right drop zone
				});

				// Create bottom drop point
				var $latest_wrapper = latest_wrapper.view.$el,
					$last_data = self.get_position( $latest_wrapper ),
					$last_wrapper_field_id = $latest_wrapper.find( '.wpmudev-form-col' ).attr( "id" ),
					$field_id = self.view.$el.attr( "id" ),
					is_last_me = $last_wrapper_field_id === $field_id
				;

				self.drops.push({
					id: self.new_id(),
					top: $last_data.center.y,
					bottom: $last_data.bottom + 65,
					left: $last_data.left,
					right: $last_data.right,
					type: 'full',
					insert: ['after', $latest_wrapper],
					wrapper: latest_wrapper,
					priority: 1,
					is_me: is_last_me
				});

				this.render_drops();
			},

			render_drops: function () {
				var self = this;

				// Render drop markers
				_.each( this.drops, function( drop ) {
					$drop = $('<div id="forminator-drop-'+ drop.id +'" class="forminator-drop forminator-drop-'+ drop.type +'"></div>');

					switch ( drop.insert[0] ) {
						case 'before':
							$drop.insertBefore( drop.insert[1] );
							break;
						case 'after':
							$drop.insertAfter( drop.insert[1] );
							break;
					}

					if ( drop.type == 'full' || drop.type == 'inside' ) {
						$drop.css( 'width', ( drop.right - drop.left ) - 20 );
					}
					else if ( drop.type == 'side-before' || drop.type == 'side-after' ) {
						$drop.css( 'height', ( drop.bottom - drop.top ) - 10 );
					}
				});
			},

			update_drop_timeout: function () {
				var self = this;

				// Check if we are dragging over a drop zone
				var zones = _.filter( this.drops, function( each ) {
						return self.event.pageY > each.top &&
							self.event.pageY < each.bottom &&
							self.event.pageX > each.left &&
							self.event.pageX < each.right
						;
					}),
					highest_priority = _.where( zones, {
						priority: _.max(
							_.pluck( zones, 'priority' )
						)
					} )
				;

				// We have a drop zone
				if( highest_priority.length > 0 ) {
					this.select_drop_zone( highest_priority[0] );
				} else {
					this.drop = false;
					// Remove all selected drop zones
					$( '.forminator-drop-use' ).removeClass( "forminator-drop-use" );
				}

			},

			select_drop_zone: function ( drop ) {
				this.drop = drop;

				var $drop = $( '#forminator-drop-' + drop.id );

				// Remove all selected drop zones
				$( '.forminator-drop-use' ).removeClass( "forminator-drop-use" );

				$drop.addClass( "forminator-drop-use" );
			},

			is_row: function ( $el ) {
				if( $el.hasClass( 'wpmudev-form-row' ) )
					return true;
				else
					return false;
			},

			has_max_cols: function ( wrapper ) {
				var wrapperModel = wrapper.model,
					fields = wrapperModel.get( 'fields' ) || [],
					fieldsCount = fields.length || 0
				;

				// Limit fields per grid to 3
				if( fieldsCount < 3 ) {
					return false;
				}

				return true;
			},

			get_position: function ( el ) {
				var $el = $(el),
					width = parseFloat( $el.css('width') ),
					height = parseFloat( $el.css('height') ) - 10,
					offset = $el.offset(),
					top = offset.top,
					bottom = top + height,
					left = offset.left - 10,
					right = left + width,
					center_y = Math.round( top + ( height / 2 ) ),
					center_x = Math.round( left + ( width / 2 ) )
				;

				if( this.is_row( $el ) ) {
					var $prev = $el.prev();

					if( $prev.length > 0 ) {
						$prev_data = this.get_position( $prev );
						top = $prev_data.center.y + 20;
						height = center_y - top;
					}
				} else {
					var $prev = $el.prev();

					if( $prev.length > 0 ) {
						$prev_data = this.get_position( $prev );
						left = $prev_data.center.x;
					} else {
						width = width / 2;
					}
				}

				return {
					width: width,
					height: height,
					top: top,
					bottom: bottom,
					left: left,
					right: right,
					center: {
						y: center_y,
						x: center_x
					},
				}
			},

			get_wrappers: function () {
				return Forminator.Grid || {};
			},

			new_id: function () {
				return Math.floor( Math.random() * 9999 );
			},

			reset: function () {
				// Clear drops
				this.drops = [];
				this.drop = false;

				// Remove drop elemnts
				$( '.forminator-drop' ).remove();
				$( '.forminator-drop-view' ).remove();
				$( '.forminator-drop-placeholder' ).remove();

				// Toggle hidden dragged element
				this.$self.css({
					'position': '',
					'top': '',
					'left': '',
					'z-index': '',
					'visibility': 'visible'
				});

				if( this.$self.hasClass( 'wpmudev-form-row' ) ) {
					_.each( this.$self.find( '.wpmudev-form-col' ), function ( child ) {
						$( child ).css( 'visibility', 'visible' );
					});
 				}

				// Detect if Row or Col is dragged and remove dragging class
				if( this.$self.hasClass( 'wpmudev-form-row' ) ) {
					this.$self.removeClass( 'forminator-row-dragging' );
				} else {
					this.$self.removeClass( 'forminator-col-dragging' );
				}

				this.$main.removeClass( 'forminator-dragging' );
			},

			remove_cols: function ( $element ) {
				// Remove all possible column classes
				$element.removeClass( 'wpmudev-form-col-3' );
				$element.removeClass( 'wpmudev-form-col-4' );
				$element.removeClass( 'wpmudev-form-col-6' );
				$element.removeClass( 'wpmudev-form-col-12' );
			},

			update_model: function () {

				// We don't have correct drop
				if( typeof this.drop.insert === "undefined" ) return;

				if( this.is_shadow() ) {
					this.model = this.model.clone_deep();
					this.model.set( 'element_id', Forminator.Utils.get_unique_id( 'field' ) );
				}

				var insertType = this.drop.insert[ 0 ],
					drop_model = this.drop.wrapper.model,
					wrappers = this.layout.get( 'wrappers' ),
					reload = false
				;

				if( _.size( wrappers ) === 0 ) {
					reload = true;
				}

				// Handle Wrapper model update
				if( this.type === "wrapper" ) {
					var my_model = this.model;

					// Update wrapper position
					this.update_wrapper_position( drop_model, my_model );
				}

				// Handle Field model update
				if( this.type === "field" ) {
					// Check if we drag field next to other fields or new wrapper
					if( typeof this.drop.field === "undefined" ) {

						// We have need new wrapper
						var my_model = this.model,
							new_model = new Forminator.Models.Wrapper({
								"fields": new Forminator.Collections.Fields( my_model )
							})
						;

						new_model.set( 'wrapper_id', Forminator.Utils.get_unique_id( 'wrapper' ) );

						// Update field to full width
						my_model.set( 'cols', 12 );

						// Add wrapper
						wrappers.add( new_model, { silent: true } );

						if( this.wrapper ) {
							// Remove field from old wrapper
							var wrapper_fields = this.wrapper.get( 'fields' );
							wrapper_fields.remove( this.model, { silent: true } );

							// Update old wrapper fields or remove
							this.update_old_wrapper( wrapper_fields );
						}

						// Update wrapper position
						this.update_wrapper_position( drop_model, new_model );

					} else {

						// We drop to same wrapper, just update positions
						if( this.wrapper && this.wrapper !== this.drop.wrapper ) {
							// Field dropped in existing wrapper
							var wrapper_fields = this.wrapper.get( 'fields' );

							// Remove field from old wrapper
							wrapper_fields.remove( this.model, { silent: true } );

							// Update old wrapper fields or remove
							this.update_old_wrapper( wrapper_fields );
						}

						var new_wrapper_fields = drop_model.get( 'fields' ),
							field_model = this.drop.field.model,
							drop_index = new_wrapper_fields.model_index( field_model ),
							insertType = this.drop.insert[ 0 ]
						;

						// If latest drop point increase the index
						if( insertType === "after" ) {
							drop_index++;
						}

						// Move the field to new wrapper
						this.model.add_to( new_wrapper_fields , drop_index );

						// Update fields cols in wrapper
						this.update_cols( new_wrapper_fields );
					}

				}


				if( reload === true ) {
					// Trigger event to reload all fields
					Forminator.Events.trigger( 'dnd:reload:fields' );
				} else {
					// Trigger event to update Grid
					Forminator.Events.trigger( 'dnd:models:updated' );
				}

				//select the field
				Forminator.Events.trigger( "forminator:dnd:field:select", this.model.cid );
				//open setting
				this.activate_field();
			},

			is_me_shadow: function ( type ) {
				// Check if new or existing field being dragged
				var shadow_type = this.$self.data( 'shadow' );

				if( shadow_type === type ) return true;

				return false;
			},

			add_with_click: function ( type ) {
				// Add field with click
				if( this.is_me_shadow( type ) ) {
					this.update_model_with_click();
				}
			},

			update_model_with_click: function () {
				var reload = false;

				if( _.size( wrappers ) === 0 ) {
					reload = true;
				}

				if( this.is_shadow() ) {
					this.model = this.model.clone_deep();
					this.model.set( 'element_id', Forminator.Utils.get_unique_id( 'field' ) );
				}

				var wrappers = this.layout.get( 'wrappers' ),
					my_model = this.model,
					new_model = new Forminator.Models.Wrapper({
						"fields": new Forminator.Collections.Fields( my_model )
					})
				;

				new_model.set( 'wrapper_id', Forminator.Utils.get_unique_id( 'wrapper' ) );

				// Update field to full width
				my_model.set( 'cols', 12 );

				// Add wrapper
				wrappers.add( new_model, { silent: true } );

				if( reload === true ) {
					// Trigger event to reload all fields
					Forminator.Events.trigger( 'dnd:reload:fields' );
				} else {
					// Trigger event to update Grid
					Forminator.Events.trigger( 'dnd:models:updated' );
				}

				//select the field
				Forminator.Events.trigger( "forminator:dnd:field:select", this.model.cid );
				//open setting
				this.activate_field();
			},

			activate_field: function () {
				//open setting on new field / old field that moved
				Forminator.Events.trigger( "forminator:sidebar:open:settings", this.model );
			},

			is_shadow: function () {
				// Check if new field
				return ( typeof this.$self.data( 'shadow' ) !== "undefined" );
			},

			update_old_wrapper: function ( wrapper_fields ) {
				var wrappers = this.layout.get( 'wrappers' );

				// Delete old wrapper if empty
				if( ! wrapper_fields.length ) {
					wrappers.remove( this.wrapper );
				} else {
					// Update fields cols in wrapper
					this.update_cols( wrapper_fields );
				}
			},

			update_cols: function ( fields ) {
				// Update field columns
				var remainingItemsCols = 12 / ( fields.length );
				fields.update_cols( remainingItemsCols );
			},

			update_wrapper_position: function ( drop_model, wrapper_model ) {
				// We don't have correct drop
				if( typeof this.drop.insert === "undefined" ) return;

				var wrappers = this.layout.get( 'wrappers' ),
					insertType = this.drop.insert[ 0 ]
				;

				var drop_index = wrappers.model_index( drop_model ),
					my_index = wrappers.model_index( wrapper_model )
				;

				// If latest drop point increase the index
				if( insertType === "after" ) {
					drop_index++;
				}

				// If wrapper index smaller than drop index we need to put it before
				if( my_index < drop_index ) {
					drop_index--;
				}

				// Move new wrapper to correct place
				wrappers.move_to( drop_index, my_index );
			},

			show_debug_data: function () {
				// If enabled drop points will be displayed
				var self = this;

				_.each( this.drops, function( each ) {
					var $view = $('<div class="forminator-drop-view"><span class="forminator-drop-view-pos"></span></div>');

					$view.css({
						top: each.top,
						left: each.left,
						width: ( each.right - each.left ),
						height: ( each.bottom - each.top )
					});

					self.$main.append($view);
				});
			}

		}

		return DragDrop;
	});
})( jQuery );
