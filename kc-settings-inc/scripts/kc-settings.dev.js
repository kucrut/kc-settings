var win = window.dialogArguments || opener || parent || top;

(function($) {
	// File (multiple)
	win.kcFileMultiple = function( files ) {
		var $target = win.kcSettings.upload.target,
		    current = $target.data('currentFiles'),
		    $last   = $target.children().last(),
		    $items  = $(),
		    $nu     = null;

		for ( var item in files ) {
			if ( !files.hasOwnProperty(item) || $.inArray(files[item].id, current) > -1 )
				continue;

			$nu = $last.clone().removeClass('hidden');

			$nu.find('img').attr('src', files[item].img);
			$nu.find('input').val(files[item].id).prop('checked', false);
			$nu.find('.title').text(files[item].title);

			$items = $items.add( $nu );
		}

		$target.append( $items );
		if ( $last.is('.hidden') ) {
			$items.show();
			$last.remove();
		}

		$target.show().prev('.info').show();
	};

	// File (single)
	win.kcFileSingle = function( data ) {
		var $target = win.kcSettings.upload.target,
		    $title  = $target.find('span').text(data.title),
		    $img    = $target.find('img').attr('src', data.img);

		$target.removeAttr('data-type');
		$target.find('input').val(data.id);
		$target.children('a.up').hide();
		$target.find('p').fadeIn().children('a.up').show().siblings('a.rm').show();

		if ( data.type == 'image' ) {
			$target.attr('data-type', data.type);
			$title.hide();

			// Replace preview image
			var thumbSize = $target.data('size');
			if ( thumbSize !== 'thumbnail' ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: 'kc_get_image_url', id: data.id, size: thumbSize },
					success: function( response ) {
						if ( response ) {
							$img.attr('src', response);
						}
					}
				});
			}
		}
		else {
			$title.show();
		}
	}

})(jQuery);


