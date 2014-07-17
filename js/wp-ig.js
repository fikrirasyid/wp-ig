jQuery(document).ready(function($) {
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
});