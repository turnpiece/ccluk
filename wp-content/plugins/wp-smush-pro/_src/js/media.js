/**
 * Adds a Smush Now button and displays stats in Media Attachment Details Screen
 */
(function ( $, _ ) {
	'use strict';

	// Local reference to the WordPress media namespace.
	const smush_media = wp.media,
		sharedTemplate = "<label class='setting smush-stats' data-setting='description'><span class='name'><%= label %></span><span class='value'><%= value %></span></label>";

	if ( 'undefined' !== typeof smush_media.view &&
		'undefined' !== typeof smush_media.view.Attachment.Details.TwoColumn ) {
		// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
		let smushMediaTwoColumn = smush_media.view.Attachment.Details.TwoColumn;

		/**
		 * Add Smush details to attachment.
		 *
		 * A similar view to media.view.Attachment.Details
		 * for use in the Edit Attachment modal.
		 *
		 * @see wp-includes/js/media-grid.js
		 */
		smush_media.view.Attachment.Details.TwoColumn = smushMediaTwoColumn.extend( {
			initialize: function () {
				this.listenTo( this.model, 'change:smush', this.render );
			},

			render: function () {
				// Ensure that the main attachment fields are rendered.
				smush_media.view.Attachment.prototype.render.apply( this, arguments );

				if ( typeof this.model.get( 'smush' ) === 'undefined' ) {
					return this;
				}

				let image = new wp.api.models.Media( {id: this.model.get( 'id' )} ),
					self  = this;

				image.fetch( {attribute: 'smush'} ).done( function ( img ) {
					if ( typeof img.smush !== 'object' ) {
						setTimeout( () => self.model.fetch(), 3000 );
					}
				});

				/**
				 * Detach the views, append our custom fields, make sure that our data is fully updated
				 * and re-render the updated view.
				 */
				this.views.detach();

				let detailsHtml = this.$el.find( '.settings' );

				// Create the template.
				let template = _.template( sharedTemplate );
				let html = template( {
					/**
					 * @var {array}  smush_vars.strings  Localization strings.
					 * @var {object} smush_vars          Object from wp_localize_script()
					 */
					label: smush_vars.strings['stats_label'],
					value: this.model.get( 'smush' )
				} );

				detailsHtml.append( html );
				this.model.fetch();
				this.views.render();

				return this;
			}
		} );
	}

	// Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
	let smushAttachmentDetails = smush_media.view.Attachment.Details;

	/**
	 * Add Smush details to attachment.
	 */
	smush_media.view.Attachment.Details = smushAttachmentDetails.extend( {
		initialize: function () {
			this.listenTo( this.model, 'change:smush', this.render );
		},

		render: function () {
			// Ensure that the main attachment fields are rendered.
			smush_media.view.Attachment.prototype.render.apply( this, arguments );

			if ( typeof this.model.get( 'smush' ) === 'undefined' ) {
				return this;
			}

			let image = new wp.api.models.Media( {id: this.model.get( 'id' )} ),
				self  = this;

			image.fetch( {attribute: 'smush'} ).done( function ( img ) {
				if ( typeof img.smush !== 'object' ) {
					setTimeout( () => self.model.fetch(), 3000 );
				}
			});

			/**
			 * Detach the views, append our custom fields, make sure that our data is fully updated
			 * and re-render the updated view.
			 */
			this.views.detach();

			let template = _.template( sharedTemplate );
			let html = template( {
				/**
				 * @var {object} smush_vars          Object from wp_localize_script()
				 * @var {array}  smush_vars.strings  Localization strings.
				 */
				label: smush_vars.strings['stats_label'],
				value: this.model.get( 'smush' )
			} );

			this.$el.append( html );

			return this;
		}
	} );

})( jQuery, _ );
