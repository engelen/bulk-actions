jQuery( document ).ready( function( $ ) {
	// Trigger a click on WordPress' native "close" button for the notice when the inline close
	// button is clicked
	$( '#wpba-notice-feedback .hide' ).click( function( e ) {
		$( this ).parents( '.notice' ).find( '.notice-dismiss' ).trigger( 'click' );
		e.preventDefault();
	} );

	$( '#wpba-notice-feedback' ).on( 'click', '.notice-dismiss', function() {
		jQuery.post( ajaxurl, { 
			'action': 'wpba/dismiss_notice',
			'notice_id': 'feedback',
		} );
	} );
} );