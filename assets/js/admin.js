function wpba_getActionTemplate( action ) {
	var element_id = 'wpba-template-' + action;
	var el_template = jQuery( '#' + element_id );

	if ( el_template.length ) {
		return el_template.html();
	}

	return '';
}

function wpba_createActionSettings( action ) {
	var template = wpba_getActionTemplate( action );

	if ( template ) {
		// Fetch template and wrap it
		var action_settings = jQuery( '<div class="wpba-action-settings wpba-settings-' + action + '" />' );
		action_settings.html( template );

		// Handle JavaScript hooks for the action settings
		action_settings.find( '.wpba-conditional' ).each( function() {
			var group = jQuery( this ).attr( 'data-wpba-conditional-group' );

			if ( group ) {
				var dependents = action_settings.find( '*[data-wpba-conditional-group="' + group + '"]' ).not( '.wpba-conditional' );
				dependents.hide();

				jQuery( this ).change( function() {
					dependents.hide();
					dependents.filter( '*[data-wpba-conditional-value="' + this.value + '"]' ).show();
				} );
			}
		} );

		// Handle additional callbacks for action settings
		action_settings.wpba_actionSettings_handleCallbacks( action );

		return action_settings;
	}
}

function wpba_getSmallestSquareImage( sizes ) {
	var smallest_image_size = -1;
	var smallest_image_square = false;
	var smallest_image_name = "";

	for ( var size in sizes ) {
		var details = sizes[ size ];

		if ( details.width === details.height ) {
			if ( ! smallest_image_square ) {
				smallest_image_size = details.width * details.height;
				smallest_image_square = true;
				smallest_image_name = size;
			}
			else if ( size.width * size.height < smallest_image_size ) {
				smallest_image_size = details.width * details.height;
				smallest_image_name = size;
			}
		}
		else if ( ! smallest_image_square && size.width * size.height < smallest_image_size ) {
			smallest_image_size = size.width * size.height;
			smallest_image_name = size;
		}
	}

	return smallest_image_name;
}

jQuery.fn.wpba_actionSettings_handleCallbacks = function( action ) {
	// Action: featured image
	if ( action === 'change-featured-image' ) {
		jQuery( this ).wpba_actionSettings_handleCallbacks_ChangeFeaturedImage();
	}
};

jQuery.fn.wpba_actionSettings_handleCallbacks_ChangeFeaturedImage = function() {
	var settings = jQuery( this );
	var frame;

	jQuery( this ).find( '.wpba-select-image' ).click( function() {
		if ( ! frame ) {
			frame = wp.media( {
				title: WPBA.i18n.change_featured_image_title
			} );

			frame.on( 'select', function() {
				// Get details of selected image
				var attachment = frame.state().get( 'selection' ).first().toJSON();

				// Change input for featured image ID in form
				settings.find( '.wpba-input' ).val( attachment.id );

				// Create image element
				var img = jQuery( '<img />' );
				img.attr( 'src', attachment.sizes[ wpba_getSmallestSquareImage( attachment.sizes ) ].url );

				// Show current image
				settings.find( '.wpba-current-image' ).html( '' ).append( img );
			} );
		}
		
		frame.open();
	} );
};

/**
 * Handle Bulk Actions client-side for bulk actions dropdown element
 *
 * @since 1.0
 *
 * @param object dropdown jQuery object for the bulk actions dropdown element
 */
function wpba_handleBulkActions( dropdown ) {
	// Container element
	var container = dropdown.parent();

	// Bulk actions additional settings element
	var settings = jQuery( '<div class="wpba-actions-settings" />' );
	dropdown.after( settings );

	// Add relevant settings if dropdown value is changed to a bulk action
	dropdown.change( function() {
		// Hide all additional action settings
		container.find( '.wpba-action-settings' ).hide();

		// Try to find existing settings element
		var action = this.value;
		var action_settings = container.find( '.wpba-settings-' + action );

		// Create settings element if it doesn't exist yet
		if ( ! action_settings.length ) {
			action_settings = wpba_createActionSettings( action );

			if ( action_settings ) {
				// Add the settings element to the settings box
				settings.append( action_settings );
			}
		}
		else {
			action_settings.show();
		}

		if ( action_settings ) {
			// Add temporary highlight to highlightable elements
			action_settings.find( '.highlightable' ).addClass( 'temporary-highlight' ).delay( 500 ).queue( function( next ) {
				jQuery( this ).removeClass( 'temporary-highlight' );
				next();
			} );

			// Focus on first input element
			action_settings.find( 'input, select' ).first().focus();
		}
	} ).trigger( 'change' );

	// Handle form submit
	dropdown.parents( '.bulkactions' ).find( ':submit' ).click( function() {
		jQuery( this ).parents( 'form' ).find( ':submit' ).removeClass( 'submitting' );
		jQuery( this ).addClass( 'submitting' );
	} );

	dropdown.parents( 'form' ).submit( function( e ) {
		var active_container = dropdown.parents( '.bulkactions' ).find( '.submitting' ).parents( '.bulkactions' ).find( '.wpba-action-settings:visible' );

		if ( active_container.length ) {
			var success = true;

			active_container.find( 'input.required, select.required' ).each( function() {
				if ( jQuery( this ).val() === '' ) {
					success = false;

					if ( jQuery( this ).is( '.highlightable' ) ) {
						jQuery( this ).addClass( 'highlight-error' );

						jQuery( this ).one( 'focus', function() {
							jQuery( this ).removeClass( 'highlight-error' );
						} );
					}
				}
			} );

			if ( ! success ) {
				e.preventDefault();
			}
		}
	} );
}

jQuery( document ).ready( function( $ ) {
	wpba_handleBulkActions( $( '#bulk-action-selector-top' ) );
	wpba_handleBulkActions( $( '#bulk-action-selector-bottom' ) );
} );
