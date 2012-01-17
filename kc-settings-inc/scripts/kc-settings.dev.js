var win = window.dialogArguments || opener || parent || top;

function kcCountObj( obj ) {
	var count = 0;
	for (var k in obj) {
		if ( obj.hasOwnProperty(k) ) {
			++count;
		}
	}
	return count;
}

function kcsbSlug( str ) {
	strNu = str.replace(/^\-+/, '');
	strNu = strNu.replace(/^_+/, '');
	strNu = strNu.replace(/[^A-Za-z0-9\-_]/g, '');

	if ( strNu.match(/^\-+/) || strNu.match(/^_+/) )
		strNu = kcsbSlug( strNu );

	return strNu;
}


function invertColor( color ) {
	inverted = new RGBColor(color);
	if ( inverted.ok ) {
		color = 'rgb(' + (255 - inverted.r) + ', ' + (255 - inverted.g) + ', ' + (255 - inverted.b) + ')';
	}

	return color;
}


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
		var target = win.kcSettings.upload.target,
		    $title = target.find('span').text(data.title);

		target.data('type', data.type)
			.find('input').val(data.id)
			.siblings('a').hide()
			.siblings('p').fadeIn()
				.find('img').attr('src', data.img);

		if ( data.type == 'image' ) {
			$title.hide();

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: { action: 'kc_get_image_url', id: data.id, size: target.data('size') },
				success: function( response ) {
					if ( response !== 0 ) {
						target.find('img').attr('src', response);
					}
				}
			});
		}
		else {
			$title.show();
		}
	}


	$.fn.kcGoto = function( opts ) {
		defaults = {
			offset: -20,
			speed: 800
		};
		opts = $.extend( {}, defaults, opts );

		return this.each(function() {
			var $target = $(this);

			$target.fadeIn(function() {
				$('html, body').stop().animate({
					scrollTop: ( $target.offset().top + opts.offset )
				}, opts.speed );
			});
		});
	};


	$.fn.kcsbUnique = function() {
		return this.each(function() {
			var $this	= $(this),
			    olVal	= $this.val();

			$this.data('olVal', olVal)
				.blur(function() {
					var $input = $(this),
					    olVal  = $this.data('olVal'),
					    nuVal  = $input.val();

					if ( nuVal != olVal && $.inArray(nuVal, kcSettings._ids[$input.data('ids')]) > -1 )
						$input.val('');
				});
		});
	};


	$.fn.kcsbCheck = function() {
		var $input = $(this);

		if ( ($input.attr('name') === 'kcsb[id]' && $input.val() === 'id') || $input.val() === '' ) {
			$input.val('').focus().css('borderColor', '#ff0000');
			return false;
		} else {
			$input.removeAttr('style');
		}
	};


	$.fn.kcFormDep = function( opts ) {
		var defaults = {
		      disable: true,
		      callback: function() {}
		    },
		    opts = $.extend({}, defaults, opts),
		    onChange = function( e ) {
					var $el = $(e.target),
							val = $el.val();

					$el.data('depTargets').each(function() {
						var $c    = $(this),
						    depon = $c.data('dep'),
						    show  = false;

						if ( !$el.prop('disabled') && ((typeof depon === 'string' && depon === val) || (typeof depon === 'object' && $.inArray(val, depon) > -1)) )
							show = true;

						$c.toggle( show );
						if ( opts.disable === true ) {
							$c.find(':input').prop('disabled', !show).trigger('change');
						}
					});
				};

		return this.each(function() {
			var $el      = $(this),
			    val      = $el.val(),
			    $targets = ( $el.data('scope') !== undefined ) ?
			                 $el.closest( $el.data('scope') ).find( $el.data('child') ) :
			                 $( $el.data('child') );

			if ( $targets.length )
				$el.data('depTargets', $targets)
					.change(onChange);
		});
	};


	$.fn.kcReorder = function( mode, all ) {
		var rgx1 = new RegExp(mode+'\\]\\[(\\d+)'),
		    rgx2 = new RegExp(mode+'\\-(\\d+)'),
		    $el  = $(this);

		if ( all === true ) {
			var $els = $el.children(),
			    i    = 0;
		} else {
			var $els = $el,
			    i    = $el.index();
		}

		$els.each(function() {
			var $x = $(this);
			$x.find(':input').each(function() {
				this.name = this.name.replace(rgx1, function(str, p1) {
					return mode + '][' + i;
				});

				if ( this.id !== '' ) {
					this.id = this.id.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					});
				}
			});

			$x.find('label').each(function() {
				var $label 	= $(this),
						atFor		= $label.attr('for');

				if ( atFor !== '' && atFor !== undefined ) {
					$label.attr( 'for', atFor.replace(rgx2, function(str, p1) {
						return mode + '-' + i;
					}) );
				}
			});

			i++;
		});

		return this;
	};
})(jQuery);