jQuery(document).ready(function($) {
	var $doc    = $(this),
	    $body   = $('body');

	var args = {
		sortable : {
			axis: 'y',
			start: function(ev, ui) {
				ui.placeholder.height( ui.item.outerHeight() );
			},
			stop: function(ev, ui) {
				// Reassign input names
				ui.item
					.parent().kcReorder( ui.item.data('mode'), true )
					.children().each(function() {
						$('> details > summary > .actions .count', this).text( $(this).index() + 1);
					});
			}
		},
		colorpicker : {
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			},
			onSubmit: function(hsb, hex, rgb, el) {
				var clr = '#'+hex;
				$(el).css({
					backgroundColor: clr,
					color: invertColor( clr )
				})
					.val( clr )
					.ColorPickerHide();
			}
		},
		datepicker : {
			date : {
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true
			},
			month : {
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: 'yy-mm',
				onClose: function(dateText, inst) {
					var $div  = $(inst.dpDiv),
					    month = $div.find('.ui-datepicker-month :selected').val(),
					    year  = $div.find('.ui-datepicker-year :selected').val();

					$(this).datepicker('setDate', new Date(year, month, 1));
				}
			}
		}
	};

	/* Theme/plugin settings page with metaboxes */
	$('#kc-settings-form').kcMetaboxDeps();

	// Form row cloner
	$.kcRowCloner();
	$.kcRowCloner.addCallback( 'add', function( obj ) {
		$('ul.kc-rows').sortable( 'refresh' );
		$('.hasDatepicker', obj.nuItem).each(function() {
			$(this)
				.removeData('datepicker')
				.removeClass('hasDatepicker')
				.datepicker( args.datepicker[$(this).attr('type')] );
		})
		$('.hasColorpicker', obj.nuItem).each(function() {
			$(this)
				.removeData('colorpickerId')
				.removeAttr('style')
				.ColorPicker(args.colorpicker);
		});
	});

	// Sort
	$('ul.kc-rows').sortable( args.sortable );

	// Tabs
	$('.kcs-tabs').kcTabs();

	// Datepicker
	var $dateInputs = $('input[type=date], input[type=month]');
	if ( $dateInputs.length && Modernizr.inputtypes.date === false ) {
		var jquiTheme = $('body').is('.admin-color-classic') ? 'cupertino' : 'flick';
		Modernizr.load([{
			load: win.kcSettings.paths.styles+'/jquery-ui/'+jquiTheme+'/style.css',
			complete: function() {
				$dateInputs.each(function() {
					$(this).datepicker( args.datepicker[$(this).attr('type')] );
				});
			}
		}]);
	}

	// Color
	var $colorInputs = $('input[type=color]');
	if ( $colorInputs.length && Modernizr.inputtypes.color === false ) {
		Modernizr.load([{
			load: [
				win.kcSettings.paths.scripts+'/colorpicker/js/colorpicker.js',
				win.kcSettings.paths.scripts+'/colorpicker/css/colorpicker.css',
				win.kcSettings.paths.scripts+'/rgbcolor.js'
			],
			complete: function () {
				$colorInputs.ColorPicker(args.colorpicker)
				.each(function() {
					var $el = $(this).addClass('hasColorpicker');
					if ( $el.val() !== '' )
						$el.css({
							backgroundColor: this.value,
							color: invertColor( this.value )
						});
				});
			}
		}]);
	}


	// File
	$body.on('click', '.kcs-file a.rm', function(e) {
		e.preventDefault();
		var $item = $(this).closest('.row');

		$item.addClass('removing').fadeOut('slow', function() {
			// am I the only one?
			if ( $item.siblings().length ) {
				$item.remove();
			}
			// No?
			else {
				$item.removeClass('removing')
					.addClass('hidden')
					.find(':input')
						.val('')
						.prop('checked', false);

				// Disable the field so it won't get saved upon submission
				$('input.fileID', $item).prop('disabled', true);

				// Hide the list and info
				$item.parent().hide().prev('.info').hide();
			}
		});

	});


	// Add files button
	$body.on('click', 'a.kcsf-upload', function(e) {
		e.preventDefault();
		var $el     = $(this),
		    $target = $el.siblings('.kc-rows'),
		    $solo   = $target.find('.row.hidden'),
				current = [];

		// If there's currently only one row and it's hidden, enable the field
		if ( $solo.length ) {
			$('input.fileID', $solo).prop('disabled', false);
		}
		else {
			$('input.fileID', $target).each(function() {
				current.push( this.value );
			});
		}

		win.kcSettings.upload.target = $target.data('currentFiles', current);
		tb_show( '', $el.attr('href') );
	});


	// Single file: remove
	// Set height
	$body.on('click', '.kcs-file-single a.rm', function(e) {
		e.preventDefault();
		$(this).fadeOut()
			.closest('div')
				.find('p.current').fadeOut(function() {
					$(this).siblings('a.up').show()
						.siblings('input').val('');
				});
	});

	// Single file: open popup to select/upload files
	$body.on('click', '.kcs-file-single a.up', function(e) {
		e.preventDefault();
		var $el = $(this);

		win.kcSettings.upload.target = $el.closest('div');
		tb_show( '', $el.attr('href') );
	});


	// Help trigger
	$('a.kc-help-trigger').on('click', function(e) {
		e.preventDefault();

		$('#contextual-help-link').click();
		$('#screen-meta').kcGoto();
	});


	// Add term form
	var $addTagForm = $('#addtag');
	if ( $addTagForm.length ) {
		var $kcsFields = $();
		$('div.kcs-field').each(function() {
			$kcsFields = $kcsFields.add( $(this).clone() );
		});

		if ( $kcsFields.length ) {
			$addTagForm.ajaxComplete( function( e, xhr, settings ) {
				if ( settings.data.indexOf('action=add-tag') < 0 )
					return;

				$('div.kcs-field').each(function(idx) {
					$(this).replaceWith( $kcsFields.eq(idx).clone() );
				});

				$('.kcs-tabs', $addTagForm).kcTabs();
				$addTagForm.trigger('kcsRefreshed');
			});
		}
	}
});
