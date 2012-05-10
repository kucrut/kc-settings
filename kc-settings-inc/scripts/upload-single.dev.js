var win = window.dialogArguments || opener || parent || top;

(function($) {
	$.fn.kcsfsPrepare = function() {
		return this.each(function() {
			var $wrap = $(this),
					$items = $wrap.children();

			if ( !$items.length )
				return;

			var currentFile = win.kcSettings.upload.target.find('input').val();

			$items.each(function() {
				var $item  = $(this),
						postID = $item.attr('id').split("-")[2];

				if ( postID === currentFile )
					return;

				var imgSrc = $item.find('img.pinkynail').attr('src'),
						title  = $item.find('.title').text(),
						type   = $item.find('#type-of-'+postID).val();

				$item.children('.new').prepend('<a href="#" class="kc-select" data-id="'+postID+'" data-img="'+imgSrc+'" data-title="'+title+'" data-type="'+type+'">'+win.kcSettings.upload.text.selFile+'</a>');
			});
		});
	};

	$(document).ready(function($) {
		// Gallery and Media gallery tabs
		$('#library-form, #gallery-form').find('#media-items').kcsfsPrepare();

		// From computer Upload tab
		$('#media-upload').ajaxComplete(function(e, xhr, settings) {
			$('#media-items', this).kcsfsPrepare();
		});

		// Send file to setting/metadata form
		$('a.kc-select').on('click', function(e) {
			e.preventDefault();

			win.kcFileSingle( $(this).data() );
			win.tb_remove();
		});
	});
})(jQuery);