jQuery(document).ready(function($) {
	var $builder  = $('#kcsb'),
	    $kcsbForm = $('form.kcsb'),
	    $kcForm   = $('#kc-settings-form');


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
	$('ul.kc-rows').sortable({
		axis: 'y',
		start: function(ev, ui) {
			ui.placeholder.height( ui.item.outerHeight() );
		},
		stop: function(ev, ui) {
			// Reassign input names
			ui.item
				.parent().kcReorder( ui.item.data('mode'), true )
				.children().each(function() {
					$('> .actions .count', this).text( $(this).index() + 1);
				});
		}
	});


	// Remove row
	$('.row a.del').live('click', function(e) {
		var $this  = $(this),
		    $item  = $this.closest('.row'),
		    $block = $item.parent(),
		    mode   = $item.data('mode'),
		    isLast = $item.is(':last-child');

		if ( !$item.siblings('.row').length )
			return false;

		$item.addClass('removing').fadeOut('slow', function() {
			$item.remove();

			// Reassign the input names and section/field numbering
			if ( !isLast ) {
				$block.kcReorder( mode, true );

				if ( $kcsbForm.length ) {
					$block.children().each(function() {
						$('> .actions .count', this).text( $(this).index() + 1);
					});
				}
			}
		});

		return false;
	});


	// Add row
	$('.row a.add').live('click', function(e) {
		var $this  = $(this),
				$item  = $this.closest('.row'),
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
				var $kids = $(this).children('.row');

				if ( $kids.length > 1 ) {
					$kids.not(':first').remove();
				}
			});

			$nu.find(':input').each(function() {
				var $this = $(this);
				if ( this.type == 'text' || this.type == 'textarea' )
					$this.removeAttr('style').val('');
				else if ( this.type == 'checkbox' || this.type == 'radio' )
					$this.prop('checked', this.checked);

				if ( $this.is('.kcsb-ids') )
					$this.kcsbUnique();
			});
		}

		// Settings page (multiinput)
		else {
			$nu.find(':input').each(function() {
				$(this).val('');
			});
		}

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
				$('> .actions .count', $nu).text( $nu.index() + 1);
			}
			else {
				$block.children().each(function() {
					$('> .actions .count', this).text( $(this).index() + 1);
				});
			}
		}

		return false;
	});

	// Clear
	$('.row a.clear').live('click', function(e) {
		$(this).closest('.row').find(':input').val('');

		return false;
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
	$('.kcs-file a.rm').live('click', function(e) {
		e.preventDefault();
		var $this = $(this),
				$item	= $this.closest('.row');

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
	$('a.kcsf-upload').live('click', function(e) {
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
	var $single_files = $('.kcs-file-single');
	if ( $single_files.length ) {
		// Set height
		$('a.rm', $single_files).click(function (e) {
			e.preventDefault();
			$(this).fadeOut()
				.closest('div')
					.find('p.current').fadeOut(function() {
						$(this).siblings('a.up').show()
							.siblings('input').val('');
					});
		});

		// Single file: select
		$('a.up', $single_files).click(function (e) {
			e.preventDefault();
			var $el = $(this);

			win.kcSettings.upload.target = $el.closest('div');
			tb_show( '', $el.attr('href') );
		});
	}


	// Sortables
	$('ul.kc-sortable').sortable({
		axis: 'y',
		start: function(ev, ui) {
			ui.placeholder.height( ui.item.outerHeight() );
		}
	});


	// Help trigger
	$('a.kc-help-trigger').live('click', function() {
		if ( win.kcHelpBox !== undefined )  {
			win.kcPopHelp();
		}
		else {
			$('#contextual-help-link').click();
			$('#screen-meta').kcGoto();
		}
		return false;
	});



	/**** Builder ****/
	if ( $builder.length ) {
		// Scroll to form
		if ( !$builder.is('.hidden') )
			$builder.kcGoto();

		// Field deps
		$('.hasdep', $builder).kcFormDep({ disable: true	}).change();

		// Check 'slug/id' fields
		$('input.kcsb-slug').live('blur', function() {
			var $this  = $(this),
			    strVal = $this.val();

			$this.val( kcsbSlug(strVal) );
		});

		$('input.kcsb-ids').kcsbUnique();

		$('input.required, input.clone-id').live('blur', function() {
			$(this).kcsbCheck();
		});


		// Show form
		$('#new-kcsb').live('click', function() {
			$builder.kcGoto();
			return false;
		});


		$('a.kcsb-cancel').live('click', function() {
			$('#kcsb').fadeOut(function() {
				$('body').kcGoto();
			});
			return false;
		});


		// Setting clone
		$('a.clone-open').live('click', function() {
			$(this).parent().children().hide().filter('div.kcsb-clone').fadeIn();
			return false;
		});


		$('a.clone-do').click(function() {
			var $this  = $(this),
			    $input = $(this).siblings('input');

			if ( $input.kcsbCheck() === false )
				return false;

			$this.attr( 'href', $this.attr('href')+'&new='+$input.val() );
		});


		$('input.clone-id').bind('keypress', function(e) {
			var key = e.keyCode || e.which;
			if ( key === 13 ) {
				var $this = $(this);
				e.preventDefault();
				$this.blur()
					.siblings('a.clone-do').data('new', $this.val()).trigger('click');
			}
		});


		$('.kcsb-tools a.close').live('click', function() {
			var $this   = $(this),
			    $parent = $this.parent();
			$this.siblings('input').val('');
			$parent.fadeOut(function() {
				$(this).siblings().show();
			});

			return false;
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
