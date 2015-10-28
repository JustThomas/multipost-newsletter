/**
 * Feature Name:	Multipost Newsletter jQuery Class
 * Version:			0.1
 * Author:			Inpsyde GmbH
 * Author URI:		http://inpsyde.com
 * Licence:			GPLv3
 * 
 * Changelog
 *
 * 0.1
 * - Initial Commit
 */

( function( $ ) {
	var multipost_newsletter_frontend = {
		init : function () {
			$( '.chzn-select' ).chosen();
			
			$( '#already_registered' ).live( 'click', function() {
				$( '#register_form' ).slideUp().next().slideDown();
				return false;
			} );
			
			$( '#register_for_newsletter' ).live( 'click', function() {
				$( '#login_form' ).slideUp().prev().slideDown();
				return false;
			} );
		},
	};
	$( document ).ready( function( $ ) {
		multipost_newsletter_frontend.init();
	} );
} )( jQuery );