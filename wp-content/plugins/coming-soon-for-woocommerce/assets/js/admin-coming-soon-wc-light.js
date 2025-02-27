

jQuery(document).ready(function($) {

	// Ajax for wizard / star / news buttons
	$(document).on('click', 'div.wc-coming-soon-wizard a', function () {

		// No AJAX action?
		if (typeof $(this).attr('data-ajax') === "undefined" ) return true;
		
		var return_value  = $(this).attr('target') === "_blank";
		var html_link     = $(this).attr('href');

		$.ajax({
			url: ajaxurl,
			data: { action: 'wc_coming_soon_wizard', ajax: $(this).attr('data-ajax'), key: $(this).attr('data-key'), param: $(this).attr('data-param') },
			error: function (xhr, status, error) {
				var errorMessage = xhr.status + ': ' + xhr.statusText
				console.log('Coming Soon for WC, AJAX error - ' + errorMessage);
				// fail? follow the link
				if (!return_value) location.href=html_link;
			},
			success: function (data) {
				if (data != '1') console.log('Coming Soon for WC, AJAX error - ' + data);
				// fail? follow the link
				if (data != '1' && !return_value) location.href=html_link;
			},
			dataType: 'html'
		});
		
		jQuery(this).closest('div.wc-coming-soon-wizard').slideUp(function () {
			jQuery(this).closest('div.wc-coming-soon-wizard').remove();
		});
		
		return return_value;
		//return false;
	});

});