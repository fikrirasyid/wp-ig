jQuery(document).ready(function($) {
	// Detecting js-encoded WP_Error response
	function is_wp_error( data ){
		if( typeof data.errors == "undefined" ){
			return false;
		} else {
			return true;
		}
	}

	// Loading shortcode Instagram contents
	if( $(".wp-ig-wrap").length > 0 ){
		$('.wp-ig-wrap').each(function(){
			var wrap = $(this);
			var wrap_source = wrap.attr('data-source');

			wrap.load( wrap_source + " .wp-ig-wrap-inside");
		});
	}

	// On shortcode, load content on the same wrapper
	$('.wp-ig-wrap').on( 'click', '.onpage', function(e){
		e.preventDefault();

		var link 	= $(this);
		var source 	= link.attr('href');
		var wrap 	= link.parents('.wp-ig-wrap');

		// Add loading state
		wrap.addClass('loading');

		// Load the new content
		wrap.load( source + " .wp-ig-wrap-inside", function(){
			wrap.removeClass('loading');
		});		
	});

	// Handling load more
	$('body').on( 'click', '.more-instagram-items', function(e){
		e.preventDefault();

		var button = $(this);
		var source = button.attr('href');
		var wrap = button.parents( ".wp-ig.instagram-items" );

		// If this is currently loading things, ignore any more click
		if( button.is( '.loading' ) )
			return;

		// Adding loading state
		button.addClass('loading');

		// Load the items
		$.get( source, function(data){

			// fade and remove load more
			button.remove();

			// Append the items
			$(data).find( ".wp-ig.instagram-items" ).children().appendTo( wrap );
		});
	} );

	// Import Item
	$('.wp-ig.instagram-items').on( 'click', '.item-not-posted.import-item', function(e){
		e.preventDefault();

		var link = $(this);
		var href = link.attr( 'href' ) + '&is_ajax=true';

		// Don't do anything while something is currently happened
		if( link.is('.importing') ){
			return;
		}

		// Add importing state
		link.addClass('importing').text( 'Importing...' );

		// Process request
		$.post( href, function( response ){

			// Parse response
			var data = $.parseJSON( response );

			// Process response
			if( is_wp_error( data ) ){
				alert( 'Error importing media. Please try again later' );

				link.removeClass('importing').text( 'Post This' );
			} else {

				link.removeClass('importing item-not-posted').addClass('item-posted').text('Posted').attr({ 'href' : data.permalink, 'target' : '_blank' });
			}
		});
	});
});