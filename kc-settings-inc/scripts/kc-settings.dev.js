var win = window.dialogArguments || opener || parent || top,
    h5_details = false;

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
	var $body     = $('body'),
	    $builder  = $('#kcsb'),
	    $kcsbForm = $('form.kcsb'),
	    $kcForm   = $('#kc-settings-form');

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
		}
	};

	/* <details /> polyfill */
	if ( $.fn.details.support ) {
		$('html').addClass( 'details' );
		h5_details = true;
	}
	else {
		$('html').addClass( 'no-details' );
		$('details').details();
	}

	/*** Plugin/theme settings ***/
	if ( $kcForm.length ) {
		var	$mBoxRoot = $kcForm.find('div.metabox-holder');

		/* Theme/plugin settings page with metaboxes */
		if ( $mBoxRoot.length ) {
			var mBoxPrefix = $mBoxRoot.attr('id'),
			    $checks    = $kcForm.find(':checkbox');

			/* Component metabox toggler */
			if ( $checks.length  ) {
				var $secTogglers = $();

				$checks.each(function() {
					var $sectBox = $( '#'+mBoxPrefix+'-'+this.value );
					if ( !$sectBox.length )
						return;

					var $check  = $(this),
					    $target = $('#'+mBoxPrefix+'-'+this.value+'-hide');

					$check.data( 'sectHider', $target ).data( 'sectBox', $sectBox );
					if ( !(this.checked === $target[0].checked) ) {
						$target.prop('checked', this.checked).triggerHandler('click');
					}

					$secTogglers = $secTogglers.add( $check );
				});

				if ( $secTogglers.length ) {
					$secTogglers.change(function() {
						var $el = $(this);
						$el.data('sectHider').prop('checked', this.checked).triggerHandler('click');

						// Scroll to
						if ( this.checked )
							$el.data('sectBox').kcGoto( {offset: -40, speed: 'slow'} );
					});
				}
			}
		}
	}


	// Sort
	$('ul.kc-rows').sortable( args.sortable );

	// Remove row
	$body.on('click', '.row a.del', function(e) {
		e.preventDefault();

		var $item = $(this).closest('.row');
		if ( !$item.siblings('.row').length )
			return false;

		var $block = $item.parent(),
		    mode   = $item.data('mode'),
		    isLast = $item.is(':last-child');

		$item.addClass('removing').fadeOut('slow', function() {
			$item.remove();

			// Reassign the input names and section/field numbering
			if ( !isLast ) {
				$block.kcReorder( mode, true );

				if ( $kcsbForm.length ) {
					$block.children().each(function() {
						$('> details > summary > .actions .count', this).text( $(this).index() + 1);
					});
				}
			}
		});
	});


	// Add row
	$body.on('click', '.row a.add', function(e) {
		e.preventDefault();

		var $el    = $(this),
		    $item  = $el.closest('.row'),
		    $block = $item.parent(),
		    mode   = $item.data('mode'),
		    isLast = $item.is(':last-child'),
		    $nu    = $item.clone(false).addClass('adding'),
		    scroll = false,
		    speed  = 400;

		if ( mode == 'sections' ) {
			scroll = true;
			speed  = 1200;
		}
		else if ( mode == 'fields' ) {
			scroll = true;
			speed  = 800;
		}

		// Builder fields
		if ( $kcsbForm.length ) {
			$nu.find('.kc-rows').each(function() {
				$(this).children('.row').not(':first').remove();
			});

			$nu.find(':input').each(function() {
				var $input = $(this);
				if ( this.type == 'text' || this.type == 'textarea' )
					$input.removeAttr('style').val('');
				else if ( this.type == 'checkbox' || this.type == 'radio' )
					$input.prop('checked', this.checked);

				if ( $input.is('.kcsb-ids') )
					$input.kcsbUnique();
			});
		}

		// Settings page (multiinput)
		else {
			$nu.find(':input').each(function() {
				var $input = $(this);
				if ( $input.data('nocleanup') !== true )
					$input.val('');
			});
		}

		$('ul.kc-rows').sortable( args.sortable );
		$('.hasdep', $nu).kcFormDep();
		var $details = $('details', $nu).details();
		if ( !h5_details )
			$details.children().not('summary').hide();

		$item.after( $nu );

		// Scroll to
		if ( scroll )
			$nu.kcGoto( {offset: -100, speed: speed});

		// Remove (bg) colors
		setTimeout(function() {
			$nu.removeClass('adding');
		}, speed);

		$block.kcReorder( mode, true );

		if ( $kcsbForm.length ) {
			if ( isLast ) {
				$('> details > summary > .actions .count', $nu).text( $nu.index() + 1);
			}
			else {
				$block.children().each(function() {
					$('> details > summary > .actions .count', this).text( $(this).index() + 1);
				});
			}
		}

		if ( !h5_details )
			$details.first().not('.open').stop().children('summary').trigger('click');
	});


	// Clear
	$body.on('click', '.row a.clear', function(e) {
		e.preventDefault();
		$(this).closest('.row').find(':input').val('');
	});


	// Datepicker
	var $dateInputs = $('input[type=date]');
	if ( $dateInputs.length && Modernizr.inputtypes.date === false ) {
		var jquiTheme = $('body').is('.admin-color-classic') ? 'cupertino' : 'flick';
		Modernizr.load([{
			load: win.kcSettings.paths.styles+'/jquery-ui/'+jquiTheme+'/style.css',
			complete: function() {
				$dateInputs.datepicker({
					dateFormat: 'yy-mm-dd'
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
				$colorInputs.ColorPicker({
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
				}).each(function() {
					var $el = $(this);
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


	// Tabs
	$('.kcs-tabs').kcTabs();


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

	/**** Builder ****/
	if ( $builder.length ) {
		// Scroll to form
		if ( !$builder.is('.hidden') )
			$builder.kcGoto();

		// Field deps
		$('.hasdep', $builder).kcFormDep();

		// Check 'slug/id' fields
		$body.on('blur', 'input.kcsb-slug', function() {
			var $input = $(this);
			$input.val( kcsbSlug( $input.val() ) );
		});

		$('input.kcsb-ids').kcsbUnique();

		$body.on('blur', 'input.required, input.clone-id', function() {
			$(this).kcsbCheck();
		});


		// Show form
		$('#new-kcsb').on('click', function(e) {
			e.preventDefault();
			$builder.kcGoto();
		});


		$('a.kcsb-cancel').on('click', function(e) {
			e.preventDefault();
			$('#kcsb').slideUp('slow');
		});


		// Setting clone
		$('a.clone-open').on('click', function(e) {
			e.preventDefault();
			$(this).parent().children().hide().filter('div.kcsb-clone').fadeIn(function() {
				$(this).find('input.clone-id').focus();
			});
		});


		$('a.clone-do').on('click', function(e) {
			var $el    = $(this),
			    $input = $(this).siblings('input');

			if ( $input.kcsbCheck() === false )
				return false;

			$el.attr( 'href', $el.attr('href')+'&new='+$input.val() );
		});


		$('input.clone-id').on('keypress', function(e) {
			var key = e.keyCode || e.which;
			if ( key === 13 ) {
				e.preventDefault();
				$(this).blur().siblings('a.clone-do').click();
			}
		});


		$('.kcsb-tools a.close').on('click', function(e) {
			e.preventDefault();
			var $el = $(this);

			$el.siblings('input').val('');
			$el.parent().fadeOut(function() {
				$(this).siblings().show();
			});
		});


		$kcsbForm.submit(function(e) {
			var isOK = true;

			$(this).find('input.required').not(':disabled').each(function() {
				if ( $(this).kcsbCheck() === false ) {
					isOK = false;
					return false;
				}
			});

			if ( !isOK )
				return false;
		});
	}

});
