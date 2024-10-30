( function( $ ) {
	'use strict';

	function KAPOW() 
	{
		var self = {
			collapseSelection: function() {}
		};
		var settings  = {
			defaultWidth: 1000
		}
		var fn = {
			init: function()
			{
				$( document ).on( 'click', '.insert-kapow-media-button', fn.open_media_window );
			},
			
			open_media_window: function()
			{
				var text = '';
				var acfField = $( this ).closest( '.acf-field' );
				// get the target editor ID from the Add Media button
				var editorId = $( this ).siblings( 'button.insert-media' ).first().data( 'editor' );
				// get the editor wrapper which help us to detect in which mode we are
				var ewrap = $( `#wp-${editorId}-wrap` ).first();
				
				if ( ewrap.hasClass( 'tmce-active' ) )
				{
					// visual mode is active, try getting the selection...
					var editor = tinyMCE.get( editorId );
					text = editor.selection.getContent( { format: 'text' } );
					if( text === '' ) {
						// ...or the whole thing
						text = editor.getContent();
					}
					else {
						// create the appropriate text selection collapse function
						var collapse = function () {
							var _selection = editor.selection;
							return function () { _selection.collapse(); }
						}

						self.collapseSelection = collapse();
					}
				}
				if ( ewrap.hasClass( 'html-active' ) )
				{
					// text mode is active, try to extract the user-selected text
					var area = $( `textarea#${editorId}` );

					if ( area )
					{
						// once again try to get a selection first...
						var textarea = area[0];
						text = textarea.value.substring( textarea.selectionStart, textarea.selectionEnd );
						if( text === '' ) {
							// ...then the whole thing
							text = area.val();
						}
						else {
							// selection collapsing is a bit different for textareas...
							var collapse = function () {
								var _area = textarea;

								return function () { _area.setSelectionRange( _area.selectionEnd, _area.selectionEnd ) }
							}

							self.collapseSelection = collapse();
						}
					}
				}
				
				if ( acfField.length )
				{
					// if all else failed, process the acf field's entire content
					if ( text === '' )
					{
						var fieldKey = acfField.data( 'key' );
						text = acf.getField( fieldKey ).getValue();
					} 
				}
				// we create a new media frame because even with the same
				// payload kapow may return us new recommendations
				self.window = wp.media( {
					title: 'Insert Media from KAPOW',
					library: { type: 'kapow', text },
					multiple: false,
					button: { text: 'Insert' }
				} );

				self.window.on( 'select', function()
				{
					var img = self.window.state().get( 'selection' ).first().toJSON();
					// before inserting we must make sure we deselect any text
					// otherwise we will be making an unwanted replacement
					self.collapseSelection();
					
					wp.media.editor.insert( '[caption width="' + settings.defaultWidth + '"]<img width="' + settings.defaultWidth + '" src="' + img.url + '" alt="' + img.alt + '" />' + img.caption + '[/caption]' );

					$.post( ajaxurl, {
						'postdata': {
							'text': text,
							'url': img.url,
							'postid': $("#post_ID").val()
						},
						'action': 'kapow_register_feedback'
					} );
				} );

				self.window.on( 'ready', function () 
				{
					// hide the date filter for now
					$( 'select#media-attachment-date-filters' ).css( 'display', 'none' );
					// hide the "Upload Files" tab
					$( '.media-menu-item:contains("Upload Files")' ).css( 'display','none' );
					// ... in WP 5.8 ..
					$( '#menu-item-upload.media-menu-item' ).css( 'display', 'none' );
				} );

				self.window.open();
				return false;
			}
		}

		$( document ).ready( fn.init );
	}

	new KAPOW();

} )( jQuery );
