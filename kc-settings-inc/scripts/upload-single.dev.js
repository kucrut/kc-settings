var win = window.dialogArguments || opener || parent || top;

jQuery(document).ready(function($) {
  var $form1  = $('#library-form, #gallery-form'),
	    $form2  = $('#file-form')
	    $mItems = $('#media-items');

  // If we're in the Gallery or Library tab
  if ( $form1.length && $mItems.children().length ) {
		var text = win.kcSettings.upload.text.selFile;
		$('.new', $mItems).each(function() {
			var $el = $(this).parent(),
			    pID = $el.attr('id').split("-")[2],
			    img = $el.find('.pinkynail').attr('src'),
			    ttl = $el.find('.title').text(),
			    type= $el.find('#type-of-'+pID).val();

			$el.children('.new').prepend('<a href="#" class="kc-select" data-id="'+pID+'" data-img="'+img+'" data-title="'+ttl+'" data-type="'+type+'">'+text+'</a>');
		});

		$('a.kc-select').click(function(e) {
			e.preventDefault();

			win.kcFileSingle( $(this).data() );
			win.tb_remove();
		});
	}
});