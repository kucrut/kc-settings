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
	$.fn.kcsbShowDeps = function( sel ) {
		return this.each(function() {
			var $el = $(this);
			if ( $el.is(sel) ) {
				if ( $el.is(':hidden') ) {
					$el.fadeIn().find(':input').each(function() {
						$(this).removeAttr('disabled');
					});
				}
			}
			else {
				$el.hide().find(':input').each(function() {
					$(this).attr('disabled', true);
				});
			}
		});
	};


	$.fn.kcsbIDep = function() {
		return this.each(function() {
			var $this			= $(this),
					iVal			= $this.val(),
					suffix		= $this.attr('name').match(/\[(\w+)\]$/)[1],
					$targets	= $this.parent().siblings('.idep_'+suffix);
			if ( $this.is('.global') ) {
				var $more = $('.global_idep_'+suffix);
				$targets	= $targets.add( $more );
			}

			if ( !$targets.length )
				return;

			$this.data('targets', $targets);
			$targets.kcsbShowDeps( '.'+iVal );

			$this.change(function() {
				var $input = $(this);
				$input.data('targets').kcsbShowDeps( '.'+$input.val() );
			});

		});
	};


	$.fn.kcsbGoto = function() {
		return this.each(function() {
			var $target = $(this);

			$target.fadeIn(function() {
				$('html, body').stop().animate({
					scrollTop: ( $target.offset().top - 20 )
				}, 800);
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

		if ( $input.val() == '' ) {
			$input.focus().css('borderColor', '#ff0000');
			return false;
		} else {
			$input.removeAttr('style');
		}
	};


	$.fn.kcsbReorder = function( mode ) {
		var regex	= new RegExp(mode+'\\]\\[(\\d+)');

		return this.each(function() {
			var $this 	= $(this),
					$items	= $this.children(),
					i				= -1;

			$items.each(function() {
				$('> .actions .count', this).text( $(this).index() + 1);
				i++;
				$(this).find(':input').each(function() {
					this.name = this.name.replace(regex, function(str, p1) {
						return mode + '][' + i;
					});
				});
			});
		});
	};
})(jQuery);


jQuery(document).ready(function($) {
	var $builder	= $('#kcsb'),
			$kcsbForm = $('form.kcsb'),
			$components = $('#kc-settings-form').find('div.postbox').find('#kcs-components');

	// Plugin/theme settings components
	if ( $components.length ) {
		var mbPrefix = '#'+$components.closest('div.metabox-holder').attr('id')+'-',
				$generalChecks = $components.find(':checkbox'),
				$sectionChecks = $();

		$generalChecks.each(function() {
			if ( !$( mbPrefix+this.value ).length )
				return;

			var $check = $(this),
					$target = $(mbPrefix+this.value+'-hide');

			$check.data( 'sectTarget', $target );
			if ( !(this.checked === $target[0].checked) ) {
				$target.prop('checked', this.checked).triggerHandler('click');
			}
			$sectionChecks = $sectionChecks.add( $check );
		});

		if ( $sectionChecks.length ) {
			$sectionChecks.change(function() {
				$(this).data('sectTarget').prop('checked', this.checked).triggerHandler('click');
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
			// Reorder input names
			ui.item.parent()
				.kcsbReorder( ui.item.data('mode') );
		}
	});


	// Remove
	$('.row a.del').live('click', function(e) {
		var $this		= $(this),
				$item		= $this.closest('.row'),
				mode		= $item.data('mode'),
				isLast	= $item.is(':last-child');

		if ( !$item.siblings('.row').length )
			return false;

		$item.addClass('removing').fadeOut('slow', function() {
			$(this).remove();
			// Reorder input names
			if ( !isLast ) {
				$item.parent().kcsbReorder( mode );
			}
		});

		return false;
	});


	// Add
	$('.row a.add').live('click', function(e) {
		var $this		= $(this),
				$item		= $this.closest('.row'),
				mode		= $item.data('mode'),
				$nu			= $item.clone(false).addClass('adding');


		if ( $kcsbForm.length ) {
			$nu.find('.kc-rows').each(function() {
				var $kids		= $(this).children('.row');

				if ( $kids.length > 1 ) {
					$kids.not(':first').remove();
				}
			});

			$nu.find(':input').each(function() {
				var $this = $(this);
				if ( this.type == 'text' || this.type == 'textarea' ) {
					$this.removeAttr('style')
						.val('');
				}
				else if ( this.type == 'checkbox' || this.type == 'radio' ) {
					if ( $this.prop('checked') )
						this.checked = true;
					else
						this.checked = false;
				}

				if ( $this.is('.kcsb-ids') )
					$this.kcsbUnique();

			});

			$('.idep', $nu).kcsbIDep();
		} else {
			$nu.find(':input').each(function() {
				$(this).val('');
			});
		}

		$item.after( $nu );
		$nu.kcsbGoto();
		setTimeout(function() {
			$nu.removeClass('adding');
		}, 1000);

		$item.parent().kcsbReorder( mode );

		return false;
	});

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

	$('ul.kc-sortable').sortable({
		axis: 'y',
		start: function(ev, ui) {
			ui.placeholder.height( ui.item.outerHeight() );
		}
	});



	/**** Builder ****/
	// Scroll to form
	if ( !$builder.is('.hidden') )
		$builder.kcsbGoto();

	$('.idep:input').kcsbIDep();

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
		$builder.kcsbGoto();
		return false;
	});


	$('a.kcsb-cancel').live('click', function() {
		$('#kcsb').fadeOut(function() {
			$('body').kcsbGoto();
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

	// Help trigger
	$('a.kc-help-trigger').live('click', function() {
		$('#contextual-help-link').click();
		$('#screen-meta').kcsbGoto();
		return false;
	});

});
