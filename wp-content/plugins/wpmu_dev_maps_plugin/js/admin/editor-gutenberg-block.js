/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global console:false*/
/*global wp:false*/
/*global document:false*/
/*global l10nEditor:false */
;(function ( undefined ) {

	var el = wp.element.createElement;

	function EditorButton( props ) {
		return el(
			'p',
			{ style: { textAlign: 'center' } },
			el(
				wp.components.Button,
				{ className: 'add_map components-button is-button is-default is-large' },
				l10nEditor.add_map
			)
		);
	}

	wp.blocks.registerBlockType( 'agm-google-maps/google-map-editor', {
		title: l10nEditor.google_maps,
		category: 'widgets',
		attributes: {
			marker: {
				type: 'string',
				source: 'html',
				selector: 'p',
			}
		},
		edit: function( me ) {
			var has_marker = !! me.attributes.marker;
			if ( ! has_marker ) {
				jQuery(document).one( 'agm-map-inserted', function( e, marker ) {
					me.setAttributes( { marker: marker } );
				});
			}
			return has_marker ? el( 'p', {}, me.attributes.marker ) : el(EditorButton);
		},
		save: function( me ) {
			return el( 'p', {}, me.attributes.marker );
		}
	} );
})();
