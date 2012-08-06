jQuery(document).ready(function($) {
	var $doc    = $(this),
	    $body   = $('body');

	var args = {
		sortable : {
			axis: 'y',
			start: function(ev, ui) {
				ui.placeholder.height( ui.item.height() );
			},
			stop: function(ev, ui) {
				ui.item .parent().kcReorder( ui.item.data('mode'), true );
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
				dateFormat: 'yy-mm',
				beforeShow: function(dateText, inst) {
					inst.dpDiv.addClass('kcDPMonth');
				},
				onChangeMonthYear: function(year, month, inst) {
					$(this).val( $.datepicker.formatDate('yy-mm', new Date(year, month - 1, 1)) );
				},
				onClose: function(dateText, inst) {
					setTimeout(function() {
						inst.dpDiv.removeClass('kcDPMonth');
					}, 300);
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

	// Chosen
	$('select.chosen').kcChosen();

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
