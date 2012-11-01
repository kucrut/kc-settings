var win = window.dialogArguments || opener || parent || top;

(function($) {
	$.fn.kcsfsPrepare = function( isAjax, newID ) {
		var mimeType = win.kcSettings.upload.mimeType;

		return this.each(function() {
			var $wrap = $(this),
			    $items = $wrap.children();

			if ( !$items.length )
				return;

			var currentFile = win.kcSettings.upload.target.find('input').val();

			$items.each(function() {
				var $item  = $(this);
				if ( $item.find('a.kc-select').length )
					return;

				var postID = isAjax ? newID : $item.attr('id').split("-")[2];
				if ( postID === currentFile )
					return;

				var itemType = $('#type-of-'+postID).val();
				if ( mimeType != 'all' && mimeType != itemType )
					return;

				var imgSrc = $item.find('img.pinkynail').attr('src'),
				    title  = $item.find('.title').text(),
				    type   = $item.find('#type-of-'+postID).val();

				$('<a href="#" class="kc-select" data-id="'+postID+'" data-img="'+imgSrc+'" data-title="'+title+'" data-type="'+type+'">'+win.kcSettings.upload.text.selFile+'</a>')
					.prependTo($item.children('.new'));
			});
		});
	};


	$(document).ready(function($) {
		$('#media-items').on('click', 'a.kc-select', function(e) {
			e.preventDefault();

			win.kcFileSingle( $(this).data() );
			win.tb_remove();
		});


		// Gallery and Media gallery tabs
		$('#library-form, #gallery-form').find('#media-items').kcsfsPrepare( false );

		// Hide gallery settings
		$('#gallery-settings').hide();

		// From computer / upload tab
		$('#media-upload').ajaxComplete(function(e, xhr, settings) {
			if ( xhr.status !== 200 || settings.url !== 'async-upload.php' )
				return;

			$('#media-items', this).kcsfsPrepare(true, xhr.responseText.match(/type-of-(\d+)/)[1]);
		});
	});
})(jQuery);
