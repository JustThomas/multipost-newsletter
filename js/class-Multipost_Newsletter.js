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

function numsort( a, b ) {
  return b - a;
}

( function( $ ) {
	var multipost_newsletter = {
		init : function () {
			
			$( '.chzn-select' ).chosen();
			
			// Changing the Menu
			$( '#menu-posts' ).removeClass( 'wp-has-current-submenu wp-menu-open' );
			$( '.toplevel_page_mpnl_options' ).addClass( 'wp-has-current-submenu wp-menu-open' ).removeClass( 'wp-not-current-submenu' );
			$( '#toplevel_page_mpnl_options' ).addClass( 'wp-has-current-submenu wp-menu-open' ).removeClass( 'wp-not-current-submenu' );
			
			// Handlediv
			$( '.handlediv' ).live( 'click', function() {
				$( this ).next().next().slideToggle( 'fast' );
			} );
			
			// Remove Post from Edition
			$( '.remove-post-from-edition' ).live( 'click', function() {
				
				var post_id = $( this ).attr( 'post' );
				var post_vars = {
					action: 'remove_post_from_edition',
					edition: $( this ).attr( 'edition' ),
					letter_slug: $( '#letter_slug' ).val(),
					post_id: post_id,
				};
				
				$.post( ajaxurl, post_vars, function( response ) {
					var jresponse = $.parseJSON( response );
					$( '#text-preview' ).html( jresponse.text );
					$( '#html-preview' ).html( jresponse.html );
					
					$( '#' + post_id ).slideUp( 'fast' ).remove();
				} );
				
				return false;
			} );

			// Add Spacer
			$( '#add_spacer' ).live( 'click', function() {
				
				var last_ids = new Array();
				$.each( $( '#posts' ).children( '.stuffbox' ), function( index, element ) {
					var new_id = last_ids.push( parseInt( $( element ).attr( 'id' ) ) );
				} );
				
				last_ids.sort( numsort );
				var last_id = last_ids[0];
				var new_id = last_id + 1;
				
				var post_vars = {
					action: 'get_spacer_code',
					edition: $( '#edition' ).val(),
					letter_slug: $( '#letter_slug' ).val(),
					spacer: $( '#spacer_input' ).val(),
					id: new_id
				};
				
				$.post( ajaxurl, post_vars, function( response ) {
					$( '#posts' ).prepend( response ).children( $( '#' + new_id ) ).slideDown( 'fast' );
				} );
				
				return false;
			} );
			
			// Save Spacer
			$( '#save_spacer' ).live( 'click', function() {
				
				var id = $( this ).attr( 'href' );
				
				if ( undefined != $( '#show_in_contents_' + id ).attr( 'checked' ) )
					var show_in_contents = 'on';
				else
					var show_in_contents = 'off';
				
				if ( undefined != $( '#dont_show_in_pdf_' + id ).attr( 'checked' ) )
					var dont_show_in_pdf = 'on';
				else
					var dont_show_in_pdf = 'off';
				
				var post_vars = {
					action: 'save_spacer',
					edition: $( '#edition' ).val(),
					letter_slug: $( '#letter_slug' ).val(),
					content_html: $( '#html_' + id ).val(),
					content_text: $( '#text_' + id ).val(),
					show_in_contents: show_in_contents,
					dont_show_in_pdf: dont_show_in_pdf,
					id: id
				};
				
				$.post( ajaxurl, post_vars, function( response ) {
					var jresponse = $.parseJSON( response );
					$( '#' + id ).children( '.inside' ).slideUp( 'fast' );
					$( '#text-preview' ).html( jresponse.text );
					$( '#html-preview' ).html( jresponse.html );
				} );
				
				return false;
			} );
			
			// Remove Spacer
			$( '#remove_spacer' ).live( 'click', function() {
				var id = $( this ).attr( 'href' );
				var post_vars = {
					action: 'remove_spacer',
					edition: $( '#edition' ).val(),
					letter_slug: $( '#letter_slug' ).val(),
					id: id
				};
				
				$.post( ajaxurl, post_vars, function( response ) {
					var jresponse = $.parseJSON( response );
					$( '#' + id ).slideUp( 'fast' ).remove();
					$( '#text-preview' ).html( jresponse.text );
					$( '#html-preview' ).html( jresponse.html );
				} );
				
				return false;
			} );
			
			// Save Post Custom
			$( '#save_post' ).live( 'click', function() {
				var id = $( this ).attr( 'href' );
				
				if ( undefined != $( '#donot_show_title_' + id ).attr( 'checked' ) )
					var dont_show_title = 'on';
				else
					var dont_show_title = 'off';
				
				if ( undefined != $( '#show_post_thumbnail_' + id ).attr( 'checked' ) )
					var show_post_thumbnail = 'on';
				else
					var show_post_thumbnail = 'off';
				
				if ( undefined != $( '#show_content_' + id + '_content' ).attr( 'checked' ) )
					var content = 'full';
				else if ( undefined != $( '#show_content_' + id + '_excerpt' ).attr( 'checked' ) )
					var content = 'excerpt';
				else if ( undefined != $( '#show_content_' + id + '_link' ).attr( 'checked' ) )
					var content = 'link';
				
				if ( undefined != $( '#dont_show_in_pdf_' + id ).attr( 'checked' ) )
					var dont_show_in_pdf = 'on';
				else
					var dont_show_in_pdf = 'off';
				
				if ( undefined != $( '#show_pdf_content_' + id + '_content' ).attr( 'checked' ) )
					var show_pdf_content = 'full';
				else if ( undefined != $( '#show_pdf_content_' + id + '_excerpt' ).attr( 'checked' ) )
					var show_pdf_content = 'excerpt';
				
				var post_vars = {
					action: 'save_post_settings',
					id: id,
					edition: $( '#edition' ).val(),
					letter_slug: $( '#letter_slug' ).val(),
					donot_show_title: dont_show_title,
					show_post_thumbnail: show_post_thumbnail,
					dont_show_in_pdf: dont_show_in_pdf,
					show_pdf_content: show_pdf_content,
					content: content
				};
				
				$.post( ajaxurl, post_vars, function( response ) {
					var jresponse = $.parseJSON( response );
					$( '#text-preview' ).html( jresponse.text );
					$( '#html-preview' ).html( jresponse.html );
					$( '#' + id ).children( '.inside' ).slideUp();
				} );
				
				return false;
			} );
			
			// Sortables
			$( '.sortable-holder' ).sortable( {
				placeholder: 'sortable-placeholder',
				connectWith: '.post-sortable',
				items: '.stuffbox',
				handle: '.hndle',
				cursor: 'move',
				distance: 2,
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				helper: 'clone',
				opacity: 0.65,
				start: function( e, ui ) {
					$( 'body' ).css( {
						WebkitUserSelect: 'none',
						KhtmlUserSelect: 'none'
					} );
				},
				stop: function( e, ui ) {
					multipost_newsletter.save_post_order();
					$( 'body' ).css( {
						WebkitUserSelect: '',
						KhtmlUserSelect: ''
					} );
				},
			} );
		},
		
		start_newsletter: function() {
			$( '#send_newsletter' ).live( 'click', function() {
				multipost_newsletter.send_newsletter( 0 );
				return false;
			} );
		},
		
		send_newsletter: function( offset ) {
			
			var post_vars = {
				send_newsletter: 'send_newsletter',
				action: 'send_newsletter',
				edition: $( '#edition' ).val(),
				subject_area: $( '#subject_area' ).val(),
				letter_slug: $( '#letter_slug' ).val(),
				groups: $( '#groups' ).val(),
				api: $( '#api' ).val(),
				offset: offset
			};
			
			// Recoursive on Success!
			$.post( ajaxurl, post_vars, function( response ) {
				
				var jresponse = $.parseJSON( response );
					
				// Hide prev spinner
				prev_offset = offset - 1;
				$( '#spinner_' + prev_offset ).hide();
				
				if ( '1' == response ) {
					$( '.spinner' ).hide();
					$( '#send_newsletter_box_response' ).append( '<p>' + multipost_newsletter_vars.all_done + '</p>' );
				} else if ( '0' == response ) {
					$( '#send_newsletter_box_response' ).append( '<div class="error"><p>' + multipost_newsletter_vars.unknown_error + '</p></div>' );
				} else {
					jresponse.offset = parseInt( jresponse.offset );
					jresponse.found = parseInt( jresponse.found );
					jresponse.offset = jresponse.offset + 25;
					
					// For the output
					if ( offset == 0 )
						output_offset = 1;
					else
						output_offset = jresponse.offset;
					
					if ( output_offset >= jresponse.found )
						output_offset = jresponse.found;
					
					if ( 25 >= jresponse.found )
						output_offset = jresponse.found;
					
					$( '#send_newsletter_box_response' ).append( '<h4>' + multipost_newsletter_vars.send_newsletter_to + output_offset + multipost_newsletter_vars.send_newsletter_to_of + jresponse.found + multipost_newsletter_vars.recipients + '<img src="' + multipost_newsletter_vars.wp_spin + '" id="spinner_' + offset + '" style="display: inline;" class="spinner" /></h4>' );
					multipost_newsletter.send_newsletter( jresponse.offset );
				}
			} );
		},
	
		save_post_order : function () {
			
			var post_vars, page_columns = $( '.columns-prefs input:checked' ).val() || 0;
			post_vars = {
				edition: $( '#edition' ).val(),
				letter_slug: $( '#letter_slug' ).val(),
				action: 'save_post_order'
			};
			$( '.sortable-holder' ).each( function() {
				post_vars['order[' + this.id.split( '-' )[0] + ']'] = $( this ).sortable( 'toArray' ).join( ',' );
			} );
			$.post( ajaxurl, post_vars, function( response ) {
				var jresponse = $.parseJSON( response );
				$( '#text-preview' ).html( jresponse.text );
				$( '#html-preview' ).html( jresponse.html );
			} );
		},
		
		switch_preview : function () {
			
			$( '#link_html_preview' ).live( 'click', function() {
				$( '#link_html_preview' ).addClass( 'nav-tab-active' );
				$( '#link_text_preview' ).removeClass( 'nav-tab-active' );
				$( '#link_pdf_preview' ).removeClass( 'nav-tab-active' );
				$( '#pdf-preview' ).css( 'display', 'none' );
				$( '#text-preview' ).css( 'display', 'none' );
				$( '#html-preview' ).css( 'display', 'block' );
				
				return false;
			} );
			
			$( '#link_text_preview' ).live( 'click', function() {
				$( '#link_html_preview' ).removeClass( 'nav-tab-active' );
				$( '#link_text_preview' ).addClass( 'nav-tab-active' );
				$( '#link_pdf_preview' ).removeClass( 'nav-tab-active' );
				$( '#pdf-preview' ).css( 'display', 'none' );
				$( '#text-preview' ).css( 'display', 'block' );
				$( '#html-preview' ).css( 'display', 'none' );
				
				return false;
			} );
			
			$( '#link_pdf_preview' ).live( 'click', function() {
				$( '#link_html_preview' ).removeClass( 'nav-tab-active' );
				$( '#link_text_preview' ).removeClass( 'nav-tab-active' );
				$( '#link_pdf_preview' ).addClass( 'nav-tab-active' );
				$( '#pdf-preview' ).css( 'display', 'block' );
				$( '#text-preview' ).css( 'display', 'none' );
				$( '#html-preview' ).css( 'display', 'none' );
				
				if ( 'true' == multipost_newsletter_vars.is_pro ) {
					$.post( ajaxurl, { edition: $( '#edition' ).val(), letter_slug: $( '#letter_slug' ).val(), action: 'generate_pdf' }, function( response ) { $( '#pdf-preview' ).html( response ); } );
				}
				return false;
			} );
		}
	};
	$( document ).ready( function( $ ) {
		multipost_newsletter.init();
		multipost_newsletter.start_newsletter();
		multipost_newsletter.switch_preview();
	} );
} )( jQuery );