function in_array(needle, haystack) {
  for(var key in haystack) {
    if( needle === haystack[key] )
      return true;
  }
  return false;
}


jQuery(document).ready(function($) {
  var win = window.dialogArguments || opener || parent || top,
      $submit = $('.ml-submit'),
      $mItems = $('#media-items');

  if ( $submit.length ) {
    $('<a class="button">'+win.kcFiles.addText+'</a>').appendTo($submit)
      .click(function(e) {
	e.preventDefault();
	var nuCount = 0;

	win.kcFiles.nu = [];
	$('input.kcs-files', $mItems).each(function() {
	  var $el = $(this);
	  if ( !in_array(this.value, win.kcFiles.files) && this.checked ) {
	    var nuItem = [this.value, $el.siblings('.title').text()],
		$thumb = $el.closest('.media-item').find('.thumbnail');

	    if ( $el.closest('.media-item').find('.image-size').length )
	      nuItem.push( $thumb.attr('src') );

	    win.kcFiles.nu.push( nuItem );
	  }
	});
	win.kcFiles.nuCount = nuCount;

	win.pret();
	win.tb_remove();
      });
  }

  $('.new', $mItems).each(function(e) {
    var $el = $(this).parent(),
	pID = $el.attr('id').split("-")[2],
	iCheck = ( in_array(pID, win.kcFiles.files) ) ? ' checked="checked"' : '';

	$input = $('<input type="checkbox" value="'+pID+'" '+iCheck+'class="kcs-files" style="margin-right:.5em"/>');

    $el.children('.new')
      .prepend($input)
      .wrapInner('<label />');
  });



  //console.log( win.kcFiles );
});
