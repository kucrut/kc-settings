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
					color: clr
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
				dateFormat: 'yy-mm',
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
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
				},
				currentText: win.kcSettings.texts.now
			},
			datetime: {
				dateFormat: 'yy-mm-dd',
				timeFormat: 'hh:mm',
				separator: 'T',
				stepHour: 1,
				stepMinute: 1,
				currentText: win.kcSettings.texts.now
			},
			text: {
				isRTL: win.isRTL,
				timeText: win.kcSettings.texts.time,
				hourText: win.kcSettings.texts.hour,
				minuteText: win.kcSettings.texts.minute,
				currentText: win.kcSettings.texts.today,
				closeText: win.kcSettings.texts.done,
				nextText: win.kcSettings.texts.next,
				prevText: win.kcSettings.texts.prev,
				dayNames: win.kcSettings.texts.dayNames.full,
				dayNamesMin: win.kcSettings.texts.dayNames.min,
				dayNamesShort: win.kcSettings.texts.dayNames.shrt,
				monthNames: win.kcSettings.texts.monthNames.full,
				monthNamesShort: win.kcSettings.texts.monthNames.shrt,
				weekHeader: win.kcSettings.texts.weekNames.shrt,
				timeOnlyTitle: win.kcSettings.texts.chooseTime
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
		Modernizr.load([{
			load: kcGetSNS('jquery_ui_datepicker', win.kcSettings.js).concat( kcGetSNS('jquery_ui', win.kcSettings.css) ),
			complete: function() {
				$dateInputs.each(function() {
					$(this).datepicker( $.extend( args.datepicker.text, args.datepicker[$(this).attr('type')] ) );
				});
			}
		}]);
	}

	var $dtInputs = $('input[type=datetime]');
	if ( $dtInputs.length && Modernizr.inputtypes['datetime'] === false ) {
		Modernizr.load([{
			load: kcGetSNS('jquery_ui_datetimepicker', win.kcSettings.js),
			complete: function() {
				var _conf = $.extend( args.datepicker.text, args.datepicker.datetime );
				$dtInputs.datetimepicker( $.extend( _conf, {timeFormat: 'hh:mmZ'} ) );
			}
		}]);
	}

	var $dtlInputs = $('input[type=datetime-local]');
	if ( $dtlInputs.length && Modernizr.inputtypes['datetime-local'] === false ) {
		Modernizr.load([{
			load: kcGetSNS('jquery_ui_datetimepicker', win.kcSettings.js),
			complete: function() {
				$dtlInputs.datetimepicker( $.extend( args.datepicker.text, args.datepicker.datetime ) );
			}
		}]);
	}


	var $timeInputs = $('input[type=time]');
	if ( $timeInputs.length && Modernizr.inputtypes['time'] === false ) {
		Modernizr.load([{
			load: kcGetSNS('jquery_ui_datetimepicker', win.kcSettings.js),
			complete: function() {
				$timeInputs.timepicker( $.extend( args.datepicker.text, args.datepicker.datetime ) );
			}
		}]);
	}

	// Color
	var $colorInputs = $('input[type=color]');
	if ( $colorInputs.length && Modernizr.inputtypes.color === false ) {
		Modernizr.load([{
			load: kcGetSNS( 'jquery_colorpicker', win.kcSettings.js).concat( kcGetSNS('jquery_colorpicker', win.kcSettings.css) ),
			complete: function () {
				$colorInputs.ColorPicker(args.colorpicker)
				.each(function() {
					var $el = $(this).addClass('hasColorpicker');
					if ( $el.val() !== '' )
						$el.css({
							backgroundColor: this.value,
							color: this.value
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
