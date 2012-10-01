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

	$('#kc-menu_navmeta').prependTo( $('#post-body') )
		.show();

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
	// Enh.
	$('select.chosen').kcChosen();

	// Polyfills
	$('input[type=color]').kcPFiColor( args.colorpicker );
	$('input[type=date]').kcPFiDate( args.datepicker.date );

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


				$('input[type=color]', $addTagForm).kcPFiColor( args.colorpicker );
				$('input[type=date]', $addTagForm).kcPFiDate( args.datepicker.date );

				$('.kcs-tabs', $addTagForm).kcTabs();
				$('select.chosen', $addTagForm).kcChosen();

				$addTagForm.trigger('kcsRefreshed');
			});
		}
	}
});
