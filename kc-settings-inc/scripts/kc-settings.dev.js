var win = window.dialogArguments || opener || parent || top;

win.kcsInsertFiles = function() {
	var count = win.kcSettings.upload.nu.length;

	if ( count ) {
		var $list = win.kcSettings.upload.id,
				$lastItem = $list.children().last(),
				$nuEls = jQuery();

		while ( count ) {
			count--;
			var $nuItem = $lastItem.clone();

			jQuery('input', $nuItem).each(function() {
				this.value = win.kcSettings.upload.nu[count][0];
			});
			jQuery('.title', $nuItem).text(win.kcSettings.upload.nu[count][1]);
			$nuItem.find('img').attr('src', win.kcSettings.upload.nu[count][2]);

			$nuEls = $nuEls.add( $nuItem );
		}

		$list.append( $nuEls );
		if ( $lastItem.is('.hidden') ) {
			$nuEls.show();
			$lastItem.remove();
		}
	}
};


// Credit: http://stackoverflow.com/questions/784012/javascript-equivalent-of-phps-in-array
function inArray(needle, haystack) {
	var length = haystack.length;
	for (var i = 0; i < length; i++) {
		if (haystack[i] == needle) return true;
	}
	return false;
}


function kcsbSlug( str ) {
	strNu = str.replace(/^\-+/, '');
	strNu = strNu.replace(/^_+/, '');
	strNu = strNu.replace(/[^A-Za-z0-9\-_]/g, '');

	if ( strNu.match(/^\-+/) || strNu.match(/^_+/) )
		strNu = kcsbSlug( strNu );

	return strNu;
}


(function($) {
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
					var $input	= $(this),
							olVal		= $this.data('olVal'),
							nuVal		= $input.val();

					if ( nuVal != olVal && inArray(nuVal, kcSettings._ids[$input.data('ids')]) )
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
		defaults = {
			disable: true,
			callback: function() {}
		};
		opts = $.extend({}, defaults, opts);

		return this.each(function() {
			var $el		= $(this),
					val		= $el.val(),
					$dep	= ( $el.data('scope') !== undefined ) ?
										$el.closest( $el.data('scope') ).find( $el.data('child') ) :
										$( $el.data('child') ),
					show;

			if ( !$dep.length )
				return;

			$dep.each(function() {
				var $c		= $(this),
						depon	= $c.data('dep');

				if ( (typeof depon === 'string' && depon === val) || (typeof depon === 'object' && inArray(val, depon)) ) {
					show = true;
				}
				else {
					show = false;
				}

				$c.toggle( show );
				if ( opts.disable === true ) {
					$c.find(':input').prop('disabled', !show);
				}
			});
		});
	};


	$.fn.kcReorder = function( mode, all ) {
		var rgx1	= new RegExp(mode+'\\]\\[(\\d+)'),
				rgx2	= new RegExp(mode+'\\-(\\d+)'),
				$el		= $(this);

		if ( all === true ) {
			var $els	= $el.children(),
					i			= 0;
		} else {
			var $els	= $el,
					i			= $el.index();
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
	var $builder	= $('#kcsb'),
			$kcsbForm = $('form.kcsb'),
			$components = $('#kc-settings-form').find('div.postbox').find('#kcs-components');

	/*** Plugin/theme settings components ***/
	if ( $components.length ) {
		var mbPrefix = '#'+$components.closest('div.metabox-holder').attr('id')+'-',
				$generalChecks = $components.find(':checkbox'),
				$sectionChecks = $();

		$generalChecks.each(function() {
			var $mBox = $( mbPrefix+this.value );
			if ( !$mBox.length )
				return;

			var $check = $(this),
					$target = $(mbPrefix+this.value+'-hide');

			$check.data( 'sectTarget', $target )
						.data( 'mBox', $mBox );
			if ( !(this.checked === $target[0].checked) ) {
				$target.prop('checked', this.checked).triggerHandler('click');
			}
			$sectionChecks = $sectionChecks.add( $check );
		});

		if ( $sectionChecks.length ) {
			$sectionChecks.change(function() {
				var $el = $(this);
				$el.data('sectTarget').prop('checked', this.checked).triggerHandler('click');

				// Scroll to
				if ( this.checked )
					$el.data('mBox').kcGoto( {offset: -40, speed: 'slow'} );
			});
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
		var $this		= $(this),
				$item		= $this.closest('.row'),
				$block	= $item.parent(),
				mode		= $item.data('mode'),
				isLast	= $item.is(':last-child');

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
		var $this		= $(this),
				$item		= $this.closest('.row'),
				$block	= $item.parent(),
				mode		= $item.data('mode'),
				isLast	= $item.is(':last-child'),
				$nu			= $item.clone(false).addClass('adding'),
				scroll	= false,
				speed		= 400;

		if ( mode == 'sections' ) {
			scroll = true;
			speed = 1200;
		}
		else if ( mode == 'fields' ) {
			scroll = true;
			speed = 800;
		}


		// Builder fields
		if ( $kcsbForm.length ) {
			$nu.find('.kc-rows').each(function() {
				var $kids		= $(this).children('.row');

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
			load: [win.kcSettings.paths.scripts+'/colorpicker/js/colorpicker.js', win.kcSettings.paths.scripts+'/colorpicker/css/colorpicker.css'],
			complete: function () {
				$colorInputs.ColorPicker({
					onBeforeShow: function () {
						$(this).ColorPickerSetColor(this.value);
					},
					onSubmit: function(hsb, hex, rgb, el) {
						var clr = '#'+hex;
						$(el).css('backgroundColor', clr )
							.val( clr )
							.ColorPickerHide();
					}
				}).each(function() {
					if ( $(this).val() !== '' )
						$(this).css('backgroundColor', $(this).val() );
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
			if ( $item.siblings().length ) {
				$item.remove();
			}
			else {
				$item.removeClass('removing')
					.addClass('hidden')
					.find(':input')
						.val('')
						.prop('checked', false);

				var $h = $('input[type="hidden"]', $item);
				$h.data( 'olName', $h.attr('name') )
					.attr('name', '');
			}
		});

	});


	// Add files button
	$('a.kcsf-upload').live('click', function(e) {
		e.preventDefault();
		var $el = $(this),
				$group = $el.parent(),
				$solo = $group.find('.row.hidden');

		win.kcSettings.upload.id = $( '#'+$group.attr('id')+' > ul' );
		win.kcSettings.upload.files = [];

		if ( $solo.length ) {
			var $h = $('input[type="hidden"]', $solo);
			$h.attr('name', $h.data('olName') );
		} else {
			$('input.mid', $group).each(function() {
				win.kcSettings.upload.files.push(this.value);
			});
		}

		tb_show( '', $el.attr('href') );
	});


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
		$('.hasdep', $builder).live('change', function() {
			$(this).kcFormDep({ disable: true	});
		}).change();

		// Check 'slug/id' fields
		$('input.kcsb-slug').live('blur', function() {
			var $this 	= $(this),
					strVal	= $this.val();

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
			var $this		= $(this),
					$input	= $(this).siblings('input');

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
			var $this = $(this),
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
