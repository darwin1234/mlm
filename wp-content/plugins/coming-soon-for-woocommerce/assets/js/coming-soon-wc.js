jQuery(document).ready(function($) {

	var cs_wrapper = $('.coming_soon_wc_product_wrapper');

	// We're on product page & there is coming soon badge?
	if ( cs_wrapper.length == 1 ) {
		
		var moved = false;

		$('.woocommerce-product-gallery__image, .woocommerce-product-gallery__image--placeholder').each( function (idx, cont) {
			
			// Target will be the focusable image (to zoom it)
			var target = $('a img', cont).first();

			// ...or fallback to the first image
			if (target.length < 1 ) target = $('img', cont).first();

			// Let's put badge inside gallery wrapper and focusable image inside badge wrapper
			if (target.length > 0) {
				var insert_on = $(target).parent();
				var new_wrapper = $(cs_wrapper).clone();
				$(new_wrapper).prependTo(insert_on);
				$(target).appendTo(new_wrapper);
			}
			moved = true;
		});
		
		if (moved) cs_wrapper.remove();
	}

	// On some themes the wrapper is outside link on category / loops
	$('.coming_soon_wc_loop_wrapper').each( function (idx, cont) {
		if ( $('a', cont).length > 0 ) {
			var img = $('a img:first', cont);
			if (img.length == 1) $('.coming_soon_img, .coming_soon_text', cont).insertBefore(img);
		}
	});
	
	// Vertical center for unknown height element
	$('.coming-soon-wc-js-middle').each ( function (idx, cont) {
		
		$('.coming_soon_text', cont).css('margin-top', -1 * Math.floor( $('.coming_soon_text', cont).innerHeight() / 2) );
	});
});