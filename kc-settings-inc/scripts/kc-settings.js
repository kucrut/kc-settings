jQuery(document).ready(function($) {
	$('a.kc-rem').live('click', function() {
		$(this).parent().remove();
		return false;
	});

	$('a.kc-add').live('click', function(e) {
		e.preventDefault();

		var $rows				= $(this).prev('div.kc-rows'),
				$lastRow		= $rows.children().last();

		if ( $('input', $lastRow).val() == '' && $('textarea', $lastRow).val() == '' )
			return false;

		var $nuRow	= $lastRow.clone(),
				$inputs	= $nuRow.children(':input');

		$inputs.each(function() {
			$(this).val('');
			this.name = this.name.replace(/\[(\d+)\]/, function(str, p1) {
				return '[' + ( parseInt(p1,10) + 1 ) + ']';
			});
		});

		//$('input', nuRow).attr('name', lastRow.attr('class') + '['+ rowNum +'][0]').val('');
		//$('textarea', nuRow).attr('name', lastRow.attr('class') + '['+ rowNum +'][1]').val('');

		$nuRow.appendTo($rows);
	});

	/*
	$('div.kc-rows > div').find(':input').blur(function() {
		var $this	= $(this),
				nuVal	= $this.val(),
				$parent	= $this.parent('div'),
				$remBut	= $('<a style="float:left;margin-top:7px" class="kc-rem button">Delete</a>');

		if ( nuVal !== '' && !$parent.find('a.kc-rem button').length )
			$parent.append($remBut);
	});
	*/


	// Datepicker
	var $dateInputs = $('input[type=date]');
	if ( $dateInputs.length && Modernizr.inputtypes.date === false ) {
		$dateInputs.datepicker({
			dateFormat: 'yy-mm-dd'
		});
	}
});
