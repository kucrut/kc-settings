window.kcSettings.mediaFieldsFrames = {};

jQuery(document).ready(function($){
	var setMedia = function( $el, media, size_wanted ) {
		var
			$img = $el.find('img'),
			$filename = $el.find('div.filename').text( media.title ),
			src, size, size_wanted;

		if ( media.type === 'image' ) {
			if ( size_wanted !== 'thumbnail' && media.sizes[size_wanted] ) {
				size = media.sizes[size_wanted];
				$el.find('div.attachment-preview')
					.add($el.find('div.thumbnail'))
					.css( {
						width: size.width,
						height: size.height
					});
			}
			else {
				size = media.sizes.thumbnail || media.sizes.medium || media.sizes.full;
				$el.find('div.attachment-preview')
					.add($el.find('div.thumbnail'))
					.removeAttr('style');
			}

			$img.attr('width', size.width);
			$img.attr('height', size.height);
			src = size.url;
			$filename.hide();
			$el.addClass('type-image');
		}
		else {
			src = media.icon;
			$filename.show();
			$el.removeClass('type-image');
		}

		$el.find('input').first().val( media.id ).triggerHandler('change.kcSettings.mediaFields');
		$img.attr('src', src);

		return $el;
	};

	$('ul.kc-media-list.multiple').sortable();

	$('body')
		.on('click', 'a.kc-media-select', function(e) {
			e.preventDefault();

			var
				$el     = $(this),
				fieldID = $el.data('fieldid'),
				$target = $('#'+fieldID),
				current = [];

			$target.find('input').each( function( idx, input ) {
				if ( input.value !== '' )
					current.push( input.value );
			});
			$target.data( 'current', current );

			if ( window.kcSettings.mediaFieldsFrames[ fieldID ] ) {
				window.kcSettings.mediaFieldsFrames[ fieldID ].open();
				return;
			}

			var _options = {
				className: 'media-frame kc-media-frame',
				frame:     'select',
				multiple:  kcSettings.mediaFields[ fieldID ].multiple,
				title:     kcSettings.mediaFields[ fieldID ].frame_title,
				button:    {
					text:  kcSettings.mediaFields[ fieldID ].insert_button
				},
				syncSelection: false
			};
			if ( kcSettings.mediaFields[ fieldID ].mime_type !== '_all' ) {
				_options.library = {
					type: kcSettings.mediaFields[ fieldID ].mime_type
				};
			}

			window.kcSettings.mediaFieldsFrames[ fieldID ] = wp.media( _options );

			window.kcSettings.mediaFieldsFrames[ fieldID ].on('select', function() {
				var $target    = $('#'+fieldID);
				var current    = $target.data('current');
				var $firstItem = $target.children().first();
				var selection  = window.kcSettings.mediaFieldsFrames[ fieldID ].state().get('selection');

				if ( window.kcSettings.mediaFieldsFrames[ fieldID ].options.multiple ) {
					var $template = $firstItem.clone();
					var $newItem  = null;
					var itemID;
					selection = selection.toJSON();

					$.each( selection, function( idx, item ) {
						itemID = item.id.toString();
						if ( $.inArray(itemID, current) > -1 )
							return;

						$target.append( setMedia( $template.clone(), item, $target.data('size') ) );
					});

					if ( $target.is('.hidden') ) {
						$firstItem.remove();
					}
				}
				else {
					setMedia( $firstItem, selection.first().toJSON(), $target.data('size') );
				}

				if ( $target.is('.hidden') ) {
					$target.fadeIn(function() {
						$target.removeClass('hidden');
					});
				}
			});

			window.kcSettings.mediaFieldsFrames[ fieldID ].open();
		})
		.on('mouseenter', 'ul.kc-media-list li.attachment', function(e) {
			$(this).addClass('details selected');
		})
		.on('mouseleave', 'ul.kc-media-list li.attachment', function(e) {
			$(this).removeClass('details selected');
		})
		.on('click', 'ul.kc-media-list a.check', function(e) {
			e.preventDefault();

			var $item   = $(this).closest('li');
			var $target = $item.parent();

			if ( $item.siblings().length ) {
				$item.fadeOut( parseInt($target.data('animate')), function() {
					$item.remove();
				});
			}
			else {
				$target.fadeOut( parseInt( $target.data('animate') ), function() {
					$target.addClass('hidden').removeAttr('style');
					$item.find('input').val('')
				});
			}
		});

	/*
	$('#addtag').ajaxComplete( function( e, xhr, settings ) {
		if ( settings.data.indexOf('action=add-tag') < 0 )
			return;

		$('div.kc-media-selector').each(function() {
			var $list = $('ul.kc-media-list', this);

			$list.fadeOut(function() {
				$list.addClass('hidden').removeAttr('style');
			})

			$list.children().filter(function(idx) {
				if ( idx == 0 ) {
					$('input', this).val('');
					$('img', this).attr('src', '');
				}
				else {
					$(this).remove();
				}
			});
		});
	});
	*/
});
