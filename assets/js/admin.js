function wpba_getActionTemplate( action ) {
	var element_id = 'wpba-template-' + action;
	var el_template = jQuery( '#' + element_id );

	if ( el_template.length ) {
		return el_template.html()
	}

	return '';
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
			var template = wpba_getActionTemplate( action );

			if ( template ) {
				action_settings = jQuery( '<div class="wpba-action-settings wpba-settings-' + action + '" />' );
				action_settings.html( template );
				settings.append( action_settings );
				action_settings.show();
			}
		}
		else {
			action_settings.show();
		}
	} );
}

jQuery( document ).ready( function( $ ) {
	wpba_handleBulkActions( $( '#bulk-action-selector-top' ) );
	wpba_handleBulkActions( $( '#bulk-action-selector-bottom' ) );
} );