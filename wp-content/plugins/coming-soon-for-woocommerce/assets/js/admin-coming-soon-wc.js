

jQuery(document).ready(function($) {

	// color selector
	if ( $('.coming-soon-wc-color-picker').wpColorPicker ) {
		$('.coming-soon-wc-color-picker').wpColorPicker({
			change: function (event, ui) {
				setTimeout(function () { reactive_update(); }, 1);
			}
		});
	}
	
	$('#coming_soon_wc_styles').show();

	// Badge loop style
	refresh_badge_loop_style = function () {
		
		badge_style = $('input[name="badge_loop_style"]:checked').val();
		
		if (badge_style == 'circle-text' || badge_style == 'square-text' || badge_style == 'line-text') {
			$('#coming_soon_wc_text').show();
			$('#coming_soon_wc_styles .coming_soon_wc_only_image').hide();
			$('#coming_soon_wc_styles .coming_soon_wc_only_text').show();
			$('#coming_soon_wc_loop_preview .coming-soon-wc-img-preview').hide();
			$('#coming_soon_wc_loop_preview .coming-soon-wc-preview').show();
		} else if (badge_style == 'off') {
			$('#coming_soon_wc_text, #coming_soon_wc_loop_preview, #coming_soon_wc_styles, #coming-soon-wc_loop_custom_css, #coming-soon-wc_loop_custom_image').hide();
			$('#coming_soon_wc_loop_preview').hide();
			return;
		} else {
			$('#coming_soon_wc_text').hide();
			$('#coming_soon_wc_styles .coming_soon_wc_only_image').show();
			$('#coming_soon_wc_styles .coming_soon_wc_only_text').hide();
			$('#coming_soon_wc_loop_preview .coming-soon-wc-img-preview').show();
			$('#coming_soon_wc_loop_preview .coming-soon-wc-preview').hide();

			$url = '';
			if (badge_style == 'custom-image') {
				$url = $('#coming_soon_wc_img_loop_preview img').attr('src');
			} else {
				cont = $('#coming_soon_wc_loop_icon_style input:checked').closest('.col');
				$url = $('img', cont).attr('src');
			}
			$('#coming_soon_wc_loop_preview .coming-soon-wc-img-preview').css('background-image', 'url(' + $url + ')');
		}
		if (badge_style == 'custom-image') {
			$('#coming-soon-wc_loop_custom_image').show();
		} else {
			$('#coming-soon-wc_loop_custom_image').hide();
		}
		$('#coming-soon-wc_loop_custom_css').show();
		reactive_update();
	};

	// Reactive preview update
	reactive_update = function () {

		preview_item = $('#coming_soon_wc_loop_preview .coming-soon-wc-preview');
		preview_item_img = $('#coming_soon_wc_loop_preview .coming-soon-wc-img-preview');
		
		if ( preview_item.length == 0 || preview_item_img == 0 ) return;

		// custom css?
		if ( $( 'input[name="coming-soon-wc_custom_css"]').is(':checked') ) {
			$('#coming_soon_wc_styles').hide();
			$('#coming_soon_wc_loop_preview').hide();
			$('#coming-soon-wc_css_helper').show();
			return;
		} else {
			$('#coming_soon_wc_styles').show();
			$('#coming_soon_wc_loop_preview').show();
			$('#coming-soon-wc_css_helper').hide();
		}

		$('#coming_soon_wc_styles .hide_' + badge_style).hide();
		$('#coming_soon_wc_styles .show_' + badge_style).show();

		badge_style = $('input[name="badge_loop_style"]:checked').val();

		// Text preview
		preview_item.html( $('input[name="coming-soon-wc_text"]').val() );

		// Direct properties
		props = [ 'font-size', 'font-weight', 'color', 'background', 'width', 'height', 'padding-top', 'padding-bottom','padding-left','padding-bottom', 'border-radius' ];
		for (i=0; i<props.length; i++) {

			field = $('input[name="coming-soon-wc_'+props[i]+'"]')
			units = field.attr('data-unit'); if (typeof units == 'undefined') units = '';

			preview_item.css( props[i], field.val() + units );
		}

		// Margin are the value of selected position
		preview_item.css( { left: 'auto', right: 'auto', top: 'auto', bottom: 'auto', marginLeft: 'auto', marginTop: 'auto' } );

		align_hor = $('select[name="coming-soon-wc_align-hor"]').val();
		if (align_hor == 'center') {
			preview_item.css( {left: '50%', marginLeft: '-' + Math.floor( $('input[name="coming-soon-wc_width"]').val() / 2 ) + 'px' } );
		} else {
			preview_item.css( align_hor, $('input[name="coming-soon-wc_margin-hor"]').val() + 'px' );
		}
			
		align_ver = $('select[name="coming-soon-wc_align-ver"]').val();
		if (align_ver == 'middle') {
			if ( badge_style == 'line-text' ) {
				$(preview_item).css('height', '');
				// Unknown height element, must be vertically centered now through JavaScript
				preview_item.css( {top: '50%', marginTop: '-' + Math.floor( $(preview_item).innerHeight() / 2 ) + 'px' } );
			} else {
				preview_item.css( {top: '50%', marginTop: '-' + Math.floor( $('input[name="coming-soon-wc_height"]').val() / 2 ) + 'px' } );
			}
		} else {
			preview_item.css( align_ver, $('input[name="coming-soon-wc_margin-ver"]').val() + 'px' );
		}

		// Background position: middle is not allowed, also center for vertical
		align_ver_bg = align_ver == 'middle' ? 'center' : align_ver;
		preview_item_img.css( 'background-position', align_hor + ' ' + align_ver_bg );

		// border radius 50% for circle 
		if (badge_style == 'circle-text') {
			preview_item.css( 'border-radius', '50%' );
		}

		// line_text has no height
		if (badge_style == 'line-text') {
			preview_item.css( { height: 'auto' } );
		}
		
		// Background size CSS
		val = $('select[name="coming-soon-wc_background-size"]').val();
		if ( !isNaN ( parseInt(val, 10) ) ) val = val + '%';
		preview_item_img.css( 'background-size', val );

		// Margins fields only visible for text badges not centered
		$('.coming-soon-wc_margin_hor_field').hide();
		$('.coming-soon-wc_margin_ver_field').hide();

		if (badge_style == 'circle-text' || badge_style == 'square-text' || badge_style == 'line-text') {
			if ( $('select[name="coming-soon-wc_align-hor"]').val() != 'center' ) $('.coming-soon-wc_margin_hor_field').show();
			if ( $('select[name="coming-soon-wc_align-ver"]').val() != 'middle' ) $('.coming-soon-wc_margin_ver_field').show();
		}
		

	};

	$('#coming_soon_wc_loop_icon_style .badge_sel_preview').click(function () {
		cont = $(this).parent();
		$('input', cont).prop("checked", true);
		refresh_badge_loop_style();
	});

	$('#coming_soon_wc_loop_icon_style input, input[name="coming-soon-wc_custom_css"]').click(function() {
		refresh_badge_loop_style();
	});

	$('#coming_soon_wc_text input, #coming_soon_wc_styles input, #coming_soon_wc_styles select').on ('keyup change', function () {
		reactive_update();
	});

	refresh_badge_loop_style();


	// on upload button click
	$('#coming_soon_wc_img_upload_preview').click( function (e) {
 
		e.preventDefault();
 
		var button = $(this),
		custom_uploader = wp.media({
			title: 'Insert image',
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type : 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: false
		}).on('select', function() { // it also has "open" and "close" events
			var attachment = custom_uploader.state().get('selection').first().toJSON();
			$('#coming_soon_wc_img_loop_preview').html('<img src="' + attachment.url + '">');
			$('input[name="coming-soon-wc_custom-img"]').val(attachment.id);
			$('#coming_soon_wc_img_remove_preview').show();
		}).open();
 
	});
 
	// on remove button click
	$('#coming_soon_wc_img_remove_preview').click ( function(e) {
 
		e.preventDefault();
 
		$('#coming_soon_wc_img_loop_preview').html('');
		$('#coming_soon_wc_img_remove_preview').hide();
		$('input[name="coming-soon-wc_custom-img"]').val('');
	});

	// reset values
	$('#coming-soon-wc_reset_loop').click( function (e) {
	
		e.preventDefault();

		if ( confirm( 'Your customisation values on loop will be reseted to defaults') ) {
			$('#coming_soon_wc_styles input').each (function (index, el) {
				attr = $(el).attr('placeholder');
				if (typeof attr !== typeof undefined && attr !== false) {
					$(el).val( attr );
				}
			});
			$('#coming_soon_wc_styles select').each (function (index, el) {
				$(el).val($("option:first", el).val());
			});
			$('.coming-soon-wc-color-picker').each (function (index, el) {
				$(el).wpColorPicker( 'color', $(el).attr('data-default') );
			});

		}
	});

});