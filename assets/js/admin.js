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

		return action_settings;
	}
}

/**
 * Handle Bulk Actions client-side for bulk actions dropdown element
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
		var action_settings = container.find( 'wpba-settings-' + action );

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
	} ).trigger( 'change' );
}

jQuery( document ).ready( function( $ ) {
	wpba_handleBulkActions( $( '#bulk-action-selector-top' ) );
	wpba_handleBulkActions( $( '#bulk-action-selector-bottom' ) );
} );