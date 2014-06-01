jQuery(document).ready(function($) { 
	// Handling load more
	$('.wp-ig.instagram-items').on( 'click', '.more-instagram-items', function(e){
		e.preventDefault();

		var button = $(this);
		var source = button.attr('href');

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
			$(data).find( ".wp-ig.instagram-items" ).children().appendTo( ".wp-ig.instagram-items" );
		});
	} );
});