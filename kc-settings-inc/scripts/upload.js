var win = window.dialogArguments || opener || parent || top;

(function($) {
	var texts     = win.kcSettings.upload.text,
	    current   = win.kcSettings.upload.target.data('currentFiles'),
	    $checks   = $(),
	    $buttons  = $('<div class="kcs-wrap"><h4>'+texts.head+'</h4> <a class="button check-all">'+texts.checkAll+'</a> <a class="button check-invert">'+texts.invert+'</a> <a class="button check-clear hidden">'+texts.clear+'</a> <a class="button add-checked hidden">'+texts.addFiles+'</a></div>')
				.on('click', 'a', function(e) {
					e.preventDefault();
					var $el = $(this);

					if ( $el.is('.check-all') ) {
						$checks.prop('checked', true).trigger('change');
					}
					else if ( $el.is('.check-clear') ) {
						$checks.prop('checked', false).trigger('change');
					}
					else if ( $el.is('.check-invert') ) {
						$checks.each(function() {
							$(this).prop('checked', !this.checked).trigger('change');
						});
					}
					else if ( $el.is('.add-checked') ) {
						var $items = $checks.filter(':checked'),
						    count  = $items.length;

						if ( !count )
							return;

						var files = {};
						$items.each(function() {
							var postID = this.value,
							    key    = 'file_'+postID,
							    $el    = $(this);

							files['file_'+postID] = {
								id : postID,
								title: $el.siblings('.title').text(),
								img: $el.closest('.media-item').find('.pinkynail').attr('src')
							}
						});

						win.kcFileMultiple( files );
						win.tb_remove();
					}
				});


	$.fn.kcsfPrepare = function( isAjax, newID ) {
		var mimeType = win.kcSettings.upload.mimeType,
		    cb = '<input type="checkbox" value="" class="kcs-files" />';

		return this.each(function() {
			var $wrap = $(this),
			    $items = $wrap.children();

			if ( !$items.length )
				return;

			if ( !$wrap.siblings('div.kcs-wrap').length )
				$wrap.parent().append($buttons);

			$items.each(function(e) {
				var $item = $(this);
				if ( $item.find('input.kcs-files').length )
					return;

				var postID  = isAjax ? newID : $item.attr('id').split("-")[2],
				    itemType = $('#type-of-'+postID).val();

				var $cb = $(cb);
				    $label = $('<label />');

				$cb.val(postID);
				if ( $.inArray(postID, current) > -1 ) {
					$cb.prop('checked', true);
				}
				if ( mimeType != 'all' && mimeType != itemType ) {
					$cb.prop('disabled', true);
					$label.attr('title', win.kcSettings.upload.text.filenomatch);
				}

				// Add new checkbox to the collection
				$checks = $checks.add( $cb );

				$item.children('.new')
					.prepend( $cb )
					.wrapInner( $label );
			});

			$checks.trigger('change');
		});
	};


	$(document).ready(function($) {
		$('body').on('change', 'input.kcs-files', function() {
			var $checked = $checks.filter(function(idx) {
				return $(this).prop('checked');
			});

			if ( $checked.length )
				$('a.add-checked, a.check-clear', $buttons).removeClass('hidden');
			else
				$('a.add-checked, a.check-clear', $buttons).addClass('hidden');
		});

		// Gallery & Media Library tabs
		$('#library-form, #gallery-form').find('#media-items').kcsfPrepare( false );

		// Hide gallery settings
		$('#gallery-settings').hide();

		// From computer / upload tab
		$('#media-upload').ajaxComplete(function(e, xhr, settings) {
			if ( xhr.status !== 200 || settings.url !== 'async-upload.php' )
				return;

			$('#media-items', this).kcsfPrepare( true, xhr.responseText.match(/type-of-(\d+)/)[1] );
		});
	});
})(jQuery);

